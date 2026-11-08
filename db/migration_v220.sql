-- ============================================================
--  EasyQSO v2.2.0 — Migration
--  Esegui su un DB già aggiornato alla v2.0.2 / v2.1.x
--  Aggiunge la lingua dell'interfaccia (it | en) agli utenti.
--  (Compatibile MySQL e MariaDB. Eseguire UNA sola volta:
--   se la colonna esiste già dà errore "Duplicate column name".)
-- ============================================================
ALTER TABLE eqso_users ADD COLUMN lang VARCHAR(5) NOT NULL DEFAULT 'it' AFTER theme;
