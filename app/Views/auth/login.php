<section class="auth-shell">
  <div class="auth-panel">
    <p class="eyebrow">Campus Recovery Desk</p>
    <h1>Sign in to manage lost and found records.</h1>
    <p class="text-secondary">Demo admin: schoolyoro@gmail.com / Testing!1</p>
    <form method="post" action="<?= url('login.post') ?>" class="needs-validation mt-4" novalidate>
      <?= csrf_field() ?>
      <label class="form-label">Email</label>
      <input class="form-control" type="email" name="email" required>
      <label class="form-label mt-3">Password</label>
      <input class="form-control" type="password" name="password" required>
      <button class="btn btn-primary w-100 mt-4" type="submit">Sign in</button>
    </form>
  </div>
</section>
