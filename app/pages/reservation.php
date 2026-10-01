<?php
$title = 'Réservation';
$services = all("SELECT * FROM services WHERE active = 1 ORDER BY category DESC, sort, id");
$slots = time_slots();
if (is_post()) {
    csrf_check();
    $sid = (int) ($_POST['service'] ?? 0); $date = post('date'); $time = post('time'); $name = post('name'); $phone = post('phone');
    $svc = one("SELECT * FROM services WHERE id = ? AND active = 1", [$sid]);
    $okDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && $date >= date('Y-m-d');
    if (!$svc || !$okDate || !in_array($time, $slots, true) || $name === '' || $phone === '') {
        flash('Veuillez remplir tous les champs avec une date à venir et une heure valide.', 'err');
    } else {
        $cid = client_for($name, $phone);
        insert('appointments', ['client_id' => $cid, 'service_id' => $sid, 'name' => $name, 'phone' => $phone, 'rdv_date' => $date, 'rdv_time' => $time, 'message' => post('message')]);
        flash('Votre demande est envoyée. Le salon vous confirme la disponibilité par WhatsApp ou téléphone.');
        redirect('reservation');
    }
}
$sel = (int) ($_POST['service'] ?? $_GET['s'] ?? 0);
?>
<div class="wrap split" style="align-items:start">
  <div><span class="eyebrow">Votre moment à vous</span><h1>Réservez votre <em>rendez-vous.</em></h1>
  <p class="notice">Choisissez votre soin et le créneau qui vous convient. Nous vous confirmerons sa disponibilité.</p>
  <form method="post" class="form"><?= csrf_field() ?>
    <label>Votre soin<select name="service" required><option value="">Sélectionner un service</option>
      <?php foreach ($services as $s): ?><option value="<?= (int) $s['id'] ?>" <?= $sel === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?> · <?= e(ucfirst($s['category'])) ?></option><?php endforeach; ?></select></label>
    <div class="row2"><label>Date souhaitée<input type="date" name="date" min="<?= date('Y-m-d') ?>" value="<?= e(post('date')) ?>" required></label>
    <label>Heure souhaitée<select name="time" required><option value="">Choisir une heure</option><?php foreach ($slots as $t): ?><option <?= post('time') === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></label></div>
    <div class="row2"><label>Votre nom<input name="name" value="<?= e(post('name')) ?>" required></label><label>Téléphone<input name="phone" value="<?= e(post('phone')) ?>" required></label></div>
    <label>Un message ?<textarea name="message" rows="3"><?= e(post('message')) ?></textarea></label>
    <button class="btn">Envoyer ma demande</button></form></div>
  <div class="card"><span class="eyebrow">Avant votre visite</span><h3>Un moment pour vos locks.</h3>
    <p>Ce formulaire est une demande de rendez-vous : le créneau n'est réservé qu'après confirmation par le salon.</p>
    <p>Les tarifs sont communiqués sur WhatsApp après discussion de votre projet (longueur, densité, soin souhaité).</p>
    <p><strong>Horaires :</strong> <?= e(setting('hours')) ?><br><strong>Adresse :</strong> <?= e(setting('address')) ?></p></div>
</div>
