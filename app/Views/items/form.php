<?php $editing = (bool) $item; ?>
<div class="page-heading">
  <div><p class="eyebrow">Report Center</p><h1><?= $editing ? 'Edit Item' : 'Report Lost or Found Item' ?></h1></div>
</div>
<section class="panel">
  <form method="post" enctype="multipart/form-data" action="<?= url($editing ? 'items.update' : 'items.store') ?>" class="row g-3 needs-validation" novalidate>
    <?= csrf_field() ?>
    <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><?php endif; ?>
    <div class="col-md-3"><label class="form-label">Type</label><select class="form-select" name="type" required><option value="lost" <?= ($item['type'] ?? '') === 'lost' ? 'selected' : '' ?>>Lost</option><option value="found" <?= ($item['type'] ?? '') === 'found' ? 'selected' : '' ?>>Found</option></select></div>
    <?php if ($editing): ?><div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status" required><?php foreach (['open','matched','claimed','closed'] as $status): ?><option value="<?= $status ?>" <?= ($item['status'] ?? '') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select></div><?php endif; ?>
    <div class="col-md-<?= $editing ? '6' : '9' ?>"><label class="form-label">Item title</label><input class="form-control" name="title" value="<?= h($item['title'] ?? '') ?>" required></div>
    <div class="col-md-6"><label class="form-label">Category</label><select class="form-select" name="category_id" required><?php foreach ($categories as $category): ?><option value="<?= $category['id'] ?>" <?= (string) ($item['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>><?= h($category['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Location</label><select class="form-select" name="location_id" required><?php foreach ($locations as $location): ?><option value="<?= $location['id'] ?>" <?= (string) ($item['location_id'] ?? '') === (string) $location['id'] ? 'selected' : '' ?>><?= h($location['name']) ?> - <?= h($location['building']) ?></option><?php endforeach; ?></select></div>
    <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="4" required><?= h($item['description'] ?? '') ?></textarea></div>
    <div class="col-md-4"><label class="form-label">Date lost/found</label><input class="form-control" type="date" name="date_seen" value="<?= h($item['date_seen'] ?? date('Y-m-d')) ?>" required></div>
    <div class="col-md-4"><label class="form-label">Contact email</label><input class="form-control" type="email" name="contact_email" value="<?= h($item['contact_email'] ?? current_user()['email']) ?>" required></div>
    <div class="col-md-4"><label class="form-label">Contact phone</label><input class="form-control" name="contact_phone" value="<?= h($item['contact_phone'] ?? '') ?>"></div>
    <div class="col-12">
      <label class="form-label">Item image</label>
      <input class="form-control" id="imageInput" type="file" name="image" accept="image/png,image/jpeg,image/webp">
      <div class="form-text">Images are validated, uploaded to Cloudinary, and delivered with automatic optimization.</div>
      <div class="image-preview-wrap mt-3 <?= empty($item['image_path']) ? 'd-none' : '' ?>" id="imagePreviewWrap">
        <img id="imagePreview" src="<?= h($item['image_path'] ?? '') ?>" alt="Image preview">
      </div>
    </div>
    <div class="col-12"><button class="btn btn-primary" type="submit"><?= $editing ? 'Save changes' : 'Submit report' ?></button></div>
  </form>
</section>
