<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Serializer;

use Sonata\CoreBundle\Serializer\BaseSerializerHandler;

/**
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 */
class GallerySerializerHandler extends BaseSerializerHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return 'sonata_media_gallery_id';
    }
}
