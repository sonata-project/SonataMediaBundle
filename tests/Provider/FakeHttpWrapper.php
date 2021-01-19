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

namespace Sonata\MediaBundle\Tests\Provider;

class FakeHttpWrapper
{
    public static $ref = [
        // youtube content
        'http://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=BDYAbAtaDzA&format=json' => 'valide_youtube.txt',
        'http://i3.ytimg.com/vi/BDYAbAtaDzA/hqdefault.jpg' => 'logo.jpg',

        // dailymotion content
        'http://www.dailymotion.com/services/oembed?url=http://www.dailymotion.com/video/x9wjql&format=json' => 'valide_dailymotion.txt',
        'http://ak2.static.dailymotion.com/static/video/711/536/16635117:jpeg_preview_large.jpg?20100801072241' => 'logo.jpg',

        // vimeo content
        'http://vimeo.com/api/oembed.json?url=http://vimeo.com/BDYAbAtaDzA' => 'valide_vimeo.txt',
        'http://b.vimeocdn.com/ts/136/375/136375440_1280.jpg' => 'logo.jpg',
    ];

    /* Properties */
    public $context;

    public $fp;

    /* Methods */
    public function __construct()
    {
    }

    public function dir_closedir(): void
    {
    }

    public function dir_opendir($path, $options): void
    {
    }

    public function dir_readdir(): void
    {
    }

    public function dir_rewinddir(): void
    {
    }

    public function mkdir($path, $mode, $options): void
    {
    }

    public function rename($path_from, $path_to): void
    {
    }

    public function rmdir($path, $options): void
    {
    }

    public function stream_cast($cast_as): void
    {
    }

    public function stream_close(): bool
    {
        return true;
    }

    public function stream_eof(): bool
    {
        return 0 === feof($this->fp);
    }

    public function stream_flush(): void
    {
    }

    public function stream_lock($operation): void
    {
    }

    public function stream_open($path, $mode, $options, &$opened_path): bool
    {
        $file = __DIR__.'/../Fixtures/'.self::$ref[$path];

        if (!is_file($file)) {
            var_dump('unable to retrieve the file : '.$file);
        }

        $this->fp = fopen($file, $mode);

        return true;
    }

    public function stream_read($count)
    {
        return fread($this->fp, $count);
    }

    public function stream_seek($offset, $whence = \SEEK_SET): void
    {
    }

    public function stream_set_option($option, $arg1, $arg2): void
    {
    }

    public function stream_stat(): void
    {
    }

    public function stream_tell(): void
    {
    }

    public function stream_write($data): void
    {
    }

    public function unlink($path): void
    {
    }

    public function url_stat($path, $flags): void
    {
    }
}
