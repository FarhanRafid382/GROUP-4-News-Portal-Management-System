<?php
session_start();
require 'includes/db.php';
require 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $user = null;
    $role = '';

    // Check Admin
    $stmt = $pdo->prepare("SELECT admin_id AS id, admin_name AS name, password_hash FROM Admin WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    $role = 'admin';

    // Check Journalist
    if (!$user) {
        $stmt = $pdo->prepare("SELECT journalist_id AS id, journalist_name AS name, password_hash FROM Journalists WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        $role = 'journalist';
    }

    // Check Reader
    if (!$user) {
        $stmt = $pdo->prepare("SELECT reader_id AS id, reader_name AS name, password_hash FROM Readers WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        $role = 'reader';
    }

    // Verify password
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $role;

        // Redirect based on role
        if ($role === 'admin') header("Location: admin_panel.php"); // <-- correct file
        elseif ($role === 'journalist') header("Location: journalist_dashboard.php");
        else header("Location: index.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<div style="max-width:400px; margin:auto;">
    <h2>Login</h2>
    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <label>Email</label>
        <input type="email" name="email" required style="width:100%;"><br><br>
        <label>Password</label>
        <input type="password" name="password" required style="width:100%;"><br><br>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
