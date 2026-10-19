<?php

declare(strict_types=1);

namespace Cetloe\Tests\Utils;

use Cetloe\Tests\Support\EnvironmentTestCase;
use Cetloe\Tests\Support\TempDir;
use Cetloe\Utils\FileMethods;

final class FileMethodsTest extends EnvironmentTestCase
{
    private string $dir;


    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = TempDir::create();
    }


    protected function tearDown(): void
    {
        TempDir::remove($this->dir);
        parent::tearDown();
    }


    /**
     * @param string $name
     * @param string $contents
     * @return string path of the written file
     */
    private function writeFile(string $name, string $contents = ''): string
    {
        $path = $this->dir . '/' . $name;
        file_put_contents($path, $contents);
        return $path;
    }


    public function testGlobReturnsMatchingPaths(): void
    {
        $a = $this->writeFile('a.txt');
        $this->writeFile('b.log');

        self::assertSame([$a], FileMethods::glob($this->dir . '/*.txt'));
        self::assertSame([], FileMethods::glob($this->dir . '/*.none'));
    }


    public function testGetRealPathResolvesRelativeSegments(): void
    {
        self::assertSame($this->dir, FileMethods::getRealPath($this->dir . '/./'));
    }


    public function testGetRealPathRejectsMissingPath(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid path');
        FileMethods::getRealPath($this->dir . '/missing');
    }


    public function testGetFilePathAcceptsFilesOnly(): void
    {
        $file = $this->writeFile('f.txt');
        self::assertSame($file, FileMethods::getFilePath($file));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File not found');
        FileMethods::getFilePath($this->dir);
    }


    public function testGetDirPathAcceptsDirectoriesOnly(): void
    {
        self::assertSame($this->dir, FileMethods::getDirPath($this->dir));

        $file = $this->writeFile('f.txt');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Directory not found');
        FileMethods::getDirPath($file);
    }


    /**
     * The map is keyed by full path with the extension-less base name as the value.
     */
    public function testGetFilesMapsPathsToBaseNamesSortedAndFilteredByExtension(): void
    {
        $two = $this->writeFile('two.txt');
        $one = $this->writeFile('one.txt');
        $this->writeFile('skip.log');
        mkdir($this->dir . '/sub.txt');

        self::assertSame([$one => 'one', $two => 'two'], FileMethods::getFiles($this->dir, 'txt'));
        self::assertSame([$one => 'one', $two => 'two'], FileMethods::getFiles($this->dir . '/', '.txt'));
    }


    public function testGetFilesWithoutExtensionKeepsFullBaseName(): void
    {
        $one = $this->writeFile('one.txt');
        self::assertSame([$one => 'one.txt'], FileMethods::getFiles($this->dir));
    }


    public function testGetFilesAcceptsExplicitPathList(): void
    {
        $one = $this->writeFile('one.txt');
        $files = FileMethods::getFiles([$one, $this->dir . '/missing.txt', $this->dir], 'txt');
        self::assertSame([$one => 'one'], $files);
    }


    public function testDeleteFilesRemovesOnlyMatchingFiles(): void
    {
        $this->writeFile('a.tmp');
        $keep = $this->writeFile('b.txt');
        mkdir($this->dir . '/c.tmp');

        FileMethods::deleteFiles($this->dir . '/*.tmp');

        self::assertFileDoesNotExist($this->dir . '/a.tmp');
        self::assertFileExists($keep);
        self::assertDirectoryExists($this->dir . '/c.tmp');
    }


    public function testGetContentsReadsFile(): void
    {
        $file = $this->writeFile('f.txt', 'payload');
        self::assertSame('payload', FileMethods::getContents($file));
    }


    public function testGetContentsRejectsMissingFile(): void
    {
        $this->expectException(\RuntimeException::class);
        FileMethods::getContents($this->dir . '/missing');
    }


    public function testPutContentsWritesStringWithTrailingNewline(): void
    {
        $path = $this->dir . '/out.txt';
        $bytes = FileMethods::putContents($path, 'abc');

        self::assertSame(4, $bytes);
        self::assertSame('abc' . PHP_EOL, file_get_contents($path));
    }


    public function testPutContentsJoinsArrayLines(): void
    {
        $path = $this->dir . '/out.txt';
        FileMethods::putContents($path, ['a', 'b']);

        self::assertSame('a' . PHP_EOL . 'b' . PHP_EOL, file_get_contents($path));
    }


    public function testPutContentsReportsUnwritablePath(): void
    {
        $warnings = $this->captureWarnings(function (): void {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Error writing to file');
            FileMethods::putContents($this->dir . '/missing/out.txt', 'abc');
        });

        self::assertCount(1, $warnings);
        self::assertStringContainsString('Failed to open stream', $warnings[0]);
    }


    public function testOpenZipFileStripsByteOrderMarkByDefault(): void
    {
        $zipPath = $this->dir . '/data.zip';
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);
        $zip->addFromString('data.csv', "\xEF\xBB\xBFhello");
        $zip->close();

        [$archive, $stream] = FileMethods::openZipFile($zipPath);
        self::assertSame('hello', stream_get_contents($stream));
        fclose($stream);
        $archive->close();

        [$archive, $stream] = FileMethods::openZipFile($zipPath, false);
        self::assertSame("\xEF\xBB\xBFhello", stream_get_contents($stream));
        fclose($stream);
        $archive->close();
    }


    public function testOpenZipFileRejectsMissingArchive(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to open zip file');
        FileMethods::openZipFile($this->dir . '/missing.zip');
    }


    public function testOpenGzipFileRoundTrips(): void
    {
        $path = $this->dir . '/data.gz';

        $out = FileMethods::openGzipFile($path);
        gzwrite($out, 'compressed');
        gzclose($out);

        $in = FileMethods::openGzipFile($path, 'r');
        self::assertSame('compressed', gzread($in, 100));
        gzclose($in);
    }


    public function testOpenGzipFileReportsUnwritablePath(): void
    {
        $warnings = $this->captureWarnings(function (): void {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Unable to open gzip file');
            FileMethods::openGzipFile($this->dir . '/missing/data.gz');
        });

        self::assertCount(1, $warnings);
    }


    public function testOpenFileReturnsStream(): void
    {
        $file = $this->writeFile('f.txt', 'x');
        $stream = FileMethods::openFile($file, 'r');

        self::assertSame('x', fread($stream, 1));
        fclose($stream);
    }


    public function testOpenFileReportsMissingFile(): void
    {
        $warnings = $this->captureWarnings(function (): void {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Unable to open file');
            FileMethods::openFile($this->dir . '/missing', 'r');
        });

        self::assertCount(1, $warnings);
        self::assertStringContainsString('Failed to open stream', $warnings[0]);
    }
}
