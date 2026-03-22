<?php
if (!defined('ABSPATH')) { exit; }

class NutritionLabels_Admin_I18n
{
    public static function init(): void
    {
        if (!(is_admin() || wp_doing_ajax())) {
            return;
        }
        add_filter('gettext', [__CLASS__, 'filter_gettext'], 20, 3);
        add_filter('ngettext', [__CLASS__, 'filter_ngettext'], 20, 5);
        add_filter('plugin_action_links_' . WINE_E_LABEL_PLUGIN_BASENAME, [__CLASS__, 'plugin_action_links']);
    }

    public static function plugin_action_links(array $links): array
    {
        $url = admin_url('admin.php?page=' . WINE_E_LABEL_ADMIN_PAGE_MAIN);
        $links[] = '<a href="' . esc_url($url) . '">' . esc_html(self::translate('Wein E-Label Einstellungen')) . '</a>';
        return $links;
    }

    public static function get_current_language(): string
    {
        $lang = (string) get_option('nutrition_labels_admin_language', 'auto');
        if (in_array($lang, ['de', 'en', 'fr', 'it'], true)) {
            return $lang;
        }

        $locale = function_exists('determine_locale') ? (string) determine_locale() : (function_exists('get_user_locale') ? (string) get_user_locale() : (string) get_locale());
        $locale = strtolower($locale);
        if (strpos($locale, 'fr') === 0) {
            return 'fr';
        }
        if (strpos($locale, 'it') === 0) {
            return 'it';
        }
        if (strpos($locale, 'en') === 0) {
            return 'en';
        }
        return 'de';
    }

    public static function get_language_options(): array
    {
        return [
            'auto' => self::translate('Automatisch (WordPress)', self::get_current_language()),
            'de' => 'Deutsch',
            'en' => 'English',
            'fr' => 'Français',
            'it' => 'Italiano',
        ];
    }

    public static function filter_gettext($translated, $text, $domain)
    {
        if (!self::supports_text_domain((string) $domain)) {
            return $translated;
        }
        return self::translate((string) $text);
    }

    public static function filter_ngettext($translated, $single, $plural, $number, $domain)
    {
        if (!self::supports_text_domain((string) $domain)) {
            return $translated;
        }
        $text = ((int) $number === 1) ? (string) $single : (string) $plural;
        return self::translate($text);
    }

    private static function supports_text_domain(string $domain): bool
    {
        return in_array($domain, [WINE_E_LABEL_TEXT_DOMAIN, WINE_E_LABEL_LEGACY_TEXT_DOMAIN], true);
    }

    public static function translate(string $text, ?string $lang = null): string
    {
        $lang = $lang ?: self::get_current_language();
        $map = self::translations();
        if (isset($map[$text][$lang]) && $map[$text][$lang] !== '') {
            return $map[$text][$lang];
        }
        foreach (self::fallback_languages($lang) as $fallback) {
            if ($fallback === $lang) {
                continue;
            }
            if (isset($map[$text][$fallback]) && $map[$text][$fallback] !== '') {
                return $map[$text][$fallback];
            }
        }
        return $text;
    }

    private static function fallback_languages(string $lang): array
    {
        $orders = [
            'de' => ['en', 'fr', 'it'],
            'en' => ['de', 'fr', 'it'],
            'fr' => ['en', 'de', 'it'],
            'it' => ['en', 'de', 'fr'],
        ];
        return $orders[$lang] ?? ['en', 'de', 'fr', 'it'];
    }

