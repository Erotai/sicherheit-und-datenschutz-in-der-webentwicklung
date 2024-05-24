# THM Security

THM Security ist ein Sicherheitsplugin für WordPress.

## Features
- [Username Enumeration](docs/username-enumeration.md)







# WPScan

In diesem Kapitel geht es darum herauszufinden, wie Angreifer an Informationen über eine WordPress Instanz kommen können. Dazu wird das Tool WPScan verwendet.


#### Anleitung um eine interaktive Ubuntu Instanz zu starten.

```
docker run -it --rm --network host ubuntu
apt update
apt upgrade -y
apt install vim htop curl iputils-ping iproute2 -y
```


#### Starten von WPScan

Dokumentation von WPScan: [Link](https://github.com/wpscanteam/wpscan/wiki/WPScan-User-Documentation#enumeration-modes)

```
docker run -it --rm --network host wpscanteam/wpscan --update --url http://127.0.0.1 --enumerate vp
```

