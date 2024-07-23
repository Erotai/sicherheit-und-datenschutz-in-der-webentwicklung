# IP-Blocker Module

## Beschreibung

Das IP-Blocker Module dient zur Blockierung von eingehenden Anfragen, die durch das [**Classifier-Module**](../docs/classifier.md) als verdächtig klassifiziert wurden.

## Technische Beschreibung

Das Modul ist klassenorientiert aufgebaut und verwendet Methoden zur Blockierung und Entblockierung von Anfragen. Dabei wird die Funktion `'classify_request()'` aus dem [**Classifier-Module**](../docs/classifier.md) verwendet, um eingehenden Anfragen einzustufen und anhand der Einstufung den Blockstatus zu überprüfen. Zudem wurde jede SQl-Querys durch den Einsatz von **`Tokens`** und `$wpdb->prepare` gegen SQL-Injections geschützt.

## Verwendete personenbezogene Daten

- `'$_SERVER['REMOTE_ADDR']'` : IP-Adresse der eingehenden Anfrage.

## Verwendete Hooks/Filter

- `add_filter('init', ['\THM\Security\IPBlocker', 'init'], 5);` registriert die Methode `'init'` mit dem gleichnamigen Hook `'init'` und Priorität **5**. Der Hook wurde gewählt, um so früh wie möglich eingehende Anfragen blockieren zu können und bei bereits blockierten Anfragen die Verbindung zu trennen. Dieser Hook wird nämlich bereits vor Laden der Drittanbieter Plug-ins gefeuert.

## Verwendete Funktionen

### 1. `init(): {...}`

Diese Funktion dient zur Initialisierung des Moduls und überprüft anhand der Prüfvariable `'$is_blocked'`, die das Ergebnis der Funktion `'check_ip_block()'` als Wert enthält, ob eine IP-Adresse bereits geblockt ist oder nicht. Wenn eine IP-Adresse bereits geblockt ist, wird die Verbindung zu dieser per `'die()'` beendet. Zusätzlich wird geprüft, ob es für diese IP-Adresse bereits ein **Log** in der Datenbank zur aktuellen Blockierung gibt oder nicht. Dies geschieht mithilfe der Funktion `'log_exists()'`. Das Resultat wird in der Prüfvariable `'$log_exists'` gespeichert. Wenn kein **Log** existiert, wird ein neuer **Log** erstellt mit der Funktion `'Log::log_access();'` aus dem [Log-Module](../docs/log.md). Ansonsten geschieht nichts.

### 2. `check_ip_block(): bool {...}`

Diese Funktion ist die Hauptfunktion des Modules und überprüft, ob eine IP-Adresse geblockt ist oder nicht. Dafür wird am Anfang der Funktion mithilfe des [**Classifier-Module**](../docs/classifier.md) das Ergebnis der Klassifizierung der Anfrage in einer Prüfvariable gespeichert: `'$request_class = Classifier::classify_request();'`. Diese Variable wird zur Festlegung des Blockstatuses verwendet. Der Blockstatus wird von der Funktion als `'boolean'`-`'return'` Wert ausgegeben. Zu Begin wird überprüft, ob die IP-Adresse der aktuellen Anfrage bereits als blockiert in der Datenbank hinterlegt ist. Dies wird per Datenbankabfrage mit folgender SQL-Query geregelt:

`'SELECT is_blocked, blocked_at FROM %i WHERE client = %s AND is_blocked = 1", $table_name, $ip'`

Falls das Ergebnis der SQL-Query das column `'is_blocked'` mit dem Wert `'true'` enthalten sollte, erfolgt die Entblockierungsprüfung, diese lässt sich in die folgenden zwei Sektionen unterteilen:

>  **1. 'Entblockierung von böswilligen IP-Adressen'**

Diese Prüfung beginnt mit einer Datenbankabfrage, die das column `'blocked_at'` ausgibt, wenn seit der Blockierung der IP-Adresse mehr als 24 Stunden vergangen sind und die `'request_class'` nicht mit der Prüfvariable `'$brute_force_class = 'brute-force-login';'` übereinstimmt. Diese Abfrage wurde so konzipiert, um die Unterscheidung zwischen mehreren Arten von **Brute-Force-Attacken** zu unterscheiden. Folgend die besprochene SQL-Query:

`"SELECT blocked_at FROM %i WHERE blocked_at + INTERVAL 24 HOUR < NOW() AND NOT request_class = %s LIMIT 1", $table_name, $brute_force_class'`

Falls das Ergebnis der SQL-Query das column `'blocked_at'` enthalten sollte, wird eine Entblockierung der IP-Adresse vorgenommen. Dies geschieht ebenfalls durch eine SQL-Query:

`'"UPDATE %i SET is_blocked = %d, blocked_at = %s WHERE client = %s AND is_blocked = 1", $table_name, $set_new_state, $set_new_date, $ip'`

Diese SQL-Query updated die Datenbanktabelle mit folgenden Werten:

````
$set_new_state = 0;
$set_new_date = '0000-00-00 00:00:00';

