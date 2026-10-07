<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

$loginError = null;

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action   = $_POST['action'] ?? 'login';
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($action === 'cancel') {
        $_SESSION['user'] = 'Invite';
        $_SESSION['role'] = 'guest';
        header('Location: index.php');
        exit;
    }

    if ($action === 'login') {
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user) {
            $isPasswordless = empty($user['password_hash']);
            $isPasswordValid = !$isPasswordless && password_verify($password, $user['password_hash']);

            if ($isPasswordless || $isPasswordValid) {
                $_SESSION['user'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_id'] = $user['id'];
                header('Location: index.php');
                exit;
            }
        }

        $loginError = "The password or user name you typed is incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="icon" type="image/png" href="./favicon.png">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/sddm.css">
</head>
<body class="sddm-screen">
    <div class="window login-dialog" role="dialog" aria-labelledby="login-title">
        <div class="title-bar">
            <span id="login-title" class="title-bar-text">Welcome</span>
        </div>

        <form method="POST" action="sddm.php" class="window-body login-body">
            <img class="login-icon" src="./img/Display.png" alt="Logon Icon">

            <div class="login-form-area">
                <?php if ($loginError): ?>
                    <div style="background: var(--win-white); border: 2px solid var(--win-dark-gray); padding: 4px 6px; color: #a00; font-size: 12px;">
                        <?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>
                <p class="login-prompt">Type a user name and password to log on to Windows.</p>

                <div class="form-row">
                    <label for="username"><u>U</u>ser name:</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="win95-input" 
                        value="Invite" 
                        autocomplete="off" 
                        autofocus
                    >
                </div>

                <div class="form-row">
                    <label for="password"><u>P</u>assword:</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="win95-input" 
                        placeholder="(None)" 
                        autocomplete="off"
                    >
                </div>
            </div>

            <div class="login-buttons">
                <button type="submit" name="action" value="login" class="btn">OK</button>
                <button type="submit" name="action" value="cancel" class="btn">Cancel</button>
            </div>
        </form>
    </div>
</body>
</html>