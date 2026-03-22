<?php
/**
 * Plugin Name: Wein E-Label Receiver
 * Plugin URI: https://github.com/jo-286/wine-e-label
 * Description: Wine E-Label Receiver for REST-delivered wine e-label pages without theme layout.
 * Version: 2.3.1
 * Author: Johannes Reith
 * Author URI: https://reithwein.com
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * Text Domain: wine-e-label-receiver
 * Update URI: https://github.com/jo-286/wine-e-label#wine-e-label-receiver
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-wine-e-label-github-updater.php';

new Wine_E_Label_GitHub_Updater(
	array(
		'plugin_file'     => __FILE__,
		'plugin_basename' => plugin_basename( __FILE__ ),
		'plugin_slug'     => 'wine-e-label-receiver',
		'plugin_name'     => 'Wein E-Label Receiver',
		'plugin_version'  => '2.3.1',
		'manifest_key'    => 'wine-e-label-receiver',
		'manifest_url'    => 'https://raw.githubusercontent.com/jo-286/wine-e-label/main/updates/plugin-updates.json',
		'homepage'        => 'https://github.com/jo-286/wine-e-label',
		'cache_key'       => 'wine_e_label_receiver_github_manifest',
	)
);

final class Wine_E_Label_Receiver {
	private const CPT             = 'reith_elabel';
	private const META_HTML       = '_relr_html';
	private const META_LANG       = '_relr_lang';
	private const META_DESIGN     = '_relr_design';
	private const META_SOURCE     = '_relr_source';
	private const NONCE           = 'relr_save_elabel';
	private const OPT_DESIGN      = 'relr_design_settings';
	private const OPT_UI          = 'relr_ui_settings';
	private const VERSION         = '2.3.1';
	private const ROUTE_NAMESPACE_V1 = 'reith-elabel/v1';
	private const ROUTE_NAMESPACE_V2 = 'reith-elabel/v2';

	private static array $defaults_design = array(
		'page_bg'              => '#f3f4f6',
		'card_bg'              => '#ffffff',
		'table_head_bg'        => '#f3f4f6',
		'text_color'           => '#111827',
		'muted_color'          => '#6b7280',
		'border_color'         => '#d1d5db',
		'base_font_size'       => 15,
		'small_font_size'      => 14,
		'button_font_size'     => 13,
		'font_family'          => 'system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif',
		'panel_radius'         => 16,
		'outer_width'          => 980,
		'label_width'          => 640,
		'outer_padding_y'      => 40,
		'card_padding'         => 26,
		'logo_enabled'         => '0',
		'logo_url'             => '',
		'logo_alt'             => '',
		'logo_max_height'      => 110,
		'product_image_enabled'=> '0',
		'product_image_max_height' => 200,
		'wine_name_enabled'    => '0',
		'wine_name_size'       => 28,
		'vintage_enabled'      => '0',
		'vintage_size'         => 17,
		'subtitle_enabled'     => '0',
		'subtitle_size'        => 20,
		'producer_region'      => '',
		'producer_country'     => '',
		'producer_address'     => '',
		'custom_css'           => '',
		'force_blank_layout'   => 1,
		'add_noindex'          => 1,
		'disable_canonical'    => 1,
		'close_comments'       => 1,
		'normalize_body'       => 1,
		'inject_viewport'      => 1,
	);

	private static array $defaults_ui = array(
		'language' => 'de',
	);

	private static array $translations = array(
		'de' => array(
			'plugin_name' => 'Wein E-Label Receiver',
			'plugin_description' => 'Empfängt Wein-E-Label-Seiten per REST, verwaltet sie zentral und liefert sie ohne Theme-Layout aus.',
			'overview' => 'Übersicht',
			'labels' => 'E-Labels',
			'design' => 'Design anpassen',
			'setup' => 'Einrichtung',
			'add_new' => 'Neu hinzufügen',
			'search' => 'Suchen',
			'status' => 'Status',
			'all_statuses' => 'Alle Status',
			'language' => 'Sprache',
			'all_languages' => 'Alle Sprachen',
			'filter' => 'Filtern',
			'reset' => 'Zurücksetzen',
			'title' => 'Titel',
			'slug' => 'Slug',
			'year' => 'Jahrgang',
			'updated' => 'Geändert',
			'created' => 'Erstellt',
			'actions' => 'Aktionen',
			'open' => 'Öffnen',
			'edit' => 'Bearbeiten',
			'download_html' => 'HTML',
			'download_json' => 'JSON',
			'trash' => 'Papierkorb',
			'bulk_actions' => 'Sammelaktionen',
			'bulk_apply' => 'Anwenden',
			'bulk_trash' => 'In Papierkorb',
			'bulk_publish' => 'Veröffentlichen',
			'bulk_draft' => 'Als Entwurf',
			'export_csv' => 'CSV exportieren',
			'labels_intro' => 'Zentrale Verwaltung aller empfangenen E-Labels dieser Receiver-Instanz.',
			'label_delete_hint' => 'Hinweis: E-Labels sollten möglichst auf der Hauptseite gelöscht werden, damit Hauptplugin und Receiver sauber synchron bleiben.',
			'reset_field' => 'Feld zurücksetzen',
			'no_labels' => 'Keine E-Labels gefunden.',
			'dashboard_intro' => 'Dieses Plugin empfängt E-Labels per REST, verwaltet sie auf der Receiver-Seite und liefert sie ohne Theme-Layout aus. Das visuelle Design wird jetzt zentral im Hauptplugin gepflegt.',
			'rest_base' => 'REST-Basis',
			'current_url' => 'Aktuelle Receiver-URL',
			'total_labels' => 'E-Labels gesamt',
			'published_labels' => 'Veröffentlicht',
			'drafts' => 'Entwürfe',
			'central_design_title' => 'Design-Verwaltung',
			'central_design_text' => 'Layout, Farben, Abstände und weitere Designwerte werden nicht mehr auf der Receiver-Seite gepflegt, sondern zentral im Hauptplugin.',
			'central_design_setup' => 'Designänderungen nimmst du im Hauptplugin unter „Design anpassen“ vor. Beim Speichern werden veröffentlichte Receiver-E-Labels automatisch neu synchronisiert.',
			'design_intro' => 'Hier steuerst du das Grundlayout der empfangenen E-Label-Seiten zentral für die gesamte Receiver-Instanz.',
			'colors' => 'Farben',
			'sizes' => 'Abstände und Größen',
			'robustness' => 'Robustheit',
			'live_preview' => 'Live-Vorschau',
			'load_defaults' => 'Standard laden',
			'page_bg' => 'Seitenhintergrund',
			'card_bg' => 'Kartenhintergrund',
			'table_head_bg' => 'Tabellenkopf',
			'text_color' => 'Fließtext',
			'muted_color' => 'Sekundärtext',
			'border_color' => 'Rahmen',
			'base_font_size' => 'Basisschriftgröße (px)',
			'small_font_size' => 'Kleine Schrift (px)',
			'button_font_size' => 'Button-Schrift (px)',
			'font_family' => 'Schriftart',
			'panel_radius' => 'Panel-Radius (px)',
			'outer_width' => 'Außenbreite (px)',
			'label_width' => 'Label-Breite (px)',
			'outer_padding_y' => 'Außenabstand oben/unten (px)',
			'card_padding' => 'Innenabstand Karte (px)',
			'custom_css' => 'Zusätzliches CSS',
			'force_blank_layout' => 'Leeres Plugin-Layout erzwingen',
			'add_noindex' => 'noindex Header setzen',
			'disable_canonical' => 'Canonical Redirect deaktivieren',
			'close_comments' => 'Kommentare und Pings schließen',
			'normalize_body' => 'Body/HTML Hintergrund neutralisieren',
			'inject_viewport' => 'Viewport-Meta sicherstellen',
			'save_design' => 'Design speichern',
			'settings_saved' => 'Einstellungen gespeichert.',
			'bulk_done' => 'Sammelaktion ausgeführt.',
			'preview_hint' => 'Die Vorschau aktualisiert sich direkt beim Ändern.',
			'font_system' => 'System Sans',
			'font_arial' => 'Arial / Helvetica',
			'font_georgia' => 'Georgia',
			'font_times' => 'Times New Roman',
			'font_verdana' => 'Verdana',
			'font_tahoma' => 'Tahoma',
			'setup_intro' => 'Receiver-Verbindung zum Hauptplugin einrichten. Das E-Label-Design wird zentral vom Hauptplugin übernommen.',
			'setup_box_title' => 'So verbindest du die Receiver-Seite mit dem Hauptplugin',
			'step_1' => 'Auf der Receiver-Domain im WordPress-Dashboard unter „Benutzer“ einen Benutzer mit Administratorrechten anlegen, zum Beispiel „api_elabel“, oder einen bestehenden Administrator verwenden.',
			'step_2' => 'Unter „Benutzer bearbeiten“ im Benutzerprofil ein Anwendungspasswort erstellen. Der Name des Anwendungspassworts kann frei gewählt werden, zum Beispiel „api_elabel“. Danach das Passwort generieren, kopieren und sicher speichern.',
			'step_3' => 'Im Hauptplugin die Receiver-URL, den Benutzernamen und das Anwendungspasswort eintragen.',
			'step_4' => 'Im Hauptplugin testen, ob die Verbindung zur Receiver-Instanz funktioniert. Außerdem prüfen, dass die REST-API erreichbar ist und keine Firewall, kein Security-Plugin und keine Auth-Weiterleitung die Anfrage blockiert.',
			'step_5' => 'Das Hauptplugin sendet die E-Labels dann an diese Receiver-Instanz und erzeugt dort eine E-Label-Seite.',
			'step_6' => 'Die Übertragung mit einem Testprodukt prüfen und kontrollieren, ob auf der Receiver-Seite die E-Label-Seite korrekt angelegt wurde und aufrufbar ist.',
			'language_setting' => 'Plugin-Sprache',
			'save_language' => 'Sprache speichern',
			'setup_hint' => 'Im Hauptplugin nur die Receiver-URL der Domain eintragen, nicht die komplette /wp-json/...-Adresse.',
			'design_page_removed_notice' => 'Die frühere Receiver-Designseite wurde entfernt. Das E-Label-Design wird jetzt zentral im Hauptplugin gepflegt.',
			'setup_receiver_url' => 'Receiver-URL für das Hauptplugin',
			'setup_info_url' => 'API-Erkennung testen',
			'setup_legacy_url' => 'Legacy-Fallback',
			'recommended' => 'empfohlen',
			'metabox_html' => 'E-Label HTML',
			'metabox_lang' => 'Label-Sprache',
			'raw_html' => 'Rohes HTML',
			'public_url' => 'Öffentliche URL',
			'location' => 'Ort',
			'main_domain' => 'Main Domain',
			'subdomain' => 'Subdomain',
			'second_domain' => 'Receiver Domain',
			'metabox_note' => 'Vollständige HTML-Dokumente werden 1:1 ausgegeben. Fragmente werden automatisch in ein minimales HTML-Dokument eingebettet.',
			'unknown' => '—',
			'preview_title' => 'Rivaner trocken 2024 Bio',
			'preview_subtitle' => 'Zutaten und Nährwerte je 100 ml',
			'preview_ingredients' => 'Trauben, Saccharose, konzentrierter Traubenmost, Säureregulator: Weinsäure, Antioxidationsmittel: Sulfite',
			'preview_energy' => 'Energie',
			'preview_fat' => 'Fett',
			'preview_saturated' => 'davon gesättigte Fettsäuren',
			'preview_carbs' => 'Kohlenhydrate',
			'preview_sugars' => 'davon Zucker',
			'preview_protein' => 'Eiweiß',
			'preview_salt' => 'Salz',
			'preview_button' => 'Mehrsprachige Ansicht',
			'download' => 'Download',
			'view' => 'Ansehen',
			'notes' => 'Hinweise',
					'orphaned_badge' => 'Verwaist / Quelle prüfen',
					'orphaned_hint' => 'Keine aktuelle Verknüpfung zum Hauptplugin erkannt. Dieses Receiver-Label wird bei zentralen Updates möglicherweise nicht mehr mit aktualisiert.',
			'source_prefix' => 'Quelle:',
		),
		'en' => array(
			'plugin_name' => 'Wine E-Label Receiver',
			'plugin_description' => 'Receives wine e-label pages via REST, manages them centrally, and serves them without theme layout.',
			'overview' => 'Overview',
			'labels' => 'E-Labels',
			'design' => 'Adjust design',
			'setup' => 'Setup',
			'add_new' => 'Add new',
			'search' => 'Search',
			'status' => 'Status',
			'all_statuses' => 'All statuses',
			'language' => 'Language',
			'all_languages' => 'All languages',
			'filter' => 'Filter',
			'reset' => 'Reset',
			'title' => 'Title',
			'slug' => 'Slug',
			'year' => 'Vintage',
			'updated' => 'Updated',
			'created' => 'Created',
			'actions' => 'Actions',
			'open' => 'Open',
			'edit' => 'Edit',
			'download_html' => 'HTML',
			'download_json' => 'JSON',
			'trash' => 'Trash',
			'bulk_actions' => 'Bulk actions',
			'bulk_apply' => 'Apply',
			'bulk_trash' => 'Move to trash',
			'bulk_publish' => 'Publish',
			'bulk_draft' => 'Set draft',
			'export_csv' => 'Export CSV',
			'labels_intro' => 'Central management of all received E-labels on this receiver instance.',
			'label_delete_hint' => 'Note: E-labels should preferably be deleted on the main site so that the main plugin and receiver stay cleanly synchronized.',
			'reset_field' => 'Reset field',
			'no_labels' => 'No E-labels found.',
			'dashboard_intro' => 'This plugin receives E-labels via REST, manages them on the receiver site and outputs them without theme layout. The visual design is now maintained centrally in the main plugin.',
			'rest_base' => 'REST base',
			'current_url' => 'Current receiver URL',
			'total_labels' => 'Total E-labels',
			'published_labels' => 'Published',
			'drafts' => 'Drafts',
			'central_design_title' => 'Design management',
			'central_design_text' => 'Layout, colors, spacing, and other design values are no longer maintained on the receiver site but centrally in the main plugin.',
			'central_design_setup' => 'Make design changes in the main plugin under “Adjust design”. When saved, published receiver labels are synchronized automatically.',
			'design_intro' => 'Configure the base layout of received E-label pages centrally for this receiver instance.',
			'colors' => 'Colors',
			'sizes' => 'Spacing and sizing',
			'robustness' => 'Robustness',
			'live_preview' => 'Live preview',
			'load_defaults' => 'Load defaults',
			'page_bg' => 'Page background',
			'card_bg' => 'Card background',
			'table_head_bg' => 'Table header',
			'text_color' => 'Body text',
			'muted_color' => 'Muted text',
			'border_color' => 'Border',
			'base_font_size' => 'Base font size (px)',
			'small_font_size' => 'Small font size (px)',
			'button_font_size' => 'Button font size (px)',
			'font_family' => 'Font family',
			'panel_radius' => 'Panel radius (px)',
			'outer_width' => 'Outer width (px)',
			'label_width' => 'Label width (px)',
			'outer_padding_y' => 'Outer top/bottom spacing (px)',
			'card_padding' => 'Card padding (px)',
			'custom_css' => 'Additional CSS',
			'force_blank_layout' => 'Force blank plugin layout',
			'add_noindex' => 'Send noindex header',
			'disable_canonical' => 'Disable canonical redirect',
			'close_comments' => 'Close comments and pings',
			'normalize_body' => 'Neutralize body/html background',
			'inject_viewport' => 'Ensure viewport meta',
			'save_design' => 'Save design',
			'settings_saved' => 'Settings saved.',
			'bulk_done' => 'Bulk action completed.',
			'preview_hint' => 'The preview updates instantly while editing.',
			'font_system' => 'System Sans',
			'font_arial' => 'Arial / Helvetica',
			'font_georgia' => 'Georgia',
			'font_times' => 'Times New Roman',
			'font_verdana' => 'Verdana',
			'font_tahoma' => 'Tahoma',
			'setup_intro' => 'Set up the receiver connection to the main plugin. The E-label design is inherited centrally from the main plugin.',
			'setup_box_title' => 'How to connect the receiver site to the main plugin',
			'step_1' => 'On the receiver domain, create a user with administrator rights in the WordPress dashboard under “Users”, for example “api_elabel”, or use an existing administrator.',
			'step_2' => 'Open “Edit User” and create an application password in the user profile. The name of the application password can be chosen freely, for example “api_elabel”. Then generate it, copy it and store it safely.',
			'step_3' => 'Enter the receiver URL, the username and the application password in the main plugin.',
			'step_4' => 'In the main plugin, test whether the connection to the receiver instance works. Also verify that the REST API is reachable and not blocked by a firewall, a security plugin or an auth redirect.',
			'step_5' => 'The main plugin will then send the E-labels to this receiver instance and create an E-label page there.',
			'step_6' => 'Test the transfer with a sample product and verify that the E-label page was created correctly on the receiver site and can be opened.',
			'language_setting' => 'Plugin language',
			'save_language' => 'Save language',
			'setup_hint' => 'In the main plugin, enter only the receiver domain URL, not the full /wp-json/... address.',
			'design_page_removed_notice' => 'The former receiver design page has been removed. E-label design is now maintained centrally in the main plugin.',
			'setup_receiver_url' => 'Receiver URL for the main plugin',
			'setup_info_url' => 'Test API discovery',
			'setup_legacy_url' => 'Legacy fallback',
			'recommended' => 'recommended',
			'metabox_html' => 'E-label HTML',
			'metabox_lang' => 'Label language',
			'raw_html' => 'Raw HTML',
			'public_url' => 'Public URL',
			'location' => 'Location',
			'main_domain' => 'Main Domain',
			'subdomain' => 'Subdomain',
			'second_domain' => 'Receiver Domain',
			'metabox_note' => 'Full HTML documents are rendered as-is. Fragments are automatically wrapped in a minimal HTML document.',
			'unknown' => '—',
			'preview_title' => 'Rivaner dry 2024 Organic',
			'preview_subtitle' => 'Ingredients and nutrition declaration per 100 ml',
			'preview_ingredients' => 'Grapes, sucrose, concentrated grape must, acidity regulator: tartaric acid, antioxidant: sulfites',
			'preview_energy' => 'Energy',
			'preview_fat' => 'Fat',
			'preview_saturated' => 'of which saturates',
			'preview_carbs' => 'Carbohydrate',
			'preview_sugars' => 'of which sugars',
			'preview_protein' => 'Protein',
			'preview_salt' => 'Salt',
			'preview_button' => 'Multilingual view',
			'download' => 'Download',
			'view' => 'View',
			'notes' => 'Notes',
			'orphaned_badge' => 'Orphaned / check source',
			'orphaned_hint' => 'No current link to the main plugin was detected. This receiver label may no longer be updated by central changes.',
			'source_prefix' => 'Source:',
		),
		'fr' => array(
			'plugin_name' => 'Récepteur d’e-label vin',
			'plugin_description' => 'Reçoit les pages d’e-label vin via REST, les gère de manière centralisée et les affiche sans mise en page du thème.',
			'overview' => 'Aperçu',
			'labels' => 'E-labels',
			'design' => 'Adapter le design',
			'setup' => 'Configuration',
			'add_new' => 'Ajouter',
			'search' => 'Rechercher',
			'status' => 'Statut',
			'all_statuses' => 'Tous les statuts',
			'language' => 'Langue',
			'all_languages' => 'Toutes les langues',
			'filter' => 'Filtrer',
			'reset' => 'Réinitialiser',
			'title' => 'Titre',
			'slug' => 'Slug',
			'year' => 'Millésime',
			'updated' => 'Modifié',
			'created' => 'Créé',
			'actions' => 'Actions',
			'open' => 'Ouvrir',
			'edit' => 'Modifier',
			'download_html' => 'HTML',
			'download_json' => 'JSON',
			'trash' => 'Corbeille',
			'bulk_actions' => 'Actions groupées',
			'bulk_apply' => 'Appliquer',
			'bulk_trash' => 'Mettre à la corbeille',
			'bulk_publish' => 'Publier',
			'bulk_draft' => 'Mettre en brouillon',
			'export_csv' => 'Exporter CSV',
			'labels_intro' => 'Gestion centrale de tous les e-labels reçus sur cette instance réceptrice.',
			'label_delete_hint' => 'Remarque : les e-labels devraient de préférence être supprimés sur le site principal afin que le plugin principal et le récepteur restent correctement synchronisés.',
			'reset_field' => 'Réinitialiser le champ',
			'no_labels' => 'Aucun e-label trouvé.',
			'dashboard_intro' => 'Ce plugin reçoit des e-labels via REST, les gère sur le site récepteur et les affiche sans mise en page du thème. Le design visuel est désormais géré de manière centralisée dans le plugin principal.',
			'rest_base' => 'Base REST',
			'current_url' => 'URL actuelle du récepteur',
			'total_labels' => 'E-labels total',
			'published_labels' => 'Publiés',
			'drafts' => 'Brouillons',
			'central_design_title' => 'Gestion du design',
			'central_design_text' => 'La mise en page, les couleurs, les espacements et les autres valeurs de design ne sont plus gérés sur le site récepteur, mais de manière centralisée dans le plugin principal.',
			'central_design_setup' => 'Effectuez les modifications de design dans le plugin principal sous « Adapter le design ». Lors de l’enregistrement, les e-labels récepteurs publiés sont resynchronisés automatiquement.',
			'design_intro' => 'Ici, vous contrôlez la mise en page de base des pages e-label reçues pour toute l’instance réceptrice.',
			'colors' => 'Couleurs',
			'sizes' => 'Espacements et tailles',
			'robustness' => 'Robustesse',
			'live_preview' => 'Aperçu en direct',
			'load_defaults' => 'Charger les valeurs par défaut',
			'page_bg' => 'Arrière-plan de page',
			'card_bg' => 'Arrière-plan de carte',
			'table_head_bg' => 'En-tête de tableau',
			'text_color' => 'Texte',
			'muted_color' => 'Texte secondaire',
			'border_color' => 'Bordure',
			'base_font_size' => 'Taille de police de base (px)',
			'small_font_size' => 'Petite police (px)',
			'button_font_size' => 'Police du bouton (px)',
			'font_family' => 'Police',
			'panel_radius' => 'Rayon des panneaux (px)',
			'outer_width' => 'Largeur extérieure (px)',
			'label_width' => 'Largeur du label (px)',
			'outer_padding_y' => 'Espacement extérieur haut/bas (px)',
			'card_padding' => 'Marge intérieure carte (px)',
			'custom_css' => 'CSS supplémentaire',
			'force_blank_layout' => 'Forcer une mise en page vide du plugin',
			'add_noindex' => 'Envoyer l’en-tête noindex',
			'disable_canonical' => 'Désactiver la redirection canonique',
			'close_comments' => 'Fermer les commentaires et les pings',
			'normalize_body' => 'Neutraliser l’arrière-plan body/html',
			'inject_viewport' => 'Garantir la balise viewport',
			'save_design' => 'Enregistrer le design',
			'settings_saved' => 'Paramètres enregistrés.',
			'bulk_done' => 'Action groupée exécutée.',
			'preview_hint' => 'L’aperçu se met à jour immédiatement pendant l’édition.',
			'font_system' => 'System Sans',
			'font_arial' => 'Arial / Helvetica',
			'font_georgia' => 'Georgia',
			'font_times' => 'Times New Roman',
			'font_verdana' => 'Verdana',
			'font_tahoma' => 'Tahoma',
			'setup_intro' => 'Configurer la connexion entre le site récepteur et le plugin principal. Le design des e-labels est désormais hérité de façon centralisée depuis le plugin principal.',
			'setup_box_title' => 'Voici comment connecter le site récepteur au plugin principal',
			'step_1' => 'Sur le domaine récepteur, créez dans le tableau de bord WordPress sous « Utilisateurs » un utilisateur avec des droits d’administrateur, par exemple « api_elabel », ou utilisez un administrateur existant.',
			'step_2' => 'Sous « Modifier l’utilisateur », créez un mot de passe d’application dans le profil utilisateur. Le nom du mot de passe d’application peut être choisi librement, par exemple « api_elabel ». Ensuite, générez le mot de passe, copiez-le et enregistrez-le en lieu sûr.',
			'step_3' => 'Dans le plugin principal, saisissez l’URL du récepteur, le nom d’utilisateur et le mot de passe d’application.',
			'step_4' => 'Dans le plugin principal, vérifiez que la connexion à l’instance réceptrice fonctionne. Vérifiez également que l’API REST est accessible et qu’aucun pare-feu, plugin de sécurité ou redirection d’authentification ne bloque la requête.',
			'step_5' => 'Le plugin principal enverra alors les e-labels à cette instance réceptrice et y créera une page e-label.',
			'step_6' => 'Testez le transfert avec un produit de test et vérifiez que la page e-label a bien été créée sur le site récepteur et qu’elle est accessible.',
			'language_setting' => 'Langue du plugin',
			'save_language' => 'Enregistrer la langue',
			'setup_hint' => 'Dans le plugin principal, saisissez uniquement l’URL de domaine du récepteur, pas l’adresse complète /wp-json/... .',
			'design_page_removed_notice' => 'L’ancienne page de design du récepteur a été supprimée. Le design des e-labels est désormais géré de manière centralisée dans le plugin principal.',
			'setup_receiver_url' => 'URL du récepteur pour le plugin principal',
			'setup_info_url' => 'Tester la découverte de l’API',
			'setup_legacy_url' => 'Point de compatibilité ancien',
			'recommended' => 'recommandé',
			'metabox_html' => 'HTML E-label',
			'metabox_lang' => 'Langue du label',
			'raw_html' => 'HTML brut',
			'public_url' => 'URL publique',
			'location' => 'Emplacement',
			'main_domain' => 'Domaine principal',
			'subdomain' => 'Sous-domaine',
			'second_domain' => 'Receiver Domain',
			'metabox_note' => 'Les documents HTML complets sont affichés tels quels. Les fragments sont automatiquement intégrés dans un document HTML minimal.',
			'unknown' => '—',
			'preview_title' => 'Rivaner sec 2024 Bio',
			'preview_subtitle' => 'Ingrédients et déclaration nutritionnelle pour 100 ml',
			'preview_ingredients' => 'Raisins, saccharose, moût de raisin concentré, régulateur d’acidité : acide tartrique, antioxydant : sulfites',
			'preview_energy' => 'Énergie',
			'preview_fat' => 'Matières grasses',
			'preview_saturated' => 'dont acides gras saturés',
			'preview_carbs' => 'Glucides',
			'preview_sugars' => 'dont sucres',
			'preview_protein' => 'Protéines',
			'preview_salt' => 'Sel',
			'preview_button' => 'Vue multilingue',
			'download' => 'Télécharger',
			'view' => 'Voir',
			'notes' => 'Remarques',
			'orphaned_badge' => 'Orphelin / verifier la source',
			'orphaned_hint' => 'Aucun lien actuel avec le plugin principal n a ete detecte. Ce label du recepteur peut ne plus etre mis a jour par les changements centraux.',
			'source_prefix' => 'Source :',
		),
		'it' => array(
			'plugin_name' => 'Ricevitore e-label vino',
			'plugin_description' => 'Riceve le pagine e-label del vino tramite REST, le gestisce centralmente e le pubblica senza il layout del tema.',
			'overview' => 'Panoramica',
			'labels' => 'E-label',
			'design' => 'Personalizza design',
			'setup' => 'Configurazione',
			'add_new' => 'Aggiungi',
			'search' => 'Cerca',
			'status' => 'Stato',
			'all_statuses' => 'Tutti gli stati',
			'language' => 'Lingua',
			'all_languages' => 'Tutte le lingue',
			'filter' => 'Filtra',
			'reset' => 'Reimposta',
			'title' => 'Titolo',
			'slug' => 'Slug',
			'year' => 'Annata',
			'updated' => 'Modificato',
			'created' => 'Creato',
			'actions' => 'Azioni',
			'open' => 'Apri',
			'edit' => 'Modifica',
			'download_html' => 'HTML',
			'download_json' => 'JSON',
			'trash' => 'Cestino',
			'bulk_actions' => 'Azioni di massa',
			'bulk_apply' => 'Applica',
			'bulk_trash' => 'Sposta nel cestino',
			'bulk_publish' => 'Pubblica',
			'bulk_draft' => 'Bozza',
			'export_csv' => 'Esporta CSV',
			'labels_intro' => 'Gestione centrale di tutte le e-label ricevute su questa istanza di ricezione.',
			'label_delete_hint' => 'Nota: le e-label dovrebbero essere eliminate preferibilmente sul sito principale, così plugin principale e istanza di ricezione restano sincronizzati correttamente.',
			'reset_field' => 'Ripristina campo',
			'no_labels' => 'Nessuna e-label trovata.',
			'dashboard_intro' => 'Questo plugin riceve e-label via REST, le gestisce sul sito di ricezione e le visualizza senza il layout del tema. Il design visivo ora viene gestito centralmente nel plugin principale.',
			'rest_base' => 'Base REST',
			'current_url' => 'URL corrente del sito di ricezione',
			'total_labels' => 'E-label totali',
			'published_labels' => 'Pubblicate',
			'drafts' => 'Bozze',
			'central_design_title' => 'Gestione design',
			'central_design_text' => 'Layout, colori, spaziature e altri valori di design non vengono più gestiti sul sito di ricezione, ma centralmente nel plugin principale.',
			'central_design_setup' => 'Apporta le modifiche al design nel plugin principale alla voce “Design anpassen”. Al salvataggio, le e-label pubblicate sul receiver vengono sincronizzate automaticamente.',
			'design_intro' => 'Qui controlli centralmente il layout base delle pagine e-label ricevute per tutta l’istanza di ricezione.',
			'colors' => 'Colori',
			'sizes' => 'Spaziature e dimensioni',
			'robustness' => 'Robustezza',
			'live_preview' => 'Anteprima live',
			'load_defaults' => 'Carica predefiniti',
			'page_bg' => 'Sfondo pagina',
			'card_bg' => 'Sfondo scheda',
			'table_head_bg' => 'Intestazione tabella',
			'text_color' => 'Testo',
			'muted_color' => 'Testo secondario',
			'border_color' => 'Bordo',
			'base_font_size' => 'Dimensione font base (px)',
			'small_font_size' => 'Font piccolo (px)',
			'button_font_size' => 'Font pulsante (px)',
			'font_family' => 'Font',
			'panel_radius' => 'Raggio pannello (px)',
			'outer_width' => 'Larghezza esterna (px)',
			'label_width' => 'Larghezza label (px)',
			'outer_padding_y' => 'Spazio esterno sopra/sotto (px)',
			'card_padding' => 'Spazio interno scheda (px)',
			'custom_css' => 'CSS aggiuntivo',
			'force_blank_layout' => 'Forza layout plugin vuoto',
			'add_noindex' => 'Invia header noindex',
			'disable_canonical' => 'Disattiva redirect canonico',
			'close_comments' => 'Chiudi commenti e ping',
			'normalize_body' => 'Neutralizza sfondo body/html',
			'inject_viewport' => 'Assicura meta viewport',
			'save_design' => 'Salva design',
			'settings_saved' => 'Impostazioni salvate.',
			'bulk_done' => 'Azione di massa eseguita.',
			'preview_hint' => 'L’anteprima si aggiorna immediatamente durante le modifiche.',
			'font_system' => 'System Sans',
			'font_arial' => 'Arial / Helvetica',
			'font_georgia' => 'Georgia',
			'font_times' => 'Times New Roman',
			'font_verdana' => 'Verdana',
			'font_tahoma' => 'Tahoma',
			'setup_intro' => 'Configura il collegamento tra il sito di ricezione e il plugin principale. Il design delle e-label viene ora ereditato centralmente dal plugin principale.',
			'setup_box_title' => 'Come collegare il sito di ricezione al plugin principale',
			'step_1' => 'Nel dominio di ricezione, nel pannello WordPress alla voce « Utenti », crea un utente con privilegi di amministratore, ad esempio « api_elabel », oppure usa un amministratore già esistente.',
			'step_2' => 'In « Modifica utente », nel profilo utente, crea una password applicazione. Il nome della password applicazione può essere scelto liberamente, ad esempio « api_elabel ». Poi genera la password, copiala e salvala in un luogo sicuro.',
			'step_3' => 'Nel plugin principale inserisci l’URL del sito di ricezione, il nome utente e la password applicazione.',
			'step_4' => 'Nel plugin principale verifica che il collegamento con l’istanza di ricezione funzioni. Controlla inoltre che l’API REST sia raggiungibile e che nessun firewall, plugin di sicurezza o reindirizzamento di autenticazione blocchi la richiesta.',
			'step_5' => 'Il plugin principale invierà quindi le e-label a questa istanza di ricezione e vi creerà una pagina e-label.',
			'step_6' => 'Verifica il trasferimento con un prodotto di prova e controlla che la pagina e-label sia stata creata correttamente sul sito di ricezione e sia apribile.',
			'language_setting' => 'Lingua del plugin',
			'save_language' => 'Salva lingua',
			'setup_hint' => 'Nel plugin principale inserisci solo l’URL del dominio di ricezione, non l’indirizzo completo /wp-json/... .',
			'design_page_removed_notice' => 'La precedente pagina design del receiver è stata rimossa. Il design delle e-label viene ora gestito centralmente nel plugin principale.',
			'setup_receiver_url' => 'URL del sito di ricezione per il plugin principale',
			'setup_info_url' => 'Test della scoperta API',
			'setup_legacy_url' => 'Fallback di compatibilità',
			'recommended' => 'consigliato',
			'metabox_html' => 'HTML E-label',
			'metabox_lang' => 'Lingua label',
			'raw_html' => 'HTML grezzo',
			'public_url' => 'URL pubblica',
			'location' => 'Posizione',
			'main_domain' => 'Dominio principale',
			'subdomain' => 'Sottodominio',
			'second_domain' => 'Receiver Domain',
			'metabox_note' => 'I documenti HTML completi vengono mostrati così come sono. I frammenti vengono automaticamente inseriti in un documento HTML minimo.',
			'unknown' => '—',
			'preview_title' => 'Rivaner secco 2024 Bio',
			'preview_subtitle' => 'Ingredienti e dichiarazione nutrizionale per 100 ml',
			'preview_ingredients' => 'Uve, saccarosio, mosto d’uva concentrato, regolatore di acidità: acido tartarico, antiossidante: solfiti',
			'preview_energy' => 'Energia',
			'preview_fat' => 'Grassi',
			'preview_saturated' => 'di cui acidi grassi saturi',
			'preview_carbs' => 'Carboidrati',
			'preview_sugars' => 'di cui zuccheri',
			'preview_protein' => 'Proteine',
			'preview_salt' => 'Sale',
			'preview_button' => 'Vista multilingue',
			'download' => 'Scarica',
			'view' => 'Visualizza',
			'notes' => 'Note',
			'orphaned_badge' => 'Orfana / controlla sorgente',
			'orphaned_hint' => 'Non e stata rilevata una connessione attuale al plugin principale. Questa e-label receiver potrebbe non essere piu aggiornata dalle modifiche centrali.',
			'source_prefix' => 'Sorgente:',
		),
	);

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_admin_actions' ) );
		add_action( 'wp_ajax_relr_render_preview', array( __CLASS__, 'ajax_render_preview' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post_' . self::CPT, array( __CLASS__, 'save_meta_boxes' ) );
		add_action( 'template_redirect', array( __CLASS__, 'disable_public_caching' ), 0 );
		add_action( 'template_redirect', array( __CLASS__, 'render_frontend' ), 0 );
		add_filter( 'redirect_canonical', array( __CLASS__, 'maybe_disable_canonical_redirect' ), 10, 2 );
		add_filter( 'comments_open', array( __CLASS__, 'close_comments_on_labels' ), 10, 2 );
		add_filter( 'pings_open', array( __CLASS__, 'close_comments_on_labels' ), 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'plugin_action_links' ) );
		add_filter( 'all_plugins', array( __CLASS__, 'filter_all_plugins' ) );
	}

	public static function activate(): void {
		self::register_post_type();
		add_option( self::OPT_DESIGN, self::$defaults_design );
		add_option( self::OPT_UI, self::$defaults_ui );
		flush_rewrite_rules();
	}

	public static function deactivate(): void {
		flush_rewrite_rules();
	}

	public static function register_post_type(): void {
		register_post_type(
			self::CPT,
			array(
				'labels' => array(
					'name'          => self::tr( 'labels' ),
					'singular_name' => self::tr( 'labels' ),
					'add_new_item'  => self::tr( 'add_new' ),
					'edit_item'     => self::tr( 'edit' ),
				),
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => true,
				'has_archive'         => false,
				'rewrite'             => array(
					'slug'       => 'e-label',
					'with_front' => false,
				),
				'supports'            => array( 'title', 'slug' ),
				'menu_icon'           => 'dashicons-media-document',
				'show_in_rest'        => false,
			)
		);
	}

	public static function plugin_action_links( array $links ): array {
		$custom = array(
			'<a href="' . esc_url( admin_url( 'admin.php?page=relr-dashboard' ) ) . '">' . esc_html( self::tr( 'overview' ) ) . '</a>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=relr-setup' ) ) . '">' . esc_html( self::tr( 'setup' ) ) . '</a>',
		);
		return array_merge( $custom, $links );
	}

	public static function filter_all_plugins( array $plugins ): array {
		$basename = plugin_basename( __FILE__ );
		if ( isset( $plugins[ $basename ] ) && is_array( $plugins[ $basename ] ) ) {
			$plugins[ $basename ]['Name']        = self::tr( 'plugin_name' );
			$plugins[ $basename ]['Title']       = self::tr( 'plugin_name' );
			$plugins[ $basename ]['Description'] = self::tr( 'plugin_description' );
			$plugins[ $basename ]['AuthorName']  = 'Johannes Reith';
			$plugins[ $basename ]['Author']      = '<a href="https://reithwein.com">Johannes Reith</a>';
			$plugins[ $basename ]['PluginURI']   = 'https://reithwein.com';
			$plugins[ $basename ]['AuthorURI']   = 'https://reithwein.com';
		}
		return $plugins;
	}

	private static function ui_settings(): array {
		$ui = get_option( self::OPT_UI, array() );
		if ( ! is_array( $ui ) ) {
			$ui = array();
		}
		return wp_parse_args( $ui, self::$defaults_ui );
	}

	private static function design_settings(): array {
		$settings = get_option( self::OPT_DESIGN, array() );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}
		return wp_parse_args( $settings, self::$defaults_design );
	}

	private static function synced_design_settings( int $post_id ): array {
		$settings = self::design_settings();
		if ( $post_id <= 0 ) {
			return $settings;
		}

		$synced = get_post_meta( $post_id, self::META_DESIGN, true );
		if ( ! is_array( $synced ) || array() === $synced ) {
			return $settings;
		}

		return array_merge( $settings, self::sanitize_remote_design_settings( $synced ) );
	}

	private static function lang(): string {
		$ui   = self::ui_settings();
		$lang = isset( $ui['language'] ) ? strtolower( (string) $ui['language'] ) : 'de';
		return isset( self::$translations[ $lang ] ) ? $lang : 'de';
	}

	private static function tr( string $key ): string {
		$lang = self::lang();
		if ( isset( self::$translations[ $lang ][ $key ] ) ) {
			return self::$translations[ $lang ][ $key ];
		}
		return self::$translations['de'][ $key ] ?? $key;
	}

	private static function color_fields(): array {
		return array(
			'page_bg',
			'card_bg',
			'table_head_bg',
			'text_color',
			'muted_color',
			'border_color',
		);
	}

	private static function size_fields(): array {
		return array(
			'base_font_size',
			'small_font_size',
			'button_font_size',
			'font_family',
			'panel_radius',
			'outer_width',
			'label_width',
			'outer_padding_y',
			'card_padding',
			'custom_css',
		);
	}

	private static function robust_fields(): array {
		return array(
			'force_blank_layout',
			'add_noindex',
			'disable_canonical',
			'close_comments',
			'normalize_body',
			'inject_viewport',
		);
	}

	private static function recommended_robust_fields(): array {
		return array(
			'force_blank_layout',
			'add_noindex',
			'close_comments',
			'normalize_body',
			'inject_viewport',
		);
	}

	private static function robust_field_label( string $field ): string {
		$label = self::tr( $field );
		if ( in_array( $field, self::recommended_robust_fields(), true ) ) {
			$label .= ' (' . self::tr( 'recommended' ) . ')';
		}
		return $label;
	}

	public static function register_admin_menu(): void {
		$cap = 'edit_pages';

		add_menu_page(
			self::tr( 'plugin_name' ),
			self::tr( 'plugin_name' ),
			$cap,
			'relr-dashboard',
			array( __CLASS__, 'render_dashboard_page' ),
			'dashicons-media-document',
			58
		);

		add_submenu_page(
			'relr-dashboard',
			self::tr( 'overview' ),
			self::tr( 'overview' ),
			$cap,
			'relr-dashboard',
			array( __CLASS__, 'render_dashboard_page' )
		);

		add_submenu_page(
			'relr-dashboard',
			self::tr( 'labels' ),
			self::tr( 'labels' ),
			$cap,
			'relr-labels',
			array( __CLASS__, 'render_labels_page' )
		);

		add_submenu_page(
			'relr-dashboard',
			self::tr( 'setup' ),
			self::tr( 'setup' ),
			$cap,
			'relr-setup',
			array( __CLASS__, 'render_setup_page' )
		);
	}

	public static function handle_admin_actions(): void {
		if ( ! is_admin() || ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		self::maybe_cleanup_duplicate_plugin_dirs();

		$current_page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( 'relr-design' === $current_page ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'relr-setup', 'relr_notice' => 'design_managed' ), admin_url( 'admin.php' ) ) );
			exit;
		}

		if ( isset( $_POST['relr_save_design'] ) ) {
			check_admin_referer( 'relr_save_design' );
			$settings = self::sanitize_design_settings( $_POST );
			update_option( self::OPT_DESIGN, $settings );
			wp_safe_redirect( add_query_arg( array( 'page' => 'relr-design', 'relr_notice' => 'design_saved' ), admin_url( 'admin.php' ) ) );
			exit;
		}

		if ( isset( $_POST['relr_save_ui_language'] ) ) {
			check_admin_referer( 'relr_save_ui_language' );
			$lang = isset( $_POST['relr_ui_language'] ) ? strtolower( sanitize_text_field( wp_unslash( $_POST['relr_ui_language'] ) ) ) : 'de';
			if ( ! isset( self::$translations[ $lang ] ) ) {
				$lang = 'de';
			}
			update_option( self::OPT_UI, array( 'language' => $lang ) );
			wp_safe_redirect( add_query_arg( array( 'page' => 'relr-setup', 'relr_notice' => 'language_saved' ), admin_url( 'admin.php' ) ) );
			exit;
		}

		if ( isset( $_POST['relr_bulk_action'] ) ) {
			check_admin_referer( 'relr_bulk_action' );
			$action = sanitize_text_field( wp_unslash( $_POST['relr_bulk_action'] ) );
			$ids    = isset( $_POST['relr_ids'] ) ? array_map( 'intval', (array) wp_unslash( $_POST['relr_ids'] ) ) : array();
			$ids    = array_filter( $ids );
			if ( $ids && in_array( $action, array( 'trash', 'publish', 'draft' ), true ) ) {
				foreach ( $ids as $id ) {
					if ( self::CPT !== get_post_type( $id ) ) {
						continue;
					}
					if ( 'trash' === $action ) {
						wp_trash_post( $id );
					} else {
						wp_update_post(
							array(
								'ID'          => $id,
								'post_status' => $action,
							)
						);
					}
				}
			}
			wp_safe_redirect( add_query_arg( array( 'page' => 'relr-labels', 'relr_notice' => 'bulk_done' ), admin_url( 'admin.php' ) ) );
			exit;
		}

		if ( isset( $_GET['relr_download'], $_GET['label_id'] ) ) {
			$format  = sanitize_text_field( wp_unslash( $_GET['relr_download'] ) );
			$label_id = (int) $_GET['label_id'];
			check_admin_referer( 'relr_download_' . $label_id . '_' . $format );
			if ( self::CPT === get_post_type( $label_id ) ) {
				self::download_label( $label_id, $format );
			}
			exit;
		}

		if ( isset( $_GET['relr_export_csv'] ) ) {
			check_admin_referer( 'relr_export_csv' );
			self::download_csv();
			exit;
		}
	}

	private static function maybe_cleanup_duplicate_plugin_dirs(): void {
		if ( empty( $_GET['relr_cleanup_plugins'] ) || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( ! empty( $_GET['_wpnonce'] ) ) {
			check_admin_referer( 'relr_cleanup_plugins' );
		}

		$raw_dirs = isset( $_GET['dirs'] ) ? (string) wp_unslash( $_GET['dirs'] ) : '';
		$dirs     = array_filter(
			array_map(
				static function ( string $dir ): string {
					return sanitize_title( $dir );
				},
				explode( ',', $raw_dirs )
			)
		);

		if ( empty( $dirs ) ) {
			wp_safe_redirect( admin_url( 'plugins.php?plugin_status=all' ) );
			exit;
		}

		$current_dir = basename( plugin_dir_path( __FILE__ ) );
		foreach ( array_unique( $dirs ) as $dir ) {
			if ( $dir === $current_dir || 0 !== strpos( $dir, 'wine-e-label-receiver' ) ) {
				continue;
			}

			$target = trailingslashit( WP_PLUGIN_DIR ) . $dir;
			if ( ! is_dir( $target ) ) {
				continue;
			}

			self::delete_directory_recursively( $target );
		}

		wp_safe_redirect( admin_url( 'plugins.php?plugin_status=all' ) );
		exit;
	}

	private static function delete_directory_recursively( string $directory ): void {
		$directory  = wp_normalize_path( $directory );
		$plugins_dir = wp_normalize_path( WP_PLUGIN_DIR );

		if ( '' === $directory || $directory === $plugins_dir || 0 !== strpos( $directory, $plugins_dir . '/' ) || ! is_dir( $directory ) ) {
			return;
		}

		$items = scandir( $directory );
		if ( ! is_array( $items ) ) {
			return;
		}

		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item ) {
				continue;
			}

			$path = $directory . '/' . $item;
			if ( is_dir( $path ) ) {
				self::delete_directory_recursively( $path );
			} elseif ( file_exists( $path ) ) {
				@unlink( $path );
			}
		}

		@rmdir( $directory );
	}

	private static function sanitize_design_settings( array $input ): array {
		$old = self::design_settings();

		foreach ( self::color_fields() as $field ) {
			$old[ $field ] = isset( $input[ $field ] ) ? self::sanitize_hex_or_default( (string) wp_unslash( $input[ $field ] ), self::$defaults_design[ $field ] ) : self::$defaults_design[ $field ];
		}

		$limits = array(
			'base_font_size'   => array( 10, 24 ),
			'small_font_size'  => array( 9, 20 ),
			'button_font_size' => array( 10, 22 ),
			'panel_radius'     => array( 0, 48 ),
			'outer_width'      => array( 480, 1600 ),
			'label_width'      => array( 320, 1200 ),
			'outer_padding_y'  => array( 0, 120 ),
			'card_padding'     => array( 0, 80 ),
			'logo_max_height'  => array( 40, 240 ),
			'product_image_max_height' => array( 60, 360 ),
			'wine_name_size'   => array( 14, 48 ),
			'vintage_size'     => array( 12, 32 ),
			'subtitle_size'    => array( 12, 36 ),
		);

		foreach ( $limits as $field => $range ) {
			$value         = isset( $input[ $field ] ) ? (int) wp_unslash( $input[ $field ] ) : self::$defaults_design[ $field ];
			$old[ $field ] = max( (int) $range[0], min( (int) $range[1], $value ) );
		}

		$old['font_family']  = isset( $input['font_family'] ) ? self::sanitize_font_family( (string) wp_unslash( $input['font_family'] ) ) : self::$defaults_design['font_family'];
		$old['custom_css']   = isset( $input['custom_css'] ) ? trim( (string) wp_unslash( $input['custom_css'] ) ) : '';

		foreach ( self::robust_fields() as $field ) {
			$old[ $field ] = isset( $input[ $field ] ) ? 1 : 0;
		}

		return $old;
	}

	private static function sanitize_remote_design_settings( array $input ): array {
		$settings = array();

		foreach ( self::color_fields() as $field ) {
			$settings[ $field ] = isset( $input[ $field ] ) ? self::sanitize_hex_or_default( (string) $input[ $field ], self::$defaults_design[ $field ] ) : self::$defaults_design[ $field ];
		}

		$limits = array(
			'base_font_size'   => array( 10, 24 ),
			'small_font_size'  => array( 9, 20 ),
			'button_font_size' => array( 10, 22 ),
			'panel_radius'     => array( 0, 48 ),
			'outer_width'      => array( 480, 1600 ),
			'label_width'      => array( 320, 1200 ),
			'outer_padding_y'  => array( 0, 120 ),
			'card_padding'     => array( 0, 80 ),
		);

		foreach ( $limits as $field => $range ) {
			$value              = isset( $input[ $field ] ) ? (int) $input[ $field ] : self::$defaults_design[ $field ];
			$settings[ $field ] = max( (int) $range[0], min( (int) $range[1], $value ) );
		}

		$settings['font_family'] = isset( $input['font_family'] ) ? self::sanitize_font_family( (string) $input['font_family'] ) : self::$defaults_design['font_family'];
		$settings['logo_enabled'] = ( isset( $input['logo_enabled'] ) && '1' === (string) $input['logo_enabled'] ) ? '1' : '0';
		$settings['product_image_enabled'] = ( isset( $input['product_image_enabled'] ) && '1' === (string) $input['product_image_enabled'] ) ? '1' : '0';
		$settings['wine_name_enabled'] = ( isset( $input['wine_name_enabled'] ) && '1' === (string) $input['wine_name_enabled'] ) ? '1' : '0';
		$settings['vintage_enabled'] = ( isset( $input['vintage_enabled'] ) && '1' === (string) $input['vintage_enabled'] ) ? '1' : '0';
		$settings['subtitle_enabled'] = ( isset( $input['subtitle_enabled'] ) && '1' === (string) $input['subtitle_enabled'] ) ? '1' : '0';
		$settings['logo_url']     = isset( $input['logo_url'] ) ? esc_url_raw( (string) $input['logo_url'] ) : '';
		$settings['logo_alt']     = isset( $input['logo_alt'] ) ? sanitize_text_field( (string) $input['logo_alt'] ) : '';
		$settings['producer_region'] = isset( $input['producer_region'] ) ? sanitize_text_field( (string) $input['producer_region'] ) : '';
		$settings['producer_country'] = isset( $input['producer_country'] ) ? sanitize_text_field( (string) $input['producer_country'] ) : '';
		$settings['producer_address'] = isset( $input['producer_address'] ) ? sanitize_textarea_field( (string) $input['producer_address'] ) : '';
		$settings['custom_css']  = isset( $input['custom_css'] ) ? trim( (string) $input['custom_css'] ) : '';

		return $settings;
	}

	private static function sanitize_hex_or_default( string $value, string $default ): string {
		$value = trim( $value );
		if ( preg_match( '/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $value ) ) {
			return strtolower( $value );
		}
		return $default;
	}

	private static function sanitize_font_family( string $font ): string {
		$allowed = self::font_options();
		return isset( $allowed[ $font ] ) ? $font : self::$defaults_design['font_family'];
	}

	private static function sanitize_source_meta( $input ): array {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$source = array(
			'type'       => isset( $input['type'] ) ? sanitize_key( (string) $input['type'] ) : '',
			'product_id' => isset( $input['product_id'] ) ? absint( $input['product_id'] ) : 0,
			'site'       => isset( $input['site'] ) ? esc_url_raw( (string) $input['site'] ) : '',
			'updated_at' => isset( $input['updated_at'] ) ? sanitize_text_field( (string) $input['updated_at'] ) : '',
			'targets'    => isset( $input['targets'] ) ? self::sanitize_source_targets( $input['targets'] ) : array(),
		);

		if ( '' === $source['type'] && 0 === $source['product_id'] && '' === $source['site'] && '' === $source['updated_at'] && empty( $source['targets'] ) ) {
			return array();
		}

		return $source;
	}

	private static function sanitize_source_targets( $input ): array {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$targets = array();
		$seen    = array();

		foreach ( $input as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$url = isset( $item['url'] ) ? esc_url_raw( (string) $item['url'] ) : '';
			if ( '' === $url ) {
				continue;
			}

			if ( isset( $seen[ $url ] ) ) {
				continue;
			}

			$kind           = isset( $item['kind'] ) ? sanitize_key( (string) $item['kind'] ) : '';
			$location_label = isset( $item['location_label'] ) ? sanitize_text_field( (string) $item['location_label'] ) : self::target_location_label( $kind );
			$host           = isset( $item['host'] ) ? sanitize_text_field( (string) $item['host'] ) : (string) wp_parse_url( $url, PHP_URL_HOST );
			$display_name   = isset( $item['display_name'] ) ? sanitize_text_field( (string) $item['display_name'] ) : trim( $location_label . ( $host ? ' - ' . $host : '' ) );

			$seen[ $url ] = true;
			$targets[] = array(
				'kind'           => $kind,
				'location_label' => $location_label,
				'host'           => $host,
				'display_name'   => $display_name,
				'url'            => $url,
				'is_primary'     => ! empty( $item['is_primary'] ),
			);
		}

		return $targets;
	}

	private static function source_meta_for_post( int $post_id ): array {
		return self::sanitize_source_meta( get_post_meta( $post_id, self::META_SOURCE, true ) );
	}

	private static function source_targets( array $source ): array {
		return self::sanitize_source_targets( $source['targets'] ?? array() );
	}

	private static function is_orphaned_label( int $post_id ): bool {
		$source = self::source_meta_for_post( $post_id );
		return empty( $source['type'] ) || 'main_plugin' !== $source['type'] || empty( $source['site'] ) || empty( $source['product_id'] );
	}

	private static function source_summary( array $source ): string {
		if ( array() === $source ) {
			return '';
		}

		$parts = array();
		if ( ! empty( $source['site'] ) ) {
			$parts[] = untrailingslashit( (string) $source['site'] );
		}
		if ( ! empty( $source['product_id'] ) ) {
			$parts[] = '#' . (int) $source['product_id'];
		}

		return implode( ' ', $parts );
	}

	private static function target_location_label( string $kind ): string {
		switch ( sanitize_key( $kind ) ) {
			case 'main':
				return self::tr( 'main_domain' );
			case 'subdomain':
				return self::tr( 'subdomain' );
			case 'receiver':
				return self::tr( 'second_domain' );
			default:
				return self::tr( 'unknown' );
		}
	}

	private static function font_options(): array {
		return array(
			'system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif' => self::tr( 'font_system' ),
			'Arial,Helvetica,sans-serif'                                      => self::tr( 'font_arial' ),
			'Georgia,"Times New Roman",serif'                                 => self::tr( 'font_georgia' ),
			'"Times New Roman",Times,serif'                                   => self::tr( 'font_times' ),
			'Verdana,Geneva,sans-serif'                                       => self::tr( 'font_verdana' ),
			'Tahoma,Geneva,sans-serif'                                        => self::tr( 'font_tahoma' ),
		);
	}

	public static function register_rest_routes(): void {
		foreach ( self::route_namespaces() as $namespace ) {
			register_rest_route(
				$namespace,
				'/info',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( __CLASS__, 'rest_info' ),
					'permission_callback' => array( __CLASS__, 'rest_permissions' ),
				)
			);

			register_rest_route(
				$namespace,
				'/labels',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'rest_upsert_label' ),
					'permission_callback' => array( __CLASS__, 'rest_permissions' ),
					'args'                => array(
						'slug'   => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_title',
						),
						'title'  => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'html'   => array(
							'required' => true,
							'type'     => 'string',
						),
						'status' => array(
							'required' => false,
							'type'     => 'string',
						),
						'lang'   => array(
							'required' => false,
							'type'     => 'string',
						),
						'design' => array(
							'required' => false,
							'type'     => 'object',
						),
					),
				)
			);

			register_rest_route(
				$namespace,
				'/labels/(?P<slug>[a-z0-9-]+)',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( __CLASS__, 'rest_get_label' ),
						'permission_callback' => array( __CLASS__, 'rest_permissions' ),
					),
					array(
						'methods'             => WP_REST_Server::DELETABLE,
						'callback'            => array( __CLASS__, 'rest_delete_label' ),
						'permission_callback' => array( __CLASS__, 'rest_permissions' ),
					),
				)
			);
		}
	}

	private static function route_namespaces(): array {
		return array(
			self::ROUTE_NAMESPACE_V2,
			self::ROUTE_NAMESPACE_V1,
		);
	}

	private static function primary_route_namespace(): string {
		return self::ROUTE_NAMESPACE_V2;
	}

	private static function route_templates( string $namespace ): array {
		return array(
			'info'   => '/wp-json/' . $namespace . '/info',
			'create' => '/wp-json/' . $namespace . '/labels',
			'item'   => '/wp-json/' . $namespace . '/labels/{slug}',
		);
	}

	public static function rest_info( WP_REST_Request $request ) {
		$namespace = (string) $request->get_route();
		$namespace = preg_match( '#^/([^/]+/v\d+)/info$#', $namespace, $m ) ? $m[1] : self::primary_route_namespace();
		$routes    = self::route_templates( $namespace );

		return new WP_REST_Response(
			array(
				'plugin'         => 'wine-e-label-receiver-reith',
				'plugin_version' => self::VERSION,
				'namespace'      => $namespace,
				'routes'         => array(
					'info'   => $routes['info'],
					'create' => $routes['create'],
					'item'   => $routes['item'],
				),
				'features'       => array(
					'supports_delete'           => true,
					'supports_html_export'      => true,
					'supports_json_export'      => true,
					'supports_design_overrides' => true,
					'supports_discovery'        => true,
				),
			)
		);
	}

	public static function rest_permissions( WP_REST_Request $request ): bool {
		unset( $request );
		return current_user_can( 'edit_pages' );
	}

	public static function rest_upsert_label( WP_REST_Request $request ) {
		$slug = sanitize_title( (string) $request->get_param( 'slug' ) );
		if ( '' === $slug ) {
			return new WP_Error( 'relr_empty_slug', 'Missing or invalid slug.', array( 'status' => 400 ) );
		}

		$title = sanitize_text_field( (string) $request->get_param( 'title' ) );
		$html  = $request->get_param( 'html' );
		$lang  = sanitize_text_field( (string) $request->get_param( 'lang' ) );
		$design = $request->get_param( 'design' );
		$source = $request->get_param( 'source' );

		if ( ! is_string( $html ) || '' === trim( $html ) ) {
			return new WP_Error( 'relr_empty_html', 'Missing HTML payload.', array( 'status' => 400 ) );
		}

		$status      = self::normalize_status( (string) $request->get_param( 'status' ) );
		$source_meta = self::sanitize_source_meta( $source );
		$post_id     = self::find_post_id_by_source( $source_meta );
		if ( $post_id <= 0 ) {
			$post_id = self::find_post_id_by_slug( $slug );
		}
		$created = false;

		$postarr = array(
			'post_type'   => self::CPT,
			'post_status' => $status,
			'post_title'  => $title,
			'post_name'   => $slug,
		);

		if ( $post_id > 0 ) {
			$postarr['ID'] = $post_id;
			$result        = wp_update_post( $postarr, true );
		} else {
			$result  = wp_insert_post( $postarr, true );
			$created = true;
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$post_id = (int) $result;
		update_post_meta( $post_id, self::META_HTML, wp_unslash( $html ) );
		update_post_meta( $post_id, self::META_LANG, $lang );
		if ( is_array( $design ) ) {
			update_post_meta( $post_id, self::META_DESIGN, self::sanitize_remote_design_settings( $design ) );
		} else {
			delete_post_meta( $post_id, self::META_DESIGN );
		}
		if ( array() !== $source_meta ) {
			update_post_meta( $post_id, self::META_SOURCE, $source_meta );
		} else {
			delete_post_meta( $post_id, self::META_SOURCE );
		}

		if ( self::design_settings()['close_comments'] ) {
			wp_update_post(
				array(
					'ID'             => $post_id,
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				)
			);
		}

		return new WP_REST_Response(
			array(
				'id'      => $post_id,
				'slug'    => get_post_field( 'post_name', $post_id ),
				'title'   => get_the_title( $post_id ),
				'status'  => get_post_status( $post_id ),
				'url'     => get_permalink( $post_id ),
				'created' => $created,
			),
			$created ? 201 : 200
		);
	}

	public static function rest_get_label( WP_REST_Request $request ) {
		$post_id = self::find_post_id_by_slug( sanitize_title( (string) $request['slug'] ) );
		if ( $post_id <= 0 ) {
			return new WP_Error( 'relr_not_found', 'E-label not found.', array( 'status' => 404 ) );
		}

		return new WP_REST_Response(
			array(
				'id'     => $post_id,
				'slug'   => get_post_field( 'post_name', $post_id ),
				'title'  => get_the_title( $post_id ),
				'status' => get_post_status( $post_id ),
				'url'    => get_permalink( $post_id ),
				'lang'   => (string) get_post_meta( $post_id, self::META_LANG, true ),
				'html'   => (string) get_post_meta( $post_id, self::META_HTML, true ),
				'design' => self::sanitize_remote_design_settings( (array) get_post_meta( $post_id, self::META_DESIGN, true ) ),
				'source' => self::source_meta_for_post( $post_id ),
			)
		);
	}

	public static function rest_delete_label( WP_REST_Request $request ) {
		$post_id = self::find_post_id_by_slug( sanitize_title( (string) $request['slug'] ) );
		if ( $post_id <= 0 ) {
			return new WP_Error( 'relr_not_found', 'E-label not found.', array( 'status' => 404 ) );
		}
		$deleted = wp_trash_post( $post_id );
		if ( ! $deleted ) {
			return new WP_Error( 'relr_delete_failed', 'E-Label konnte nicht gelöscht werden.', array( 'status' => 500 ) );
		}
		return new WP_REST_Response(
			array(
				'deleted' => true,
				'slug'    => sanitize_title( (string) $request['slug'] ),
			)
		);
	}

	public static function add_meta_boxes(): void {
		add_meta_box(
			'relr_html_box',
			self::tr( 'metabox_html' ),
			array( __CLASS__, 'render_meta_box' ),
			self::CPT,
			'normal',
			'high'
		);
	}

	public static function render_meta_box( WP_Post $post ): void {
		wp_nonce_field( self::NONCE, self::NONCE );
		$html = (string) get_post_meta( $post->ID, self::META_HTML, true );
		$lang = (string) get_post_meta( $post->ID, self::META_LANG, true );
		$url  = get_permalink( $post );
		?>
		<p>
			<label for="relr_lang"><strong><?php echo esc_html( self::tr( 'metabox_lang' ) ); ?></strong></label><br>
			<input type="text" id="relr_lang" name="relr_lang" value="<?php echo esc_attr( $lang ); ?>" placeholder="de" class="regular-text">
		</p>
		<p>
			<label for="relr_html"><strong><?php echo esc_html( self::tr( 'raw_html' ) ); ?></strong></label>
		</p>
		<textarea id="relr_html" name="relr_html" rows="22" style="width:100%;font-family:monospace;"><?php echo esc_textarea( $html ); ?></textarea>
		<?php if ( $url ) : ?>
			<p>
				<strong><?php echo esc_html( self::tr( 'public_url' ) ); ?>:</strong>
				<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $url ); ?></a>
			</p>
		<?php endif; ?>
		<p><?php echo esc_html( self::tr( 'metabox_note' ) ); ?></p>
		<?php
	}

	public static function save_meta_boxes( int $post_id ): void {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE ] ) ), self::NONCE ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$html = isset( $_POST['relr_html'] ) ? wp_unslash( $_POST['relr_html'] ) : '';
		$lang = isset( $_POST['relr_lang'] ) ? sanitize_text_field( wp_unslash( $_POST['relr_lang'] ) ) : '';

		update_post_meta( $post_id, self::META_HTML, $html );
		update_post_meta( $post_id, self::META_LANG, $lang );

		if ( self::design_settings()['close_comments'] ) {
			wp_update_post(
				array(
					'ID'             => $post_id,
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				)
			);
		}
	}

	public static function render_dashboard_page(): void {
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		$counts = wp_count_posts( self::CPT );
		$rest   = rest_url( self::primary_route_namespace() . '/labels' );
		?>
		<div class="wrap relr-wrap">
			<h1><?php echo esc_html( self::tr( 'plugin_name' ) ); ?></h1>
			<p><?php echo esc_html( self::tr( 'dashboard_intro' ) ); ?></p>

			<?php self::render_notice(); ?>

			<div class="postbox" style="padding:18px;max-width:980px;margin-top:18px;">
				<h2 style="margin-top:0;"><?php echo esc_html( self::tr( 'central_design_title' ) ); ?></h2>
				<p style="margin:0;"><?php echo esc_html( self::tr( 'central_design_text' ) ); ?></p>
			</div>

			<div style="display:grid;grid-template-columns:repeat(3,minmax(180px,1fr));gap:16px;max-width:980px;margin-top:18px;">
				<?php self::stat_card( self::tr( 'total_labels' ), (int) ( $counts->publish + $counts->draft + $counts->private ) ); ?>
				<?php self::stat_card( self::tr( 'published_labels' ), (int) $counts->publish ); ?>
				<?php self::stat_card( self::tr( 'drafts' ), (int) $counts->draft ); ?>
			</div>

			<div style="display:grid;grid-template-columns:minmax(280px,520px) minmax(280px,520px);gap:18px;max-width:1080px;margin-top:22px;">
				<div class="postbox" style="padding:18px;">
					<h2 style="margin-top:0;"><?php echo esc_html( self::tr( 'rest_base' ) ); ?></h2>
					<code style="display:block;padding:10px 12px;border:1px solid #dcdcde;background:#f6f7f7;"><?php echo esc_html( $rest ); ?></code>
				</div>
				<div class="postbox" style="padding:18px;">
					<h2 style="margin-top:0;"><?php echo esc_html( self::tr( 'current_url' ) ); ?></h2>
					<code style="display:block;padding:10px 12px;border:1px solid #dcdcde;background:#f6f7f7;"><?php echo esc_html( home_url( '/' ) ); ?></code>
				</div>
			</div>
		</div>
		<?php
	}

	private static function stat_card( string $label, int $value ): void {
		?>
		<div class="postbox" style="padding:18px;">
			<div style="font-size:12px;color:#646970;text-transform:uppercase;letter-spacing:.04em;"><?php echo esc_html( $label ); ?></div>
			<div style="font-size:28px;font-weight:700;line-height:1.2;margin-top:8px;"><?php echo esc_html( (string) $value ); ?></div>
		</div>
		<?php
	}

	public static function render_labels_page(): void {
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$status = isset( $_GET['status_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['status_filter'] ) ) : '';
		$lang   = isset( $_GET['lang_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['lang_filter'] ) ) : '';

		$args = array(
			'post_type'      => self::CPT,
			'post_status'    => 'any',
			'posts_per_page' => 100,
			'orderby'        => 'modified',
			'order'          => 'DESC',
			's'              => $search,
		);

		if ( in_array( $status, array( 'publish', 'draft', 'private', 'trash' ), true ) ) {
			$args['post_status'] = $status;
		}

		if ( '' !== $lang ) {
			$args['meta_query'] = array(
				array(
					'key'   => self::META_LANG,
					'value' => $lang,
				),
			);
		}

		$query = new WP_Query( $args );
		?>
		<div class="wrap relr-wrap">
			<h1 style="display:flex;align-items:center;gap:12px;justify-content:space-between;">
				<span><?php echo esc_html( self::tr( 'labels' ) ); ?></span>
				<a class="page-title-action" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . self::CPT ) ); ?>"><?php echo esc_html( self::tr( 'add_new' ) ); ?></a>
			</h1>
			<p><?php echo esc_html( self::tr( 'labels_intro' ) ); ?></p>
			<p class="notice-inline description" style="max-width:980px;margin:10px 0 16px;padding:10px 12px;border-left:4px solid #2271b1;background:#fff;"><?php echo esc_html( self::tr( 'label_delete_hint' ) ); ?></p>
			<?php self::render_notice(); ?>

			<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="margin:16px 0;">
				<input type="hidden" name="page" value="relr-labels">
				<div style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
					<div>
						<label style="display:block;font-weight:600;margin-bottom:6px;"><?php echo esc_html( self::tr( 'search' ) ); ?></label>
						<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" class="regular-text">
					</div>
					<div>
						<label style="display:block;font-weight:600;margin-bottom:6px;"><?php echo esc_html( self::tr( 'status' ) ); ?></label>
						<select name="status_filter">
							<option value=""><?php echo esc_html( self::tr( 'all_statuses' ) ); ?></option>
							<option value="publish" <?php selected( $status, 'publish' ); ?>>publish</option>
							<option value="draft" <?php selected( $status, 'draft' ); ?>>draft</option>
							<option value="private" <?php selected( $status, 'private' ); ?>>private</option>
							<option value="trash" <?php selected( $status, 'trash' ); ?>>trash</option>
						</select>
					</div>
					<div>
						<label style="display:block;font-weight:600;margin-bottom:6px;"><?php echo esc_html( self::tr( 'language' ) ); ?></label>
						<select name="lang_filter">
							<option value=""><?php echo esc_html( self::tr( 'all_languages' ) ); ?></option>
							<option value="de" <?php selected( $lang, 'de' ); ?>>de</option>
							<option value="en" <?php selected( $lang, 'en' ); ?>>en</option>
							<option value="fr" <?php selected( $lang, 'fr' ); ?>>fr</option>
							<option value="it" <?php selected( $lang, 'it' ); ?>>it</option>
						</select>
					</div>
					<div>
						<button class="button button-primary"><?php echo esc_html( self::tr( 'filter' ) ); ?></button>
					</div>
					<div>
						<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=relr-labels' ) ); ?>"><?php echo esc_html( self::tr( 'reset' ) ); ?></a>
					</div>
					<div>
						<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=relr-labels&relr_export_csv=1' ), 'relr_export_csv' ) ); ?>"><?php echo esc_html( self::tr( 'export_csv' ) ); ?></a>
					</div>
				</div>
			</form>

			<form method="post">
				<?php wp_nonce_field( 'relr_bulk_action' ); ?>
				<div style="display:flex;gap:12px;align-items:center;margin-bottom:12px;">
					<select name="relr_bulk_action">
						<option value=""><?php echo esc_html( self::tr( 'bulk_actions' ) ); ?></option>
						<option value="publish"><?php echo esc_html( self::tr( 'bulk_publish' ) ); ?></option>
						<option value="draft"><?php echo esc_html( self::tr( 'bulk_draft' ) ); ?></option>
						<option value="trash"><?php echo esc_html( self::tr( 'bulk_trash' ) ); ?></option>
					</select>
					<button class="button"><?php echo esc_html( self::tr( 'bulk_apply' ) ); ?></button>
				</div>

				<table class="widefat striped" style="max-width:100%;">
					<thead>
						<tr>
							<td style="width:32px;"><input type="checkbox" id="relr-check-all"></td>
							<th><?php echo esc_html( self::tr( 'title' ) ); ?></th>
							<th><?php echo esc_html( self::tr( 'public_url' ) ); ?></th>
							<th><?php echo esc_html( self::tr( 'location' ) ); ?></th>
							<th><?php echo esc_html( self::tr( 'slug' ) ); ?></th>
							<th><?php echo esc_html( self::tr( 'year' ) ); ?></th>
							<th><?php echo esc_html( self::tr( 'language' ) ); ?></th>
							<th><?php echo esc_html( self::tr( 'status' ) ); ?></th>
							<th><?php echo esc_html( self::tr( 'updated' ) ); ?></th>
							<th><?php echo esc_html( self::tr( 'actions' ) ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php if ( $query->have_posts() ) : ?>
						<?php while ( $query->have_posts() ) : $query->the_post(); ?>
							<?php
							$id         = get_the_ID();
							$post       = get_post( $id );
							$lang_value = (string) get_post_meta( $id, self::META_LANG, true );
							$year       = self::extract_year( (string) get_the_title( $id ) );
							$source     = self::source_meta_for_post( $id );
							$is_orphan  = self::is_orphaned_label( $id );
							$source_text = self::source_summary( $source );
							$source_targets = self::source_targets( $source );
							$public_url = get_permalink( $id );
							$public_host = (string) wp_parse_url( $public_url, PHP_URL_HOST );
							?>
							<tr>
								<td><input type="checkbox" name="relr_ids[]" value="<?php echo esc_attr( (string) $id ); ?>"></td>
								<td>
									<strong><?php echo esc_html( get_the_title( $id ) ); ?></strong>
									<?php if ( $is_orphan ) : ?>
										<div style="margin-top:6px;"><span style="display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;background:#fff4e5;color:#8a5a00;font-size:12px;font-weight:600;"><?php echo esc_html( self::tr( 'orphaned_badge' ) ); ?></span></div>
										<div style="margin-top:6px;color:#646970;font-size:12px;line-height:1.45;"><?php echo esc_html( self::tr( 'orphaned_hint' ) ); ?></div>
									<?php elseif ( '' !== $source_text ) : ?>
										<div style="margin-top:6px;color:#646970;font-size:12px;line-height:1.45;"><?php echo esc_html( self::tr( 'source_prefix' ) . ' ' . $source_text ); ?></div>
									<?php endif; ?>
									<?php if ( ! empty( $source_targets ) ) : ?>
										<div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap;">
											<?php foreach ( $source_targets as $target ) : ?>
												<a
													href="<?php echo esc_url( (string) $target['url'] ); ?>"
													target="_blank"
													rel="noopener"
													style="display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;background:#f6f7f7;color:#1d2327;font-size:12px;text-decoration:none;">
													<?php echo esc_html( (string) ( $target['display_name'] ?: $target['location_label'] ) ); ?>
												</a>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</td>
								<td>
									<a href="<?php echo esc_url( $public_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( preg_replace( '#^https?://#', '', (string) $public_url ) ); ?></a>
								</td>
								<td>
									<div><?php echo esc_html( self::target_location_label( 'receiver' ) ); ?></div>
									<?php if ( '' !== $public_host ) : ?>
										<div style="margin-top:4px;color:#646970;font-size:12px;"><?php echo esc_html( $public_host ); ?></div>
									<?php endif; ?>
								</td>
								<td><code><?php echo esc_html( $post ? $post->post_name : '' ); ?></code></td>
								<td><?php echo esc_html( $year ?: self::tr( 'unknown' ) ); ?></td>
								<td><?php echo esc_html( $lang_value ?: self::tr( 'unknown' ) ); ?></td>
								<td><?php echo esc_html( get_post_status( $id ) ); ?></td>
								<td><?php echo esc_html( get_the_modified_date( 'd.m.Y H:i', $id ) ); ?></td>
								<td>
									<div style="display:flex;gap:8px;flex-wrap:wrap;">
										<a class="button button-small" href="<?php echo esc_url( get_permalink( $id ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( self::tr( 'open' ) ); ?></a>
										<a class="button button-small" href="<?php echo esc_url( get_edit_post_link( $id ) ); ?>"><?php echo esc_html( self::tr( 'edit' ) ); ?></a>
										<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=relr-labels&relr_download=html&label_id=' . $id ), 'relr_download_' . $id . '_html' ) ); ?>"><?php echo esc_html( self::tr( 'download_html' ) ); ?></a>
										<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=relr-labels&relr_download=json&label_id=' . $id ), 'relr_download_' . $id . '_json' ) ); ?>"><?php echo esc_html( self::tr( 'download_json' ) ); ?></a>
									</div>
								</td>
							</tr>
						<?php endwhile; wp_reset_postdata(); ?>
					<?php else : ?>
						<tr><td colspan="10"><?php echo esc_html( self::tr( 'no_labels' ) ); ?></td></tr>
					<?php endif; ?>
					</tbody>
				</table>
			</form>

			<script>
			document.addEventListener('DOMContentLoaded', function () {
				const toggle = document.getElementById('relr-check-all');
				if (!toggle) return;
				toggle.addEventListener('change', function () {
					document.querySelectorAll('input[name="relr_ids[]"]').forEach(function (cb) {
						cb.checked = toggle.checked;
					});
				});
			});
			</script>
		</div>
		<?php
	}


	public static function render_design_page(): void {
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		$s           = self::design_settings();
		?>
		<div class="wrap relr-wrap">
			<h1><?php echo esc_html( self::tr( 'design' ) ); ?></h1>
			<p><?php echo esc_html( self::tr( 'design_intro' ) ); ?></p>
			<?php self::render_notice(); ?>

			<form method="post" id="relr-design-form">
				<?php wp_nonce_field( 'relr_save_design' ); ?>
				<input type="hidden" name="relr_save_design" value="1">

				<div class="relr-design-viewport" style="overflow-x:auto;overflow-y:visible;padding-bottom:8px;">
					<div class="relr-design-shell" style="display:flex;flex-wrap:nowrap;align-items:flex-start;gap:18px;min-width:920px;">
						<div class="relr-design-left" style="flex:0 0 420px;width:420px;min-width:420px;display:flex;flex-direction:column;gap:14px;">
							<div class="relr-card">
								<div class="relr-card-head">
									<h2><?php echo esc_html( self::tr( 'colors' ) ); ?></h2>
									<button type="button" class="relr-icon-reset relr-reset-section" data-section="colors" title="<?php echo esc_attr( self::tr( 'load_defaults' ) ); ?>" aria-label="<?php echo esc_attr( self::tr( 'load_defaults' ) ); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
								</div>
								<div class="relr-color-list">
									<?php foreach ( self::color_fields() as $field ) : ?>
										<div class="relr-color-row">
											<label for="<?php echo esc_attr( $field ); ?>"><?php echo esc_html( self::tr( $field ) ); ?></label>
											<div class="relr-color-controls">
												<input type="color" id="<?php echo esc_attr( $field ); ?>_picker" value="<?php echo esc_attr( $s[ $field ] ); ?>" data-sync="<?php echo esc_attr( $field ); ?>">
												<input type="text" id="<?php echo esc_attr( $field ); ?>" name="<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( $s[ $field ] ); ?>" data-default="<?php echo esc_attr( self::$defaults_design[ $field ] ); ?>" class="regular-text relr-color-text">
												<button type="button" class="relr-inline-reset relr-reset-field" data-field="<?php echo esc_attr( $field ); ?>" title="<?php echo esc_attr( self::tr( 'reset_field' ) ); ?>" aria-label="<?php echo esc_attr( self::tr( 'reset_field' ) ); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>

							<div class="relr-card">
								<div class="relr-card-head">
									<h2><?php echo esc_html( self::tr( 'sizes' ) ); ?></h2>
									<button type="button" class="relr-icon-reset relr-reset-section" data-section="sizes" title="<?php echo esc_attr( self::tr( 'load_defaults' ) ); ?>" aria-label="<?php echo esc_attr( self::tr( 'load_defaults' ) ); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
								</div>
								<div class="relr-field-stack">
									<?php foreach ( array( 'base_font_size', 'small_font_size', 'button_font_size', 'font_family', 'panel_radius', 'outer_width', 'label_width', 'outer_padding_y', 'card_padding' ) as $field ) : ?>
										<div class="relr-field-row">
											<label for="<?php echo esc_attr( $field ); ?>"><?php echo esc_html( self::tr( $field ) ); ?></label>
											<?php if ( 'font_family' === $field ) : ?>
												<div class="relr-input-inline">
													<select name="font_family" id="font_family" data-default="<?php echo esc_attr( self::$defaults_design['font_family'] ); ?>">
														<?php foreach ( self::font_options() as $value => $label ) : ?>
															<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $s['font_family'], $value ); ?>><?php echo esc_html( $label ); ?></option>
														<?php endforeach; ?>
													</select>
													<button type="button" class="relr-inline-reset relr-reset-field" data-field="font_family" title="<?php echo esc_attr( self::tr( 'reset_field' ) ); ?>" aria-label="<?php echo esc_attr( self::tr( 'reset_field' ) ); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
												</div>
											<?php else : ?>
												<div class="relr-input-inline">
													<input type="<?php echo esc_attr( 'number' ); ?>" id="<?php echo esc_attr( $field ); ?>" name="<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( (string) $s[ $field ] ); ?>" data-default="<?php echo esc_attr( (string) self::$defaults_design[ $field ] ); ?>" min="0">
													<button type="button" class="relr-inline-reset relr-reset-field" data-field="<?php echo esc_attr( $field ); ?>" title="<?php echo esc_attr( self::tr( 'reset_field' ) ); ?>" aria-label="<?php echo esc_attr( self::tr( 'reset_field' ) ); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
												</div>
											<?php endif; ?>
										</div>
									<?php endforeach; ?>
									<div class="relr-field-row">
										<label for="custom_css"><?php echo esc_html( self::tr( 'custom_css' ) ); ?></label>
										<div class="relr-input-inline relr-input-inline-textarea">
											<textarea id="custom_css" name="custom_css" rows="4" data-default=""><?php echo esc_textarea( $s['custom_css'] ); ?></textarea>
											<button type="button" class="relr-inline-reset relr-reset-field relr-align-start" data-field="custom_css" title="<?php echo esc_attr( self::tr( 'reset_field' ) ); ?>" aria-label="<?php echo esc_attr( self::tr( 'reset_field' ) ); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
										</div>
									</div>
								</div>
							</div>

							<div class="relr-card relr-robust-card">
								<div class="relr-card-head">
									<h2><?php echo esc_html( self::tr( 'robustness' ) ); ?></h2>
									<button type="button" class="relr-icon-reset relr-reset-section" data-section="robustness" title="<?php echo esc_attr( self::tr( 'load_defaults' ) ); ?>" aria-label="<?php echo esc_attr( self::tr( 'load_defaults' ) ); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
								</div>
								<div class="relr-check-stack">
									<?php foreach ( self::robust_fields() as $field ) : ?>
										<div class="relr-check-row">
											<label class="relr-check-label"><input type="checkbox" id="<?php echo esc_attr( $field ); ?>" name="<?php echo esc_attr( $field ); ?>" value="1" <?php checked( ! empty( $s[ $field ] ) ); ?> data-default="<?php echo esc_attr( (string) self::$defaults_design[ $field ] ); ?>"><span><?php echo esc_html( self::robust_field_label( $field ) ); ?></span></label>
											<button type="button" class="relr-inline-reset relr-reset-field" data-field="<?php echo esc_attr( $field ); ?>" title="<?php echo esc_attr( self::tr( 'reset_field' ) ); ?>" aria-label="<?php echo esc_attr( self::tr( 'reset_field' ) ); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
										</div>
									<?php endforeach; ?>
								</div>
							</div>

							<p class="relr-save-row"><button class="button button-primary button-large"><?php echo esc_html( self::tr( 'save_design' ) ); ?></button></p>
						</div>

						<div class="relr-design-right" style="flex:0 0 860px;width:860px;min-width:860px;">
							<div class="relr-card relr-preview-card">
								<div class="relr-card-head">
									<h2><?php echo esc_html( self::tr( 'live_preview' ) ); ?></h2>
									<button type="button" class="relr-icon-reset relr-reset-preview" title="<?php echo esc_attr( self::tr( 'load_defaults' ) ); ?>" aria-label="<?php echo esc_attr( self::tr( 'load_defaults' ) ); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
								</div>
								<p class="description" style="margin:0 0 10px 0;"><?php echo esc_html( self::tr( 'preview_hint' ) ); ?></p>
								<div id="relr-preview-shell">
									<style id="relr-admin-live-style"></style>
									<div id="relr-preview-stage">
										<div id="relr-preview-canvas" class="relr-receiver-shell"><?php echo self::sample_preview_fragment(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<style>
					.relr-card{background:#fff;border:1px solid #d9e1ea;border-radius:16px;padding:10px 12px;box-shadow:0 1px 2px rgba(16,24,40,.03)}
					.relr-card-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin:-10px -12px 10px -12px;padding:10px 12px;background:#eef3f7;border-bottom:1px solid #dce5ee;border-top-left-radius:16px;border-top-right-radius:16px}
					.relr-card-head h2{margin:0;font-size:14px;line-height:1.2;color:#435466;font-weight:600}
					.relr-icon-reset{display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:999px;border:1px solid #d7e0ea;background:#fff;color:#8392a3;cursor:pointer;padding:0}
					.relr-icon-reset:hover{background:#f5f8fb;color:#556476}
					.relr-icon-reset .dashicons{font-size:12px;line-height:1;width:12px;height:12px}
					.relr-color-list,.relr-field-stack,.relr-check-stack{display:flex;flex-direction:column;gap:8px}
					.relr-color-row,.relr-field-row{display:flex;flex-direction:column;gap:4px}
					.relr-color-row label,.relr-field-row label{font-weight:600;font-size:11px;line-height:1.2;color:#526173}
					.relr-color-controls{display:grid;grid-template-columns:34px minmax(0,1fr) 26px;gap:6px;align-items:center}
					.relr-color-controls input[type=color]{width:34px;height:28px;padding:1px;border:1px solid #d0d7de;border-radius:6px;background:#fff}
					.relr-field-row input[type=number],.relr-field-row input[type=text],.relr-field-row select,.relr-field-row textarea,.relr-color-controls input[type=text]{width:100%;max-width:none;margin:0;min-height:30px;border-color:#cfd7df;border-radius:6px}
					.relr-field-row textarea{font-family:Consolas,Monaco,monospace;min-height:82px;padding-top:6px;padding-bottom:6px}
					.relr-input-inline{display:grid;grid-template-columns:minmax(0,1fr) 26px;gap:6px;align-items:center}
					.relr-input-inline-textarea{align-items:start}
					.relr-inline-reset{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:8px;border:1px solid #d7e0ea;background:#fff;color:#8793a0;cursor:pointer;padding:0}
					.relr-inline-reset:hover{background:#f5f8fb;color:#556476}
					.relr-inline-reset .dashicons{font-size:12px;line-height:1;width:12px;height:12px}
					.relr-check-row{display:flex;gap:8px;align-items:center;justify-content:space-between}
					.relr-check-label{display:flex;gap:8px;align-items:flex-start;flex:1;font-size:12px;line-height:1.3;color:#1f2937}
					.relr-save-row{margin:0}
					#relr-preview-shell{background:#f7f9fb;border:1px solid #dde5ee;border-radius:14px;padding:14px;overflow:auto;min-height:760px}
					#relr-preview-stage{min-height:740px;padding:14px;border:1px solid #dce5ee;border-radius:14px;background:#fff;box-shadow:0 10px 28px rgba(16,24,40,.06)}
					#relr-preview-canvas{min-height:980px}
				</style>

				<script>
				document.addEventListener('DOMContentLoaded', function () {
					try {
					const form = document.getElementById('relr-design-form');
					if (!form) return;

					const defaults = <?php echo wp_json_encode( self::$defaults_design ); ?>;
					const colorFields = <?php echo wp_json_encode( self::color_fields() ); ?>;
					const sizeFields = <?php echo wp_json_encode( array( 'base_font_size', 'small_font_size', 'button_font_size', 'font_family', 'panel_radius', 'outer_width', 'label_width', 'outer_padding_y', 'card_padding', 'custom_css' ) ); ?>;
					const robustFields = <?php echo wp_json_encode( self::robust_fields() ); ?>;
					const fieldLimits = {
						base_font_size:[10,24],
						small_font_size:[9,20],
						button_font_size:[10,22],
						panel_radius:[0,36],
						outer_width:[520,1600],
						label_width:[320,1200],
						outer_padding_y:[0,80],
						card_padding:[8,48]
					};
					const inputs = form.querySelectorAll('input, select, textarea');
					const previewRoot = document.getElementById('relr-preview-canvas');
					const previewStyle = document.getElementById('relr-admin-live-style');

					function val(id) {
						const el = document.getElementById(id);
						if (!el) return '';
						return el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value;
					}

					function currentValues() {
						return {
							page_bg: val('page_bg') || defaults.page_bg,
							card_bg: val('card_bg') || defaults.card_bg,
							table_head_bg: val('table_head_bg') || defaults.table_head_bg,
							text_color: val('text_color') || defaults.text_color,
							muted_color: val('muted_color') || defaults.muted_color,
							border_color: val('border_color') || defaults.border_color,
							base_font_size: parseInt(val('base_font_size') || defaults.base_font_size, 10) || defaults.base_font_size,
							small_font_size: parseInt(val('small_font_size') || defaults.small_font_size, 10) || defaults.small_font_size,
							button_font_size: parseInt(val('button_font_size') || defaults.button_font_size, 10) || defaults.button_font_size,
							font_family: val('font_family') || defaults.font_family,
							panel_radius: parseInt(val('panel_radius') || defaults.panel_radius, 10) || defaults.panel_radius,
							outer_width: parseInt(val('outer_width') || defaults.outer_width, 10) || defaults.outer_width,
							label_width: parseInt(val('label_width') || defaults.label_width, 10) || defaults.label_width,
							outer_padding_y: parseInt(val('outer_padding_y') || defaults.outer_padding_y, 10) || defaults.outer_padding_y,
							card_padding: parseInt(val('card_padding') || defaults.card_padding, 10) || defaults.card_padding,
							custom_css: val('custom_css') || ''
						};
					}

					function syncColorInputs() {
						document.querySelectorAll('input[type=color][data-sync]').forEach(function (picker) {
							const target = document.getElementById(picker.dataset.sync);
							if (target && /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(target.value)) {
								picker.value = target.value;
							}
						});
					}

					function applyLimits() {
						Object.keys(fieldLimits).forEach(function (field) {
							const el = document.getElementById(field);
							if (!el) return;
							let value = parseInt(el.value || defaults[field], 10);
							if (isNaN(value)) value = defaults[field];
							value = Math.max(fieldLimits[field][0], Math.min(fieldLimits[field][1], value));
							el.value = value;
						});
					}

					function buildLiveCss(values) {
						const css = [
							'#relr-preview-canvas{background:var(--relr-page-bg) !important;font-family:var(--relr-font) !important;font-size:var(--relr-base) !important;color:var(--relr-text) !important;padding:0 !important;}',
							'#relr-preview-canvas, #relr-preview-canvas *{box-sizing:border-box;}',
							'#relr-preview-canvas .relr-preview-shell{width:min(var(--relr-outer-width), 100%) !important;max-width:none !important;margin:0 auto !important;padding:var(--relr-outer-pad) 24px !important;background:var(--relr-page-bg) !important;}',
							'#relr-preview-canvas #label, #relr-preview-canvas .relr-preview-card{width:min(var(--relr-label-width), 100%) !important;max-width:none !important;margin:0 auto !important;padding:var(--relr-card-pad) !important;background:var(--relr-card-bg) !important;border-radius:var(--relr-radius) !important;border:1px solid var(--relr-border) !important;color:var(--relr-text) !important;box-shadow:none !important;}',
							'#relr-preview-canvas, #relr-preview-canvas p, #relr-preview-canvas div, #relr-preview-canvas span, #relr-preview-canvas td, #relr-preview-canvas th, #relr-preview-canvas strong{color:var(--relr-text) !important;}',
							'#relr-preview-canvas .relr-preview-meta, #relr-preview-canvas .relr-preview-footnote, #relr-preview-canvas .relr-preview-trace td, #relr-preview-canvas .relr-preview-trace{font-size:var(--relr-small) !important;color:var(--relr-muted) !important;line-height:1.45 !important;}',
							'#relr-preview-canvas table{width:100% !important;border-collapse:collapse !important;border-color:var(--relr-border) !important;font-size:var(--relr-base) !important;}',
							'#relr-preview-canvas th, #relr-preview-canvas td{border:1px solid var(--relr-border) !important;padding:10px 12px !important;font-size:var(--relr-base) !important;line-height:1.45 !important;vertical-align:top !important;}',
							'#relr-preview-canvas th{background:var(--relr-head-bg) !important;}',
							'#relr-preview-canvas .nlm-preview-lang{display:flex !important;justify-content:flex-end !important;align-items:center !important;gap:10px !important;width:min(var(--relr-label-width), 100%) !important;margin:0 auto 14px auto !important;}',
							'#relr-preview-canvas .nlm-preview-lang button{display:inline-flex !important;align-items:center !important;justify-content:center !important;background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%) !important;border:1px solid #d9e4ef !important;color:#334155 !important;box-shadow:0 1px 2px rgba(15,23,42,.03) !important;border-radius:999px !important;padding:calc(var(--relr-button) * 0.38) calc(var(--relr-button) * 0.9) !important;font-size:var(--relr-button) !important;font-weight:600 !important;line-height:1 !important;min-width:calc(var(--relr-button) * 2.6) !important;cursor:pointer !important;transition:all .16s ease !important;}',
							'#relr-preview-canvas .nlm-preview-lang button:hover{border-color:#bfd0e3 !important;background:#f5f9ff !important;transform:translateY(-1px);}',
							'#relr-preview-canvas .nlm-preview-lang .is-active, #relr-preview-canvas .nlm-preview-lang [aria-pressed="true"]{background:#eef5ff !important;border-color:#bfd3ea !important;color:#244267 !important;box-shadow:0 0 0 3px rgba(36,66,103,.06) !important;}',
							'#relr-preview-canvas .relr-preview-ingredients, #relr-preview-canvas .relr-preview-footnote, #relr-preview-canvas .relr-preview-meta{padding-left:12px !important;padding-right:12px !important;margin-left:0 !important;margin-right:0 !important;line-height:1.55 !important;}',
							'#relr-preview-canvas .relr-preview-ingredients{font-size:var(--relr-base) !important;}'
						].join("\n");

						return values.custom_css ? css + "\n" + values.custom_css : css;
					}

					function applyPreviewVars(values) {
						if (!previewRoot) return;
						previewRoot.style.setProperty('--relr-page-bg', values.page_bg);
						previewRoot.style.setProperty('--relr-card-bg', values.card_bg);
						previewRoot.style.setProperty('--relr-head-bg', values.table_head_bg);
						previewRoot.style.setProperty('--relr-text', values.text_color);
						previewRoot.style.setProperty('--relr-muted', values.muted_color);
						previewRoot.style.setProperty('--relr-border', values.border_color);
						previewRoot.style.setProperty('--relr-font', values.font_family);
						previewRoot.style.setProperty('--relr-base', values.base_font_size + 'px');
						previewRoot.style.setProperty('--relr-small', values.small_font_size + 'px');
						previewRoot.style.setProperty('--relr-button', values.button_font_size + 'px');
						previewRoot.style.setProperty('--relr-radius', values.panel_radius + 'px');
						previewRoot.style.setProperty('--relr-outer-width', values.outer_width + 'px');
						previewRoot.style.setProperty('--relr-label-width', values.label_width + 'px');
						previewRoot.style.setProperty('--relr-outer-pad', values.outer_padding_y + 'px');
						previewRoot.style.setProperty('--relr-card-pad', values.card_padding + 'px');
					}

					function applyLivePreviewCss() {
						const values = currentValues();
						applyPreviewVars(values);
						if (previewStyle) {
							previewStyle.textContent = buildLiveCss(values);
						}
					}


					const previewTranslations = {
						de: {
							subtitle: 'Nährwertangaben je 100 ml',
							meta: 'Zutaten und Nährwerte je 100 ml',
							energy: 'Brennwert',
							carbs: 'Kohlenhydrate',
							sugars: 'davon Zucker',
							trace: 'Enthält geringfügige Mengen von: Fett, gesättigten Fettsäuren, Eiweiß und Salz',
							ingredients_label: 'Zutaten:',
							ingredients_html: 'Trauben*, Saccharose*, <strong>Sulfite</strong>, Gummiarabikum, unter Schutzatmosphäre abgefüllt',
							footnote: '* aus ökologischer Erzeugung'
						},
						en: {
							subtitle: 'Nutrition declaration per 100 ml',
							meta: 'Ingredients and nutrition declaration per 100 ml',
							energy: 'Energy',
							carbs: 'Carbohydrates',
							sugars: 'of which sugars',
							trace: 'Contains negligible amounts of fat, saturates, protein and salt',
							ingredients_label: 'Ingredients:',
							ingredients_html: 'Grapes*, sucrose*, <strong>sulfites</strong>, gum arabic, bottled under protective atmosphere',
							footnote: '* from organic production'
						},
						it: {
							subtitle: 'Dichiarazione nutrizionale per 100 ml',
							meta: 'Ingredienti e dichiarazione nutrizionale per 100 ml',
							energy: 'Energia',
							carbs: 'Carboidrati',
							sugars: 'di cui zuccheri',
							trace: 'Contiene quantità trascurabili di grassi, acidi grassi saturi, proteine e sale',
							ingredients_label: 'Ingredienti:',
							ingredients_html: 'Uve*, saccarosio*, <strong>solfiti</strong>, gomma arabica, imbottigliato in atmosfera protettiva',
							footnote: '* da produzione biologica'
						},
						fr: {
							subtitle: 'Déclaration nutritionnelle pour 100 ml',
							meta: 'Ingrédients et déclaration nutritionnelle pour 100 ml',
							energy: 'Énergie',
							carbs: 'Glucides',
							sugars: 'dont sucres',
							trace: 'Contient des quantités négligeables de matières grasses, acides gras saturés, protéines et sel',
							ingredients_label: 'Ingrédients :',
							ingredients_html: 'Raisins*, saccharose*, <strong>sulfites</strong>, gomme arabique, mis en bouteille sous atmosphère protectrice',
							footnote: '* issu de l\'agriculture biologique'
						}
					};

					function setPreviewLanguage(lang) {
						if (!previewRoot) return;
						const tr = previewTranslations[lang] || previewTranslations.de;
						const map = {subtitle:'relr-preview-subtitle',meta:'relr-preview-meta',energy:'relr-preview-energy',carbs:'relr-preview-carbs',sugars:'relr-preview-sugars',trace:'relr-preview-trace',footnote:'relr-preview-footnote'};
						Object.keys(map).forEach(function(key){
							const el = previewRoot.querySelector('#' + map[key]);
							if (el) el.textContent = tr[key];
						});
						const label = previewRoot.querySelector('#relr-preview-ingredients-label');
						if (label) label.textContent = tr.ingredients_label;
						const body = previewRoot.querySelector('#relr-preview-ingredients-body');
						if (body) body.innerHTML = tr.ingredients_html;
						previewRoot.querySelectorAll('[data-relr-lang]').forEach(function(btn){
							const active = btn.getAttribute('data-relr-lang') === lang;
							btn.classList.toggle('is-active', active);
							btn.setAttribute('aria-pressed', active ? 'true' : 'false');
						});
					}

					function wirePreviewLanguageButtons() {
						if (!previewRoot) return;
						previewRoot.querySelectorAll('[data-relr-lang]').forEach(function(btn){
							btn.addEventListener('click', function(ev){
								ev.preventDefault();
								setPreviewLanguage(btn.getAttribute('data-relr-lang') || 'de');
							});
						});
						setPreviewLanguage('de');
					}

					function schedulePreview() {
						applyLimits();
						applyLivePreviewCss();
					}

					document.querySelectorAll('input[type=color][data-sync]').forEach(function (picker) {
						picker.addEventListener('input', function () {
							const target = document.getElementById(this.dataset.sync);
							if (target) target.value = this.value;
							schedulePreview();
						});
					});

					document.querySelectorAll('.relr-color-text').forEach(function (input) {
						input.addEventListener('input', function () {
							const picker = document.getElementById(this.id + '_picker');
							if (picker && /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(this.value)) picker.value = this.value;
							schedulePreview();
						});
					});

					inputs.forEach(function (el) {
						el.addEventListener('input', schedulePreview);
						el.addEventListener('change', schedulePreview);
					});

					document.querySelectorAll('.relr-reset-field').forEach(function (button) {
						button.addEventListener('click', function () {
							const field = this.dataset.field;
							const el = document.getElementById(field);
							if (!el) return;
							const def = el.dataset.default !== undefined ? el.dataset.default : (defaults[field] !== undefined ? defaults[field] : '');
							if (el.type === 'checkbox') {
								el.checked = String(def) === '1';
							} else {
								el.value = def;
							}
							const picker = document.getElementById(field + '_picker');
							if (picker && typeof def === 'string' && def.charAt(0) === '#') picker.value = def;
							schedulePreview();
						});
					});

					document.querySelectorAll('.relr-reset-section').forEach(function (button) {
						button.addEventListener('click', function () {
							const section = this.dataset.section;
							let fields = [];
							if (section === 'colors') fields = colorFields;
							if (section === 'sizes') fields = sizeFields;
							if (section === 'robustness') fields = robustFields;
							fields.forEach(function (field) {
								const el = document.getElementById(field);
								if (!el) return;
								if (el.type === 'checkbox') {
									el.checked = String(el.dataset.default) === '1';
								} else {
									el.value = el.dataset.default || defaults[field] || '';
								}
								const picker = document.getElementById(field + '_picker');
								if (picker && defaults[field] && typeof defaults[field] === 'string' && defaults[field].charAt(0) === '#') picker.value = defaults[field];
							});
							syncColorInputs();
							schedulePreview();
						});
					});

					const previewReset = document.querySelector('.relr-reset-preview');
					if (previewReset) {
						previewReset.addEventListener('click', function () {
							Object.keys(defaults).forEach(function (field) {
								const el = document.getElementById(field);
								if (!el) return;
								if (el.type === 'checkbox') {
									el.checked = String(defaults[field]) === '1';
								} else {
									el.value = defaults[field];
								}
								const picker = document.getElementById(field + '_picker');
								if (picker && defaults[field] && typeof defaults[field] === 'string' && defaults[field].charAt(0) === '#') picker.value = defaults[field];
							});
							syncColorInputs();
							schedulePreview();
						});
					}

					syncColorInputs();
					applyLimits();
					applyLivePreviewCss();
					wirePreviewLanguageButtons();
					} catch (err) {
						console.error('RELR design preview failed', err);
					}
				});
				</script>
			</form>
		</div>
		<?php
	}

	public static function ajax_render_preview(): void {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
		}
		check_ajax_referer( 'relr_preview', 'nonce' );
		$settings = self::sanitize_design_settings( $_POST );
		wp_send_json_success(
			array(
				'html' => self::build_preview_document( $settings ),
			)
		);
	}

	private static function build_preview_document( array $settings ): string {
		$preview = self::get_preview_payload();
		return self::build_output_document( $preview['title'], $preview['html'], $preview['lang'], $settings );
	}

	private static function get_preview_payload(): array {
		return array(
			'title' => self::tr( 'preview_title' ),
			'html'  => self::sample_preview_fragment(),
			'lang'  => self::lang(),
		);
	}

	private static function sample_preview_fragment(): string {
		return '<div class="relr-preview-shell">'
			. '<div class="nlm-preview-lang" id="nlm_preview_lang_switch">'
			. '<button type="button" class="is-active" data-relr-lang="de" aria-pressed="true">DE</button>'
			. '<button type="button" data-relr-lang="en" aria-pressed="false">EN</button>'
			. '<button type="button" data-relr-lang="it" aria-pressed="false">IT</button>'
			. '<button type="button" data-relr-lang="fr" aria-pressed="false">FR</button>'
			. '</div>'
			. '<div id="label" class="relr-preview-card">'
			. '<div id="relr-preview-meta" class="relr-preview-meta">Zutaten und Nährwerte je 100 ml</div>'
			. '<table><thead><tr><th colspan="2" id="relr-preview-subtitle">Nährwertangaben je 100 ml</th></tr></thead><tbody>'
			. '<tr><td id="relr-preview-energy">Brennwert</td><td>310 kJ / 75 kcal</td></tr>'
			. '<tr><td id="relr-preview-carbs">Kohlenhydrate</td><td>1.5 g</td></tr>'
			. '<tr><td id="relr-preview-sugars">davon Zucker</td><td>0.7 g</td></tr>'
			. '<tr class="relr-preview-trace"><td colspan="2" id="relr-preview-trace">Enthält geringfügige Mengen von: Fett, gesättigten Fettsäuren, Eiweiß und Salz</td></tr>'
			. '</tbody></table>'
			. '<div class="relr-preview-ingredients"><strong id="relr-preview-ingredients-label">Zutaten:</strong> <span id="relr-preview-ingredients-body">Trauben*, Saccharose*, <strong>Sulfite</strong>, Gummiarabikum, unter Schutzatmosphäre abgefüllt</span></div>'
			. '<p class="relr-preview-footnote" id="relr-preview-footnote">* aus ökologischer Erzeugung</p>'
			. '</div>'
			. '</div>';
	}

	public static function render_setup_page(): void {
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		$ui            = self::ui_settings();
		$receiver_url  = untrailingslashit( home_url() );
		$discovery_url = rest_url( self::ROUTE_NAMESPACE_V2 . '/info' );
		$legacy_url    = rest_url( self::ROUTE_NAMESPACE_V1 . '/info' );
		?>
		<div class="wrap relr-wrap">
			<h1><?php echo esc_html( self::tr( 'setup' ) ); ?></h1>
			<p><?php echo esc_html( self::tr( 'setup_intro' ) ); ?></p>
			<?php self::render_notice(); ?>

			<div style="display:grid;grid-template-columns:minmax(320px,760px) minmax(280px,360px);gap:20px;max-width:1160px;align-items:start;">
				<div class="postbox" style="padding:20px;border-radius:14px;">
					<h2 style="margin:0 0 16px 0;"><?php echo esc_html( self::tr( 'setup_box_title' ) ); ?></h2>
					<ol style="margin:0 0 16px 22px;">
						<li style="margin-bottom:12px;"><?php echo esc_html( self::tr( 'step_1' ) ); ?></li>
						<li style="margin-bottom:12px;"><?php echo esc_html( self::tr( 'step_2' ) ); ?></li>
						<li style="margin-bottom:12px;"><?php echo esc_html( self::tr( 'step_3' ) ); ?></li>
						<li style="margin-bottom:12px;"><?php echo esc_html( self::tr( 'step_4' ) ); ?></li>
						<li style="margin-bottom:12px;"><?php echo esc_html( self::tr( 'step_5' ) ); ?></li>
						<li><?php echo esc_html( self::tr( 'step_6' ) ); ?></li>
					</ol>
					<p style="margin:0 0 14px 0;"><strong><?php echo esc_html( self::tr( 'notes' ) ); ?>:</strong> <?php echo esc_html( self::tr( 'setup_hint' ) ); ?></p>
					<p style="margin:0 0 12px 0;"><strong><?php echo esc_html( self::tr( 'setup_receiver_url' ) ); ?>:</strong><br><code><?php echo esc_html( $receiver_url ); ?></code></p>
					<p style="margin:0 0 12px 0;"><strong><?php echo esc_html( self::tr( 'setup_info_url' ) ); ?>:</strong><br><code><?php echo esc_html( $discovery_url ); ?></code></p>
					<p style="margin:0;"><strong><?php echo esc_html( self::tr( 'setup_legacy_url' ) ); ?>:</strong><br><code><?php echo esc_html( $legacy_url ); ?></code></p>
				</div>

				<div class="postbox" style="padding:20px;border-radius:14px;">
					<h2 style="margin:0 0 14px 0;"><?php echo esc_html( self::tr( 'central_design_title' ) ); ?></h2>
					<p style="margin:0;"><?php echo esc_html( self::tr( 'central_design_setup' ) ); ?></p>
				</div>

				<div class="postbox" style="padding:20px;border-radius:14px;">
					<h2 style="margin:0 0 14px 0;"><?php echo esc_html( self::tr( 'language_setting' ) ); ?></h2>
					<form method="post">
						<?php wp_nonce_field( 'relr_save_ui_language' ); ?>
						<input type="hidden" name="relr_save_ui_language" value="1">
						<select name="relr_ui_language" style="width:100%;margin-bottom:12px;">
							<option value="de" <?php selected( $ui['language'], 'de' ); ?>>Deutsch</option>
							<option value="en" <?php selected( $ui['language'], 'en' ); ?>>English</option>
							<option value="fr" <?php selected( $ui['language'], 'fr' ); ?>>Français</option>
							<option value="it" <?php selected( $ui['language'], 'it' ); ?>>Italiano</option>
						</select>
						<button class="button button-primary"><?php echo esc_html( self::tr( 'save_language' ) ); ?></button>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	private static function render_notice(): void {
		if ( ! isset( $_GET['relr_notice'] ) ) {
			return;
		}
		$notice = sanitize_text_field( wp_unslash( $_GET['relr_notice'] ) );
		$message = '';
		$class   = 'notice-success';
		if ( in_array( $notice, array( 'design_saved', 'language_saved' ), true ) ) {
			$message = self::tr( 'settings_saved' );
		}
		if ( 'bulk_done' === $notice ) {
			$message = self::tr( 'bulk_done' );
		}
		if ( 'design_managed' === $notice ) {
			$message = self::tr( 'design_page_removed_notice' );
			$class   = 'notice-info';
		}
		if ( '' !== $message ) {
			echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
		}
	}

	private static function extract_year( string $title ): string {
		if ( preg_match( '/\b(19|20)\d{2}\b/', $title, $m ) ) {
			return $m[0];
		}
		return '';
	}

	private static function download_label( int $label_id, string $format ): void {
		$title = get_the_title( $label_id );
		$slug  = get_post_field( 'post_name', $label_id );
		$lang  = (string) get_post_meta( $label_id, self::META_LANG, true );
		$html  = (string) get_post_meta( $label_id, self::META_HTML, true );

		if ( 'html' === $format ) {
			nocache_headers();
			header( 'Content-Type: text/html; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $slug . '.html' ) . '"' );
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			exit;
		}

		if ( 'json' === $format ) {
			nocache_headers();
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $slug . '.json' ) . '"' );
			echo wp_json_encode(
				array(
					'id'     => $label_id,
					'title'  => $title,
					'slug'   => $slug,
					'lang'   => $lang,
					'status' => get_post_status( $label_id ),
					'url'    => get_permalink( $label_id ),
					'html'   => $html,
				),
				JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
			);
			exit;
		}
	}

	private static function download_csv(): void {
		$query = new WP_Query(
			array(
				'post_type'      => self::CPT,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'orderby'        => 'modified',
				'order'          => 'DESC',
			)
		);

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="receiver-e-labels.csv"' );

		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'ID', 'Title', 'Slug', 'Language', 'Status', 'URL', 'Modified' ), ';' );

		while ( $query->have_posts() ) {
			$query->the_post();
			$id = get_the_ID();
			fputcsv(
				$out,
				array(
					$id,
					get_the_title( $id ),
					get_post_field( 'post_name', $id ),
					(string) get_post_meta( $id, self::META_LANG, true ),
					get_post_status( $id ),
					get_permalink( $id ),
					get_the_modified_date( 'Y-m-d H:i:s', $id ),
				),
				';'
			);
		}
		wp_reset_postdata();
		fclose( $out );
		exit;
	}

	public static function render_frontend(): void {
		if ( ! is_singular( self::CPT ) ) {
			return;
		}
		$settings = self::design_settings();
		if ( empty( $settings['force_blank_layout'] ) ) {
			return;
		}

		$post_id = get_queried_object_id();
		if ( $post_id <= 0 ) {
			return;
		}

		$settings = self::synced_design_settings( $post_id );
		$html     = (string) get_post_meta( $post_id, self::META_HTML, true );
		$lang     = (string) get_post_meta( $post_id, self::META_LANG, true );
		if ( '' === $lang ) {
			$lang = determine_locale();
			$lang = strtolower( substr( $lang, 0, 2 ) );
		}

		nocache_headers();
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true );
		header( 'Pragma: no-cache', true );
		header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT', true );
		header_remove( 'Last-Modified' );
		if ( ! empty( $settings['add_noindex'] ) ) {
			header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
		}
		header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ), true );

		echo self::build_output_document( get_the_title( $post_id ), $html, $lang, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	public static function maybe_disable_canonical_redirect( $redirect_url, $requested_url ) {
		unset( $requested_url );
		$settings = self::design_settings();
		if ( is_singular( self::CPT ) && ! empty( $settings['disable_canonical'] ) ) {
			return false;
		}
		return $redirect_url;
	}

	public static function disable_public_caching(): void {
		if ( ! is_singular( self::CPT ) ) {
			return;
		}

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
			define( 'DONOTCACHEOBJECT', true );
		}
		if ( ! defined( 'DONOTCACHEDB' ) ) {
			define( 'DONOTCACHEDB', true );
		}
		if ( ! defined( 'DONOTMINIFY' ) ) {
			define( 'DONOTMINIFY', true );
		}
	}

	public static function close_comments_on_labels( $open, $post_id ) {
		$settings = self::design_settings();
		if ( self::CPT === get_post_type( $post_id ) && ! empty( $settings['close_comments'] ) ) {
			return false;
		}
		return $open;
	}

	private static function build_output_document( string $title, string $html, string $lang, array $settings ): string {
		$css = self::build_frontend_css( $settings );
		if ( self::looks_like_full_html_document( $html ) ) {
			return self::inject_into_full_document( $html, $css, $lang, ! empty( $settings['inject_viewport'] ) );
		}

		return self::wrap_fragment_in_document( $title, $html, $lang, $css );
	}

	private static function build_frontend_css( array $s ): string {
		$custom_css = trim( (string) $s['custom_css'] );
		$css = '
:root{
	--relr-page-bg:' . esc_attr( $s['page_bg'] ) . ';
	--relr-card-bg:' . esc_attr( $s['card_bg'] ) . ';
	--relr-head-bg:' . esc_attr( $s['table_head_bg'] ) . ';
	--relr-text:' . esc_attr( $s['text_color'] ) . ';
	--relr-muted:' . esc_attr( $s['muted_color'] ) . ';
	--relr-border:' . esc_attr( $s['border_color'] ) . ';
	--relr-accent:#244267;
	--relr-font:' . $s['font_family'] . ';
	--relr-base:' . (int) $s['base_font_size'] . 'px;
	--relr-small:' . (int) $s['small_font_size'] . 'px;
	--relr-button:' . (int) $s['button_font_size'] . 'px;
	--relr-radius:' . (int) $s['panel_radius'] . 'px;
	--relr-outer-width:' . (int) $s['outer_width'] . 'px;
	--relr-label-width:' . (int) $s['label_width'] . 'px;
	--relr-outer-pad:' . (int) $s['outer_padding_y'] . 'px;
	--relr-card-pad:' . (int) $s['card_padding'] . 'px;
	--relr-shadow:none;
}
html,body{
	margin:0 !important;
	padding:0 !important;
	font-family:var(--relr-font) !important;
	font-size:var(--relr-base) !important;
	color:var(--relr-text) !important;
}
body{
	background:var(--relr-page-bg) !important;
}
.relr-receiver-shell, body{
	min-height:100vh;
}
.relr-receiver-shell{
	box-sizing:border-box;
	width:min(var(--relr-outer-width), calc(100vw - 48px));
	max-width:none;
	margin:0 auto;
	padding:var(--relr-outer-pad) 24px;
}
.relr-receiver-shell--passthrough{
	width:100% !important;
	max-width:none !important;
	margin:0 !important;
	padding:0 !important;
}
.relr-receiver-fragment{
	box-sizing:border-box !important;
	width:100% !important;
	max-width:none !important;
	margin:0 auto !important;
	padding:0 !important;
	background:transparent !important;
	border:0 !important;
	border-radius:0 !important;
	box-shadow:none !important;
	color:var(--relr-text) !important;
}
.nler-logo-wrap,
.wel-logo-wrap,
.relr-logo-wrap{
	display:flex !important;
	justify-content:center !important;
	align-items:center !important;
	width:min(var(--relr-label-width), 100%) !important;
	margin:0 auto 14px auto !important;
	padding:0 12px !important;
	box-sizing:border-box !important;
}
.nler-logo-image,
.wel-logo-image,
.relr-logo-image{
	display:block !important;
	max-width:100% !important;
	max-height:110px !important;
	width:auto !important;
	height:auto !important;
	object-fit:contain !important;
}
#label,.relr-receiver-label,body > #label, body > .label, .nutrition-label, .e-label-card, .container > #label{
	box-sizing:border-box !important;
	width:min(var(--relr-label-width), 100%) !important;
	max-width:none !important;
	margin:0 auto !important;
	padding:var(--relr-card-pad) !important;
	background:var(--relr-card-bg) !important;
	border-radius:var(--relr-radius) !important;
	box-shadow:none !important;
	border:1px solid var(--relr-border) !important;
	color:var(--relr-text) !important;
}
h1,h2,h3,h4,h5,h6{
	color:var(--relr-text) !important;
}
p,li,span,div,td,th,label,small{
	color:inherit;
}
small,.small,.muted,.text-muted{
	font-size:var(--relr-small) !important;
	color:var(--relr-muted) !important;
}
table{
	width:100% !important;
	border-collapse:collapse !important;
}
th,td{
	border:1px solid var(--relr-border) !important;
	padding:10px 12px !important;
}
th{
	background:var(--relr-head-bg) !important;
}
a,.relr-link{
	color:inherit !important;
}
.relr-receiver-shell .nlm-preview-lang,
.relr-receiver-shell .nlm-lang-switch,
.relr-receiver-shell .nler-switches,
.relr-receiver-shell .lang-switch,
.relr-receiver-shell .language-switcher,
.relr-receiver-shell .language-switch,
.relr-receiver-shell [id*="lang"][id*="switch"],
.relr-receiver-shell [class*="lang"][class*="switch"]{
	display:flex !important;
	justify-content:center !important;
	align-items:center !important;
	gap:6px !important;
	max-width:var(--relr-label-width) !important;
	width:100% !important;
	margin:0 auto 8px auto !important;
	padding-right:0 !important;
	box-sizing:border-box !important;
}
.relr-receiver-shell .nlm-preview-lang button,
.relr-receiver-shell .nlm-preview-lang a,
.relr-receiver-shell .nlm-lang-switch button,
.relr-receiver-shell .nlm-lang-switch a,
.relr-receiver-shell .nler-switches button,
.relr-receiver-shell .nler-switches a,
.relr-receiver-shell .lang-switch button,
.relr-receiver-shell .lang-switch a,
.relr-receiver-shell .language-switcher button,
.relr-receiver-shell .language-switcher a,
.relr-receiver-shell .language-switch button,
.relr-receiver-shell .language-switch a,
.relr-receiver-shell [id*="lang"][id*="switch"] button,
.relr-receiver-shell [id*="lang"][id*="switch"] a,
.relr-receiver-shell [class*="lang"][class*="switch"] button,
.relr-receiver-shell [class*="lang"][class*="switch"] a{
	display:inline-flex !important;
	align-items:center !important;
	justify-content:center !important;
	background:#ffffff !important;
	background-color:#ffffff !important;
	border:1px solid #d7e0ea !important;
	color:#334155 !important;
	box-shadow:0 1px 1px rgba(15,23,42,.03) !important;
	border-radius:999px !important;
	padding:4px 10px !important;
	font-size:calc(var(--relr-button) - 3px) !important;
	font-weight:600 !important;
	line-height:1.1 !important;
	min-width:0 !important;
	text-decoration:none !important;
	cursor:pointer !important;
	-webkit-appearance:none !important;
	appearance:none !important;
}
.relr-receiver-shell .nlm-preview-lang .is-active,
.relr-receiver-shell .nlm-preview-lang [aria-current="true"],
.relr-receiver-shell .nlm-preview-lang [aria-pressed="true"],
.relr-receiver-shell .nlm-lang-switch .is-active,
.relr-receiver-shell .nler-switches .is-active,
.relr-receiver-shell .nler-switches [aria-current="true"],
.relr-receiver-shell .nler-switches [aria-pressed="true"],
.relr-receiver-shell .lang-switch .is-active,
.relr-receiver-shell .language-switcher .is-active,
.relr-receiver-shell .language-switch .is-active,
.relr-receiver-shell [id*="lang"][id*="switch"] .is-active,
.relr-receiver-shell [class*="lang"][class*="switch"] .is-active{
	background:#f4f7fb !important;
	background-color:#f4f7fb !important;
	border-color:#c7d3e0 !important;
	color:var(--relr-accent) !important;
}
.relr-receiver-shell table + div,
.relr-receiver-shell table + p,
.relr-receiver-shell .relr-preview-ingredients,
.relr-receiver-shell .relr-preview-footnote{
	padding-left:12px !important;
	padding-right:12px !important;
	margin-left:0 !important;
	margin-right:0 !important;
	box-sizing:border-box !important;
}
.relr-receiver-shell table + div > div{
	padding-left:0 !important;
	padding-right:0 !important;
}
.relr-receiver-shell .nler-panel{
	display:none !important;
}
.relr-receiver-shell .nler-panel.is-active{
	display:block !important;
}
.relr-receiver-shell .nler-table{
	width:100% !important;
	border-collapse:collapse !important;
	background:#ffffff !important;
	border:1px solid var(--relr-border) !important;
	font-size:var(--relr-base) !important;
}
.relr-receiver-shell .nler-table th,
.relr-receiver-shell .nler-table td{
	border:1px solid var(--relr-border) !important;
	padding:10px 12px !important;
	vertical-align:top !important;
	color:var(--relr-text) !important;
	font-size:var(--relr-base) !important;
	line-height:1.45 !important;
}
.relr-receiver-shell .nler-table thead th{
	text-align:left !important;
	background:var(--relr-head-bg) !important;
	font-weight:600 !important;
}
.relr-receiver-shell .nler-row{
	display:flex !important;
	justify-content:space-between !important;
	gap:16px !important;
}
.relr-receiver-shell .nler-row span:first-child{
	color:var(--relr-text) !important;
}
.relr-receiver-shell .nler-row span:last-child{
	text-align:right !important;
	white-space:nowrap !important;
	color:var(--relr-text) !important;
	font-weight:500 !important;
}
.relr-receiver-shell .nler-small{
	font-size:var(--relr-small) !important;
	color:var(--relr-muted) !important;
}
';
		if ( ! empty( $s['normalize_body'] ) ) {
			$css .= '
body,html{
	background:var(--relr-page-bg) !important;
	background-image:none !important;
}
body:before,body:after,html:before,html:after{
	display:none !important;
	content:none !important;
}
';
		}
		if ( '' !== $custom_css ) {
			$css .= "\n" . $custom_css;
		}
		return $css;
	}

	private static function inject_into_full_document( string $html, string $css, string $lang, bool $ensure_viewport ): string {
		$is_central_remote = self::looks_like_central_remote_fragment( $html );
		$body_class        = $is_central_remote ? self::central_remote_body_class( $html ) : '';
		$style_tag = $is_central_remote ? '' : '<style id="relr-receiver-design">' . $css . '</style>';
		$shell_class       = $is_central_remote ? '' : 'relr-receiver-shell';
		if ( $ensure_viewport && false === stripos( $html, 'name="viewport"' ) ) {
			$style_tag = '<meta name="viewport" content="width=device-width, initial-scale=1">' . $style_tag;
		}
		if ( '' !== $style_tag && false !== stripos( $html, '</head>' ) ) {
			$html = preg_replace( '/<\/head>/i', $style_tag . '</head>', $html, 1 );
		} elseif ( '' !== $style_tag ) {
			$html = $style_tag . $html;
		}
		if ( preg_match( '/<html\b/i', $html ) && false === stripos( $html, 'lang=' ) ) {
			$html = preg_replace( '/<html\b([^>]*)>/i', '<html$1 lang="' . esc_attr( $lang ) . '">', $html, 1 );
		}
		if ( $is_central_remote && '' !== $body_class && false !== stripos( $html, '<body' ) && false === stripos( $html, $body_class ) ) {
			$html = preg_replace_callback(
				'/<body\b([^>]*)>/i',
				static function ( array $matches ) use ( $body_class ): string {
					$attrs = $matches[1] ?? '';
					if ( preg_match( '/\bclass\s*=\s*(["\'])([^"\']*)\1/i', $attrs, $class_matches ) ) {
						$replacement = 'class=' . $class_matches[1] . trim( $class_matches[2] . ' ' . $body_class ) . $class_matches[1];
						$attrs       = preg_replace( '/\bclass\s*=\s*(["\'])([^"\']*)\1/i', $replacement, $attrs, 1 );
						return '<body' . $attrs . '>';
					}
					return '<body' . $attrs . ' class="' . esc_attr( $body_class ) . '">';
				},
				$html,
				1
			);
		}
		if ( ! $is_central_remote && false !== stripos( $html, '<body' ) && false === stripos( $html, 'relr-receiver-shell' ) ) {
			$html = preg_replace( '/<body\b([^>]*)>/i', '<body$1><div class="' . esc_attr( $shell_class ) . '">', $html, 1 );
			$html = preg_replace( '/<\/body>/i', '</div></body>', $html, 1 );
		}
		return $html;
	}

	private static function wrap_fragment_in_document( string $title, string $html, string $lang, string $css ): string {
		$charset = get_bloginfo( 'charset' );
		$title   = esc_html( $title );
		$lang    = esc_attr( $lang ?: 'de' );
		$is_central_remote = self::looks_like_central_remote_fragment( $html );
		$body_class        = $is_central_remote ? self::central_remote_body_class( $html ) : '';
		if ( $is_central_remote ) {
			return '<!doctype html>'
				. '<html lang="' . $lang . '">'
				. '<head>'
				. '<meta charset="' . esc_attr( $charset ) . '">'
				. '<meta name="viewport" content="width=device-width, initial-scale=1">'
				. '<meta name="robots" content="noindex,nofollow,noarchive">'
				. '<title>' . $title . '</title>'
				. '</head>'
				. '<body' . ( '' !== $body_class ? ' class="' . esc_attr( $body_class ) . '"' : '' ) . '>'
				. $html
				. '</body>'
				. '</html>';
		}

		$wrapper_class     = $is_central_remote ? 'relr-receiver-fragment' : 'relr-receiver-label';
		$shell_class       = $is_central_remote ? 'relr-receiver-shell relr-receiver-shell--passthrough' : 'relr-receiver-shell';

		return '<!doctype html>'
			. '<html lang="' . $lang . '">'
			. '<head>'
			. '<meta charset="' . esc_attr( $charset ) . '">'
			. '<meta name="viewport" content="width=device-width, initial-scale=1">'
			. '<meta name="robots" content="noindex,nofollow,noarchive">'
			. '<title>' . $title . '</title>'
			. '<style id="relr-receiver-design">' . $css . '</style>'
			. '</head>'
			. '<body><div class="' . esc_attr( $shell_class ) . '"><div class="' . esc_attr( $wrapper_class ) . '">'
			. $html
			. '</div></div></body>'
			. '</html>';
	}

	private static function find_post_id_by_source( array $source ): int {
		$source = self::sanitize_source_meta( $source );
		if ( array() === $source || empty( $source['product_id'] ) ) {
			return 0;
		}

		$candidates = get_posts(
			array(
				'post_type'      => self::CPT,
				'post_status'    => 'any',
				'posts_per_page' => 10,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'     => self::META_SOURCE,
						'value'   => '"product_id";i:' . (int) $source['product_id'] . ';',
						'compare' => 'LIKE',
					),
				),
			)
		);

		if ( empty( $candidates ) ) {
			return 0;
		}

		$expected_site = untrailingslashit( (string) ( $source['site'] ?? '' ) );
		$expected_type = (string) ( $source['type'] ?? '' );

		foreach ( $candidates as $candidate_id ) {
			$candidate_source = self::source_meta_for_post( (int) $candidate_id );
			if ( (int) ( $candidate_source['product_id'] ?? 0 ) !== (int) $source['product_id'] ) {
				continue;
			}
			if ( '' !== $expected_type && (string) ( $candidate_source['type'] ?? '' ) !== $expected_type ) {
				continue;
			}
			if ( '' !== $expected_site && untrailingslashit( (string) ( $candidate_source['site'] ?? '' ) ) !== $expected_site ) {
				continue;
			}
			return (int) $candidate_id;
		}

		return 0;
	}

	private static function find_post_id_by_slug( string $slug ): int {
		$posts = get_posts(
			array(
				'name'           => $slug,
				'post_type'      => self::CPT,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		return isset( $posts[0] ) ? (int) $posts[0] : 0;
	}

	private static function normalize_status( string $status ): string {
		$allowed = array( 'publish', 'draft', 'private' );
		$status  = strtolower( trim( $status ) );
		return in_array( $status, $allowed, true ) ? $status : 'publish';
	}

	private static function looks_like_full_html_document( string $html ): bool {
		return (bool) preg_match( '/<!doctype\\s+html/i', $html ) || (bool) preg_match( '/<html\\b/i', $html );
	}

	private static function looks_like_central_remote_fragment( string $html ): bool {
		return false !== stripos( $html, 'nler-page-shell' )
			|| false !== stripos( $html, 'nler-remote' )
			|| false !== stripos( $html, 'wel-page-shell' );
	}

	private static function central_remote_body_class( string $html ): string {
		if ( false !== stripos( $html, 'nler-page-shell' ) || false !== stripos( $html, 'nler-remote' ) ) {
			return 'nler-label-body';
		}
		if ( false !== stripos( $html, 'wel-page-shell' ) ) {
			return 'wel-label-body';
		}
		return '';
	}
}

class_alias( 'Wine_E_Label_Receiver', 'Reith_ELabel_Receiver' );

Wine_E_Label_Receiver::init();
register_activation_hook( __FILE__, array( 'Wine_E_Label_Receiver', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Wine_E_Label_Receiver', 'deactivate' ) );
