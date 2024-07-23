# Log Modul

## Beschreibung
Das Log Modul des THM Security Plugins protokolliert Zugriffe auf die Webseite und stellt eine Verwaltungsseite zur Anzeige und Verwaltung dieser Protokolle bereit.

## Technische Beschreibung
Das Modul registriert verschiedene Hooks und Filter, um die Protokollierung der Zugriffe zu ermöglichen und eine Verwaltungsseite im WordPress-Adminbereich bereitzustellen. Es wird bei der Initialisierung des Plugins geladen.

## Verwendete Hooks/Filter
- add_action('admin_menu', ['\THM\Security\Log', 'add_menu']);
- add_action('wp_loaded', ['\THM\Security\Log', 'log_access']);
- add_filter('xmlrpc_enabled', '__return_false');

### Verwendete Funktionen
1. add_menu(): void
   Diese Funktion fügt einen Menüpunkt zur Verwaltung von Zugriffsprotokollen im WordPress-Adminbereich hinzu.

2. render_management_page(): void
   Diese Funktion rendert die Verwaltungsseite, auf der die Zugriffsprotokolle angezeigt werden.

3. render_access_log(): void
   Diese Funktion rendert die Tabelle der Zugriffsprotokolle auf der Verwaltungsseite.

4. log_access(): void
   Diese Funktion protokolliert Zugriffe auf die Webseite in der Datenbank.

### Anwendung der Funktionen
1. add_menu(): void
   Beispiel:

```php
\THM\Security\Log::add_menu();
```
2. render_management_page(): void
   Beispiel:

``` php
\THM\Security\Log::render_management_page();
```

3. render_access_log(): void
   Beispiel:

``` php
\THM\Security\Log::render_access_log();
```

4. log_access(): void
   Beispiel:

```php
\THM\Security\Log::log_access();
```

Beispiele von Funktionsaufrufen
1. Normale Anwendung
   ```php
   // Hook-Registrierung
   add_action('admin_menu', ['\THM\Security\Log', 'add_menu']);
   add_action('wp_loaded', ['\THM\Security\Log', 'log_access']);
    
    // Beispiel für die Anzeige der Verwaltungsseite
    \THM\Security\Log::render_management_page();

    // Beispiel für die Protokollierung eines Zugriffs
    \THM\Security\Log::log_access();
    ``` 
## Navigation
- [Plugin](/README.md)
- [Leaks](../docs/leaks.md)
- [Classifier](../docs/classifier.md)
- [IP-Blocker](../docs/ip-blocker.md)
- [Database](../docs/database)