<?php
/**
 * Log in / Sign up (port of the original home/auth page): a flip card with the two forms.
 * The original "Sign-In Options" panel listed crypto wallets that never worked and is not carried over.
 *
 * @var callable(mixed): string $e
 * @var string $csrf
 * @var string $ts
 * @var bool $showRegister   the Sign Up face is shown (after a failed sign-up, or ?signup)
 * @var list<string> $loginErrors
 * @var list<string> $registerErrors
 * @var string $notice      one-time success message (account created)
 * @var array<string, string> $old
 * @var string $referral
 */
$val = static fn(string $k): string => $old[$k] ?? '';
?>
	<main id="main">
		<div class="section">
			<div class="container">
				<div class="row full-height justify-content-center">
					<div class="col-12 text-center align-self-center p-form">
						<div class="section pb-5 pt-4 pt-sm-2 text-center">
							<h6 class="mb-0 pb-3"><span>Log In </span><span>Sign Up</span></h6>
							<input class="checkbox" type="checkbox" id="reg-log" name="reg-log"<?= $showRegister ? ' checked' : '' ?> aria-label="Switch between Log In and Sign Up" />
							<label class="flip-label" for="reg-log"></label>
							<div class="card-3d-wrap mx-auto">
								<div class="card-3d-wrapper">

									<form name="form1" method="post" action="/home/auth/login">
										<input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
										<div class="card-front overflow-hidden ">
											<div id="login1" class="center-wrap switch-group">
												<div class="section text-center">
													<h4 class="mb-4 pb-3">Log In</h4>
<?php if ($notice !== ''): ?>
													<p class="form-notice" role="status"><?= $e($notice) ?></p>
<?php endif; ?>
<?php foreach ($loginErrors as $error): ?>
													<p class="form-error" role="alert"><?= $e($error) ?></p>
<?php endforeach; ?>
													<div class="form-group">
														<input type="email" name="logemail" class="form-style"
															placeholder="Your Email" id="logemail" autocomplete="username" aria-label="Email" required maxlength="254" value="<?= $e($val('logemail')) ?>">
														<i class="input-icon uil uil-at" aria-hidden="true"></i>
													</div>
													<div class="form-group mt-2">
														<input type="password" name="logpass" class="form-style"
															placeholder="Your Password" id="logpass" autocomplete="current-password" aria-label="Password" required maxlength="128">
														<i class="input-icon uil uil-lock-alt" aria-hidden="true"></i>
													</div>

													<button type="submit" class="submit-btn mt-4">
														<span></span>
														<span></span>
														<span></span>
														<span></span>
														Log In
													</button>

													<p class="mb-0 mt-4 text-center">
														<a href="/home/auth/reset" class="link">Forgot your password?</a>
													</p>
												</div>
											</div>
										</div>
									</form>

									<form name="form2" method="post" action="/home/auth/register">
										<input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
										<input type="hidden" name="_ts" value="<?= $e($ts) ?>">
										<div class="hp" aria-hidden="true"><label for="website">Leave this field empty</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
										<div class="card-back">
											<div class="center-wrap">
												<div class="section text-center">
													<h4 class="mb-4 pb-0">Sign Up</h4>
<?php foreach ($registerErrors as $error): ?>
													<p class="form-error" role="alert"><?= $e($error) ?></p>
<?php endforeach; ?>

													<div class="form-group">
														<input type="text" name="regname" class="form-style"
															placeholder="Your Full Name" id="regname" aria-label="Full name"
															autocomplete="name" maxlength="80" value="<?= $e($val('regname')) ?>">
														<i class="input-icon uil uil-user" aria-hidden="true"></i>
													</div>
													<div class="form-group mt-2">
														<input type="text" name="regnick" class="form-style"
															placeholder="Desired Nickname" id="regnick" aria-label="Nickname" required minlength="3" maxlength="32"
															autocomplete="username" value="<?= $e($val('regnick')) ?>">
														<i class="input-icon uil uil-user" aria-hidden="true"></i>
													</div>
													<div class="form-group mt-2">
														<input type="email" name="regemail" class="form-style"
															placeholder="Your Email" id="regemail" aria-label="Email" required maxlength="254"
															autocomplete="email" value="<?= $e($val('regemail')) ?>">
														<i class="input-icon uil uil-at" aria-hidden="true"></i>
													</div>
													<div class="form-group mt-2 flex-row">
														<input type="password" name="regpass" class="form-style" minlength="10" maxlength="128"
															placeholder="Your Password" id="regpass" aria-label="Password" required autocomplete="new-password">
														<i class="input-icon uil uil-lock-alt" aria-hidden="true"></i>
														<button type="button" class="input-icon input-icon--right uil uil-eye-slash" id="eye" aria-label="Show password"></button>
													</div>
													<div class="form-group mt-2">
														<input type="password" name="regpass2" class="form-style"
															placeholder="Repeat Password" id="regpass2" aria-label="Repeat password"
															autocomplete="new-password" required minlength="10" maxlength="128">
														<i class="input-icon uil uil-lock-alt" aria-hidden="true"></i>
													</div>
<?php if ($referral !== ''): ?>
													<div class="form-group mt-2">
														<input type="text" name="regref" class="form-style"
															value="<?= $e($referral) ?>" maxlength="40"
															placeholder="Referral Code" aria-label="Referral code" autocomplete="off">
														<i class="input-icon uil uil-users-alt" aria-hidden="true"></i>
													</div>
<?php endif; ?>

													<button type="submit" class="submit-btn submit-btn--register mt-4">
														<span></span>
														<span></span>
														<span></span>
														<span></span>
														Register
													</button>
													<p class="hint">At least 10 characters, with upper and lower case letters and a digit.</p>
												</div>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>