    private static function translations(): array
    {
        static $translations = null;
        if ($translations !== null) {
            return $translations;
        }
        $translations = [

            'WordPress-Funktionen sind nicht verfügbar. Bitte Administrator kontaktieren.' => ['de' => 'WordPress-Funktionen sind nicht verfügbar. Bitte Administrator kontaktieren.', 'en' => 'WordPress functions are not available. Please contact the administrator.', 'fr' => 'Les fonctions WordPress ne sont pas disponibles. Veuillez contacter l’administrateur.', 'it' => 'Le funzioni di WordPress non sono disponibili. Contatta l’amministratore.'],
            'Rewrite-Regeln aktualisieren' => ['de' => 'Rewrite-Regeln aktualisieren', 'en' => 'Refresh rewrite rules', 'fr' => 'Actualiser les règles de réécriture', 'it' => 'Aggiorna le regole di riscrittura'],
            'Wenn Short-URLs nicht funktionieren (404-Fehler), kannst du hier die WordPress-Rewrite-Regeln neu aufbauen.' => ['de' => 'Wenn Short-URLs nicht funktionieren (404-Fehler), kannst du hier die WordPress-Rewrite-Regeln neu aufbauen.', 'en' => 'If short URLs are not working (404 errors), you can rebuild the WordPress rewrite rules here.', 'fr' => 'Si les URL courtes ne fonctionnent pas (erreurs 404), vous pouvez reconstruire ici les règles de réécriture de WordPress.', 'it' => 'Se gli URL brevi non funzionano (errore 404), puoi ricostruire qui le regole di riscrittura di WordPress.'],
            'Standardsprache' => ['de' => 'Standardsprache', 'en' => 'Default language', 'fr' => 'Langue par défaut', 'it' => 'Lingua predefinita'],
            'Lösche Ausgewählte' => ['de' => 'Lösche Ausgewählte', 'en' => 'Delete selected', 'fr' => 'Supprimer la sélection', 'it' => 'Elimina selezionati'],
            'Erfolgreich %d E-Label-Eintrag gelöscht' => ['de' => 'Erfolgreich %d E-Label-Eintrag gelöscht', 'en' => 'Successfully deleted %d e-label entry', 'fr' => '%d entrée e-label supprimée avec succès', 'it' => 'Voce e-label %d eliminata con successo'],
            'Erfolgreich %d E-Label-Einträge gelöscht' => ['de' => 'Erfolgreich %d E-Label-Einträge gelöscht', 'en' => 'Successfully deleted %d e-label entries', 'fr' => '%d entrées e-label supprimées avec succès', 'it' => '%d voci e-label eliminate con successo'],
            'Bitte mindestens einen Eintrag auswählen.' => ['de' => 'Bitte mindestens einen Eintrag auswählen.', 'en' => 'Please select at least one entry.', 'fr' => 'Veuillez sélectionner au moins une entrée.', 'it' => 'Seleziona almeno una voce.'],
            'Du hast keine Berechtigung für den CSV-Export.' => ['de' => 'Du hast keine Berechtigung für den CSV-Export.', 'en' => 'You do not have permission to export CSV.', 'fr' => 'Vous n’avez pas l’autorisation d’exporter en CSV.', 'it' => 'Non hai il permesso di esportare il CSV.'],
            'Du hast keine Berechtigung, Einstellungen zu ändern.' => ['de' => 'Du hast keine Berechtigung, Einstellungen zu ändern.', 'en' => 'You do not have permission to change settings.', 'fr' => 'Vous n’avez pas l’autorisation de modifier les réglages.', 'it' => 'Non hai il permesso di modificare le impostazioni.'],
            'Wein E-Label Einstellungen' => ['de' => 'Wein E-Label Einstellungen', 'en' => 'Wine E-Label Settings', 'fr' => 'Paramètres Wine E-Label', 'it' => 'Impostazioni Wine E-Label'],
            'Wein E-Label' => ['de' => 'Wein E-Label', 'en' => 'Wine E-Label', 'fr' => 'Wine E-Label', 'it' => 'Wine E-Label'],
            'Datenbankverwaltung' => ['de' => 'Datenbankverwaltung', 'en' => 'Database Management', 'fr' => 'Gestion de la base de données', 'it' => 'Gestione database'],
            'Plugin-Einstellungen konfigurieren und QR-Code-Erzeugung verwalten.' => ['de' => 'Plugin-Einstellungen konfigurieren und QR-Code-Erzeugung verwalten.', 'en' => 'Configure plugin settings and manage QR code generation.', 'fr' => 'Configurez les paramètres du plugin et gérez la génération des codes QR.', 'it' => 'Configura le impostazioni del plugin e gestisci la generazione dei codici QR.'],
            'Einstellungen' => ['de' => 'Einstellungen', 'en' => 'Settings', 'fr' => 'Réglages', 'it' => 'Impostazioni'],
            'Allgemein' => ['de' => 'Allgemein', 'en' => 'General', 'fr' => 'Général', 'it' => 'Generale'],
            'Sprache' => ['de' => 'Sprache', 'en' => 'Language', 'fr' => 'Langue', 'it' => 'Lingua'],
            'Einrichtung' => ['de' => 'Einrichtung', 'en' => 'Setup', 'fr' => 'Configuration', 'it' => 'Configurazione'],
            'Werkzeugsprache' => ['de' => 'Werkzeugsprache', 'en' => 'Tool language', 'fr' => 'Langue de l’outil', 'it' => 'Lingua dello strumento'],
            'Wähle die Sprache für die Plugin-Oberfläche im WordPress-Adminbereich.' => ['de' => 'Wähle die Sprache für die Plugin-Oberfläche im WordPress-Adminbereich.', 'en' => 'Choose the language for the plugin interface in the WordPress admin area.', 'fr' => 'Choisissez la langue de l’interface du plugin dans l’administration WordPress.', 'it' => 'Scegli la lingua dell’interfaccia del plugin nell’area amministrativa di WordPress.'],
            'Einstellungen speichern' => ['de' => 'Einstellungen speichern', 'en' => 'Save settings', 'fr' => 'Enregistrer les réglages', 'it' => 'Salva impostazioni'],
            'QR-Code-Größe' => ['de' => 'QR-Code-Größe', 'en' => 'QR code size', 'fr' => 'Taille du code QR', 'it' => 'Dimensione del codice QR'],
            'QR-Code-Format' => ['de' => 'QR-Code-Format', 'en' => 'QR code format', 'fr' => 'Format du code QR', 'it' => 'Formato del codice QR'],
            'Fehlerkorrektur' => ['de' => 'Fehlerkorrektur', 'en' => 'Error correction', 'fr' => 'Correction d’erreurs', 'it' => 'Correzione degli errori'],
            'E-Label-Basis-URL' => ['de' => 'E-Label-Basis-URL', 'en' => 'E-label base URL', 'fr' => 'URL de base de l’e-label', 'it' => 'URL base dell’e-label'],
            'Optional override for the public e-label target URL. Leave empty to use this WordPress domain automatically. The product slug is appended automatically.' => ['de' => 'Optionale Überschreibung für die öffentliche E-Label-Ziel-URL. Leer lassen, um automatisch diese WordPress-Domain zu verwenden. Der Produkt-Slug wird automatisch angehängt.', 'en' => 'Optional override for the public e-label target URL. Leave empty to use this WordPress domain automatically. The product slug is appended automatically.', 'fr' => 'Remplacement facultatif de l’URL publique cible de l’e-label. Laissez vide pour utiliser automatiquement ce domaine WordPress. Le slug produit est ajouté automatiquement.', 'it' => 'Override opzionale dell’URL pubblica di destinazione dell’e-label. Lascia vuoto per usare automaticamente questo dominio WordPress. Lo slug del prodotto viene aggiunto automaticamente.'],
            'The local WordPress route always stays on /l/[slug] for this installation. This field only changes the public target URL and does not rewrite normal WordPress pages.' => ['de' => 'Die lokale WordPress-Route bleibt in dieser Installation immer auf /l/[slug]. Dieses Feld ändert nur die öffentliche Ziel-URL und schreibt normale WordPress-Seiten nicht um.', 'en' => 'The local WordPress route always stays on /l/[slug] for this installation. This field only changes the public target URL and does not rewrite normal WordPress pages.', 'fr' => 'La route locale WordPress reste toujours sur /l/[slug] pour cette installation. Ce champ modifie uniquement l’URL publique cible et ne réécrit pas les pages WordPress normales.', 'it' => 'La route locale di WordPress resta sempre su /l/[slug] per questa installazione. Questo campo cambia solo l’URL pubblica di destinazione e non riscrive le normali pagine WordPress.'],
            'Subdomain E-Label URLs' => ['de' => 'Subdomain E-Label URLs', 'en' => 'Subdomain e-label URLs', 'fr' => 'URLs d’e-label en sous-domaine', 'it' => 'URL e-label su sottodominio'],
            'Enable subdomain e-label URLs' => ['de' => 'Enable subdomain e-label URLs', 'en' => 'Enable subdomain e-label URLs', 'fr' => 'Activer les URLs d’e-label en sous-domaine', 'it' => 'Attiva gli URL e-label su sottodominio'],
            'Serve e-labels from a dedicated subdomain as recommended by EU e-labelling regulations.' => ['de' => 'Serve e-labels from a dedicated subdomain as recommended by EU e-labelling regulations.', 'en' => 'Serve e-labels from a dedicated subdomain as recommended by EU e-labelling regulations.', 'fr' => 'Diffuser les e-labels depuis un sous-domaine dédié comme recommandé par la réglementation européenne.'],
            'Dedicated subdomains are handled separately. The main domain keeps its normal WordPress pages untouched, while local labels still remain available on /l/[slug].' => ['de' => 'Dedizierte Subdomains werden separat behandelt. Die Hauptdomain behält ihre normalen WordPress-Seiten unverändert, während lokale Labels weiterhin unter /l/[slug] verfügbar bleiben.', 'en' => 'Dedicated subdomains are handled separately. The main domain keeps its normal WordPress pages untouched, while local labels still remain available on /l/[slug].', 'fr' => 'Les sous-domaines dédiés sont gérés séparément. Le domaine principal conserve ses pages WordPress normales inchangées, tandis que les e-labels locaux restent disponibles sous /l/[slug].', 'it' => 'I sottodomini dedicati vengono gestiti separatamente. Il dominio principale mantiene intatte le sue normali pagine WordPress, mentre le e-label locali restano disponibili su /l/[slug].'],
            'Subdomain' => ['de' => 'Subdomain', 'en' => 'Subdomain', 'fr' => 'Sous-domaine'],
            'Do not use a root URL here to replace normal WordPress page slugs. The plugin keeps local label routing on /l/[slug] and only uses the dedicated host for explicit e-label requests.' => ['de' => 'Verwende hier keine Root-URL, um normale WordPress-Seiten-Slugs zu ersetzen. Das Plugin belässt das lokale Label-Routing auf /l/[slug] und nutzt den dedizierten Host nur für explizite E-Label-Anfragen.', 'en' => 'Do not use a root URL here to replace normal WordPress page slugs. The plugin keeps local label routing on /l/[slug] and only uses the dedicated host for explicit e-label requests.', 'fr' => 'N’utilisez pas ici une URL racine pour remplacer les slugs normaux des pages WordPress. Le plugin conserve le routage local des labels sur /l/[slug] et n’utilise l’hôte dédié que pour les demandes explicites d’e-label.', 'it' => 'Non usare qui un URL di root per sostituire gli slug normali delle pagine WordPress. Il plugin mantiene il routing locale delle etichette su /l/[slug] e usa l’host dedicato solo per richieste esplicite di e-label.'],
            'URL Scheme' => ['de' => 'URL Scheme', 'en' => 'URL scheme', 'fr' => 'Schéma d’URL', 'it' => 'Schema URL'],
            'https (recommended)' => ['de' => 'https (recommended)', 'en' => 'https (recommended)', 'fr' => 'https (recommandé)'],
            'Delete Data on Uninstall' => ['de' => 'Delete Data on Uninstall', 'en' => 'Delete data on uninstall', 'fr' => 'Supprimer les données lors de la désinstallation', 'it' => 'Elimina i dati alla disinstallazione'],
            'Externe E-Label-Domain (REST API)' => ['de' => 'Externe E-Label-Domain (REST API)', 'en' => 'External e-label domain (REST API)', 'fr' => 'Domaine externe d’e-label (API REST)'],
            'Binde optional eine separate WordPress-Domain an, auf der E-Label-Seiten per REST API getrennt von der Hauptseite veröffentlicht werden können.' => ['de' => 'Binde optional eine separate WordPress-Domain an, auf der E-Label-Seiten per REST API getrennt von der Hauptseite veröffentlicht werden können.', 'en' => 'Optionally connect a separate WordPress domain where e-label pages can be published via REST API separately from the main site.', 'fr' => 'Connectez si besoin un domaine WordPress séparé sur lequel les pages d’e-label peuvent être publiées via l’API REST indépendamment du site principal.'],
            'REST API aktivieren' => ['de' => 'REST API aktivieren', 'en' => 'Enable REST API', 'fr' => 'Activer l’API REST'],
            'Externe E-Label-Domain per REST API verwenden' => ['de' => 'Externe E-Label-Domain per REST API verwenden', 'en' => 'Use external e-label domain via REST API', 'fr' => 'Utiliser un domaine externe d’e-label via l’API REST'],
            'REST API Ziel-URL' => ['de' => 'REST API Ziel-URL', 'en' => 'REST API target URL', 'fr' => 'URL cible de l’API REST'],
            'Vollständige Basis-URL der externen WordPress-Seite, z. B. https://reith-label.de' => ['de' => 'Vollständige Basis-URL der externen WordPress-Seite, z. B. https://reith-label.de', 'en' => 'Full base URL of the external WordPress site, e.g. https://reith-label.de', 'fr' => 'URL de base complète du site WordPress externe, par ex. https://reith-label.de'],
            'Es wird nur die Basisdomain gespeichert, ohne /wp-json/wp/v2/pages am Ende.' => ['de' => 'Es wird nur die Basisdomain gespeichert, ohne /wp-json/wp/v2/pages am Ende.', 'en' => 'Only the base domain is stored, without /wp-json/wp/v2/pages at the end.', 'fr' => 'Seul le domaine de base est enregistré, sans /wp-json/wp/v2/pages à la fin.'],
            'REST API Benutzername' => ['de' => 'REST API Benutzername', 'en' => 'REST API username', 'fr' => 'Nom d’utilisateur API REST'],
            'WordPress-Benutzername der externen E-Label-Seite.' => ['de' => 'WordPress-Benutzername der externen E-Label-Seite.', 'en' => 'WordPress username of the external e-label site.', 'fr' => 'Nom d’utilisateur WordPress du site externe d’e-label.'],
            'REST API Application Password' => ['de' => 'REST API Application Password', 'en' => 'REST API application password', 'fr' => 'Mot de passe d’application API REST'],
            'Application Password des WordPress-Benutzers auf der externen E-Label-Seite.' => ['de' => 'Application Password des WordPress-Benutzers auf der externen E-Label-Seite.', 'en' => 'Application Password of the WordPress user on the external e-label site.', 'fr' => 'Mot de passe d’application de l’utilisateur WordPress sur le site externe d’e-label.'],
            'Leer lassen, um das bereits gespeicherte Passwort beizubehalten.' => ['de' => 'Leer lassen, um das bereits gespeicherte Passwort beizubehalten.', 'en' => 'Leave empty to keep the password already stored.', 'fr' => 'Laissez vide pour conserver le mot de passe déjà enregistré.'],
            'Anzeigen' => ['de' => 'Anzeigen', 'en' => 'Show', 'fr' => 'Afficher', 'it' => 'Mostra'],
            'Verbergen' => ['de' => 'Verbergen', 'en' => 'Hide', 'fr' => 'Masquer', 'it' => 'Nascondi'],
            'Verbindung testen' => ['de' => 'Verbindung testen', 'en' => 'Test connection', 'fr' => 'Tester la connexion'],
            'So richtest du die Verbindung ein' => ['de' => 'So richtest du die Verbindung ein', 'en' => 'How to set up the connection', 'fr' => 'Comment configurer la connexion'],
            'Auf example.com einen Benutzer anlegen, z. B. api_elabel' => ['de' => 'Auf example.com einen Benutzer anlegen, z. B. api_elabel', 'en' => 'Create a user on example.com, for example api_elabel', 'fr' => 'Créer un utilisateur sur example.com, par exemple api_elabel'],
            'Unter Benutzer > Profil ein Application Password erzeugen' => ['de' => 'Unter Benutzer > Profil ein Application Password erzeugen', 'en' => 'Create an Application Password under Users > Profile', 'fr' => 'Créer un mot de passe d’application sous Utilisateurs > Profil'],
            'Von example.com Benutzername und Application Password hier eingeben' => ['de' => 'Von example.com Benutzername und Application Password hier eingeben', 'en' => 'Enter the username and application password from example.com here', 'fr' => 'Saisissez ici le nom d’utilisateur et le mot de passe d’application de example.com'],
            'Bitte zuerst eine gültige REST API Ziel-URL eingeben.' => ['de' => 'Bitte zuerst eine gültige REST API Ziel-URL eingeben.', 'en' => 'Please enter a valid REST API target URL first.', 'fr' => 'Veuillez d’abord saisir une URL cible valide pour l’API REST.'],
            'Verbindung fehlgeschlagen: %s' => ['de' => 'Verbindung fehlgeschlagen: %s', 'en' => 'Connection failed: %s', 'fr' => 'Connexion échouée : %s'],
            'REST API antwortet nicht wie erwartet (HTTP %d).' => ['de' => 'REST API antwortet nicht wie erwartet (HTTP %d).', 'en' => 'REST API did not respond as expected (HTTP %d).', 'fr' => 'L’API REST n’a pas répondu comme prévu (HTTP %d).'],
            'REST API erreichbar.' => ['de' => 'REST API erreichbar.', 'en' => 'REST API reachable.', 'fr' => 'API REST accessible.'],
            'Authentifizierung konnte nicht geprüft werden: %s' => ['de' => 'Authentifizierung konnte nicht geprüft werden: %s', 'en' => 'Authentication could not be verified: %s', 'fr' => 'L’authentification n’a pas pu être vérifiée : %s'],
            'Authentifizierung erfolgreich geprüft.' => ['de' => 'Authentifizierung erfolgreich geprüft.', 'en' => 'Authentication verified successfully.', 'fr' => 'Authentification vérifiée avec succès.'],
            'Authentifizierung fehlgeschlagen (HTTP %d).' => ['de' => 'Authentifizierung fehlgeschlagen (HTTP %d).', 'en' => 'Authentication failed (HTTP %d).', 'fr' => 'Échec de l’authentification (HTTP %d).'],
            'Verbindung teilweise geprüft.' => ['de' => 'Verbindung teilweise geprüft.', 'en' => 'Connection partially checked.', 'fr' => 'Connexion vérifiée partiellement.'],
            'REST API erreichbar, aber Authentifizierung fehlgeschlagen (HTTP %d).' => ['de' => 'REST API erreichbar, aber Authentifizierung fehlgeschlagen (HTTP %d).', 'en' => 'REST API reachable, but authentication failed (HTTP %d).', 'fr' => 'API REST accessible, mais authentification échouée (HTTP %d).'],
            'Verbindung wird geprüft …' => ['de' => 'Verbindung wird geprüft …', 'en' => 'Checking connection …', 'fr' => 'Vérification de la connexion …'],
            'Verbindung erfolgreich geprüft.' => ['de' => 'Verbindung erfolgreich geprüft.', 'en' => 'Connection checked successfully.', 'fr' => 'Connexion vérifiée avec succès.'],
            'Verbindung konnte nicht geprüft werden.' => ['de' => 'Verbindung konnte nicht geprüft werden.', 'en' => 'Connection could not be checked.', 'fr' => 'La connexion n’a pas pu être vérifiée.'],
            'Externe E-Label-Seite konnte nicht erzeugt werden.' => ['de' => 'Externe E-Label-Seite konnte nicht erzeugt werden.', 'en' => 'External e-label page could not be generated.', 'fr' => 'La page e-label externe n’a pas pu être générée.'],
            'Externe E-Label-Seite konnte nicht erstellt werden (HTTP %d).' => ['de' => 'Externe E-Label-Seite konnte nicht erstellt werden (HTTP %d).', 'en' => 'External e-label page could not be created (HTTP %d).', 'fr' => 'La page e-label externe n’a pas pu être créée (HTTP %d).'],
            'Externe E-Label-Seite konnte nicht erstellt werden: %s' => ['de' => 'Externe E-Label-Seite konnte nicht erstellt werden: %s', 'en' => 'External e-label page could not be created: %s', 'fr' => 'La page e-label externe n’a pas pu être créée : %s'],
            'Externe E-Label-Seite konnte nicht aufgebaut werden.' => ['de' => 'Externe E-Label-Seite konnte nicht aufgebaut werden.', 'en' => 'External e-label page could not be built.', 'fr' => 'La page e-label externe n’a pas pu être générée.'],
            'REST-API-Verbindung ist aktiviert, aber Ziel-URL, Benutzername oder Application Password fehlen.' => ['de' => 'REST-API-Verbindung ist aktiviert, aber Ziel-URL, Benutzername oder Application Password fehlen.', 'en' => 'REST API connection is enabled, but target URL, username or application password are missing.', 'fr' => 'La connexion API REST est activée, mais l’URL cible, le nom d’utilisateur ou le mot de passe d’application sont manquants.'],
            'Permanently delete all nutrition label data when the plugin is uninstalled' => ['de' => 'Permanently delete all nutrition label data when the plugin is uninstalled', 'en' => 'Permanently delete all nutrition label data when the plugin is uninstalled', 'fr' => 'Supprimer définitivement toutes les données d’e-label lors de la désinstallation du plugin'],
            'Warning: enabling this will drop the database table and all nutrition label records when the plugin is deleted. This cannot be undone.' => ['de' => 'Warnung: Wenn diese Option aktiv ist, werden beim Löschen des Plugins auch die Datenbanktabelle und alle E-Label-Einträge entfernt. Das kann nicht rückgängig gemacht werden.', 'en' => 'Warning: enabling this will drop the database table and all nutrition label records when the plugin is deleted. This cannot be undone.', 'fr' => 'Attention : cette option supprimera la table de base de données et tous les enregistrements d’e-label lors de la suppression du plugin. Cette action est irréversible.'],
            'Information' => ['de' => 'Information', 'en' => 'Information', 'fr' => 'Informations'],
            'Current Entries:' => ['de' => 'Current Entries:', 'en' => 'Current entries:', 'fr' => 'Entrées actuelles :'],
            '%d nutrition labels active' => ['de' => '%d nutrition labels active', 'en' => '%d nutrition labels active', 'fr' => '%d e-labels actifs'],
            'Plug-in-Oberfläche' => ['de' => 'Plug-in-Oberfläche', 'en' => 'Plugin interface', 'fr' => 'Interface du plugin'],
            'Du hast keine Berechtigung, auf diese Seite zuzugreifen.' => ['de' => 'Du hast keine Berechtigung, auf diese Seite zuzugreifen.', 'en' => 'You do not have permission to access this page.', 'fr' => 'Vous n’avez pas l’autorisation d’accéder à cette page.'],
            'Wein E-Label – Datenbankverwaltung' => ['de' => 'Wein E-Label – Datenbankverwaltung', 'en' => 'Wine E-Label – Database Management', 'fr' => 'Wine E-Label – Gestion de la base de données', 'it' => 'Wine E-Label – Gestione database'],
            'Produktname oder Slug suchen …' => ['de' => 'Produktname oder Slug suchen …', 'en' => 'Search product name or slug …', 'fr' => 'Rechercher le nom du produit ou le slug …'],
            'Alle Status' => ['de' => 'Alle Status', 'en' => 'All statuses', 'fr' => 'Tous les statuts'],
            'E-Label erstellt' => ['de' => 'E-Label erstellt', 'en' => 'E-label created', 'fr' => 'E-label créé'],
            'Nur Import vorhanden' => ['de' => 'Nur Import vorhanden', 'en' => 'Import only available', 'fr' => 'Import disponible uniquement'],
            'Nur manuelle Daten' => ['de' => 'Nur manuelle Daten', 'en' => 'Manual data only', 'fr' => 'Données manuelles uniquement'],
            'Unvollständig' => ['de' => 'Unvollständig', 'en' => 'Incomplete', 'fr' => 'Incomplet'],
            'Alle Jahrgänge' => ['de' => 'Alle Jahrgänge', 'en' => 'All vintages', 'fr' => 'Tous les millésimes'],
            'Filtern' => ['de' => 'Filtern', 'en' => 'Filter', 'fr' => 'Filtrer'],
            'Zurücksetzen' => ['de' => 'Zurücksetzen', 'en' => 'Reset', 'fr' => 'Réinitialiser'],
            'Keine E-Label-Einträge gefunden.' => ['de' => 'Keine E-Label-Einträge gefunden.', 'en' => 'No e-label entries found.', 'fr' => 'Aucune entrée d’e-label trouvée.'],
            'Zeige %1$d von %2$d Einträgen' => ['de' => 'Zeige %1$d von %2$d Einträgen', 'en' => 'Showing %1$d of %2$d entries', 'fr' => 'Affichage de %1$d sur %2$d entrées'],
            'Alle auswählen' => ['de' => 'Alle auswählen', 'en' => 'Select all', 'fr' => 'Tout sélectionner'],
            'Produkt' => ['de' => 'Produkt', 'en' => 'Product', 'fr' => 'Produit'],
            'Short Code' => ['de' => 'Short Code', 'en' => 'Short code', 'fr' => 'Code court'],
            'Jahrgang' => ['de' => 'Jahrgang', 'en' => 'Vintage', 'fr' => 'Millésime'],
            'Status' => ['de' => 'Status', 'en' => 'Status', 'fr' => 'Statut'],
            'Erstellt' => ['de' => 'Erstellt', 'en' => 'Created', 'fr' => 'Créé'],
            'Aktionen' => ['de' => 'Aktionen', 'en' => 'Actions', 'fr' => 'Actions'],
            'QR-Export' => ['de' => 'QR-Export', 'en' => 'QR export', 'fr' => 'Export QR'],
            'Auswählen' => ['de' => 'Auswählen', 'en' => 'Select', 'fr' => 'Sélectionner'],
            'E-Label öffnen' => ['de' => 'E-Label öffnen', 'en' => 'Open e-label', 'fr' => 'Ouvrir l’e-label'],
            'QR herunterladen' => ['de' => 'QR herunterladen', 'en' => 'Download QR', 'fr' => 'Télécharger le QR'],
            'Löschen' => ['de' => 'Löschen', 'en' => 'Delete', 'fr' => 'Supprimer'],
            'Delete' => ['de' => 'Delete', 'en' => 'Delete', 'fr' => 'Supprimer'],
            'Lösche Auswählened' => ['de' => 'Lösche Ausgewählte', 'en' => 'Delete selected', 'fr' => 'Supprimer la sélection', 'it' => 'Elimina selezionati'],
            'QR für Auswahl herunterladen' => ['de' => 'QR für Auswahl herunterladen', 'en' => 'Download QR for selection', 'fr' => 'Télécharger les QR pour la sélection'],
            'Als CSV exportieren' => ['de' => 'Als CSV exportieren', 'en' => 'Export as CSV', 'fr' => 'Exporter en CSV'],
            'Erzeuge…' => ['de' => 'Erzeuge…', 'en' => 'Generating…', 'fr' => 'Génération…'],
            'Fehler:' => ['de' => 'Fehler:', 'en' => 'Error:', 'fr' => 'Erreur :'],
            'QR-Code konnte nicht erzeugt werden' => ['de' => 'QR-Code konnte nicht erzeugt werden', 'en' => 'QR code could not be generated', 'fr' => 'Le code QR n’a pas pu être généré'],
            'Fehler: Could not generate QR code' => ['de' => 'Fehler: QR-Code konnte nicht erzeugt werden.', 'en' => 'Error: Could not generate QR code', 'fr' => 'Erreur : impossible de générer le code QR'],
            'Bitte mindestens einen Eintrag zum Löschen auswählen' => ['de' => 'Bitte mindestens einen Eintrag zum Löschen auswählen', 'en' => 'Please select at least one entry to delete', 'fr' => 'Veuillez sélectionner au moins une entrée à supprimer'],
            'Bitte mindestens einen Eintrag für den QR-Download auswählen' => ['de' => 'Bitte mindestens einen Eintrag für den QR-Download auswählen', 'en' => 'Please select at least one entry for QR download', 'fr' => 'Veuillez sélectionner au moins une entrée pour le téléchargement QR'],
            'Lösche nutrition label entry?

Produkt will NOT be deleted - only the nutrition label data will be removed.

This cannot be undone.' => ['de' => 'Lösche nutrition label entry?

Produkt will NOT be deleted - only the nutrition label data will be removed.

This cannot be undone.', 'en' => 'Delete nutrition label entry?

The product will NOT be deleted - only the nutrition label data will be removed.

This cannot be undone.', 'fr' => 'Supprimer l’entrée de l’e-label ?

Le produit ne sera PAS supprimé - seules les données de l’e-label seront supprimées.

Cette action est irréversible.'],
            'Lösche ' => ['de' => 'Lösche ', 'en' => 'Delete ', 'fr' => 'Supprimer '],
            ' nutrition label entries?

Produkts will NOT be deleted - only the nutrition label data will be removed.

This cannot be undone.' => ['de' => ' nutrition label entries?

Produkts will NOT be deleted - only the nutrition label data will be removed.

This cannot be undone.', 'en' => ' nutrition label entries?

Products will NOT be deleted - only the nutrition label data will be removed.

This cannot be undone.', 'fr' => ' entrées d’e-label ?

Les produits ne seront PAS supprimés - seules les données des e-labels seront supprimées.

Cette action est irréversible.'],
            'Fehler: Could not delete entry' => ['de' => 'Fehler: Eintrag konnte nicht gelöscht werden.', 'en' => 'Error: Could not delete entry', 'fr' => 'Erreur : impossible de supprimer l’entrée'],
            'Fehler: Could not delete entries' => ['de' => 'Fehler: Einträge konnten nicht gelöscht werden.', 'en' => 'Error: Could not delete entries', 'fr' => 'Erreur : impossible de supprimer les entrées'],
            'Bitte zuerst eine ZIP-, JSON- oder HTML-Datei auswählen.' => ['de' => 'Bitte zuerst eine ZIP-, JSON- oder HTML-Datei auswählen.', 'en' => 'Please select a ZIP, JSON or HTML file first.', 'fr' => 'Veuillez d’abord sélectionner un fichier ZIP, JSON ou HTML.'],
            'Import erfolgreich' => ['de' => 'Import erfolgreich', 'en' => 'Import successful', 'fr' => 'Import réussi', 'it' => 'Importazione riuscita'],
            'Bitte mindestens eine Zutat auswählen.' => ['de' => 'Bitte mindestens eine Zutat auswählen.', 'en' => 'Please select at least one ingredient.', 'fr' => 'Veuillez sélectionner au moins un ingrédient.'],
            'Bitte zuerst eine Datei importieren oder E-Label-Daten eingeben.' => ['de' => 'Bitte zuerst eine Datei importieren oder E-Label-Daten eingeben.', 'en' => 'Please import a file first or enter e-label data.', 'fr' => 'Veuillez d’abord importer un fichier ou saisir les données d’e-label.'],
            'Bitte zuerst einen Slug angeben oder aus der Wein-Nr. übernehmen.' => ['de' => 'Bitte zuerst einen Slug angeben oder aus der Wein-Nr. übernehmen.', 'en' => 'Please enter a slug first or apply the suggestion from the wine number.', 'fr' => 'Veuillez d’abord saisir un slug ou reprendre la proposition depuis le numéro de vin.'],
            'Dieser Slug ist bereits vergeben.' => ['de' => 'Dieser Slug ist bereits vergeben.', 'en' => 'This slug is already in use.', 'fr' => 'Ce slug est déjà utilisé.'],
            'Link konnte nicht erzeugt werden.' => ['de' => 'Link konnte nicht erzeugt werden.', 'en' => 'Link could not be generated.', 'fr' => 'Le lien n’a pas pu être généré.'],
            'Import gelöscht.' => ['de' => 'Import gelöscht.', 'en' => 'Import deleted.', 'fr' => 'Import supprimé.'],
            'Ungültige Produkt-ID.' => ['de' => 'Ungültige Produkt-ID.', 'en' => 'Invalid product ID.', 'fr' => 'ID produit invalide.'],
            'Ungültige Produkt-ID' => ['de' => 'Ungültige Produkt-ID', 'en' => 'Invalid product ID', 'fr' => 'ID produit invalide'],
            'Für dieses Produkt sind keine verwertbaren E-Label-Daten vorhanden.' => ['de' => 'Für dieses Produkt sind keine verwertbaren E-Label-Daten vorhanden.', 'en' => 'There are no usable e-label data for this product.', 'fr' => 'Aucune donnée d’e-label exploitable n’est disponible pour ce produit.'],
            'Import, manuelle Daten und erzeugtes E-Label wurden zurückgesetzt.' => ['de' => 'Import, manuelle Daten und erzeugtes E-Label wurden zurückgesetzt.', 'en' => 'Import, manual data and generated e-label have been reset.', 'fr' => 'L’import, les données manuelles et l’e-label généré ont été réinitialisés.'],
            'Nicht autorisiert' => ['de' => 'Nicht autorisiert', 'en' => 'Not authorized', 'fr' => 'Non autorisé'],
            'Für dieses Produkt konnte keine E-Label-URL erzeugt werden.' => ['de' => 'Für dieses Produkt konnte keine E-Label-URL erzeugt werden.', 'en' => 'No e-label URL could be generated for this product.', 'fr' => 'Aucune URL d’e-label n’a pu être générée pour ce produit.'],
            'Invalid nonce' => ['de' => 'Invalid nonce', 'en' => 'Invalid nonce', 'fr' => 'Nonce invalide'],
            'You do not have permission to change settings.' => ['de' => 'You do not have permission to change settings.', 'en' => 'You do not have permission to change settings.', 'fr' => 'Vous n’avez pas l’autorisation de modifier les réglages.'],
            'Settings saved successfully!' => ['de' => 'Einstellungen gespeichert.', 'en' => 'Settings saved successfully!', 'fr' => 'Réglages enregistrés avec succès !', 'it' => 'Impostazioni salvate con successo.'],
            'Nur ZIP-, JSON- oder HTML-Dateien sind erlaubt.' => ['de' => 'Nur ZIP-, JSON- oder HTML-Dateien sind erlaubt.', 'en' => 'Only ZIP, JSON or HTML files are allowed.', 'fr' => 'Seuls les fichiers ZIP, JSON ou HTML sont autorisés.'],
            'Could not create import directory.' => ['de' => 'Import-Ordner konnte nicht erstellt werden.', 'en' => 'Could not create import directory.', 'fr' => 'Impossible de créer le dossier d’import.'],
            'Could not move uploaded file.' => ['de' => 'Hochgeladene Datei konnte nicht verschoben werden.', 'en' => 'Could not move uploaded file.', 'fr' => 'Impossible de déplacer le fichier importé.'],
            'Import erfolgreich (Quelle: JSON).' => ['de' => 'Import erfolgreich (Quelle: JSON).', 'en' => 'Import successful (source: JSON).', 'fr' => 'Import réussi (source : JSON).'],
            'Import erfolgreich (Quelle: ZIP).' => ['de' => 'Import erfolgreich (Quelle: ZIP).', 'en' => 'Import successful (source: ZIP).', 'fr' => 'Import réussi (source : ZIP).'],
            'Import erfolgreich (Quelle: HTML).' => ['de' => 'Import erfolgreich (Quelle: HTML).', 'en' => 'Import successful (source: HTML).', 'fr' => 'Import réussi (source : HTML).'],
            'ZIP file could not be opened.' => ['de' => 'ZIP file could not be opened.', 'en' => 'ZIP file could not be opened.', 'fr' => 'Le fichier ZIP n’a pas pu être ouvert.'],
            'Die JSON-Daten im ZIP konnten nicht verarbeitet werden.' => ['de' => 'Die JSON-Daten im ZIP konnten nicht verarbeitet werden.', 'en' => 'The JSON data inside the ZIP could not be processed.', 'fr' => 'Les données JSON du ZIP n’ont pas pu être traitées.'],
            'Es konnten weder brauchbare JSON- noch HTML-Daten im Import gefunden werden.' => ['de' => 'Es konnten weder brauchbare JSON- noch HTML-Daten im Import gefunden werden.', 'en' => 'No usable JSON or HTML data could be found in the import.', 'fr' => 'Aucune donnée JSON ou HTML exploitable n’a été trouvée dans l’import.'],
            'Die JSON-Daten enthalten keine verwertbaren Produktinformationen.' => ['de' => 'Die JSON-Daten enthalten keine verwertbaren Produktinformationen.', 'en' => 'The JSON data do not contain usable product information.', 'fr' => 'Les données JSON ne contiennent aucune information produit exploitable.'],
            'HTML could not be parsed.' => ['de' => 'HTML could not be parsed.', 'en' => 'HTML could not be parsed.', 'fr' => 'Le HTML n’a pas pu être analysé.'],
            'No ingredient block could be found in the import.' => ['de' => 'No ingredient block could be found in the import.', 'en' => 'No ingredient block could be found in the import.', 'fr' => 'Aucun bloc d’ingrédients n’a été trouvé dans l’import.'],
            'E-Label Import & QR' => ['de' => 'E-Label Import & QR', 'en' => 'E-label import & QR', 'fr' => 'Import d’e-label & QR'],
            'E-Label Daten' => ['de' => 'E-Label Daten', 'en' => 'E-label data', 'fr' => 'Données d’e-label'],
            'WIPZN-Import importieren, E-Label-Link prüfen und QR-Code direkt erzeugen.' => ['de' => 'WIPZN-Import importieren, E-Label-Link prüfen und QR-Code direkt erzeugen.', 'en' => 'Import a WIPZN file, check the e-label link and generate the QR code directly.', 'fr' => 'Importez un fichier WIPZN, vérifiez le lien de l’e-label et générez directement le code QR.'],
            'Produkt %1$d: %2$s' => ['de' => 'Produkt %1$d: %2$s', 'en' => 'Product %1$d: %2$s', 'fr' => 'Produit %1$d : %2$s', 'it' => 'Prodotto %1$d: %2$s'],
            'Hinweis: Auf der externen Receiver-Seite konnten nicht alle Einträge gelöscht werden.' => ['de' => 'Hinweis: Auf der externen Receiver-Seite konnten nicht alle Einträge gelöscht werden.', 'en' => 'Note: Not all entries could be deleted on the external receiver site.', 'fr' => 'Remarque : toutes les entrées n’ont pas pu être supprimées sur le site receiver externe.', 'it' => 'Nota: non tutte le voci hanno potuto essere eliminate sul sito receiver esterno.'],
            'QR-Code konnte nicht erzeugt werden.' => ['de' => 'QR-Code konnte nicht erzeugt werden.', 'en' => 'QR code could not be generated.', 'fr' => 'Le code QR n’a pas pu être généré.', 'it' => 'Il codice QR non ha potuto essere generato.'],
            'E-Label-Seite und QR-Code wurden gelöscht.' => ['de' => 'E-Label-Seite und QR-Code wurden gelöscht.', 'en' => 'E-label page and QR code were deleted.', 'fr' => 'La page e-label et le code QR ont été supprimés.', 'it' => 'La pagina e-label e il codice QR sono stati eliminati.'],
            'Hinweis zur externen Receiver-Seite: %s' => ['de' => 'Hinweis zur externen Receiver-Seite: %s', 'en' => 'Note about the external receiver site: %s', 'fr' => 'Remarque sur le site receiver externe : %s', 'it' => 'Nota sul sito receiver esterno: %s'],
            'Receiver-Endpunkt antwortet, aber der API-Benutzer hat keine ausreichenden Rechte. Gib dem Benutzer auf der Zielseite mindestens Editor-Rechte.' => ['de' => 'Receiver-Endpunkt antwortet, aber der API-Benutzer hat keine ausreichenden Rechte. Gib dem Benutzer auf der Zielseite mindestens Editor-Rechte.', 'en' => 'The receiver endpoint responds, but the API user does not have sufficient permissions. Give that user at least Editor permissions on the target site.', 'fr' => 'Le point de terminaison receiver répond, mais l’utilisateur API ne dispose pas des droits suffisants. Donnez-lui au moins les droits d’éditeur sur le site cible.', 'it' => 'L’endpoint receiver risponde, ma l’utente API non dispone di permessi sufficienti. Assegna almeno i permessi di Editor sul sito di destinazione.'],
            'Receiver-Endpunkt vorhanden, aber ohne gültige Authentifizierung nicht nutzbar.' => ['de' => 'Receiver-Endpunkt vorhanden, aber ohne gültige Authentifizierung nicht nutzbar.', 'en' => 'Receiver endpoint exists, but it cannot be used without valid authentication.', 'fr' => 'Le point de terminaison receiver existe, mais il ne peut pas être utilisé sans authentification valide.', 'it' => 'L’endpoint receiver esiste, ma non può essere usato senza un’autenticazione valida.'],
            'Receiver-Endpunkt konnte nicht geprüft werden: %s' => ['de' => 'Receiver-Endpunkt konnte nicht geprüft werden: %s', 'en' => 'Receiver endpoint could not be checked: %s', 'fr' => 'Le point de terminaison receiver n’a pas pu être vérifié : %s', 'it' => 'L’endpoint receiver non ha potuto essere verificato: %s'],
            'Receiver-Endpunkt nicht gefunden. Ist das Plugin Reith E-Label Receiver auf der Zielseite aktiv?' => ['de' => 'Receiver-Endpunkt nicht gefunden. Ist das Plugin Reith E-Label Receiver auf der Zielseite aktiv?', 'en' => 'Receiver endpoint not found. Is the Reith E-Label Receiver plugin active on the target site?', 'fr' => 'Point de terminaison receiver introuvable. Le plugin Reith E-Label Receiver est-il actif sur le site cible ?', 'it' => 'Endpoint receiver non trovato. Il plugin Reith E-Label Receiver è attivo sul sito di destinazione?'],
            'Receiver-Endpunkt gefunden (API-Discovery).' => ['de' => 'Receiver-Endpunkt gefunden (API-Discovery).', 'en' => 'Receiver endpoint found (API discovery).', 'fr' => 'Point de terminaison receiver trouvé (découverte API).', 'it' => 'Endpoint receiver trovato (API discovery).'],
            'Receiver-Endpunkt gefunden (REST-Index erkannt).' => ['de' => 'Receiver-Endpunkt gefunden (REST-Index erkannt).', 'en' => 'Receiver endpoint found (REST index detected).', 'fr' => 'Point de terminaison receiver trouvé (index REST détecté).', 'it' => 'Endpoint receiver trovato (indice REST rilevato).'],
            'Receiver-Endpunkt gefunden.' => ['de' => 'Receiver-Endpunkt gefunden.', 'en' => 'Receiver endpoint found.', 'fr' => 'Point de terminaison receiver trouvé.', 'it' => 'Endpoint receiver trovato.'],
            'REST API Ziel-URL fehlt.' => ['de' => 'REST API Ziel-URL fehlt.', 'en' => 'REST API target URL is missing.', 'fr' => 'L’URL cible de l’API REST est manquante.', 'it' => 'Manca l’URL di destinazione della REST API.'],
            'Receiver-Endpunkt vorhanden, aber die Authentifizierung oder Berechtigung reicht nicht aus.' => ['de' => 'Receiver-Endpunkt vorhanden, aber die Authentifizierung oder Berechtigung reicht nicht aus.', 'en' => 'Receiver endpoint exists, but authentication or permissions are insufficient.', 'fr' => 'Le point de terminaison receiver existe, mais l’authentification ou les autorisations sont insuffisantes.', 'it' => 'L’endpoint receiver esiste, ma autenticazione o permessi non sono sufficienti.'],
            'Receiver-Endpunkt nicht gefunden.' => ['de' => 'Receiver-Endpunkt nicht gefunden.', 'en' => 'Receiver endpoint not found.', 'fr' => 'Point de terminaison receiver introuvable.', 'it' => 'Endpoint receiver non trovato.'],
            'Receiver-API konnte nicht erkannt werden: %s' => ['de' => 'Receiver-API konnte nicht erkannt werden: %s', 'en' => 'Receiver API could not be detected: %s', 'fr' => 'L’API receiver n’a pas pu être détectée : %s', 'it' => 'La receiver API non ha potuto essere rilevata: %s'],
            'Receiver-API liefert keinen gültigen Erstell-Endpunkt.' => ['de' => 'Receiver-API liefert keinen gültigen Erstell-Endpunkt.', 'en' => 'Receiver API does not provide a valid create endpoint.', 'fr' => 'L’API receiver ne fournit pas de point de terminaison de création valide.', 'it' => 'La receiver API non fornisce un endpoint di creazione valido.'],
            'Externe E-Label-Seite konnte nicht erstellt werden (HTTP %1$d). %2$s' => ['de' => 'Externe E-Label-Seite konnte nicht erstellt werden (HTTP %1$d). %2$s', 'en' => 'External e-label page could not be created (HTTP %1$d). %2$s', 'fr' => 'La page e-label externe n’a pas pu être créée (HTTP %1$d). %2$s', 'it' => 'La pagina e-label esterna non ha potuto essere creata (HTTP %1$d). %2$s'],
            'Receiver-API liefert keinen gültigen Lösch-Endpunkt.' => ['de' => 'Receiver-API liefert keinen gültigen Lösch-Endpunkt.', 'en' => 'Receiver API does not provide a valid delete endpoint.', 'fr' => 'L’API receiver ne fournit pas de point de terminaison de suppression valide.', 'it' => 'La receiver API non fornisce un endpoint di eliminazione valido.'],
            'Externe E-Label-Seite konnte nicht gelöscht werden (HTTP %1$d). %2$s' => ['de' => 'Externe E-Label-Seite konnte nicht gelöscht werden (HTTP %1$d). %2$s', 'en' => 'External e-label page could not be deleted (HTTP %1$d). %2$s', 'fr' => 'La page e-label externe n’a pas pu être supprimée (HTTP %1$d). %2$s', 'it' => 'La pagina e-label esterna non ha potuto essere eliminata (HTTP %1$d). %2$s'],
            'Einstellungen gespeichert.' => ['de' => 'Einstellungen gespeichert.', 'en' => 'Settings saved.', 'fr' => 'Réglages enregistrés.', 'it' => 'Impostazioni salvate.'],
            'Fehler: Eintrag konnte nicht gelöscht werden.' => ['de' => 'Fehler: Eintrag konnte nicht gelöscht werden.', 'en' => 'Error: Entry could not be deleted.', 'fr' => 'Erreur : l’entrée n’a pas pu être supprimée.', 'it' => 'Errore: la voce non ha potuto essere eliminata.'],
            'Fehler: QR-Code konnte nicht erzeugt werden.' => ['de' => 'Fehler: QR-Code konnte nicht erzeugt werden.', 'en' => 'Error: QR code could not be generated.', 'fr' => 'Erreur : le code QR n’a pas pu être généré.', 'it' => 'Errore: il codice QR non ha potuto essere generato.'],
            'Fehler: Einträge konnten nicht gelöscht werden.' => ['de' => 'Fehler: Einträge konnten nicht gelöscht werden.', 'en' => 'Error: Entries could not be deleted.', 'fr' => 'Erreur : les entrées n’ont pas pu être supprimées.', 'it' => 'Errore: le voci non hanno potuto essere eliminate.'],
            'Pixel dimensions of the downloaded QR image. For print use SVG format is recommended — it is resolution-independent and will be crisp at any size regardless of this setting.' => ['de' => 'Pixelabmessungen des heruntergeladenen QR-Bilds. Für den Druck wird SVG empfohlen — es ist auflösungsunabhängig und bleibt in jeder Größe scharf, unabhängig von dieser Einstellung.', 'en' => 'Pixel dimensions of the downloaded QR image. For print, SVG format is recommended — it is resolution-independent and will be crisp at any size regardless of this setting.', 'fr' => 'Dimensions en pixels de l’image QR téléchargée. Pour l’impression, le format SVG est recommandé — il est indépendant de la résolution et restera net à toute taille.', 'it' => 'Dimensioni in pixel dell’immagine QR scaricata. Per la stampa è consigliato il formato SVG: è indipendente dalla risoluzione e rimane nitido a qualsiasi dimensione.'],
            'SVG is a vector format — edges stay perfectly sharp at any print size and is recommended for wine labels. PNG is a pixel-based image; choose a large size if using PNG for print.' => ['de' => 'SVG ist ein Vektorformat — Kanten bleiben in jeder Druckgröße perfekt scharf und es wird für Weinlabels empfohlen. PNG ist pixelbasiert; wähle eine große Größe, wenn du PNG für den Druck verwendest.', 'en' => 'SVG is a vector format — edges stay perfectly sharp at any print size and is recommended for wine labels. PNG is a pixel-based image; choose a large size if using PNG for print.', 'fr' => 'Le SVG est un format vectoriel — les contours restent parfaitement nets à toute taille d’impression et il est recommandé pour les étiquettes de vin. Le PNG est basé sur des pixels ; choisissez une grande taille si vous l’utilisez pour l’impression.', 'it' => 'SVG è un formato vettoriale: i bordi restano perfettamente nitidi a qualsiasi dimensione di stampa ed è consigliato per le etichette del vino. PNG è basato su pixel; scegli una dimensione grande se lo usi per la stampa.'],
            'Higher correction levels add redundant data so the code can still scan if partially damaged, but produce a denser, more complex pattern. For small clean wine labels (18 mm) Low is recommended — it produces the fewest modules and is easiest to scan.' => ['de' => 'Höhere Fehlerkorrekturstufen fügen Redundanz hinzu, damit der Code auch bei teilweiser Beschädigung lesbar bleibt, erzeugen aber ein dichteres, komplexeres Muster. Für kleine saubere Weinlabels (18 mm) wird Low empfohlen — es erzeugt die wenigsten Module und ist am leichtesten zu scannen.', 'en' => 'Higher correction levels add redundant data so the code can still scan if partially damaged, but produce a denser, more complex pattern. For small clean wine labels (18 mm), Low is recommended — it produces the fewest modules and is easiest to scan.', 'fr' => 'Des niveaux de correction plus élevés ajoutent des données redondantes pour que le code reste lisible s’il est partiellement endommagé, mais produisent un motif plus dense et plus complexe. Pour de petites étiquettes de vin propres (18 mm), le niveau Low est recommandé.', 'it' => 'Livelli di correzione più alti aggiungono dati ridondanti affinché il codice resti leggibile anche se parzialmente danneggiato, ma producono un motivo più denso e complesso. Per piccole etichette da vino pulite (18 mm) è consigliato Low.'],
            'Enter a subdomain (e.g. elabel) or a full hostname (e.g. elabel.example.com). The subdomain must resolve to this WordPress installation.' => ['de' => 'Gib eine Subdomain (z. B. elabel) oder einen vollständigen Hostnamen (z. B. elabel.example.com) ein. Die Subdomain muss auf diese WordPress-Installation zeigen.', 'en' => 'Enter a subdomain (e.g. elabel) or a full hostname (e.g. elabel.example.com). The subdomain must resolve to this WordPress installation.', 'fr' => 'Saisissez un sous-domaine (par ex. elabel) ou un nom d’hôte complet (par ex. elabel.example.com). Le sous-domaine doit pointer vers cette installation WordPress.', 'it' => 'Inserisci un sottodominio (ad es. elabel) o un hostname completo (ad es. elabel.example.com). Il sottodominio deve puntare a questa installazione WordPress.'],
            'http' => ['de' => 'http', 'en' => 'http', 'fr' => 'http', 'it' => 'http'],
            'Use with caution — this removes database records, generated URLs and QR references when uninstalling the plugin.' => ['de' => 'Mit Vorsicht verwenden — beim Deinstallieren des Plugins werden Datenbankeinträge, erzeugte URLs und QR-Verweise entfernt.', 'en' => 'Use with caution — this removes database records, generated URLs and QR references when uninstalling the plugin.', 'fr' => 'À utiliser avec précaution — cela supprime les enregistrements de base de données, les URL générées et les références QR lors de la désinstallation du plugin.', 'it' => 'Usare con cautela: durante la disinstallazione del plugin vengono rimossi record del database, URL generate e riferimenti QR.'],
            'Externe Receiver-Seite per REST API' => ['de' => 'Externe Receiver-Seite per REST API', 'en' => 'External receiver site via REST API', 'fr' => 'Site receiver externe via API REST', 'it' => 'Sito receiver esterno tramite REST API'],
            'REST API Verbindung aktivieren' => ['de' => 'REST API Verbindung aktivieren', 'en' => 'Enable REST API connection', 'fr' => 'Activer la connexion REST API', 'it' => 'Attiva la connessione REST API'],
            'Externe E-Label-Seite per Receiver-Plugin verwenden' => ['de' => 'Externe E-Label-Seite per Receiver-Plugin verwenden', 'en' => 'Use external e-label site via receiver plugin', 'fr' => 'Utiliser un site e-label externe via le plugin receiver', 'it' => 'Usa un sito e-label esterno tramite plugin receiver'],
            'Empfohlener Weg für produktive Setups: Die Pflichtinformationen werden auf einer getrennten WordPress-Seite veröffentlicht.' => ['de' => 'Empfohlener Weg für produktive Setups: Die Pflichtinformationen werden auf einer getrennten WordPress-Seite veröffentlicht.', 'en' => 'Recommended for production setups: mandatory information is published on a separate WordPress site.', 'fr' => 'Méthode recommandée pour les installations productives : les informations obligatoires sont publiées sur un site WordPress séparé.', 'it' => 'Metodo consigliato per setup produttivi: le informazioni obbligatorie vengono pubblicate su un sito WordPress separato.'],
            'Receiver Basis-URL' => ['de' => 'Receiver Basis-URL', 'en' => 'Receiver base URL', 'fr' => 'URL de base du receiver', 'it' => 'URL base del receiver'],
            'Basis-Domain der externen WordPress-Seite mit installiertem Reith E-Label Receiver. Keine /wp-json-Adresse eintragen.' => ['de' => 'Basis-Domain der externen WordPress-Seite mit installiertem Reith E-Label Receiver. Keine /wp-json-Adresse eintragen.', 'en' => 'Base domain of the external WordPress site with Reith E-Label Receiver installed. Do not enter a /wp-json URL.', 'fr' => 'Domaine de base du site WordPress externe avec Reith E-Label Receiver installé. Ne saisissez pas d’adresse /wp-json.', 'it' => 'Dominio base del sito WordPress esterno con Reith E-Label Receiver installato. Non inserire un indirizzo /wp-json.'],
            'WordPress-Benutzername der externen Receiver-Seite.' => ['de' => 'WordPress-Benutzername der externen Receiver-Seite.', 'en' => 'WordPress username of the external receiver site.', 'fr' => 'Nom d’utilisateur WordPress du site receiver externe.', 'it' => 'Nome utente WordPress del sito receiver esterno.'],
            'Application Password des WordPress-Benutzers auf der externen Receiver-Seite.' => ['de' => 'Application Password des WordPress-Benutzers auf der externen Receiver-Seite.', 'en' => 'Application password of the WordPress user on the external receiver site.', 'fr' => 'Mot de passe d’application de l’utilisateur WordPress sur le site receiver externe.', 'it' => 'Application Password dell’utente WordPress sul sito receiver esterno.'],
            'Auf der Zielseite das Plugin Reith E-Label Receiver installieren und aktivieren' => ['de' => 'Auf der Zielseite das Plugin Reith E-Label Receiver installieren und aktivieren', 'en' => 'Install and activate the Reith E-Label Receiver plugin on the target site', 'fr' => 'Installer et activer le plugin Reith E-Label Receiver sur le site cible', 'it' => 'Installa e attiva il plugin Reith E-Label Receiver sul sito di destinazione'],
            'Dort einen Benutzer anlegen, z. B. api_elabel' => ['de' => 'Dort einen Benutzer anlegen, z. B. api_elabel', 'en' => 'Create a user there, e.g. api_elabel', 'fr' => 'Créer un utilisateur sur ce site, par ex. api_elabel', 'it' => 'Crea lì un utente, ad es. api_elabel'],
            'Unter Benutzer > Profil ein Application Password erzeugen und hier Basis-URL, Benutzername und Passwort eintragen' => ['de' => 'Unter Benutzer > Profil ein Application Password erzeugen und hier Basis-URL, Benutzername und Passwort eintragen', 'en' => 'Create an application password under Users > Profile and enter base URL, username and password here', 'fr' => 'Créer un mot de passe d’application sous Utilisateurs > Profil et saisir ici l’URL de base, le nom d’utilisateur et le mot de passe', 'it' => 'Crea un Application Password in Utenti > Profilo e inserisci qui URL base, nome utente e password'],
            'E-Label und QR-Code löschen' => ['de' => 'E-Label und QR-Code löschen', 'en' => 'Delete e-label and QR code', 'fr' => 'Supprimer l’e-label et le code QR', 'it' => 'Elimina e-label e codice QR'],
            'statt Name %s anzeigen' => ['de' => 'statt Name %s anzeigen', 'en' => 'show %s instead of name', 'fr' => 'afficher %s au lieu du nom', 'it' => 'mostra %s invece del nome'],
            'Zusätzliche Stoffe' => ['de' => 'Zusätzliche Stoffe', 'en' => 'Additional substances', 'fr' => 'Substances supplémentaires', 'it' => 'Sostanze aggiuntive'],
            'Zusätzlichen Stoff hinzufügen' => ['de' => 'Zusätzlichen Stoff hinzufügen', 'en' => 'Add additional substance', 'fr' => 'Ajouter une substance supplémentaire', 'it' => 'Aggiungi sostanza aggiuntiva'],
            'Stoff oder E-Nr.' => ['de' => 'Stoff oder E-Nr.', 'en' => 'Substance or E number', 'fr' => 'Substance ou n° E', 'it' => 'Sostanza o n. E'],
            'Entfernen' => ['de' => 'Entfernen', 'en' => 'Remove', 'fr' => 'Supprimer', 'it' => 'Rimuovi'],
            'Noch keine zusätzlichen Stoffe angelegt.' => ['de' => 'Noch keine zusätzlichen Stoffe angelegt.', 'en' => 'No additional substances added yet.', 'fr' => 'Aucune substance supplémentaire ajoutée pour le moment.', 'it' => 'Nessuna sostanza aggiuntiva ancora inserita.'],
            'Manuell geleert: %d Felder' => ['de' => 'Manuell geleert: %d Felder', 'en' => 'Manually cleared: %d fields', 'fr' => 'Vidés manuellement : %d champs', 'it' => 'Svuotati manualmente: %d campi'],
            'Gruppe: %s' => ['de' => 'Gruppe: %s', 'en' => 'Group: %s', 'fr' => 'Groupe : %s', 'it' => 'Gruppo: %s'],
            '%s: Modus' => ['de' => '%s: Modus', 'en' => '%s: mode', 'fr' => '%s : mode', 'it' => '%s: modalità'],
            '%1$s: %2$s' => ['de' => '%1$s: %2$s', 'en' => '%1$s: %2$s', 'fr' => '%1$s : %2$s', 'it' => '%1$s: %2$s'],
            'Auswahl' => ['de' => 'Auswahl', 'en' => 'Selection', 'fr' => 'Sélection', 'it' => 'Selezione'],
            'E-Nummer-Anzeige' => ['de' => 'E-Nummer-Anzeige', 'en' => 'E-number display', 'fr' => 'Affichage du numéro E', 'it' => 'Visualizzazione numero E'],
            'Interner E-Nummer-Wert' => ['de' => 'Interner E-Nummer-Wert', 'en' => 'Internal E-number value', 'fr' => 'Valeur interne du numéro E', 'it' => 'Valore interno del numero E'],
            'Interne Anzeigeoption' => ['de' => 'Interne Anzeigeoption', 'en' => 'Internal display option', 'fr' => 'Option d’affichage interne', 'it' => 'Opzione di visualizzazione interna'],
            'Zusätzlicher Stoff %1$d: %2$s' => ['de' => 'Zusätzlicher Stoff %1$d: %2$s', 'en' => 'Additional substance %1$d: %2$s', 'fr' => 'Substance supplémentaire %1$d : %2$s', 'it' => 'Sostanza aggiuntiva %1$d: %2$s'],
            'E-Label und QR-Code werden gelöscht …' => ['de' => 'E-Label und QR-Code werden gelöscht …', 'en' => 'Deleting e-label and QR code …', 'fr' => 'Suppression de l’e-label et du code QR…', 'it' => 'Eliminazione di e-label e codice QR…'],
            'E-Label-Seite und QR-Code konnten nicht gelöscht werden.' => ['de' => 'E-Label-Seite und QR-Code konnten nicht gelöscht werden.', 'en' => 'E-label page and QR code could not be deleted.', 'fr' => 'La page e-label et le code QR n’ont pas pu être supprimés.', 'it' => 'La pagina e-label e il codice QR non hanno potuto essere eliminati.'],
            'Bitte zuerst alle Pflichtfelder füllen.' => ['de' => 'Bitte zuerst alle Pflichtfelder füllen.', 'en' => 'Please fill all required fields first.', 'fr' => 'Veuillez d’abord remplir tous les champs obligatoires.', 'it' => 'Compila prima tutti i campi obbligatori.'],
            'Fehler' => ['de' => 'Fehler', 'en' => 'Error', 'fr' => 'Erreur', 'it' => 'Errore'],
            'Lade Daten …' => ['de' => 'Lade Daten …', 'en' => 'Loading data …', 'fr' => 'Chargement des données…', 'it' => 'Caricamento dati…'],
            'QR-Code wird erzeugt …' => ['de' => 'QR-Code wird erzeugt …', 'en' => 'Generating QR code …', 'fr' => 'Génération du code QR…', 'it' => 'Generazione del codice QR…'],
            '* from organic farming' => ['de' => '* aus ökologischer Landwirtschaft', 'en' => '* from organic farming', 'fr' => '* issu de l’agriculture biologique', 'it' => '* da agricoltura biologica'],
            'Invalid Product Id or product does not exist' => ['de' => 'Ungültige Produkt-ID oder Produkt existiert nicht', 'en' => 'Invalid product ID or product does not exist', 'fr' => 'ID produit invalide ou produit inexistant', 'it' => 'ID prodotto non valido o prodotto inesistente'],
            'Unable to generate shortcode - exceeded 50 tries' => ['de' => 'Shortcode konnte nicht erzeugt werden – 50 Versuche überschritten', 'en' => 'Unable to generate shortcode - exceeded 50 tries', 'fr' => 'Impossible de générer le shortcode – plus de 50 tentatives', 'it' => 'Impossibile generare lo shortcode: superati 50 tentativi'],
            'Shortcode DB Update failed' => ['de' => 'Shortcode-Datenbankupdate fehlgeschlagen', 'en' => 'Shortcode DB update failed', 'fr' => 'La mise à jour de la base du shortcode a échoué', 'it' => 'Aggiornamento database shortcode non riuscito'],
            'Not found' => ['de' => 'Nicht gefunden', 'en' => 'Not found', 'fr' => 'Introuvable', 'it' => 'Non trovato'],
            'E-Label not found' => ['de' => 'E-Label nicht gefunden', 'en' => 'E-label not found', 'fr' => 'E-label introuvable', 'it' => 'E-label non trovato'],
            'No import file uploaded.' => ['de' => 'Keine Importdatei hochgeladen.', 'en' => 'No import file uploaded.', 'fr' => 'Aucun fichier d’import téléversé.', 'it' => 'Nessun file di importazione caricato.'],
            'Import-Ordner konnte nicht erstellt werden.' => ['de' => 'Import-Ordner konnte nicht erstellt werden.', 'en' => 'Import directory could not be created.', 'fr' => 'Le dossier d’import n’a pas pu être créé.', 'it' => 'La cartella di importazione non ha potuto essere creata.'],
            'Hochgeladene Datei konnte nicht verschoben werden.' => ['de' => 'Hochgeladene Datei konnte nicht verschoben werden.', 'en' => 'Uploaded file could not be moved.', 'fr' => 'Le fichier téléversé n’a pas pu être déplacé.', 'it' => 'Il file caricato non ha potuto essere spostato.'],
            'je 100 ml' => ['de' => 'je 100 ml', 'en' => 'per 100 ml', 'fr' => 'pour 100 ml', 'it' => 'per 100 ml'],
            'Wein E-Label Nährwerttabelle' => ['de' => 'Wein E-Label Nährwerttabelle', 'en' => 'Wine E-Label nutrition table', 'fr' => 'Tableau nutritionnel Wine E-Label', 'it' => 'Tabella nutrizionale Wine E-Label'],
            'Inhalt' => ['de' => 'Inhalt', 'en' => 'Content', 'fr' => 'Contenu', 'it' => 'Contenuto'],
            'Aktuelles Produkt' => ['de' => 'Aktuelles Produkt', 'en' => 'Current product', 'fr' => 'Produit actuel', 'it' => 'Prodotto attuale'],
            'Produkt-ID manuell' => ['de' => 'Produkt-ID manuell', 'en' => 'Manual product ID', 'fr' => 'ID produit manuel', 'it' => 'ID prodotto manuale'],
            'Produkt-ID' => ['de' => 'Produkt-ID', 'en' => 'Product ID', 'fr' => 'ID produit', 'it' => 'ID prodotto'],
            'Titel anzeigen' => ['de' => 'Titel anzeigen', 'en' => 'Show title', 'fr' => 'Afficher le titre', 'it' => 'Mostra titolo'],
            'Zusätzliche Nährwertangaben anzeigen' => ['de' => 'Zusätzliche Nährwertangaben anzeigen', 'en' => 'Show additional nutrition values', 'fr' => 'Afficher les valeurs nutritionnelles supplémentaires', 'it' => 'Mostra valori nutrizionali aggiuntivi'],
            'Zutaten unter der Tabelle anzeigen' => ['de' => 'Zutaten unter der Tabelle anzeigen', 'en' => 'Show ingredients below the table', 'fr' => 'Afficher les ingrédients sous le tableau', 'it' => 'Mostra gli ingredienti sotto la tabella'],
            'Link zum E-Label anzeigen' => ['de' => 'Link zum E-Label anzeigen', 'en' => 'Show link to the e-label', 'fr' => 'Afficher le lien vers l’e-label', 'it' => 'Mostra il link all’e-label'],

            'Import bestätigen' => ['de' => 'Import bestätigen', 'en' => 'Confirm import', 'fr' => 'Confirmer l’import'],
            'Import wird gelöscht …' => ['de' => 'Import wird gelöscht …', 'en' => 'Deleting import …', 'fr' => 'Suppression de l’import …'],
            'WIPZN-Import' => ['de' => 'WIPZN-Import', 'en' => 'WIPZN import', 'fr' => 'Import WIPZN'],
            'Zum Aktualisieren der Nährwerte einfach neue Datei einfügen.' => ['de' => 'Zum Aktualisieren der Nährwerte einfach neue Datei einfügen.', 'en' => 'To update the nutrition values, simply insert a new file.', 'fr' => 'Pour mettre à jour les valeurs nutritionnelles, il suffit d’ajouter un nouveau fichier.'],
            'Wein-Nr. (optional)' => ['de' => 'Wein-Nr. (optional)', 'en' => 'Wine no. (optional)', 'fr' => 'N° de vin (optionnel)'],
            'Basis-URL' => ['de' => 'Basis-URL', 'en' => 'Base URL', 'fr' => 'URL de base'],
            'Bitte zuerst im Plugin-Admin setzen' => ['de' => 'Bitte zuerst im Plugin-Admin setzen', 'en' => 'Please set this first in the plugin admin.', 'fr' => 'Veuillez d’abord le définir dans l’administration du plugin.'],
            'Slug / URL-Teil' => ['de' => 'Slug / URL-Teil', 'en' => 'Slug / URL part', 'fr' => 'Slug / partie URL'],
            'Vorschlag aus Wein-Nr. übernehmen' => ['de' => 'Vorschlag aus Wein-Nr. übernehmen', 'en' => 'Apply suggestion from wine number', 'fr' => 'Reprendre la proposition à partir du numéro de vin'],
            'URL-Vorschau' => ['de' => 'URL-Vorschau', 'en' => 'URL preview', 'fr' => 'Aperçu de l’URL'],
            'WIPZN-Import' => ['de' => 'WIPZN-Import', 'en' => 'WIPZN import', 'fr' => 'Import WIPZN'],
            'ZIP, JSON oder HTML hier ablegen' => ['de' => 'ZIP, JSON oder HTML hier ablegen', 'en' => 'Drop ZIP, JSON or HTML here', 'fr' => 'Déposez ici un ZIP, JSON ou HTML'],
            'oder klicken, um eine Datei auszuwählen' => ['de' => 'oder klicken, um eine Datei auszuwählen', 'en' => 'or click to select a file', 'fr' => 'ou cliquez pour sélectionner un fichier'],
            'Keine Datei ausgewählt' => ['de' => 'Keine Datei ausgewählt', 'en' => 'No file selected', 'fr' => 'Aucun fichier sélectionné'],
            'Import bestätigen' => ['de' => 'Import bestätigen', 'en' => 'Confirm import', 'fr' => 'Confirmer l’import'],
            'Import löschen' => ['de' => 'Import löschen', 'en' => 'Delete import', 'fr' => 'Supprimer l’import'],
            'E-Label und QR-Code erstellen' => ['de' => 'E-Label und QR-Code erstellen', 'en' => 'Create e-label and QR code', 'fr' => 'Créer l’e-label et le code QR'],
            ' E-Label-Einträge?\n\nDie Produkte selbst werden nicht gelöscht – nur die E-Label-Daten werden entfernt.\n\nDieser Vorgang kann nicht rückgängig gemacht werden.' => ['de' => ' E-Label-Einträge?\n\nDie Produkte selbst werden nicht gelöscht – nur die E-Label-Daten werden entfernt.\n\nDieser Vorgang kann nicht rückgängig gemacht werden.', 'en' => ' nutrition label entries?\n\nProducts will NOT be deleted - only the nutrition label data will be removed.\n\nThis cannot be undone.', 'fr' => ' entrées d’e-label ?\n\nLes produits ne seront PAS supprimés – seules les données de l’e-label seront supprimées.\n\nCette action est irréversible.'],
            'Diesen E-Label-Eintrag löschen?\n\nDas Produkt selbst wird nicht gelöscht – nur die E-Label-Daten werden entfernt.\n\nDieser Vorgang kann nicht rückgängig gemacht werden.' => ['de' => 'Diesen E-Label-Eintrag löschen?\n\nDas Produkt selbst wird nicht gelöscht – nur die E-Label-Daten werden entfernt.\n\nDieser Vorgang kann nicht rückgängig gemacht werden.', 'en' => 'Delete nutrition label entry?\n\nProduct will NOT be deleted - only the nutrition label data will be removed.\n\nThis cannot be undone.', 'fr' => 'Supprimer l’entrée de l’e-label ?\n\nLe produit lui-même ne sera PAS supprimé – seules les données de l’e-label seront supprimées.\n\nCette action est irréversible.'],
            'E-Label ansehen' => ['de' => 'E-Label ansehen', 'en' => 'View e-label', 'fr' => 'Afficher l’e-label'],
            'E-Label und QR-Code aktualisieren' => ['de' => 'E-Label und QR-Code aktualisieren', 'en' => 'Update e-label and QR code', 'fr' => 'Mettre à jour l’e-label et le code QR'],
            'Importstatus' => ['de' => 'Importstatus', 'en' => 'Import status', 'fr' => 'Statut de l’import'],
            'Import fehlgeschlagen' => ['de' => 'Import fehlgeschlagen', 'en' => 'Import failed', 'fr' => 'Échec de l’import'],
            'Noch kein Import durchgeführt' => ['de' => 'Noch kein Import durchgeführt', 'en' => 'No import performed yet', 'fr' => 'Aucun import effectué pour le moment'],
            'E-Label-Seite' => ['de' => 'E-Label-Seite', 'en' => 'E-label page', 'fr' => 'Page e-label'],
            'E-Label-Seite erfolgreich erstellt' => ['de' => 'E-Label-Seite erfolgreich erstellt', 'en' => 'E-label page created successfully', 'fr' => 'Page e-label créée avec succès'],
            'Link öffnen' => ['de' => 'Link öffnen', 'en' => 'Open link', 'fr' => 'Ouvrir le lien'],
            'QR-Code' => ['de' => 'QR-Code', 'en' => 'QR code', 'fr' => 'Code QR'],
            'QR-Code erfolgreich erstellt' => ['de' => 'QR-Code erfolgreich erstellt', 'en' => 'QR code created successfully', 'fr' => 'Code QR créé avec succès'],
            'Download' => ['de' => 'Download', 'en' => 'Download', 'fr' => 'Télécharger'],
            'Letzter Import' => ['de' => 'Letzter Import', 'en' => 'Last import', 'fr' => 'Dernier import'],
            'QR-Code Vorschau' => ['de' => 'QR-Code Vorschau', 'en' => 'QR code preview', 'fr' => 'Aperçu du code QR'],
            'Quelldatei herunterladen' => ['de' => 'Quelldatei herunterladen', 'en' => 'Download source file', 'fr' => 'Télécharger le fichier source'],
            'Importierte Daten und manuelle Eingaben bleiben gemeinsam bearbeitbar. Manuelle Änderungen haben beim Erstellen Vorrang.' => ['de' => 'Importierte Daten und manuelle Eingaben bleiben gemeinsam bearbeitbar. Manuelle Änderungen haben beim Erstellen Vorrang.', 'en' => 'Imported data and manual inputs remain editable together. Manual changes take priority when generating the label.', 'fr' => 'Les données importées et les saisies manuelles restent modifiables ensemble. Les modifications manuelles ont priorité lors de la génération du label.'],
            'Ablaufstatus' => ['de' => 'Ablaufstatus', 'en' => 'Workflow status', 'fr' => 'État du processus'],
            'Import' => ['de' => 'Import', 'en' => 'Import', 'fr' => 'Import'],
            'Manuelle Daten' => ['de' => 'Manuelle Daten', 'en' => 'Manual data', 'fr' => 'Données manuelles', 'it' => 'Dati manuali'],
            'Pflichtfelder' => ['de' => 'Pflichtfelder', 'en' => 'Required fields', 'fr' => 'Champs obligatoires'],
            'Datenquelle' => ['de' => 'Datenquelle', 'en' => 'Data source', 'fr' => 'Source des données'],
            'Produktinformationen' => ['de' => 'Produktinformationen', 'en' => 'Product information', 'fr' => 'Informations produit'],
            'Bezeichnung' => ['de' => 'Bezeichnung', 'en' => 'Name', 'fr' => 'Dénomination'],
            'Pflichtfeld' => ['de' => 'Pflichtfeld', 'en' => 'Required field', 'fr' => 'Champ obligatoire'],
            'Wein-Nr.' => ['de' => 'Wein-Nr.', 'en' => 'Wine no.', 'fr' => 'N° de vin'],
            'AP-Nr.' => ['de' => 'AP-Nr.', 'en' => 'AP no.', 'fr' => 'N° AP'],
            'Kategorie' => ['de' => 'Kategorie', 'en' => 'Category', 'fr' => 'Catégorie', 'it' => 'Categoria'],
            'Kategorie wählen' => ['de' => 'Kategorie wählen', 'en' => 'Select category', 'fr' => 'Choisir une catégorie'],
            'Pflichtfelder sind mit * markiert.' => ['de' => 'Pflichtfelder sind mit * markiert.', 'en' => 'Required fields are marked with *.', 'fr' => 'Les champs obligatoires sont marqués par *.'],
            'Nährwertangaben' => ['de' => 'Nährwertangaben', 'en' => 'Nutrition values', 'fr' => 'Valeurs nutritionnelles'],
            'Alkohol' => ['de' => 'Alkohol', 'en' => 'Alcohol', 'fr' => 'Alcool'],
            'Alkohol in vol%' => ['de' => 'Alkohol in vol%', 'en' => 'Alcohol in vol%', 'fr' => 'Alcool en vol%'],
            'automatisch aus g/l berechnet' => ['de' => 'automatisch aus g/l berechnet', 'en' => 'automatically calculated from g/l', 'fr' => 'calculé automatiquement à partir de g/l'],
            'Restzucker' => ['de' => 'Restzucker', 'en' => 'Residual sugar', 'fr' => 'Sucres résiduels'],
            'Gesamtsäure' => ['de' => 'Gesamtsäure', 'en' => 'Total acidity', 'fr' => 'Acidité totale'],
            'Glycerin' => ['de' => 'Glycerin', 'en' => 'Glycerol', 'fr' => 'Glycérol'],
            'Standardwert' => ['de' => 'Standardwert', 'en' => 'Standard value', 'fr' => 'Valeur standard'],
            'Standardwert edelsüß' => ['de' => 'Standardwert edelsüß', 'en' => 'Standard value sweet wine', 'fr' => 'Valeur standard vin doux'],
            'manueller Analysewert' => ['de' => 'manueller Analysewert', 'en' => 'Manual analysis value', 'fr' => 'Valeur d’analyse manuelle'],
            'wirksamer Wert' => ['de' => 'wirksamer Wert', 'en' => 'Effective value', 'fr' => 'Valeur effective'],
            'Berechnete Werte (pro 100ml)' => ['de' => 'Berechnete Werte (pro 100ml)', 'en' => 'Calculated values (per 100ml)', 'fr' => 'Valeurs calculées (pour 100 ml)'],
            'Brennwert' => ['de' => 'Brennwert', 'en' => 'Energy', 'fr' => 'Énergie'],
            'Kohlenhydrate' => ['de' => 'Kohlenhydrate', 'en' => 'Carbohydrates', 'fr' => 'Glucides'],
            'davon Zucker' => ['de' => 'davon Zucker', 'en' => 'of which sugars', 'fr' => 'dont sucres'],
            'Weitere Nährwertangaben' => ['de' => 'Weitere Nährwertangaben', 'en' => 'Additional nutrition values', 'fr' => 'Autres valeurs nutritionnelles'],
            'Enthält geringfügige Mengen von: Fett, gesättigten Fettsäuren, Eiweiß und Salz' => ['de' => 'Enthält geringfügige Mengen von: Fett, gesättigten Fettsäuren, Eiweiß und Salz', 'en' => 'Contains negligible amounts of fat, saturated fat, protein and salt', 'fr' => 'Contient des quantités négligeables de matières grasses, d’acides gras saturés, de protéines et de sel'],
            'Analysewerte auflisten' => ['de' => 'Analysewerte auflisten', 'en' => 'List analysis values', 'fr' => 'Lister les valeurs d’analyse'],
            'Fett' => ['de' => 'Fett', 'en' => 'Fat', 'fr' => 'Matières grasses'],
            'davon gesättigte Fettsäuren' => ['de' => 'davon gesättigte Fettsäuren', 'en' => 'of which saturates', 'fr' => 'dont acides gras saturés'],
            'Eiweiß' => ['de' => 'Eiweiß', 'en' => 'Protein', 'fr' => 'Protéines'],
            'Salz' => ['de' => 'Salz', 'en' => 'Salt', 'fr' => 'Sel'],
            'Angegebener Salzgehalt ist ausschließlich auf die Anwesenheit natürlich vorkommenden Natriums zurückzuführen.' => ['de' => 'Angegebener Salzgehalt ist ausschließlich auf die Anwesenheit natürlich vorkommenden Natriums zurückzuführen.', 'en' => 'The stated salt content is exclusively due to naturally occurring sodium.', 'fr' => 'La teneur en sel indiquée est exclusivement due à la présence de sodium naturellement présent.'],
            'Erstellung' => ['de' => 'Erstellung', 'en' => 'Creation', 'fr' => 'Création'],
            'Daten aus bestehendem Produkt übernehmen' => ['de' => 'Daten aus bestehendem Produkt übernehmen', 'en' => 'Copy data from existing product', 'fr' => 'Reprendre les données d’un produit existant'],
            'Produkt auswählen …' => ['de' => 'Produkt auswählen …', 'en' => 'Select product …', 'fr' => 'Sélectionner un produit …'],
            'Daten übernehmen' => ['de' => 'Daten übernehmen', 'en' => 'Apply data', 'fr' => 'Reprendre les données'],
            'Gut für Vorjahresprodukte: Formularwerte werden übernommen, Slug und erzeugte Seite bleiben unberührt.' => ['de' => 'Gut für Vorjahresprodukte: Formularwerte werden übernommen, Slug und erzeugte Seite bleiben unberührt.', 'en' => 'Good for previous vintages: form values are copied, slug and generated page remain unchanged.', 'fr' => 'Pratique pour les produits du millésime précédent : les valeurs du formulaire sont reprises, le slug et la page générée restent inchangés.'],
            'Prüfung vor dem Erstellen' => ['de' => 'Prüfung vor dem Erstellen', 'en' => 'Validation before creation', 'fr' => 'Vérification avant création'],
            'Wein-Nr. für Link' => ['de' => 'Wein-Nr. für Link', 'en' => 'Wine no. for link', 'fr' => 'N° de vin pour le lien'],
            'Slug kopieren' => ['de' => 'Slug kopieren', 'en' => 'Copy slug', 'fr' => 'Copier le slug'],
            'E-Label-Link' => ['de' => 'E-Label-Link', 'en' => 'E-label link', 'fr' => 'Lien e-label'],
            'Link kopieren' => ['de' => 'Link kopieren', 'en' => 'Copy link', 'fr' => 'Copier le lien'],
            'QR-Code ansehen' => ['de' => 'QR-Code ansehen', 'en' => 'View QR code', 'fr' => 'Afficher le code QR'],
            'QR-Code verbergen' => ['de' => 'QR-Code verbergen', 'en' => 'Hide QR code', 'fr' => 'Masquer le code QR'],
            'QR-Code herunterladen' => ['de' => 'QR-Code herunterladen', 'en' => 'Download QR code', 'fr' => 'Télécharger le code QR'],
            'Hinweis zu Verantwortung und Haftung' => ['de' => 'Hinweis zu Verantwortung und Haftung', 'en' => 'Notice on responsibility and liability', 'fr' => 'Avis de responsabilité et de garantie', 'it' => 'Avvertenza su responsabilità e garanzia'],
            'Dieses Plugin ist eine technische Hilfe. Für die inhaltliche Richtigkeit, Vollständigkeit und rechtliche Prüfung aller eingegebenen, importierten, übersetzten oder ausgegebenen Daten ist ausschließlich der Nutzer verantwortlich. Die Nutzung ersetzt keine Rechtsberatung. Trotz sorgfältiger Entwicklung wird keine Gewähr für die rechtliche Zulässigkeit oder Fehlerfreiheit in jedem Einzelfall übernommen, soweit gesetzlich zulässig.' => ['de' => 'Dieses Plugin ist eine technische Hilfe. Für die inhaltliche Richtigkeit, Vollständigkeit und rechtliche Prüfung aller eingegebenen, importierten, übersetzten oder ausgegebenen Daten ist ausschließlich der Nutzer verantwortlich. Die Nutzung ersetzt keine Rechtsberatung. Trotz sorgfältiger Entwicklung wird keine Gewähr für die rechtliche Zulässigkeit oder Fehlerfreiheit in jedem Einzelfall übernommen, soweit gesetzlich zulässig.', 'en' => 'This plugin is a technical aid only. The user alone is responsible for the factual accuracy, completeness and legal review of all entered, imported, translated or generated data. Use of the plugin does not replace legal advice. Despite careful development, no warranty is given for the legal admissibility or error-free nature of the generated content in every individual case, to the extent permitted by law.', 'fr' => 'Ce plugin constitue uniquement une aide technique. L’utilisateur est seul responsable de l’exactitude, de l’exhaustivité et de la vérification juridique de toutes les données saisies, importées, traduites ou générées. Son utilisation ne remplace pas un conseil juridique. Malgré un développement soigné, aucune garantie n’est donnée quant à la licéité ou à l’absence d’erreurs des contenus générés dans chaque cas particulier, dans la mesure autorisée par la loi.', 'it' => 'Questo plugin è solo un supporto tecnico. L’utente è l’unico responsabile della correttezza dei contenuti, della completezza e della verifica giuridica di tutti i dati inseriti, importati, tradotti o generati. L’uso del plugin non sostituisce una consulenza legale. Nonostante uno sviluppo accurato, non viene fornita alcuna garanzia circa la liceità o l’assenza di errori dei contenuti generati in ogni singolo caso, nei limiti consentiti dalla legge.'],
            'Manuelle Daten leeren' => ['de' => 'Manuelle Daten leeren', 'en' => 'Clear manual data', 'fr' => 'Effacer les données manuelles'],
            'Alles zurücksetzen' => ['de' => 'Alles zurücksetzen', 'en' => 'Reset everything', 'fr' => 'Tout réinitialiser'],
            'Import löschen entfernt nur die Importquelle. Manuelle Daten leeren setzt nur das Formular zurück. Alles zurücksetzen entfernt beides und die erzeugte E-Label-Seite.' => ['de' => 'Import löschen entfernt nur die Importquelle. Manuelle Daten leeren setzt nur das Formular zurück. Alles zurücksetzen entfernt beides und die erzeugte E-Label-Seite.', 'en' => 'Delete import removes only the import source. Clear manual data resets only the form. Reset everything removes both and the generated e-label page.', 'fr' => 'Supprimer l’import retire uniquement la source d’import. Effacer les données manuelles réinitialise uniquement le formulaire. Tout réinitialiser supprime les deux ainsi que la page e-label générée.'],
            'Zutaten' => ['de' => 'Zutaten', 'en' => 'Ingredients', 'fr' => 'Ingrédients', 'it' => 'Ingredienti'],
            'Zutaten aufzählen' => ['de' => 'Zutaten aufzählen', 'en' => 'List ingredients', 'fr' => 'Lister les ingrédients'],
            'Alternativauswahl zur Angabe von bis zu 3 Stoffen, die wahlweise eingesetzt werden können' => ['de' => 'Alternativauswahl zur Angabe von bis zu 3 Stoffen, die wahlweise eingesetzt werden können', 'en' => 'Alternative selection for listing up to 3 substances that may be used alternatively', 'fr' => 'Sélection alternative pour indiquer jusqu’à 3 substances pouvant être utilisées au choix'],
            'Name' => ['de' => 'Name', 'en' => 'Name', 'fr' => 'Nom'],
            'Bio' => ['de' => 'Bio', 'en' => 'Organic', 'fr' => 'Bio'],
            'Darstellung' => ['de' => 'Darstellung', 'en' => 'Display', 'fr' => 'Affichage'],
            'Statisch' => ['de' => 'Statisch', 'en' => 'Static', 'fr' => 'Statique'],
            'Aufklappbar' => ['de' => 'Aufklappbar', 'en' => 'Collapsible', 'fr' => 'Dépliable'],
            'Standardmäßig geöffnet' => ['de' => 'Standardmäßig geöffnet', 'en' => 'Open by default', 'fr' => 'Ouvert par défaut'],
            'Accordion-Überschrift' => ['de' => 'Accordion-Überschrift', 'en' => 'Accordion heading', 'fr' => 'Titre de l’accordéon'],
            'biologisch' => ['de' => 'biologisch', 'en' => 'organic', 'fr' => 'biologique'],
            'Live-Vorschau' => ['de' => 'Live-Vorschau', 'en' => 'Live preview', 'fr' => 'Aperçu en direct'],
            'Seite geprüft' => ['de' => 'Seite geprüft', 'en' => 'Page checked', 'fr' => 'Page vérifiée'],
            'Vorschau lokal' => ['de' => 'Vorschau lokal', 'en' => 'Local preview', 'fr' => 'Aperçu local'],
            'Kompakte Vorschau für Handy-Layout und QR-Scanbarkeit.' => ['de' => 'Kompakte Vorschau für Handy-Layout und QR-Scanbarkeit.', 'en' => 'Compact preview for mobile layout and QR scannability.', 'fr' => 'Aperçu compact pour la mise en page mobile et la lisibilité du QR.'],
            'Nährwertangaben je 100ml' => ['de' => 'Nährwertangaben je 100ml', 'en' => 'Nutrition declaration per 100ml', 'fr' => 'Déclaration nutritionnelle pour 100 ml'],
            'Nährwertangaben je 100 ml' => ['de' => 'Nährwertangaben je 100 ml', 'en' => 'Nutrition declaration per 100 ml', 'fr' => 'Déclaration nutritionnelle pour 100 ml'],
            'geladen' => ['de' => 'geladen', 'en' => 'loaded', 'fr' => 'chargé'],
            'offen' => ['de' => 'offen', 'en' => 'open', 'fr' => 'ouvert'],
            'vorhanden' => ['de' => 'vorhanden', 'en' => 'available', 'fr' => 'présent'],
            'leer' => ['de' => 'leer', 'en' => 'empty', 'fr' => 'vide'],
            'erstellt' => ['de' => 'erstellt', 'en' => 'created', 'fr' => 'créé'],
            'vollständig' => ['de' => 'vollständig', 'en' => 'complete', 'fr' => 'complet'],
            'fehlt' => ['de' => 'fehlt', 'en' => 'missing', 'fr' => 'manquant'],
            'Alkohol (g/l)' => ['de' => 'Alkohol (g/l)', 'en' => 'Alcohol (g/l)', 'fr' => 'Alcool (g/l)'],
            'Import + manuelle Änderungen' => ['de' => 'Import + manuelle Änderungen', 'en' => 'Import + manual changes', 'fr' => 'Import + modifications manuelles'],
            'Nur manuelle Eingabe' => ['de' => 'Nur manuelle Eingabe', 'en' => 'Manual input only', 'fr' => 'Saisie manuelle uniquement'],
            'Noch keine Daten' => ['de' => 'Noch keine Daten', 'en' => 'No data yet', 'fr' => 'Pas encore de données'],
            'Quelldatei: %s' => ['de' => 'Quelldatei: %s', 'en' => 'Source file: %s', 'fr' => 'Fichier source : %s'],
            'Manuell geändert: %d Felder' => ['de' => 'Manuell geändert: %d Felder', 'en' => 'Manually changed: %d fields', 'fr' => 'Modifié manuellement : %d champs'],
            'Manuell ergänzt: %d Felder' => ['de' => 'Manuell ergänzt: %d Felder', 'en' => 'Manually added: %d fields', 'fr' => 'Ajouté manuellement : %d champs'],
            'Keine Abweichungen zwischen Import und aktueller Eingabe.' => ['de' => 'Keine Abweichungen zwischen Import und aktueller Eingabe.', 'en' => 'No differences between import and current input.', 'fr' => 'Aucune différence entre l’import et la saisie actuelle.'],
            'Letzter Import: %s' => ['de' => 'Letzter Import: %s', 'en' => 'Last import: %s', 'fr' => 'Dernier import : %s'],
            'Aktueller Formularstand' => ['de' => 'Aktueller Formularstand', 'en' => 'Current form state', 'fr' => 'État actuel du formulaire'],
            'Glycerin-Modus' => ['de' => 'Glycerin-Modus', 'en' => 'Glycerol mode', 'fr' => 'Mode glycérol'],
            'Weitere Nährwerte' => ['de' => 'Weitere Nährwerte', 'en' => 'Additional nutrition values', 'fr' => 'Autres valeurs nutritionnelles'],
            'gesättigte Fettsäuren' => ['de' => 'gesättigte Fettsäuren', 'en' => 'saturates', 'fr' => 'acides gras saturés'],
            'Salz-Hinweis' => ['de' => 'Salz-Hinweis', 'en' => 'Salt note', 'fr' => 'Note sur le sel'],
            'Slug fehlt' => ['de' => 'Slug fehlt', 'en' => 'Slug missing', 'fr' => 'Slug manquant'],
            'Für einen sauberen QR-Code muss ein Slug gesetzt sein.' => ['de' => 'Für einen sauberen QR-Code muss ein Slug gesetzt sein.', 'en' => 'A slug must be set for a clean QR code.', 'fr' => 'Un slug doit être défini pour obtenir un code QR propre.'],
            'QR-kompakt' => ['de' => 'QR-kompakt', 'en' => 'Compact QR', 'fr' => 'QR compact'],
            'Kurzer Slug, gut lesbar und in der Regel sehr gut scanbar.' => ['de' => 'Kurzer Slug, gut lesbar und in der Regel sehr gut scanbar.', 'en' => 'Short slug, easy to read and usually very easy to scan.', 'fr' => 'Slug court, facile à lire et généralement très facile à scanner.'],
            'noch gut' => ['de' => 'noch gut', 'en' => 'still okay', 'fr' => 'encore correct'],
            'Scannbar, aber ein kürzerer Slug wäre auf Etiketten sauberer.' => ['de' => 'Scannbar, aber ein kürzerer Slug wäre auf Etiketten sauberer.', 'en' => 'Scannable, but a shorter slug would be cleaner on labels.', 'fr' => 'Scannable, mais un slug plus court serait plus propre sur les étiquettes.'],
            'recht lang' => ['de' => 'recht lang', 'en' => 'rather long', 'fr' => 'assez long'],
            'Für kleine Etiketten besser kürzen, damit der QR-Code ruhiger bleibt.' => ['de' => 'Für kleine Etiketten besser kürzen, damit der QR-Code ruhiger bleibt.', 'en' => 'Better shorten for small labels so the QR code stays cleaner.', 'fr' => 'Mieux vaut raccourcir pour les petites étiquettes afin que le code QR reste plus propre.'],
            'Bitte einen Slug angeben oder den Vorschlag aus der Wein-Nr. übernehmen.' => ['de' => 'Bitte einen Slug angeben oder den Vorschlag aus der Wein-Nr. übernehmen.', 'en' => 'Please enter a slug or apply the suggestion from the wine number.', 'fr' => 'Veuillez saisir un slug ou reprendre la proposition depuis le numéro de vin.'],
            'Bitte zuerst die Basis-URL im Plugin-Admin setzen.' => ['de' => 'Bitte zuerst die Basis-URL im Plugin-Admin setzen.', 'en' => 'Please set the base URL first in the plugin admin.', 'fr' => 'Veuillez d’abord définir l’URL de base dans l’administration du plugin.'],
            'Noch nicht erstellt. Erst importieren, dann E-Label und QR-Code erstellen.' => ['de' => 'Noch nicht erstellt. Erst importieren, dann E-Label und QR-Code erstellen.', 'en' => 'Not created yet. Import first, then create the e-label and QR code.', 'fr' => 'Pas encore créé. Importez d’abord, puis créez l’e-label et le code QR.'],
            'Kein gültiger Slug oder keine Basis-URL vorhanden.' => ['de' => 'Kein gültiger Slug oder keine Basis-URL vorhanden.', 'en' => 'No valid slug or no base URL available.', 'fr' => 'Aucun slug valide ou aucune URL de base disponible.'],
            'E-Label-Seite konnte nicht mit Inhalt erzeugt werden.' => ['de' => 'E-Label-Seite konnte nicht mit Inhalt erzeugt werden.', 'en' => 'The e-label page could not be generated with content.', 'fr' => 'La page e-label n’a pas pu être générée avec du contenu.'],
            'Noch kein QR-Code erzeugt.' => ['de' => 'Noch kein QR-Code erzeugt.', 'en' => 'No QR code generated yet.', 'fr' => 'Aucun code QR généré pour le moment.'],
            'QR-Code kann ohne gültigen Link nicht erzeugt werden.' => ['de' => 'QR-Code kann ohne gültigen Link nicht erzeugt werden.', 'en' => 'A QR code cannot be generated without a valid link.', 'fr' => 'Un code QR ne peut pas être généré sans lien valide.'],
            'Import läuft …' => ['de' => 'Import läuft …', 'en' => 'Importing …', 'fr' => 'Import en cours …'],
            'Import fehlgeschlagen.' => ['de' => 'Import fehlgeschlagen.', 'en' => 'Import failed.', 'fr' => 'Échec de l’import.'],
            'E-Label und QR-Code werden erstellt …' => ['de' => 'E-Label und QR-Code werden erstellt …', 'en' => 'E-label and QR code are being created …', 'fr' => 'L’e-label et le code QR sont en cours de création …'],
            'Erstellung fehlgeschlagen.' => ['de' => 'Erstellung fehlgeschlagen.', 'en' => 'Creation failed.', 'fr' => 'Échec de la création.'],
            'Import konnte nicht gelöscht werden.' => ['de' => 'Import konnte nicht gelöscht werden.', 'en' => 'Import could not be deleted.', 'fr' => 'L’import n’a pas pu être supprimé.'],
            'Manuelle Daten wurden geleert.' => ['de' => 'Manuelle Daten wurden geleert.', 'en' => 'Manual data have been cleared.', 'fr' => 'Les données manuelles ont été effacées.'],
            'Alles wird zurückgesetzt …' => ['de' => 'Alles wird zurückgesetzt …', 'en' => 'Everything is being reset …', 'fr' => 'Tout est en cours de réinitialisation …'],
            'Alle E-Label-Daten wurden zurückgesetzt.' => ['de' => 'Alle E-Label-Daten wurden zurückgesetzt.', 'en' => 'All e-label data have been reset.', 'fr' => 'Toutes les données d’e-label ont été réinitialisées.'],
            'Daten wurden aus dem ausgewählten Produkt übernommen.' => ['de' => 'Daten wurden aus dem ausgewählten Produkt übernommen.', 'en' => 'Data have been copied from the selected product.', 'fr' => 'Les données ont été reprises depuis le produit sélectionné.'],
            'Daten konnten nicht übernommen werden.' => ['de' => 'Daten konnten nicht übernommen werden.', 'en' => 'Data could not be copied.', 'fr' => 'Les données n’ont pas pu être reprises.'],
            'Slug kopiert.' => ['de' => 'Slug kopiert.', 'en' => 'Slug copied.', 'fr' => 'Slug copié.'],
            'Link kopiert.' => ['de' => 'Link kopiert.', 'en' => 'Link copied.', 'fr' => 'Lien copié.'],
            'Rewrite-Regeln wurden erfolgreich aktualisiert!' => ['de' => 'Rewrite-Regeln wurden erfolgreich aktualisiert!', 'en' => 'Rewrite rules were refreshed successfully!', 'fr' => 'Les règles de réécriture ont été actualisées avec succès !'],
            'Fehler: Rewrite-Regeln konnten nicht aktualisiert werden.' => ['de' => 'Fehler: Rewrite-Regeln konnten nicht aktualisiert werden.', 'en' => 'Error: Rewrite rules could not be refreshed.', 'fr' => 'Erreur : les règles de réécriture n’ont pas pu être actualisées.'],
            'WIPZN-Importe sowie ZIP-, JSON- und HTML-Dateien, erzeugt E-Label-Seiten und QR-Codes für WooCommerce-Produkte.' => ['de' => 'WIPZN-Importe sowie ZIP-, JSON- und HTML-Dateien, erzeugt E-Label-Seiten und QR-Codes für WooCommerce-Produkte.', 'en' => 'Imports WIPZN files as well as ZIP, JSON and HTML files, and generates e-label pages and QR codes for WooCommerce products.', 'fr' => 'Importe des fichiers WIPZN ainsi que des fichiers ZIP, JSON et HTML, et génère des pages e-label et des codes QR pour les produits WooCommerce.'],
        ];

        $manualIt = [
            'Bitte zuerst eine gültige REST API Ziel-URL eingeben.' => 'Inserisci prima un URL di destinazione REST API valido.',
            'REST API erreichbar.' => 'REST API raggiungibile.',
            'Authentifizierung erfolgreich geprüft.' => 'Autenticazione verificata con successo.',
            'Verbindung erfolgreich geprüft.' => 'Connessione verificata con successo.',
            'Verbindung konnte nicht geprüft werden.' => 'Impossibile verificare la connessione.',
            'E-Label erstellt' => 'E-label creato',
            'Nur Import vorhanden' => 'Solo import disponibile',
            'Nur manuelle Daten' => 'Solo dati manuali',
            'Unvollständig' => 'Incompleto',
            'Filtern' => 'Filtra',
            'Zurücksetzen' => 'Reimposta',
            'Keine E-Label-Einträge gefunden.' => 'Nessuna voce e-label trovata.',
            'Produkt' => 'Prodotto',
            'Status' => 'Stato',
            'Erstellt' => 'Creato',
            'Aktionen' => 'Azioni',
            'Auswählen' => 'Seleziona',
            'E-Label öffnen' => 'Apri e-label',
            'QR herunterladen' => 'Scarica QR',
            'Löschen' => 'Elimina',
            'Delete' => 'Elimina',
            'Als CSV exportieren' => 'Esporta come CSV',
            'Fehler:' => 'Errore:',
            'Bitte zuerst eine Datei importieren oder E-Label-Daten eingeben.' => 'Importa prima un file o inserisci i dati e-label.',
            'Bitte zuerst einen Slug angeben oder aus der Wein-Nr. übernehmen.' => 'Inserisci prima uno slug o applica il suggerimento dal numero del vino.',
            'Dieser Slug ist bereits vergeben.' => 'Questo slug è già in uso.',
            'Link konnte nicht erzeugt werden.' => 'Impossibile generare il link.',
            'Import gelöscht.' => 'Importazione eliminata.',
            'Ungültige Produkt-ID.' => 'ID prodotto non valida.',
            'Nicht autorisiert' => 'Non autorizzato',
            'Nur ZIP-, JSON- oder HTML-Dateien sind erlaubt.' => 'Sono consentiti solo file ZIP, JSON o HTML.',
            'ZIP file could not be opened.' => 'Impossibile aprire il file ZIP.',
            'HTML could not be parsed.' => 'Impossibile analizzare l\'HTML.',
            'No ingredient block could be found in the import.' => 'Nell\'importazione non è stato trovato alcun blocco ingredienti.',
            'E-Label Import & QR' => 'Importazione E-Label e QR',
            'E-Label Daten' => 'Dati E-Label',
            'Werkzeugsprache' => 'Lingua dello strumento',
            'Einstellungen' => 'Impostazioni',
            'Allgemein' => 'Generale',
            'Sprache' => 'Lingua',
            'Einrichtung' => 'Configurazione',
        ];

        foreach ($translations as $key => &$values) {
            if ((!isset($values['it']) || $values['it'] === '') && isset($manualIt[$key])) {
                $values['it'] = $manualIt[$key];
            }
            if ((!isset($values['it']) || $values['it'] === '') && isset($values['en']) && $values['en'] !== '') {
                $values['it'] = $values['en'];
            }
        }
        unset($values);

        foreach ($translations as $key => $entry) {
            foreach (['de', 'en', 'fr', 'it'] as $langKey) {
                if (!isset($translations[$key][$langKey]) || $translations[$key][$langKey] === '') {
                    foreach (self::fallback_languages($langKey) as $fallbackLang) {
                        if (isset($entry[$fallbackLang]) && $entry[$fallbackLang] !== '') {
                            $translations[$key][$langKey] = $entry[$fallbackLang];
                            break;
                        }
                    }
                }
                if (!isset($translations[$key][$langKey]) || $translations[$key][$langKey] === '') {
                    $translations[$key][$langKey] = $key;
                }
            }
        }

        return $translations;
    }
}
