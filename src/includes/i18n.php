<?php
// ============================================================
//  EasyQSO — Internazionalizzazione (IT → EN)
//  La lingua sorgente è l'italiano: le chiavi sono i testi italiani.
//  - PHP:   tr('Testo italiano')                  → testo nella lingua corrente
//  - Client: i18n_client_script() inietta dizionario + motore che
//            traduce DOM, attributi e contenuti dinamici.
//  Lingue supportate: it, en
// ============================================================

const I18N_LANGS = ['it', 'en'];

function i18n_valid(?string $l): ?string {
    $l = strtolower(substr((string)$l, 0, 2));
    return in_array($l, I18N_LANGS, true) ? $l : null;
}

// Lingua corrente: ?lang= → cookie → Accept-Language → it
function i18n_lang(): string {
    if ($l = i18n_valid($_GET['lang'] ?? null)) return $l;
    if ($l = i18n_valid($_COOKIE['easyqso_lang'] ?? null)) return $l;
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && ($l = i18n_valid($_SERVER['HTTP_ACCEPT_LANGUAGE']))) return $l;
    return 'it';
}

function tr(string $it, ?string $lang = null): string {
    $lang = i18n_valid($lang) ?? i18n_lang();
    if ($lang === 'it') return $it;
    return i18n_dict()[$it] ?? $it;
}

