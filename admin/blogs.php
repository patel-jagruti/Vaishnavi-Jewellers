<?php
declare(strict_types=1);

/**
 * Admin Blogs Management
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/base_url.php';

ensure_session();
require_login();
require_admin();

$pdo = db();
$action = $_GET['action'] ?? 'list';
$notice = '';

// Ensure blogs table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS blogs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255),
  excerpt VARCHAR(500),
  content TEXT,
  image VARCHAR(500),
  author VARCHAR(255),
  category VARCHAR(100),
  tags VARCHAR(255),
  is_published TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Handle add blog
if ($action === 'add' && !empty($_POST['title'])) {
  $title = trim($_POST['title']);
  $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
  $excerpt = trim($_POST['excerpt'] ?? '');
  $content = trim($_POST['content'] ?? '');
  $image = trim($_POST['image'] ?? '');
  $author = trim($_POST['author'] ?? 'Admin');
  $category = trim($_POST['category'] ?? 'General');
  $tags = trim($_POST['tags'] ?? '');
  $is_published = isset($_POST['is_published']) ? 1 : 0;

  $stmt = $pdo->prepare("INSERT INTO blogs (title, slug, excerpt, content, image, author, category, tags, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([$title, $slug, $excerpt, $content, $image, $author, $category, $tags, $is_published]);
  $notice = 'Blog post added successfully.';
  $action = 'list';
}

// Handle update
if ($action === 'update' && !empty($_GET['id']) && !empty($_POST['title'])) {
  $id = (int)$_GET['id'];
  $title = trim($_POST['title']);
  $excerpt = trim($_POST['excerpt'] ?? '');
  $content = trim($_POST['content'] ?? '');
  $image = trim($_POST['image'] ?? '');
  $author = trim($_POST['author'] ?? 'Admin');
  $category = trim($_POST['category'] ?? 'General');
  $tags = trim($_POST['tags'] ?? '');
  $is_published = isset($_POST['is_published']) ? 1 : 0;

  $stmt = $pdo->prepare("UPDATE blogs SET title = ?, excerpt = ?, content = ?, image = ?, author = ?, category = ?, tags = ?, is_published = ? WHERE id = ?");
  $stmt->execute([$title, $excerpt, $content, $image, $author, $category, $tags, $is_published, $id]);
  $notice = 'Blog post updated successfully.';
  $action = 'list';
}

// Handle toggle
if ($action === 'toggle' && !empty($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("UPDATE blogs SET is_published = NOT is_published WHERE id = ?");
  $stmt->execute([$id]);
  $notice = 'Blog status toggled.';
  $action = 'list';
}

// Handle delete
if ($action === 'delete' && !empty($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
  $stmt->execute([$id]);
  $notice = 'Blog post deleted successfully.';
}

// Fetch blogs
$blogs = $pdo->query('SELECT * FROM blogs ORDER BY id DESC LIMIT 20')->fetchAll();

// Get single blog for edit
$editBlog = null;
if ($action === 'edit' && !empty($_GET['id'])) {
  $stmtEdit = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
  $stmtEdit->execute([(int)$_GET['id']]);
  $editBlog = $stmtEdit->fetch();
}

$stats = [
  'total' => (int)$pdo->query('SELECT COUNT(*) FROM blogs')->fetchColumn(),
  'published' => (int)$pdo->query('SELECT COUNT(*) FROM blogs WHERE is_published = 1')->fetchColumn(),
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Blogs | Admin Panel</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/visnavi%20jwallers/assets/css/admin-premium.css" />
  <style>
    .blog-excerpt { max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:#666; }
    .badge-published { background: #e8f5e9; color: #2e7d32; }
    .badge-draft { background: rgba(107,30,45,0.1); color: var(--maroon); }
  </style>
</head>
<body>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <div class="brand">Vaishnavi Panel</div>
      <nav>
        <a href="/visnavi%20jwallers/admin/"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="/visnavi%20jwallers/admin/products.php"><i class="fas fa-gem"></i> Products</a>
        <a href="/visnavi%20jwallers/admin/orders.php"><i class="fas fa-shopping-bag"></i> Orders</a>
        <a href="/visnavi%20jwallers/admin/categories.php"><i class="fas fa-tags"></i> Categories</a>
        <a href="/visnavi%20jwallers/admin/users.php"><i class="fas fa-users"></i> Customers</a>
        <a href="/visnavi%20jwallers/admin/banners.php"><i class="fas fa-images"></i> Banners</a>
        <a href="/visnavi%20jwallers/admin/blogs.php" class="active"><i class="fas fa-newspaper"></i> Blogs</a>
        <a href="/visnavi%20jwallers/admin/settings.php"><i class="fas fa-cog"></i> Settings</a>
        <a href="/visnavi%20jwallers/index.php"><i class="fas fa-store"></i> Back to Store</a>
      </nav>
    </aside>

    <main class="admin-main">
      <h1>Blog Posts</h1>

      <?php if ($notice): ?>
        <div style="background:#d4edda; padding:12px; border-radius:4px; margin-bottom:16px;"><?= h($notice) ?></div>
      <?php endif; ?>

      <div class="admin-stats">
        <div class="stat-card">
          <div class="value"><?= $stats['total'] ?></div>
          <div class="label">Total Posts</div>
        </div>
        <div class="stat-card">
          <div class="value"><?= $stats['published'] ?></div>
          <div class="label">Published</div>
        </div>
      </div>

      <?php if ($action === 'edit' && $editBlog): ?>
        <div class="admin-section">
          <h2>Edit Blog Post</h2>
          <form method="post" action="?action=update&id=<?= (int)$editBlog['id'] ?>">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; max-width:800px;">
              <div style="grid-column:span 2;">
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Title</label>
                <input type="text" name="title" class="input" value="<?= h((string)$editBlog['title']) ?>" required>
              </div>
              <div>
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Category</label>
                <input type="text" name="category" class="input" value="<?= h((string)$editBlog['category']) ?>">
              </div>
              <div>
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Author</label>
                <input type="text" name="author" class="input" value="<?= h((string)$editBlog['author']) ?>">
              </div>
              <div style="grid-column:span 2;">
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Excerpt</label>
                <input type="text" name="excerpt" class="input" value="<?= h((string)$editBlog['excerpt']) ?>">
              </div>
              <div style="grid-column:span 2;">
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Image URL</label>
                <input type="text" name="image" class="input" value="<?= h((string)$editBlog['image']) ?>">
              </div>
              <div style="grid-column:span 2;">
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Content</label>
                <textarea name="content" class="input" style="min-height:160px; resize:vertical;"><?= h((string)$editBlog['content']) ?></textarea>
              </div>
              <div>
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Tags (comma separated)</label>
                <input type="text" name="tags" class="input" value="<?= h((string)$editBlog['tags']) ?>">
              </div>
              <div style="display:flex; align-items:center; gap:8px; margin-top:28px;">
                <input type="checkbox" name="is_published" id="edit_publish" style="width:16px; height:16px; cursor:pointer;" <?= $editBlog['is_published'] ? 'checked' : '' ?>> 
                <label for="edit_publish" style="font-size:13px; font-weight:600; color:#555; cursor:pointer;">Publish Immediately</label>
              </div>
              <div style="grid-column:span 2; margin-top:10px;">
                <button type="submit" class="btn">Update Post</button>
                <a class="btn btn-secondary" href="/visnavi%20jwallers/admin/blogs.php" style="margin-left:8px;">Cancel</a>
              </div>
            </div>
          </form>
        </div>
      <?php elseif ($action === 'add'): ?>
        <div class="admin-section">
          <h2>Add Blog Post</h2>
          <form method="post" action="?action=add">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; max-width:800px;">
              <div style="grid-column:span 2;">
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Title</label>
                <input type="text" name="title" class="input" placeholder="Blog Post Title" required>
              </div>
              <div>
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Category</label>
                <input type="text" name="category" class="input" placeholder="e.g. Jewellery Care">
              </div>
              <div>
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Author</label>
                <input type="text" name="author" class="input" value="Admin">
              </div>
              <div style="grid-column:span 2;">
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Excerpt</label>
                <input type="text" name="excerpt" class="input" placeholder="Short description...">
              </div>
              <div style="grid-column:span 2;">
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Image URL</label>
                <input type="text" name="image" class="input" placeholder="/assets/images/...">
              </div>
              <div style="grid-column:span 2;">
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Content</label>
                <textarea name="content" class="input" style="min-height:160px; resize:vertical;" placeholder="Blog content..."></textarea>
              </div>
              <div>
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Tags</label>
                <input type="text" name="tags" class="input" placeholder="gold, care, tips">
              </div>
              <div style="display:flex; align-items:center; gap:8px; margin-top:28px;">
                <input type="checkbox" name="is_published" id="add_publish" style="width:16px; height:16px; cursor:pointer;"> 
                <label for="add_publish" style="font-size:13px; font-weight:600; color:#555; cursor:pointer;">Publish Immediately</label>
              </div>
              <div style="grid-column:span 2; margin-top:10px;">
                <button type="submit" class="btn">Add Post</button>
                <a class="btn btn-secondary" href="/visnavi%20jwallers/admin/blogs.php" style="margin-left:8px;">Cancel</a>
              </div>
            </div>
          </form>
        </div>
      <?php else: ?>
        <div class="admin-section">
          <p><a class="btn" href="?action=add"><i class="fas fa-plus"></i> Add New Blog Post</a></p>
          <table>
            <thead>
              <tr>
                <th>Image Preview</th>
                <th>Title</th>
                <th>Category</th>
                <th>Author</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($blogs as $b): ?>
                <tr>
                  <td>
                    <?php if (!empty($b['image'])): ?>
                      <img src="<?= h(asset((string)$b['image'])) ?>" alt="" style="width:100px; height:50px; object-fit:cover; border-radius:6px; border:1px solid #eee;">
                    <?php else: ?>
                      <span style="color:#999; font-size:12px;"><i class="fas fa-image"></i> No image</span>
                    <?php endif; ?>
                  </td>
                  <td style="font-weight:600; color:var(--maroon);"><?= h((string)$b['title']) ?></td>
                  <td style="font-size:13px; color:#666;"><?= h((string)$b['category']) ?></td>
                  <td><?= h((string)$b['author']) ?></td>
                  <td>
                    <span class="badge <?= $b['is_published'] ? 'badge-published' : 'badge-draft' ?>">
                      <?= $b['is_published'] ? 'Published' : 'Draft' ?>
                    </span>
                  </td>
                  <td><?= date('d M Y', strtotime($b['created_at'])) ?></td>
                  <td>
                    <div class="actions-cell">
                      <a class="btn btn-sm" href="?action=toggle&id=<?= (int)$b['id'] ?>" style="min-width: 80px;"><?= $b['is_published'] ? 'Unpublish' : 'Publish' ?></a>
                      <a class="btn btn-sm btn-secondary" href="?action=edit&id=<?= (int)$b['id'] ?>"><i class="fas fa-edit"></i> Edit</a>
                      <a class="btn btn-sm btn-danger" href="?action=delete&id=<?= (int)$b['id'] ?>" onclick="return confirm('Delete post?')"><i class="fas fa-trash"></i> Delete</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <?php if (empty($blogs)): ?>
            <p style="padding:40px; text-align:center; color:#888; font-size:14px;"><i class="fas fa-info-circle"></i> No blog posts yet. Add one above.</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>