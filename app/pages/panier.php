<?php
$title = 'Panier';
$_SESSION['cart'] = is_array($_SESSION['cart'] ?? null) ? $_SESSION['cart'] : [];
$cart = &$_SESSION['cart'];
if (is_post()) {
    csrf_check();
    if (isset($_POST['add'])) {
        $id = (int) $_POST['add'];
        if (one("SELECT id FROM products WHERE id = ? AND active = 1 AND stock > 0", [$id])) $cart[$id] = ($cart[$id] ?? 0) + 1;
        flash('Produit ajouté au panier.'); redirect('panier');
    }
    if (isset($_POST['qty'])) {
        foreach ($_POST['qty'] as $id => $qty) { if ((int) $qty <= 0) unset($cart[(int) $id]); else $cart[(int) $id] = min(20, (int) $qty); }
        flash('Panier mis à jour.'); redirect('panier');
    }
    if (isset($_POST['order'])) {
        $name = post('name'); $phone = post('phone'); $addr = post('address');
        $lines = [];
        foreach ($cart as $id => $qty) { $p = one("SELECT * FROM products WHERE id = ? AND active = 1", [$id]); if ($p) $lines[] = [$p, min($qty, max(0, (int) $p['stock']))]; }
        $lines = array_filter($lines, fn($l) => $l[1] > 0);
        if ($name === '' || $phone === '' || $addr === '' || !$lines) { flash('Complétez vos informations (nom, téléphone, adresse).', 'err'); redirect('panier'); }
        $total = array_sum(array_map(fn($l) => $l[0]['price'] * $l[1], $lines));
        $oid = insert('orders', ['name' => $name, 'phone' => $phone, 'address' => $addr, 'total' => $total]);
        foreach ($lines as [$p, $qty]) {
            insert('order_items', ['order_id' => $oid, 'product_id' => $p['id'], 'label' => $p['name'], 'qty' => $qty, 'price' => $p['price']]);
            q("UPDATE products SET stock = stock - ? WHERE id = ?", [$qty, $p['id']]);
        }
        $cart = [];
        flash("Merci ! Votre commande n°$oid est enregistrée. Nous vous appelons pour la livraison (paiement à la livraison).");
        redirect('boutique');
    }
}
$lines = []; $total = 0;
foreach ($cart as $id => $qty) { $p = one("SELECT * FROM products WHERE id = ? AND active = 1", [$id]); if ($p) { $lines[] = [$p, $qty]; $total += $p['price'] * $qty; } }
?>
<div class="wrap"><h1>Votre panier</h1>
<?php if (!$lines): ?><div class="empty">Votre panier est vide. <a href="<?= url('boutique') ?>">Voir la boutique</a></div><?php else: ?>
<form method="post"><?= csrf_field() ?>
<table class="cart"><tr><th>Produit</th><th>Prix</th><th>Qté</th><th>Total</th></tr>
<?php foreach ($lines as [$p, $qty]): ?><tr><td><?= e($p['name']) ?></td><td><?= money($p['price']) ?></td>
<td><input style="width:80px" type="number" min="0" max="20" name="qty[<?= (int) $p['id'] ?>]" value="<?= (int) $qty ?>"></td><td><?= money($p['price'] * $qty) ?></td></tr><?php endforeach; ?>
<tr><td colspan="3"><strong>Total</strong></td><td><strong><?= money($total) ?></strong></td></tr></table>
<p><button class="btn ghost">Mettre à jour</button></p></form>
<h2>Finaliser la commande</h2><p class="notice">Paiement à la livraison.</p>
<form method="post" class="form"><?= csrf_field() ?><input type="hidden" name="order" value="1">
<label>Votre nom<input name="name" required></label><label>Téléphone<input name="phone" required></label>
<label>Adresse de livraison<textarea name="address" rows="3" required></textarea></label>
<button class="btn">Confirmer la commande</button></form>
<?php endif; ?></div>
