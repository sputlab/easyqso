-- ============================================================
--  EasyQSO v2.0.2 — Migration
--  Esegui su un DB già aggiornato alla v2.0.1
-- ============================================================
SET NAMES utf8mb4;

-- ── Colonne aggiuntive su eqso_users ────────────────────────
ALTER TABLE eqso_users
ALTER TABLE eqso_users ADD COLUMN suspended_reason VARCHAR(255) DEFAULT NULL AFTER is_active;
ALTER TABLE eqso_users ADD COLUMN force_pw_change TINYINT(1) NOT NULL DEFAULT 0 AFTER suspended_reason;

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
