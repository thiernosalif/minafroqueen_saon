<?php
$title = 'Laisser un avis';
if (is_post()) {
    csrf_check();
    $n = post('name'); $b = post('body'); $r = max(1, min(5, (int) post('rating', '5')));
    if ($n === '' || $b === '') flash('Merci de remplir votre nom et votre avis.', 'err');
    else { insert('reviews', ['name' => $n, 'rating' => $r, 'body' => $b]); flash('Merci ! Votre avis sera publié après validation par le salon.'); redirect(''); }
}
?>
<div class="wrap"><span class="eyebrow">Avis clients</span><h1>Racontez-nous <em>votre expérience.</em></h1>
<form method="post" class="form"><?= csrf_field() ?>
<label>Votre nom<input name="name" required></label>
<label>Note<select name="rating"><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"><?= str_repeat('★', $i) ?></option><?php endfor; ?></select></label>
<label>Votre avis<textarea name="body" rows="5" required></textarea></label><button class="btn">Envoyer mon avis</button></form></div>
