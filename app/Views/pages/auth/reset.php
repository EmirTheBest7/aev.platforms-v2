<?php
/**
 * Password reset: sending reset links needs an outgoing mail server, which is not configured. The page says so
 * instead of pretending to send anything.
 *
 * @var callable(mixed): string $e
 * @var string $email
 */
?>
	<main id="main">
		<div class="section">
			<div class="container">
				<div class="row full-height justify-content-center">
					<div class="col-12 text-center align-self-center p-form">
						<div class="section pb-5 pt-4 pt-sm-2 text-center">
							<div class="reset-card">
								<h4 class="mb-4 pb-0">Password reset</h4>
								<p>Self-service password reset by email is not available yet.</p>
								<p>Please write to <a class="link" href="mailto:<?= $e($email) ?>"><?= $e($email) ?></a> from the address of your account and we will help you.</p>
								<p class="mt-4"><a class="link" href="/home/auth">← Back to Log In</a></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>
