# v1.0.0 QA Test Matrisi

## Kurulum

- XenForo 2.3.x üzerinde temiz kurulum.
- `xf_warext_ucm_action_log`, `xf_warext_ucm_warning_content`, `xf_warext_ucm_bulk_operation` tablolarının oluşması.
- 0.10.0 sürümünden 1.0.0 sürümüne yükseltmede yalnızca eksik bulk operation tablosunun eklenmesi.
- Kaldırmada üç Warext UCM tablosunun silinmesi.

## XenForo Thread

- Kullanıcı profilinden İçerikleri Yönet bağlantısı.
- Görünmeyen node içeriklerinin listelenmemesi.
- Kategori, forum, durum, kilit, önek, tarih, başlık, cevap ve görüntülenme filtreleri.
- Tekli, sayfa, kategori ve filtre sonucu seçimleri.
- Taşı, soft delete, hard delete, restore, lock/unlock, sticky/unsticky, approve/unapprove, prefix ve başlık işlemleri.
- Hard delete için ayrı permission ve açık onay.

## Resource Manager

- XFRM yokken kaynak sekmesinin görünmemesi ve eklentinin hatasız çalışmaya devam etmesi.
- XFRM varken kullanıcı kaynaklarının listelenmesi.
- Görünmeyen Resource Category içeriklerinin dışarıda kalması.
- Taşıma, silme, geri getirme, onay, önek ve başlık işlemleri.

## Büyük İşlemler

- 500 veya daha az içerikte request içi işlem.
- 501+ filtre/kategori sonucunda `xf_job` kuyruğuna geçiş.
- 100 öğelik varsayılan batch ilerlemesi.
- Cursor değerinin her içerikten sonra yükselmesi.
- Aynı operation için eş zamanlı ikinci worker'ın lock nedeniyle işlem yapmaması.
- Worker yeniden başladığında daha önce uygulanmış prepend/append işlemlerinin tekrarlanmaması.
- Moderatörün yetkisi iş sırasında kaldırıldığında operation'ın güvenli biçimde failed durumuna geçmesi.

## Kayıt ve Uyarı

- Değişiklik yapan işlemlerin action log tablosuna yazılması.
- XenForo moderator log entegrasyonu.
- Hard delete sonrasında özel action log kaydının içerik silinmiş olsa bile korunması.
- Tek warning kaydının birden çok thread ile ilişkilendirilebilmesi.

## Otomatik Doğrulama

GitHub Actions PHP 8.1 ve 8.4 üzerinde tüm PHP dosyalarını `php -l` ile kontrol eder, tüm JSON dosyalarını parse eder, `_data` XML dosyalarını parse eder ve PHP 8.4 job'ında kurulabilir ZIP artifact üretir.
