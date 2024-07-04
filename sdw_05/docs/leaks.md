# sdw_05 Plugin

## Beschreibung

Das `sdw_05` Plugin verhindert die Anzeige von Benutzernamen an öffentlichen Orten und ersetzt sie durch den Nicknamen oder "Anonym".

## Technische Beschreibung

Das Plugin verwendet verschiedene Hooks und Filter, um die Anzeige von Benutzernamen zu verhindern und stattdessen Nicknamen oder "Anonym" anzuzeigen. Es registriert diese Hooks und Filter bei der Initialisierung.

## Verwendete personenbezogene Daten

- `get_user_by('login', $name)` : Benutzerinformationen anhand des Loginnamens.
- `get_user_by('id', $author_id)` : Benutzerinformationen anhand der Benutzer-ID.
- `get_users(array('fields' => array('ID', 'user_login', 'display_name')))` : Liste der Benutzer mit spezifischen Feldern.
- `get_userdata($user_id)` : Benutzerinformationen anhand der Benutzer-ID.

## Verwendete Hooks/Filter

- `add_filter('the_author', 'hide_usernames');`
- `add_filter('get_comment_author', 'hide_usernames');`
- `add_filter('comment_author', 'hide_usernames');`
- `add_filter('author_link', 'disable_author_links', 10, 2);`
- `add_filter('rest_prepare_user', 'hide_usernames_in_rest', 10, 3);`
- `add_action('admin_notices', 'check_display_name_vs_username');`
- `add_action('profile_update', 'force_display_name_update', 10, 2);`
- `add_filter('get_the_author_display_name', 'hide_usernames');`
- `add_filter('wp_nav_menu_items', 'hide_usernames_in_menu', 10, 2);`

## Verwendete Funktionen

### 1. `hide_usernames($name): string`

Diese Funktion ersetzt den Benutzernamen durch den Nicknamen oder "Anonym", falls der Nickname leer ist oder dem Benutzernamen entspricht.

### 2. `disable_author_links($link, $author_id): string`

Diese Funktion entfernt Autor-Links, indem sie das Linkziel durch `'#'` ersetzt.

### 3. `hide_usernames_in_rest($response, $user, $request): WP_REST_Response`

Diese Funktion entfernt den Benutzernamen aus den REST API Antworten und ersetzt ihn durch den Nicknamen oder "Anonym".

### 4. `check_display_name_vs_username(): void`

Diese Funktion zeigt eine Admin-Warnung an, wenn der öffentliche Name eines Benutzers dem Benutzernamen entspricht.

### 5. `force_display_name_update($user_id, $old_user_data): void`

Diese Funktion erzwingt die Aktualisierung des öffentlichen Namens auf den Nicknamen oder "Anonym", falls der öffentliche Name dem Benutzernamen entspricht.

### 6. `hide_usernames_in_menu($items, $args): array`

Diese Funktion ersetzt Benutzernamen in Menüs durch den Nicknamen oder "Anonym".

## Anwendung der Funktionen

### 1. `hide_usernames($name): string`

Beispiel:
$author_name = hide_usernames('username123');
echo $author_name; // Gibt den Nicknamen oder 'Anonym' aus.

### 2. `disable_author_links($link, $author_id): string`

Diese Funktion entfernt Autor-Links, indem sie das Linkziel durch `'#'` ersetzt.

### 3. `hide_usernames_in_rest($response, $user, $request): WP_REST_Response`

Diese Funktion entfernt den Benutzernamen aus den REST API Antworten und ersetzt ihn durch den Nicknamen oder "Anonym".

### 4. `check_display_name_vs_username(): void`

Diese Funktion zeigt eine Admin-Warnung an, wenn der öffentliche Name eines Benutzers dem Benutzernamen entspricht.

### 5. `force_display_name_update($user_id, $old_user_data): void`

Diese Funktion erzwingt die Aktualisierung des öffentlichen Namens auf den Nicknamen oder "Anonym", falls der öffentliche Name dem Benutzernamen entspricht.

### 6. `hide_usernames_in_menu($items, $args): array`

Diese Funktion ersetzt Benutzernamen in Menüs durch den Nicknamen oder "Anonym".

## Anwendung der Funktionen

### 1. `hide_usernames($name): string`

Beispiel:
```php
$author_name = hide_usernames('username123');
echo $author_name; // Gibt den Nicknamen oder 'Anonym' aus.

```

### 2. disable_author_links($link, $author_id): string
Beispiel:

```php
$author_link = disable_author_links('https://example.com/author/username123', 1);
echo $author_link; // Gibt '#' aus.
```

### 3. hide_usernames_in_rest($response, $user, $request): WP_REST_Response
Beispiel:

```php
$response = hide_usernames_in_rest($response, $user, $request);
print_r($response->get_data()); // Zeigt die REST API Antwort ohne Benutzernamen.
```

### 4. check_display_name_vs_username(): void
Beispiel:

```php
check_display_name_vs_username(); // Zeigt eine Admin-Warnung an, falls nötig.
```
### 5. force_display_name_update($user_id, $old_user_data): void
Beispiel:

```php
force_display_name_update(1, $old_user_data); // Aktualisiert den öffentlichen Namen, falls nötig.
```
### 6. hide_usernames_in_menu($items, $args): array
Beispiel:

```php

$menu_items = hide_usernames_in_menu($items, $args);
print_r($menu_items); // Zeigt die Menüeinträge mit Nicknamen oder 'Anonym'.
```
## Beispiele von Funktionsaufrufen
### 1. Normale Anwendung
```php
// Hook-Registrierung
sdw_05_register_hooks();

// Beispiel für die Anzeige eines Benutzernamens
echo hide_usernames('username123'); // Ausgabe: Nickname oder 'Anonym'

// Beispiel für die Entfernung eines Autor-Links
echo disable_author_links('https://example.com/author/username123', 1); // Ausgabe: #
```
## Navigation
- [plugin: README](../README.md)
- [database: README](../docs/database.md)
- [log: README](../docs/log.md)
- [ip-blocker: README](../docs/ip-blocker.md)
- [leaks: README](../docs/leaks.md)