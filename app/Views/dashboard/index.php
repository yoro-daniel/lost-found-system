<div class="page-heading">
  <div>
    <p class="eyebrow">Admin Dashboard</p>
    <h1>Lost and Found Overview</h1>
  </div>
  <a class="btn btn-primary" href="<?= url('reports') ?>">Generate report</a>
</div>

<section class="metric-grid">
  <article><span><?= (int) $stats['lost_total'] ?></span><p>Total lost items</p></article>
  <article><span><?= (int) $stats['found_total'] ?></span><p>Total found items</p></article>
  <article><span><?= (int) $stats['claimed_total'] ?></span><p>Claimed items</p></article>
  <article><span><?= (int) $stats['pending_claims'] ?></span><p>Pending claims</p></article>
</section>

<div class="row g-4 mt-1">
  <div class="col-xl-8">
    <section class="panel">
      <h2>Item Analytics</h2>
      <canvas id="itemsChart" height="120"></canvas>
    </section>
  </div>
  <div class="col-xl-4">
    <section class="panel">
      <h2>Category Distribution</h2>
      <canvas id="categoryChart" height="180"></canvas>
    </section>
  </div>
</div>

<div class="row g-4 mt-1">
  <div class="col-xl-7">
    <section class="panel">
      <h2>Recent Activity Logs</h2>
      <div class="activity-list">
        <?php foreach ($activities as $activity): ?>
          <div><strong><?= h($activity['action']) ?></strong><span><?= h($activity['description']) ?></span><small><?= h($activity['created_at']) ?></small></div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
  <div class="col-xl-5">
    <section class="panel">
      <h2>Email Delivery</h2>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Recipient</th><th>Status</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($emailLogs as $log): ?>
              <tr><td><?= h($log['recipient_email']) ?></td><td><span class="badge text-bg-<?= $log['status'] === 'sent' ? 'success' : 'danger' ?>"><?= h($log['status']) ?></span></td><td><?= h($log['created_at']) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</div>

<script>
window.dashboardStats = {
  itemTotals: [<?= (int) $stats['lost_total'] ?>, <?= (int) $stats['found_total'] ?>, <?= (int) $stats['claimed_total'] ?>, <?= (int) $stats['pending_claims'] ?>],
  categoryLabels: <?= json_encode(array_column($byCategory, 'name')) ?>,
  categoryTotals: <?= json_encode(array_map('intval', array_column($byCategory, 'total'))) ?>
};
</script>
