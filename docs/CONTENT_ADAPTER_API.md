# Warext Studios | Kullanıcı İçerik Yöneticisi - İçerik Adaptör API

Üçüncü taraf eklentiler kendi içerik türlerini ana eklenti kodunu değiştirmeden sisteme bağlayabilir.

`xf_content_type_field` sisteminde `warext_ucm_handler_class` alanını kendi adaptör sınıfınıza bağlayın. Adaptör sınıfı `WarextStudios\UserContentManager\Content\AbstractHandler` sınıfını genişletmelidir.

Adaptör; entity short name, sahip kullanıcı alanı, içerik ID, kategori/container ID, başlık, durum, URL, desteklenen işlemler ve her işlem için kaynak eklentinin gerçek izin kontrolünü sağlamalıdır.

Bağımlı eklentiniz yoksa `isAvailable()` metodu `false` döndürmelidir. Böylece kayıtlı içerik türü otomatik olarak devre dışı kalır.

Örnek:

```xml
<field content_type="market_listing" field_name="warext_ucm_handler_class" field_value="Vendor\Addon\Content\Listing"/>
```

Toplu işlemlerde kaynak eklentinin resmi service/handler yapısını kullanın. Doğrudan tablo güncellemesiyle sayaç, izin veya lifecycle mekanizmalarını atlamayın.
