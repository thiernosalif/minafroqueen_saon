<?php
$title = 'Clientes';
$id = (int) ($params[0] ?? 0);
if (is_post()) {
    csrf_check();
    $d = ['name' => post('name'), 'phone' => post('phone'), 'email' => post('email'), 'notes' => post('notes')];
    if (post('action') === 'delete') { q('DELETE FROM clients WHERE id = ?', [$id]); flash('Cliente supprimée.'); redirect('admin/clients'); }
    if ($d['name'] === '') { flash('Le nom est obligatoire.', 'err'); redirect('admin/clients' . ($id ? "/$id" : '')); }
    if ($id) { update('clients', $id, $d); flash('Fiche mise à jour.'); redirect("admin/clients/$id"); }
    $id = insert('clients', $d); flash('Cliente ajoutée.'); redirect("admin/clients/$id");
}
if ($id): $c = one('SELECT * FROM clients WHERE id = ?', [$id]);
    if (!$c) { echo '<h1>Cliente introuvable</h1>'; return; }
    $title = $c['name'];
    $rdvs = all("SELECT a.*, s.name AS service FROM appointments a LEFT JOIN services s ON s.id = a.service_id WHERE a.client_id = ? AND a.status <> 'message' ORDER BY a.rdv_date DESC", [$id]);
    $invs = all("SELECT * FROM invoices WHERE client_id = ? ORDER BY inv_date DESC", [$id]);
    $spent = (int) val("SELECT COALESCE(SUM(total),0) FROM invoices WHERE client_id = ? AND status = 'paid'", [$id]);
?>
<h1><?= e($c['name']) ?></h1>
<p><a href="<?= url('admin/clients') ?>">← Toutes les clientes</a> · Total payé : <strong><?= money($spent) ?></strong> · <a target="_blank" href="<?= e(wa_link_to($c['phone'], 'Bonjour ' . $c['name'] . ', ')) ?>">WhatsApp ↗</a></p>
<div class="card"><form method="post" class="stack"><?= csrf_field() ?>
<div class="row"><label>Nom<input name="name" value="<?= e($c['name']) ?>" required></label><label>Téléphone<input name="phone" value="<?= e($c['phone']) ?>"></label><label>Email<input name="email" value="<?= e($c['email']) ?>"></label></div>
<label>Suivi / notes (type de locks, allergies, préférences…)<textarea name="notes" rows="4"><?= e($c['notes']) ?></textarea></label>
<div><button class="btn">Enregistrer</button> <a class="btn ghost" href="<?= url('admin/factures/new?client=' . $id) ?>">Nouvelle facture</a></div></form></div>
<h2>Rendez-vous</h2><div class="table-wrap"><table><tr><th>Date</th><th>Soin</th><th>Statut</th></tr>
<?php foreach ($rdvs as $a): ?><tr><td><?= dfr($a['rdv_date']) ?> <?= e($a['rdv_time']) ?></td><td><?= e($a['service']) ?></td><td><span class="pill <?= e($a['status']) ?>"><?= e(st($a['status'])) ?></span></td></tr><?php endforeach; ?>
<?php if (!$rdvs): ?><tr><td colspan="3" class="muted">Aucun rendez-vous.</td></tr><?php endif; ?></table></div>
<h2>Factures</h2><div class="table-wrap"><table><tr><th>N°</th><th>Date</th><th>Total</th><th>Statut</th></tr>
<?php foreach ($invs as $i): ?><tr><td><a href="<?= url('admin/factures/' . (int) $i['id']) ?>"><?= e($i['number']) ?></a></td><td><?= dfr($i['inv_date']) ?></td><td><?= money($i['total']) ?></td><td><span class="pill <?= e($i['status']) ?>"><?= e(st($i['status'])) ?></span></td></tr><?php endforeach; ?>
<?php if (!$invs): ?><tr><td colspan="4" class="muted">Aucune facture.</td></tr><?php endif; ?></table></div>
<?php return; endif;
$s = trim($_GET['s'] ?? '');
$rows = $s !== '' ? all("SELECT c.*, (SELECT COUNT(*) FROM appointments a WHERE a.client_id = c.id AND a.status <> 'message') AS visits, (SELECT COALESCE(SUM(total),0) FROM invoices i WHERE i.client_id = c.id AND i.status='paid') AS spent FROM clients c WHERE c.name LIKE ? OR c.phone LIKE ? ORDER BY c.name", ["%$s%", "%$s%"])
  : all("SELECT c.*, (SELECT COUNT(*) FROM appointments a WHERE a.client_id = c.id AND a.status <> 'message') AS visits, (SELECT COALESCE(SUM(total),0) FROM invoices i WHERE i.client_id = c.id AND i.status='paid') AS spent FROM clients c ORDER BY c.name");
?>
<h1>Clientes</h1>
<form class="bar"><input class="grow" name="s" placeholder="Rechercher par nom ou téléphone" value="<?= e($s) ?>"><button class="btn">Rechercher</button></form>
<div class="card"><h2 style="margin-top:0">Ajouter une cliente</h2><form method="post" class="stack"><?= csrf_field() ?>
<div class="row"><label>Nom<input name="name" required></label><label>Téléphone<input name="phone"></label><label>Email<input name="email"></label></div><div><button class="btn">Ajouter</button></div></form></div>
<h2>Liste (<?= count($rows) ?>)</h2><div class="table-wrap"><table><tr><th>Nom</th><th>Téléphone</th><th>Visites</th><th>Total payé</th></tr>
<?php foreach ($rows as $c): ?><tr><td><a href="<?= url('admin/clients/' . (int) $c['id']) ?>"><?= e($c['name']) ?></a></td><td><?= e($c['phone']) ?></td><td><?= (int) $c['visits'] ?></td><td><?= money($c['spent']) ?></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="4" class="muted">Aucune cliente.</td></tr><?php endif; ?></table></div>
