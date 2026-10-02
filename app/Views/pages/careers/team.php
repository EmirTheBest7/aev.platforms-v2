<?php
/**
 * Team page (port of the original page/careers/team). Names, roles and photos are the original static content.
 * Social links of the original were placeholders (href="#"); the icons are kept, without a dead link.
 *
 * @var callable(mixed): string $e
 */
$team = [
    ['/assets/images/avatar.png', 'Emir Aliev', 'uil uil-telegram-alt', 'CEO, COO, DEV'],
    ['/assets/images/careers/team/IMG_3264.JPG', 'Ksenia Kabanova', 'fa-brands fa-x-twitter', 'SMM, UX/UI, Marketing'],
    ['/assets/images/careers/team/IMG_3262.JPG', 'Olesia Savicka', 'fa-brands fa-x-twitter', 'UX/UI Designer'],
    ['/assets/images/careers/team/IMG_3266.JPG', 'Ivan Vasilchenko', 'fa-brands fa-x-twitter', 'WebDEV.'],
    ['/assets/images/careers/team/IMG_3268.JPG', 'Erzhan Aydarbekov', 'fa-brands fa-x-twitter', 'Community Manager, PHP'],
    ['/assets/images/careers/team/IMG_3267.JPG', 'Yulia Belyaeva', 'fa-brands fa-x-twitter', 'Python, JS'],
    ['/assets/images/careers/team/IMG_3263.JPG', 'Gleb Trofimov', 'fa-brands fa-linkedin-in', 'AI/ML Engineer'],
    ['/assets/images/careers/team/IMG_3269.JPG', 'Denis Chernov', 'fa-brands fa-instagram', 'Blockchain'],
];
?>
<section>

  <div class="container">
    <div class="headline">Work at ΛΞV</div>
    <div class="main-text">Join a team and inspire the work.</div>
    <div class="sub-text">Discover how you can make an impact:
    see our areas of work,
    worldwide locations
    and opportunities for students.
    </div>	

		<a href="/careers" class="sub-link">See Opportunities <i class="uil uil-angle-right-b" aria-hidden="true"></i></a>
  </div>

  <blockquote>
    Creativity is just connecting things. When you ask creative people how they did something, they feel a little guilty because they didn't really do it, they just saw something. It seemed obvious to them after a while. That's because they were able to connect experiences they've had and synthesize new things.
    <span>Steve Jobs</span>
  </blockquote>

  <h2>our team</h2>
  <p>This is where individual imaginations gather together, committing to the values that lead to great work. Here, you’ll do more than join something — you’ll add something.</p>
  <div class="cards">
<?php foreach ($team as [$photo, $name, $icon, $role]): ?>

    <div class="card">
      <div class="card-img-wrapper">
        <img src="<?= $e($photo) ?>" alt="<?= $e($name) ?>">
      </div>
      <span class="social-icon" aria-hidden="true"><i class="<?= $e($icon) ?>"></i></span>
      <div class="card-content-wrapper">
        <div class="card-content">
          <h3><?= $e($name) ?></h3>
          <p><?= $e($role) ?></p>
          <div>
              <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="none" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><title>right</title><path d="M73.8 39.8H67v6.8H19.4v6.8H67v6.8h6.8v-6.8h6.8v-6.8h-6.8v-6.8zM60.2 33H67v6.8h-6.8zM53.4 26.2h6.8V33h-6.8zM60.2 60.2H67V67h-6.8zM53.4 67h6.8v6.8h-6.8z"></path></svg>
          </div>
        </div>
      </div>
    </div>
<?php endforeach; ?>

  </div>
</section>
