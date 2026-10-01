<?php
$name = setting('salon_name', 'Min Afro Queen');
$logo = setting('logo');
$cartCount = array_sum($_SESSION['cart'] ?? []);
$nav = ['' => 'Accueil', 'services' => 'Services', 'galerie' => 'Galerie', 'journal' => 'Journal', 'boutique' => 'Boutique', 'reservation' => 'Réservation', 'contact' => 'Contact'];
$cur = $seg[0] ?? '';
$fl = flash();
?><!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title ? "$title | $name" : "$name | Salon de locks à Dakar — Hommes & Femmes") ?></title>
<meta name="description" content="<?= e($name) ?> — salon de locks à Dakar pour hommes et femmes : sisterlocks, retwist, extensions, entretien.">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/css/site.css') ?>?v=1">
</head>
<body>
<div class="topbar">L'ART DES LOCKS À DAKAR <span>✦</span> EXPERTISE MASCULINE &amp; FÉMININE</div>
<header class="header">
  <a class="brand" href="<?= url() ?>" aria-label="<?= e($name) ?>">
    <?php if ($logo): ?><img src="<?= img($logo) ?>" alt="" height="44"><?php endif; ?>
    <span><?= e($name) ?></span>
  </a>
  <button class="burger" aria-label="Menu" onclick="document.body.classList.toggle('menu-open')">☰</button>
  <nav class="nav">
    <?php foreach ($nav as $k => $label): ?><a href="<?= url($k) ?>" class="<?= $cur === $k ? 'on' : '' ?>"><?= e($label) ?></a><?php endforeach; ?>
    <a href="<?= url('panier') ?>" class="cart">Panier<?= $cartCount ? " ($cartCount)" : '' ?></a>
    <a class="btn small" href="<?= url('reservation') ?>">Prendre rendez-vous</a>
  </nav>
</header>
<?php if ($fl): ?><div class="flash <?= $fl[1] ?>"><?= e($fl[0]) ?></div><?php endif; ?>
<main><?= $content ?></main>
<footer class="footer">
  <div>
    <strong class="serif"><?= e($name) ?></strong>
    <p><?= e(setting('address')) ?><br><?= e(setting('hours')) ?></p>
  </div>
  <div>
    <a href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">WhatsApp</a>
    <?php if (setting('instagram')): ?><a href="<?= e(setting('instagram')) ?>" target="_blank" rel="noopener">Instagram</a><?php endif; ?>
    <?php if (setting('facebook')): ?><a href="<?= e(setting('facebook')) ?>" target="_blank" rel="noopener">Facebook</a><?php endif; ?>
    <?php if (setting('tiktok')): ?><a href="<?= e(setting('tiktok')) ?>" target="_blank" rel="noopener">TikTok</a><?php endif; ?>
  </div>
  <small>© <?= date('Y') ?> <?= e($name) ?></small>
</footer>
<a class="wa-float" href="<?= e(wa_link('Bonjour ' . $name . ', je souhaite avoir des renseignements.')) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">WhatsApp</a>
</body>
</html>
