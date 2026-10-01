<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
$CONFIG = require is_file(ROOT . '/config.php') ? ROOT . '/config.php' : ROOT . '/config.sample.php';

date_default_timezone_set('Africa/Dakar');
if (!empty($CONFIG['debug'])) { ini_set('display_errors', '1'); error_reporting(E_ALL); }
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();

/* ---------- Base de données ---------- */
function db(): PDO {
    static $pdo = null;
    global $CONFIG;
    if ($pdo) return $pdo;
    if (($CONFIG['db_driver'] ?? 'mysql') === 'sqlite') {
        $pdo = new PDO('sqlite:' . ROOT . '/storage/local.sqlite');
        $pdo->exec('PRAGMA foreign_keys = ON');
    } else {
        $pdo = new PDO("mysql:host={$CONFIG['db_host']};dbname={$CONFIG['db_name']};charset=utf8mb4",
            $CONFIG['db_user'], $CONFIG['db_pass']);
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}
function q(string $sql, array $p = []): PDOStatement { $s = db()->prepare($sql); $s->execute($p); return $s; }
function all(string $sql, array $p = []): array { return q($sql, $p)->fetchAll(); }
function one(string $sql, array $p = []): ?array { $r = q($sql, $p)->fetch(); return $r ?: null; }
function val(string $sql, array $p = []) { $r = q($sql, $p)->fetchColumn(); return $r === false ? null : $r; }
function insert(string $table, array $data): int {
    $cols = implode(',', array_keys($data));
    $ph = implode(',', array_fill(0, count($data), '?'));
    q("INSERT INTO $table ($cols) VALUES ($ph)", array_values($data));
    return (int) db()->lastInsertId();
}
function update(string $table, int $id, array $data): void {
    $set = implode(',', array_map(fn($c) => "$c = ?", array_keys($data)));
    q("UPDATE $table SET $set WHERE id = ?", [...array_values($data), $id]);
}
function is_sqlite(): bool { global $CONFIG; return ($CONFIG['db_driver'] ?? '') === 'sqlite'; }

/* ---------- Réglages ---------- */
function setting(string $k, string $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        try { $cache = array_column(all('SELECT k, v FROM settings'), 'v', 'k'); } catch (Throwable $e) { $cache = []; }
    }
    return ($cache[$k] ?? '') !== '' ? $cache[$k] : $default;
}
function save_setting(string $k, string $v): void {
    if (val('SELECT COUNT(*) FROM settings WHERE k = ?', [$k])) q('UPDATE settings SET v = ? WHERE k = ?', [$v, $k]);
    else insert('settings', ['k' => $k, 'v' => $v]);
}

