<?php

declare(strict_types=1);

namespace Website\Careers;

use PDO;

/**
 * Job postings (table `jobs`). The only SQL of the Careers pages: every query is prepared and
 * parameterised, and only published rows are ever returned.
 *
 * @phpstan-type Job array<string, string|null>
 */
final class JobRepository
{
    private const COLUMNS = 'job_url, job_name, job_company, job_location, job_logo, job_salary, job_type, job_lead, job_desc, job_responsibilities, job_skills';

    /** Shape a requested slug must have before it reaches the database (job_url is VARCHAR(32)). */
    public const SLUG_PATTERN = '/^[A-Za-z0-9_-]{1,32}$/D';

    public function __construct(private readonly PDO $pdo) {}

    /** @return list<Job> */
    public function published(): array
    {
        $statement = $this->pdo->query('SELECT ' . self::COLUMNS . ' FROM jobs WHERE published = 1 ORDER BY id');

        return $statement === false ? [] : array_values($statement->fetchAll());
    }

    /** @return Job|null */
    public function findBySlug(string $slug): ?array
    {
        if (preg_match(self::SLUG_PATTERN, $slug) !== 1) {
            return null;
        }
        $statement = $this->pdo->prepare('SELECT ' . self::COLUMNS . ' FROM jobs WHERE job_url = :slug AND published = 1 LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Other published postings for the "Related jobs" box.
     *
     * @return list<Job>
     */
    public function related(string $exceptSlug, int $limit = 3): array
    {
        $statement = $this->pdo->prepare('SELECT ' . self::COLUMNS . ' FROM jobs WHERE published = 1 AND job_url <> :slug ORDER BY id LIMIT :limit');
        $statement->bindValue('slug', $exceptSlug);
        $statement->bindValue('limit', max(1, $limit), PDO::PARAM_INT);
        $statement->execute();

        return array_values($statement->fetchAll());
    }
}
