CREATE TABLE IF NOT EXISTS civicrm_demographics_audit_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    action VARCHAR(128) NOT NULL,
    details TEXT NULL,
    created_at DATETIME NOT NULL
);
