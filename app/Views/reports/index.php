<div class="page-heading">
  <div><p class="eyebrow">Report Generation</p><h1>System Report</h1></div>
  <button class="btn btn-primary" onclick="window.print()">Print report</button>
</div>
<section class="metric-grid">
  <article><span><?= (int) $stats['lost_total'] ?></span><p>Lost items</p></article>
  <article><span><?= (int) $stats['found_total'] ?></span><p>Found items</p></article>
  <article><span><?= (int) $stats['claimed_total'] ?></span><p>Claimed items</p></article>
  <article><span><?= (int) $stats['pending_claims'] ?></span><p>Pending claims</p></article>
</section>
<section class="panel mt-4">
  <h2>Item Image Report</h2>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Image</th><th>Tracking</th><th>Item</th><th>Type</th><th>Status</th><th>Location</th></tr></thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><?php if ($item['image_path']): ?><img class="thumb" src="<?= h($item['image_path']) ?>" alt="Item image"><?php else: ?><span class="text-secondary">No image</span><?php endif; ?></td>
            <td><?= h($item['tracking_code']) ?></td>
            <td><?= h($item['title']) ?></td>
            <td><?= h($item['type']) ?></td>
            <td><?= h($item['status']) ?></td>
            <td><?= h($item['location_name']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<div class="row g-4 mt-1">
  <div class="col-lg-6"><section class="panel"><h2>Activity Logs</h2><?php foreach ($activities as $activity): ?><p class="report-line"><strong><?= h($activity['action']) ?></strong> - <?= h($activity['description']) ?><small><?= h($activity['created_at']) ?></small></p><?php endforeach; ?></section></div>
  <div class="col-lg-6"><section class="panel"><h2>SMS Logs</h2><?php foreach ($smsLogs as $log): ?><p class="report-line"><strong><?= h($log['status']) ?></strong> - <?= h($log['recipient_phone']) ?><small><?= h($log['message']) ?></small></p><?php endforeach; ?></section></div>
</div>
