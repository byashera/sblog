<?php
error_reporting(0); // Kurulum sırasında gereksiz uyarıları gizle
$adim = isset($_GET['adim']) ? $_GET['adim'] : 1;
$hata = "";

// Eğer zaten kuruluysa ana sayfaya at
if(file_exists('sistem/db.php')) {
    header("Location: index.php");
    exit;
}

if($_POST && $adim == 2) {
    $db_host = $_POST['db_host'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $db_name = $_POST['db_name'];
    $site_name = $_POST['site_name'];
    $formspree = $_POST['formspree'];
    $admin_user = $_POST['admin_user'];
    $admin_pass = password_hash($_POST['admin_pass'], PASSWORD_DEFAULT);

    try {
        // Önce DB seçmeden sunucuya bağlan
        $pdo = new PDO("mysql:host=$db_host;charset=utf8", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Veritabanını oluştur ve seç
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8 COLLATE utf8_general_ci");
        $pdo->exec("USE `$db_name`");

        // Tabloları Oluştur
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'author', 'user') DEFAULT 'user',
            profile_pic VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            author_id INT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT,
            user_id INT,
            comment_text TEXT,
            is_reported TINYINT(1) DEFAULT 0,
            reported_by INT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");

        // Admin Hesabını Ekle
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
        $stmt->execute([$admin_user, $admin_pass]);

        // sistem/db.php Dosyasını Üret
        $db_icerik = "<?php
\$host = '$db_host';
\$user = '$db_user';
\$pass = '$db_pass';
\$db_name = '$db_name';
\$ayar_site_adi = '$site_name';
\$ayar_formspree = '$formspree';

try {
    \$db = new PDO(\"mysql:host=\$host;dbname=\$db_name;charset=utf8\", \$user, \$pass);
    \$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException \$e) {
    die(\"Veritabanı bağlantı hatası\");
}
if (session_status() == PHP_SESSION_NONE) { session_start(); }
?>";
        // Klasör yoksa oluştur
        if(!is_dir('sistem')) { mkdir('sistem', 0777, true); }
        if(!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        
        file_put_contents('sistem/db.php', $db_icerik);
        
        header("Location: install.php?adim=3");
        exit;

    } catch(PDOException $e) {
        $hata = "Veritabanı bağlantı hatası: Bilgilerinizi kontrol edin. (" . $e->getMessage() . ")";
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Görkemli Blog - Kurulum Sihirbazı</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .install-box { max-width: 600px; margin: 50px auto; background: var(--card); padding: 40px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); border-top: 5px solid var(--accent); }
        .install-box h2 { color: var(--accent); text-align: center; margin-top:0; }
        .install-box p { text-align: center; color: var(--meta-text); margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; }
        .form-group input { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg); color: var(--text); box-sizing: border-box; }
        .section-title { border-bottom: 1px solid var(--border); padding-bottom: 10px; margin-bottom: 20px; margin-top: 30px; color: var(--text); }
        .step-indicator { display: flex; justify-content: center; gap: 10px; margin-bottom: 30px; }
        .step-dot { width: 30px; height: 30px; border-radius: 50%; background: var(--bg); display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid var(--border); }
        .step-active { background: var(--accent); color: white; border-color: var(--accent); }
    </style>
</head>
<body>
    <div class="container">
        <div class="install-box">
            
            <div class="step-indicator">
                <div class="step-dot <?php echo $adim==1 ? 'step-active':''; ?>">1</div>
                <div class="step-dot <?php echo $adim==2 ? 'step-active':''; ?>">2</div>
                <div class="step-dot <?php echo $adim==3 ? 'step-active':''; ?>">3</div>
            </div>

            <?php if($hata): ?>
                <div class="alert alert-error"><?php echo $hata; ?></div>
            <?php endif; ?>

            <?php if($adim == 1): ?>
                <h2>Hoş Geldiniz!</h2>
                <p>Görkemli Blog altyapısını kurmaya hazır mısınız? Sadece birkaç adımda kendi profesyonel blog sitenizi yayına alacaksınız.</p>
                <div style="background: var(--bg); padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9em;">
                    <strong>Gereksinimler:</strong><br>
                    - MySQL Veritabanı Bilgileri<br>
                    - Sistem Klasörlerine Yazma İzni<br>
                    - Formspree İletişim Kodu (İsteğe bağlı)
                </div>
                <a href="?adim=2" class="btn-theme" style="display: block; text-align: center; text-decoration: none; padding: 15px;">Kuruluma Başla →</a>

            <?php elseif($adim == 2): ?>
                <h2>Sistem Ayarları</h2>
                <p>Lütfen veritabanı ve yönetici bilgilerinizi eksiksiz doldurun.</p>
                <form method="POST" action="?adim=2">
                    
                    <h3 class="section-title">Genel Ayarlar</h3>
                    <div class="form-group"><label>Site Adı:</label><input type="text" name="site_name" value="Blog Dünyası" required></div>
                    <div class="form-group"><label>Formspree URL (İletişim Formu İçin):</label><input type="url" name="formspree" placeholder="https://formspree.io/f/XXXXX" required></div>

                    <h3 class="section-title">Veritabanı Ayarları</h3>
                    <div class="form-group"><label>Veritabanı Sunucusu:</label><input type="text" name="db_host" value="localhost" required></div>
                    <div class="form-group"><label>Veritabanı Kullanıcı Adı:</label><input type="text" name="db_user" value="root" required></div>
                    <div class="form-group"><label>Veritabanı Şifresi:</label><input type="text" name="db_pass"></div>
                    <div class="form-group"><label>Oluşturulacak Veritabanı Adı:</label><input type="text" name="db_name" value="blog_sistemi" required></div>

                    <h3 class="section-title">Yönetici (Admin) Hesabı</h3>
                    <div class="form-group"><label>Admin Kullanıcı Adı:</label><input type="text" name="admin_user" placeholder="admin" required></div>
                    <div class="form-group"><label>Admin Şifresi:</label><input type="password" name="admin_pass" required></div>

                    <button type="submit" class="btn-theme" style="width: 100%; padding: 15px; font-size: 1.1em; margin-top: 10px;">Sistemi Kur ve Veritabanını Oluştur</button>
                </form>

            <?php elseif($adim == 3): ?>
                <h2 style="color: #2ecc71;">Tebrikler! Kurulum Tamamlandı.</h2>
                <p>Veritabanınız başarıyla oluşturuldu, tablolar eklendi ve admin hesabınız tanımlandı.</p>
                <div style="background: rgba(231, 76, 60, 0.1); border: 1px solid #e74c3c; color: #e74c3c; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9em; text-align: left;">
                    <strong>⚠️ Güvenlik Uyarısı:</strong> Güvenliğiniz için kurulum tamamlandıktan sonra ana dizindeki <code>install.php</code> dosyasını silmeniz şiddetle tavsiye edilir!
                </div>
                <a href="index.php" class="btn-theme" style="display: block; text-align: center; text-decoration: none; padding: 15px;">Siteye Git ve Giriş Yap →</a>
            <?php endif; ?>

        </div>
    </div>
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'dark');</script>
</body>
</html>