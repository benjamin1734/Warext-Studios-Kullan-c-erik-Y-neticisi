# Güvenlik

Warext Studios | Kullanıcı İçerik Yöneticisi, XenForo'nun native içerik ve node permission kontrollerini aşmamalıdır.

## Temel güvenlik ilkeleri

- Tüm değiştirici işlemler POST üzerinden yürütülür ve XenForo CSRF korumasına tabidir.
- Her içerik toplu işlem öncesinde kendi `can*` izinleriyle yeniden doğrulanır.
- Kalıcı silme ayrı `warextUcm/hardDelete` izni ve açık onay gerektirir.
- Büyük Job işlemleri başlatan moderatörün güncel izinleriyle tekrar çalışır.
- XFRM kurulu değilse Resource adaptörü kullanılmaz.
- Kullanıcı tarafından gönderilen thread/resource ID değerleri hedef kullanıcı ve görünür container kapsamıyla sınırlandırılır.
- 500 üzeri işlemler request içinde çalıştırılmaz; XenForo Job sistemiyle batch edilir.
- Background operation aynı anda yalnızca tek worker tarafından işlenir.

Güvenlik açığı tespit edildiğinde herkese açık issue içinde exploit ayrıntısı paylaşmak yerine repo sahibiyle özel kanaldan iletişim kurulmalıdır.
