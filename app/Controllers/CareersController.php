<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\HttpException;
use App\Http\Request;
use App\Http\Response;
use App\Models\JobRepository;
use App\Support\Logger;
use App\Support\View;

/**
 * Careers: job list (/careers), one posting (/careers/{slug}) and the team page (/careers/team).
 * Data comes from JobRepository; a database failure is logged and shown as a generic 503, never as SQL.
 *
 * @phpstan-import-type Job from JobRepository
 */
final class CareersController
{
    private const LOGO_DIR = '/assets/images/careers/';
    private const DEFAULT_LOGO = 'job_icon.png';

    /** Display names of the filter tabs of the original page (company value => label). */
    private const COMPANY_LABELS = ['ΛΞV - HR' => 'AEV.HR', '4RLTY' => '3E.RLTY'];

    public function __construct(
        private readonly View $view,
        private readonly JobRepository $jobs,
        private readonly Logger $logger,
        private readonly string $contactEmail,
    ) {}

    public function index(Request $request): Response
    {
        $jobs = array_map(fn(array $job): array => $this->present($job), $this->load(fn() => $this->jobs->published()));

        $companies = [];
        foreach ($jobs as $job) {
            $companies[$job['job_company']] ??= ['key' => 'c' . (count($companies) + 1), 'label' => self::COMPANY_LABELS[$job['job_company']] ?? $job['job_company']];
        }
        foreach ($jobs as &$job) {
            $job['tag'] = $companies[$job['job_company']]['key'];
        }
        unset($job);

        return $this->page('pages/careers/index', ['jobs' => $jobs, 'companies' => array_values($companies)], [
            'title' => 'ΛΞV | Careers',
            'description' => 'Open roles at ΛΞV. Join a team and inspire the work.',
            'path' => '/careers',
            'bodyClass' => 'filter-main page-careers-list',
            'styles' => ['/assets/css/core.css', '/assets/css/careers/uikit.min.css', '/assets/css/careers/list.css'],
            'scripts' => ['/assets/vendor/jquery/jquery-3.1.0.min.js', '/assets/vendor/uikit/uikit.min.js', '/assets/js/careers/list.js'],
        ]);
    }

    /** @param array<string, string> $params */
    public function show(Request $request, array $params): Response
    {
        $slug = $params['slug'] ?? '';
        if (preg_match(JobRepository::SLUG_PATTERN, $slug) !== 1) {
            throw new HttpException(404);
        }
        $job = $this->load(fn() => $this->jobs->findBySlug($slug));
        if ($job === null) {
            throw new HttpException(404);
        }
        $related = $this->load(fn() => $this->jobs->related($slug));
        $job = $this->present($job);

        return $this->page('pages/careers/show', [
            'job' => $job,
            'responsibilities' => $this->sentences($job['job_responsibilities']),
            'skills' => $this->sentences($job['job_skills']),
            'email' => $this->contactEmail,
            'related' => array_map(fn(array $r): array => $this->present($r), $related),
        ], [
            'title' => 'ΛΞV | ' . $job['job_name'],
            'description' => $job['job_name'] . ' — ' . $job['job_company'] . ', ' . $job['job_location'] . '.',
            'path' => '/careers/' . $job['job_url'],
            'bodyClass' => 'page-careers-job',
            'styles' => ['/assets/css/careers/fonts.css', '/assets/css/core.css', '/assets/css/careers/desc.css'],
        ], ['backHref' => '/careers', 'backIcon' => 'uil-step-backward-alt']);
    }

    public function team(Request $request): Response
    {
        return $this->page('pages/careers/team', [], [
            'title' => 'ΛΞV | Our Team',
            'description' => 'Work at ΛΞV. Join a team and inspire the work.',
            'path' => '/careers/team',
            'bodyClass' => 'page-careers-team',
            'styles' => ['/assets/css/careers/fonts.css', '/assets/vendor/fontawesome/brands.css', '/assets/css/careers/team.css'],
        ], null);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $meta
     * @param array<string, string>|null $navbar back-link of the top bar; null = no top bar on this page
     */
    private function page(string $template, array $data, array $meta, ?array $navbar = ['backHref' => '/', 'backIcon' => 'uil-estate']): Response
    {
        return new Response($this->view->render($template, $data + ['navbar' => $navbar], $meta, 'page'));
    }

    /**
     * @template T
     * @param callable(): T $query
     * @return T
     */
    private function load(callable $query): mixed
    {
        try {
            return $query();
        } catch (\PDOException $e) {
            $this->logger->error('careers.database', ['exception' => $e]);
            throw new HttpException(503);
        }
    }

    /**
     * @param Job $job
     * @return Job
     */
    private function present(array $job): array
    {
        $logo = (string) ($job['job_logo'] ?? '');
        $safe = preg_match('/^[A-Za-z0-9._-]{1,120}$/', $logo) === 1 ? $logo : self::DEFAULT_LOGO;

        return $job + ['logo_url' => self::LOGO_DIR . $safe];
    }

    /**
     * The original page turned a paragraph of sentences into a bullet list (split after . ? ! before a letter).
     *
     * @return list<string>
     */
    private function sentences(?string $text): array
    {
        $parts = preg_split('/(?<=[.?!])\s+(?=[a-z])/i', trim((string) $text)) ?: [];

        return array_values(array_filter(array_map('trim', $parts), static fn(string $s): bool => $s !== ''));
    }
}
