<?php
$title = 'Factures';
$arg = $params[0] ?? '';
$mode = $params[1] ?? '';

function sync_invoice_payment(int $id): void {
    $inv = one('SELECT * FROM invoices WHERE id = ?', [$id]);
    $tr = one('SELECT id FROM transactions WHERE invoice_id = ?', [$id]);
    if ($inv['status'] === 'paid') {
        $d = ['tr_date' => date('Y-m-d'), 'type' => 'in', 'label' => 'Facture ' . $inv['number'], 'category' => 'Prestations', 'amount' => (int) $inv['total'], 'invoice_id' => $id];
        if ($tr) update('transactions', (int) $tr['id'], ['amount' => (int) $inv['total']]); else insert('transactions', $d);
    } elseif ($tr) q('DELETE FROM transactions WHERE id = ?', [$tr['id']]);
}

if (is_post()) {
    csrf_check();
    $id = (int) ($arg === 'new' ? 0 : $arg);
    if (post('action') === 'delete' && $id) { q('DELETE FROM transactions WHERE invoice_id = ?', [$id]); q('DELETE FROM invoice_items WHERE invoice_id = ?', [$id]); q('DELETE FROM invoices WHERE id = ?', [$id]); flash('Facture supprimée.'); redirect('admin/factures'); }
    $labels = $_POST['label'] ?? []; $qtys = $_POST['qty'] ?? []; $prices = $_POST['price'] ?? [];
    $items = []; $total = 0;
    foreach ($labels as $i => $l) { $l = trim((string) $l); if ($l === '') continue; $qt = max(1, (int) ($qtys[$i] ?? 1)); $pr = max(0, (int) ($prices[$i] ?? 0)); $items[] = [$l, $qt, $pr]; $total += $qt * $pr; }
    $client = (int) post('client_id') ?: null;
    if (!$items) { flash('Ajoutez au moins une ligne.', 'err'); redirect('admin/factures/' . ($id ?: 'new')); }
    $d = ['client_id' => $client, 'inv_date' => post('inv_date') ?: date('Y-m-d'), 'status' => post('status') === 'paid' ? 'paid' : 'unpaid', 'notes' => post('notes'), 'total' => $total];
    if ($id) { update('invoices', $id, $d); q('DELETE FROM invoice_items WHERE invoice_id = ?', [$id]); }
    else { $n = (int) val("SELECT COUNT(*) FROM invoices WHERE inv_date LIKE ?", [date('Y') . '%']) + 1; $d['number'] = sprintf('F-%s-%04d', date('Y'), $n); $id = insert('invoices', $d); }
    foreach ($items as [$l, $qt, $pr]) insert('invoice_items', ['invoice_id' => $id, 'label' => $l, 'qty' => $qt, 'price' => $pr]);
    sync_invoice_payment($id);
    if (!empty($_GET['rdv'])) update('appointments', (int) $_GET['rdv'], ['status' => 'done']);
    flash('Facture enregistrée.'); redirect("admin/factures/$id");
}

if ($arg !== '' && $arg !== 'new' && $mode === 'print'):
    $inv = one('SELECT i.*, c.name AS cname, c.phone AS cphone FROM invoices i LEFT JOIN clients c ON c.id = i.client_id WHERE i.id = ?', [(int) $arg]);
    if (!$inv) { echo 'Introuvable'; return; }
    $items = all('SELECT * FROM invoice_items WHERE invoice_id = ?', [$inv['id']]);
    $raw = true; ?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Facture <?= e($inv['number']) ?></title>
