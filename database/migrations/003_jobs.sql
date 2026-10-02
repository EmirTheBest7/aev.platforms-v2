-- Careers. Column names follow the legacy `jobs` table so an existing export imports unchanged
-- (scripts/import-jobs.php). No rows are shipped: job content belongs to the owner.
CREATE TABLE IF NOT EXISTS jobs (
    id                   INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    job_url              VARCHAR(32)  NOT NULL,
    job_name             VARCHAR(160) NOT NULL,
    job_company          VARCHAR(120) NOT NULL,
    job_location         VARCHAR(120) NOT NULL,
    job_logo             VARCHAR(120) NULL,
    job_salary           VARCHAR(80)  NULL,
    job_type             VARCHAR(60)  NULL,
    job_lead             VARCHAR(255) NULL,
    job_desc             TEXT         NULL,
    job_responsibilities TEXT         NULL,
    job_skills           TEXT         NULL,
    published            TINYINT(1)   NOT NULL DEFAULT 1,
    created_at           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_jobs_url (job_url)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
