# Warext Studios | Kullanıcı İçerik Yöneticisi

XenForo 2.3 için kullanıcıların oluşturduğu içerikleri kategori ve içerik türüne göre görüntülemek, filtrelemek ve yetkiye bağlı olarak toplu yönetmek amacıyla geliştirilen moderasyon eklentisi.

## Sürüm

`1.0.0`

## Doğrudan XenForo Kurulumu

**Kurulum ZIP'i:** [WarextStudios-UserContentManager-1.0.0.zip](releases/WarextStudios-UserContentManager-1.0.0.zip?raw=1)

Bu ZIP doğrudan XenForo Admin CP > Add-ons > Install/upgrade from archive alanına yüklenmek için hazırlanmıştır.

Arşiv yükleyicisini kullanmak için `src/config.php` içinde aşağıdaki ayarın açık olması gerekir:

```php
$config['enableAddOnArchiveInstaller'] = true;
```

ZIP'in kökünde yalnızca standart `upload/` yapısı bulunur. `_output` geliştirme dosyaları pakete dahil edilmez ve XenForo dosya sağlık kontrolü için `hashes.json` otomatik üretilir.

## Gereksinimler

- XenForo 2.3.0+
- PHP 8.1+
- PHP 8.4 uyumluluğu
- XenForo Resource Manager isteğe bağlıdır

## Özellikler

- Kullanıcı profili üzerinden içerik yönetim merkezi
- XenForo default arayüz bileşenleri
- Konu kategori gruplaması ve gelişmiş filtreleme
- Tekli, sayfa, kategori ve tüm filtre sonucu seçimi
- Toplu taşıma, silme, kalıcı silme, geri getirme, kilit ve sabitleme
- Toplu onay, önek ve başlık düzenleme
- Kalıcı silme için ayrı yetki ve açık onay
- İçerik bazında XenForo native permission yeniden doğrulaması
- İşlem geçmişi ve XenForo moderator log entegrasyonu
- Birden fazla konuya dayalı tek kullanıcı uyarısı
- XenForo Resource Manager otomatik ve opsiyonel entegrasyonu
- XFRM kaynak taşıma, silme, geri getirme, onay ve düzenleme
- `warext_ucm_handler_class` tabanlı üçüncü taraf içerik adaptör API
- 500 içeriğe kadar anlık toplu işlem
- 500 üzeri işlemlerde XenForo Job sistemi
- Cursor tabanlı batch işleme
- Operation bazlı concurrency kilidi ve idempotent ilerleme
- Job çalışırken işlemi başlatan moderatörün güncel izinlerini tekrar doğrulama
- PHP 8.1 ve PHP 8.4 GitHub Actions lint
- Tüm add-on JSON ve `_data` XML doğrulaması

## Eklenti kimliği

`WarextStudios/UserContentManager`

## Manuel Kurulum

Arşiv yükleyici kapalıysa `upload/` içeriğini XenForo kök dizinine yükleyin ve ACP > Add-ons alanından `Warext Studios | Kullanıcı İçerik Yöneticisi` eklentisini kurun.

Resource Manager kurulu değilse kaynak yönetimi otomatik olarak devre dışı kalır ve eklenti konu yönetimiyle çalışmaya devam eder.

## Geliştirici API

Üçüncü taraf içerik entegrasyonu için `docs/CONTENT_ADAPTER_API.md` dosyasına bakın.
