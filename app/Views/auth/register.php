<section class="auth-shell">
  <div class="auth-panel">
    <p class="eyebrow">New Reporter</p>
    <h1>Create your account.</h1>
    <form method="post" action="<?= url('register.post') ?>" class="needs-validation mt-4" novalidate>
      <?= csrf_field() ?>
      <label class="form-label">Full name</label>
      <input class="form-control" name="name" required>
      <label class="form-label mt-3">Email</label>
      <input class="form-control" type="email" name="email" required>
      <label class="form-label mt-3">Password</label>
      <input class="form-control" type="password" name="password" minlength="8" pattern="(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}" required>
      <div class="form-text">Use at least 8 characters with 1 uppercase letter, 1 number, and 1 special character.</div>
      <button class="btn btn-primary w-100 mt-4" type="submit">Create account</button>
    </form>
  </div>
</section>
