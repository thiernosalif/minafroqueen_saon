<?php
$title = 'Tableau de bord';
$m = date('Y-m');
$in = (int) val("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='in' AND tr_date LIKE ?", [$m . '%']);
$out = (int) val("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='out' AND tr_date LIKE ?", [$m . '%']);
$unpaid = (int) val("SELECT COALESCE(SUM(total),0) FROM invoices WHERE status='unpaid'");
$next = all("SELECT a.*, s.name AS service FROM appointments a LEFT JOIN services s ON s.id = a.service_id WHERE a.status IN ('pending','confirmed') AND a.rdv_date >= ? ORDER BY a.rdv_date, a.rdv_time LIMIT 8", [date('Y-m-d')]);
?>
<h1>Tableau de bord</h1>
<div class="cards">
  <div class="card stat"><b><?= money($in) ?></b><span>Entrées du mois</span></div>
  <div class="card stat"><b><?= money($out) ?></b><span>Dépenses du mois</span></div>
  <div class="card stat"><b><?= money($in - $out) ?></b><span>Résultat du mois</span></div>
  <div class="card stat"><b><?= money($unpaid) ?></b><span>Factures impayées</span></div>
  <div class="card stat"><b><?= (int) val('SELECT COUNT(*) FROM clients') ?></b><span>Clientes</span></div>
</div>
<h2>Prochains rendez-vous</h2>
<div class="table-wrap"><table><tr><th>Date</th><th>Heure</th><th>Cliente</th><th>Soin</th><th>Statut</th></tr>
<?php foreach ($next as $a): ?><tr><td><?= dfr($a['rdv_date']) ?></td><td><?= e($a['rdv_time']) ?></td><td><?= e($a['name']) ?><br><span class="muted"><?= e($a['phone']) ?></span></td><td><?= e($a['service']) ?></td><td><span class="pill <?= e($a['status']) ?>"><?= e(st($a['status'])) ?></span></td></tr><?php endforeach; ?>
<?php if (!$next): ?><tr><td colspan="5" class="muted">Aucun rendez-vous à venir.</td></tr><?php endif; ?></table></div>
