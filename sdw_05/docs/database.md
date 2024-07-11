# Classifier Module

## Beschreibung

Das Database Module dient zur Verwaltung der Datenbankoperationen, die das Erstellen sowie Löschen von Datenbanktabellen und Datenbankeinträgen ermöglichen.
## Technische Beschreibung

Das Module ist klassenorientiert aufgebaut und benutzt Methoden zur Installation und Deinstallation der Datenbank. Außerdem stellt das Modul Methoden bereit, um Einträge einer Tabelle hinzuzufügen oder auszulesen. Des Weiteren werden in diesem Modul Cron-Jobs zur regelmäßigen Bereinigung bereitgestellt. Anfragen, die als `'normal'` kategorisiert wurden, werden alle **7 Tage** aus der Datenbank entfernt und Anfragen, die als **nicht** `'normal'` kategorisiert wurden, werden nach **30 Tagen** aus der Datenbank entfernt. Zudem wurde jede SQl-Queries durch den Einsatz von **`Tokens`** und `$wpdb->prepare` gegen SQL-Injections geschützt.

## Verwendete personenbezogene Daten

- `'$_SERVER['REMOTE_ADDR']'` : IP-Adresse der eingehenden Anfrage.
- `'$_SERVER['REMOTE_URI']'` : URL der eingehenden Anfrage.
- `'$_SERVER['REQUEST_METHOD']'` : Methode der eingehenden Anfrage.
- `'$_SERVER['HTTP_USER_AGENT']'` : User-Agent der eingehenden Anfrage.

## Verwendete Hooks/Filter

- `register_activation_hook(MAIN_FILE, ['THM\Security\Database', 'init']);` registriert die Methode `'init'` mit dem Hook `'register_activation_hook'`. Der Hook wurde gewählt, um die Datenbank mit Cron-Job bei Aktivierung des Plugins zu inizialisieren.
- `register_deactivation_hook(MAIN_FILE, ['THM\Security\Database', 'uninstall_db']);` registriert die Methode `'uninstall_db'` mit dem Hook `'register_deactivation_hook'`. Der Hook wurde gewählt, um die Datenbank bei Deaktivierung des Plugins zu deinstallieren.
- `register_uninstall_hook(MAIN_FILE, ['THM\Security\Database', 'uninstall_db']);` registriert die Methode `'uninstall_db'` mit dem Hook `'register_uninstall_hook'`. Der Hook wurde gewählt, um die Datenbank bei Deinstallierung des Plugins ui deinstallieren.
- `add_action('database_check_cron_job', ['THM\Security\Database', 'check_database_reset']);` registriert die Methode `'check_database_reset'` für den Cron-Job  `'database_check_cron_job'`.

## Verwendete Funktionen

### 1. `init() {...}`

Diese Funktion dient zur Initialisierung des Moduls und verwenden die klasseneigene Funktion `'self::install_db();'`´um die vom Plugin verwendete Tabelle in der WordPress-Datenbank zu erstellen. Danach wird mit der Funktion `'wp_next_scheduled('database_check_cron_job')'` überprüft, ob noch kein Cron-Job mit dem Namen `'database_check_cron_job'` existiert. Falls keiner existieren sollte, wird mit der Funktion `'wp_schedule_event(time(), 'daily', 'database_check_cron_job')'` ein Cron-Job geplant, der jeden Tag zur Zeit der ersten Aktivierung dieses überprüft, ob die Datenbank bereinigt werden muss.

### 2. `check_database_reset() {...}`

Diese Funktion dient zur Überprüfung, ob die von dem Modul erstellte Tabelle `'request_manager_access_log'` bereinigt werden muss oder nicht. Die Bereinigung kann in zwei Kategorien unterteilt werden:

>  **1. 'Löschen von Einträgen mit der Klassifizierung `!= 'normal'`'**

Dieser Punkt der Bereinigung überprüft mit einer Datenbankabfrage, ob ein Eintrag vorhanden ist, der älter als **30 Tage** ist und von der `'request_class != 'normal''` ist. Dazu die folgende SQL-Query:

- `"SELECT time FROM %i WHERE NOT request_class = %s AND time + INTERVAL 30 DAY < NOW() LIMIT 1", $db, $request_class`

Falls ein Eintrag vorhanden sein sollte, wird die klasseneigene Funktion `'self::delete_malicious_requests();'` aufgerufen. Falls kein Eintrag vorhanden ist, passiert nichts.

>  **2. 'Löschen von Einträgen mit der Klassifizierung `'normal'`'**

Dieser Punkt der Bereinigung überpüft mit einer Datenbankabfrage, ob ein Eintrag vorhanden ist, der älter als **7 Tage** ist und von der `'request_class === 'normal''` ist. Dazu die folgende SQL-Query:

- `"SELECT time FROM %i WHERE request_class = %s AND time + INTERVAL 7 DAY < NOW() LIMIT 1", $db, $request_class`

