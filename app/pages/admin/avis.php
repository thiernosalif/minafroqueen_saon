<?php
$title = 'Avis';
if (is_post()) {
    csrf_check(); $id = (int) post('id'); $a = post('action');
    if ($a === 'delete') q('DELETE FROM reviews WHERE id = ?', [$id]);
    elseif (in_array($a, ['approved', 'hidden', 'pending'], true)) update('reviews', $id, ['status' => $a]);
    flash('Avis mis à jour.'); redirect('admin/avis');
}
$rows = all('SELECT * FROM reviews ORDER BY created DESC');
?>
<h1>Avis clients</h1>
<div class="table-wrap"><table><tr><th>Date</th><th>Cliente</th><th>Avis</th><th>Statut</th><th></th></tr>
<?php foreach ($rows as $r): ?><tr><td><?= dfr($r['created']) ?></td><td><?= e($r['name']) ?><br><?= str_repeat('★', (int) $r['rating']) ?></td><td><?= e($r['body']) ?></td><td><span class="pill <?= e($r['status']) ?>"><?= e(st($r['status'])) ?></span></td>
<td><?php foreach (['approved' => 'Publier', 'hidden' => 'Masquer', 'delete' => 'Supprimer'] as $k => $l): if ($r['status'] === $k) continue; ?><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="action" value="<?= $k ?>"><button class="btn sm <?= $k === 'delete' ? 'danger' : 'ghost' ?>"><?= $l ?></button></form> <?php endforeach; ?></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5" class="muted">Aucun avis.</td></tr><?php endif; ?></table></div>
