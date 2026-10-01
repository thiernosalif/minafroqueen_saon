<?php
declare(strict_types=1);
// Assistant d'installation : à ouvrir UNE fois dans le navigateur, puis supprimer ce fichier.
require __DIR__ . '/app/bootstrap.php';

$msg = ''; $done = false;
try { $installed = (int) val('SELECT COUNT(*) FROM users'); } catch (Throwable $e) { $installed = 0; $dbErr = $e->getMessage(); }

if ($installed) {
    $msg = 'Le site est déjà installé. Supprimez le fichier install.php du serveur.';
} elseif (is_post()) {
    $email = post('email'); $pass = $_POST['pass'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
        $msg = 'Email invalide ou mot de passe trop court (8 caractères minimum).';
    } else {
        $engine = is_sqlite() ? '' : ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
        $pk = is_sqlite() ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
        $sql = str_replace(['{PK}', '{ENGINE}'], [$pk, $engine], file_get_contents(__DIR__ . '/app/schema.sql'));
        foreach (array_filter(array_map('trim', explode(";\n", $sql))) as $st) db()->exec($st);

        insert('users', ['email' => $email, 'pass' => password_hash($pass, PASSWORD_DEFAULT)]);

        $services = [
            ['Installation de Sisterlocks', 'femmes', 'Diagnostic, séparation et création de vos sisterlocks.', '3 à 5 h estimées'],
            ['Extensions de locks', 'femmes', 'Ajout de longueur et de volume pour un rendu naturel et durable.', '3 à 5 h estimées'],
            ['Reprise / Retwist', 'femmes', 'Reprise précise des racines et finition soignée.', '1 h 30 à 2 h estimées'],
            ['Entretien & Soin des locks', 'femmes', 'Nettoyage, hydratation et soin du cuir chevelu.', '1 h à 2 h estimées'],
            ['Coiffure sur locks (cérémonie)', 'femmes', 'Mise en beauté élégante pour vos dîners et cérémonies.', '2 à 3 h estimées'],
            ['Passion twist sur natte', 'femmes', 'Twist sur natte, look tendance et longue tenue.', '3 à 4 h estimées'],
            ['Départ de locks homme', 'hommes', 'Création de vos premiers locks avec un tracé net et durable.', '3 à 5 h estimées'],
            ['Retwist homme', 'hommes', 'Reprise des racines et finition propre.', '1 h à 2 h estimées'],
            ['Coiffure sur locks homme', 'hommes', 'Styles et attaches soignés pour toutes occasions.', '1 h à 2 h estimées'],
        ];
        foreach ($services as $i => [$n, $c, $d, $t]) insert('services', ['name' => $n, 'category' => $c, 'description' => $d, 'duration' => $t, 'price' => 0, 'sort' => $i]);

        foreach (['whatsapp' => '221777090874', 'address' => 'Sicap Foire, en face Dibiterie Koromakk Faye', 'open_time' => '09:30', 'close_time' => '20:00',
                  'hours' => '9h30 - 20h', 'salon_name' => 'Min Afro Queen', 'city' => 'Dakar, Sénégal'] as $k => $v) save_setting($k, $v);
        $done = true;
    }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Installation — Min Afro Queen</title><link rel="stylesheet" href="assets/css/admin.css"></head>
<body class="bare"><main class="card login">
<h1>Installation</h1>
<?php if (!empty($dbErr)): ?><p class="err">Connexion à la base impossible : <?= e($dbErr) ?><br>Vérifiez <code>config.php</code>.</p>
<?php elseif ($done): ?><p class="ok">Installation terminée. <strong>Supprimez maintenant le fichier install.php</strong> du serveur.</p><p><a class="btn" href="admin/login">Se connecter</a></p>
<?php else: ?>
  <?php if ($msg): ?><p class="err"><?= e($msg) ?></p><?php endif; ?>
  <?php if (!$installed): ?>
  <p>Créez le compte administrateur.</p>
  <form method="post"><label>Email<input type="email" name="email" required></label>
  <label>Mot de passe (8 caractères min.)<input type="password" name="pass" minlength="8" required></label>
  <button class="btn">Installer</button></form>
  <?php endif; ?>
<?php endif; ?>
</main></body></html>
