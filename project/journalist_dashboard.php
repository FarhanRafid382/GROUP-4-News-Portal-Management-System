<?php
session_start();
require 'includes/db.php';
require 'includes/header.php';

// Debug mode for real errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only allow logged-in journalists
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'journalist') {
    die("Journalists Only");
}

// Fetch categories (FIXED)
// Make sure your SQL file contains: categories(category_id, category_name)
$cat_stmt = $pdo->query("SELECT category_id, category_name FROM categories");
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle new article submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_article'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = $_POST['category'];

    if ($title && $content && $category_id) {
        // Make table name consistent: article
        $stmt = $pdo->prepare("
            INSERT INTO article (title, content, publish_date, is_approved, journalist_id, category_id)
            VALUES (?, ?, NOW(), 0, ?, ?)
        ");
        $stmt->execute([$title, $content, $_SESSION['user_id'], $category_id]);
        $msg = "<p style='color:green'>Article submitted for approval.</p>";
    } else {
        $msg = "<p style='color:red'>All fields are required.</p>";
    }
}

// Fetch journalist's own articles
$articles = $pdo->prepare("
    SELECT a.*, c.category_name
    FROM article a
    JOIN categories c ON a.category_id = c.category_id
    WHERE a.journalist_id = ?
    ORDER BY a.publish_date DESC
");
$articles->execute([$_SESSION['user_id']]);
$my_articles = $articles->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="max-width:900px;margin:auto;">
<h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>

<?php if(isset($msg)) echo $msg; ?>

<h2>Write New Article</h2>
<form method="POST">
    <label>Title</label><br>
    <input type="text" name="title" required style="width:100%;"><br><br>

    <label>Category</label><br>
    <select name="category" required>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['category_id'] ?>">
                <?= htmlspecialchars($cat['category_name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Content</label><br>
    <textarea name="content" rows="10" required style="width:100%;"></textarea><br><br>

    <button type="submit" name="submit_article">Submit Article</button>
</form>

<hr>

<h2>My Articles</h2>

<?php if (count($my_articles) > 0): ?>
<table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
<tr>
    <th>Title</th>
    <th>Category</th>
    <th>Published</th>
    <th>Status</th>
</tr>

<?php foreach ($my_articles as $a): ?>
<tr>
    <td>
        <a href="article.php?id=<?= $a['article_id'] ?>" target="_blank">
            <?= htmlspecialchars($a['title']) ?>
        </a>
    </td>
    <td><?= htmlspecialchars($a['category_name']) ?></td>
    <td><?= $a['publish_date'] ?></td>
    <td>
        <?= $a['is_approved']
            ? '<span style="color:green">Approved</span>'
            : '<span style="color:orange">Pending</span>' ?>
    </td>
</tr>
<?php endforeach; ?>

</table>

<?php else: ?>
<p>You have not submitted any articles yet.</p>
<?php endif; ?>

</div>
</body>
</html>