<style>body{font-family:Inter,Arial,sans-serif;max-width:760px;margin:2rem auto;padding:0 1rem;color:#222}table{width:100%;border-collapse:collapse;margin:1.5rem 0}th,td{padding:.6rem;border-bottom:1px solid #ddd;text-align:left}.r{text-align:right}h1{color:#4f2c7c;margin:0}.top{display:flex;justify-content:space-between}.paid{color:#2f7a4d;font-weight:700}@media print{.np{display:none}}</style></head><body>
<p class="np"><button onclick="print()">Imprimer / Enregistrer en PDF</button></p>
<div class="top"><div><h1><?= e(setting('salon_name', 'Min Afro Queen')) ?></h1><?= e(setting('address')) ?><br>WhatsApp : +<?= e(wa_number()) ?></div>
<div class="r"><strong>FACTURE <?= e($inv['number']) ?></strong><br><?= dfr($inv['inv_date']) ?><br><?= $inv['status'] === 'paid' ? '<span class="paid">PAYÉE</span>' : 'À régler' ?></div></div>
<p><strong>Cliente :</strong> <?= e($inv['cname'] ?: '—') ?> <?= e($inv['cphone']) ?></p>
<table><tr><th>Désignation</th><th class="r">Qté</th><th class="r">Prix unitaire</th><th class="r">Total</th></tr>
<?php foreach ($items as $it): ?><tr><td><?= e($it['label']) ?></td><td class="r"><?= (int) $it['qty'] ?></td><td class="r"><?= money($it['price']) ?></td><td class="r"><?= money($it['qty'] * $it['price']) ?></td></tr><?php endforeach; ?>
<tr><td colspan="3" class="r"><strong>Total</strong></td><td class="r"><strong><?= money($inv['total']) ?></strong></td></tr></table>
<?php if ($inv['notes']): ?><p><?= nl2br(e($inv['notes'])) ?></p><?php endif; ?><p>Merci de votre confiance.</p></body></html>
<?php return; endif;

if ($arg === '') {
    $rows = all('SELECT i.*, c.name AS cname FROM invoices i LEFT JOIN clients c ON c.id = i.client_id ORDER BY i.inv_date DESC, i.id DESC LIMIT 300'); ?>
<h1>Factures</h1><div class="bar"><a class="btn" href="<?= url('admin/factures/new') ?>">Nouvelle facture</a></div>
<div class="table-wrap"><table><tr><th>N°</th><th>Date</th><th>Cliente</th><th>Total</th><th>Statut</th></tr>
<?php foreach ($rows as $i): ?><tr><td><a href="<?= url('admin/factures/' . (int) $i['id']) ?>"><?= e($i['number']) ?></a></td><td><?= dfr($i['inv_date']) ?></td><td><?= e($i['cname']) ?></td><td><?= money($i['total']) ?></td><td><span class="pill <?= e($i['status']) ?>"><?= e(st($i['status'])) ?></span></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5" class="muted">Aucune facture.</td></tr><?php endif; ?></table></div>
<?php return; }

$inv = $arg === 'new' ? null : one('SELECT * FROM invoices WHERE id = ?', [(int) $arg]);
if ($arg !== 'new' && !$inv) { echo '<h1>Facture introuvable</h1>'; return; }
$items = $inv ? all('SELECT * FROM invoice_items WHERE invoice_id = ?', [$inv['id']]) : [];
$clientSel = (int) ($inv['client_id'] ?? $_GET['client'] ?? 0);
if (!$items && !empty($_GET['rdv'])) {
    $r = one('SELECT s.name, s.price FROM appointments a JOIN services s ON s.id = a.service_id WHERE a.id = ?', [(int) $_GET['rdv']]);
    if ($r) $items[] = ['label' => $r['name'], 'qty' => 1, 'price' => $r['price']];
}
if (!$items) $items[] = ['label' => '', 'qty' => 1, 'price' => 0];
$clients = all('SELECT id, name, phone FROM clients ORDER BY name');
?>
<h1><?= $inv ? 'Facture ' . e($inv['number']) : 'Nouvelle facture' ?></h1>
<?php if ($inv): ?><p><a class="btn ghost" target="_blank" href="<?= url('admin/factures/' . (int) $inv['id'] . '/print') ?>">Imprimer / PDF</a> <a href="<?= url('admin/factures') ?>">← Retour</a></p><?php endif; ?>
<div class="card"><form method="post" class="stack" style="max-width:none"><?= csrf_field() ?>
<div class="row"><label>Cliente<select name="client_id"><option value="">— Aucune —</option><?php foreach ($clients as $c): ?><option value="<?= (int) $c['id'] ?>" <?= $clientSel === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?> <?= e($c['phone']) ?></option><?php endforeach; ?></select></label>
<label>Date<input type="date" name="inv_date" value="<?= e($inv['inv_date'] ?? date('Y-m-d')) ?>"></label>
<label>Statut<select name="status"><option value="unpaid">Impayée</option><option value="paid" <?= ($inv['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Payée (ajoute l'entrée en comptabilité)</option></select></label></div>
<table id="items"><tr><th>Désignation</th><th style="width:90px">Qté</th><th style="width:160px">Prix unitaire</th><th></th></tr>
<?php foreach ($items as $it): ?><tr><td><input name="label[]" value="<?= e($it['label']) ?>"></td><td><input type="number" min="1" name="qty[]" value="<?= (int) $it['qty'] ?>"></td><td><input type="number" min="0" name="price[]" value="<?= (int) $it['price'] ?>"></td><td><button type="button" class="btn sm danger" onclick="this.closest('tr').remove()">×</button></td></tr><?php endforeach; ?></table>
<div><button type="button" class="btn sm ghost" onclick="var t=document.getElementById('items'),r=t.rows[t.rows.length-1].cloneNode(true);r.querySelectorAll('input').forEach(function(i){i.value=i.name=='qty[]'?1:(i.name=='price[]'?0:'')});t.appendChild(r)">+ Ajouter une ligne</button></div>
<label>Notes<textarea name="notes" rows="2"><?= e($inv['notes'] ?? '') ?></textarea></label>
<div><button class="btn">Enregistrer</button></div></form>
<?php if ($inv): ?><form method="post" onsubmit="return confirm('Supprimer cette facture ?')" style="margin-top:1rem"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><button class="btn danger sm">Supprimer la facture</button></form><?php endif; ?></div>
