<?php
require 'includes/db.php';
require 'includes/header.php';

// Only admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') die("Admins Only");

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $role = $_POST['role'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (strlen($password) < 6) {
        $msg = "<p style='color:red'>Password must be at least 6 characters.</p>";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            if ($role === 'admin') {
                $stmt = $pdo->prepare("INSERT INTO Admin (admin_name, email, password_hash) VALUES (?, ?, ?)");
            } elseif ($role === 'journalist') {
                $stmt = $pdo->prepare("INSERT INTO Journalists (journalist_name, email, password_hash) VALUES (?, ?, ?)");
            } else {
                $stmt = $pdo->prepare("INSERT INTO Readers (reader_name, email, password_hash) VALUES (?, ?, ?)");
            }
            $stmt->execute([$name, $email, $hash]);
            $msg = "<p style='color:green'>$role added successfully!</p>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) $msg = "<p style='color:red'>Email already exists.</p>";
            else $msg = "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
        }
    }
}

// Handle Delete User
if (isset($_GET['delete']) && isset($_GET['role'])) {
    $del_id = (int)$_GET['delete'];
    $role = $_GET['role'];
    if ($role === 'admin') $stmt = $pdo->prepare("DELETE FROM Admin WHERE admin_id = ?");
    elseif ($role === 'journalist') $stmt = $pdo->prepare("DELETE FROM Journalists WHERE journalist_id = ?");
    else $stmt = $pdo->prepare("DELETE FROM Readers WHERE reader_id = ?");
    $stmt->execute([$del_id]);
    header("Location: manage_users.php");
    exit;
}

// Handle Edit User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $role = $_POST['role'];
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($password) { // update password only if entered
        if (strlen($password) < 6) {
            $msg = "<p style='color:red'>Password must be at least 6 characters.</p>";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            if ($role === 'admin') $stmt = $pdo->prepare("UPDATE Admin SET admin_name=?, email=?, password_hash=? WHERE admin_id=?");
            elseif ($role === 'journalist') $stmt = $pdo->prepare("UPDATE Journalists SET journalist_name=?, email=?, password_hash=? WHERE journalist_id=?");
            else $stmt = $pdo->prepare("UPDATE Readers SET reader_name=?, email=?, password_hash=? WHERE reader_id=?");
            $stmt->execute([$name, $email, $hash, $id]);
            $msg = "<p style='color:green'>$role updated successfully!</p>";
        }
    } else { // update only name and email
        if ($role === 'admin') $stmt = $pdo->prepare("UPDATE Admin SET admin_name=?, email=? WHERE admin_id=?");
        elseif ($role === 'journalist') $stmt = $pdo->prepare("UPDATE Journalists SET journalist_name=?, email=? WHERE journalist_id=?");
        else $stmt = $pdo->prepare("UPDATE Readers SET reader_name=?, email=? WHERE reader_id=?");
        $stmt->execute([$name, $email, $id]);
        $msg = "<p style='color:green'>$role updated successfully!</p>";
    }
}

// Handle fetch for editing
$edit_user = null;
if (isset($_GET['edit']) && isset($_GET['role'])) {
    $edit_id = (int)$_GET['edit'];
    $role = $_GET['role'];
    if ($role === 'admin') $stmt = $pdo->prepare("SELECT * FROM Admin WHERE admin_id=?");
    elseif ($role === 'journalist') $stmt = $pdo->prepare("SELECT * FROM Journalists WHERE journalist_id=?");
    else $stmt = $pdo->prepare("SELECT * FROM Readers WHERE reader_id=?");
    $stmt->execute([$edit_id]);
    $edit_user = $stmt->fetch();
}

// Fetch all users
$admins = $pdo->query("SELECT admin_id, admin_name, email FROM Admin ORDER BY admin_name ASC")->fetchAll();
$journalists = $pdo->query("SELECT journalist_id, journalist_name, email FROM Journalists ORDER BY journalist_name ASC")->fetchAll();
$readers = $pdo->query("SELECT reader_id, reader_name, email FROM Readers ORDER BY reader_name ASC")->fetchAll();
?>

