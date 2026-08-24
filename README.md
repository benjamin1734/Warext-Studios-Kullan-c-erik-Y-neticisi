# Warext Studios | Kullanıcı İçerik Yöneticisi

XenForo 2.3 için kullanıcıların oluşturduğu içerikleri kategori ve içerik türüne göre görüntülemek, filtrelemek ve yetkiye bağlı olarak toplu yönetmek amacıyla geliştirilen moderasyon eklentisi.

## Sürüm

`1.0.4`

## Doğrudan XenForo Kurulumu

**Kurulum ZIP'i:** [WarextStudios-UserContentManager-1.0.4.zip](releases/WarextStudios-UserContentManager-1.0.4.zip?raw=1)

ZIP doğrudan XenForo Admin CP > Add-ons > Install/upgrade from archive alanına yüklenir.

## Kullanım

- Forum kullanıcı profili > Moderator tools > İçerikleri Yönet
- Admin CP > Users > Kullanıcı İçerik Yöneticisi
- Admin CP > Users > kullanıcıyı düzenle > Actions > İçerikleri Yönet

Super admin hesapları Warext UCM izinlerine otomatik olarak sahiptir. Diğer moderatör ve yöneticiler için ilgili `warextUcm` izinleri ayrıca tanımlanmalıdır.

## Gereksinimler

- XenForo 2.3.0+
- PHP 8.1+
- PHP 8.4 uyumluluğu
- XenForo Resource Manager isteğe bağlıdır

## Özellikler

- Kullanıcı içerik yönetim merkezi
- XenForo default arayüz bileşenleri
- Konu kategori gruplaması ve gelişmiş filtreleme
- Tekli, sayfa, kategori ve tüm filtre sonucu seçimi
- Toplu taşıma, silme, kalıcı silme, geri getirme, kilit ve sabitleme
- Toplu onay, önek ve başlık düzenleme
- Kalıcı silme için ayrı yetki ve açık onay
- İçerik bazında XenForo native permission yeniden doğrulaması
- İşlem geçmişi ve XenForo moderator log entegrasyonu
- Birden fazla konuya dayalı tek kullanıcı uyarısı
- XenForo Resource Manager opsiyonel entegrasyonu
- 500 üzeri işlemlerde XenForo Job sistemi
- Cursor tabanlı batch işleme
- Operation bazlı concurrency kilidi ve idempotent ilerleme

## Eklenti kimliği

`WarextStudios/UserContentManager`