// Dizionario: testo italiano => inglese
function i18n_dict(): array {
    static $d = null;
    if ($d !== null) return $d;
    $d = [
    // ── Login / registrazione ─────────────────────────────
    'DX CONTEST LOGGER v' => 'DX CONTEST LOGGER v',
    'Accedi' => 'Sign in', 'Registrati' => 'Sign up', 'Password' => 'Password', 'Email' => 'Email',
    'Password dimenticata?' => 'Forgot your password?', 'ACCEDI' => 'SIGN IN',
    'Nominativo' => 'Callsign', 'Conferma password' => 'Confirm password', 'REGISTRATI' => 'SIGN UP',
    'La tua email' => 'Your email', 'INVIA LINK RESET' => 'SEND RESET LINK',
    'tu@email.com' => 'you@email.com', 'Minimo 8 caratteri' => 'At least 8 characters',
    'Ripeti la password' => 'Repeat the password',
    // ── Header / menu ─────────────────────────────────────
    'PUNTI' => 'POINTS', 'Tema' => 'Theme', 'Account' => 'Account', 'Il mio account' => 'My account',
    'Gestione Utenti' => 'User management', 'Strumenti' => 'Tools', 'Statistiche' => 'Statistics',
    'Mappa DXCC' => 'DXCC map', 'Importa ADIF' => 'Import ADIF', 'Tutti gli strumenti' => 'All tools',
    'Info' => 'Info', 'About' => 'About', 'Changelog' => 'Changelog', 'Segnala bug' => 'Report a bug',
    'Esci' => 'Sign out', '📖 Guida' => '📖 Guide', 'Guida' => 'Guide', 'Lingua' => 'Language',
    // ── Contest ───────────────────────────────────────────
    'Elapsed' => 'Elapsed', 'Punti' => 'Points', 'Molt. DXCC' => 'DXCC mult.', 'Score totale' => 'Total score',
    '↺ Reset' => '↺ Reset', '⏹ Annulla contest' => '⏹ Cancel contest', 'Nascondi barra' => 'Hide bar',
    '🏆 CONTEST' => '🏆 CONTEST', 'Contest annullato.' => 'Contest cancelled.',
    'Annullare il contest? Il timer verrà fermato e azzerato. I QSO nel log non vengono toccati.'
        => 'Cancel the contest? The timer will be stopped and reset. QSOs in the log are not touched.',
    // ── Form QSO ──────────────────────────────────────────
    'Data UTC' => 'UTC date', 'Ora UTC' => 'UTC time', 'Freq (MHz)' => 'Freq (MHz)', 'Banda' => 'Band',
    'Modo' => 'Mode', 'RST TX' => 'RST TX', 'RST RX' => 'RST RX', 'Nr. TX' => 'Nr. TX', 'Nr. RX' => 'Nr. RX',
    'DXCC / Pfx' => 'DXCC / Pfx', 'Note' => 'Notes', '+ LOG QSO' => '+ LOG QSO', 'RESET' => 'RESET',
    'Ora auto' => 'Auto time', 'aggiorna UTC' => 'updates UTC', 'Freq fissa' => 'Fixed freq',
    'mantieni ultima' => 'keep last', '⚠ DUPE! Nominativo già presente nel log.' => '⚠ DUPE! Callsign already in the log.',
    'Cerca su QRZ.com' => 'Look up on QRZ.com', 'Op, QTH…' => 'Op, QTH…', '— fuori banda' => '— out of band',
    '↑ banda rilevata' => '↑ band detected',
    // ── Toolbar / tabella ─────────────────────────────────
    'FILTRO:' => 'FILTER:', 'Nominativo…' => 'Callsign…', 'Data' => 'Date', 'Freq' => 'Freq',
    'Nr TX/RX' => 'Nr TX/RX', 'Nessun QSO registrato.' => 'No QSOs logged.',
    'Inserisci il primo contatto sopra.' => 'Enter your first contact above.',
    // ── DX Cluster ────────────────────────────────────────
    '📡 DX CLUSTER' => '📡 DX CLUSTER', 'Tutte le bande' => 'All bands', 'Tutti i modi' => 'All modes',
    'Caricamento…' => 'Loading…', 'Nessuna voce.' => 'No entries.',
    // ── Gestione utenti ───────────────────────────────────
    'Utenti' => 'Users', 'Audit Log' => 'Audit log', 'Tutti' => 'All', 'Attivi' => 'Active',
    'Sospesi' => 'Suspended', 'Non verificati' => 'Unverified', '+ Nuovo utente' => '+ New user',
    'Cerca nominativo o email…' => 'Search callsign or email…', 'Crea nuovo utente' => 'Create new user',
    'Ruolo Admin' => 'Admin role', 'CREA' => 'CREATE', 'Annulla' => 'Cancel', 'Verif.' => 'Verif.',
    'Admin' => 'Admin', 'Stato' => 'Status', 'Registrato' => 'Registered', 'Ultimo acc.' => 'Last login',
    '← Prec' => '← Prev', 'Succ →' => 'Next →', 'Azione' => 'Action', 'Target' => 'Target', 'Data/Ora' => 'Date/Time',
    'Min 8 caratteri' => 'Min 8 characters', 'Attivo' => 'Active', 'Sospeso' => 'Suspended',
    'Nessun utente trovato.' => 'No users found.', 'Modifica utente' => 'Edit user', 'Verificato' => 'Verified',
    '✓ Sì' => '✓ Yes', '✕ No' => '✕ No', '✓ Attivo' => '✓ Active', '✕ Sospendi' => '✕ Suspend',
    'Motivo sospensione' => 'Suspension reason', 'Opzionale' => 'Optional', 'SALVA' => 'SAVE',
    '📧 Reset Password' => '📧 Reset password', '🗑 Elimina' => '🗑 Delete', 'Elimina utente' => 'Delete user',
    'Azione irreversibile. Scegli cosa fare con i QSO:' => 'Irreversible action. Choose what to do with the QSOs:',
    'Anonimizza QSO' => 'Anonymise QSOs', 'Nominativo → "DELETED"' => 'Callsign → "DELETED"',
    'Elimina QSO' => 'Delete QSOs', 'Rimozione definitiva' => 'Permanent removal',
    'CONFERMA ELIMINAZIONE' => 'CONFIRM DELETION', 'Mai' => 'Never', 'Utente creato!' => 'User created!',
    'Utente creato.' => 'User created.', 'Utente aggiornato.' => 'User updated.', 'Utente eliminato.' => 'User deleted.',
    'Email reset inviata!' => 'Reset email sent!', 'Email reset inviata.' => 'Reset email sent.',
    // ── Strumenti ─────────────────────────────────────────
    '🛠️ Strumenti' => '🛠️ Tools', 'Carica un log esistente' => 'Load an existing log', 'Export Cabrillo' => 'Cabrillo export',
    'Formato per contest' => 'Contest format', 'Entità lavorate' => 'Entities worked', 'Grafici e analisi' => 'Charts and analysis',
    'Backup automatico' => 'Automatic backup', 'Salvataggio periodico' => 'Periodic saving', 'Notifiche' => 'Notifications',
    'Alert QSO loggato' => 'Alert when a QSO is logged', '📥 Importa ADIF' => '📥 Import ADIF',
    'Trascina il file .adi/.adif o clicca per selezionarlo' => 'Drag the .adi/.adif file here or click to select it',
    '🏆 Export Cabrillo' => '🏆 Cabrillo export', 'Contest' => 'Contest', 'Categoria' => 'Category',
    'Potenza' => 'Power', 'All Band' => 'All bands', '⬇ SCARICA .CBR' => '⬇ DOWNLOAD .CBR',
    '🗺️ Mappa DXCC lavorate' => '🗺️ DXCC entities worked', 'Lavorata' => 'Worked', 'Non lavorata' => 'Not worked',
    '📊 Analisi QSO' => '📊 QSO analysis', 'Per ora' => 'By hour', 'Per banda' => 'By band', 'Per modo' => 'By mode',
    'Per giorno' => 'By day', '💾 Backup automatico ADIF' => '💾 Automatic ADIF backup', 'Ogni' => 'Every',
    '10 QSO' => '10 QSOs', 'loggati' => 'logged', 'ora' => 'hour', 'Solo manuale' => 'Manual only',
    '⬇ Backup ora' => '⬇ Back up now', 'Attiva backup auto' => 'Enable auto backup',
    '✕ Disattiva backup auto' => '✕ Disable auto backup', '🔔 Notifiche desktop' => '🔔 Desktop notifications',
    'Stato:' => 'Status:', 'Non attive' => 'Off', 'Attive' => 'On', 'Bloccate dal browser' => 'Blocked by the browser',
    'Ricevi una notifica ogni volta che viene loggato un QSO.' => 'Get a notification every time a QSO is logged.',
    'Attiva notifiche' => 'Enable notifications', '🔔 Notifica di test' => '🔔 Test notification',
    'Notifiche non supportate.' => 'Notifications are not supported.', 'QSO loggato — EasyQSO' => 'QSO logged — EasyQSO',
    'Nessun QSO!' => 'No QSOs!', 'Nessun QSO nel log.' => 'No QSOs in the log.', 'File Cabrillo esportato!' => 'Cabrillo file exported!',
    'Errore export.' => 'Export error.', 'Errore import.' => 'Import error.', 'Errore di rete.' => 'Network error.',
    '✅ Backup automatico attivo.' => '✅ Automatic backup enabled.', 'Caricamento Chart.js…' => 'Loading Chart.js…',
    'QSO per ora UTC' => 'QSOs per UTC hour', 'QSO per banda' => 'QSOs per band', 'QSO per modo' => 'QSOs per mode',
    'QSO per giorno' => 'QSOs per day', 'Permesso negato' => 'Permission denied',
    // ── Cluster / DXCC ────────────────────────────────────
    ' — già lavorata' => ' — already worked', ' — non ancora lavorata' => ' — not worked yet',
    '— già lavorata' => '— already worked', '— non ancora lavorata' => '— not worked yet',
    // ── Toast / messaggi form ─────────────────────────────
    'Inserisci il nominativo!' => 'Enter the callsign!', 'Errore salvataggio.' => 'Save error.', 'Errore.' => 'Error.',
    'Eliminare questo QSO?' => 'Delete this QSO?', 'Compila tutti i campi.' => 'Fill in all fields.',
    'Inserisci la tua email.' => 'Enter your email.', 'Operazione completata.' => 'Operation completed.',
    'Registrazione completata!' => 'Registration completed!', 'Le password non coincidono.' => 'Passwords do not match.',
    'Password aggiornata!' => 'Password updated!', 'Email di conferma inviata. Controlla la tua casella.' => 'Confirmation email sent. Check your inbox.',
    // ── About / changelog ─────────────────────────────────
    '— DX Contest Logger' => '— DX Contest Logger', 'Made with ❤️ by' => 'Made with ❤️ by',
    'Logbook web per radioamatori con account personale su DB, ottimizzato per i DX contest.'
        => 'Web logbook for radio amateurs with a personal database account, optimised for DX contests.',
    '📡 Inserimento rapido QSO con Enter' => '📡 Quick QSO entry with Enter', '🎯 DXCC auto-fill dal nominativo' => '🎯 DXCC auto-fill from the callsign',
    '⏰ Orologio UTC live e aggiornamento ora auto' => '⏰ Live UTC clock and automatic time update', '🔁 Memoria ultima frequenza' => '🔁 Last frequency memory',
    '⚠️ Dupe alert' => '⚠️ Dupe alert', '✏️ Modifica inline QSO' => '✏️ Inline QSO editing', '👤 Account personale su MySQL' => '👤 Personal account on MySQL',
    '📊 Statistiche e grafici per ora/banda/modo' => '📊 Statistics and charts by hour/band/mode', '🗺️ Mappa DXCC interattiva' => '🗺️ Interactive DXCC map',
    '📡 DX Cluster live con prefill form' => '📡 Live DX Cluster with form prefill', '🏆 Modalità contest con score live' => '🏆 Contest mode with live score',
    '💾 Backup ADIF automatico' => '💾 Automatic ADIF backup', '📥 Import ADIF' => '📥 ADIF import', '⬇ Export ADIF, CSV, Cabrillo' => '⬇ ADIF, CSV, Cabrillo export',
    '🔔 Notifiche desktop' => '🔔 Desktop notifications', '🔍 Lookup QRZ.com one-click' => '🔍 One-click QRZ.com lookup', '🎨 3 temi grafici' => '🎨 3 visual themes',
    '🌐 Italiano / English' => '🌐 Italian / English', '📖 Guida integrata' => '📖 Built-in guide',
    '❤️ Supporta con PayPal' => '❤️ Support via PayPal', '🔗 Altre WebApp' => '🔗 More WebApps', '📋 Changelog' => '📋 Changelog', '🐛 Segnala bug' => '🐛 Report a bug',
    'Novembre 2026' => 'November 2026', 'Settembre 2026' => 'September 2026', 'Aprile 2026' => 'April 2026',
    'v2.2.0 — corrente' => 'v2.2.0 — current',
    '🌐 Interfaccia in italiano e inglese, lingua impostabile dalla pagina account' => '🌐 Italian and English interface, language selectable from the account page',
    '📖 Guida integrata nel menu e guida illustrata in HTML con screenshot' => '📖 Built-in guide in the menu and illustrated HTML guide with screenshots',
    '⏹ Pulsante “Annulla contest” per fermare e azzerare il timer' => '⏹ “Cancel contest” button to stop and reset the timer',
    '👥 Flag Admin visibile nella creazione utente' => '👥 Admin flag now visible when creating a user',
    '⬇ Pulsanti ADIF e CSV affiancati a destra' => '⬇ ADIF and CSV buttons side by side on the right',
    '🧹 Rimosso il vecchio pannello Account (sostituito dalla pagina dedicata)' => '🧹 Removed the old Account panel (replaced by the dedicated page)',
    'v2.1.2' => 'v2.1.2', '🐛 Corretti i file .htaccess di api/ e includes/ che causavano errore 500 al login' => '🐛 Fixed the .htaccess files in api/ and includes/ that caused a 500 error at login',
    '🔑 Inoltro dell\'header Authorization (login seguito da logout immediato)' => '🔑 Authorization header forwarding (login followed by immediate logout)',
    '🗄️ Schema DB completo e hash admin di default corretto' => '🗄️ Complete DB schema and fixed default admin hash',
    'v2.1.1' => 'v2.1.1', "🛠️ L'API risponde sempre in JSON anche in caso di errore interno" => '🛠️ The API always answers in JSON, even on internal errors',
    '🗄️ Avviso esplicito se il database non è migrato' => '🗄️ Explicit warning if the database is not migrated',
    'v2.1.0' => 'v2.1.0', '👤 Pagina di autogestione account (nominativo, password, sessioni, eliminazione)' => '👤 Account self-service page (callsign, password, sessions, deletion)',
    '📻 Banda 30m (e 17m/12m) nei filtri, cluster e Cabrillo' => '📻 30m band (and 17m/12m) in filters, cluster and Cabrillo',
    'v2.0.2' => 'v2.0.2', '🎯 DXCC auto-fill dal nominativo (260 prefissi)' => '🎯 DXCC auto-fill from the callsign (260 prefixes)',
    '🏆 Export Cabrillo per contest' => '🏆 Cabrillo export for contests', '📊 Grafici QSO per ora/banda/modo/giorno' => '📊 QSO charts by hour/band/mode/day',
    '🗺️ Mappa DXCC interattiva su canvas' => '🗺️ Interactive DXCC map on canvas', '🏆 Modalità Contest con timer e score live' => '🏆 Contest mode with timer and live score',
    'v2.0.1' => 'v2.0.1', '👤 Autenticazione utenti (email + verifica + reset pw)' => '👤 User authentication (email + verification + password reset)',
    '🗄️ Salvataggio QSO su MySQL' => '🗄️ QSOs stored on MySQL', '👥 Gestione Utenti per admin con audit log' => '👥 Admin user management with audit log',
    '🛡️ Rate limiting su login/register/reset' => '🛡️ Rate limiting on login/register/reset', '🔒 Cookie fallback per hosting Nginx' => '🔒 Cookie fallback for Nginx hosting',
    '🎨 3 temi: Radio, Dark, Light' => '🎨 3 themes: Radio, Dark, Light', 'v1.01–1.06' => 'v1.01–1.06',
    '🚀 Prima release — log QSO, DUPE alert, ADIF/CSV, statistiche, filtri, ordinamento' => '🚀 First release — QSO log, DUPE alert, ADIF/CSV, statistics, filters, sorting',
    '📡 Rilevamento banda automatico (ITU R1)' => '📡 Automatic band detection (ITU R1)', '⏰ Toggle ora auto UTC e freq fissa' => '⏰ Auto UTC time and fixed frequency toggles',
    // ── Pagina account ────────────────────────────────────
    'Il mio account — EasyQSO' => 'My account — EasyQSO', '← Torna al logbook' => '← Back to the logbook',
    'Sessione non valida.' => 'Invalid session.', 'Esegui il login' => 'Sign in', 'Profilo' => 'Profile',
    '2–12 caratteri: lettere, numeri e "/"' => '2–12 characters: letters, numbers and "/"', 'QSO registrati' => 'QSOs logged',
    'Membro dal' => 'Member since', 'Ultimo accesso' => 'Last login', 'SALVA NOMINATIVO' => 'SAVE CALLSIGN', 'Sicurezza' => 'Security',
    'Password attuale' => 'Current password', 'Nuova password' => 'New password', 'Min 8, 1 maiusc., 1 numero' => 'Min 8, 1 uppercase, 1 number',
    'Conferma' => 'Confirm', 'Disconnetti le altre sessioni attive' => 'Sign out the other active sessions',
    'AGGIORNA PASSWORD' => 'UPDATE PASSWORD', 'Sessioni attive' => 'Active sessions', 'Nessuna sessione attiva.' => 'No active sessions.',
    'CORRENTE' => 'CURRENT', '✕ Revoca' => '✕ Revoke', 'Elimina account' => 'Delete account',
    "Azione irreversibile. Riceverai un'email con un link di conferma valido 24 ore." => 'Irreversible action. You will receive an email with a confirmation link valid for 24 hours.',
    'Anonimizza i QSO (nominativo → "DELETED")' => 'Anonymise the QSOs (callsign → "DELETED")', 'Elimina tutti i QSO' => 'Delete all QSOs',
    'RICHIEDI ELIMINAZIONE' => 'REQUEST DELETION', 'Italiano' => 'Italiano', 'English' => 'English',
    'Lingua dell\'interfaccia' => 'Interface language', 'Lingua aggiornata.' => 'Language updated.',
    'Nominativo aggiornato.' => 'Callsign updated.', 'Aggiornamento in corso…' => 'Updating…',
    "Sei sicuro? Riceverai un'email di conferma. I QSO saranno: ELIMINATI" => 'Are you sure? You will receive a confirmation email. The QSOs will be: DELETED',
    "Sei sicuro? Riceverai un'email di conferma. I QSO saranno: ANONIMIZZATI" => 'Are you sure? You will receive a confirmation email. The QSOs will be: ANONYMISED',
    'Email inviata! Controlla la casella entro 24 ore.' => 'Email sent! Check your inbox within 24 hours.',
    'Compila tutti i campi.' => 'Fill in all fields.', 'Sessione scaduta.' => 'Session expired.', 'Sessione revocata.' => 'Session revoked.',
    // ── Errori API ────────────────────────────────────────
    'Accesso negato.' => 'Access denied.', 'Account eliminato.' => 'Account deleted.',
    'Account non verificato. Controlla la tua email.' => 'Account not verified. Check your email.',
    'Azione QSO non valida.' => 'Invalid QSO action.', 'Azione non riconosciuta.' => 'Unknown action.', 'Azione non valida.' => 'Invalid action.',
    'Banda obbligatoria.' => 'Band is required.', 'Credenziali non valide.' => 'Invalid credentials.',
    'DX Cluster non raggiungibile.' => 'DX Cluster unreachable.', 'Data obbligatoria.' => 'Date is required.', 'Dati mancanti.' => 'Missing data.',
    'Deve esistere almeno un admin.' => 'At least one admin must exist.', 'Email già registrata.' => 'Email already registered.',
    'Email non valida.' => 'Invalid email.', 'Email verificata!' => 'Email verified!', 'Endpoint non trovato.' => 'Endpoint not found.',
    'Formato nominativo non valido (2-12 caratteri alfanumerici).' => 'Invalid callsign format (2-12 alphanumeric characters).',
    'ID mancante.' => 'Missing ID.', 'Impostazioni salvate.' => 'Settings saved.',
    'La password deve contenere almeno un numero.' => 'The password must contain at least one number.',
    'La password deve contenere almeno una lettera maiuscola.' => 'The password must contain at least one uppercase letter.',
    'Link non valido o scaduto.' => 'Invalid or expired link.', 'Logout effettuato.' => 'Signed out.', 'Metodo non valido.' => 'Invalid method.',
    'Modo obbligatorio.' => 'Mode is required.', 'Nessun QSO da esportare.' => 'No QSOs to export.',
    'Nessun QSO trovato nel file ADIF.' => 'No QSOs found in the ADIF file.', 'Nessun campo da aggiornare.' => 'No fields to update.',
    'Nessun contenuto ADIF ricevuto.' => 'No ADIF content received.', 'Nominativo non valido.' => 'Invalid callsign.',
    'Nominativo obbligatorio.' => 'Callsign is required.', 'Non autenticato.' => 'Not authenticated.',
    'Non puoi eliminare il tuo account da qui.' => "You can't delete your own account from here.",
    'Non puoi rimuovere i tuoi privilegi admin.' => "You can't remove your own admin privileges.", 'Ora obbligatoria.' => 'Time is required.',
    'Password aggiornata.' => 'Password updated.', 'Password attuale errata.' => 'Current password is wrong.',
    'Password minimo 8 caratteri.' => 'Password must be at least 8 characters.', 'QSO eliminato.' => 'QSO deleted.', 'QSO non trovato.' => 'QSO not found.',
    'Registrazione completata! Controlla la tua email.' => 'Registration completed! Check your email.', 'Risorsa non valida.' => 'Invalid resource.',
    'Risposta non valida dal cluster.' => 'Invalid response from the cluster.', 'Sessione non trovata.' => 'Session not found.',
    'Tabella DXCC non disponibile.' => 'DXCC table not available.', 'Token mancante.' => 'Missing token.',
    'Token non valido o già usato.' => 'Invalid or already used token.', 'Token non valido o scaduto.' => 'Invalid or expired token.',
    'Troppi tentativi. Attendi 15 minuti.' => 'Too many attempts. Wait 15 minutes.', "Troppi tentativi. Riprova tra un'ora." => 'Too many attempts. Try again in an hour.',
    'Troppi tentativi.' => 'Too many attempts.', 'Utente non trovato o inattivo.' => 'User not found or inactive.', 'Utente non trovato.' => 'User not found.',
    "Se l'email esiste riceverai le istruzioni." => 'If the email exists you will receive the instructions.',
    'Database non aggiornato: esegui le migrazioni in db/.' => 'Database not up to date: run the migrations in db/.',
    'Errore interno del server.' => 'Internal server error.', 'Modifica' => 'Edit', 'Elimina' => 'Delete', 'Nessuno spot.' => 'No spots.', 'Torna alla Home' => 'Back to home', 'Lingua non valida.' => 'Invalid language.',
    // ── Pagine server (verify / reset / delete) ───────────
    'Verifica Email — EasyQSO' => 'Email verification — EasyQSO', 'Link non valido' => 'Invalid link',
    'Il tuo account è attivo. Puoi accedere a EasyQSO.' => 'Your account is active. You can sign in to EasyQSO.',
    'Il link è scaduto o già utilizzato. Richiedi una nuova verifica.' => 'The link has expired or was already used. Request a new verification.',
    'Vai al Login' => 'Go to sign in', 'Torna al Login' => 'Back to sign in', 'Reset Password — EasyQSO' => 'Reset password — EasyQSO',
    'RESET PASSWORD' => 'RESET PASSWORD', 'Password aggiornata!' => 'Password updated!', 'Puoi accedere con la tua nuova password.' => 'You can now sign in with your new password.',
    'Link non valido. Richiedi un nuovo reset dalla pagina di login.' => 'Invalid link. Request a new reset from the sign-in page.',
    'Nuova password' => 'New password', 'Minimo 8 caratteri' => 'At least 8 characters', 'Conferma password' => 'Confirm password',
    'Token scaduto o non valido.' => 'Expired or invalid token.', 'Eliminazione Account — EasyQSO' => 'Account deletion — EasyQSO',
    'Nessun token fornito.' => 'No token provided.', 'Link scaduto o già usato' => 'Link expired or already used',
    'Il link non è più valido.' => 'The link is no longer valid.', 'Account eliminato' => 'Account deleted',
    'Il tuo account EasyQSO è stato eliminato con successo. I tuoi dati sono stati rimossi.' => 'Your EasyQSO account has been deleted successfully. Your data has been removed.',
    // ── Guida integrata ───────────────────────────────────
    '📖 Guida rapida' => '📖 Quick guide', 'Apri la guida completa con screenshot →' => 'Open the full illustrated guide →',
    ];
    return $d;
}

