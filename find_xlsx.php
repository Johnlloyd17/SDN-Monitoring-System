<?php
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.'));
foreach ($iterator as $file) {
    if ($file->isFile() && preg_match('/\.xlsx$/i', $file->getFilename())) {
        echo $file->getPathname() . "\n";
    }
}