# Kuaför Randevu Sistemi

Bu proje, 
-	##PHP Sunucu tarafı programlama dili olarak kullanılmıştır. Tüm dinamik işlemler, oturum yönetimi, form işlemleri ve veritabanı bağlantıları PHP ile yapılmış.
-	##MySQL
Veritabanı yönetim sistemi olarak kullanılmıştır. Randevu, kullanıcı, müşteri gibi veriler MySQL veritabanında saklanıyor.
-	##HTML
Web sayfalarının iskeletini oluşturmak için kullanılmıştır.
-	##CSS
Sayfa tasarımı ve stil vermek için kullanılmıştır. (ör: styles/styles.css, <style> blokları)
-	##JavaScript
Sayfa üzerinde dinamik işlemler ve etkileşimler için kullanılmıştır. (ör: sidebar açma/kapama işlemleri)
-	##Bootstrap (veya Bootstrap ikonları)
Arayüzde ikonlar ve bazı stil bileşenleri için kullanılmış olabilir (ör: bi bi-clock-history gibi class’lar). kullanılarak geliştirilmiş bir kuaför randevu sistemi örneğidir. Sistem, kullanıcıların giriş yapıp/kaydolup randevu almasına, randevuları görüntülemesine ve yönetmesine olanak sağlar.

## Özellikler

- Kullanıcılar hesap oluşturabilir veya giriş yapabilir.
- Kullanıcılar randevu alabilir ve güncel randevularını görüntüleyebilir.
- Kullanıcılar randevu taleplerini iptal edebilir.
- Kuaför salonu personeli randevuları güncelleyebilir veya iptal edebilir.

## Gereksinimler

- XAMPP [önerilen] veya herhangi bir web geliştirme bileşenlerini bir araya getiren benzer bir platform.

## Kurulum

1. Bu depoyu XAMPP dosyalarının içindeki ***htdocs*** dizinine giderek klonlayın: <br> `git clone git@github.com:bilalyarmaci/kuafor-randevu-sistemi.git`
2. XAMPP platformundan *Apache Web Server* ve *MySQL Database*'i çalıştırın.
3. *sqlDatabase.sql* dosyasındaki MySQL sorgusunu kopyalayın.
4. Tarayıcınızda `localhost/phpmyadmin` adresine gidin.
5. Üst menüdeki `📃SQL` kısmına kopyaladığınız MySQL sorgusunu yapıştırın ve kodu çalıştırın.

## Kullanım

1. Çalışır halde değilse XAMPP platformundan *Apache Web Server* ve *MySQL Database*'i çalıştırın.
2. Tarayıcınızda `localhost/randevu-sistemi/` adresine gidin.
3. Kayıtlı bir kullanıcı olarak giriş yapın veya yeni bir hesap oluşturun.
4. Randevu oluşturmak için uygun bir tarih ve saat seçin ve randevu alın.
5. Kuaför salonu personeli (admin), giriş yaptıktan sonra randevu taleplerini görüntüleyebilir ve güncelleme/iptal etme seçeneklerini kullanabilir.
6. Kullanıcılar, randevularını görüntüleyebilir ve gerektiğinde güncelleme/iptal işlemlerini gerçekleştirebilir.

## İletişim

Eğer herhangi bir sorunuz, öneriniz veya geri bildiriminiz varsa, lütfen iletişime geçmekten çekinmeyin. İletişim bilgilerini aşağıda bulabilirsiniz:

-   LinkedIn: [http://www.linkedin.com/in/yusuf-burkuk-390b4027a]
-   E-posta: [yusufbrkk12@gmail.com]
