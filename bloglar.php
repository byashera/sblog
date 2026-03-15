<?php 
if(!file_exists('sistem/db.php')) { header("Location: install.php"); exit; }
include 'sistem/db.php'; 
?>
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Tüm Bloglar - Blog Dünyası</title>
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

        <h2 class="section-title">Görkemli Blog Arşivi</h2>
        <div class="blog-grid" style="margin-bottom: 50px;">
            <?php
            // LIMIT olmadan hepsini çekiyoruz
            $query = $db->query("SELECT p.*, COUNT(c.id) as comment_count FROM posts p LEFT JOIN comments c ON p.id = c.post_id GROUP BY p.id ORDER BY p.created_at DESC");
            $posts = $query->fetchAll(PDO::FETCH_ASSOC);

            if(count($posts) > 0):
                foreach($posts as $row): 
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
                            📅 <?php echo date("d.m.Y", strtotime($row['created_at'])); ?> | 💬 <?php echo $row['comment_count']; ?> Yorum
                        </div>
                        <p><?php echo substr(htmlspecialchars($row['content']), 0, 100); ?>...</p>
                        <a href="post.php?id=<?php echo $row['id']; ?>" class="read-more">Devamını Oku →</a>
                    </div>
                </div>
            <?php endforeach; else: echo "<p>Henüz yazı yok.</p>"; endif; ?>
        </div>
    </div>
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'dark');</script>
</body>
</html>