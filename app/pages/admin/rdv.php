<?php
$title = 'Rendez-vous';
if (is_post()) {
    csrf_check();
    $id = (int) post('id'); $a = post('action');
    if ($a === 'delete') q('DELETE FROM appointments WHERE id = ?', [$id]);
    elseif (in_array($a, ['confirmed', 'cancelled', 'done', 'pending'], true)) update('appointments', $id, ['status' => $a]);
    flash('Rendez-vous mis à jour.'); redirect('admin/rdv' . (isset($_GET['f']) ? '?f=' . urlencode($_GET['f']) : ''));
}
$f = $_GET['f'] ?? 'open';
$where = ['open' => "a.status IN ('pending','confirmed')", 'message' => "a.status = 'message'", 'done' => "a.status IN ('done','cancelled')", 'all' => '1=1'][$f] ?? '1=1';
$rows = all("SELECT a.*, s.name AS service FROM appointments a LEFT JOIN services s ON s.id = a.service_id WHERE $where ORDER BY a.rdv_date DESC, a.rdv_time DESC LIMIT 200");
$tabs = ['open' => 'À traiter', 'message' => 'Messages contact', 'done' => 'Terminés / annulés', 'all' => 'Tous'];
?>
<h1>Rendez-vous</h1>
<div class="bar"><?php foreach ($tabs as $k => $l): ?><a class="btn sm <?= $f === $k ? '' : 'ghost' ?>" href="?f=<?= $k ?>"><?= $l ?></a><?php endforeach; ?></div>
<div class="table-wrap"><table><tr><th>Date</th><th>Cliente</th><th>Soin / message</th><th>Statut</th><th>Actions</th></tr>
<?php foreach ($rows as $a): ?><tr>
<td><?= $a['status'] === 'message' ? dfr($a['created']) : dfr($a['rdv_date']) . '<br>' . e($a['rdv_time']) ?></td>
<td><a href="<?= url('admin/clients/' . (int) $a['client_id']) ?>"><?= e($a['name']) ?></a><br><span class="muted"><?= e($a['phone']) ?></span>
<br><a target="_blank" href="<?= e(wa_link_to($a['phone'], 'Bonjour ' . $a['name'] . ', ')) ?>">WhatsApp ↗</a></td>
<td><?= e($a['service']) ?><br><span class="muted"><?= e($a['message']) ?></span></td>
<td><span class="pill <?= e($a['status']) ?>"><?= e(st($a['status'])) ?></span></td>
<td><?php foreach (['confirmed' => 'Confirmer', 'done' => 'Terminé', 'cancelled' => 'Annuler'] as $k => $l): if ($a['status'] === $k || $a['status'] === 'message') continue; ?>
<form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $a['id'] ?>"><input type="hidden" name="action" value="<?= $k ?>"><button class="btn sm ghost"><?= $l ?></button></form> <?php endforeach; ?>
<?php if ($a['status'] !== 'message'): ?><a class="btn sm ghost" href="<?= url('admin/factures/new?client=' . (int) $a['client_id'] . '&rdv=' . (int) $a['id']) ?>">Facturer</a><?php endif; ?>
<form method="post" class="inline" onsubmit="return confirm('Supprimer ?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $a['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn sm danger">×</button></form></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5" class="muted">Rien à afficher.</td></tr><?php endif; ?></table></div>
