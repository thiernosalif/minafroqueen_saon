<?php
$title = 'Boutique';
$products = all("SELECT * FROM products WHERE active = 1 ORDER BY created DESC");
$n = array_sum($_SESSION['cart'] ?? []);
?>
<div class="wrap">
  <div class="section-head"><div><span class="eyebrow">Boutique royale</span><h1>Prenez soin de votre <em>couronne.</em></h1>
  <p class="notice">Des essentiels pour prolonger le soin de vos locks à la maison. Paiement à la livraison.</p></div>
  <a class="btn ghost" href="<?= url('panier') ?>">Panier (<?= $n ?>)</a></div>
  <?php if (!$products): ?><div class="empty">La boutique se prépare. Les produits du salon seront bientôt disponibles ici.</div><?php else: ?>
  <div class="grid"><?php foreach ($products as $p) include __DIR__ . '/../views/product_card.php'; ?></div><?php endif; ?>
</div>
