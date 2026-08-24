# Değişiklik Geçmişi

## 1.0.2

- XenForo content type field export formatı düzeltildi.
- Geçersiz permission_groups geliştirme verisi kaldırıldı.
- Moderasyon izinleri XenForo çekirdeğinin generalModeratorPermissions arayüz grubuna taşındı.
- Kurulum ZIP doğrulaması güncellendi.

## 1.0.1

- İlk kurulum veri tanımları düzenlendi.
- Opsiyonel Resource Manager entegrasyonunun kurulum bağımlılığı kaldırıldı.

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
