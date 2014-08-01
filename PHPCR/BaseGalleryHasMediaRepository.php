<?php

namespace Sonata\MediaBundle\PHPCR;

use Doctrine\ODM\PHPCR\DocumentRepository;
use Doctrine\ODM\PHPCR\Id\RepositoryIdInterface;

class GalleryHasMediaRepository extends DocumentRepository implements RepositoryIdInterface
{
    public function generateId($document, $parent = null)
    {
        return '/cms/gallery-has-media/'  . uniqid();
    }
}
