-- ============================================================
--  EasyQSO v2.1.2 — Schema MySQL
--  Compatibile con MySQL 5.7+ / MariaDB 10.3+
--  Tabelle: eqso_users, eqso_sessions, eqso_qso_log
--  Importa con: mysql -u user -p dbname < schema.sql
-- ============================================================
SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS eqso_users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    callsign      VARCHAR(12)   NOT NULL,
    email         VARCHAR(180)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    is_verified   TINYINT(1)    NOT NULL DEFAULT 0,
    verify_token  VARCHAR(64)   DEFAULT NULL,
    reset_token   VARCHAR(64)   DEFAULT NULL,
    reset_expires DATETIME      DEFAULT NULL,
    is_admin      TINYINT(1)    NOT NULL DEFAULT 0,
    is_active     TINYINT(1)    NOT NULL DEFAULT 1,
    suspended_reason VARCHAR(255) DEFAULT NULL,
    force_pw_change  TINYINT(1)   NOT NULL DEFAULT 0,
    theme         VARCHAR(20)   NOT NULL DEFAULT 'radio',
    opt_autotime  TINYINT(1)    NOT NULL DEFAULT 1,
    opt_keepfreq  TINYINT(1)    NOT NULL DEFAULT 0,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login    DATETIME      DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS eqso_sessions (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    token      VARCHAR(64)  NOT NULL UNIQUE,
    expires_at DATETIME     NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES eqso_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS eqso_qso_log (
    id           INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED   NOT NULL,
    qso_date     DATE           NOT NULL,
    qso_time     TIME           NOT NULL,
    callsign     VARCHAR(12)    NOT NULL,
    freq         DECIMAL(10,3)  DEFAULT NULL,
    band         VARCHAR(6)     NOT NULL,
    mode         VARCHAR(10)    NOT NULL,
    rst_sent     VARCHAR(5)     NOT NULL DEFAULT '59',
    rst_rcvd     VARCHAR(5)     NOT NULL DEFAULT '59',
    serial_sent  VARCHAR(10)    DEFAULT NULL,
    serial_rcvd  VARCHAR(10)    DEFAULT NULL,
    dxcc         VARCHAR(15)    DEFAULT NULL,
    note         VARCHAR(255)   DEFAULT NULL,
    created_at   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES eqso_users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, qso_date),
    INDEX idx_user_call (user_id, callsign),
    INDEX idx_user_band (user_id, band)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Token monouso per azioni via email ──────────────────────
CREATE TABLE IF NOT EXISTS eqso_account_tokens (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    token      VARCHAR(64)  NOT NULL UNIQUE,
    action     VARCHAR(50)  NOT NULL,   -- delete_account | reset_password
    payload    JSON         DEFAULT NULL,
    expires_at DATETIME     NOT NULL,
    used_at    DATETIME     DEFAULT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES eqso_users(id) ON DELETE CASCADE,
    INDEX idx_action_token (action, token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Audit log admin ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS eqso_audit_log (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id       INT UNSIGNED NOT NULL,
    admin_callsign VARCHAR(12)  NOT NULL,
    target_user_id INT UNSIGNED DEFAULT NULL,
    action         VARCHAR(80)  NOT NULL,
    detail         JSON         DEFAULT NULL,
    ip             VARCHAR(45)  DEFAULT NULL,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin  (admin_id),
    INDEX idx_target (target_user_id),
    INDEX idx_ts     (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Rate limiting ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS eqso_rate_limits (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier   VARCHAR(120) NOT NULL,
    action       VARCHAR(50)  NOT NULL,
    attempts     SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    window_start DATETIME NOT NULL,
    INDEX idx_id_action (identifier, action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  UTENTE ADMIN DI DEFAULT
--  Email:    admin@easyqso.local
--  Password: Admin1234!
--  ⚠️  CAMBIA SUBITO LA PASSWORD dopo il primo accesso!
--  Per generare un nuovo hash usa:
--  php -r "echo password_hash('NuovaPassword', PASSWORD_BCRYPT, ['cost'=>12]);"
-- ============================================================
INSERT IGNORE INTO eqso_users
    (callsign, email, password_hash, is_verified, is_admin)
VALUES (
    'ADMIN',
    'admin@easyqso.local',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
    1, 1
);
