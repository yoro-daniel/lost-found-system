<div class="page-heading"><div><p class="eyebrow">Administration</p><h1>User Management</h1></div></div>
<div class="row g-4">
  <div class="col-lg-4">
    <section class="panel">
      <h2>Create User</h2>
      <form method="post" action="<?= url('users.store') ?>" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <label class="form-label">Name</label><input class="form-control" name="name" required>
        <label class="form-label mt-3">Email</label><input class="form-control" type="email" name="email" required>
        <label class="form-label mt-3">Password</label><input class="form-control" type="password" name="password" required>
        <div class="row g-2 mt-1">
          <div class="col"><label class="form-label">Role</label><select class="form-select" name="role"><option value="user">User</option><option value="admin">Admin</option></select></div>
          <div class="col"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
        <button class="btn btn-primary w-100 mt-3">Create user</button>
      </form>
    </section>
  </div>
  <div class="col-lg-8">
    <section class="panel">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Save</th></tr></thead>
          <tbody>
            <?php foreach ($users as $row): ?>
              <tr>
                <form method="post" action="<?= url('users.update') ?>">
                  <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                  <td><input class="form-control form-control-sm" name="name" value="<?= h($row['name']) ?>"></td>
                  <td><?= h($row['email']) ?></td>
                  <td><select class="form-select form-select-sm" name="role"><option value="user" <?= $row['role'] === 'user' ? 'selected' : '' ?>>User</option><option value="admin" <?= $row['role'] === 'admin' ? 'selected' : '' ?>>Admin</option></select></td>
                  <td><select class="form-select form-select-sm" name="status"><option value="active" <?= $row['status'] === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= $row['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></td>
                  <td><button class="btn btn-sm btn-outline-primary">Save</button></td>
                </form>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</div>