is_blocked = %d, $set_new_state
blocked_at = %s, $set_new_date
````

Nach Abschluss der Datenbankabfrage wird das Ergebnis, das die IP-Adresse nicht mehr blockiert ist, noch als Rückgabewert `'return false;'` ausgegeben.

>  **2. 'Entblockierung von Brute-Force-Login IP-Adressen'**

Diese Prüfung beginnt ebenfalls mit einer Datenbankabfrage, die das column `'blocked_at'` ausgibt. Doch anders als bei der ersten Prüfung wird in dieser SQL-Query nach einem Datensatz gesucht, wo die Zeit seit der Blockierung der IP-Adresse mehr als 1 Stunde beträgt und die `'request_class'` mit der Prüfvariable `'$brute_force_class = 'brute-force-login';'` übereinstimmt. Dies dient zur selektiven Ausgabe von Datensätzen, die als `'brute-force-login'` eingestuft wurden.  Diese Funktion unterscheidet zwischen `'brute-force-login'` und `'brute-force'`, um IP-Adressen nach unterschiedlichen Zeiten Entblockierung zu können. Folgend die besprochene SQL-Query:

`"SELECT blocked_at FROM %i WHERE blocked_at + INTERVAL 1 HOUR < NOW() AND request_class = %s LIMIT 1", $table_name, $brute_force_class'`

Falls das Ergebnis der SQL-Query das column `'blocked_at'` enthalten sollte, wird eine Entblockierung der IP-Adresse vorgenommen. Dies geschieht ebenfalls durch eine SQL-Query:

`'"UPDATE %i SET is_blocked = %d, blocked_at = %s WHERE client = %s AND is_blocked = 1", $table_name, $set_new_state, $set_new_date, $ip'`

Diese SQL-Query updated die Datenbanktabelle mit folgenden Werten:

````
$set_new_state = 0;
$set_new_date = '0000-00-00 00:00:00';

is_blocked = %d, $set_new_state
blocked_at = %s, $set_new_date
````

Nach Abschluss der Datenbankabfrage wird das Ergebnis, das die IP-Adresse nicht mehr blockiert ist, noch als Rückgabewert `'return false;'` ausgegeben.

>  **2.5 'Fall: Zeitprüfung negativ'**

Falls keine der letzten zwei Prüfungen der Zeit positiv war, gilt die IP-Adresse noch als blockiert und es wird Rückgabewert `'return true;'` ausgegeben.

>  **1. 'Blockierung von böswilligen IP-Adressen'**

Nach den beiden Entblockierungsprüfungen erfolgt die Blockierung der IP-Adressen. Dieses Verfahren ist sehr simpel und überprüft jeglich, ob die am Anfang der Funktion festgelegte `'$request_class'` variable von der Klassifizierung nicht normal ist `$request_class !== 'normal'`. Wenn dies der Fall ist, gibt die Funktion als Rückgabewert `'return true;'` aus.

>  **1.5 'Fall: Jeglicher Test negativ'**

Falls jeder Test negativ war, gibt die Funktion den Rückgabewert `return false;` aus.

### 3. `check_block_time(): string {...}`

Diese Funktion hat keine Parameter und gibt als Rückgabewert einen String aus. Sie dient dazu eine Blockierungszeit zu erstellen, wenn eine IP-Adresse blockiert wurde. Dazu nutzt die Funktion `'self::check_ip_block()'` und überprüft, ob die Rückgabe `'true'` oder `'´false'` ist. Bei dem Rückgabewert `'true'` wird mithilfe von `'new \DateTime())->Format('Y-m-d H:i:s')'` die aktuelle Zeit als Rückgabewert der Funktion ausgegeben. Falls die IP-Adresse nicht blockiert ist, wird als Rückgabewert `'NULL'` ausgegeben.

### 4. `log_exists(): bool {...}`

Diese Funktion hat keine Parameter und gibt als Rückgabewert einen Wahrheitswert aus. Sie wird benutzt, um zu überprüfen, ob bereits ein Log von einer blockierten IP-Adresse in der Datenbank existiert oder nicht. Für die Überprüfung wird eine SQL-Query genutzt, die den letzten blockierten Logeintrag von der IP-Adresse der aktuellen Anfrage ausgibt. Die SQL-Query sieht wie folgt aus:

`'"SELECT is_blocked FROM %i WHERE client = %s AND is_blocked = 1 ORDER BY time DESC LIMIT 1", $table_name, $ip'`

Falls das Ergebnis der SQL-Query das column `'is_blocked'` mit dem Wert `'true'` enthält, gibt die Funktion den Rückgabewert `'return true'` aus. Falls der Wert nicht `'true'` war, gibt die Funktion den Rückgabewert `'return false'` aus.

## Navigation
- [Plugin](/README.md)
- [Leaks](../docs/leaks.md)
- [Classifier](../docs/classifier.md)
- [IP-Blocker](../docs/ip-blocker.md)
- [Database](../docs/database)
- [Log](../docs/log.md)