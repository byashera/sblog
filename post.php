<?php
if(!file_exists('sistem/db.php')) { header("Location: install.php"); exit;
include 'sistem/db.php';
if(!isset($_GET['id'])) { header("Location: index.php"); exit; }
$post_id = $_GET['id'];

$postQuery = $db->prepare("SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.id = ?");
$postQuery->execute([$post_id]);
$post = $postQuery->fetch(PDO::FETCH_ASSOC);
if(!$post) { die("Yazı bulunamadı."); }

// Yorumları çekerken kullanıcının rolünü de (u.role) alıyoruz
$comQuery = $db->prepare("SELECT c.*, u.username, u.profile_pic, u.role FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.id DESC");
$comQuery->execute([$post_id]);
$comments = $comQuery->fetchAll();
$comment_count = count($comments);
?>
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .post-cover { width: 100%; height: 400px; object-fit: cover; border-radius: 15px; margin-bottom: 30px; }
        .comment-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .comment-user-info { display: flex; align-items: center; gap: 10px; }
        .comment-avatar { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; }
        .btn-action { padding: 5px 10px; border-radius: 5px; text-decoration: none; font-size: 0.8em; }
        .btn-report { background: #f39c12; color: white; }
        .btn-delete-sm { background: #e74c3c; color: white; }
        .role-badge { font-size: 0.7em; padding: 2px 8px; border-radius: 10px; color: white; margin-left: 5px; vertical-align: middle; }
        .role-admin { background-color: #e74c3c; }
        .role-author { background-color: #2ecc71; }
        .role-user { background-color: var(--meta-text); }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1 style="margin: 0; color: var(--accent);"><?php echo htmlspecialchars($ayar_site_adi); ?></h1>
            <div>
                <button id="themeToggleBtn" class="btn-theme" onclick="toggleTheme()">🌙 Gece Modu</button>
                <?php if(isset($_SESSION['user_id'])): 
                    $avatar = $_SESSION['profile_pic'] ? $_SESSION['profile_pic'] : "https://ui-avatars.com/api/?name=".$_SESSION['username']."&background=6c5ce7&color=fff";
                ?>
                    <div class="profile-menu">
                        <img src="<?php echo $avatar; ?>" class="profile-pic" alt="Profil">
                        <div class="dropdown-content">
                            <div style="padding: 15px; border-bottom: 1px solid var(--border); font-weight: bold; color: var(--accent);">
                                Merhaba, <?php echo $_SESSION['username']; ?>
                            </div>
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

        <?php 
        if(isset($_GET['bildirim'])) {
            if($_GET['bildirim'] == 'yorum_eklendi') echo '<div class="alert alert-success">Yorumunuz başarıyla eklendi!</div>';
            if($_GET['bildirim'] == 'yorum_bildirildi') echo '<div class="alert alert-success" style="background:rgba(243, 156, 18, 0.1); color:#f39c12; border-color:#f39c12;">Yorum yöneticilere bildirildi. Teşekkürler!</div>';
        }
        ?>

        <div style="margin-bottom: 20px;">
            <a href="index.php" class="btn-theme" style="text-decoration:none; display:inline-block;">← Siteye Geri Dön</a>
        </div>

        <div style="background:var(--card); padding:40px; border-radius:20px; box-shadow: 0 5px 25px rgba(0,0,0,0.05); margin-bottom:30px;">
            <?php if($post['image']): ?>
                <img src="<?php echo $post['image']; ?>" class="post-cover" alt="Blog Görseli">
            <?php endif; ?>

            <h1 style="color:var(--accent); font-size: 2.5em; margin-top:0;"><?php echo htmlspecialchars($post['title']); ?></h1>
            <div style="color:var(--meta-text); margin-bottom: 25px; border-bottom:1px solid var(--border); padding-bottom:15px;">
                ✍️ Yazar: <b><?php echo $post['author_name'] ? $post['author_name'] : 'Yönetici'; ?></b> | 
                📅 <?php echo date("d.m.Y H:i", strtotime($post['created_at'])); ?> | 
                💬 Toplam <?php echo $comment_count; ?> Yorum
            </div>
            
            <div style="font-size: 1.15em; line-height: 1.9;">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>
        </div>

        <div style="background:var(--card); padding:30px; border-radius:20px;">
            <h3>Yorumlar (<?php echo $comment_count; ?>)</h3>
            <?php
            if($comment_count > 0):
                foreach($comments as $c): 
                    $c_avatar = $c['profile_pic'] ? $c['profile_pic'] : "https://ui-avatars.com/api/?name=".$c['username']."&background=6c5ce7&color=fff";
                    
                    // Rol Etiketini Belirleme
                    if($c['role'] == 'admin') { $rol_isim = 'Yönetici'; $rol_class = 'role-admin'; }
                    elseif($c['role'] == 'author') { $rol_isim = 'Blog Yazarı'; $rol_class = 'role-author'; }
                    else { $rol_isim = 'Üye'; $rol_class = 'role-user'; }
            ?>
                    <div style="background:var(--bg); padding:20px; border-radius:15px; margin-bottom:15px; border-left:4px solid var(--accent);">
                        <div class="comment-header">
                            <div class="comment-user-info">
                                <img src="<?php echo $c_avatar; ?>" class="comment-avatar">
                                <div>
                                    <div style="font-weight:bold; color:var(--accent);">
                                        <?php echo htmlspecialchars($c['username']); ?>
                                        <span class="role-badge <?php echo $rol_class; ?>"><?php echo $rol_isim; ?></span>
                                    </div>
                                    <div style="font-size:0.8em; color:var(--meta-text);"><?php echo date("d.m.Y H:i", strtotime($c['created_at'])); ?></div>
                                </div>
                            </div>
                            
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <div>
                                    <?php if($_SESSION['role'] == 'admin'): ?>
                                        <a href="sistem/islem.php?durum=yorum_sil&id=<?php echo $c['id']; ?>&post_id=<?php echo $post_id; ?>" class="btn-action btn-delete-sm" onclick="return confirm('Silinsin mi?');">🗑️ Sil</a>
                                    <?php endif; ?>
                                    
                                    <?php if($c['user_id'] != $_SESSION['user_id'] && $c['is_reported'] == 0): ?>
                                        <a href="sistem/islem.php?durum=yorum_bildir&id=<?php echo $c['id']; ?>&post_id=<?php echo $post_id; ?>" class="btn-action btn-report" onclick="return confirm('Şikayet etmek istiyor musunuz?');">⚠️ Bildir</a>
                                    <?php elseif($c['is_reported'] == 1): ?>
                                        <span style="font-size:0.8em; color:#f39c12;">Bildirildi</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="margin-top: 10px;"><?php echo htmlspecialchars($c['comment_text']); ?></div>
                    </div>
            <?php endforeach; else: echo "<p style='color:var(--meta-text);'>İlk görkemli yorumu sen yap!</p>"; endif; ?>

            <?php if(isset($_SESSION['user_id'])): ?>
                <h4 style="margin-top:30px;">Yorum Ekle</h4>
                <form action="sistem/islem.php?durum=yorum_ekle" method="POST">
                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                    <textarea name="comment" rows="4" placeholder="Düşüncelerini paylaş..." required style="border-radius: 10px;"></textarea>
                    <button type="submit" class="btn-theme">Gönder</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const target = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', target);
            localStorage.setItem('theme', target);
            document.getElementById('themeToggleBtn').innerHTML = target === 'dark' ? '🌙 Gece Modu' : '☀️ Gündüz Modu';
        }
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        document.getElementById('themeToggleBtn').innerHTML = savedTheme === 'dark' ? '🌙 Gece Modu' : '☀️ Gündüz Modu';
    </script>
</body>
</html>