<div class="page-heading">
  <div>
    <p class="eyebrow">CRUD + JOIN Queries</p>
    <h1>Item Registry</h1>
  </div>
  <a class="btn btn-primary" href="<?= url('items.create') ?>">Report item</a>
</div>

<section class="panel">
  <form class="row g-3 align-items-end" method="get">
    <input type="hidden" name="route" value="items">
    <div class="col-lg-4"><label class="form-label">Smart search</label><input class="form-control" name="q" value="<?= h($filters['q']) ?>" placeholder="Title, description, tracking code, location"></div>
    <div class="col-md-2"><label class="form-label">Type</label><select class="form-select" name="type"><option value="">All</option><option value="lost" <?= $filters['type'] === 'lost' ? 'selected' : '' ?>>Lost</option><option value="found" <?= $filters['type'] === 'found' ? 'selected' : '' ?>>Found</option></select></div>
    <div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All</option><?php foreach (['open','matched','claimed','closed'] as $status): ?><option value="<?= $status ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><label class="form-label">Category</label><select class="form-select" name="category_id"><option value="">All</option><?php foreach ($categories as $category): ?><option value="<?= $category['id'] ?>" <?= (string) $filters['category_id'] === (string) $category['id'] ? 'selected' : '' ?>><?= h($category['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><label class="form-label">Location</label><select class="form-select" name="location_id"><option value="">All</option><?php foreach ($locations as $location): ?><option value="<?= $location['id'] ?>" <?= (string) $filters['location_id'] === (string) $location['id'] ? 'selected' : '' ?>><?= h($location['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-12 d-flex gap-2"><button class="btn btn-primary" type="submit">Apply filters</button><a class="btn btn-outline-secondary" href="<?= url('items') ?>">Reset</a></div>
  </form>
</section>

<section class="panel mt-4">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Item</th><th>Type</th><th>Status</th><th>Category</th><th>Location</th><th>Reporter</th><th>Claims</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><div class="d-flex align-items-center gap-3"><?php if ($item['image_path']): ?><img class="thumb" src="<?= asset($item['image_path']) ?>" alt="Item image"><?php endif; ?><div><strong><?= h($item['title']) ?></strong><small><?= h($item['tracking_code']) ?> - <?= h($item['description']) ?></small></div></div></td>
            <td><span class="badge text-bg-<?= $item['type'] === 'lost' ? 'warning' : 'info' ?>"><?= h($item['type']) ?></span></td>
            <td><span class="badge text-bg-secondary"><?= h($item['status']) ?></span></td>
            <td><?= h($item['category_name']) ?></td>
            <td><?= h($item['location_name']) ?></td>
            <td><?= h($item['reporter_name']) ?></td>
            <td><?= (int) $item['claim_count'] ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="<?= url('items.edit', ['id' => $item['id']]) ?>">Edit</a>
              <?php if (is_admin()): ?><form class="d-inline" method="post" action="<?= url('items.delete') ?>" onsubmit="return confirm('Delete this item?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
