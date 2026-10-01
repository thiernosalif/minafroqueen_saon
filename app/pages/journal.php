<?php
if (!empty($params[0])):
    $post = one("SELECT * FROM posts WHERE slug = ? AND published = 1", [$params[0]]);
    if (!$post) { http_response_code(404); echo '<div class="wrap"><h1>Article introuvable</h1></div>'; return; }
    $title = $post['title'];
?>
<div class="wrap"><article class="article">
  <span class="eyebrow"><?= dfr($post['created']) ?></span><h1><?= e($post['title']) ?></h1>
  <?php if ($post['cover']): ?><img class="cover" src="<?= e(img($post['cover'])) ?>" alt=""><?php endif; ?>
  <div class="body"><?= e($post['body']) ?></div>
  <?php if ($post['video']): ?><p><video class="video" controls preload="metadata" src="<?= e(img($post['video'])) ?>"></video></p>
  <?php elseif (embed_url($post['video_url'])): ?><p><iframe class="video" allowfullscreen src="<?= e(embed_url($post['video_url'])) ?>"></iframe></p><?php endif; ?>
  <p><a href="<?= url('journal') ?>">← Retour au journal</a></p>
</article></div>
<?php return; endif;
$title = 'Journal';
$posts = all("SELECT * FROM posts WHERE published = 1 ORDER BY created DESC");
?>
<div class="wrap">
  <span class="eyebrow">Le journal du salon</span><h1>Histoires, looks et <em>inspirations.</em></h1>
  <?php if (!$posts): ?><div class="empty">Les premières publications arrivent bientôt.</div><?php else: ?>
  <div class="grid"><?php foreach ($posts as $p): ?>
    <a class="card" style="text-decoration:none" href="<?= url('journal/' . $p['slug']) ?>">
      <?php if ($p['cover']): ?><img class="thumb" src="<?= e(img($p['cover'])) ?>" alt=""><?php endif; ?>
      <span class="tag"><?= dfr($p['created']) ?></span><h3><?= e($p['title']) ?></h3><p><?= e($p['excerpt']) ?></p></a>
  <?php endforeach; ?></div><?php endif; ?>
</div>
