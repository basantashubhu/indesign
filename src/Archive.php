<?php

namespace Santa\Indesign;

class Archive
{

    public function unzipIdml($idmlPath, $outDir)
    {
        $this->rrmdir($outDir);
        mkdir($outDir, 0777, true);

        $zip = new \ZipArchive();
        if ($zip->open($idmlPath) !== true) {
            throw new \RuntimeException("Cannot open IDML: $idmlPath");
        }
        if (!$zip->extractTo($outDir)) {
            $zip->close();
            throw new \RuntimeException("Cannot extract IDML to: $outDir");
        }
        $zip->close();
    }

    public function rrmdir($dir)
    {
        if (!is_dir($dir))
            return;
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }

    public function zipDirToIdml($srcDir, $outIdmlPath)
    {
        if (file_exists($outIdmlPath))
            unlink($outIdmlPath);

        if(!is_dir(dirname($outIdmlPath))) {
            mkdir(dirname($outIdmlPath), 0777, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($outIdmlPath, \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException("Cannot create IDML: $outIdmlPath");
        }

        $srcDir = rtrim($srcDir, '/') . '/';
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($srcDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            /** @var \SplFileInfo $file */
            if (!$file->isFile())
                continue;
            $abs = $file->getPathname();
            $rel = str_replace($srcDir, '', str_replace(DIRECTORY_SEPARATOR, '/', $abs));
            // IDML expects forward slashes
            $rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);
            $zip->addFile($abs, $rel);
        }

        $zip->close();
    }
}