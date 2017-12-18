<?php
class CFile {
    public static function createUniqueFileName($baseDir, $ext = '', $prefix = '', $moreEntropy = true)
    {
        while (true) {
            $outputFileName = uniqid($prefix, $moreEntropy) . $ext;
            if (!file_exists($baseDir . DIRECTORY_SEPARATOR . $outputFileName)) break;
        }
        return $outputFileName;
    }
} 