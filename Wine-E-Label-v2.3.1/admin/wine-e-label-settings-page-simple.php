<?php
if (!defined('ABSPATH')) {
  exit;
}

if (!isset($_GET['page']) || !in_array((string) $_GET['page'], [WINE_E_LABEL_ADMIN_PAGE_MAIN, 'wine_e_label_main'], true)) {
  return;
}

if (file_exists(WINE_E_LABEL_PLUGIN_DIR . 'admin/settings-functions.php')) {
  require_once WINE_E_LABEL_PLUGIN_DIR . 'admin/settings-functions.php';
}

if (!function_exists('settings_fields') || !function_exists('get_option')) {
  wp_die(__('WordPress-Funktionen sind nicht verfügbar. Bitte Administrator kontaktieren.', 'wine-e-label'));
}

if (isset($_POST['submit-wine-e-label-settings']) && class_exists('Wine_E_Label_Admin_Extended')) {
  Wine_E_Label_Admin_Extended::handle_settings_submission();
}

$current_qr_size       = get_option('qr_size', '500x500');
$current_qr_format     = get_option('qr_format', 'png');
$current_qr_correction = get_option('qr_error_correction', 'low');
$current_base_url      = get_option('wine_e_label_base_url', '');
$current_rest_enabled  = get_option('wine_e_label_rest_enabled', 'no');
$current_rest_base_url = get_option('wine_e_label_rest_base_url', '');
$current_rest_username = get_option('wine_e_label_rest_username', '');
$current_rest_password = get_option('wine_e_label_rest_app_password', '');
$current_lang          = Wine_E_Label_Admin_I18n::get_current_language();
$current_tab           = isset($_GET['tab']) ? sanitize_key((string) $_GET['tab']) : 'general';
if (!in_array($current_tab, ['general', 'setup', 'language'], true)) {
  $current_tab = 'general';
}
$current_subdomain_enabled = get_option('wine_e_label_use_subdomain', 'no') === 'yes';
$current_scheme = get_option('wine_e_label_subdomain_scheme', 'https');
$public_base_summary = Wine_E_Label_URL::get_public_base_url(false);
$local_route_summary = Wine_E_Label_URL::get_local_base_url();
$publish_mode_summary = $current_rest_enabled === 'yes'
  ? __('Receiver / REST API', 'wine-e-label')
  : ($current_subdomain_enabled ? __('Subdomain', 'wine-e-label') : __('Lokale WordPress-Seite', 'wine-e-label'));
$db = new Wine_E_Label_DB_Extended();
$active_count = $db->count_all_entries();
$settings_base_url = admin_url('admin.php?page=' . WINE_E_LABEL_ADMIN_PAGE_MAIN);
$rest_test_nonce  = wp_create_nonce('wine_e_label_test_rest_connection');

