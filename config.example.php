<?php
// =====================================================================
//  NEWSROOM CHECK - SETTINGS  (the only file you normally need to edit)
// =====================================================================
const APP_NAME = 'Newsroom Check';

// MySQL (XAMPP default: user "root", empty password)
const DB_HOST = 'localhost', DB_NAME = 'your_database_name', DB_USER = 'your_database_user', DB_PASS = 'your_database_password';
// Free Groq API key from https://console.groq.com/keys  (or set env var GROQ_API_KEY)
const GROQ_API_KEY = 'your_groq_api_key';                 // paste between the quotes, e.g. 'gsk_abc...'
// Models are tried in this order until one works (each Groq model has its own free limits)
const AI_MODELS = ['openai/gpt-oss-120b', 'llama-3.3-70b-versatile', 'openai/gpt-oss-20b', 'llama-3.1-8b-instant', 'groq/compound'];
const AI_DAILY_LIMIT = 30;               // AI checks per user per day
const CA_BUNDLE = '';                    // Windows "SSL certificate problem"? use 'C:/xampp/apache/bin/curl-ca-bundle.crt'

// Shown on About / Contact pages - put your own details here
const CONTACT_EMAIL = 'support@newsroomcheck.example';
const CONTACT_PHONE = '+91 00000 00000';
const CONTACT_HOURS = 'Mon - Sat, 10:00 AM - 6:00 PM IST';
const CONTACT_ADDRESS = 'Department of Computer Science, S.R.M. college kudal, Maharashtra, India';
const TEAM = [['Utkarsha Pavnoji', 'Project Lead & Backend'], ['Teammate 2', 'Frontend & Design'], ['Teammate 3', 'Testing & Documentation']];

date_default_timezone_set('Asia/Kolkata');
session_start();

function db(): PDO {
    static $pdo;
    if (!$pdo) {
        try {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        } catch (PDOException $ex) {
            die('Database not ready. Start MySQL in XAMPP, then open <a href="setup.php">setup.php</a>. (' . e($ex->getMessage()) . ')');
        }
    }
    return $pdo;
}
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function csrf(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function check_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(400); die('Invalid request token. Go back and retry.'); } }
function user() { return $_SESSION['user'] ?? null; }
function require_login(): void {
    if (user()) return;
    $p = parse_url($_SERVER['REQUEST_URI'] ?? ''); // remember the page so we can return after login
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') $_SESSION['next'] = basename($p['path'] ?? '') . (isset($p['query']) ? '?' . $p['query'] : '');
    $_SESSION['notice'] = 'Please log in to check news and see your results.';
    header('Location: login.php'); exit;
}
function require_admin(): void { require_login(); if (user()['role'] !== 'admin') { http_response_code(403); die('Admins only.'); } }
