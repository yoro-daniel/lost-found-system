<?php $user = current_user(); $messages = flashes(); ?>
<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title ?? config('name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
</head>
<body>
  <nav class="navbar navbar-expand-lg app-nav sticky-top">
    <div class="container-fluid px-4">
      <a class="navbar-brand fw-bold" href="<?= url($user ? 'items' : 'login') ?>">LostFound</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <?php if ($user): ?>
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <?php if (is_admin()): ?><li class="nav-item"><a class="nav-link" href="<?= url('dashboard') ?>">Dashboard</a></li><?php endif; ?>
            <li class="nav-item"><a class="nav-link" href="<?= url('items') ?>">Items</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= url('items.create') ?>">Report Item</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= url('claims') ?>">Claims</a></li>
            <?php if (is_admin()): ?>
              <li class="nav-item"><a class="nav-link" href="<?= url('users') ?>">Users</a></li>
              <li class="nav-item"><a class="nav-link" href="<?= url('reports') ?>">Reports</a></li>
            <?php endif; ?>
          </ul>
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary" id="themeToggle" type="button">Dark mode</button>
            <span class="small text-secondary"><?= h($user['name']) ?></span>
            <a class="btn btn-sm btn-primary" href="<?= url('logout') ?>">Logout</a>
          </div>
        <?php else: ?>
          <div class="ms-auto d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" id="themeToggle" type="button">Dark mode</button>
            <a class="btn btn-sm btn-outline-primary" href="<?= url('login') ?>">Sign in</a>
            <a class="btn btn-sm btn-primary" href="<?= url('register') ?>">Register</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <main class="container-fluid px-4 py-4">
    <?php require $viewFile; ?>
  </main>

  <div class="toast-container position-fixed top-0 end-0 p-3">
    <?php foreach ($messages as $message): ?>
      <div class="toast align-items-center border-0 text-bg-<?= h($message['type']) ?>" role="alert">
        <div class="d-flex">
          <div class="toast-body"><?= h($message['message']) ?></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>