<div style="max-width:700px; margin:auto;">

<h2><?php echo $edit_user ? "Edit User" : "Add User"; ?></h2>
<?php if(isset($msg)) echo $msg; ?>

<form method="POST" style="margin-bottom:30px;">
    <?php if($edit_user): ?>
        <input type="hidden" name="role" value="<?php echo $role; ?>">
        <input type="hidden" name="id" value="<?php echo $edit_user['admin_id'] ?? $edit_user['journalist_id'] ?? $edit_user['reader_id']; ?>">
    <?php endif; ?>
    
    <label>Name</label>
    <input type="text" name="name" value="<?php echo $edit_user ? htmlspecialchars($edit_user['admin_name'] ?? $edit_user['journalist_name'] ?? $edit_user['reader_name']) : ''; ?>" required>
    
    <label>Email</label>
    <input type="email" name="email" value="<?php echo $edit_user ? htmlspecialchars($edit_user['email']) : ''; ?>" required>
    
    <label>Password (leave blank to keep current)</label>
    <input type="password" name="password">
    
    <?php if(!$edit_user): ?>
        <label>Role</label>
        <select name="role" required>
            <option value="admin">Admin</option>
            <option value="journalist">Journalist</option>
            <option value="reader">Reader</option>
        </select>
    <?php endif; ?>
    
    <button type="submit" name="<?php echo $edit_user ? 'edit_user' : 'add_user'; ?>">
        <?php echo $edit_user ? 'Update User' : 'Add User'; ?>
    </button>
</form>

<h2>Existing Admins</h2>
<?php if ($admins) { ?>
<table border="1" cellpadding="5" cellspacing="0" style="width:100%; margin-bottom:30px;">
    <tr><th>Name</th><th>Email</th><th>Actions</th></tr>
    <?php foreach($admins as $a) { ?>
        <tr>
            <td><?php echo htmlspecialchars($a['admin_name']); ?></td>
            <td><?php echo htmlspecialchars($a['email']); ?></td>
            <td>
                <a href="?edit=<?php echo $a['admin_id']; ?>&role=admin">Edit</a> |
                <a href="?delete=<?php echo $a['admin_id']; ?>&role=admin" onclick="return confirm('Delete this admin?')">Delete</a>
            </td>
        </tr>
    <?php } ?>
</table>
<?php } else { echo "<p>No admins found.</p>"; } ?>

<h2>Existing Journalists</h2>
<?php if ($journalists) { ?>
<table border="1" cellpadding="5" cellspacing="0" style="width:100%; margin-bottom:30px;">
    <tr><th>Name</th><th>Email</th><th>Actions</th></tr>
    <?php foreach($journalists as $j) { ?>
        <tr>
            <td><?php echo htmlspecialchars($j['journalist_name']); ?></td>
            <td><?php echo htmlspecialchars($j['email']); ?></td>
            <td>
                <a href="?edit=<?php echo $j['journalist_id']; ?>&role=journalist">Edit</a> |
                <a href="?delete=<?php echo $j['journalist_id']; ?>&role=journalist" onclick="return confirm('Delete this journalist?')">Delete</a>
            </td>
        </tr>
    <?php } ?>
</table>
<?php } else { echo "<p>No journalists found.</p>"; } ?>

<h2>Existing Readers</h2>
<?php if ($readers) { ?>
<table border="1" cellpadding="5" cellspacing="0" style="width:100%; margin-bottom:30px;">
    <tr><th>Name</th><th>Email</th><th>Actions</th></tr>
    <?php foreach($readers as $r) { ?>
        <tr>
            <td><?php echo htmlspecialchars($r['reader_name']); ?></td>
            <td><?php echo htmlspecialchars($r['email']); ?></td>
            <td>
                <a href="?edit=<?php echo $r['reader_id']; ?>&role=reader">Edit</a> |
                <a href="?delete=<?php echo $r['reader_id']; ?>&role=reader" onclick="return confirm('Delete this reader?')">Delete</a>
            </td>
        </tr>
    <?php } ?>
</table>
<?php } else { echo "<p>No readers found.</p>"; } ?>

</div>
</body>
</html>
