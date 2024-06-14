#### Anleitung um eine interaktive Ubuntu Instanz zu starten.

Einen neuen Ubuntu Container starten. Dabei wird das aktuelle Verzeichnis in den Container gemountet. Der Container wird im interaktiven Modus gestartet.
```
docker run -it --name ubuntu --network host -v $(pwd):/host_dir -w /host_dir ubuntu

apt update
apt upgrade -y
apt install php8.3-cli php8.3-curl
exit
```

Später erneut mit dem Container verbinden.
```
docker start -i ubuntu
```

## PHP Script starten

``` 
php username-enumeration.php

php username-enumeration.php > username-enumeration.json
```