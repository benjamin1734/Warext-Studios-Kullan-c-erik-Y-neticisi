# Değişiklik Geçmişi

## 1.0.0

- 500 üzeri konu ve kaynak işlemleri XenForo Job sistemine taşındı.
- Cursor tabanlı batch işleme eklendi.
- Büyük işlemler için kalıcı operation kaydı, durum ve sayaç takibi eklendi.
- Aynı operation'ın eş zamanlı ikinci worker tarafından çalıştırılmasını engelleyen DB kilidi eklendi.
- Background job işlemleri başlatan moderatörün güncel XenForo izinleriyle yeniden doğrulanır hale getirildi.
- Job sırasında hedef forum ve kaynak kategorisi erişimi tekrar kontrol edilir hale getirildi.
- Hard-delete sonrası özel log kaydı silinmiş entity nesnesine bağımlı olmaktan çıkarıldı.
- Thread ve Resource final controller katmanları etkinleştirildi.
- Kurulum, 0.10.0 yükseltme ve kaldırma şeması queue tablosuyla tamamlandı.
- README ve doğrulama altyapısı nihai sürüme göre güncellendi.

## 0.10.0

- Uyarı ve moderasyon kayıt altyapısı eklendi.
- İçerik işlemleri özel işlem geçmişine ve XenForo moderator log sistemine bağlandı.
- Birden fazla seçili konuya dayalı tek kullanıcı uyarısı eklendi.
- Uyarı ile ilişkili içerikleri saklayan bağlantı tablosu eklendi.
- XenForo Resource Manager için otomatik ResourceItem adaptörü eklendi.
- Kaynak filtreleme ve toplu yönetim sayfası eklendi.
- XFRM taşıma ve silme işlemleri resmi ResourceItem servislerine bağlandı.
- Üçüncü taraf eklentiler için genişletilebilir içerik adaptör API dokümante edildi.

## 0.7.0

- Toplu seçim, toplu moderasyon ve toplu düzenleme sistemi eklendi.

## 0.4.0

- Kategori gruplaması ve gelişmiş filtre sistemi eklendi.

## 0.3.0

- Kullanıcı içerik yönetim sayfası ve profil erişimi eklendi.

## 0.2.0

- İçerik adaptör mimarisi ve Thread adaptörü eklendi.

## 0.1.0

- XenForo 2.3 eklenti iskeleti oluşturuldu.
