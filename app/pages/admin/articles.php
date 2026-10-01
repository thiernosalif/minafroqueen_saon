<?php
$title = 'Journal';
$arg = $params[0] ?? '';
if (is_post()) {
    csrf_check();
    $id = (int) ($arg === 'new' ? 0 : $arg);
    if (post('action') === 'delete' && $id) { $p = one('SELECT * FROM posts WHERE id = ?', [$id]); delete_upload($p['cover'] ?? null); delete_upload($p['video'] ?? null); q('DELETE FROM posts WHERE id = ?', [$id]); flash('Article supprimé.'); redirect('admin/articles'); }
    $old = $id ? one('SELECT * FROM posts WHERE id = ?', [$id]) : null;
    $slug = slugify(post('title'));
    if (val('SELECT COUNT(*) FROM posts WHERE slug = ? AND id <> ?', [$slug, $id])) $slug .= '-' . time();
    $d = ['title' => post('title'), 'excerpt' => post('excerpt'), 'body' => post('body'), 'video_url' => post('video_url'), 'published' => isset($_POST['published']) ? 1 : 0];
    if ($d['title'] === '') { flash('Le titre est obligatoire.', 'err'); redirect('admin/articles/' . ($id ?: 'new')); }
    if (!$old) $d['slug'] = $slug;
    if ($c = upload('cover')) { delete_upload($old['cover'] ?? null); $d['cover'] = $c; }
    if ($v = upload('video', 'video')) { delete_upload($old['video'] ?? null); $d['video'] = $v; }
    if (isset($_POST['rm_video']) && $old) { delete_upload($old['video']); $d['video'] = null; }
    if ($old) update('posts', $id, $d); else $id = insert('posts', $d);
    flash('Article enregistré.'); redirect("admin/articles/$id");
}
if ($arg === '') { $rows = all('SELECT * FROM posts ORDER BY created DESC'); ?>
<h1>Journal</h1><div class="bar"><a class="btn" href="<?= url('admin/articles/new') ?>">Nouvel article</a></div>
<div class="table-wrap"><table><tr><th></th><th>Titre</th><th>Date</th><th>Statut</th></tr>
<?php foreach ($rows as $p): ?><tr><td><?php if ($p['cover']): ?><img class="thumb" src="<?= e(img($p['cover'])) ?>" alt=""><?php endif; ?></td><td><a href="<?= url('admin/articles/' . (int) $p['id']) ?>"><?= e($p['title']) ?></a></td><td><?= dfr($p['created']) ?></td><td><span class="pill <?= $p['published'] ? 'approved' : 'pending' ?>"><?= $p['published'] ? 'Publié' : 'Brouillon' ?></span></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="4" class="muted">Aucun article.</td></tr><?php endif; ?></table></div>
<?php return; }
$p = $arg === 'new' ? null : one('SELECT * FROM posts WHERE id = ?', [(int) $arg]);
if ($arg !== 'new' && !$p) { echo '<h1>Introuvable</h1>'; return; }
?>
<h1><?= $p ? 'Modifier l\'article' : 'Nouvel article' ?></h1><p><a href="<?= url('admin/articles') ?>">← Retour</a></p>
<div class="card"><form method="post" enctype="multipart/form-data" class="stack"><?= csrf_field() ?>
<label>Titre<input name="title" value="<?= e($p['title'] ?? '') ?>" required></label>
<label>Résumé (affiché dans la liste)<textarea name="excerpt" rows="2"><?= e($p['excerpt'] ?? '') ?></textarea></label>
<label>Contenu<textarea name="body" rows="10"><?= e($p['body'] ?? '') ?></textarea></label>
<label>Photo de couverture<input type="file" name="cover" accept="image/*"></label>
<?php if (!empty($p['cover'])): ?><img class="thumb" style="width:120px;height:120px" src="<?= e(img($p['cover'])) ?>" alt=""><?php endif; ?>
<label>Vidéo (fichier mp4 — limite serveur : <?= e(ini_get('upload_max_filesize')) ?>)<input type="file" name="video" accept="video/mp4,video/webm,video/quicktime"></label>
<?php if (!empty($p['video'])): ?><label><span><input type="checkbox" name="rm_video"> Supprimer la vidéo actuelle (<?= e($p['video']) ?>)</span></label><?php endif; ?>
<label>…ou lien YouTube / Vimeo<input name="video_url" value="<?= e($p['video_url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=…"></label>
<label><span><input type="checkbox" name="published" <?= ($p['published'] ?? 1) ? 'checked' : '' ?>> Publié sur le site</span></label>
<div><button class="btn">Enregistrer</button></div></form>
<?php if ($p): ?><form method="post" onsubmit="return confirm('Supprimer cet article ?')" style="margin-top:1rem"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><button class="btn sm danger">Supprimer</button></form><?php endif; ?></div>
