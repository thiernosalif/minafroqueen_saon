<?php
$services = all("SELECT * FROM services WHERE active = 1 ORDER BY sort, id LIMIT 6");
$posts = all("SELECT * FROM posts WHERE published = 1 ORDER BY created DESC LIMIT 3");
$products = all("SELECT * FROM products WHERE active = 1 ORDER BY created DESC LIMIT 3");
$reviews = all("SELECT * FROM reviews WHERE status = 'approved' ORDER BY created DESC LIMIT 3");
$hero = setting('hero_image');
?>
<section class="hero">
  <div class="hero-text">
    <span class="eyebrow"><?= e(strtoupper(setting('city', 'Dakar, Sénégal'))) ?> · LE SOIN DES LOCKS</span>
    <h1><?= e(setting('hero_title', 'Vos locks.')) ?><br><em><?= e(setting('hero_subtitle', 'Votre couronne.')) ?></em></h1>
    <p><?= e(setting('hero_text', 'Chaque texture raconte une histoire. Nous prenons soin de la vôtre avec précision, douceur et fierté.')) ?></p>
    <div class="btns">
      <a class="btn" href="<?= url('reservation') ?>">Prendre rendez-vous</a>
      <a class="btn ghost" target="_blank" rel="noopener" href="<?= e(wa_link('Bonjour, je souhaite avoir des renseignements sur vos services.')) ?>">Écrire sur WhatsApp</a>
    </div>
  </div>
  <div class="hero-img" <?= $hero ? 'style="background-image:url(' . e(img($hero)) . ')"' : '' ?>></div>
</section>

<section class="wrap">
  <div class="section-head"><div><span class="eyebrow">Notre savoir-faire</span><h2>L'art de sublimer<br><em>chaque lock.</em></h2></div><a class="btn ghost" href="<?= url('services') ?>">Tous les services</a></div>
  <div class="grid">
  <?php foreach ($services as $s): ?>
    <article class="card">
      <span class="tag"><?= e($s['category']) ?></span><h3><?= e($s['name']) ?></h3><p><?= e($s['description']) ?></p>
      <div class="actions"><a class="btn small ghost" style="color:var(--violet-d)!important" target="_blank" rel="noopener" href="<?= e(wa_link('Bonjour, je souhaite connaître le tarif pour : ' . $s['name'])) ?>">Devis sur WhatsApp</a></div>
    </article>
  <?php endforeach; ?>
  </div>
  <p class="notice">Tarifs communiqués sur WhatsApp, après discussion de votre projet.</p>
</section>

<section class="band"><div class="wrap split">
  <div><span class="eyebrow">Une beauté qui vous ressemble</span><h2>Votre texture est <em>une œuvre d'art.</em></h2>
  <p><?= e(setting('about_text', "Du premier départ de locks à l'entretien régulier, chaque geste est pensé pour respecter votre chevelure et révéler votre personnalité. Femmes et hommes, votre couronne est entre de bonnes mains.")) ?></p>
  <a class="btn" href="<?= url('galerie') ?>">Découvrir la galerie</a></div>
  <?php $about = setting('about_image'); if ($about): ?><img src="<?= e(img($about)) ?>" alt=""><?php endif; ?>
</div></section>

<section class="wrap">
  <div class="section-head"><div><span class="eyebrow">Le journal</span><h2>Dernières publications</h2></div><a href="<?= url('journal') ?>">Voir tout →</a></div>
  <?php if (!$posts): ?><div class="empty">Les nouveautés du salon seront bientôt partagées ici.</div><?php else: ?>
  <div class="grid"><?php foreach ($posts as $p): ?>
    <a class="card" style="text-decoration:none" href="<?= url('journal/' . $p['slug']) ?>">
      <?php if ($p['cover']): ?><img class="thumb" src="<?= e(img($p['cover'])) ?>" alt=""><?php endif; ?>
      <span class="tag"><?= dfr($p['created']) ?></span><h3><?= e($p['title']) ?></h3><p><?= e($p['excerpt']) ?></p></a>
  <?php endforeach; ?></div><?php endif; ?>
</section>

<?php if ($products): ?>
<section class="band"><div class="wrap">
  <div class="section-head"><div><span class="eyebrow">Boutique royale</span><h2>Le rituel continue <em>chez vous.</em></h2></div><a href="<?= url('boutique') ?>">La boutique →</a></div>
  <div class="grid"><?php foreach ($products as $p) include __DIR__ . '/../views/product_card.php'; ?></div>
</div></section>
<?php endif; ?>

<section class="wrap">
  <span class="eyebrow">Avis clients</span><h2>Vos histoires donnent vie à notre salon.</h2>
  <?php if (!$reviews): ?><div class="empty">Les premiers avis seront partagés ici. <a href="<?= url('avis') ?>">Racontez-nous votre expérience ↗</a></div>
  <?php else: ?><div class="grid"><?php foreach ($reviews as $r): ?>
    <div class="card"><span class="stars"><?= str_repeat('★', (int) $r['rating']) ?></span><p><?= e($r['body']) ?></p><strong><?= e($r['name']) ?></strong></div>
  <?php endforeach; ?></div><p><a href="<?= url('avis') ?>">Laisser un avis ↗</a></p><?php endif; ?>
</section>
