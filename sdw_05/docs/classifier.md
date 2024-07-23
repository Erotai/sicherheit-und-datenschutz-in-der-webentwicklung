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

Die Brute Force Detection wiederum wird in zwei Kategorien unterteilt: **``'brute-force-login'``** auf der **``'wp-login'``** Seite und **``'brute-force'``** auf der gesamten Website. Ein Brute Force Angriff wird als **``'brute-force-login'``** identifiziert, wenn die Datenbank **10** Einlogversuche der **selben** IP-Adresse in den letzten **10 Minuten** erhalten hat.

Ein Brute Force Angriff wird als **``'brute-force'``** identifiziert, wenn **50** Anfragen von der **selben** IP-Adresse innerhalb der letzten **5 Minuten** registriert werden.
Zur Sicherung gegen SQL Injections wurden `Tokens` in der SQL-Abfrage und `$wpdb->prepare` verwendet.

> **2. 'Access Tool Detection'**

Die Access Tool Detection basiert auf einer Liste von den häufigsten verwendeten User-Agents. Diese sind in einer Variable per RegEx gespeichert.

`$user_agents = "/windows|linux|fedora|ubuntu|macintosh|i-phone|i-pod|i-pad|android|wordpress/i";` 

Es wird geprüft, ob der User Agent der eingehenden Anfrage in dieser Liste vorhanden ist, wenn nicht, wird diese Anfrage als `Access-Tool` kategorisiert.

> **3. 'Pattern Detection'**

Die Pattern Detection verwendet zur Überprüfung ein Array aus Key-Value Pairs, der Key ist hierbei der Name der Klassifizierung z. B. `config-grabber` und die Value besteht aus einem RegEx das ein gewisses Pattern enthält im Falle von dem `config-grabber` = `'/\/wp-config.php/i'`. Es wird über jedes Key-Value Pair des Arrays iteriert und überprüft, ob die `URI` der eingehenden Anfrage oder der `Body` der Anfrage ein Pattern enthält. Wenn ein Pattern übereinstimmen sollte, wird der Name des Keys als Klassifizierung verwendet.

## Navigation
- [Plugin](/README.md)
- [Leaks](../docs/leaks.md)
- [IP-Blocker](../docs/ip-blocker.md)
- [Database](../docs/database)
- [Log](../docs/log.md)