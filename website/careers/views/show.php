<?php
/**
 * One job posting (port of the original page/careers/desc). Every value is escaped; the posting text is plain text.
 *
 * @var callable(mixed): string $e
 * @var array<string, string|null> $job
 * @var list<string> $responsibilities
 * @var list<string> $skills
 * @var list<array<string, string|null>> $related
 * @var string $email
 */
?>
  <div class="content-container">

    <article class="job-listing">

      <header class="job-listing-header">

        <div class="job-listing-header__banner">
          <span>Summary</span>
        </div>

        <div class="job-listing-header__content">

          <div class="job-listing-header__company-logo"> <!-- /_assets/job_icon.png -->
            <img src="<?= $e($job['logo_url']) ?>" alt="Logo Alt" itemprop="image" class="job-logo">
          </div>

          <div class="job-listing-header__job-details">
            <div class="job-listing-header__job-details-top">
              <h1 class="job-listing-header__job-title" itemprop="title"><?= $e($job['job_name']) ?></h1>
              <span class="job-listing-header__company-name" itemprop="hiringOrganization">
                <span class="job-listing-header__company-type"><?= $e($job['job_company']) ?></span>
              </span>
            </div>
            <div class="job-listing-header__job-details-middle">
              <table class="job-listing-header__mata-table">
                <tr itemprop="baseSalary" itemscope itemtype="https://schema.org/MonetaryAmount">
                  <th>Salary</th>
                  <td>
                    <span itemprop="currency">€</span>
                    <span itemprop="value"><?= $e($job['job_salary']) ?></span>
                  </td>
                </tr>
                <!--<tr>
                  <th>Benefits</th>
                  <td itemprop="jobBenefits">1</td>
                  <td itemprop="jobBenefits">2</td>
                </tr>-->
                <tr itemprop="jobLocation" itemscope itemtype="https://schema.org/Place">
                  <th>Location</th>
                  <td itemprop="address"><?= $e($job['job_location']) ?></td>
                </tr>
                <tr>
                  <th>Category</th>
                  <td itemprop="occupationalCategory">Software Development</td>
                </tr>
                <tr>
                  <th>Type</th>
                  <td itemprop="employmentType"><?= $e($job['job_type']) ?></td>
                </tr>
                <tr>
                  <th>Team Lead</th>
                  <td><?= $e($job['job_lead']) ?></td>
                </tr>
              </table>
            </div>
            <div class="job-listing-header__job-details-bottom">
              <a href="mailto:<?= $e($email) ?>" class="btn btn-apply">Apply Now</a>
            </div>
          </div>

        </div>

      </header>

      <section class="job-listing__job-description" itemprop="description">

        <h2 class="job-listing__job-description-title">Job Description</h2>

        <p><?= nl2br($e($job['job_desc'])) ?></p>
        <p><strong>Job Title &amp; Location:</strong></p>
        <p><?= $e($job['job_name']) ?> in <?= $e($job['job_location']) ?>.</p>
        <p><strong>Salary &amp; Benefits:</strong></p>
        <p>
          <div class="acs-desc">
            <p>At our startup, we believe in recognizing and rewarding our team members for their hard work, dedication, and contributions. Our <strong>ACS System</strong> (ΛΞV Credit System) ensures that effort translates directly into tangible rewards. Here's what you can expect:</p>
            <h4>1. ACS Credits:</h4>
            <ul>
              <li>Instead of traditional monetary bonuses, you'll earn <strong>ACS credits</strong> for completing tasks, achieving milestones, or contributing to our company's growth.</li>
              <li>These credits serve as a digital currency within our startup ecosystem.</li>
            </ul>
            <h4>2. Task Completion Rewards:</h4>
            <ul>
              <li>Successfully complete a task, whether it's coding a feature, designing a logo, closing a sale, or organizing team events, and receive a predetermined amount of <strong>ACS credits</strong>.</li>
            </ul>
            <h4>3. Credit Accumulation:</h4>
            <ul>
              <li>Your accumulated <strong>ACS credits</strong> are stored securely in your digital wallet.</li>
              <li>The more tasks you complete, the higher your <strong>ACS balance</strong> grows.</li>
            </ul>
            <h4>4. Conversion to Real Money:</h4>
            <ul>
              <li>The exciting part: <strong>ACS credits</strong> are convertible to real money!</li>
              <li>Transfer your earned credits from your wallet to your bank account or preferred payment method.</li>
            </ul>
            <h4>5. Fairness and Transparency:</h4>
            <ul>
              <li>We value transparency. You'll always know the value of your contributions and the corresponding <strong>ACS credits</strong> you've earned.</li>
            </ul>
            <h4>6. Motivation and Recognition:</h4>
            <ul>
              <li><strong>ACS credits</strong> provide a tangible way to recognize and motivate you.</li>
              <li>Whether it's a small task or a major project, every effort counts.</li>
            </ul>
            <h4>7. Security and Accountability:</h4>
            <ul>
              <li>Our system maintains a secure ledger of all transactions.</li>
              <li>Each credit transfer is recorded, ensuring accountability and preventing misuse.</li>
            </ul>
            <p>Join us and let your hard work pay off! 💡💰</p>
          </div>
          
          <br>
          — 🖖 Hey buddy attitude and open culture<br><br>
          — ⏰ Flexible working hours<br><br>
          — 🍺 Meetups, team building activities, and other community events<br><br>
          — 🛍️ Discounted prices on electronics thanks to our partner<br><br>
        </p>
        
        <p><strong>Type of Role:</strong></p>
        <p>This is a freelance role that includes weekends and late nights.</p>
        <p><strong>Key Tasks and Responsibilities:</strong></p>
        <ul>
          <?php foreach ($responsibilities as $item): ?>
          <li><?= $e($item) ?></li>
          <?php endforeach; ?>
        </ul>
        <p><strong>Skills and Experience Required:</strong></p>
        <ul>
          <?php foreach ($skills as $skill): ?>
          <li><?= $e($skill) ?></li>
          <?php endforeach; ?>
        </ul>
        
        <p><strong>How to Apply:</strong></p>
        <p>Apply by submitting your CV to our email <span class="apply-mail"><?= $e($email) ?></span> in absolute confidence ensuring we have all your contact
          details including mobile telephone number and personal e-mail address.</p>
        <p>Please note that as a member of the IOR (Institute of Recruiters) we never forward CV’s to clients
          without having fully discussed the role with you and gained your permission to do so.</p>
        <p>Should you not have been contacted within 5 days you can assume on this occasion you have been
          unsuccessful.</p>

      </section>

    </article>

    <aside class="job-listing-sidebar">

      <section class="sidebar-widget">
        <h3 class="sidebar-widget__title">Quick Actions</h3>
        <div class="sidebar-widget__quick-actions">
          <!-- Back to prev page link -->
          <a href="/careers" class="sidebar-widget__quick-actions-prev-page">
            <i class="uil uil-step-backward"></i> Back to Jobs - List
            <span></span>
          </a>
        </div>
      </section>

      <section class="sidebar-widget">
        <h3 class="sidebar-widget__title">Related Jobs <small>by <em>Category</em></small></h3>
        <ul class="sidebar-widget__related-jobs">

          <?php foreach ($related as $rel): ?>
          <li class="sidebar-widget__related-job">
            <a href="/careers/<?= $e(rawurlencode((string) $rel['job_url'])) ?>" class="sidebar-widget__related-job--link">
              <h4 class="sidebar-widget__related-job-title"><?= $e($rel['job_name']) ?></h4>
              <span class="sidebar-widget__related-job-company"><?= $e($rel['job_company']) ?></span>
              <span class="sidebar-widget__related-job-location"><?= $e($rel['job_location']) ?></span>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </section>

    </aside>

  </div>
  