<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Provider;

class FakeHttpWrapper
{
    public static $ref = array(
        // youtube content
        'http://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=BDYAbAtaDzA&format=json' => 'valide_youtube.txt',
        'http://i3.ytimg.com/vi/BDYAbAtaDzA/hqdefault.jpg' => 'logo.jpg',

        // dailymotion content
        'http://www.dailymotion.com/services/oembed?url=http://www.dailymotion.com/video/x9wjql&format=json' => 'valide_dailymotion.txt',
        'http://ak2.static.dailymotion.com/static/video/711/536/16635117:jpeg_preview_large.jpg?20100801072241' => 'logo.jpg',

        // vimeo content
        'http://vimeo.com/api/oembed.json?url=http://vimeo.com/BDYAbAtaDzA' => 'valide_vimeo.txt',
        'http://b.vimeocdn.com/ts/136/375/136375440_1280.jpg' => 'logo.jpg',
    );

    /* Properties */
    public $context;

    public $fp;

    /* Methods */
    public function __construct()
    {
    }

    public function dir_closedir()
    {
    }

    public function dir_opendir($path, $options)
    {
    }

    public function dir_readdir()
    {
    }

    public function dir_rewinddir()
    {
    }

    public function mkdir($path, $mode, $options)
    {
    }

    public function rename($path_from, $path_to)
    {
    }

    public function rmdir($path, $options)
    {
    }

    public function stream_cast($cast_as)
    {
    }

    public function stream_close()
    {
        return true;
    }

    public function stream_eof()
    {
        return feof($this->fp) == 0;
    }

    public function stream_flush()
    {
    }

    public function stream_lock($operation)
    {
    }

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $file = __DIR__.'/../fixtures/'.self::$ref[$path];

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

    public function stream_seek($offset, $whence = SEEK_SET)
    {
    }

    public function  stream_set_option($option, $arg1, $arg2)
    {
    }

    public function  stream_stat()
    {
    }

    public function  stream_tell()
    {
    }

    public function  stream_write($data)
    {
    }

    public function  unlink($path)
    {
    }

    public function  url_stat($path, $flags)
    {
    }
}
