<?php
/**
 * Common head include file
 * 
 * Usage: 
 *   In root directory:
 *     $root_path = '';
 *     require 'etc/head.php';
 *   
 *   In subdirectory (e.g., employee/):
 *     $root_path = '../';
 *     require '../etc/head.php';
 * 
 * Optional parameters (set before including):
 *   $page_title - Page title (default: "Πρωτέας")
 *   $root_path - Path to project root (default: '../' if not set, tries to auto-detect)
 */

// Auto-detect root_path if not set
if (!isset($root_path)) {
    // Try to detect based on current file location
    $current_file = __FILE__;
    $project_root = dirname(dirname($current_file));
    $caller_file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? '';
    
    if ($caller_file) {
        $caller_dir = dirname($caller_file);
        $relative = str_replace($project_root . DIRECTORY_SEPARATOR, '', $caller_dir);
        $depth = substr_count($relative, DIRECTORY_SEPARATOR);
        $root_path = $depth > 0 ? str_repeat('../', $depth) : '';
    } else {
        // Fallback: assume we're in a subdirectory
        $root_path = '../';
    }
}

// Ensure root_path ends with / if not empty
if ($root_path && substr($root_path, -1) !== '/') {
    $root_path .= '/';
}

// Set default title if not provided
if (!isset($page_title)) {
    $page_title = 'Πρωτέας';
}
?>
<script src="https://cdn.tailwindcss.com"></script>
<LINK href="<?php echo $root_path; ?>css/style.css" rel="stylesheet" type="text/css">
<LINK href="<?php echo $root_path; ?>css/demo_table.css" rel="stylesheet" type="text/css">
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title><?php echo htmlspecialchars($page_title); ?></title>
<link rel="shortcut icon" href="<?php echo $root_path; ?>favicon.ico" type="image/x-icon">
<link rel="icon" href="<?php echo $root_path; ?>favicon.ico" type="image/x-icon">
