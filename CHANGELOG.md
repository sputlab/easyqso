# Changelog

Formato basato su [Keep a Changelog](https://keepachangelog.com/it/1.1.0/),
versionamento secondo [Semantic Versioning](https://semver.org/lang/it/).

## [2.2.0] - 2026-11
### Added
- **Traduzione in inglese** di tutta la webapp (pagine, messaggi API, email). Lingua impostabile da *Il mio account* (nuova colonna `eqso_users.lang`, migrazione `db/migration_v220.sql`); selettore IT/EN anche nel login.
- **Guida integrata** (menu ☰ → *Guida*) e **guida illustrata** `src/guide.html` con screenshot (IT/EN).
- Pulsante **Annulla contest**: ferma e azzera il timer del contest (i QSO nel log non vengono toccati).
### Fixed
- Il flag **Admin** nella creazione di un nuovo utente era invisibile (checkbox nascosta dallo stile dei campi).
- I pulsanti **ADIF** e **CSV** sono ora affiancati a destra della barra filtri.
### Changed
- Rimosso il vecchio pannello Account dalla pagina principale (sostituito da `account.php`).
- Il README documenta quale file SQL usare per installazione, reinstallazione e aggiornamento.

## [2.1.2] - 2026-11
### Fixed
- `src/api/.htaccess` e `src/includes/.htaccess` contenevano un prefisso `-e ` errato (presente dalla 2.0.2) che causava HTTP 500 su Apache, quindi nessun login. Ora le direttive sono valide; `includes/` usa `Require all denied` con fallback Apache 2.2.
- `db/schema.sql` non includeva le tabelle/colonne introdotte dalla 2.0.2 (`eqso_rate_limits`, `eqso_account_tokens`, `eqso_audit_log`, `suspended_reason`, `force_pw_change`): le nuove installazioni andavano in errore al login. Ora lo schema è completo.
- L'hash dell'utente admin di default in `schema.sql` non corrispondeva a `Admin1234!` (login impossibile): sostituito con un hash valido.
- Header `Authorization` non inoltrato a PHP su Apache (login ok ma 401 sulle chiamate successive e logout immediato): aggiunto l'inoltro in `.htaccess`.
- La tabella DXCC è ora servita da `/api/dxcc` invece che da `assets/dxcc.json` (alcuni hosting rispondono 403 sui file statici in `assets/`).
### Added
- `db/schema_reset.sql`: reinstallazione completa da zero (cancella e ricrea tutte le tabelle).

## [2.1.1] - 2026-11
### Fixed
- L'API risponde sempre con JSON anche in caso di errore interno (prima un 500 HTML anonimo faceva mostrare solo "Errore." al login); il dettaglio viene scritto nel log PHP.
- Se il DB non è migrato (tabella/colonna mancante) l'API segnala "Database non aggiornato: esegui le migrazioni in db/".

## [2.1.0] - 2026-11
### Added
- Pagina di autogestione account `src/account.php`: modifica nominativo, cambio password, gestione/revoca sessioni, richiesta eliminazione account. Raggiungibile dal menu (“Il mio account”).
- Banda **30m** (e 17m/12m) tra i filtri del log, nel filtro DX Cluster e nell'export Cabrillo.
### Changed
- Versione portata a 2.1.0 (`VERSION`, `APP_VERSION`, intestazioni file, export ADIF/Cabrillo).

## [2.0.2] - 2026-09
### Changed
- Riorganizzazione del repository (`src/`, `db/`, `docs/`, `tests/`, `branding/`).
- Aggiunti licenza MIT, `.gitignore`, `VERSION` e changelog.
- Aggiunta migrazione `db/migration_v202.sql` da v2.0.1.

## [2.0.1] - 2026-09
- Versione precedente (non presente in questo repository).
