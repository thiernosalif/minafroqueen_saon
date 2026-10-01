<?php
$menu = ['' => 'Tableau de bord', 'rdv' => 'Rendez-vous', 'clients' => 'Clientes', 'factures' => 'Factures', 'compta' => 'Comptabilité',
  'services' => 'Services', 'articles' => 'Journal', 'galerie' => 'Galerie', 'produits' => 'Produits', 'commandes' => 'Commandes', 'avis' => 'Avis', 'reglages' => 'Réglages'];
$badge = ['rdv' => (int) val("SELECT COUNT(*) FROM appointments WHERE status IN ('pending','message')"),
          'commandes' => (int) val("SELECT COUNT(*) FROM orders WHERE status = 'new'"),
          'avis' => (int) val("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")];
$cur = $seg[1] ?? ''; $fl = flash();
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex"><title><?= e($title ?: 'Administration') ?> — Admin</title>
<link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>?v=1"></head>
<body>
<aside class="side">
  <a class="logo" href="<?= url('admin') ?>">Min Afro Queen<small>Administration</small></a>
  <nav><?php foreach ($menu as $k => $l): ?>
    <a href="<?= url('admin/' . $k) ?>" class="<?= $cur === $k ? 'on' : '' ?>"><?= e($l) ?><?php if (!empty($badge[$k])): ?><b><?= $badge[$k] ?></b><?php endif; ?></a>
  <?php endforeach; ?></nav>
  <div class="side-foot"><a href="<?= url() ?>" target="_blank">Voir le site ↗</a><a href="<?= url('admin/logout') ?>">Déconnexion</a></div>
</aside>
<main class="main">
  <?php if ($fl): ?><div class="flash <?= $fl[1] ?>"><?= e($fl[0]) ?></div><?php endif; ?>
  <?= $content ?>
</main>
</body></html>
