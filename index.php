<?php 
if(!file_exists('sistem/db.php')) { header("Location: install.php"); exit; }
include 'sistem/db.php'; 
?>
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Blog Dünyası</title>
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

        <section class="hero-section">
            <h2>Hoş Geldiniz!</h2>
            <p>Blog Sitesi yazılımınız başarıyla kurulmuştur. Tadını çıkarın!</p>
        </section>

        <div class="home-layout">
            
            <main class="main-content">
                <h2 class="section-title">Güncel Yazılar</h2>
                <div class="blog-grid">
                    <?php
                    // Ana sayfada sadece son 4 yazıyı gösteriyoruz
                    $query = $db->query("SELECT p.*, COUNT(c.id) as comment_count FROM posts p LEFT JOIN comments c ON p.id = c.post_id GROUP BY p.id ORDER BY p.created_at DESC LIMIT 4");
                    $posts = $query->fetchAll(PDO::FETCH_ASSOC);

                    if(count($posts) > 0):
                        foreach($posts as $row): 
                            $date = date("d.m.Y", strtotime($row['created_at']));
                    ?>
                        <div class="blog-card">
                            <?php if($row['image']): ?>
                                <img src="<?php echo $row['image']; ?>" class="blog-img" alt="Blog Görseli">
                            <?php else: ?>
                                <div class="blog-img" style="background: linear-gradient(45deg, var(--accent), var(--card));"></div>
                            <?php endif; ?>
                            <div class="blog-content-box">
                                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                <div style="font-size: 0.85em; color: var(--meta-text); margin-bottom: 10px;">
                                    📅 <?php echo $date; ?> | 💬 <?php echo $row['comment_count']; ?> Yorum
                                </div>
                                <p><?php echo substr(htmlspecialchars($row['content']), 0, 80); ?>...</p>
                                <a href="post.php?id=<?php echo $row['id']; ?>" class="read-more">Devamını Oku →</a>
                            </div>
                        </div>
                    <?php endforeach; else: echo "<p>Henüz yazı yok.</p>"; endif; ?>
                </div>
                <div class="btn-center-container">
                    <a href="bloglar.php" class="btn-theme" style="text-decoration:none; padding: 15px 35px; font-size: 1.1em;">Tüm Yazıları Gör →</a>
                </div>
            </main>

            <aside class="right-sidebar">
                <h3 class="sidebar-title">👥 Aramıza Yeni Katılanlar</h3>
                <ul class="new-user-list">
                    <?php
                    // En son kayıt olan 5 üyeyi çekiyoruz
                    $yeni_uyeler = $db->query("SELECT username, profile_pic, created_at, role FROM users ORDER BY id DESC LIMIT 5")->fetchAll();
                    foreach($yeni_uyeler as $uye):
                        $uye_avatar = $uye['profile_pic'] ? $uye['profile_pic'] : "https://ui-avatars.com/api/?name=".$uye['username']."&background=6c5ce7&color=fff";
                    ?>
                    <li class="new-user-item">
                        <img src="<?php echo $uye_avatar; ?>" class="new-user-avatar" alt="Avatar">
                        <div class="new-user-info">
                            <span class="new-user-name">
                                <?php echo htmlspecialchars($uye['username']); ?>
                                <?php if($uye['role'] == 'admin'): ?>
                                    <span style="font-size: 0.7em; background: #e74c3c; color: white; padding: 2px 5px; border-radius: 5px; margin-left: 5px;">Admin</span>
                                <?php elseif($uye['role'] == 'author'): ?>
                                    <span style="font-size: 0.7em; background: #2ecc71; color: white; padding: 2px 5px; border-radius: 5px; margin-left: 5px;">Yazar</span>
                                <?php endif; ?>
                            </span>
                            <span class="new-user-date">Kayıt: <?php echo date("d.m.Y", strtotime($uye['created_at'])); ?></span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
            
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Blog Dünyası. Tüm hakları saklıdır.</p>
        <p style="font-size: 0.8em; color: var(--meta-text);">Görkemli Tasarımlar ile kodlanmıştır.</p>
    </footer>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const target = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', target);
            localStorage.setItem('theme', target);
            document.getElementById('themeToggleBtn').innerHTML = target === 'dark' ? '🌙 Gece' : '☀️ Gündüz';
        }
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        document.getElementById('themeToggleBtn').innerHTML = savedTheme === 'dark' ? '🌙 Gece' : '☀️ Gündüz';
    </script>
</body>
</html>