// Pattern per testi con parti variabili (regex JS → sostituzione con $1, $2…)
function i18n_patterns(): array {
    return [
        ['^Pag\. (\d+)/(\d+) — (\d+) utenti$', 'Page $1/$2 — $3 users'],
        ['^Pag\. (\d+)/(\d+) — (\d+) voci$',   'Page $1/$2 — $3 entries'],
        ['^Aggiornato (.+)$',                  'Updated $1'],
        ['^Banda più attiva: (.+) \((\d+) QSO\)$', 'Most active band: $1 ($2 QSOs)'],
        ['^Modo più usato: (.+) \((\d+) QSO\)$',   'Most used mode: $1 ($2 QSOs)'],
        ['^Ora più attiva: (.+) UTC \((\d+) QSO\)$', 'Most active hour: $1 UTC ($2 QSOs)'],
        ['^(\d+) / (\d+) DXCC lavorate$',      '$1 / $2 DXCC worked'],
        ['^(\d+) QSO negli ultimi (\d+) giorni$', '$1 QSOs in the last $2 days'],
        ['^Nominativo aggiornato: (.+)$',      'Callsign updated: $1'],
        ['^✅ Backup scaricato (.+)\.$',       '✅ Backup downloaded $1.'],
        ['^QSO #(\d+) — (.+) loggato!$',       'QSO #$1 — $2 logged!'],
        ['^QSO #(\d+) aggiornato!$',           'QSO #$1 updated!'],
        ['^Importati (\d+) QSO, (\d+) ignorati\.$', 'Imported $1 QSOs, $2 skipped.'],
        ['^Importati (\d+) QSO\.$',            'Imported $1 QSOs.'],
        ['^scade (.+)$',                       'expires $1'],
        ['^Sicuro di voler eliminare (.+)\?$', 'Are you sure you want to delete $1?'],
    ];
}

