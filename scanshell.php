<?php
/*
 * SPHINX101 Web Shell Scanner - Stable Edition
 * Author: SPHINX101
 * Version: 5.1
 */

// ==============================================
// KONFIGURASI DASAR
// ==============================================
session_start();
error_reporting(0);
header('X-Powered-By: SPHINX101 Scanner');
date_default_timezone_set('Asia/Jakarta');

// Lokasi penyimpanan
define('BACKUP_DIR', __DIR__.'/backups/');
define('QUARANTINE_DIR', __DIR__.'/quarantine/');

// ==============================================
// FUNGSI UTAMA
// ==============================================

// Fungsi untuk memindai file
function scan_file($path) {
    $patterns = [
        'shell_exec\(' => 5,
        'system\(' => 5,
        'exec\(' => 5,
        'eval\(' => 5,
        'base64_decode\(' => 3
    ];
    
    if(!is_readable($path) || filesize($path) > 5*1024*1024) return false;
    
    $content = strtolower(file_get_contents($path));
    $result = [
        'file' => $path,
        'patterns' => [],
        'risk_score' => 0
    ];
    
    foreach($patterns as $pattern => $score) {
        if(preg_match("/$pattern/", $content)) {
            $result['patterns'][$pattern] = $score;
            $result['risk_score'] += $score;
        }
    }
    
    return !empty($result['patterns']) ? $result : false;
}

// Fungsi penghapusan aman
function safe_delete($path) {
    if(!file_exists($path)) return false;
    
    // Buat folder jika belum ada
    if(!file_exists(BACKUP_DIR)) mkdir(BACKUP_DIR, 0755, true);
    if(!file_exists(QUARANTINE_DIR)) mkdir(QUARANTINE_DIR, 0755, true);
    
    $backup_path = BACKUP_DIR.basename($path).'.bak';
    $quarantine_path = QUARANTINE_DIR.basename($path).'.qtn';
    
    // Buat backup
    if(!copy($path, $backup_path)) return false;
    
    // Coba pindahkan ke karantina
    if(rename($path, $quarantine_path)) {
        return ['status' => 'quarantined', 'backup' => $backup_path];
    } 
    // Jika gagal, coba hapus langsung
    elseif(unlink($path)) {
        return ['status' => 'deleted', 'backup' => $backup_path];
    }
    
    return false;
}

// ==============================================
// TAMPILAN WEB
// ==============================================
if(isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if($_GET['action'] == 'delete' && isset($_GET['file'])) {
        $file = realpath($_GET['file']);
        if($file && file_exists($file)) {
            die(json_encode(safe_delete($file)));
        }
    }
    
    die(json_encode(['error' => 'Invalid request']));
}

// Tampilkan antarmuka
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPHINX101 Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fa; }
        .risk-high { background-color: #fff3f3; border-left: 4px solid #dc3545; }
        .risk-medium { background-color: #fff9e6; border-left: 4px solid #ffc107; }
        .risk-low { background-color: #f0f8ff; border-left: 4px solid #0d6efd; }
        .badge-risk { font-size: 0.8em; padding: 3px 8px; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold">SPHINX101 Scanner</h1>
            <p class="text-muted">Web shell detection tool</p>
        </div>
        
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="post" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="scan_path" class="form-control" 
                               placeholder="Enter directory path" 
                               value="<?= htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="scan" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Scan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if(isset($_POST['scan'])): ?>
            <?php
            $scan_path = realpath($_POST['scan_path']);
            $results = [];
            
            if($scan_path && is_dir($scan_path)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($scan_path, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                
                foreach($iterator as $file) {
                    if($file->isFile() && $result = scan_file($file->getRealPath())) {
                        $results[] = $result;
                    }
                }
                
                // Urutkan berdasarkan risiko
                usort($results, fn($a, $b) => $b['risk_score'] <=> $a['risk_score']);
            }
            ?>
            
            <div class="card shadow">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Scan Results</h5>
                    <?php if(!empty($results)): ?>
                        <button id="deleteHighRisk" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash-alt me-1"></i> Delete High Risk
                        </button>
                    <?php endif; ?>
                </div>
                
                <?php if(empty($results)): ?>
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h5>No suspicious files found</h5>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>File</th>
                                    <th width="120px">Risk</th>
                                    <th width="200px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($results as $result): ?>
                                    <?php
                                    $risk_class = match(true) {
                                        $result['risk_score'] >= 5 => 'risk-high',
                                        $result['risk_score'] >= 2 => 'risk-medium',
                                        default => 'risk-low'
                                    };
                                    
                                    $risk_badge = match(true) {
                                        $result['risk_score'] >= 5 => ['label' => 'High', 'class' => 'bg-danger'],
                                        $result['risk_score'] >= 2 => ['label' => 'Medium', 'class' => 'bg-warning'],
                                        default => ['label' => 'Low', 'class' => 'bg-primary']
                                    };
                                    ?>
                                    <tr class="<?= $risk_class ?>" data-file="<?= htmlspecialchars($result['file']) ?>">
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars(basename($result['file'])) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars(dirname($result['file'])) ?></small>
                                            <div class="mt-2">
                                                <?php foreach($result['patterns'] as $pattern => $score): ?>
                                                    <span class="badge bg-secondary me-1 mb-1"><?= htmlspecialchars($pattern) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-risk <?= $risk_badge['class'] ?>">
                                                <?= $risk_badge['label'] ?> (<?= $result['risk_score'] ?>)
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary view-file" data-file="<?= htmlspecialchars($result['file']) ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-file" data-file="<?= htmlspecialchars($result['file']) ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal untuk view file -->
    <div class="modal fade" id="fileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fileName"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <pre id="fileContent" class="p-3 bg-light rounded"></pre>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        // View file content
        document.querySelectorAll('.view-file').forEach(btn => {
            btn.addEventListener('click', function() {
                const filePath = this.getAttribute('data-file');
                fetch('?action=view&file=' + encodeURIComponent(filePath))
                    .then(response => response.text())
                    .then(content => {
                        document.getElementById('fileName').textContent = filePath.split('/').pop();
                        document.getElementById('fileContent').textContent = content;
                        new bootstrap.Modal(document.getElementById('fileModal')).show();
                    });
            });
        });
        
        // Delete single file
        document.querySelectorAll('.delete-file').forEach(btn => {
            btn.addEventListener('click', function() {
                const filePath = this.getAttribute('data-file');
                if(confirm(`Delete this file?\n${filePath}`)) {
                    fetch('?action=delete&file=' + encodeURIComponent(filePath))
                        .then(response => response.json())
                        .then(result => {
                            if(result.status) {
                                alert(`File ${result.status} successfully`);
                                location.reload();
                            } else {
                                alert('Failed to delete file');
                            }
                        });
                }
            });
        });
        
        // Delete all high risk files
        document.getElementById('deleteHighRisk')?.addEventListener('click', function() {
            const files = [];
            document.querySelectorAll('.risk-high').forEach(row => {
                files.push(row.getAttribute('data-file'));
            });
            
            if(files.length === 0) {
                alert('No high risk files found');
                return;
            }
            
            if(confirm(`Delete ${files.length} high risk files?`)) {
                Promise.all(files.map(file => 
                    fetch('?action=delete&file=' + encodeURIComponent(file))
                ).then(() => {
                    alert(`${files.length} files deleted`);
                    location.reload();
                });
            }
        });
    </script>
</body>
</html>