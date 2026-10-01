<?php
$title = 'Galerie';
$cat = $_GET['c'] ?? 'tout';
$tabs = ['tout' => 'Tout', 'femmes' => 'Femmes', 'hommes' => 'Hommes', 'avant-apres' => 'Avant-après'];
if (!isset($tabs[$cat])) $cat = 'tout';
$items = $cat === 'tout' ? all("SELECT * FROM media ORDER BY created DESC") : all("SELECT * FROM media WHERE category = ? ORDER BY created DESC", [$cat]);
?>
<div class="wrap">
  <span class="eyebrow">Notre univers</span><h1>La beauté sous <em>toutes ses formes.</em></h1>
  <div class="tabs"><?php foreach ($tabs as $k => $l): ?><a class="<?= $cat === $k ? 'on' : '' ?>" href="?c=<?= $k ?>"><?= $l ?></a><?php endforeach; ?></div>
  <?php if (!$items): ?><div class="empty">Les réalisations seront bientôt publiées ici.</div><?php else: ?>
  <div class="gallery"><?php foreach ($items as $m): ?>
    <figure>
      <?php if ($m['type'] === 'video' && $m['file']): ?><video controls preload="metadata" src="<?= e(img($m['file'])) ?>"></video>
      <?php elseif ($m['type'] === 'video' && embed_url($m['video_url'])): ?><iframe loading="lazy" allowfullscreen src="<?= e(embed_url($m['video_url'])) ?>"></iframe>
      <?php else: ?><img loading="lazy" src="<?= e(img($m['file'])) ?>" alt="<?= e($m['caption']) ?>"><?php endif; ?>
      <?php if ($m['caption']): ?><figcaption><?= e($m['caption']) ?></figcaption><?php endif; ?>
    </figure>
  <?php endforeach; ?></div><?php endif; ?>
</div>
