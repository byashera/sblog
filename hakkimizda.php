<?php 
if(!file_exists('sistem/db.php')) { header("Location: install.php"); exit; }
include 'sistem/db.php'; 
?>
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Hakkımızda - Blog Dünyası</title>
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
                <a href="iletisim.php">İletişim</a>
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

        <div style="background: var(--card); padding: 50px; border-radius: 20px; box-shadow: 0 5px 25px rgba(0,0,0,0.05); max-width: 800px; margin: 40px auto; text-align: center;">
            <h1 style="color: var(--accent); margin-top: 0;">Biz Kimiz?</h1>
            <p style="font-size: 1.2em; line-height: 1.8; color: var(--text);">
                Merhaba! Blog sitemize hoş geldiniz.<br><br>
                Sizin için en güzel yazıları en iyi yazarlarımızla sunuyoruz.<br>
                En güzel site scriptiyle bunun tadını çıkarın.
            </p>
        </div>
    </div>
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'dark');</script>
</body>
</html>