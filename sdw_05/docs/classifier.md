# Classifier Module

## Beschreibung

Das Classifier Module dient zur Überprüfung von eingehenden Anfragen, ob diese von normalen Nutzern stammen oder böswillige Angriffe auf die Website sind. Nach der Überprüfung wird die eingehende Anfrage mit einer Klasse versehen.

## Technische Beschreibung

Das Module ist klassenorientiert aufgebaut und ruft keine eigenen Methoden auf. Die enthaltenen Funktionen werden von anderen Modulen wie dem **['ip-blocker'](../docs/ip-blocker.md)** und **['log'](../docs/log.md)** verwendet, um die Klasse einer Anfrage zu definieren.

## Verwendete personenbezogene Daten

- `'$_SERVER['REMOTE_ADDR']'` : IP-Adresse der eingehenden Anfrage.
- `'$_SERVER['REMOTE_URI']'` : URL der eingehenden Anfrage.
- `'$_SERVER['HTTP_USER_AGENT']'` : User-Agent der eingehenden Anfrage.

## Verwendete Hooks/Filter

- KEINE

## Verwendete Funktionen

 ### 1. `classify_request(): string {...}`

Diese Funktion dient zur Unterteilung der eingehenden Anfragen in Klassen. Dabei wird am Anfang die Variable `$request_class = 'normal'` deklariert und mit einem Standard Wert von `'normal'` initialisiert. Danach erfolgen mehrer Prüfungen, die diesen Wert verändern können. Wenn alle Prüfungen durchlaufen sind, gibt die Funktion die `$request_class` als `string` `return` Wert aus. Die Funktion verwendet alle Personen bezogenen Daten, die innerhalb dieser Markdown Datei beschrieben wurden. Diese werden zur Klassifizierung benötigt und werden durch das PHP Array `$SERVER` erhalten. Es ist keine Eingabe von Parametern nötig. Die verwendeten Prüfungen funktionieren wie folgt:   

>  **1. 'Brute Force Detection'**

Die Brute Force Detection wiederum wird in zwei Kategorien unterteilt: **'Brute Force Login'** auf der **'wp-login'** Seite und **'General Brute Force'** auf der gesamten Website. Ein Brute Force Angriff wird als **'Brute Force Login'** identifiziert, wenn die Datenbank **10** Einlogversuche der **selben** IP-Adresse in den letzten **10 Minuten** erhalten hat.
Dies geschieht durch folgende SQL Query:

`"SELECT COUNT(*) FROM %i WHERE client = %s AND url LIKE %s AND time > now() - interval 10 minute", $table_name, $ip, $brute_force_login_uri`.

Wiederum ein Brute Force Angriff auf jegliche andere Seiten wird ab **50** Anfragen von der **selben** IP-Adresse innerhalb der letzten **5 Minuten** registriert. Dies geschieht durch folgende SQL Query:

`"SELECT COUNT(*) FROM %i WHERE client = %s AND time > now() - interval 5 minute", $table_name, $ip`    

Zur Sicherung gegen SQL Injections wurden `Tokens` in der SQL-Abfrage und `$wpdb->prepare` verwendet.

> **2. 'Access Tool Detection'**

Die Access Tool Detection basiert auf einer Liste von den häufigsten verwendeten User-Agents. Diese sind in einer Variable per RegEx gespeichert.

`$user_agents = "/windows|linux|fedora|ubuntu|macintosh|i-phone|i-pod|i-pad|android|wordpress/i";` 

Es wird geprüft, ob der User Agent der eingehenden Anfrage in dieser Liste vorhanden ist, wenn nicht, wird diese Anfrage als `Access-Tool` kategorisiert.

> **3. 'Pattern Detection'**

Die Pattern Detection verwendet zur Überprüfung ein Array aus Key-Value Pairs, der Key ist hierbei der Name der Klassifizierung z. B. `config-grabber` und die Value besteht aus einem RegEx das ein gewisses Pattern enthält im Falle von dem `config-grabber` = `'/\/wp-config.php/i'`. Es wird über jedes Key-Value Pair des Arrays iteriert und überprüft, ob die `URI` der eingehenden Anfrage oder der `Body` der Anfrage ein Pattern enthält. Wenn ein Pattern übereinstimmen sollte, wird der Name des Keys als Klassifizierung verwendet.

## Anwendung verwendeter Funktionen

### 1. `classify_request(): string {...}`

Beispiel:
````
**** SPEICHERN DES ERGEBNISSES IN EINER VARIABLE ****
$klassifizierung_der_aktuellen_anfrage = classify_request();

**** AUSGABE DER VAIRABLE ****
echo $klassifizierung_der_aktuellen_anfrage;

> normal

````
## Beispiele von Klassifizierungen

### 1. Normale Anfrage
````
**** REQUEST ****
GET /wp-login.php?loggedout HTTP/1.1
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36
Accept: */*
Host: localhost
Accept-Encoding: gzip, deflate, br
Connection: keep-alive
Cookie: wordpress_test_cookie=WP%20Cookie%20check
 
**** RESPONSE ****
HTTP/1.1 200 OK
Date: Fri, 28 Jun 2024 11:32:58 GMT
Server: Apache/2.4.57 (Debian)
X-Powered-By: PHP/8.2.18
Expires: Wed, 11 Jan 1984 05:00:00 GMT
Cache-Control: no-cache, must-revalidate, max-age=0
Set-Cookie: wordpress_test_cookie=WP%20Cookie%20check; path=/
X-Frame-Options: SAMEORIGIN
Referrer-Policy: strict-origin-when-cross-origin
Vary: Accept-Encoding
Content-Encoding: gzip
Content-Length: 1696
Keep-Alive: timeout=5, max=100
Connection: Keep-Alive
Content-Type: text/html; charset=UTF-8
````
Klassifizierung = `'normal'`.

### 2. Böswillige Anfrage
````
**** REQUEST ****
GET /wp-config.php HTTP/1.1
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36
Accept: */*
Host: localhost
Accept-Encoding: gzip, deflate, br
Connection: keep-alive
Cookie: wordpress_test_cookie=WP%20Cookie%20check

**** RESPONSE **** 
HTTP/1.1 200 OK
Date: Fri, 28 Jun 2024 11:36:26 GMT
Server: Apache/2.4.57 (Debian)
X-Powered-By: PHP/8.2.18
Content-Length: 39
Keep-Alive: timeout=5, max=100
Connection: Keep-Alive
Content-Type: text/html; charset=UTF-8
 
Ihre IP-Adresse wurde blockiert aufgrund von Verdacht auf böswillige Absichten! Freigeben der IP-Adresse erfolgt nach 24 Stunden!
````
Klassifizierung = `'config-grabber'`.

## Navigation
 - [plugin: README](../README.md)
 - [database: README](../docs/database.md)
 - [log: README](../docs/log.md)
 - [ip-blocker: README](../docs/ip-blocker.md)
 - [leaks: README](../docs/leaks.md)
