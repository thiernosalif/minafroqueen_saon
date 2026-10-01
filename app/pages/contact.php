<?php
$title = 'Contact';
if (is_post()) {
    csrf_check();
    $n = post('name'); $p = post('phone'); $m = post('message');
    if ($n === '' || $p === '' || $m === '') flash('Veuillez remplir tous les champs.', 'err');
    else { $cid = client_for($n, $p); insert('appointments', ['client_id' => $cid, 'service_id' => null, 'name' => $n, 'phone' => $p, 'rdv_date' => date('Y-m-d'), 'rdv_time' => '', 'message' => "[Message contact] $m", 'status' => 'message']);
      flash('Merci, votre message est bien reçu. Nous vous répondons rapidement.'); redirect('contact'); }
}
$addr = setting('address');
$map = setting('maps_url', 'https://www.google.com/maps/search/' . rawurlencode($addr . ' Dakar'));
?>
<div class="wrap split" style="align-items:start">
  <div><span class="eyebrow">Échangeons</span><h1>Parlons de <em>vos locks.</em></h1>
  <p>Une question, une envie ou un projet ? Écrivez-nous, nous serons ravies de vous répondre.</p>
  <div class="card"><span class="tag">Nous trouver</span><p><?= e($addr) ?></p><a href="<?= e($map) ?>" target="_blank" rel="noopener">Ouvrir dans Google Maps ↗</a></div><br>
  <div class="card"><span class="tag">Horaires</span><p><?= e(setting('hours')) ?></p></div><br>
  <div class="card"><span class="tag">WhatsApp</span><a class="btn" target="_blank" rel="noopener" href="<?= e(wa_link('Bonjour, je souhaite avoir des renseignements.')) ?>">Envoyez-nous un message</a></div></div>
  <form method="post" class="form card"><?= csrf_field() ?>
    <label>Votre nom<input name="name" required></label><label>Votre téléphone<input name="phone" required></label>
    <label>Votre message<textarea name="message" rows="5" required></textarea></label><button class="btn">Envoyer le message</button></form>
</div>
