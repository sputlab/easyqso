# EasyQSO

Logbook web per radioamatori (PHP + MySQL, SPA integrata): log QSO, import ADIF, export Cabrillo, DX Cluster, DXCC, pannello admin, pagina di autogestione account (`account.php`: nominativo, password, sessioni, eliminazione).

**Versione corrente: 2.1.0** · Licenza [MIT](LICENSE) · [Changelog](CHANGELOG.md)

## Prerequisiti
- PHP 8.1+ con PDO MySQL
- MySQL 5.7+ / MariaDB 10.3+
- Nginx (vedi [docs/nginx.conf](docs/nginx.conf)) o Apache

## Installazione

1. **Carica** il contenuto di `src/` sul server
2. **DB**: crea il database e importa `db/schema.sql`; se aggiorni da v2.0.1 importa `db/migration_v202.sql` (la v2.1.0 non richiede nuove migrazioni)
3. **Config**: `cp includes/config.example.php includes/config.php` e compila i dati (generare `SECRET_KEY` con `openssl rand -hex 32`)
4. **Nginx**: aggiungi i blocchi `location` di `docs/nginx.conf` al tuo virtual host
5. **Apri** `https://tuodominio.com/easyqso/`

## Account admin iniziale
`schema.sql` crea l'utente `admin@easyqso.local` con una password **di esempio pubblica**.
**Cambiala subito** dopo il primo accesso (pannello Account) oppure sostituisci l'hash in `schema.sql` prima dell'import:
`php -r "echo password_hash('NuovaPassword', PASSWORD_BCRYPT, ['cost'=>12]);"`

## Struttura
```
.
├── src/        applicazione (index.php, api/, includes/, admin/, assets/, ...)
├── db/         schema.sql e migrazioni
├── docs/       nginx.conf e documentazione
├── tests/      suite PHPUnit 10+
├── branding/   icone
├── VERSION  CHANGELOG.md  LICENSE
```

## Test
```
cd tests && composer install && vendor/bin/phpunit EasyQSOTest.php
```
(richiede `src/includes/config.php` puntato a un DB di test)

## Versionamento
[Semantic Versioning](https://semver.org/lang/it/): `MAJOR.MINOR.PATCH`. Le release sono taggate `vX.Y.Z`.

Made with ❤️ by [sputlab.com](https://sputlab.com)
