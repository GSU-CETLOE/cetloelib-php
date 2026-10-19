<?php

declare(strict_types=1);

namespace Cetloe\Tests\Support;

/**
 * Creates and removes scratch directories under working/tests so test output never leaks into the source tree.
 */
final class TempDir
{
    /**
     * @return string absolute path of a fresh, empty directory
     */
    public static function create(): string
    {
        $root = dirname(__DIR__, 2) . '/working/tests';
        if (!is_dir($root) && !mkdir($root, 0777, true) && !is_dir($root)) {
            throw new \RuntimeException("Unable to create directory: {$root}");
        }

        $dir = $root . '/' . uniqid('t', true);
        if (!mkdir($dir) && !is_dir($dir)) {
            throw new \RuntimeException("Unable to create directory: {$dir}");
        }

        return $dir;
    }


    /**
     * @param string $dir directory created by {@see create()}
     * @return void
     */
    public static function remove(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $entries = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($entries as $entry) {
            if (!$entry instanceof \SplFileInfo) {
                continue;
            }
            $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }
        rmdir($dir);
    }
}
