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

class GalleryManager extends AbstractGalleryManager
{
    protected $modelManager;
    protected $class;

    /**
     * @param \Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager $modelManager
     * @param $class
     */
    public function __construct(ModelManager $modelManager, $class)
    {
        $this->modelManager = $modelManager;
        $this->class        = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function update(GalleryInterface $gallery)
    {
        $this->dm->persist($gallery);
        $this->dm->flush();
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
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria)
    {
        return $this->getRepository()->findBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(GalleryInterface $gallery)
    {
        $this->dm->remove($gallery);
        $this->dm->flush();
    }
}
