-- Authentication (core/auth). MySQL / MariaDB, utf8mb4.
CREATE TABLE IF NOT EXISTS users (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(254)    NOT NULL,
    username      VARCHAR(32)     NOT NULL,
    password_hash VARCHAR(255)    NOT NULL,
    display_name  VARCHAR(80)     NULL,
    referral_code VARCHAR(40)     NULL,
    created_at    DATETIME        NOT NULL,
    last_login_at DATETIME        NULL,
    locked_until  DATETIME        NULL,
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email        VARCHAR(254)    NOT NULL,
    ip_address   VARCHAR(45)     NOT NULL,
    user_agent   VARCHAR(255)    NULL,
    successful   TINYINT(1)      NOT NULL,
    attempted_at DATETIME        NOT NULL,
    KEY idx_login_attempts_email_time (email, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS auth_audit_log (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id    BIGINT UNSIGNED NULL,
    event_type VARCHAR(64)     NOT NULL,
    ip_address VARCHAR(45)     NOT NULL,
    user_agent VARCHAR(255)    NULL,
    metadata   TEXT            NULL,
    created_at DATETIME        NOT NULL,
    KEY idx_audit_user (user_id),
    KEY idx_audit_event_time (event_type, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
