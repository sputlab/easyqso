# EasyQSO

Logbook web per radioamatori (PHP + MySQL, SPA integrata): log QSO, import ADIF, export ADIF/CSV/Cabrillo, DX Cluster, mappa DXCC, modalità contest, statistiche, pannello admin e pagina di autogestione account. Interfaccia in **italiano** e **inglese**.

**Versione corrente: 2.2.0** · Licenza [MIT](LICENSE) · [Changelog](CHANGELOG.md) · 📖 [Guida illustrata](src/guide.html)

## Funzionalità
- Inserimento rapido QSO, rilevamento banda dalla frequenza, DXCC automatico dal nominativo, dupe alert, modifica inline
- Filtri per nominativo/banda, ordinamento, export ADIF e CSV (pulsanti in alto a destra della tabella)
- Modalità **Contest** con timer e score live; pulsanti Reset, Nascondi e **Annulla contest**
- Import ADIF, export Cabrillo, mappa DXCC, grafici, backup automatico, notifiche desktop, DX Cluster live
- Account personale (menu ☰ → *Il mio account*): nominativo, password, **lingua IT/EN**, sessioni attive, eliminazione account
- Gestione utenti per admin (creazione con flag Admin, sospensione, reset password, audit log)
- 3 temi grafici (Radio, Dark, Light)

## Documentazione per l'utente
- **Guida integrata**: menu ☰ → *Guida* (riepilogo rapido)
- **Guida illustrata con screenshot**: [`src/guide.html`](src/guide.html) (si apre anche dalla guida integrata; bilingue IT/EN). In produzione è raggiungibile su `https://tuodominio.com/easyqso/guide.html`.

## Prerequisiti
- PHP 8.1+ con PDO MySQL (testato anche con PHP 8.4)
- MySQL 5.7+ / MariaDB 10.3+
- Nginx (vedi [docs/nginx.conf](docs/nginx.conf)) o Apache (i file `.htaccess` inoltrano l'header `Authorization` a PHP)

## Installazione

1. **Carica** il contenuto di `src/` sul server (compresi i file nascosti `.htaccess`). Permessi consigliati: cartelle `755`, file `644`
2. **Database** — scegli il caso:
   | Situazione | Cosa importare |
   |---|---|
   | Nuova installazione | `db/schema.sql` |
   | Reinstallazione da zero (**cancella tutti i dati**) | `db/schema_reset.sql` |
   | Aggiornamento da 2.0.1 | `db/migration_v202.sql`, poi `db/migration_v220.sql` |
   | Aggiornamento da 2.0.2 / 2.1.x | `db/migration_v220.sql` |

   `schema.sql` non modifica un DB esistente (usa `CREATE TABLE IF NOT EXISTS`).
3. **Config**: `cp includes/config.example.php includes/config.php` e compila i dati (genera `SECRET_KEY` con `openssl rand -hex 32`). Il file `config.php` non è nel repository: dopo ogni aggiornamento controlla che `APP_VERSION` sia coerente
4. **Nginx**: aggiungi i blocchi `location` di `docs/nginx.conf` al tuo virtual host
5. **Apri** `https://tuodominio.com/easyqso/`

## Account admin iniziale
`schema.sql` / `schema_reset.sql` creano l'utente `admin@easyqso.local` con password **di esempio pubblica** `Admin1234!`.
**Cambiala subito** dopo il primo accesso (menu ☰ → *Il mio account*) oppure sostituisci l'hash nello schema prima dell'import:
`php -r "echo password_hash('NuovaPassword', PASSWORD_BCRYPT, ['cost'=>12]);"`

## Lingue
La lingua dell'interfaccia si imposta da *Il mio account → Lingua* ed è salvata nel profilo (colonna `eqso_users.lang`). Nella schermata di login si può scegliere con i pulsanti IT/EN; per le pagine senza login (verifica email, reset password) vale la lingua del browser o il parametro `?lang=`. Le email di sistema sono inviate nella lingua dell'utente.
I testi sono in italiano nel codice; le traduzioni inglesi sono in [`src/includes/i18n.php`](src/includes/i18n.php) (dizionario IT → EN): per aggiungere una frase basta una nuova coppia.

## Struttura
```
.
├── src/        applicazione (index.php, account.php, guide.html, api/, includes/, admin/, assets/, ...)
├── db/         schema.sql, schema_reset.sql e migrazioni
├── docs/       nginx.conf
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
