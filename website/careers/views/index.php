<?php
/**
 * Careers list (port of the original page/careers/list).
 *
 * @var callable(mixed): string $e
 * @var list<array<string, string|null>> $jobs
 * @var list<array{key: string, label: string}> $companies
 */
?>
  <nav class="actions-menu container" aria-label="Careers">
    <a aria-disabled="true" title="Not available yet"><i class="uil uil-bookmark" aria-hidden="true"></i> Saved</a>
    <a aria-disabled="true" title="Not available yet"><i class="uil uil-shield-plus" aria-hidden="true"></i> Applied</a>
    <a href="/careers/team"><i class="uil uil-book-reader" aria-hidden="true"></i> Meet our Team</a>
  </nav>

  <section class="search container">

    <ul class="comp-filter flex">
      <li class="uk-active" uk-filter-control="group: tag"><a role="button" tabindex="0">All</a></li>
<?php foreach ($companies as $company): ?>
      <div class="filter-divider"></div>
      <li data-reset-search uk-filter-control="filter: [tag='<?= $e($company['key']) ?>'];"><a role="button" tabindex="0"><?= $e($company['label']) ?></a></li>
<?php endforeach; ?>
    </ul>

    <form class="flex" data-search-form>
        <input uk-filter-control="" class="uk-search-input" type="search" placeholder="Search..." aria-label="Search jobs" data-search-input>
        <button class="search__btn" type="submit">Search</button>
    </form>
  </section>

  <section class="listings container">

    <h3 class="listings__heading">Based on your profile</h3>
    <ul class="listings__grid filter" tabindex="0">
      <p class="skills-no-result uk-hidden">No results</p>
<?php if ($jobs === []): ?>
      <p class="skills-no-result visible">No open positions at the moment.</p>
<?php endif; ?>
<?php foreach ($jobs as $job): ?>
      <li class="jobcard skills-el" tag="<?= $e($job['tag']) ?>" data-name="<?= $e(mb_strtolower((string) $job['job_name'])) ?>">
        <img src="<?= $e($job['logo_url']) ?>" alt="" class="jobcard__logo" />
        <div class="jobcard__heading"><?= $e($job['job_name']) ?></div>
        <div class="jobcard__text"><?= $e($job['job_company']) ?></div>
        <div class="jobcard__text"><?= $e($job['job_location']) ?></div>
        <hr class="jobcard__separator" />
        <a href="/careers/<?= $e(rawurlencode((string) $job['job_url'])) ?>" class="job-jobcard-menu-btn">More Information <i class="uil uil-angle-right-b" aria-hidden="true"></i></a>
      </li>
<?php endforeach; ?>
    </ul>
  </section>
