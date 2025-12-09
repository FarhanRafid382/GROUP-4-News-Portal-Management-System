<?php
require 'includes/db.php';
require 'includes/header.php';

// Only admin allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') die("Admins Only");

// Enable PDO errors for debugging
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// === Handle Article Approve/Delete ===
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'approve') {
        $stmt = $pdo->prepare("UPDATE Article SET is_approved = 1 WHERE article_id = ?");
        $stmt->execute([$id]);
    } elseif ($_GET['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM Article WHERE article_id = ?");
        $stmt->execute([$id]);
    }
    header("Location: admin_panel.php");
    exit;
}

// === Handle User Add/Edit/Delete ===
$edit_user = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?? '';

    if (isset($_POST['add_user'])) {
        if (strlen($password) < 6) $msg = "<p style='color:red'>Password must be at least 6 characters.</p>";
        else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                if ($role === 'admin') $stmt = $pdo->prepare("INSERT INTO Admin (admin_name,email,password_hash) VALUES (?,?,?)");
                elseif ($role === 'journalist') $stmt = $pdo->prepare("INSERT INTO Journalists (journalist_name,email,password_hash) VALUES (?,?,?)");
                else $stmt = $pdo->prepare("INSERT INTO Readers (reader_name,email,password_hash) VALUES (?,?,?)");
                $stmt->execute([$name,$email,$hash]);
                $msg = "<p style='color:green'>$role added successfully!</p>";
            } catch(PDOException $e){ $msg="<p style='color:red'>Email already exists.</p>"; }
        }
    } elseif (isset($_POST['edit_user']) && $id) {
        $hashPart = $password ? ", password_hash=?" : "";
        if ($role==='admin') $stmt = $pdo->prepare("UPDATE Admin SET admin_name=?, email=? $hashPart WHERE admin_id=?");
        elseif ($role==='journalist') $stmt = $pdo->prepare("UPDATE Journalists SET journalist_name=?, email=? $hashPart WHERE journalist_id=?");
        else $stmt = $pdo->prepare("UPDATE Readers SET reader_name=?, email=? $hashPart WHERE reader_id=?");
        $params = $password ? [$name,$email,password_hash($password,PASSWORD_DEFAULT),$id] : [$name,$email,$id];
        $stmt->execute($params);
        $msg = "<p style='color:green'>$role updated successfully!</p>";
    }
}

// Load user for editing
if (isset($_GET['edit'], $_GET['role'])) {
    $edit_id = (int)$_GET['edit'];
    $role = $_GET['role'];
    if ($role==='admin') $stmt=$pdo->prepare("SELECT * FROM Admin WHERE admin_id=?");
    elseif ($role==='journalist') $stmt=$pdo->prepare("SELECT * FROM Journalists WHERE journalist_id=?");
    else $stmt=$pdo->prepare("SELECT * FROM Readers WHERE reader_id=?");
    $stmt->execute([$edit_id]);
    $edit_user=$stmt->fetch();
}

// Handle delete user
if (isset($_GET['delete'], $_GET['role'])) {
    $del_id = (int)$_GET['delete'];
    $role = $_GET['role'];
    if ($role==='admin') $stmt=$pdo->prepare("DELETE FROM Admin WHERE admin_id=?");
    elseif ($role==='journalist') $stmt=$pdo->prepare("DELETE FROM Journalists WHERE journalist_id=?");
    else $stmt=$pdo->prepare("DELETE FROM Readers WHERE reader_id=?");
    $stmt->execute([$del_id]);
    header("Location: admin_panel.php");
    exit;
}

// Fetch pending articles
$articles = $pdo->query("
    SELECT a.*, j.journalist_name, c.category_name 
    FROM Article a 
    JOIN Journalists j ON a.journalist_id=j.journalist_id 
    JOIN Categories c ON a.category_id=c.category_id 
    WHERE a.is_approved=0
")->fetchAll();

// Fetch all users
$admins = $pdo->query("SELECT * FROM Admin")->fetchAll();
$journalists = $pdo->query("SELECT * FROM Journalists")->fetchAll();
$readers = $pdo->query("SELECT * FROM Readers")->fetchAll();
?>

<div style="max-width:900px;margin:auto;">
<h1>Admin Panel</h1>

<h2>Pending Articles</h2>
<?php if(count($articles) > 0): ?>
<table border="1" cellpadding="5" cellspacing="0" style="width:100%; margin-bottom:30px;">
<tr><th>Title</th><th>Journalist</th><th>Category</th><th>Actions</th></tr>
<?php foreach($articles as $a): ?>
<tr>
    <td><a href="article.php?id=<?=$a['article_id']?>" target="_blank"><?=htmlspecialchars($a['title'])?></a></td>
    <td><?=htmlspecialchars($a['journalist_name'])?></td>
    <td><?=htmlspecialchars($a['category_name'])?></td>
    <td>
        <a href="?action=approve&id=<?=$a['article_id']?>" style="color:green" onclick="return confirm('Approve this article?')">[Approve]</a>
        <a href="?action=delete&id=<?=$a['article_id']?>" style="color:red" onclick="return confirm('Delete this article?')">[Delete]</a>
    </td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p>No pending articles.</p>
<?php endif; ?>

<h2>Manage Users</h2>
<?php if(isset($msg)) echo $msg; ?>
<form method="POST" style="margin-bottom:30px;">
    <?php if($edit_user): ?>
        <input type="hidden" name="role" value="<?=$role?>">
        <input type="hidden" name="id" value="<?=$edit_user['admin_id'] ?? $edit_user['journalist_id'] ?? $edit_user['reader_id']?>">
    <?php endif; ?>
    
    <label>Name</label>
    <input type="text" name="name" value="<?= $edit_user ? htmlspecialchars($edit_user['admin_name'] ?? $edit_user['journalist_name'] ?? $edit_user['reader_name']) : '' ?>" required>
    
    <label>Email</label>
    <input type="email" name="email" value="<?= $edit_user ? htmlspecialchars($edit_user['email']) : '' ?>" required>
    
    <label>Password <?= $edit_user ? "(leave blank to keep current)" : "" ?></label>
    <input type="password" name="password">
    
    <?php if(!$edit_user): ?>
    <label>Role</label>
    <select name="role" required>
        <option value="admin">Admin</option>
        <option value="journalist">Journalist</option>
        <option value="reader">Reader</option>
    </select>
    <?php endif; ?>
    
    <button type="submit" name="<?= $edit_user ? 'edit_user':'add_user' ?>"><?= $edit_user ? 'Update User':'Add User' ?></button>
</form>

<h2>All Users</h2>
<?php
function render_user_table($users, $role_name, $role_key){
    if(count($users)==0){ echo "<p>No $role_name found.</p>"; return; }
    echo "<h3>$role_name</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='width:100%; margin-bottom:20px;'>
            <tr><th>Name</th><th>Email</th><th>Actions</th></tr>";
    foreach($users as $u){
        $id = $u[$role_key.'_id'];
        $name = htmlspecialchars($u[$role_key.'_name']);
        $email = htmlspecialchars($u['email']);
        echo "<tr>
                <td>$name</td>
                <td>$email</td>
                <td>
                    <a href='?edit=$id&role=$role_key'>Edit</a> | 
                    <a href='?delete=$id&role=$role_key' onclick='return confirm(\"Delete?\")'>Delete</a>
                </td>
              </tr>";
    }
    echo "</table>";
}

render_user_table($admins, "Admins", "admin");
render_user_table($journalists, "Journalists", "journalist");
render_user_table($readers, "Readers", "reader");
?>
</div>
</body>
</html>
