<?php
$title = 'Réglages';
if (is_post()) {
    csrf_check();
    if (post('action') === 'password') {
        $u = one('SELECT * FROM users WHERE id = ?', [$_SESSION['admin_id']]);
        if (!password_verify($_POST['old'] ?? '', $u['pass'])) flash('Ancien mot de passe incorrect.', 'err');
        elseif (strlen($_POST['new'] ?? '') < 8) flash('Nouveau mot de passe : 8 caractères minimum.', 'err');
        else { update('users', (int) $u['id'], ['pass' => password_hash($_POST['new'], PASSWORD_DEFAULT)]); flash('Mot de passe changé.'); }
        redirect('admin/reglages');
    }
    foreach (['salon_name', 'city', 'whatsapp', 'address', 'hours', 'open_time', 'close_time', 'maps_url', 'instagram', 'facebook', 'tiktok', 'hero_title', 'hero_subtitle', 'hero_text', 'about_text'] as $k) save_setting($k, post($k));
    foreach (['logo', 'hero_image', 'about_image'] as $k) { if ($f = upload($k)) { delete_upload(setting($k)); save_setting($k, $f); } }
    flash('Réglages enregistrés.'); redirect('admin/reglages');
}
$f = fn(string $k, string $label, string $d = '', string $t = 'text') => '<label>' . $label . '<input type="' . $t . '" name="' . $k . '" value="' . e(setting($k, $d)) . '"></label>';
?>
<h1>Réglages du site</h1>
<div class="card"><form method="post" enctype="multipart/form-data" class="stack"><?= csrf_field() ?>
<h2 style="margin-top:0">Identité</h2>
<?= $f('salon_name', 'Nom du salon', 'Min Afro Queen') ?><?= $f('city', 'Ville', 'Dakar, Sénégal') ?>
<label>Logo<input type="file" name="logo" accept="image/*"></label><?php if (setting('logo')): ?><img src="<?= e(img(setting('logo'))) ?>" height="50" alt=""><?php endif; ?>
<h2>Contact</h2>
<?= $f('whatsapp', 'Numéro WhatsApp (avec indicatif, sans +)', '221777090874') ?><?= $f('address', 'Adresse') ?><?= $f('hours', 'Horaires affichés', '9h30 - 20h') ?>
<div class="row"><?= $f('open_time', 'Ouverture (créneaux RDV)', '09:30', 'time') ?><?= $f('close_time', 'Fermeture', '20:00', 'time') ?></div>
<?= $f('maps_url', 'Lien Google Maps') ?><?= $f('instagram', 'Instagram (lien)') ?><?= $f('facebook', 'Facebook (lien)') ?><?= $f('tiktok', 'TikTok (lien)') ?>
<h2>Page d'accueil</h2>
<?= $f('hero_title', 'Titre', 'Vos locks.') ?><?= $f('hero_subtitle', 'Sous-titre (en violet)', 'Votre couronne.') ?>
<label>Texte d'accroche<textarea name="hero_text" rows="2"><?= e(setting('hero_text')) ?></textarea></label>
<label>Photo principale (accueil)<input type="file" name="hero_image" accept="image/*"></label><?php if (setting('hero_image')): ?><img src="<?= e(img(setting('hero_image'))) ?>" height="80" alt=""><?php endif; ?>
<label>Texte de présentation<textarea name="about_text" rows="3"><?= e(setting('about_text')) ?></textarea></label>
<label>Photo de présentation<input type="file" name="about_image" accept="image/*"></label><?php if (setting('about_image')): ?><img src="<?= e(img(setting('about_image'))) ?>" height="80" alt=""><?php endif; ?>
<div><button class="btn">Enregistrer</button></div></form></div>
<h2>Changer le mot de passe</h2><div class="card"><form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="action" value="password">
<label>Ancien mot de passe<input type="password" name="old" required></label><label>Nouveau mot de passe<input type="password" name="new" minlength="8" required></label><div><button class="btn">Changer</button></div></form></div>
