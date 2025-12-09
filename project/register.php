 <?php require 'includes/db.php'; ?>
<?php require 'includes/header.php'; ?>

<div style="max-width:400px; margin:auto;">
    <h2>Reader Registration</h2>

    <?php
    // Initialize variables to retain user input on error
    $name_val = '';
    $email_val = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $name_val = htmlspecialchars($name);
        $email_val = htmlspecialchars($email);

        if (strlen($password) < 6) {
            echo "<p style='color:red'>Password must be at least 6 characters long.</p>";
        } else {
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare("INSERT INTO Readers (reader_name, email, password_hash) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $pass_hash]);
                echo "<p style='color:green'>Registration successful! <a href='login.php'>Login here</a></p>";
                // Clear values after success
                $name_val = $email_val = '';
            } catch (PDOException $e) {
                // Assuming duplicate email error
                echo "<p style='color:red'>Email already registered.</p>";
            }
        }
    }
    ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required value="<?php echo $name_val; ?>">
        <input type="email" name="email" placeholder="Email" required value="<?php echo $email_val; ?>">
        <input type="password" name="password" placeholder="Password (min 6 chars)" required>
        <button type="submit">Register</button>
    </form>
</div>
</body>
</html>
