<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Fixtures;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class FilesystemTestCase extends TestCase
{
    /**
     * @var string[]
     */
    protected array $longPathNamesWindows = [];

    protected Filesystem $filesystem;

    protected string $workspace;

    private int $umask;

    private static bool $linkOnWindows = true;

    private static bool $symlinkOnWindows = true;

    public static function setUpBeforeClass(): void
    {
        if ('\\' === \DIRECTORY_SEPARATOR) {
            self::$linkOnWindows = true;
            $originFile = tempnam(sys_get_temp_dir(), 'li');
            static::assertNotFalse($originFile);

            $targetFile = tempnam(sys_get_temp_dir(), 'li');
            static::assertNotFalse($targetFile);

            if (true !== @link($originFile, $targetFile)) {
                $report = error_get_last();
                if (\is_array($report) && str_contains($report['message'], 'error code(1314)')) {
                    self::$linkOnWindows = false;
                }
            } else {
                @unlink($targetFile);
            }

            self::$symlinkOnWindows = true;
            $originDir = tempnam(sys_get_temp_dir(), 'sl');
            static::assertNotFalse($originDir);

            $targetDir = tempnam(sys_get_temp_dir(), 'sl');
            static::assertNotFalse($targetDir);

            if (true !== @symlink($originDir, $targetDir)) {
                $report = error_get_last();
                if (\is_array($report) && str_contains($report['message'], 'error code(1314)')) {
                    self::$symlinkOnWindows = false;
                }
            } else {
                @unlink($targetDir);
            }
        }
    }

    protected function setUp(): void
    {
        $this->umask = umask(0);
        $this->filesystem = new Filesystem();
        $this->workspace = sys_get_temp_dir().'/'.microtime(true).'.'.random_int(0, mt_getrandmax());
        mkdir($this->workspace, 0777, true);

        $realpath = realpath($this->workspace);

        static::assertNotFalse($realpath);

        $this->workspace = $realpath;
    }

    protected function tearDown(): void
    {
        if ([] !== $this->longPathNamesWindows) {
            foreach ($this->longPathNamesWindows as $path) {
                exec('DEL '.$path);
            }
            $this->longPathNamesWindows = [];
        }

        $this->filesystem->remove($this->workspace);
        umask($this->umask);
    }

    protected function assertFilePermissions(int $expectedFilePerms, string $filePath): void
    {
        $actualFilePerms = (int) substr(\sprintf('%o', fileperms($filePath)), -3);
        static::assertSame(
            $expectedFilePerms,
            $actualFilePerms,
            \sprintf('File permissions for %s must be %s. Actual %s', $filePath, $expectedFilePerms, $actualFilePerms)
        );
    }

    protected function getFileOwner(string $filepath): ?string
    {
        $this->markAsSkippedIfPosixIsMissing();

        $infos = stat($filepath);

        static::assertNotFalse($infos);

        $datas = posix_getpwuid($infos[4]);

        return (false !== $datas) ? $datas['name'] : null;
    }

    protected function getFileGroup(string $filepath): string
    {
        $this->markAsSkippedIfPosixIsMissing();

        $infos = stat($filepath);

        static::assertNotFalse($infos);

        $datas = posix_getgrgid($infos[5]);

        if (false !== $datas) {
            return $datas['name'];
        }

        static::markTestSkipped('Unable to retrieve file group name');
    }

    protected function markAsSkippedIfLinkIsMissing(): void
    {
        if (!\function_exists('link')) {
            static::markTestSkipped('link is not supported');
        }

        if ('\\' === \DIRECTORY_SEPARATOR && false === self::$linkOnWindows) {
            static::markTestSkipped('link requires "Create hard links" privilege on windows');
        }
    }

    protected function markAsSkippedIfSymlinkIsMissing(bool $relative = false): void
    {
        if ('\\' === \DIRECTORY_SEPARATOR && false === self::$symlinkOnWindows) {
            static::markTestSkipped('symlink requires "Create symbolic links" privilege on Windows');
        }

        // https://bugs.php.net/69473
        if ($relative && '\\' === \DIRECTORY_SEPARATOR && 1 === \PHP_ZTS) {
            static::markTestSkipped('symlink does not support relative paths on thread safe Windows PHP versions');
        }
    }

    protected function markAsSkippedIfChmodIsMissing(): void
    {
        if ('\\' === \DIRECTORY_SEPARATOR) {
            static::markTestSkipped('chmod is not supported on Windows');
        }
    }

    protected function markAsSkippedIfPosixIsMissing(): void
    {
        if (!\function_exists('posix_isatty')) {
            static::markTestSkipped('Function posix_isatty is required.');
        }
    }
}
