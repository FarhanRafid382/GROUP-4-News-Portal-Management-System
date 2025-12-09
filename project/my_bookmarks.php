<?php require 'includes/db.php'; ?>
<?php require 'includes/header.php'; ?>
<?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'reader') die("Access Denied"); ?>

<h1>My Bookmarks</h1>

<?php
$stmt = $pdo->prepare("
    SELECT a.*, j.journalist_name 
    FROM Bookmarks b 
    JOIN Article a ON b.article_id = a.article_id 
    JOIN Journalists j ON a.journalist_id = j.journalist_id 
    WHERE b.reader_id = ?
    ORDER BY b.bookmark_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$rows = $stmt->fetchAll();

if ($rows) {
    foreach ($rows as $row) {
        echo '<div class="card" style="border:1px solid #ddd; padding:15px; margin-bottom:15px; border-radius:5px;">';
        echo '<h3><a href="article.php?id='.$row['article_id'].'">'.htmlspecialchars($row['title']).'</a></h3>';
        echo '<small>By '.htmlspecialchars($row['journalist_name']).'</small>';
        echo '</div>';
    }
} else {
    echo "<p>No bookmarks yet.</p>";
}
?>

</div>
</body>
</html>
