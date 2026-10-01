<?php
$title = 'Comptabilité';
$month = preg_match('/^\d{4}-\d{2}$/', $_GET['m'] ?? '') ? $_GET['m'] : date('Y-m');
if (is_post()) {
    csrf_check();
    if (post('action') === 'delete') { q('DELETE FROM transactions WHERE id = ? AND invoice_id IS NULL', [(int) post('id')]); flash('Écriture supprimée.'); }
    else {
        $amt = (int) post('amount');
        if ($amt <= 0 || post('label') === '') flash('Libellé et montant obligatoires.', 'err');
        else { insert('transactions', ['tr_date' => post('tr_date') ?: date('Y-m-d'), 'type' => post('type') === 'in' ? 'in' : 'out', 'label' => post('label'), 'category' => post('category'), 'amount' => $amt]); flash('Écriture ajoutée.'); }
    }
    redirect('admin/compta?m=' . $month);
}
$rows = all("SELECT * FROM transactions WHERE tr_date LIKE ? ORDER BY tr_date DESC, id DESC", [$month . '%']);
if (isset($_GET['export'])) {
    $raw = true; header('Content-Type: text/csv; charset=utf-8'); header("Content-Disposition: attachment; filename=comptabilite-$month.csv");
    echo "\xEF\xBB\xBF" . "Date;Type;Libellé;Catégorie;Montant\n";
    foreach ($rows as $r) echo implode(';', [dfr($r['tr_date']), $r['type'] === 'in' ? 'Entrée' : 'Dépense', '"' . str_replace('"', '""', $r['label']) . '"', $r['category'], $r['amount']]) . "\n";
    return;
}
$in = array_sum(array_map(fn($r) => $r['type'] === 'in' ? $r['amount'] : 0, $rows));
$out = array_sum(array_map(fn($r) => $r['type'] === 'out' ? $r['amount'] : 0, $rows));
$cats = [];
foreach ($rows as $r) if ($r['type'] === 'out') $cats[$r['category'] ?: 'Autre'] = ($cats[$r['category'] ?: 'Autre'] ?? 0) + $r['amount'];
arsort($cats);
?>
<h1>Comptabilité</h1>
<form class="bar"><label style="display:flex;gap:.5rem;align-items:center">Mois <input type="month" name="m" value="<?= e($month) ?>" style="width:auto" onchange="this.form.submit()"></label>
<a class="btn ghost" href="?m=<?= e($month) ?>&export=1">Exporter (Excel/CSV)</a></form>
<div class="cards"><div class="card stat"><b class="ok"><?= money($in) ?></b><span>Entrées</span></div><div class="card stat"><b class="err"><?= money($out) ?></b><span>Dépenses</span></div><div class="card stat"><b><?= money($in - $out) ?></b><span>Résultat</span></div></div>
<?php if ($cats): ?><p class="muted">Dépenses par catégorie : <?php foreach ($cats as $c => $a): ?><span class="pill"><?= e($c) ?> : <?= money($a) ?></span> <?php endforeach; ?></p><?php endif; ?>
<div class="card"><h2 style="margin-top:0">Ajouter une écriture</h2><form method="post" class="stack" style="max-width:none"><?= csrf_field() ?>
<div class="row"><label>Type<select name="type"><option value="out">Dépense</option><option value="in">Entrée (hors facture)</option></select></label>
<label>Date<input type="date" name="tr_date" value="<?= date('Y-m-d') ?>"></label><label>Libellé<input name="label" required></label>
<label>Catégorie<input name="category" list="cats" placeholder="Produits, Loyer, Salaire…"><datalist id="cats"><option>Produits</option><option>Loyer</option><option>Salaires</option><option>Électricité / eau</option><option>Transport</option><option>Publicité</option></datalist></label>
<label>Montant (FCFA)<input type="number" min="1" name="amount" required></label></div><div><button class="btn">Ajouter</button></div></form></div>
<h2>Écritures de <?= e($month) ?></h2><div class="table-wrap"><table><tr><th>Date</th><th>Libellé</th><th>Catégorie</th><th class="tot">Montant</th><th></th></tr>
<?php foreach ($rows as $r): ?><tr><td><?= dfr($r['tr_date']) ?></td><td><?= e($r['label']) ?></td><td><?= e($r['category']) ?></td>
<td class="tot <?= $r['type'] === 'in' ? 'ok' : 'err' ?>"><?= $r['type'] === 'in' ? '+' : '−' ?> <?= money($r['amount']) ?></td>
<td><?php if (!$r['invoice_id']): ?><form method="post" class="inline" onsubmit="return confirm('Supprimer ?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="btn sm danger">×</button></form><?php else: ?><span class="muted">facture</span><?php endif; ?></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5" class="muted">Aucune écriture ce mois-ci.</td></tr><?php endif; ?></table></div>
