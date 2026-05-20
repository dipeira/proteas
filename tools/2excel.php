    <?php
    if (isset($_POST['data'])) {
        $contents = $_POST['data'];
    } elseif (isset($_GET['data'])) {
        $contents = $_GET['data'];
    } else {
        $contents = '';
    }
    
    if (!empty($contents)) {
        // Strip image tags (icons) completely
        $contents = preg_replace('/<img[^>]*>/i', '', $contents);
        // Strip anchor tags (links) but keep their content
        $contents = preg_replace('/<a\b[^>]*>(.*?)<\/a>/is', '$1', $contents);
    }
    
    $filename = "export.xls";
    
    header('Content-type: application/ms-excel');
    header('Content-Disposition: attachment; filename='.$filename);
    echo $contents;
    ?>