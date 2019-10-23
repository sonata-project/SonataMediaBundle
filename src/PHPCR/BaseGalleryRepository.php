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

namespace Sonata\MediaBundle\PHPCR;

use Doctrine\ODM\PHPCR\DocumentRepository;
use Doctrine\ODM\PHPCR\Id\RepositoryIdInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class BaseGalleryRepository extends DocumentRepository implements RepositoryIdInterface
{
    /**
     * @param mixed $document
     * @param mixed $parent
     *
     * @return string
     */
    public function generateId($document, $parent = null)
    {
        return '/cms/gallery/'.uniqid();
    }
}
