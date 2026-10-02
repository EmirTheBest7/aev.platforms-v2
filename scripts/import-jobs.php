<?php

declare(strict_types=1);

/**
 * Imports job postings from a JSON file into the `jobs` table (upsert by job_url, prepared statements).
 *
 *   php scripts/import-jobs.php jobs.json
 *
 * The file is a list of objects with the table's column names:
 *   job_url (a-z A-Z 0-9 _ -, max 32), job_name, job_company, job_location [required],
 *   job_logo, job_salary, job_type, job_lead, job_desc, job_responsibilities, job_skills, published (0|1) [optional].
 * Job content belongs to the owner: nothing is shipped with the repository.
 */

use App\Models\JobRepository;
use App\Support\Env;
use Core\Auth\Database\PdoConnection;

require dirname(__DIR__) . '/vendor/autoload.php';
Env::load(dirname(__DIR__) . '/.env');

$file = $argv[1] ?? '';
if ($file === '' || !is_file($file)) {
    fwrite(STDERR, "Usage: php scripts/import-jobs.php jobs.json\n");
    exit(1);
}
$rows = json_decode((string) file_get_contents($file), true);
if (!is_array($rows)) {
    fwrite(STDERR, "The file is not a JSON list.\n");
    exit(1);
}

$pdo = PdoConnection::forMysql(
    (string) Env::get('DB_HOST', ''),
    (string) Env::get('DB_DATABASE', ''),
    (string) Env::get('DB_USERNAME', ''),
    (string) Env::get('DB_PASSWORD', ''),
    Env::int('DB_PORT', 3306),
)->connect();

$columns = ['job_url', 'job_name', 'job_company', 'job_location', 'job_logo', 'job_salary', 'job_type', 'job_lead', 'job_desc', 'job_responsibilities', 'job_skills', 'published'];
$sql = 'INSERT INTO jobs (' . implode(',', $columns) . ') VALUES (' . implode(',', array_map(static fn(string $c): string => ':' . $c, $columns)) . ') '
    . 'ON DUPLICATE KEY UPDATE ' . implode(',', array_map(static fn(string $c): string => "$c = VALUES($c)", array_slice($columns, 1)));
$statement = $pdo->prepare($sql);

$count = 0;
foreach ($rows as $index => $row) {
    if (!is_array($row) || preg_match(JobRepository::SLUG_PATTERN, (string) ($row['job_url'] ?? '')) !== 1) {
        fwrite(STDERR, "Row {$index}: invalid or missing job_url — skipped.\n");
        continue;
    }
    foreach (['job_name', 'job_company', 'job_location'] as $required) {
        if (trim((string) ($row[$required] ?? '')) === '') {
            fwrite(STDERR, "Row {$index}: {$required} is required — skipped.\n");
            continue 2;
        }
    }
    $values = [];
    foreach ($columns as $column) {
        $values[$column] = $column === 'published' ? (int) (($row['published'] ?? 1) ? 1 : 0) : (isset($row[$column]) ? (string) $row[$column] : null);
    }
    $statement->execute($values);
    ++$count;
}
echo "imported {$count} job(s)\n";
