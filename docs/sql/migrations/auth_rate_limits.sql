CREATE TABLE IF NOT EXISTS auth_rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(32) NOT NULL,
    identifier_hash CHAR(64) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    window_started_at DATETIME NOT NULL,
    blocked_until DATETIME NULL,
    UNIQUE KEY uq_auth_rate_limit (action, identifier_hash, ip_hash),
    INDEX idx_auth_rate_limits_blocked (blocked_until)
);
