<?php
if (is_admin()) redirect('admin');
$err = '';
if (is_post()) {
    csrf_check();
    $_SESSION['tries'] = ($_SESSION['tries'] ?? 0);
    if ($_SESSION['tries'] >= 8) { $err = 'Trop de tentatives. Fermez le navigateur et réessayez plus tard.'; }
    else {
        $u = one('SELECT * FROM users WHERE email = ?', [post('email')]);
        if ($u && password_verify($_POST['pass'] ?? '', $u['pass'])) {
            session_regenerate_id(true); $_SESSION['admin_id'] = (int) $u['id']; $_SESSION['tries'] = 0; redirect('admin');
        }
        $_SESSION['tries']++; $err = 'Email ou mot de passe incorrect.';
    }
}
?>
<main class="card login"><h1>Administration</h1><p class="muted">Min Afro Queen</p>
<?php if ($err): ?><p class="err"><?= e($err) ?></p><?php endif; ?>
<form method="post" class="stack"><?= csrf_field() ?><label>Email<input type="email" name="email" required autofocus></label>
<label>Mot de passe<input type="password" name="pass" required></label><button class="btn">Se connecter</button></form></main>
