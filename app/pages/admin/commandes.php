<?php
$title = 'Commandes';
if (is_post()) {
    csrf_check();
    $id = (int) post('id'); $s = post('action');
    $o = one('SELECT * FROM orders WHERE id = ?', [$id]);
    if ($o && in_array($s, ['new', 'delivered', 'cancelled'], true)) {
        update('orders', $id, ['status' => $s]);
        $label = "Commande #$id"; $tr = one('SELECT id FROM transactions WHERE label = ?', [$label]);
        if ($s === 'delivered' && !$tr) insert('transactions', ['tr_date' => date('Y-m-d'), 'type' => 'in', 'label' => $label, 'category' => 'Boutique', 'amount' => (int) $o['total']]);
        if ($s !== 'delivered' && $tr) q('DELETE FROM transactions WHERE id = ?', [$tr['id']]);
        if ($s === 'cancelled' && $o['status'] !== 'cancelled') foreach (all('SELECT * FROM order_items WHERE order_id = ?', [$id]) as $it) q('UPDATE products SET stock = stock + ? WHERE id = ?', [$it['qty'], $it['product_id']]);
        flash('Commande mise à jour (une livraison ajoute l\'entrée en comptabilité).');
    }
    redirect('admin/commandes');
}
$rows = all('SELECT * FROM orders ORDER BY created DESC LIMIT 200');
?>
<h1>Commandes boutique</h1>
<div class="table-wrap"><table><tr><th>N°</th><th>Date</th><th>Cliente</th><th>Détail</th><th>Total</th><th>Statut</th><th></th></tr>
<?php foreach ($rows as $o): $its = all('SELECT * FROM order_items WHERE order_id = ?', [$o['id']]); ?><tr>
<td>#<?= (int) $o['id'] ?></td><td><?= dfr($o['created'], true) ?></td>
<td><?= e($o['name']) ?><br><span class="muted"><?= e($o['phone']) ?></span><br><span class="muted"><?= e($o['address']) ?></span></td>
<td><?php foreach ($its as $it): ?><?= (int) $it['qty'] ?> × <?= e($it['label']) ?><br><?php endforeach; ?></td><td><?= money($o['total']) ?></td>
<td><span class="pill <?= e($o['status']) ?>"><?= e(st($o['status'])) ?></span></td>
<td><?php foreach (['delivered' => 'Livrée', 'cancelled' => 'Annuler'] as $k => $l): if ($o['status'] === $k) continue; ?><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $o['id'] ?>"><input type="hidden" name="action" value="<?= $k ?>"><button class="btn sm ghost"><?= $l ?></button></form> <?php endforeach; ?></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="7" class="muted">Aucune commande.</td></tr><?php endif; ?></table></div>
