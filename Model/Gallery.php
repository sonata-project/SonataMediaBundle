<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

abstract class Gallery implements GalleryInterface
{
    /**
     * @var string
     */
    protected $context;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var \Datetime
     */
    protected $updatedAt;

    /**
     * @var \Datetime
     */
    protected $createdAt;

    /**
     * @var string
     */
    protected $defaultFormat = 'reference';

    /**
     * @var GalleryItemInterface[]
     */
    protected $galleryItems;

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getName() ?: '-';
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultFormat($defaultFormat)
    {
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultFormat()
    {
        return $this->defaultFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function setGalleryItems($galleryItems)
    {
        $this->galleryItems = new ArrayCollection();

        foreach ($galleryItems as $galleryItem) {
            $this->addGalleryItem($galleryItem);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getGalleryItems()
    {
        return $this->galleryItems;
    }

    /**
     * {@inheritdoc}
     */
    public function addGalleryItem(GalleryItemInterface $galleryItem)
    {
        $galleryItem->setGallery($this);

        $this->galleryItems[] = $galleryItem;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }
}
