<?php
$title = 'Galerie';
if (is_post()) {
    csrf_check();
    if (post('action') === 'delete') { $m = one('SELECT * FROM media WHERE id = ?', [(int) post('id')]); if ($m) { delete_upload($m['file']); q('DELETE FROM media WHERE id = ?', [$m['id']]); } flash('Supprimé.'); redirect('admin/galerie'); }
    $cat = in_array(post('category'), ['femmes', 'hommes', 'avant-apres'], true) ? post('category') : 'femmes';
    $n = 0;
    // photos multiples
    if (!empty($_FILES['photos']['name'][0])) {
        foreach ($_FILES['photos']['name'] as $i => $_) {
            $_FILES['one'] = ['name' => $_FILES['photos']['name'][$i], 'type' => $_FILES['photos']['type'][$i], 'tmp_name' => $_FILES['photos']['tmp_name'][$i], 'error' => $_FILES['photos']['error'][$i], 'size' => $_FILES['photos']['size'][$i]];
            if ($f = upload('one')) { insert('media', ['type' => 'image', 'file' => $f, 'caption' => post('caption'), 'category' => $cat]); $n++; }
        }
    }
    if ($f = upload('video', 'video')) { insert('media', ['type' => 'video', 'file' => $f, 'caption' => post('caption'), 'category' => $cat]); $n++; }
    if (post('video_url') !== '') { if (embed_url(post('video_url'))) { insert('media', ['type' => 'video', 'video_url' => post('video_url'), 'caption' => post('caption'), 'category' => $cat]); $n++; } else flash('Lien vidéo non reconnu (YouTube ou Vimeo).', 'err'); }
    if ($n) flash("$n élément(s) ajouté(s).");
    redirect('admin/galerie');
}
$items = all('SELECT * FROM media ORDER BY created DESC');
?>
<h1>Galerie photos &amp; vidéos</h1>
<div class="card"><form method="post" enctype="multipart/form-data" class="stack"><?= csrf_field() ?>
<div class="row"><label>Catégorie<select name="category"><option value="femmes">Femmes</option><option value="hommes">Hommes</option><option value="avant-apres">Avant-après</option></select></label>
<label>Légende (optionnelle)<input name="caption"></label></div>
<label>Photos (plusieurs possibles)<input type="file" name="photos[]" multiple accept="image/*"></label>
<label>Vidéo (mp4, limite serveur : <?= e(ini_get('upload_max_filesize')) ?>)<input type="file" name="video" accept="video/mp4,video/webm,video/quicktime"></label>
<label>…ou lien YouTube / Vimeo<input name="video_url" placeholder="https://www.youtube.com/watch?v=…"></label>
<div><button class="btn">Ajouter à la galerie</button></div></form></div>
<h2><?= count($items) ?> élément(s)</h2>
<div class="media-grid"><?php foreach ($items as $m): ?><div class="card">
<?php if ($m['type'] === 'video' && $m['file']): ?><video src="<?= e(img($m['file'])) ?>" muted></video>
<?php elseif ($m['type'] === 'video'): ?><div class="thumb" style="width:100%;height:auto;aspect-ratio:1;display:grid;place-items:center">▶ <?= e($m['video_url']) ?></div>
<?php else: ?><img src="<?= e(img($m['file'])) ?>" alt=""><?php endif; ?>
<p class="muted"><?= e($m['category']) ?> · <?= e($m['caption']) ?></p>
<form method="post" onsubmit="return confirm('Supprimer ?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>"><button class="btn sm danger">Supprimer</button></form></div><?php endforeach; ?></div>
