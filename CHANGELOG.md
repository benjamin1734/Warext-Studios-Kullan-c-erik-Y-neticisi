# Değişiklik Geçmişi

## 1.0.1

- XenForo kurulumunda özel izin grubunun eksik tanımlanmasından kaynaklanan veri doğrulama hatası düzeltildi.
- Resource Manager kurulu değilken `resource` content type kaydının kurulumu engellemesi önlendi.
- XFRM kaynak adaptörü yalnızca Resource Manager mevcutsa dinamik olarak etkinleşir hale getirildi.
- XenForo izin tanımlarına açık varsayılan değerler eklendi.
- Doğrudan XenForo kurulum ZIP üretimi sürüm numarasını `addon.json` dosyasından okuyacak şekilde güncellendi.

## 1.0.0

- Kullanıcı içerik yönetim merkezi tamamlandı.
- Gelişmiş filtreleme ve kategori gruplaması eklendi.
- Toplu seçim, taşıma, silme, geri getirme, kilit, sabitleme, onay, önek ve başlık işlemleri tamamlandı.
- İşlem geçmişi ve XenForo moderator log entegrasyonu eklendi.
- Birden fazla içeriğe bağlı tek kullanıcı uyarısı eklendi.
- XenForo Resource Manager için opsiyonel kaynak yönetimi eklendi.
- Üçüncü taraf içerik adaptör API eklendi.
- 500 üzeri işlemler XenForo Job sistemine taşındı.
- Cursor tabanlı batch işleme, concurrency kilidi ve idempotent ilerleme eklendi.
- PHP 8.1 ve PHP 8.4 doğrulama altyapısı eklendi.
