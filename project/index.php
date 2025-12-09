<?php require 'includes/db.php'; ?>
<?php require 'includes/header.php'; ?>

<h1>Latest News</h1>

<!-- Search Form -->
<form method="GET" style="display:flex; gap:10px; margin-bottom:20px;">
    <input type="text" name="search" placeholder="Search..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
    <button type="submit">Search</button>
</form>

<?php
// Base SQL
$sql = "SELECT a.article_id, a.title, a.content, a.publish_date, c.category_name, j.journalist_name 
        FROM Article a 
        JOIN Categories c ON a.category_id = c.category_id 
        JOIN Journalists j ON a.journalist_id = j.journalist_id 
        WHERE a.is_approved = 1";

$params = [];

// Apply search if present
if (!empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $sql .= " AND (a.title LIKE ? OR a.content LIKE ?)";
    $params[] = $search;
    $params[] = $search;
}

// Order by newest
$sql .= " ORDER BY a.publish_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Display articles
if ($articles) {
    foreach ($articles as $row) {
        $title = htmlspecialchars($row['title']);
        $journalist = htmlspecialchars($row['journalist_name']);
        $category = htmlspecialchars($row['category_name']);
        $preview = mb_strimwidth(htmlspecialchars($row['content']), 0, 200, '...');
        $id = $row['article_id'];
        
        echo "<div class='card' style='border:1px solid #ddd; padding:15px; margin-bottom:20px; border-radius:5px;'>";
        echo "<h2><a href='article.php?id=$id'>$title</a></h2>";
        echo "<small style='color:#666'>By $journalist | $category | {$row['publish_date']}</small>";
        echo "<p>$preview</p>";
        echo "<a href='article.php?id=$id'>Read More</a>";
        echo "</div>";
    }
} else {
    echo "<p>No articles found.</p>";
}
?>
