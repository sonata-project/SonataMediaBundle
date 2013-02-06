<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\MediaBundle\PHPCR;

use Sonata\MediaBundle\Model\GalleryManager as AbstractGalleryManager;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager;
use Sonata\MediaBundle\PHPCR\MediaManager;

class GalleryManager extends AbstractGalleryManager
{
    protected $modelManager;
    protected $class;
    protected $mediaManager;

    /**
     * @param \Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager $modelManager
     * @param $class
     */
    public function __construct(ModelManager $modelManager, $class, MediaManager $mediaManager)
    {
        $this->modelManager = $modelManager;
        $this->class        = $class;
        $this->mediaManager = $mediaManager;
    }

    /**
     * {@inheritdoc}
     */
    public function update(GalleryInterface $gallery)
    {
        $this->modelManager->update($gallery);
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        return $this->mediaManager->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria)
    {
        return $this->mediaManager->findBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(GalleryInterface $gallery)
    {
        $this->modelManager->delete($gallery);
    }
}
