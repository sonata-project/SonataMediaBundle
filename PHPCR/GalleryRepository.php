<?php

namespace Sandbox\MediaBundle\Document;

use Doctrine\ODM\PHPCR\Id\RepositoryIdInterface;
use Doctrine\ODM\PHPCR\DocumentRepository as BaseDocumentRepository;

class GalleryRepository extends BaseDocumentRepository implements RepositoryIdInterface
{
    /**
     * Generate a document id
     *
     * @param object $document
     * @param object $parent
     *
     * @return string
     */
    public function generateId($document, $parent = null) 
    {
        $idPrefix = $document->getIdPrefix();

        if (0 == strlen($idPrefix)) {
            throw new \LogicException('Can not determine the prefix. Either this is a new, unpersisted document or the listener that calls setPrefix is not set up correctly.');
        }

        return $idPrefix.'/'.$document->getName();
    }
}
