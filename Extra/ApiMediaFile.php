<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Extra;

use Symfony\Component\HttpFoundation\File\File;

class ApiMediaFile extends File
{
    /**
     * @var string
     */
    protected $extension;

    /**
     * @var string
     */
    protected $mimetype;

    /**
     * @var resource
     */
    protected $resource;

    /**
     * @param resource $handle
     */
    public function __construct($handle)
    {
        if (!is_resource($handle)) {
            throw new \RuntimeException('handle is not a resource');
        }

        $this->resource = $handle;

        $meta = stream_get_meta_data($handle);

        parent::__construct($meta['uri']);
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension ?: parent::getExtension();
    }

    /**
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * @return string
     */
    public function getMimetype()
    {
        return $this->mimetype ?: parent::getMimeType();
    }

    /**
     * @param string $mimetype
     */
    public function setMimetype($mimetype)
    {
        $this->mimetype = $mimetype;
    }

    /**
     * @return null|string
     */
    public function guessExtension()
    {
        return $this->extension ?: parent::guessExtension();
    }
}
