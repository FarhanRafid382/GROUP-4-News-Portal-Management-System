<?php
require 'includes/db.php';
require 'includes/header.php';

// Validate article ID
if (!isset($_GET['id'])) die("Article ID missing.");
$id = (int)$_GET['id'];

// Fetch article
$stmt = $pdo->prepare("
    SELECT a.*, j.journalist_name, c.category_name 
    FROM Article a 
    JOIN Journalists j ON a.journalist_id = j.journalist_id 
    JOIN Categories c ON a.category_id = c.category_id 
    WHERE a.article_id = ? AND a.is_approved = 1
");
$stmt->execute([$id]);
$article = $stmt->fetch();
if (!$article) die("Article not found.");

// Bookmark handling
$is_bookmarked = false;
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'reader') {
    $check = $pdo->prepare("SELECT bookmark_id FROM Bookmarks WHERE reader_id = ? AND article_id = ?");
    $check->execute([$_SESSION['user_id'], $id]);
    $bm = $check->fetch();
    if ($bm) $is_bookmarked = true;

    if (isset($_POST['toggle_bm'])) {
        if ($is_bookmarked) {
            $pdo->prepare("DELETE FROM Bookmarks WHERE bookmark_id = ?")->execute([$bm['bookmark_id']]);
        } else {
            $pdo->prepare("INSERT INTO Bookmarks (reader_id, article_id, bookmark_date) VALUES (?, ?, NOW())")
                ->execute([$_SESSION['user_id'], $id]);
        }
        header("Location: article.php?id=$id");
        exit;
    }
}

// Comment handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_btn'])) {
    if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'reader') {
        $text = trim($_POST['comment']);
        if ($text) {
            $pdo->prepare("INSERT INTO Comment (reader_id, article_id, comment_text, comment_date) VALUES (?, ?, ?, NOW())")
                ->execute([$_SESSION['user_id'], $id, $text]);
            header("Location: article.php?id=$id"); // Refresh to show comment
            exit;
        }
    }
}

// Escape article content
$title = htmlspecialchars($article['title']);
$journalist = htmlspecialchars($article['journalist_name']);
$category = htmlspecialchars($article['category_name']);
$content = nl2br(htmlspecialchars($article['content']));
$publish_date = $article['publish_date'];
?>

<h1><?php echo $title; ?></h1>
<p style="color:#777">By <?php echo $journalist; ?> | <?php echo $category; ?> | <?php echo $publish_date; ?></p>
<div style="font-size:1.1em; line-height:1.6; margin: 20px 0;"><?php echo $content; ?></div>

<?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'reader'): ?>
    <!-- Bookmark Button -->
    <form method="POST" style="margin-bottom:20px;">
        <button type="submit" name="toggle_bm" class="<?php echo $is_bookmarked ? 'btn-danger':'btn-success'; ?>">
            <?php echo $is_bookmarked ? 'Remove Bookmark' : 'Bookmark This'; ?>
        </button>
    </form>

    <!-- Comment Form -->
    <form method="POST" style="margin-bottom:30px;">
        <textarea name="comment" rows="3" placeholder="Write a comment..." required style="width:100%;"></textarea><br>
        <button type="submit" name="comment_btn">Post Comment</button>
    </form>
<?php endif; ?>

<hr>
<h3>Comments</h3>
<?php
// Fetch comments
$comments_stmt = $pdo->prepare("
    SELECT c.comment_text, c.comment_date, r.reader_name 
    FROM Comment c 
    JOIN Readers r ON c.reader_id = r.reader_id 
    WHERE c.article_id = ? 
    ORDER BY c.comment_date DESC
");
$comments_stmt->execute([$id]);
$comments = $comments_stmt->fetchAll();

if ($comments) {
    echo '<ul style="list-style:none; padding:0;">';
    foreach ($comments as $c) {
        echo '<li style="margin-bottom:15px; border-bottom:1px solid #ccc; padding-bottom:10px;">';
        echo '<strong>'.htmlspecialchars($c['reader_name']).'</strong> <span style="color:#777; font-size:0.9em;">('.$c['comment_date'].')</span>';
        echo '<p>'.nl2br(htmlspecialchars($c['comment_text'])).'</p>';
        echo '</li>';
    }
    echo '</ul>';
} else {
    echo '<p>No comments yet.</p>';
}
?>
