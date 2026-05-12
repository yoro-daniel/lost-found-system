<div class="page-heading"><div><p class="eyebrow">Claim Workflow</p><h1>Claim Requests</h1></div></div>
<div class="row g-4">
  <div class="col-lg-4">
    <section class="panel">
      <h2>Submit Claim</h2>
      <form method="post" action="<?= url('claims.store') ?>" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <label class="form-label">Item</label><select class="form-select" name="item_id" required><?php foreach ($items as $item): ?><option value="<?= $item['id'] ?>"><?= h($item['tracking_code']) ?> - <?= h($item['title']) ?></option><?php endforeach; ?></select>
        <label class="form-label mt-3">Claimant name</label><input class="form-control" name="claimant_name" value="<?= h(current_user()['name']) ?>" required>
        <label class="form-label mt-3">Claimant email</label><input class="form-control" type="email" name="claimant_email" value="<?= h(current_user()['email']) ?>" required>
        <label class="form-label mt-3">Phone</label><input class="form-control" name="claimant_phone">
        <label class="form-label mt-3">Proof of ownership</label><textarea class="form-control" name="proof_text" rows="4" required></textarea>
        <button class="btn btn-primary w-100 mt-3">Submit claim</button>
      </form>
    </section>
  </div>
  <div class="col-lg-8">
    <section class="panel">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Item</th><th>Claimant</th><th>Proof</th><th>Status</th><th>Review</th></tr></thead>
          <tbody>
            <?php foreach ($claims as $claim): ?>
              <tr>
                <td><strong><?= h($claim['tracking_code']) ?></strong><small><?= h($claim['item_title']) ?></small></td>
                <td><?= h($claim['claimant_name']) ?><small><?= h($claim['claimant_email']) ?></small></td>
                <td><?= h($claim['proof_text']) ?></td>
                <td><span class="badge text-bg-secondary"><?= h($claim['status']) ?></span></td>
                <td>
                  <?php if (is_admin() && $claim['status'] === 'pending'): ?>
                    <form class="d-inline" method="post" action="<?= url('claims.review') ?>"><?= csrf_field() ?><input type="hidden" name="claim_id" value="<?= (int) $claim['id'] ?>"><input type="hidden" name="status" value="approved"><button class="btn btn-sm btn-success">Approve</button></form>
                    <form class="d-inline" method="post" action="<?= url('claims.review') ?>"><?= csrf_field() ?><input type="hidden" name="claim_id" value="<?= (int) $claim['id'] ?>"><input type="hidden" name="status" value="rejected"><button class="btn btn-sm btn-outline-danger">Reject</button></form>
                  <?php else: ?>-<?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</div>