$setup_translations = [
  'de' => [
    'tab' => 'Einrichtung',
    'intro_title' => 'Einrichtung',
    'intro_text' => 'Dieses Plugin erstellt elektronische E-Labels für Weinprodukte. Die Produktdaten können importiert oder manuell gepflegt werden. Die fertige E-Label-Seite kann lokal auf derselben WordPress-Seite, auf einer Subdomain oder auf einer externen WordPress-Seite über REST API mit installiertem Receiver-Plugin veröffentlicht werden.',
    'neutral_note' => 'Im Guide werden bewusst neutrale Beispiel-Domains verwendet, damit das Plugin auch auf anderen Websites sauber einsetzbar ist. Statt einer festen Domain sollte überall mit Platzhaltern wie https://deine-label-domain.de gearbeitet werden.',
    'card1_title' => '1. Datenquelle wählen',
    'import_title' => 'Import von WIPZN',
    'import_text' => 'Wenn die Produktdaten bereits auf wipzn.de gepflegt wurden, kann die exportierte Datei direkt importiert werden. Je nach Export stehen ZIP-, JSON- oder HTML-Dateien zur Verfügung.',
    'import_steps' => ['Produktdaten im vorgelagerten System pflegen','Exportdatei herunterladen','Datei im Produkt unter „WIPZN-Import“ hochladen','Import prüfen','E-Label und QR-Code erzeugen'],
    'manual_title' => 'Manuelle Eingabe',
    'manual_text' => 'Wenn keine Importdatei vorliegt, können alle Daten direkt im Plugin eingetragen werden. Auch wenn bereits ein Import vorliegt, können Werte danach manuell ergänzt, korrigiert oder geändert werden. Das ist sinnvoll, wenn Importdaten unvollständig sind, einzelne Angaben berichtigt werden müssen oder zusätzliche Stoffe, Kategorien oder Pflichtwerte nachgetragen werden sollen.',
    'card2_title' => '2. Veröffentlichungsweg wählen',
    'publish_options' => [
      ['title' => 'Lokale E-Label-Seite auf derselben Website', 'text' => 'Das E-Label wird direkt auf der WordPress-Seite erzeugt, auf der auch das Hauptplugin läuft.', 'example' => 'https://deine-domain.de/l/dein-slug/', 'note' => 'Diese Variante ist praktisch für Tests und für Nutzer, die alles auf einer einzigen Website betreiben wollen.'],
      ['title' => 'Veröffentlichung auf einer Subdomain', 'text' => 'Das E-Label wird auf einer separaten Subdomain veröffentlicht.', 'example' => 'https://labels.deine-domain.de', 'note' => 'Diese Variante trennt die E-Label-Ausgabe organisatorisch besser von der Hauptseite, bleibt aber innerhalb derselben Domainstruktur.'],
      ['title' => 'Externe WordPress-Seite per REST API mit Receiver-Plugin', 'text' => 'Das Hauptplugin sendet die E-Label-Daten an eine zweite WordPress-Installation, auf der das Receiver-Plugin läuft.', 'example' => 'Hauptseite: https://www.deine-weinseite.de · E-Label-Seite: https://deine-label-domain.de', 'note' => 'Diese Variante ist in der Regel die rechtlich und technisch sauberste Lösung, weil sich die Pflichtinformationen dort am zuverlässigsten getrennt von Shop-, Marketing- und Tracking-Inhalten ausliefern lassen.'],
    ],
    'card3_title' => '3. Externe Receiver-Seite einrichten',
    'receiver_steps' => ['Auf der Ziel-Domain eine WordPress-Installation bereitstellen, z. B. https://deine-label-domain.de','Das Receiver-Plugin auf dieser Zielseite installieren und aktivieren','Auf der Receiver-Seite einen Benutzer mit Administratorrechten anlegen oder einen bestehenden Administrator verwenden','Im Benutzerprofil dieses Benutzers ein Anwendungspasswort erzeugen, z. B. mit dem Namen api_elabel','Im Hauptplugin die Receiver-URL, den Benutzernamen und das Anwendungspasswort eintragen'],
    'receiver_hint_title' => 'Wichtig',
    'receiver_hint' => 'Im Hauptplugin nur die Basis-Domain der Receiver-Seite eintragen, nicht die komplette /wp-json/...-Adresse. Richtig: https://deine-label-domain.de · Falsch: https://deine-label-domain.de/wp-json/reith-elabel/v2/info',
    'card4_title' => '4. Verbindung testen',
    'test_text' => 'Nach dem Eintragen der Zugangsdaten sollte die Verbindung im Hauptplugin getestet werden. Dabei wird geprüft, ob die REST API erreichbar ist, ob die Authentifizierung funktioniert, ob der Receiver-Endpunkt vorhanden ist und ob Sender und Receiver zueinander passen.',
    'test_routes' => ['API-Erkennung: https://deine-label-domain.de/wp-json/reith-elabel/v2/info','Legacy-Fallback: https://deine-label-domain.de/wp-json/reith-elabel/v1/info'],
    'card5_title' => '5. Welche Lösung ist wann sinnvoll?',
    'fit_local' => 'Lokale Seite: sinnvoll für Tests, einfache Setups und Nutzer mit nur einer WordPress-Seite.',
    'fit_sub' => 'Subdomain: sinnvoll für organisatorische Trennung innerhalb derselben Domainstruktur.',
    'fit_rest' => 'Externe Receiver-Seite per REST API: sinnvoll für produktive Nutzung, saubere Trennung von Hauptwebsite und Pflichtangaben sowie reduzierte und kontrollierte Ausgabe.',
    'fit_recommendation' => 'Empfehlung: Für den späteren Live-Betrieb ist die externe Receiver-Seite per REST API meist der bevorzugte Weg.',
    'faq_title' => 'FAQ',
    'faq_items' => [
      ['q' => 'Ist das Hosting auf der eigenen Homepage grundsätzlich verboten?', 'a' => 'Nicht pauschal durch den Gesetzestext allein. Maßgeblich ist, dass die elektronisch bereitgestellten Pflichtangaben nicht für Verkaufs- oder Marketingzwecke angezeigt werden und dass keine Nutzerdaten gesammelt oder getrackt werden.'],
      ['q' => 'Warum wird trotzdem meist von der eigenen Homepage abgeraten?', 'a' => 'Weil die Europäische Kommission die Vorgaben streng auslegt. Im Kern soll das elektronische Etikett mit vollständiger Zutatenliste und Nährwertdeklaration nicht per QR-Code auf die Homepage des Erzeugers als Teil seiner normalen Website verlinken. Typische Erzeuger-Websites enthalten regelmäßig kommerzielle Informationen für Marketing und/oder Verkauf und sind deshalb nicht der bevorzugte Zielort für Pflichtinformationen.'],
      ['q' => 'Heißt das, lokale oder Subdomain-Lösungen sind immer unzulässig?', 'a' => 'Nein. Aber sie sind rechtlich angreifbarer, wenn die konkrete Zielseite Teil der normalen Erzeuger-Website bleibt oder dort Shop-, Marketing- oder Tracking-Inhalte mitlaufen. Lokal erzeugte E-Label-Seiten und Subdomain-Lösungen sind nur dann vertretbar, wenn die konkrete E-Label-Seite selbst strikt reduziert und frei von Shop-, Marketing- und Tracking-Inhalten bleibt.'],
      ['q' => 'Warum ist die externe Receiver-Seite der bevorzugte Weg?', 'a' => 'Weil sich dort die verlangte Trennung am zuverlässigsten umsetzen lässt: Pflichtinformationen auf einer neutralen, reduzierten Zielseite, getrennt von der normalen Website, von Verkaufsinhalten und von Tracking. Technisch ist das meist auch die sauberste Lösung.'],
    ],
    'order_title' => 'Empfohlene Reihenfolge',
    'order_steps' => ['Daten per WIPZN importieren oder manuell eingeben','Veröffentlichungsweg festlegen','Bei externer Nutzung Receiver-Seite einrichten','Verbindung testen','Testprodukt erzeugen','Link und QR-Code prüfen','Erst danach produktiv verwenden'],
  ],
  'en' => [
    'tab' => 'Setup',
    'intro_title' => 'Setup',
    'intro_text' => 'This plugin creates electronic e-labels for wine products. Product data can be imported or maintained manually. The finished e-label page can be published locally on the same WordPress site, on a subdomain or on an external WordPress site via REST API with the Receiver plugin installed.',
    'neutral_note' => 'This guide deliberately uses neutral example domains so the plugin can be used cleanly on other websites as well. Instead of a fixed domain, use placeholders such as https://your-label-domain.example.',
    'card1_title' => '1. Choose the data source',
    'import_title' => 'Import from WIPZN',
    'import_text' => 'If the product data is already maintained on wipzn.de, the exported file can be imported directly. Depending on the export, ZIP, JSON or HTML files may be available.',
    'import_steps' => ['Maintain the product data in the upstream system','Download the export file','Upload the file under “WIPZN Import” in the product','Check the import','Create the e-label and QR code'],
    'manual_title' => 'Manual entry',
    'manual_text' => 'If no import file is available, all data can be entered directly in the plugin. Manual entry is not only an alternative to import: even if an import already exists, values can still be added, corrected or changed afterwards. This is useful when import data is incomplete, individual values must be corrected or additional substances, categories or mandatory values need to be added.',
    'card2_title' => '2. Choose the publication path',
    'publish_options' => [
      ['title' => 'Local e-label page on the same website', 'text' => 'The e-label is created directly on the WordPress site where the main plugin runs.', 'example' => 'https://your-domain.example/l/your-slug/', 'note' => 'This is practical for tests and for users who want to keep everything on a single website.'],
      ['title' => 'Publish on a subdomain', 'text' => 'The e-label is published on a separate subdomain.', 'example' => 'https://labels.your-domain.example', 'note' => 'This separates the e-label output from the main site more clearly while still staying within the same domain structure.'],
      ['title' => 'External WordPress site via REST API with Receiver plugin', 'text' => 'The main plugin sends the e-label data to a second WordPress installation where the Receiver plugin is active.', 'example' => 'Main site: https://www.your-winery.example · E-label site: https://your-label-domain.example', 'note' => 'This is usually the cleanest legal and technical option because the mandatory information can be delivered most reliably separate from shop, marketing and tracking content.'],
    ],
    'card3_title' => '3. Set up the external Receiver site',
    'receiver_steps' => ['Provide a WordPress installation on the target domain, e.g. https://your-label-domain.example','Install and activate the Receiver plugin on that target site','Create a user with administrator rights on the Receiver site or use an existing administrator','Create an application password in that user profile, e.g. with the name api_elabel','Enter the Receiver URL, username and application password in the main plugin'],
    'receiver_hint_title' => 'Important',
    'receiver_hint' => 'Only enter the base domain of the Receiver site in the main plugin, not the full /wp-json/... address. Correct: https://your-label-domain.example · Wrong: https://your-label-domain.example/wp-json/reith-elabel/v2/info',
    'card4_title' => '4. Test the connection',
    'test_text' => 'After entering the credentials, test the connection in the main plugin. This checks whether the REST API is reachable, whether authentication works, whether the Receiver endpoint exists and whether sender and receiver match.',
    'test_routes' => ['API discovery: https://your-label-domain.example/wp-json/reith-elabel/v2/info','Legacy fallback: https://your-label-domain.example/wp-json/reith-elabel/v1/info'],
    'card5_title' => '5. Which option makes sense when?',
    'fit_local' => 'Local page: useful for tests, simple setups and users with only one WordPress site.',
    'fit_sub' => 'Subdomain: useful for organisational separation within the same domain structure.',
    'fit_rest' => 'External Receiver site via REST API: useful for productive use, a clean separation from the main website and reduced, controlled output.',
    'fit_recommendation' => 'Recommendation: For production use, the external Receiver site via REST API is usually the preferred route.',
    'faq_title' => 'FAQ',
    'faq_items' => [
      ['q' => 'Is hosting on your own homepage generally forbidden?', 'a' => 'Not automatically by the legal text alone. The key point is that the electronically provided mandatory information must not be shown for sales or marketing purposes and that no user data may be collected or tracked.'],
      ['q' => 'Why is the producer homepage still usually discouraged?', 'a' => 'Because the European Commission interprets the rules strictly. In essence, the electronic label with full ingredient list and nutrition declaration should not link via QR code to the producer homepage as part of the normal website. Typical producer websites usually contain commercial information for marketing and/or sales and are therefore not the preferred destination for mandatory information.'],
      ['q' => 'Does that mean local or subdomain solutions are always inadmissible?', 'a' => 'No. But they are more legally vulnerable if the target page remains part of the normal producer website or still contains shop, marketing or tracking content. Locally generated e-label pages and subdomain solutions are only defensible if the concrete e-label page itself remains strictly reduced and free of shop, marketing and tracking content.'],
      ['q' => 'Why is the external Receiver site the preferred route?', 'a' => 'Because it allows the required separation most reliably: mandatory information on a neutral, reduced target page, separated from the normal website, from sales content and from tracking. Technically, this is usually the cleanest solution as well.'],
    ],
    'order_title' => 'Recommended sequence',
    'order_steps' => ['Import data from WIPZN or enter it manually','Choose the publication path','If using an external setup, configure the Receiver site','Test the connection','Create a test product','Check the link and QR code','Only then use it productively'],
  ],
  'fr' => [
    'tab' => 'Configuration',
    'intro_title' => 'Configuration',
    'intro_text' => 'Ce plugin crée des e-labels électroniques pour les produits vitivinicoles. Les données produit peuvent être importées ou gérées manuellement. La page e-label finale peut être publiée localement sur le même site WordPress, sur un sous-domaine ou sur un site WordPress externe via REST API avec le plugin Receiver installé.',
    'neutral_note' => 'Ce guide utilise volontairement des domaines d’exemple neutres afin que le plugin puisse aussi être utilisé proprement sur d’autres sites. Au lieu d’un domaine fixe, utilisez des espaces réservés comme https://votre-domaine-label.example.',
    'card1_title' => '1. Choisir la source des données',
    'import_title' => 'Import depuis WIPZN',
    'import_text' => 'Si les données du produit sont déjà gérées sur wipzn.de, le fichier exporté peut être importé directement. Selon l’export, des fichiers ZIP, JSON ou HTML peuvent être disponibles.',
    'import_steps' => ['Gérer les données produit dans le système amont','Télécharger le fichier d’export','Téléverser le fichier dans le produit sous « Import WIPZN »','Vérifier l’import','Créer l’e-label et le code QR'],
    'manual_title' => 'Saisie manuelle',
    'manual_text' => 'Si aucun fichier d’import n’est disponible, toutes les données peuvent être saisies directement dans le plugin. La saisie manuelle n’est pas seulement une alternative à l’import : même lorsqu’un import existe déjà, les valeurs peuvent ensuite être complétées, corrigées ou modifiées. Cela est utile lorsque les données importées sont incomplètes, que certaines valeurs doivent être corrigées ou que des substances, catégories ou mentions obligatoires supplémentaires doivent être ajoutées.',
    'card2_title' => '2. Choisir le mode de publication',
    'publish_options' => [
      ['title' => 'Page e-label locale sur le même site', 'text' => 'L’e-label est créé directement sur le site WordPress où le plugin principal fonctionne.', 'example' => 'https://votre-domaine.example/l/votre-slug/', 'note' => 'Cette variante est pratique pour les tests et pour les utilisateurs qui veulent tout gérer sur un seul site.'],
      ['title' => 'Publication sur un sous-domaine', 'text' => 'L’e-label est publié sur un sous-domaine séparé.', 'example' => 'https://labels.votre-domaine.example', 'note' => 'Cette variante sépare plus clairement la sortie e-label du site principal tout en restant dans la même structure de domaine.'],
      ['title' => 'Site WordPress externe via REST API avec le plugin Receiver', 'text' => 'Le plugin principal envoie les données e-label vers une seconde installation WordPress sur laquelle le plugin Receiver est actif.', 'example' => 'Site principal : https://www.votre-domaine-vin.example · Site e-label : https://votre-domaine-label.example', 'note' => 'C’est en général la solution la plus propre sur le plan juridique et technique, car les informations obligatoires peuvent y être délivrées de la manière la plus fiable, séparées du shop, du marketing et du tracking.'],
    ],
    'card3_title' => '3. Configurer le site Receiver externe',
    'receiver_steps' => ['Mettre à disposition une installation WordPress sur le domaine cible, par ex. https://votre-domaine-label.example','Installer et activer le plugin Receiver sur ce site cible','Créer sur le site Receiver un utilisateur avec des droits administrateur ou utiliser un administrateur existant','Créer dans ce profil utilisateur un mot de passe d’application, par ex. nommé api_elabel','Renseigner dans le plugin principal l’URL du Receiver, le nom d’utilisateur et le mot de passe d’application'],
    'receiver_hint_title' => 'Important',
    'receiver_hint' => 'Dans le plugin principal, saisissez uniquement le domaine de base du site Receiver, pas l’adresse complète /wp-json/.... Correct : https://votre-domaine-label.example · Incorrect : https://votre-domaine-label.example/wp-json/reith-elabel/v2/info',
    'card4_title' => '4. Tester la connexion',
    'test_text' => 'Après avoir saisi les identifiants, testez la connexion dans le plugin principal. Cela vérifie si la REST API est joignable, si l’authentification fonctionne, si l’endpoint Receiver existe et si l’émetteur et le receiver correspondent.',
    'test_routes' => ['Détection API : https://votre-domaine-label.example/wp-json/reith-elabel/v2/info','Fallback legacy : https://votre-domaine-label.example/wp-json/reith-elabel/v1/info'],
    'card5_title' => '5. Quelle solution choisir et quand ?',
    'fit_local' => 'Page locale : utile pour les tests, les configurations simples et les utilisateurs avec un seul site WordPress.',
    'fit_sub' => 'Sous-domaine : utile pour une séparation organisationnelle au sein de la même structure de domaine.',
    'fit_rest' => 'Site Receiver externe via REST API : utile pour une utilisation productive, une séparation claire du site principal et une sortie réduite et contrôlée.',
    'fit_recommendation' => 'Recommandation : pour la mise en production, le site Receiver externe via REST API est généralement la voie privilégiée.',
    'faq_title' => 'FAQ',
    'faq_items' => [
      ['q' => 'L’hébergement sur sa propre page d’accueil est-il interdit de manière générale ?', 'a' => 'Pas automatiquement par le seul texte juridique. L’essentiel est que les informations obligatoires fournies électroniquement ne soient pas affichées à des fins de vente ou de marketing et qu’aucune donnée utilisateur ne soit collectée ou tracée.'],
      ['q' => 'Pourquoi déconseille-t-on quand même généralement la page d’accueil du producteur ?', 'a' => 'Parce que la Commission européenne interprète les règles de manière stricte. En substance, l’étiquette électronique avec la liste complète des ingrédients et la déclaration nutritionnelle ne devrait pas renvoyer par QR code vers la page d’accueil du producteur comme partie de son site habituel. Les sites de producteurs contiennent généralement des informations commerciales pour le marketing et/ou la vente et ne sont donc pas la destination privilégiée pour les informations obligatoires.'],
      ['q' => 'Cela signifie-t-il que les solutions locales ou sur sous-domaine sont toujours irrecevables ?', 'a' => 'Non. Mais elles sont plus vulnérables juridiquement si la page cible reste partie intégrante du site habituel du producteur ou si elle contient encore du shop, du marketing ou du tracking. Les pages e-label générées localement et les solutions sur sous-domaine ne sont défendables que si la page e-label concernée reste elle-même strictement réduite et exempte de shop, de marketing et de tracking.'],
      ['q' => 'Pourquoi le site Receiver externe est-il la voie privilégiée ?', 'a' => 'Parce qu’il permet la séparation requise de la manière la plus fiable : informations obligatoires sur une page cible neutre et réduite, séparée du site habituel, des contenus de vente et du tracking. Techniquement, c’est aussi généralement la solution la plus propre.'],
    ],
    'order_title' => 'Ordre recommandé',
    'order_steps' => ['Importer les données depuis WIPZN ou les saisir manuellement','Choisir le mode de publication','En cas d’utilisation externe, configurer le site Receiver','Tester la connexion','Créer un produit de test','Vérifier le lien et le code QR','Seulement ensuite l’utiliser en production'],
  ],
  'it' => [
    'tab' => 'Configurazione',
    'intro_title' => 'Configurazione',
    'intro_text' => 'Questo plugin crea e-label elettroniche per i prodotti vitivinicoli. I dati del prodotto possono essere importati oppure gestiti manualmente. La pagina e-label finale può essere pubblicata localmente sullo stesso sito WordPress, su un sottodominio oppure su un sito WordPress esterno tramite REST API con il plugin Receiver installato.',
    'neutral_note' => 'Questa guida utilizza volutamente domini di esempio neutri, così il plugin può essere usato correttamente anche su altri siti. Invece di un dominio fisso, usa segnaposto come https://tuo-dominio-label.example.',
    'card1_title' => '1. Scegliere la fonte dei dati',
    'import_title' => 'Import da WIPZN',
    'import_text' => 'Se i dati del prodotto sono già gestiti su wipzn.de, il file esportato può essere importato direttamente. A seconda dell’esportazione possono essere disponibili file ZIP, JSON oppure HTML.',
    'import_steps' => ['Gestire i dati del prodotto nel sistema a monte','Scaricare il file di esportazione','Caricare il file nel prodotto sotto “Import WIPZN”','Controllare l’importazione','Creare e-label e codice QR'],
    'manual_title' => 'Inserimento manuale',
    'manual_text' => 'Se non è disponibile alcun file di importazione, tutti i dati possono essere inseriti direttamente nel plugin. L’inserimento manuale non è solo un’alternativa all’importazione: anche se un import esiste già, i valori possono comunque essere aggiunti, corretti o modificati in seguito. Questo è utile quando i dati importati sono incompleti, alcuni valori devono essere corretti oppure devono essere aggiunte ulteriori sostanze, categorie o valori obbligatori.',
    'card2_title' => '2. Scegliere il percorso di pubblicazione',
    'publish_options' => [
      ['title' => 'Pagina e-label locale sullo stesso sito', 'text' => 'L’e-label viene creata direttamente sul sito WordPress su cui gira il plugin principale.', 'example' => 'https://tuo-dominio.example/l/tuo-slug/', 'note' => 'Questa variante è pratica per i test e per gli utenti che vogliono gestire tutto su un unico sito.'],
      ['title' => 'Pubblicazione su un sottodominio', 'text' => 'L’e-label viene pubblicata su un sottodominio separato.', 'example' => 'https://labels.tuo-dominio.example', 'note' => 'Questa variante separa meglio l’output e-label dal sito principale, pur rimanendo nella stessa struttura di dominio.'],
      ['title' => 'Sito WordPress esterno tramite REST API con plugin Receiver', 'text' => 'Il plugin principale invia i dati dell’e-label a una seconda installazione WordPress sulla quale è attivo il plugin Receiver.', 'example' => 'Sito principale: https://www.tua-cantina.example · Sito e-label: https://tuo-dominio-label.example', 'note' => 'Di norma questa è la soluzione più pulita dal punto di vista legale e tecnico, perché le informazioni obbligatorie possono essere fornite nel modo più affidabile separate da shop, marketing e tracking.'],
    ],
    'card3_title' => '3. Configurare il sito Receiver esterno',
    'receiver_steps' => ['Predisporre un’installazione WordPress sul dominio di destinazione, ad es. https://tuo-dominio-label.example','Installare e attivare il plugin Receiver su quel sito di destinazione','Sul sito Receiver creare un utente con diritti di amministratore oppure usare un amministratore esistente','Creare nel profilo utente una password applicativa, ad es. con il nome api_elabel','Inserire nel plugin principale l’URL del Receiver, il nome utente e la password applicativa'],
    'receiver_hint_title' => 'Importante',
    'receiver_hint' => 'Nel plugin principale inserire solo il dominio base del sito Receiver, non l’indirizzo completo /wp-json/.... Corretto: https://tuo-dominio-label.example · Errato: https://tuo-dominio-label.example/wp-json/reith-elabel/v2/info',
    'card4_title' => '4. Testare la connessione',
    'test_text' => 'Dopo aver inserito le credenziali, testa la connessione nel plugin principale. Viene controllato se la REST API è raggiungibile, se l’autenticazione funziona, se l’endpoint Receiver esiste e se sender e receiver corrispondono.',
    'test_routes' => ['Rilevamento API: https://tuo-dominio-label.example/wp-json/reith-elabel/v2/info','Fallback legacy: https://tuo-dominio-label.example/wp-json/reith-elabel/v1/info'],
    'card5_title' => '5. Quale soluzione è sensata e quando?',
    'fit_local' => 'Pagina locale: utile per test, configurazioni semplici e utenti con un solo sito WordPress.',
    'fit_sub' => 'Sottodominio: utile per una separazione organizzativa all’interno della stessa struttura di dominio.',
    'fit_rest' => 'Sito Receiver esterno tramite REST API: utile per uso produttivo, separazione chiara dal sito principale e output ridotto e controllato.',
    'fit_recommendation' => 'Raccomandazione: per l’uso in produzione, il sito Receiver esterno tramite REST API è di solito la via preferita.',
    'faq_title' => 'FAQ',
    'faq_items' => [
      ['q' => 'L’hosting sulla propria homepage è in generale vietato?', 'a' => 'Non automaticamente in base al solo testo di legge. Il punto decisivo è che le informazioni obbligatorie fornite elettronicamente non siano mostrate a fini di vendita o marketing e che non vengano raccolti o tracciati dati degli utenti.'],
      ['q' => 'Perché allora di solito si sconsiglia comunque la homepage del produttore?', 'a' => 'Perché la Commissione europea interpreta le regole in modo rigoroso. In sostanza, l’etichetta elettronica con elenco completo degli ingredienti e dichiarazione nutrizionale non dovrebbe collegarsi tramite QR code alla homepage del produttore come parte del normale sito. I siti dei produttori contengono di solito informazioni commerciali per marketing e/o vendita e quindi non sono la destinazione preferita per le informazioni obbligatorie.'],
      ['q' => 'Questo significa che soluzioni locali o su sottodominio sono sempre inammissibili?', 'a' => 'No. Tuttavia sono più esposte dal punto di vista legale se la pagina di destinazione resta parte del normale sito del produttore oppure se include ancora shop, marketing o tracking. Le pagine e-label generate localmente e le soluzioni su sottodominio sono difendibili solo se la pagina e-label concreta resta essa stessa strettamente ridotta e priva di shop, marketing e tracking.'],
      ['q' => 'Perché il sito Receiver esterno è la via preferita?', 'a' => 'Perché consente di realizzare nel modo più affidabile la separazione richiesta: informazioni obbligatorie su una pagina di destinazione neutra e ridotta, separata dal sito normale, dai contenuti di vendita e dal tracking. Dal punto di vista tecnico, di solito è anche la soluzione più pulita.'],
    ],
    'order_title' => 'Sequenza consigliata',
    'order_steps' => ['Importare i dati da WIPZN oppure inserirli manualmente','Scegliere il percorso di pubblicazione','In caso di uso esterno, configurare il sito Receiver','Testare la connessione','Creare un prodotto di prova','Verificare il link e il codice QR','Solo dopo usarlo in produzione'],
  ],
];
$setup_lang = $setup_translations[$current_lang] ?? $setup_translations['de'];

