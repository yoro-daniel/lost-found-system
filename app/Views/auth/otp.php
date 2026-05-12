<section class="auth-shell">
  <div class="auth-panel">
    <p class="eyebrow">Two-step verification</p>
    <h1>Enter the OTP sent to your email.</h1>
    <p class="text-secondary">The code expires in 10 minutes.</p>
    <form method="post" action="<?= url('otp.post') ?>" class="needs-validation mt-4" novalidate>
      <?= csrf_field() ?>
      <label class="form-label">One-time password</label>
      <input class="form-control otp-input" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus>
      <button class="btn btn-primary w-100 mt-4" type="submit">Verify and sign in</button>
      <a class="btn btn-link w-100 mt-2" href="<?= url('login') ?>">Use another account</a>
    </form>
  </div>
</section>
