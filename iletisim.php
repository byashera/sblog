<?php include 'sistem/db.php'; ?>
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>İletişim - Blog Dünyası</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1 style="margin: 0; color: var(--accent);"><?php echo htmlspecialchars($ayar_site_adi); ?></h1>
            <nav class="nav-links">
                <a href="index.php">Ana Sayfa</a>
                <a href="bloglar.php">Bloglar</a>
                <a href="hakkimizda.php">Hakkımızda</a>
                <a href="iletisim.php" style="color: var(--accent);">İletişim</a>
            </nav>
            <div style="display: flex; align-items: center;">
                <button id="themeToggleBtn" class="btn-theme" onclick="toggleTheme()">🌙 Gece</button>
                <?php if(isset($_SESSION['user_id'])): 
                    $avatar = $_SESSION['profile_pic'] ? $_SESSION['profile_pic'] : "https://ui-avatars.com/api/?name=".$_SESSION['username']."&background=6c5ce7&color=fff";
                ?>
                    <div class="profile-menu">
                        <img src="<?php echo $avatar; ?>" class="profile-pic" alt="Profil">
                        <div class="dropdown-content">
                            <div style="padding: 15px; border-bottom: 1px solid var(--border); font-weight: bold; color: var(--accent);">Merhaba, <?php echo $_SESSION['username']; ?></div>
                            <a href="sistem/profile.php">⚙️ Üye Ayarları</a>
                            <?php if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'author'): ?>
                                <a href="sistem/admin.php">🛡️ Yönetim Paneli</a>
                            <?php endif; ?>
                            <a href="sistem/logout.php" style="color: #e74c3c;">🚪 Çıkış Yap</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="sistem/login.php" class="btn-theme" style="text-decoration:none; margin-left: 10px;">Giriş Yap</a>
                <?php endif; ?>
            </div>
        </header>

        <div class="auth-wrapper" style="min-height: 60vh;">
            <div class="auth-box" style="max-width: 600px;">
                <h2 style="color: var(--accent); text-align: center; margin-top: 0;">Benimle İletişime Geçin</h2>
                <p style="text-align: center; color: var(--meta-text); margin-bottom: 30px;">Görüş, öneri veya proje fikirleriniz için aşağıdaki formu doldurabilirsiniz.</p>
                
                <div id="form-status"></div> <form id="contact-form" action="<?php echo $ayar_formspree; ?>" method="POST">
                    <label>Adınız Soyadınız:</label>
                    <input type="text" name="name" placeholder="Adınızı girin..." required>
                    
                    <label>E-Posta Adresiniz:</label>
                    <input type="email" name="email" placeholder="ornek@mail.com" required>
                    
                    <label>Mesajınız:</label>
                    <textarea name="message" rows="5" placeholder="Mesajınızı buraya yazın..." required></textarea>
                    
                    <button type="submit" class="btn-theme" style="width: 100%; padding: 15px; font-size: 1.1em; margin-top: 10px;">Mesajı Gönder</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Tema Ayarları
        document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'dark');
        function toggleTheme() {
            const html = document.documentElement;
            const target = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', target);
            localStorage.setItem('theme', target);
            document.getElementById('themeToggleBtn').innerHTML = target === 'dark' ? '🌙 Gece' : '☀️ Gündüz';
        }

        // Formspree AJAX Gönderimi (Sayfa yönlendirmesini engeller)
        var form = document.getElementById("contact-form");
        var statusMsg = document.getElementById("form-status");

        form.addEventListener("submit", async function(event) {
            event.preventDefault();
            var data = new FormData(event.target);
            fetch(event.target.action, {
                method: form.method,
                body: data,
                headers: { 'Accept': 'application/json' }
            }).then(response => {
                if (response.ok) {
                    statusMsg.innerHTML = '<div class="alert alert-success">Mesajınız başarıyla iletildi!</div>';
                    form.reset();
                } else {
                    statusMsg.innerHTML = '<div class="alert alert-error">Mesaj gönderilirken bir hata oluştu.</div>';
                }
            }).catch(error => {
                statusMsg.innerHTML = '<div class="alert alert-error">Beklenmeyen bir ağ hatası oluştu.</div>';
            });
        });
    </script>
</body>
</html>