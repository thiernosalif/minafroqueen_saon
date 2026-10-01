<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';

$path = trim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$base = trim($CONFIG['base_url'] ?? '', '/');
if ($base !== '' && str_starts_with($path, $base)) $path = trim(substr($path, strlen($base)), '/');
$seg = $path === '' ? [] : explode('/', $path);

// Site pas encore installé -> assistant d'installation
try { $installed = (int) val('SELECT COUNT(*) FROM users') > 0; } catch (Throwable $e) { $installed = false; }
if (!$installed) { redirect('install.php'); }

$public = ['' => 'home', 'services' => 'services', 'galerie' => 'galerie', 'journal' => 'journal', 'boutique' => 'boutique',
    'panier' => 'panier', 'reservation' => 'reservation', 'contact' => 'contact', 'avis' => 'avis'];
$admin = ['' => 'dashboard', 'login' => 'login', 'logout' => 'logout', 'services' => 'services', 'rdv' => 'rdv', 'clients' => 'clients',
    'factures' => 'factures', 'compta' => 'compta', 'articles' => 'articles', 'galerie' => 'galerie', 'produits' => 'produits',
    'commandes' => 'commandes', 'avis' => 'avis', 'reglages' => 'reglages'];

$params = [];
$isAdmin = ($seg[0] ?? '') === 'admin';
if ($isAdmin) {
    $key = $seg[1] ?? '';
    $file = $admin[$key] ?? null;
    $params = array_slice($seg, 2);
    if ($file && $file !== 'login' && $file !== 'logout') require_admin();
    $file = $file ? "admin/$file" : null;
} else {
    $key = $seg[0] ?? '';
    $file = $public[$key] ?? null;
    $params = array_slice($seg, 1);
}

if (!$file || !is_file(__DIR__ . "/app/pages/$file.php")) {
    http_response_code(404);
    $file = 'notfound';
}

$title = '';
$layout = $isAdmin && !in_array($file, ['admin/login'], true) ? 'admin' : ($file === 'admin/login' ? 'bare' : 'site');
$raw = false;                       // une page peut désactiver le layout (impression facture, export)
ob_start();
require __DIR__ . "/app/pages/$file.php";
$content = ob_get_clean();
if ($raw) { echo $content; exit; }
require __DIR__ . "/app/views/layout_$layout.php";