$setup_disclaimer = [
  'de' => [
    'title' => 'Hinweis zu Verantwortung und Haftung',
    'text' => 'Dieses Plugin dient ausschließlich als technische Unterstützung zur Erstellung elektronischer Wein-E-Labels. Für die inhaltliche Richtigkeit, Vollständigkeit und rechtliche Prüfung der eingegebenen, importierten, übersetzten oder ausgegebenen Daten ist ausschließlich der Nutzer verantwortlich.',
    'legal' => 'Die Nutzung des Plugins ersetzt keine Rechtsberatung. Trotz sorgfältiger Entwicklung wird keine Gewähr für die rechtliche Zulässigkeit, Vollständigkeit oder Fehlerfreiheit der erstellten Inhalte in jedem Einzelfall übernommen, soweit gesetzlich zulässig.'
  ],
  'en' => [
    'title' => 'Notice on responsibility and liability',
    'text' => 'This plugin is provided solely as a technical aid for creating electronic wine e-labels. The user alone is responsible for the factual accuracy, completeness and legal review of all entered, imported, translated or generated data.',
    'legal' => 'Use of the plugin does not replace legal advice. Despite careful development, no warranty is given for the legal admissibility, completeness or error-free nature of the generated content in every individual case, to the extent permitted by law.'
  ],
  'fr' => [
    'title' => 'Avis de responsabilité et de garantie',
    'text' => 'Ce plugin sert exclusivement d’assistance technique pour la création d’e-labels viticoles électroniques. L’utilisateur est seul responsable de l’exactitude, de l’exhaustivité et de la vérification juridique des données saisies, importées, traduites ou générées.',
    'legal' => 'L’utilisation du plugin ne remplace pas un conseil juridique. Malgré un développement soigné, aucune garantie n’est donnée quant à la licéité, l’exhaustivité ou l’absence d’erreurs des contenus générés dans chaque cas particulier, dans la mesure autorisée par la loi.'
  ],
  'it' => [
    'title' => 'Avvertenza su responsabilità e garanzia',
    'text' => 'Questo plugin serve esclusivamente come supporto tecnico per la creazione di e-label elettroniche per il vino. L’utente è l’unico responsabile della correttezza dei contenuti, della completezza e della verifica giuridica dei dati inseriti, importati, tradotti o generati.',
    'legal' => 'L’uso del plugin non sostituisce una consulenza legale. Nonostante uno sviluppo accurato, non viene fornita alcuna garanzia circa la liceità, la completezza o l’assenza di errori dei contenuti generati in ogni singolo caso, nei limiti consentiti dalla legge.'
  ],
][$current_lang] ?? [
  'title' => 'Hinweis zu Verantwortung und Haftung',
  'text' => 'Dieses Plugin dient ausschließlich als technische Unterstützung zur Erstellung elektronischer Wein-E-Labels. Für die inhaltliche Richtigkeit, Vollständigkeit und rechtliche Prüfung der eingegebenen, importierten, übersetzten oder ausgegebenen Daten ist ausschließlich der Nutzer verantwortlich.',
  'legal' => 'Die Nutzung des Plugins ersetzt keine Rechtsberatung. Trotz sorgfältiger Entwicklung wird keine Gewähr für die rechtliche Zulässigkeit, Vollständigkeit oder Fehlerfreiheit der erstellten Inhalte in jedem Einzelfall übernommen, soweit gesetzlich zulässig.'
];
?>
<style>
  .wine-e-label-shell{display:grid;gap:20px;margin-top:20px;}
  .wine-e-label-hero{background:linear-gradient(135deg,#fbf3ed 0%,#fffaf6 52%,#ffffff 100%);border:1px solid #ead7ca;border-radius:18px;padding:24px 26px;box-shadow:0 12px 30px rgba(76,38,22,.06);}
  .wine-e-label-hero-grid{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(280px,.9fr);gap:24px;align-items:start;}
  .wine-e-label-eyebrow{display:inline-flex;align-items:center;gap:8px;padding:6px 12px;border-radius:999px;background:#fff3ea;color:#8a3c17;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;}
  .wine-e-label-hero h2{margin:14px 0 10px;font-size:28px;line-height:1.15;}
  .wine-e-label-hero p{margin:0;color:#5f534c;max-width:72ch;line-height:1.6;}
  .wine-e-label-hero-note{margin-top:16px;padding:14px 16px;border-radius:12px;background:rgba(255,255,255,.78);border:1px solid #edd8cc;color:#5e4637;}
  .wine-e-label-hero-side{display:grid;gap:14px;}
  .wine-e-label-hero-mini{background:rgba(255,255,255,.88);border:1px solid #ecd7ca;border-radius:14px;padding:16px 18px;}
  .wine-e-label-hero-mini strong{display:block;margin-bottom:6px;font-size:13px;}
  .wine-e-label-metrics{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-top:18px;}
  .wine-e-label-metric{background:#fff;border:1px solid #ebdcd2;border-radius:14px;padding:16px 18px;box-shadow:0 8px 22px rgba(76,38,22,.04);}
  .wine-e-label-metric-label{display:block;font-size:12px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;color:#7a6a5f;margin-bottom:8px;}
  .wine-e-label-metric-value{display:block;font-size:18px;font-weight:700;color:#1d2327;line-height:1.3;word-break:break-word;}
  .wine-e-label-metric code{font-size:12px;}
  .wine-e-label-form-shell{display:grid;gap:20px;}
  .wine-e-label-general-form{display:flex;flex-direction:column;gap:20px;}
  .wine-e-label-general-form > .wine-e-label-rest-grid{order:1;margin-top:0;}
  .wine-e-label-general-form > .form-table{order:2;margin-top:0;border-collapse:separate;border-spacing:0;}
  .wine-e-label-general-form > p.submit{order:3;margin:0;padding:18px 20px;border-radius:16px;background:#fff;border:1px solid #ded4cd;box-shadow:0 10px 26px rgba(35,23,15,.04);}
  .wine-e-label-general-form > p.submit .button-primary{margin:0;}
  .wine-e-label-general-form .form-table,
  .wine-e-label-general-form .form-table tbody,
  .wine-e-label-general-form .form-table tr,
  .wine-e-label-general-form .form-table th,
  .wine-e-label-general-form .form-table td{display:block;width:auto;padding:0;}
  .wine-e-label-general-form .form-table tr{position:relative;overflow:hidden;background:#fff;border:1px solid #ded4cd;border-radius:16px;padding:20px 22px;margin:0 0 16px;box-shadow:0 10px 26px rgba(35,23,15,.04);}
  .wine-e-label-general-form .form-table tr:nth-child(-n+3){border-left:5px solid #7a2d1f;}
  .wine-e-label-general-form .form-table tr:nth-child(n+4):nth-child(-n+7){border-left:5px solid #215f78;}
  .wine-e-label-general-form .form-table tr:nth-child(8){border-left:5px solid #946200;}
  .wine-e-label-general-form .form-table tr:nth-child(1),
  .wine-e-label-general-form .form-table tr:nth-child(2),
  .wine-e-label-general-form .form-table tr:nth-child(3),
  .wine-e-label-general-form .form-table tr:nth-child(4),
  .wine-e-label-general-form .form-table tr:nth-child(5),
  .wine-e-label-general-form .form-table tr:nth-child(6),
  .wine-e-label-general-form .form-table tr:nth-child(7){margin-bottom:0;box-shadow:none;}
  .wine-e-label-general-form .form-table tr:nth-child(1){padding-top:58px;border-radius:16px 16px 0 0;border-bottom:none;background:linear-gradient(180deg,#fff9f5 0,#fff 145px);}
  .wine-e-label-general-form .form-table tr:nth-child(1)::before{content:"QR-Code-Einstellungen";position:absolute;top:20px;left:22px;font-size:18px;font-weight:700;color:#1d2327;}
  .wine-e-label-general-form .form-table tr:nth-child(2){border-radius:0;border-top:none;border-bottom:none;}
  .wine-e-label-general-form .form-table tr:nth-child(3){border-radius:0 0 16px 16px;border-top:none;margin:0 0 18px;box-shadow:0 10px 26px rgba(35,23,15,.04);}
  .wine-e-label-general-form .form-table tr:nth-child(4){padding-top:58px;border-radius:16px 16px 0 0;border-bottom:none;background:linear-gradient(180deg,#f4fbff 0,#fff 145px);}
  .wine-e-label-general-form .form-table tr:nth-child(4)::before{content:"Ziel-URLs und Subdomain";position:absolute;top:20px;left:22px;font-size:18px;font-weight:700;color:#1d2327;}
  .wine-e-label-general-form .form-table tr:nth-child(5),
  .wine-e-label-general-form .form-table tr:nth-child(6){border-radius:0;border-top:none;border-bottom:none;}
  .wine-e-label-general-form .form-table tr:nth-child(7){border-radius:0 0 16px 16px;border-top:none;margin:0 0 18px;box-shadow:0 10px 26px rgba(35,23,15,.04);}
  .wine-e-label-general-form .form-table th{margin-bottom:10px;}
  .wine-e-label-general-form .form-table th label,
  .wine-e-label-general-form .form-table th{font-size:15px;font-weight:700;color:#1d2327;}
  .wine-e-label-general-form .form-table td > label{display:block;}
  .wine-e-label-general-form .form-table .description{margin:8px 0 0;color:#675d56;line-height:1.55;max-width:78ch;}
  .wine-e-label-general-form .form-table input[type="text"],
  .wine-e-label-general-form .form-table select{width:100%;max-width:520px;}
  .wine-e-label-general-columns{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;}
  .wine-e-label-settings-card,
  .wine-e-label-rest-grid{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(280px,.85fr);gap:24px;align-items:start;margin-top:24px;}
  .wine-e-label-rest-card,.wine-e-label-rest-guide,.wine-e-label-setup-card,.wine-e-label-settings-card,.wine-e-label-ops-card{background:#fff;border:1px solid #ded4cd;border-radius:16px;padding:22px;box-shadow:0 10px 26px rgba(35,23,15,.05);}
  .wine-e-label-rest-card{border-top:4px solid #7a2d1f;background:linear-gradient(180deg,#fff9f5 0,#fff 130px);}
  .wine-e-label-rest-guide{margin-top:0;border-top:4px solid #215f78;background:linear-gradient(180deg,#f4fbff 0,#fff 130px);}
  .wine-e-label-rest-card h2,.wine-e-label-rest-guide h3,.wine-e-label-setup-card h2,.wine-e-label-setup-card h3{margin:0 0 14px;}
  .wine-e-label-settings-card h3,.wine-e-label-ops-card h3{margin:0 0 6px;font-size:18px;}
  .wine-e-label-rest-kicker{display:inline-flex;align-items:center;gap:8px;padding:6px 12px;border-radius:999px;background:#fff3ea;color:#8a3c17;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;margin-bottom:14px;}
  .wine-e-label-rest-guide .wine-e-label-rest-kicker{background:#eaf6fd;color:#0b5671;}
  .wine-e-label-rest-lead{margin:0 0 18px;color:#5f534c;line-height:1.6;max-width:70ch;}
  .wine-e-label-card-lead{margin:0 0 16px;color:#5f534c;line-height:1.55;}
  .wine-e-label-settings-stack{display:grid;gap:16px;}
  .wine-e-label-setting{padding-top:16px;border-top:1px solid #f0e8e2;}
  .wine-e-label-setting:first-child{padding-top:0;border-top:none;}
  .wine-e-label-setting label strong,.wine-e-label-setting > strong{display:block;margin-bottom:6px;font-size:14px;}
  .wine-e-label-setting .description{margin:6px 0 0;color:#6b625c;line-height:1.5;}
  .wine-e-label-setting input[type="text"],
  .wine-e-label-setting input[type="password"],
  .wine-e-label-setting select{width:100%;max-width:520px;}
  .wine-e-label-choice-list{display:grid;gap:10px;margin-top:4px;}
  .wine-e-label-choice-list label{display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border:1px solid #eadfd8;border-radius:12px;background:#fcfaf8;}
  .wine-e-label-choice-list label:hover{background:#fffdfb;border-color:#dbc3b5;}
  .wine-e-label-choice-list input{margin-top:2px;}
  .wine-e-label-subtle-code{display:block;margin-top:10px;padding:10px 12px;border:1px solid #ece2da;border-radius:12px;background:#f7f4f1;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,Liberation Mono,Courier New,monospace;font-size:12px;word-break:break-all;}
  .wine-e-label-rest-fields{display:grid;grid-template-columns:180px minmax(0,1fr);gap:14px 18px;align-items:start;}
  .wine-e-label-rest-fields .description{margin:6px 0 0;}
  .wine-e-label-rest-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:10px;}
  .wine-e-label-rest-status{font-weight:500;}
  .wine-e-label-rest-status-line{display:block;margin-top:2px;}
  .wine-e-label-rest-status-line.is-success{color:#1d7f31;}
  .wine-e-label-rest-status-line.is-error{color:#b32d2e;}
  .wine-e-label-rest-status.is-error{color:#b32d2e;}
  .wine-e-label-password-wrap{display:flex;gap:8px;align-items:center;}
  .wine-e-label-password-wrap input{flex:1 1 auto;}
  .wine-e-label-rest-guide ol{margin:14px 0 0;padding:0;list-style:none;display:grid;gap:10px;counter-reset:receiver-steps;}
  .wine-e-label-rest-guide li{position:relative;padding:12px 12px 12px 48px;border:1px solid #d8e7f0;border-radius:12px;background:#fff;line-height:1.5;counter-increment:receiver-steps;}
  .wine-e-label-rest-guide li::before{content:counter(receiver-steps);position:absolute;left:12px;top:12px;display:grid;place-items:center;width:24px;height:24px;border-radius:999px;background:#0b5671;color:#fff;font-size:12px;font-weight:700;}
  .wine-e-label-rest-guide p{margin:0 0 8px;}
  .wine-e-label-setup-intro{margin-top:24px;display:grid;gap:16px;}
  .wine-e-label-setup-grid{margin-top:18px;display:grid;gap:18px;}
  .wine-e-label-setup-columns{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;}
  .wine-e-label-setup-card p{margin:0 0 10px;line-height:1.5;}
  .wine-e-label-setup-card ul,.wine-e-label-setup-card ol{margin:8px 0 0 18px;line-height:1.6;}
  .wine-e-label-setup-note{border-left:4px solid #2271b1;background:#f0f6fc;padding:12px 14px;border-radius:6px;}
  .wine-e-label-setup-warning{border-left-color:#dba617;background:#fff8e5;}
  .wine-e-label-setup-example{display:block;margin-top:8px;padding:10px 12px;border:1px solid #dcdcde;border-radius:6px;background:#f6f7f7;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,Liberation Mono,Courier New,monospace;font-size:12px;word-break:break-all;}
  .wine-e-label-setup-list-title{font-weight:600;margin-bottom:8px;display:block;}
  .wine-e-label-setup-faq-item{padding-top:14px;margin-top:14px;border-top:1px solid #f0f0f1;}
  .wine-e-label-setup-faq-item:first-child{padding-top:0;margin-top:0;border-top:none;}
  .wine-e-label-form-actions{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:18px 20px;border-radius:16px;background:#fff;border:1px solid #ded4cd;box-shadow:0 10px 26px rgba(35,23,15,.04);}
  .wine-e-label-form-actions p{margin:0;color:#675d56;}
  .wine-e-label-ops-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;margin-top:20px;}
  .wine-e-label-ops-card p{margin:0 0 10px;line-height:1.55;color:#5f534c;}
  .wine-e-label-info-list{margin:12px 0 0;padding:0;list-style:none;display:grid;gap:12px;}
  .wine-e-label-info-list li{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;padding-top:12px;border-top:1px solid #f1ebe6;}
  .wine-e-label-info-list li:first-child{padding-top:0;border-top:none;}
  .wine-e-label-info-list strong{display:block;font-size:13px;color:#5f534c;}
  .wine-e-label-info-list code{font-size:12px;word-break:break-all;}
  .wine-e-label-actions{margin-top:0;}
  .wine-e-label-actions form{margin:0;}
  .wine-e-label-tabs.nav-tab-wrapper{margin-top:0;padding:6px;background:#f3efeb;border:1px solid #e2d5cc;border-radius:14px;}
  .wine-e-label-tabs .nav-tab{margin-left:0;border:none;background:transparent;border-radius:10px;padding:10px 14px;font-weight:600;color:#5c514a;}
  .wine-e-label-tabs .nav-tab:hover{background:#fff;}
  .wine-e-label-tabs .nav-tab-active{background:#fff;color:#1d2327;box-shadow:0 4px 12px rgba(31,17,9,.06);}
  @media (max-width: 960px){.wine-e-label-hero-grid,.wine-e-label-rest-grid,.wine-e-label-setup-columns,.wine-e-label-general-columns,.wine-e-label-ops-grid,.wine-e-label-metrics{grid-template-columns:1fr;}.wine-e-label-rest-fields{grid-template-columns:1fr;}.wine-e-label-form-actions{align-items:flex-start;flex-direction:column;}}
</style>
<div class="wrap">
  <div class="wine-e-label-shell">
  <div class="wine-e-label-hero">
    <div class="wine-e-label-hero-grid">
      <div>
        <span class="wine-e-label-eyebrow"><?php esc_html_e('Receiver First', 'wine-e-label'); ?></span>
        <h2><?php esc_html_e('Wein E-Label Einstellungen', 'wine-e-label'); ?></h2>
          <p><?php esc_html_e('Für produktive Setups sollte zuerst die externe Receiver-Verbindung sauber eingerichtet werden. Danach lassen sich QR-Code, Routing und öffentliche Ziel-URLs für den Live-Betrieb feinjustieren.', 'wine-e-label'); ?></p>
          <div class="wine-e-label-hero-note"><?php esc_html_e('Die allgemeinen Einstellungen steuern nicht nur QR-Dateien, sondern den gesamten Auslieferungsweg deiner E-Label-Seiten: lokal, per Subdomain oder über eine getrennte Receiver-Seite.', 'wine-e-label'); ?></div>
      </div>
      <div class="wine-e-label-hero-side">
        <div class="wine-e-label-hero-mini">
          <strong><?php esc_html_e('Empfohlener Live-Weg', 'wine-e-label'); ?></strong>
          <span><?php esc_html_e('Externe Receiver-Seite per REST API mit reduziertem Pflichtseiten-Output.', 'wine-e-label'); ?></span>
        </div>
        <div class="wine-e-label-hero-mini">
          <strong><?php esc_html_e('Aktueller Modus', 'wine-e-label'); ?></strong>
          <span><?php echo esc_html($publish_mode_summary); ?></span>
        </div>
      </div>
    </div>
    <div class="wine-e-label-metrics">
      <div class="wine-e-label-metric">
        <span class="wine-e-label-metric-label"><?php esc_html_e('Aktive Labels', 'wine-e-label'); ?></span>
        <span class="wine-e-label-metric-value"><?php echo esc_html((string) $active_count); ?></span>
      </div>
      <div class="wine-e-label-metric">
                    <span class="wine-e-label-metric-label"><?php esc_html_e('Öffentliche Ziel-URL', 'wine-e-label'); ?></span>
        <span class="wine-e-label-metric-value"><code><?php echo esc_html($public_base_summary); ?>/[slug]</code></span>
      </div>
      <div class="wine-e-label-metric">
        <span class="wine-e-label-metric-label"><?php esc_html_e('Lokale Route', 'wine-e-label'); ?></span>
        <span class="wine-e-label-metric-value"><code><?php echo esc_html($local_route_summary); ?>/[slug]</code></span>
      </div>
    </div>
  </div>

  <nav class="nav-tab-wrapper wine-e-label-tabs">
    <a href="<?php echo esc_url(add_query_arg('tab', 'setup', $settings_base_url)); ?>" class="nav-tab <?php echo $current_tab === 'setup' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html($setup_lang['tab']); ?></a>
    <a href="<?php echo esc_url(add_query_arg('tab', 'general', $settings_base_url)); ?>" class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Allgemein', 'wine-e-label'); ?></a>
    <a href="<?php echo esc_url(add_query_arg('tab', 'language', $settings_base_url)); ?>" class="nav-tab <?php echo $current_tab === 'language' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Sprache', 'wine-e-label'); ?></a>
  </nav>

  <?php if ($current_tab === 'setup') : ?>
    <div class="wine-e-label-setup-intro">
      <div class="wine-e-label-setup-card">
        <h2><?php echo esc_html($setup_lang['intro_title']); ?></h2>
        <p><?php echo esc_html($setup_lang['intro_text']); ?></p>
        <div class="wine-e-label-setup-note"><?php echo esc_html($setup_lang['neutral_note']); ?></div>
      </div>
      <div class="wine-e-label-setup-card">
        <h3><?php echo esc_html($setup_disclaimer['title']); ?></h3>
        <p><?php echo esc_html($setup_disclaimer['text']); ?></p>
        <div class="wine-e-label-setup-note wine-e-label-setup-warning"><?php echo esc_html($setup_disclaimer['legal']); ?></div>
      </div>
    </div>

    <div class="wine-e-label-setup-grid">
      <div class="wine-e-label-setup-columns">
        <div class="wine-e-label-setup-card">
          <h3><?php echo esc_html($setup_lang['card1_title']); ?></h3>
          <p><strong><?php echo esc_html($setup_lang['import_title']); ?></strong></p>
          <p><?php echo esc_html($setup_lang['import_text']); ?></p>
          <ol>
            <?php foreach ($setup_lang['import_steps'] as $step) : ?>
              <li><?php echo esc_html($step); ?></li>
            <?php endforeach; ?>
          </ol>
          <p style="margin-top:14px;"><strong><?php echo esc_html($setup_lang['manual_title']); ?></strong></p>
          <p><?php echo esc_html($setup_lang['manual_text']); ?></p>
        </div>

        <div class="wine-e-label-setup-card">
          <h3><?php echo esc_html($setup_lang['card2_title']); ?></h3>
          <?php foreach ($setup_lang['publish_options'] as $option) : ?>
            <div class="wine-e-label-setup-faq-item">
              <p><strong><?php echo esc_html($option['title']); ?></strong></p>
              <p><?php echo esc_html($option['text']); ?></p>
              <span class="wine-e-label-setup-example"><?php echo esc_html($option['example']); ?></span>
              <p style="margin-top:10px;"><?php echo esc_html($option['note']); ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="wine-e-label-setup-columns">
        <div class="wine-e-label-setup-card">
          <h3><?php echo esc_html($setup_lang['card3_title']); ?></h3>
          <ol>
            <?php foreach ($setup_lang['receiver_steps'] as $step) : ?>
              <li><?php echo esc_html($step); ?></li>
            <?php endforeach; ?>
          </ol>
          <div class="wine-e-label-setup-note wine-e-label-setup-warning" style="margin-top:16px;">
            <strong><?php echo esc_html($setup_lang['receiver_hint_title']); ?></strong><br>
            <?php echo esc_html($setup_lang['receiver_hint']); ?>
          </div>
        </div>

        <div class="wine-e-label-setup-card">
          <h3><?php echo esc_html($setup_lang['card4_title']); ?></h3>
          <p><?php echo esc_html($setup_lang['test_text']); ?></p>
          <ul>
            <?php foreach ($setup_lang['test_routes'] as $route) : ?>
              <li><?php echo esc_html($route); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <div class="wine-e-label-setup-columns">
        <div class="wine-e-label-setup-card">
          <h3><?php echo esc_html($setup_lang['card5_title']); ?></h3>
          <ul>
            <li><?php echo esc_html($setup_lang['fit_local']); ?></li>
            <li><?php echo esc_html($setup_lang['fit_sub']); ?></li>
            <li><?php echo esc_html($setup_lang['fit_rest']); ?></li>
          </ul>
          <div class="wine-e-label-setup-note" style="margin-top:16px;"><?php echo esc_html($setup_lang['fit_recommendation']); ?></div>
        </div>

        <div class="wine-e-label-setup-card">
          <h3><?php echo esc_html($setup_lang['order_title']); ?></h3>
          <ol>
            <?php foreach ($setup_lang['order_steps'] as $step) : ?>
              <li><?php echo esc_html($step); ?></li>
            <?php endforeach; ?>
          </ol>
        </div>
      </div>

      <div class="wine-e-label-setup-card">
        <h3><?php echo esc_html($setup_lang['faq_title']); ?></h3>
        <?php foreach ($setup_lang['faq_items'] as $item) : ?>
          <div class="wine-e-label-setup-faq-item">
            <p><strong><?php echo esc_html($item['q']); ?></strong></p>
            <p><?php echo esc_html($item['a']); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php else : ?>
    <form method="post" action="" class="<?php echo esc_attr($current_tab === 'general' ? 'wine-e-label-general-form' : ''); ?>" style="margin-top:18px;">
      <?php
      if (class_exists('Wine_E_Label_Admin_Extended')) {
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr(Wine_E_Label_Admin_Extended::get_settings_nonce()) . '">';
      }
      ?>

      <?php if ($current_tab === 'general') : ?>
        <table class="form-table" role="presentation">
          <tbody>
            <tr>
              <th scope="row"><label for="qr_size"><?php esc_html_e('QR-Code-Größe', 'wine-e-label'); ?></label></th>
              <td>
                <select name="wine_e_label_settings[qr_size]" id="qr_size"><?php echo qr_size_options($current_qr_size); ?></select>
                <p class="description"><?php esc_html_e('Pixel dimensions of the downloaded QR image. For print use SVG format is recommended — it is resolution-independent and will be crisp at any size regardless of this setting.', 'wine-e-label'); ?></p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="qr_format"><?php esc_html_e('QR-Code-Format', 'wine-e-label'); ?></label></th>
              <td>
                <select name="wine_e_label_settings[qr_format]" id="qr_format"><?php echo qr_format_options($current_qr_format); ?></select>
                <p class="description"><?php esc_html_e('SVG is a vector format — edges stay perfectly sharp at any print size and is recommended for wine labels. PNG is a pixel-based image; choose a large size if using PNG for print.', 'wine-e-label'); ?></p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="qr_error_correction"><?php esc_html_e('Fehlerkorrektur', 'wine-e-label'); ?></label></th>
              <td>
                <select name="wine_e_label_settings[qr_error_correction]" id="qr_error_correction"><?php echo qr_error_correction_options($current_qr_correction); ?></select>
                <p class="description"><?php esc_html_e('Higher correction levels add redundant data so the code can still scan if partially damaged, but produce a denser, more complex pattern. For small clean wine labels (18 mm) Low is recommended — it produces the fewest modules and is easiest to scan.', 'wine-e-label'); ?></p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="base_url"><?php esc_html_e('E-Label-Basis-URL', 'wine-e-label'); ?></label></th>
              <td>
                <input type="text" name="wine_e_label_settings[base_url]" id="base_url" value="<?php echo esc_attr($current_base_url); ?>" placeholder="<?php echo esc_attr(Wine_E_Label_URL::get_local_base_url()); ?>" class="regular-text code">
                <p class="description"><?php esc_html_e('Optional override for the public e-label target URL. Leave empty to use this WordPress domain automatically. The product slug is appended automatically.', 'wine-e-label'); ?></p>
                <p class="description"><?php esc_html_e('The local WordPress route always stays on /l/[slug] for this installation. This field only changes the public target URL and does not rewrite normal WordPress pages.', 'wine-e-label'); ?></p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="use_subdomain"><?php esc_html_e('Subdomain E-Label URLs', 'wine-e-label'); ?></label></th>
              <td>
                <label><input type="checkbox" name="wine_e_label_settings[use_subdomain]" id="use_subdomain" value="1" <?php checked('yes', get_option('wine_e_label_use_subdomain', 'no')); ?>> <?php esc_html_e('Enable subdomain e-label URLs', 'wine-e-label'); ?></label>
                <p class="description"><?php esc_html_e('Serve e-labels from a dedicated subdomain as recommended by EU e-labelling regulations.', 'wine-e-label'); ?></p>
                <p class="description"><?php esc_html_e('Dedicated subdomains are handled separately. The main domain keeps its normal WordPress pages untouched, while local labels still remain available on /l/[slug].', 'wine-e-label'); ?></p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="subdomain"><?php esc_html_e('Subdomain', 'wine-e-label'); ?></label></th>
              <td>
                <input type="text" name="wine_e_label_settings[subdomain]" id="subdomain" value="<?php echo esc_attr(get_option('wine_e_label_subdomain', '')); ?>" placeholder="elabel" class="regular-text">
                <p class="description"><?php esc_html_e('Enter a subdomain (e.g. elabel) or a full hostname (e.g. elabel.example.com). The subdomain must resolve to this WordPress installation.', 'wine-e-label'); ?></p>
                <p class="description"><?php esc_html_e('Do not use a root URL here to replace normal WordPress page slugs. The plugin keeps local label routing on /l/[slug] and only uses the dedicated host for explicit e-label requests.', 'wine-e-label'); ?></p>
              </td>
            </tr>
            <tr>
              <th scope="row"><?php esc_html_e('URL Scheme', 'wine-e-label'); ?></th>
              <td>
                <?php $current_scheme = get_option('wine_e_label_subdomain_scheme', 'https'); ?>
                <label><input type="radio" name="wine_e_label_settings[subdomain_scheme]" value="https" <?php checked('https', $current_scheme); ?>> <?php esc_html_e('https (recommended)', 'wine-e-label'); ?></label><br>
                <label><input type="radio" name="wine_e_label_settings[subdomain_scheme]" value="http" <?php checked('http', $current_scheme); ?>> <?php esc_html_e('http', 'wine-e-label'); ?></label>
              </td>
            </tr>
            <tr>
              <th scope="row"><?php esc_html_e('Delete Data on Uninstall', 'wine-e-label'); ?></th>
              <td>
                <label><input type="checkbox" name="wine_e_label_settings[delete_data_on_uninstall]" id="delete_data_on_uninstall" value="1" <?php checked('yes', get_option('wine_e_label_delete_data_on_uninstall', 'no')); ?>> <?php esc_html_e('Permanently delete all nutrition label data when the plugin is uninstalled', 'wine-e-label'); ?></label>
                <p class="description" style="color:#d63638;"><?php esc_html_e('Use with caution — this removes database records, generated URLs and QR references when uninstalling the plugin.', 'wine-e-label'); ?></p>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="wine-e-label-rest-grid">
          <div class="wine-e-label-rest-card">
            <span class="wine-e-label-rest-kicker"><?php esc_html_e('Receiver zuerst', 'wine-e-label'); ?></span>
            <h2><?php esc_html_e('Externe Receiver-Seite per REST API', 'wine-e-label'); ?></h2>
            <p class="wine-e-label-rest-lead"><?php esc_html_e('Wenn du das Plugin produktiv nutzt, richte hier zuerst die externe Zielseite ein. Diese Variante trennt Pflichtinformationen sauber von Shop, Marketing und Tracking und ist deshalb die wichtigste Einstellung auf dieser Seite.', 'wine-e-label'); ?></p>
            <div class="wine-e-label-rest-fields">
              <div>
                <label for="wine_e_label_rest_enabled"><strong><?php esc_html_e('REST API Verbindung aktivieren', 'wine-e-label'); ?></strong></label>
              </div>
              <div>
                <label><input type="checkbox" name="wine_e_label_settings[rest_enabled]" id="wine_e_label_rest_enabled" value="1" <?php checked('yes', $current_rest_enabled); ?>> <?php esc_html_e('Externe E-Label-Seite per Receiver-Plugin verwenden', 'wine-e-label'); ?></label>
                <p class="description"><?php esc_html_e('Empfohlener Weg für produktive Setups: Die Pflichtinformationen werden auf einer getrennten WordPress-Seite veröffentlicht.', 'wine-e-label'); ?></p>
              </div>

              <div>
                <label for="wine_e_label_rest_base_url"><strong><?php esc_html_e('Receiver Basis-URL', 'wine-e-label'); ?></strong></label>
              </div>
              <div>
                <input type="text" name="wine_e_label_settings[rest_base_url]" id="wine_e_label_rest_base_url" value="<?php echo esc_attr($current_rest_base_url); ?>" placeholder="https://deine-label-domain.de" class="regular-text code">
                <p class="description"><?php esc_html_e('Basis-Domain der externen WordPress-Seite mit installiertem Reith E-Label Receiver. Keine /wp-json-Adresse eintragen.', 'wine-e-label'); ?></p>
              </div>

              <div>
                <label for="wine_e_label_rest_username"><strong><?php esc_html_e('REST API Benutzername', 'wine-e-label'); ?></strong></label>
              </div>
              <div>
                <input type="text" name="wine_e_label_settings[rest_username]" id="wine_e_label_rest_username" value="<?php echo esc_attr($current_rest_username); ?>" class="regular-text">
                <p class="description"><?php esc_html_e('WordPress-Benutzername der externen Receiver-Seite.', 'wine-e-label'); ?></p>
              </div>

              <div>
                <label for="wine_e_label_rest_app_password"><strong><?php esc_html_e('REST API Application Password', 'wine-e-label'); ?></strong></label>
              </div>
              <div>
                <div class="wine-e-label-password-wrap">
                  <input type="password" name="wine_e_label_settings[rest_app_password]" id="wine_e_label_rest_app_password" value="" class="regular-text" autocomplete="new-password" placeholder="<?php echo $current_rest_password !== '' ? esc_attr('••••••••••••') : ''; ?>">
                  <button type="button" class="button button-secondary" id="wine_e_label_toggle_rest_password"><?php esc_html_e('Anzeigen', 'wine-e-label'); ?></button>
                </div>
                <p class="description"><?php esc_html_e('Application Password des WordPress-Benutzers auf der externen Receiver-Seite.', 'wine-e-label'); ?></p>
                <p class="description"><?php esc_html_e('Leer lassen, um das bereits gespeicherte Passwort beizubehalten.', 'wine-e-label'); ?></p>
              </div>
            </div>

            <div class="wine-e-label-rest-actions">
              <button type="button" class="button button-secondary" id="wine_e_label_test_rest_connection"><?php esc_html_e('Verbindung testen', 'wine-e-label'); ?></button>
              <span class="wine-e-label-rest-status" id="wine_e_label_rest_test_status" aria-live="polite"></span>
            </div>
          </div>

          <aside class="wine-e-label-rest-guide">
            <span class="wine-e-label-rest-kicker"><?php esc_html_e('Start here', 'wine-e-label'); ?></span>
            <h3><?php esc_html_e('So richtest du die Verbindung ein', 'wine-e-label'); ?></h3>
            <p class="wine-e-label-rest-lead"><?php esc_html_e('Arbeite diese drei Schritte einmal sauber durch. Danach kannst du die Verbindung direkt testen und erst anschliessend QR, Basis-URL und lokale Alternativen feinjustieren.', 'wine-e-label'); ?></p>
            <ol>
              <li><?php esc_html_e('Auf der Zielseite das Plugin Reith E-Label Receiver installieren und aktivieren', 'wine-e-label'); ?></li>
              <li><?php esc_html_e('Dort einen Benutzer anlegen, z. B. api_elabel', 'wine-e-label'); ?></li>
              <li><?php esc_html_e('Unter Benutzer > Profil ein Application Password erzeugen und hier Basis-URL, Benutzername und Passwort eintragen', 'wine-e-label'); ?></li>
            </ol>
          </aside>
        </div>
      <?php else : ?>
        <table class="form-table" role="presentation">
          <tbody>
            <tr>
              <th scope="row"><label for="wine_e_label_admin_language"><?php esc_html_e('Werkzeugsprache', 'wine-e-label'); ?></label></th>
              <td>
                <select name="wine_e_label_settings[admin_language]" id="wine_e_label_admin_language">
                  <?php foreach (Wine_E_Label_Admin_I18n::get_language_options() as $code => $label) : ?>
                    <option value="<?php echo esc_attr($code); ?>" <?php selected($current_lang, $code); ?>><?php echo esc_html($label); ?></option>
                  <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e('Wähle die Sprache für die Plugin-Oberfläche im WordPress-Adminbereich.', 'wine-e-label'); ?></p>
              </td>
            </tr>
          </tbody>
        </table>
      <?php endif; ?>

      <?php submit_button(__('Einstellungen speichern', 'wine-e-label'), 'primary', 'submit-wine-e-label-settings'); ?>
    </form>

    <?php if ($current_tab === 'general') : ?>
      <div class="wine-e-label-ops-grid">
        <div class="wine-e-label-ops-card wine-e-label-actions">
          <h3><?php esc_html_e('Routing und Rewrite', 'wine-e-label'); ?></h3>
          <p><?php esc_html_e('Wenn Short-URLs oder Label-Routen nicht sauber reagieren, kannst du hier die WordPress-Rewrite-Regeln neu aufbauen.', 'wine-e-label'); ?></p>
        <form method="post" action="">
          <?php wp_nonce_field('flush_rewrite_rules', '_wpnonce_flush'); ?>
          <input type="hidden" name="action" value="flush_rewrite_rules">
          <button type="submit" class="button button-secondary">🔄 <?php esc_html_e('Rewrite-Regeln aktualisieren', 'wine-e-label'); ?></button>
        </form>
        </div>

      <script>
        jQuery(document).ready(function($) {
          function toggleSubdomainFields() {
            var enabled = $('#use_subdomain').is(':checked');
            $('#subdomain').prop('disabled', !enabled);
            $('input[name="wine_e_label_settings[subdomain_scheme]"]').prop('disabled', !enabled);
          }
          function toggleRestFields() {
            var enabled = $('#wine_e_label_rest_enabled').is(':checked');
            $('#wine_e_label_rest_base_url, #wine_e_label_rest_username, #wine_e_label_rest_app_password, #wine_e_label_toggle_rest_password, #wine_e_label_test_rest_connection').prop('disabled', !enabled);
            if (!enabled) {
              $('#wine_e_label_rest_test_status').text('').removeClass('is-success is-error');
            }
          }
          toggleSubdomainFields();
          toggleRestFields();
          $('#use_subdomain').on('change', toggleSubdomainFields);
          $('#wine_e_label_rest_enabled').on('change', toggleRestFields);
          $('#wine_e_label_toggle_rest_password').on('click', function(){
            var $input = $('#wine_e_label_rest_app_password');
            var isPassword = $input.attr('type') === 'password';
            $input.attr('type', isPassword ? 'text' : 'password');
            $(this).text(isPassword ? <?php echo wp_json_encode(__('Verbergen', 'wine-e-label')); ?> : <?php echo wp_json_encode(__('Anzeigen', 'wine-e-label')); ?>);
          });
          $('#wine_e_label_test_rest_connection').on('click', function(){
            var $status = $('#wine_e_label_rest_test_status');
            $status.removeClass('is-success is-error').text(<?php echo wp_json_encode(__('Verbindung wird geprüft …', 'wine-e-label')); ?>);
            $.ajax({
              url: ajaxurl,
              type: 'POST',
              dataType: 'json',
              data: {
                action: 'wine_e_label_test_rest_connection',
                _wpnonce: <?php echo wp_json_encode($rest_test_nonce); ?>,
                base_url: $('#wine_e_label_rest_base_url').val(),
                username: $('#wine_e_label_rest_username').val(),
                app_password: $('#wine_e_label_rest_app_password').val()
              }
            }).done(function(response){
              if (response && response.success) {
                if (response.data && response.data.lines && response.data.lines.length) {
                  var html = response.data.lines.map(function(line){
                    var typeClass = line.type === 'error' ? 'is-error' : 'is-success';
                    return '<span class="wine-e-label-rest-status-line ' + typeClass + '">' + $('<div>').text(line.text || '').html() + '</span>';
                  }).join('');
                  $status.html(html);
                } else {
                  $status.addClass('is-success').text(response.data && response.data.message ? response.data.message : <?php echo wp_json_encode(__('Verbindung erfolgreich geprüft.', 'wine-e-label')); ?>);
                }
              } else {
                $status.addClass('is-error').text(response && response.data && response.data.message ? response.data.message : <?php echo wp_json_encode(__('Verbindung konnte nicht geprüft werden.', 'wine-e-label')); ?>);
              }
            }).fail(function(xhr){
              var message = <?php echo wp_json_encode(__('Verbindung konnte nicht geprüft werden.', 'wine-e-label')); ?>;
              if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                message = xhr.responseJSON.data.message;
              }
              $status.addClass('is-error').text(message);
            });
          });

          $('form input[name="action"][value="flush_rewrite_rules"]').closest('form').submit(function(e) {
            e.preventDefault();
            $.ajax({
              url: ajaxurl,
              type: 'POST',
              data: {
                action: 'flush_rewrite_rules',
                _wpnonce_flush: $('input[name="_wpnonce_flush"]').val()
              },
              success: function(response) {
                if (response.success) {
                  alert(response.message);
                } else {
                  alert('<?php echo esc_js(__('Fehler: Rewrite-Regeln konnten nicht aktualisiert werden.', 'wine-e-label')); ?>');
                }
              },
              error: function() {
                alert('<?php echo esc_js(__('Fehler: Rewrite-Regeln konnten nicht aktualisiert werden.', 'wine-e-label')); ?>');
              }
            });
          });
        });
      </script>

      <div class="wine-e-label-ops-card wine-e-label-info">
        <h3><?php esc_html_e('Aktuelle Konfiguration', 'wine-e-label'); ?></h3>
        <p><?php esc_html_e('Hier siehst du den aktuellen Live-Stand der wichtigsten Routing- und QR-Einstellungen.', 'wine-e-label'); ?></p>
        <ul class="wine-e-label-info-list">
          <li><strong><?php esc_html_e('Current Entries:', 'wine-e-label'); ?></strong> <?php printf(esc_html__('%d nutrition labels active', 'wine-e-label'), $active_count); ?></li>
          <li><strong><?php esc_html_e('E-Label-Basis-URL', 'wine-e-label'); ?>:</strong> <code><?php echo esc_html(Wine_E_Label_URL::get_public_base_url(false)); ?>/[slug]</code></li>
          <li><strong><?php esc_html_e('QR-Code-Größe', 'wine-e-label'); ?>:</strong> <code><?php echo esc_html(get_option('qr_size', '500x500')); ?></code></li>
          <li><strong><?php esc_html_e('QR-Code-Format', 'wine-e-label'); ?>:</strong> <code><?php echo esc_html(strtoupper(get_option('qr_format', 'png'))); ?></code></li>
          <li><strong><?php esc_html_e('Fehlerkorrektur', 'wine-e-label'); ?>:</strong> <code><?php echo esc_html(ucfirst(get_option('qr_error_correction', 'low'))); ?></code></li>
        </ul>
      </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
</div>
