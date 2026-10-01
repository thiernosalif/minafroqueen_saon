<?php
$title = 'Services';
$cat = ($_GET['c'] ?? 'femmes') === 'hommes' ? 'hommes' : 'femmes';
$services = all("SELECT * FROM services WHERE active = 1 AND category = ? ORDER BY sort, id", [$cat]);
?>
<div class="wrap">
  <span class="eyebrow">La carte des soins</span><h1>Des soins à la hauteur de <em>votre couronne.</em></h1>
  <p class="notice">Un accompagnement sur mesure, du premier départ à chaque retwist. Les tarifs sont communiqués sur WhatsApp, après discussion de votre projet.</p>
  <div class="tabs"><a href="?c=femmes" class="<?= $cat === 'femmes' ? 'on' : '' ?>">Femmes</a><a href="?c=hommes" class="<?= $cat === 'hommes' ? 'on' : '' ?>">Hommes</a></div>
  <div class="grid">
  <?php foreach ($services as $s): ?>
    <article class="card"><h3><?= e($s['name']) ?></h3>
      <span class="price"><?= $s['price'] > 0 ? 'À partir de ' . money($s['price']) : 'Sur devis' ?></span>
      <p><?= e($s['description']) ?></p><span class="notice"><?= e($s['duration']) ?></span>
      <div class="actions">
        <a class="btn small ghost" style="color:var(--violet-d)!important" target="_blank" rel="noopener" href="<?= e(wa_link('Bonjour, je souhaite connaître le tarif pour : ' . $s['name'])) ?>">Demander le tarif</a>
        <a class="btn small" href="<?= url('reservation?s=' . $s['id']) ?>">Réserver</a>
      </div></article>
  <?php endforeach; ?>
  <?php if (!$services): ?><div class="empty">Aucun service pour le moment.</div><?php endif; ?>
  </div>
</div>
