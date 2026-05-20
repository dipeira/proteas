<?php
session_start();
$contents = $_SESSION['page'];

if (!empty($contents)) {
    // Replace checked checkboxes with a checkmark
    $contents = preg_replace('/<input[^>]*checked[^>]*>/i', '✓ ', $contents);
    // Strip all HTML tags except table structure and headings
    $contents = strip_tags($contents, '<table><thead><tbody><tr><td><th><h2><h3>');
}

$filename = "export.xls";

header('Content-type: application/ms-excel');
header('Content-Disposition: attachment; filename=' . $filename);
echo $contents;
?>