// Motore client: traduce DOM, attributi e contenuti dinamici (MutationObserver)
function i18n_client_script(): string {
    $dict = json_encode(i18n_dict(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $pat  = json_encode(i18n_patterns(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $lang = i18n_lang();
    return '<script>window.I18N_DICT=' . $dict . ';window.I18N_PAT=' . $pat . ';window.I18N_LANG=' . json_encode($lang) . ";\n"
         . <<<'JS'
(function(){
  var D=window.I18N_DICT,P=(window.I18N_PAT||[]).map(function(x){return[new RegExp(x[0]),x[1]]});
  var ATTRS=['placeholder','title','aria-label'];
  var orig=new WeakMap();
  var I=window.I18N={lang:'it',
    t:function(s){
      if(I.lang!=='en'||typeof s!=='string')return s;
      var core=s.trim().replace(/\s+/g,' ');if(!core)return s;
      var r=D[core];
      if(r===undefined){for(var i=0;i<P.length;i++){if(P[i][0].test(core)){r=core.replace(P[i][0],P[i][1]);break}}}
      if(r===undefined)return s;
      return s.match(/^\s*/)[0]+r+s.match(/\s*$/)[0];
    },
    locale:function(){return I.lang==='en'?'en-GB':'it-IT'}
  };
  window.T=I.t;
  function skip(n){for(var e=n.nodeType===1?n:n.parentElement;e;e=e.parentElement){
    if(e.hasAttribute&&e.hasAttribute('data-notr'))return true;
    if(e.tagName==='SCRIPT'||e.tagName==='STYLE')return true}return false}
  function doText(n){
    if(skip(n))return;
    var rec=orig.get(n);if(rec&&n.data!==rec.s)rec=null;
    var o=rec?rec.o:n.data,out=I.t(o);
    if(out!==n.data){orig.set(n,{o:o,s:out});n.data=out}
    else if(out!==o)orig.set(n,{o:o,s:out});
  }
  function doAttrs(el){
    if(el.nodeType!==1||skip(el))return;
    var m=el.__i18n||(el.__i18n={});
    ATTRS.forEach(function(a){
      if(!el.hasAttribute(a))return;
      var v=el.getAttribute(a),rec=m[a];if(rec&&v!==rec.s)rec=null;
      var o=rec?rec.o:v,out=I.t(o);
      if(out!==v){m[a]={o:o,s:out};el.setAttribute(a,out)}
      else if(out!==o)m[a]={o:o,s:out};
    });
  }
  function walk(root){
    if(root.nodeType===3){doText(root);return}
    if(root.nodeType!==1&&root.nodeType!==9)return;
    var w=document.createTreeWalker(root,NodeFilter.SHOW_ELEMENT|NodeFilter.SHOW_TEXT,null),n=root.nodeType===1?root:w.nextNode();
    if(root.nodeType===1)doAttrs(root);
    while((n=w.nextNode())){n.nodeType===3?doText(n):doAttrs(n)}
  }
  var _c=window.confirm,_a=window.alert;
  window.confirm=function(m){return _c.call(window,I.t(m))};
  window.alert=function(m){return _a.call(window,I.t(m))};
  var obs=new MutationObserver(function(ms){ms.forEach(function(m){
    if(m.type==='characterData')doText(m.target);
    else if(m.type==='attributes')doAttrs(m.target);
    else m.addedNodes.forEach(walk);
  })});
  I.set=function(l,persist){
    l=(l==='en')?'en':'it';I.lang=l;document.documentElement.lang=l;
    try{localStorage.setItem('easyqso_lang',l)}catch(e){}
    document.cookie='easyqso_lang='+l+'; max-age=31536000; path=/; SameSite=Lax';
    walk(document);
    document.querySelectorAll('[data-lang-sel]').forEach(function(b){b.classList.toggle('active',b.getAttribute('data-lang-sel')===l)});
    document.querySelectorAll('[data-lang-block]').forEach(function(b){b.style.display=b.getAttribute('data-lang-block')===l?'':'none'});
  };
  var start=window.I18N_LANG;
  try{var ls=localStorage.getItem('easyqso_lang');if(ls==='it'||ls==='en')start=ls}catch(e){}
  function init(){
    I.set(start);
    obs.observe(document.documentElement,{childList:true,subtree:true,characterData:true,attributes:true,attributeFilter:ATTRS});
  }
  document.readyState==='loading'?document.addEventListener('DOMContentLoaded',init):init();
})();
</script>
JS;
}
