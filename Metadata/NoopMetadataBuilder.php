<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Metadata;

use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;

class NoopMetadataBuilder implements MetadataBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(MediaInterface $media, $filename)
    {
        return array();
    }
}
