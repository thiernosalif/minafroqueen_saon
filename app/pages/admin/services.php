<?php
$title = 'Services';
if (is_post()) {
    csrf_check();
    $a = post('action'); $id = (int) post('id');
    $d = ['name' => post('name'), 'category' => post('category') === 'hommes' ? 'hommes' : 'femmes', 'description' => post('description'),
          'duration' => post('duration'), 'price' => (int) post('price'), 'sort' => (int) post('sort'), 'active' => isset($_POST['active']) ? 1 : 0];
    if ($a === 'delete') { q('DELETE FROM services WHERE id = ?', [$id]); flash('Service supprimé.'); }
    elseif ($d['name'] === '') flash('Le nom est obligatoire.', 'err');
    elseif ($id) { update('services', $id, $d); flash('Service mis à jour.'); }
    else { insert('services', $d); flash('Service ajouté.'); }
    redirect('admin/services');
}
$rows = all('SELECT * FROM services ORDER BY category DESC, sort, id');
$edit = isset($_GET['edit']) ? one('SELECT * FROM services WHERE id = ?', [(int) $_GET['edit']]) : null;
?>
<h1>Services</h1>
<div class="card"><h2 style="margin-top:0"><?= $edit ? 'Modifier' : 'Ajouter' ?> un service</h2>
<form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
<div class="row"><label>Nom<input name="name" value="<?= e($edit['name'] ?? '') ?>" required></label>
<label>Catégorie<select name="category"><option value="femmes">Femmes</option><option value="hommes" <?= ($edit['category'] ?? '') === 'hommes' ? 'selected' : '' ?>>Hommes</option></select></label></div>
<label>Description<textarea name="description" rows="2"><?= e($edit['description'] ?? '') ?></textarea></label>
<div class="row"><label>Durée (texte)<input name="duration" value="<?= e($edit['duration'] ?? '') ?>" placeholder="3 à 5 h estimées"></label>
<label>Prix à partir de (FCFA, 0 = sur devis)<input type="number" min="0" name="price" value="<?= (int) ($edit['price'] ?? 0) ?>"></label>
<label>Ordre<input type="number" name="sort" value="<?= (int) ($edit['sort'] ?? 0) ?>"></label></div>
<label><span><input type="checkbox" name="active" <?= ($edit['active'] ?? 1) ? 'checked' : '' ?>> Visible sur le site</span></label>
<div><button class="btn">Enregistrer</button> <?php if ($edit): ?><a class="btn ghost" href="<?= url('admin/services') ?>">Annuler</a><?php endif; ?></div></form></div>
<h2>Liste</h2><div class="table-wrap"><table><tr><th>Nom</th><th>Catégorie</th><th>Prix</th><th>Durée</th><th>Visible</th><th></th></tr>
<?php foreach ($rows as $s): ?><tr><td><?= e($s['name']) ?></td><td><?= e($s['category']) ?></td><td><?= $s['price'] ? money($s['price']) : 'Sur devis' ?></td><td><?= e($s['duration']) ?></td><td><?= $s['active'] ? 'Oui' : 'Non' ?></td>
<td><a class="btn sm ghost" href="?edit=<?= (int) $s['id'] ?>">Modifier</a>
<form method="post" class="inline" onsubmit="return confirm('Supprimer ce service ?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><button class="btn sm danger">Supprimer</button></form></td></tr><?php endforeach; ?></table></div>
