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

namespace Sonata\MediaBundle\Extra;

use Symfony\Component\HttpFoundation\File\File;

final class ApiMediaFile extends File
{
    /**
     * @var string|null
     */
    private $extension;

    /**
     * @var string|null
     */
    private $mimetype;

    /**
     * @param resource $handle
     */
    public function __construct($handle)
    {
        if (!\is_resource($handle)) {
            throw new \RuntimeException('handle is not a resource');
        }

        $meta = stream_get_meta_data($handle);

        parent::__construct($meta['uri']);
    }

    public function getExtension(): string
    {
        return $this->extension ?? parent::getExtension();
    }

    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    public function getMimeType(): ?string
    {
        return $this->mimetype ?? parent::getMimeType();
    }

    public function setMimetype(string $mimetype): void
    {
        $this->mimetype = $mimetype;
    }

    public function guessExtension(): ?string
    {
        return $this->extension ?? parent::guessExtension();
    }
}
