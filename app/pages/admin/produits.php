<?php
$title = 'Produits';
if (is_post()) {
    csrf_check();
    $id = (int) post('id');
    if (post('action') === 'delete') { $p = one('SELECT * FROM products WHERE id = ?', [$id]); if ($p) { delete_upload($p['image']); q('DELETE FROM products WHERE id = ?', [$id]); } flash('Produit supprimé.'); redirect('admin/produits'); }
    $old = $id ? one('SELECT * FROM products WHERE id = ?', [$id]) : null;
    $d = ['name' => post('name'), 'description' => post('description'), 'price' => (int) post('price'), 'stock' => (int) post('stock'), 'active' => isset($_POST['active']) ? 1 : 0];
    if ($d['name'] === '') { flash('Le nom est obligatoire.', 'err'); redirect('admin/produits'); }
    if ($f = upload('image')) { delete_upload($old['image'] ?? null); $d['image'] = $f; }
    if ($old) update('products', $id, $d); else insert('products', $d);
    flash('Produit enregistré.'); redirect('admin/produits');
}
$rows = all('SELECT * FROM products ORDER BY created DESC');
$e = isset($_GET['edit']) ? one('SELECT * FROM products WHERE id = ?', [(int) $_GET['edit']]) : null;
?>
<h1>Produits</h1>
<div class="card"><h2 style="margin-top:0"><?= $e ? 'Modifier' : 'Ajouter' ?> un produit</h2><form method="post" enctype="multipart/form-data" class="stack"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) ($e['id'] ?? 0) ?>">
<div class="row"><label>Nom<input name="name" value="<?= e($e['name'] ?? '') ?>" required></label><label>Prix (FCFA)<input type="number" min="0" name="price" value="<?= (int) ($e['price'] ?? 0) ?>"></label><label>Stock<input type="number" min="0" name="stock" value="<?= (int) ($e['stock'] ?? 0) ?>"></label></div>
<label>Description<textarea name="description" rows="2"><?= e($e['description'] ?? '') ?></textarea></label>
<label>Photo<input type="file" name="image" accept="image/*"></label>
<label><span><input type="checkbox" name="active" <?= ($e['active'] ?? 1) ? 'checked' : '' ?>> Visible dans la boutique</span></label>
<div><button class="btn">Enregistrer</button> <?php if ($e): ?><a class="btn ghost" href="<?= url('admin/produits') ?>">Annuler</a><?php endif; ?></div></form></div>
<h2>Liste</h2><div class="table-wrap"><table><tr><th></th><th>Produit</th><th>Prix</th><th>Stock</th><th>Visible</th><th></th></tr>
<?php foreach ($rows as $p): ?><tr><td><?php if ($p['image']): ?><img class="thumb" src="<?= e(img($p['image'])) ?>" alt=""><?php endif; ?></td><td><?= e($p['name']) ?></td><td><?= money($p['price']) ?></td><td><?= (int) $p['stock'] ?></td><td><?= $p['active'] ? 'Oui' : 'Non' ?></td>
<td><a class="btn sm ghost" href="?edit=<?= (int) $p['id'] ?>">Modifier</a> <form method="post" class="inline" onsubmit="return confirm('Supprimer ?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><button class="btn sm danger">×</button></form></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="6" class="muted">Aucun produit.</td></tr><?php endif; ?></table></div>