/* ---------- Helpers ---------- */
function e($s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function url(string $path = ''): string { global $CONFIG; return ($CONFIG['base_url'] ?? '') . '/' . ltrim($path, '/'); }
/** URL d'un fichier statique avec version automatique (évite le cache navigateur après une mise à jour). */
function asset(string $path): string {
    $f = ROOT . '/' . ltrim($path, '/');
    return url($path) . '?v=' . (is_file($f) ? filemtime($f) : 0);
}
function redirect(string $path): never { header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path))); exit; }
function money($n): string { return number_format((float) $n, 0, ',', ' ') . ' FCFA'; }
function dfr(?string $d, bool $time = false): string { return $d ? date($time ? 'd/m/Y H:i' : 'd/m/Y', strtotime($d)) : ''; }
function slugify(string $s): string {
    $s = strtolower(trim(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s));
    return trim(preg_replace('/[^a-z0-9]+/', '-', $s), '-') ?: 'article';
}
function flash(?string $msg = null, string $type = 'ok'): ?array {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return null; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">'; }
function csrf_check(): void {
    $t = $_SESSION['csrf'] ?? '';
    if ($t === '' || !hash_equals($t, $_POST['_csrf'] ?? '')) { http_response_code(419); exit('Session expirée, rechargez la page.'); }
}
function is_post(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
function post(string $k, string $d = ''): string { return trim((string) ($_POST[$k] ?? $d)); }

function wa_number(): string { return preg_replace('/\D/', '', setting('whatsapp', '221777090874')); }
function wa_link(string $text = ''): string { return 'https://wa.me/' . wa_number() . ($text !== '' ? '?text=' . rawurlencode($text) : ''); }

/* ---------- Authentification admin ---------- */
function is_admin(): bool { return !empty($_SESSION['admin_id']); }
function require_admin(): void { if (!is_admin()) redirect('admin/login'); }

/* ---------- Uploads ---------- */
function upload(string $field, string $kind = 'image'): ?string {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) { flash('Échec de l\'envoi du fichier (taille trop grande ? limite serveur : ' . ini_get('upload_max_filesize') . ').', 'err'); return null; }
    $allowed = $kind === 'video'
        ? ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov']
        : ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!isset($allowed[$mime])) { flash('Format de fichier non autorisé.', 'err'); return null; }
    $name = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    if (!is_dir(ROOT . '/uploads')) mkdir(ROOT . '/uploads', 0755, true);
    if (!move_uploaded_file($f['tmp_name'], ROOT . '/uploads/' . $name)) { flash('Impossible d\'enregistrer le fichier.', 'err'); return null; }
    return $name;
}
function delete_upload(?string $name): void {
    if ($name && preg_match('/^[\w.-]+$/', $name)) @unlink(ROOT . '/uploads/' . $name);
}
function img(?string $name, string $fallback = ''): string { return $name ? url('uploads/' . $name) : $fallback; }

/** Transforme un lien YouTube/Vimeo en URL d'intégration, sinon null. */
function embed_url(?string $u): ?string {
    if (!$u) return null;
    if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|shorts/|embed/))([\w-]{6,})~', $u, $m)) return 'https://www.youtube.com/embed/' . $m[1];
    if (preg_match('~vimeo\.com/(\d+)~', $u, $m)) return 'https://player.vimeo.com/video/' . $m[1];
    return null;
}

/* ---------- Réservation ---------- */
function time_slots(): array {
    [$oh, $om] = array_map('intval', explode(':', setting('open_time', '09:30')));
    [$ch, $cm] = array_map('intval', explode(':', setting('close_time', '20:00')));
    $slots = [];
    for ($t = $oh * 60 + $om; $t <= $ch * 60 + $cm - 60; $t += 30) $slots[] = sprintf('%02d:%02d', intdiv($t, 60), $t % 60);
    return $slots;
}

/** Trouve ou crée une cliente à partir de son téléphone. */
function client_for(string $name, string $phone): int {
    $digits = preg_replace('/\D/', '', $phone);
    foreach (all('SELECT id, phone FROM clients') as $c) {
        if ($digits !== '' && preg_replace('/\D/', '', $c['phone']) === $digits) return (int) $c['id'];
    }
    return insert('clients', ['name' => $name, 'phone' => $phone]);
}

/* ---------- Admin ---------- */
/** Libellés français des statuts. */
function st(string $s): string {
    return ['pending' => 'En attente', 'confirmed' => 'Confirmé', 'done' => 'Terminé', 'cancelled' => 'Annulé', 'message' => 'Message',
            'unpaid' => 'Impayée', 'paid' => 'Payée', 'new' => 'Nouvelle', 'delivered' => 'Livrée', 'approved' => 'Publié', 'hidden' => 'Masqué',
            'in' => 'Entrée', 'out' => 'Dépense'][$s] ?? $s;
}
/** Lien WhatsApp vers un numéro de cliente (Sénégal par défaut). */
function wa_link_to(string $phone, string $text = ''): string {
    $d = preg_replace('/\D/', '', $phone);
    if (strlen($d) === 9) $d = '221' . $d;
    return 'https://wa.me/' . $d . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}
