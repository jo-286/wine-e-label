<?php

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('Wine_E_Label_GitHub_Updater')) {
	class Wine_E_Label_GitHub_Updater
	{
		private string $plugin_file;
		private string $plugin_basename;
		private string $plugin_slug;
		private string $plugin_name;
		private string $plugin_version;
		private string $manifest_key;
		private string $manifest_url;
		private string $homepage;
		private string $cache_key;

		public function __construct(array $args)
		{
			$defaults = array(
				'plugin_file'     => '',
				'plugin_basename' => '',
				'plugin_slug'     => '',
				'plugin_name'     => '',
				'plugin_version'  => '',
				'manifest_key'    => '',
				'manifest_url'    => '',
				'homepage'        => '',
				'cache_key'       => '',
			);

			$args = wp_parse_args($args, $defaults);

			$this->plugin_file     = (string) $args['plugin_file'];
			$this->plugin_basename = (string) $args['plugin_basename'];
			$this->plugin_slug     = (string) $args['plugin_slug'];
			$this->plugin_name     = (string) $args['plugin_name'];
			$this->plugin_version  = (string) $args['plugin_version'];
			$this->manifest_key    = (string) $args['manifest_key'];
			$this->manifest_url    = (string) $args['manifest_url'];
			$this->homepage        = (string) $args['homepage'];
			$this->cache_key       = (string) $args['cache_key'];

			if ($this->plugin_basename === '' || $this->plugin_slug === '' || $this->manifest_key === '' || $this->manifest_url === '') {
				return;
			}

			add_filter('pre_set_site_transient_update_plugins', array($this, 'inject_update'));
			add_filter('plugins_api', array($this, 'plugins_api'), 20, 3);
			add_action('upgrader_process_complete', array($this, 'clear_manifest_cache'), 10, 2);
		}

		public function inject_update($transient)
		{
			if (!is_object($transient) || empty($transient->checked) || !is_array($transient->checked)) {
				return $transient;
			}

			if (!isset($transient->checked[$this->plugin_basename])) {
				return $transient;
			}

			$plugin = $this->get_manifest_plugin();
			if ($plugin === null || empty($plugin['version'])) {
				return $transient;
			}

			$update_item = $this->build_update_item($plugin);

			if (version_compare((string) $plugin['version'], $this->plugin_version, '>')) {
				$transient->response[$this->plugin_basename] = $update_item;
			} else {
				$transient->no_update[$this->plugin_basename] = $update_item;
			}

			return $transient;
		}

		public function plugins_api($result, string $action, $args)
		{
			if ($action !== 'plugin_information' || !is_object($args) || empty($args->slug) || $args->slug !== $this->plugin_slug) {
				return $result;
			}

			$plugin = $this->get_manifest_plugin();
			if ($plugin === null) {
				return $result;
			}

			$sections = array();
			if (!empty($plugin['sections']) && is_array($plugin['sections'])) {
				foreach ($plugin['sections'] as $key => $value) {
					$sections[(string) $key] = (string) $value;
				}
			}

			return (object) array(
				'name'           => (string) ($plugin['name'] ?? $this->plugin_name),
				'slug'           => $this->plugin_slug,
				'version'        => (string) ($plugin['version'] ?? $this->plugin_version),
				'author'         => (string) ($plugin['author'] ?? ''),
				'author_profile' => (string) ($plugin['author_profile'] ?? ''),
				'homepage'       => (string) ($plugin['homepage'] ?? $this->homepage),
				'requires'       => (string) ($plugin['requires'] ?? ''),
				'requires_php'   => (string) ($plugin['requires_php'] ?? ''),
				'tested'         => (string) ($plugin['tested'] ?? ''),
				'last_updated'   => (string) ($plugin['last_updated'] ?? ''),
				'download_link'  => (string) ($plugin['package'] ?? ''),
				'sections'       => $sections,
				'banners'        => array(),
				'icons'          => array(),
			);
		}

		public function clear_manifest_cache($upgrader, array $hook_extra): void
		{
			if (empty($hook_extra['plugins']) || !is_array($hook_extra['plugins'])) {
				return;
			}

			if (!in_array($this->plugin_basename, $hook_extra['plugins'], true)) {
				return;
			}

			if ($this->cache_key !== '') {
				delete_site_transient($this->cache_key);
			}
		}

		private function get_manifest_plugin(): ?array
		{
			$manifest = $this->get_manifest();
			if ($manifest === null || empty($manifest['plugins'][$this->manifest_key]) || !is_array($manifest['plugins'][$this->manifest_key])) {
				return null;
			}

			return $manifest['plugins'][$this->manifest_key];
		}

		private function get_manifest(): ?array
		{
			if ($this->cache_key !== '') {
				$cached = get_site_transient($this->cache_key);
				if (is_array($cached)) {
					return $cached;
				}
			}

			$response = wp_safe_remote_get(
				$this->manifest_url,
				array(
					'timeout' => 15,
					'headers' => array(
						'Accept' => 'application/json',
					),
				)
			);

			if (is_wp_error($response)) {
				return null;
			}

			$status_code = (int) wp_remote_retrieve_response_code($response);
			if ($status_code < 200 || $status_code >= 300) {
				return null;
			}

			$body = (string) wp_remote_retrieve_body($response);
			if ($body === '') {
				return null;
			}

			$manifest = json_decode($body, true);
			if (!is_array($manifest)) {
				return null;
			}

			if ($this->cache_key !== '') {
				set_site_transient($this->cache_key, $manifest, 15 * MINUTE_IN_SECONDS);
			}

			return $manifest;
		}

		private function build_update_item(array $plugin): object
		{
			return (object) array(
				'id'            => (string) ($plugin['homepage'] ?? $this->homepage),
				'slug'          => $this->plugin_slug,
				'plugin'        => $this->plugin_basename,
				'new_version'   => (string) ($plugin['version'] ?? $this->plugin_version),
				'url'           => (string) ($plugin['homepage'] ?? $this->homepage),
				'package'       => (string) ($plugin['package'] ?? ''),
				'requires'      => (string) ($plugin['requires'] ?? ''),
				'requires_php'  => (string) ($plugin['requires_php'] ?? ''),
				'tested'        => (string) ($plugin['tested'] ?? ''),
				'icons'         => array(),
				'banners'       => array(),
				'banners_rtl'   => array(),
				'compatibility' => new stdClass(),
			);
		}
	}
}
