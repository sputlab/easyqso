<?php
// ============================================================
//  EasyQSO v2.1.1 — Configurazione
//  1. Copia questo file:  cp config.example.php config.php
//  2. Compila tutti i campi qui sotto
//  3. NON committare mai config.php su repository pubblici!
// ============================================================

// ── Database MySQL ───────────────────────────────────────────
define('DB_HOST',    'localhost');       // Host MySQL (di solito localhost)
define('DB_NAME',    'easyqso');        // Nome del database
define('DB_USER',    'easyqso_user');   // Utente MySQL
define('DB_PASS',    'password_sicura');// Password MySQL
define('DB_CHARSET', 'utf8mb4');

// ── Sicurezza ────────────────────────────────────────────────
// Genera una stringa random con: openssl rand -hex 32
define('SECRET_KEY', 'incolla-qui-una-stringa-random-di-almeno-64-caratteri-unica-per-la-tua-installazione');

// Durata sessione in secondi (default: 30 giorni)
define('SESSION_TTL', 60 * 60 * 24 * 30);

// ── URL ──────────────────────────────────────────────────────
// URL base della tua installazione, SENZA slash finale
// Esempi:
//   https://tuodominio.com/easyqso
//   https://tuodominio.com          (se installato nella root)
define('BASE_URL', 'https://www.sputlab.com/webapp/easyqso'); // ← IMPORTANTE: usa sempre l'URL canonico (con www se il sito reindirizza a www)

// ── Email ────────────────────────────────────────────────────
// Indirizzo mittente per le email di sistema
define('MAIL_FROM',      'noreply@tuodominio.com');
define('MAIL_FROM_NAME', 'EasyQSO');

// ── App ──────────────────────────────────────────────────────
define('APP_VERSION', '2.1.1');
define('APP_NAME',    'EasyQSO');
