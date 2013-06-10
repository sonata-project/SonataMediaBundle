<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Filesystem;

use Gaufrette\Adapter\Local as BaseLocal;

class Local extends BaseLocal
{
    /**
     * @var path of directory relative to web
     */
    protected $relativeWebPath;

    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Set path of the directory relative to the web path
     *
     * @param string $path
     */
    public function setRelativeWebPath($path)
    {
        $this->relativeWebPath = $path;
    }

    /**
     * Get path of the directory relative to the web path,
     * can be used in the url
     *
     * @return string path
     */
    public function getRelativeWebPath()
    {
        return $this->relativeWebPath;
    }
}