Falls ein Eintrag vorhanden sein sollte, wird die klasseneigene Funktion `'self::delete_normal_requests();'` aufgerufen. Falls kein Eintrag vorhanden ist, passiert nichts.

### 3. `delete_normal_requests() {...}`

Diese Funktion dient zur Löschung von allen als `'normal'` klassifizierten Anfragen, die in der Tabelle `'request_manager_access_log'` gespeichert wurden. Dies erfolgt durch folgende SQL-Query:

- `"DELETE FROM %i WHERE request_class = %s",$db, $request_class `

### 4. ` delete_malicious_requests() {...}`

Diese Funktion dient zur Löschung von allen als `!= 'normal'` klassifizierten Anfragen, die in der Tabelle `'request_manager_access_log'` gespeichert wurden. Dies erfolgt durch folgende SQL-Query:

- `"DELETE FROM %i WHERE NOT request_class = %s",$db, $request_class`

### 5. `uninstall_db() {...}`

Diese Funktion dient Löschung der von dem Modul erstellten Tabelle `'request_manager_access_log'` aus der WordPress-Datenbank. Dies erfolgt durch folgende SQL-Query:

- `"DROP TABLE IF EXISTS %i", $db`

Außerdem wird der geplante Cron-Job entfernt durch die Funktion `'wp_clear_scheduled_hook('database_check_cron_job')'`.

### 6. `install_db() {...}`

Diese Funktion dient zur Erstellung der Tabelle `'request_manager_access_log'` in der von WordPress verwendeten Datenbank, dazu wird die wpdb Prefix verwendet `'$wpdb->prefix . self::$table_name;'`. Der verwendete Zeichensatz und die verwendete Sortiereihenfolge werden aus den Standardeinstellungen der WordPress-Datenbank entnommen `'$wpdb->get_charset_collate();'`. Zur Erstellung der Datenbank wird `'dbDelta($table)'` verwendet, um sicherzustellen, dass die erstellte Tabelle zu 100 % mit der WordPress Datenbank kompatibel ist. Die Struktur der Tabelle sieht wie folgt aus:

````
"CREATE TABLE $db (
			time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            client VARCHAR(32) NOT NULL,
            url VARCHAR(128) NOT NULL,
            method VARCHAR(32) NOT NULL,
            agent VARCHAR(128) NOT NULL,
            request_class VARCHAR(128) NOT NULL,
            is_blocked BOOLEAN NOT NULL,
            blocked_at TIMESTAMP NULL DEFAULT NULL
		) $charset_collate;";
````

### 7. `get_access_log() {...}`

Diese Funktion dient zum Auslesen von Tabelleneinträgen. Dazu wird eine Datenbankabfrage verwendet, die sicherstellt, dass nicht mehr als 50 Einträge gleichzeitig ausgelesen werden, um die Last bei Verwendung der Funktion zu verringern. Es wurde SQL-Query verwendet:

- `"SELECT * FROM %i ORDER BY time ASC LIMIT 50"), $table_name`

Nach erfolgreicher Datenbankabfrage wird das Resultat der SQL-Abfrage als `'return'` Wert ausgegeben.

### 8. `append_access_log($client, $url, $method, $agent, $request_class, $is_blocked, $blocked_at) {...}`

Diese Funktion dient zum Hinzufügen von neuen Datensätzen in die Tabelle `'request_manager_access_log'`. Die Funktion hat folgende Parameter, die gegeben sein müssen, um die Funktion verwenden zu können:

````
**** Parameter ****
$client,
$url,
$method,
$agent,
$request_class,
$is_blocked,
$blocked_at
````

Weiterhin wird für das Hinzufügen der Daten `'$wpdb->insert(...)` mit folgenden Parametern verwendet:

````
**** Parameter ****
$table_name,
['client' => $client,
'url' => $url,
'method' => $method,
'agent' => $agent,
'request_class' => $request_class,
'is_blocked' => $is_blocked,
'blocked_at' => $blocked_at
])
````

## Anwendung verwendeter Funktionen

### 1. `get_access_log() {...}`

Beispiel:
````
**** SPEICHERN DES ERGEBNISSES IN EINER VARIABLE ****
$logs = get_access_log()

**** AUSGABE DER VAIRABLE ****
echo $logs

> TABELLE HINZUFÜGEN NICHT VERGESSEN

````

### 1. `append_access_log($client, $url, $method, $agent, $request_class, $is_blocked, $blocked_at) {...}`

Beispiel:
````
**** PARAMETER DER FUNKTION MIT VARIABLEN FÜLLEN ****
append_access_log($client, $url, $method, $agent, $request_class, $is_blocked, $blocked_at)
````

## Navigation
- [Plugin](../README.md)
- [Leaks](../docs/leaks.md)
- [Classifier](../docs/classifier.md)
- [IP-Blocker](../docs/ip-blocker.md)
- [Database](../docs/database)
- [Log](../docs/log.md)
