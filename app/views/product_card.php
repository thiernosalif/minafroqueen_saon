<article class="card">
  <?php if ($p['image']): ?><img class="thumb" src="<?= e(img($p['image'])) ?>" alt="<?= e($p['name']) ?>"><?php endif; ?>
  <h3><?= e($p['name']) ?></h3><p><?= e($p['description']) ?></p><span class="price"><?= money($p['price']) ?></span>
  <div class="actions">
  <?php if ($p['stock'] > 0): ?>
    <form method="post" action="<?= url('panier') ?>"><?= csrf_field() ?><input type="hidden" name="add" value="<?= (int) $p['id'] ?>"><button class="btn small">Ajouter au panier</button></form>
  <?php else: ?><span class="notice">Épuisé</span><?php endif; ?>
  </div>
</article>
