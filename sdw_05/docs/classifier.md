# Classifier Module

## Beschreibung

Das Classifier Module dient zur Überpüfung von eingehenden Anfragen ob diese von normalen Nutzern stammen oder böswillige Angriffe auf die Website sind. Nach der Überprüfung wird die eingehende Anfrage mit einer Klasse versehen.

## Technische Beschreibung

Das Module ist Klassenorientiert aufgebaut und ruft keine eigenen Methoden auf. Die enthaltenen Funktionen werden von anderen Modulen wie dem **'ip-blocker.php'** und **'log.php'** verwendet um die Klasse einer Anfrage zu definieren. 

## Verwendete personenbezogene Daten

- `'$_SERVER['REMOTE_ADDR']'` : IP-Adresse der eingehenden Anfrage
- `'$_SERVER['REMOTE_URI']'` : URL der eingehenden Anfrage
- `'$_SERVER['HTTP_USER_AGENT']'` : User-Agent der eingehenden Anfrage

## Verwendete Hooks/Filter

- KEINE

## Verwendete Funktionen

 ### 1. `classify_request(): string {...}`

Diese Funtkion dient zur unterteilung der eingehenden Anfragen in Klassen. Dabei wird am Anfang die variable `$request_class = 'normal'` deklariert und mit einem standart Wert von `'normal'` initialisiert. Danach erfolgen mehrer Prüfungen, die diesen Wert verändern können. Wenn alle Prüfungen durchlaufen sind gibt die Funktion den Wert als `return` Wert aus. Die verwendetetn Prüfungen funktionieren wie folgt:
   
>  **1. 'Brute Force Detection'**

Die Brute Force Detection wiederum wird in zwei Kategorien unterteilt: **'Brute Force Login'** auf der **'wp-login'** Seite und **'Generall Brute Force'** auf der gesamten Website. Ein Brute Force Angriff wird als **'Brute Force Login'** identifiziert, wenn die Datenbank **10** Einlogversuche der **selben** IP-Adresse in den letzten **10 Minuten** erhalten hat.
Dies geschieht durch folgende SQL Query:

`"SELECT COUNT(*) FROM $table_name WHERE client = %s AND url LIKE %s AND time > now() - interval 10 minute", $ip, $brute_force_login_uri`.

Wiederum ein Brute Force Angriff auf jegliche andere Seiten wird ab **100** Anfragen von der **selben** IP-Adresse innerhlab der letzten **5 Minuten** registriert. Dies geschieht durch folgende SQL Query:

`"SELECT COUNT(*) FROM $table_name WHERE client = %s AND time > now() - interval 5 minute", $ip`    

Zur sicherung gegen SQL Injections wurden `Tokens` in der SQL Abfrage und `$wpdb->prepare` verwendet. 

> **2. 'Access Tool Detection'**

gugug 

> **3. 'Pattern Detection'**
   
## Beispiele von Klassifizierungen

### 1. Normale Anfrage



### 2. Böswillige Anfrage

## Navigation

