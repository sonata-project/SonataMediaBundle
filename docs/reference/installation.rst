.. index::
    single: Installation
    single: Configuration

Installation
============

Prerequisites
-------------

PHP ^7.2 and Symfony ^4.4 are needed to make this bundle work, there are
also some Sonata dependencies that need to be installed and configured beforehand.

Optional dependencies:

* `SonataAdminBundle <https://sonata-project.org/bundles/admin>`_
* `SonataClassificationBundle <https://sonata-project.org/bundles/classification>`_

And the persistence bundle (choose one):

* `SonataDoctrineOrmAdminBundle <https://sonata-project.org/bundles/doctrine-orm-admin>`_
* `SonataDoctrinePHPCRAdminBundle <https://sonata-project.org/bundles/doctrine-phpcr-admin>`_
* `SonataDoctrineMongoDBAdminBundle <https://sonata-project.org/bundles/mongo-admin>`_

Follow also their configuration step; you will find everything you need in
their own installation chapter.

.. note::

    If a dependency is already installed somewhere in your project or in
    another dependency, you won't need to install it again.

Install Symfony Flex packs
--------------------------

With this method you can directly setup all the entities required to make this bundle work
with the different persistence bundles supported.

If you picked ``SonataDoctrineOrmAdminBundle``, install the Sonata Media ORM pack::

    composer require sonata-project/media-orm-pack

If you picked ``SonataDoctrineMongoDBAdminBundle``, install the Sonata Media ODM pack::

    composer require sonata-project/media-odm-pack

Install without Symfony Flex packs
----------------------------------

Add ``SonataMediaBundle`` via composer::

    composer require sonata-project/media-bundle

To load external resources, e.g. Vimeo or YouTube, you must use a ``psr/http-client`` and ``psr/http-factory``::

    composer require symfony/http-client nyholm/psr7

If you want to use the REST API, you also need ``friendsofsymfony/rest-bundle`` and ``nelmio/api-doc-bundle``::

    composer require friendsofsymfony/rest-bundle nelmio/api-doc-bundle

Next, be sure to enable the bundles in your ``config/bundles.php`` file if they
are not already enabled::

    // config/bundles.php

    return [
        // ...
        Sonata\MediaBundle\SonataMediaBundle::class => ['all' => true],
    ];

Configuration
=============

SonataMediaBundle Configuration
-------------------------------

.. code-block:: yaml

    # config/packages/sonata_media.yaml

    sonata_media:
        class:
            media: App\Entity\SonataMediaMedia
            gallery: App\Entity\SonataMediaGallery
            gallery_item: App\Entity\SonataMediaGalleryItem
        db_driver: doctrine_orm # or doctrine_mongodb, doctrine_phpcr it is mandatory to choose one here
        default_context: default # you need to set a context
        contexts:
            default:  # the default context is mandatory
                providers:
                    - sonata.media.provider.dailymotion
                    - sonata.media.provider.youtube
                    - sonata.media.provider.image
                    - sonata.media.provider.file
                    - sonata.media.provider.vimeo
                formats:
                    small: { width: 100 , quality: 70}
                    big: { width: 500 , quality: 70}
        cdn:
            server:
                path: /uploads/media # http://media.sonata-project.org/
        filesystem:
            local:
                directory: '%kernel.root_dir%/../public/uploads/media'
                create: false

.. note::

    You can define formats per provider type. You might want to set
    a transversal ``admin`` format to be used by the ``mediaadmin`` class.

Also, you can determine the resizer to use; the default value is
``sonata.media.resizer.simple`` but you can change it to ``sonata.media.resizer.square`` or ``sonata.media.resizer.crop``

.. code-block:: yaml

    # config/packages/sonata_media.yaml

    sonata_media:
        providers:
            image:
                resizer: sonata.media.resizer.square

.. note::

    The square resizer works like the simple resizer when the image format has
    only the width. But if you specify the height the resizer crop the image in
    the lower size.

    The crop resizer crops the image to the exact width and height. This is done by
    resizing the image first and cropping the unwanted parts at the end.

Doctrine ORM Configuration
--------------------------

Add the bundle in the config mapping definition (or enable `auto_mapping`_)::

    # config/packages/doctrine.yaml

    doctrine:
        orm:
            entity_managers:
                default:
                    mappings:
                        SonataMediaBundle: ~

And then create the corresponding entities, ``src/Entity/SonataMediaMedia``::

    // src/Entity/SonataMediaMedia.php

    use Doctrine\ORM\Mapping as ORM;
    use Sonata\MediaBundle\Entity\BaseMedia;

    /**
     * @ORM\Entity
     * @ORM\Table(name="media__media")
     */
    class SonataMediaMedia extends BaseMedia
    {
        /**
         * @ORM\Id
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         */
        protected $id;
    }

``src/Entity/SonataMediaGallery``::

    // src/Entity/SonataMediaGallery.php

    use Doctrine\ORM\Mapping as ORM;
    use Sonata\MediaBundle\Entity\BaseGallery;

    /**
     * @ORM\Entity
     * @ORM\Table(name="media__gallery")
     */
    class SonataMediaGallery extends BaseGallery
    {
        /**
         * @ORM\Id
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         */
        protected $id;
    }

and ``src/Entity/SonataMediaGalleryItem``::

    // src/Entity/SonataMediaGalleryItem.php

    use Doctrine\ORM\Mapping as ORM;
    use Sonata\MediaBundle\Entity\BaseGalleryItem;

    /**
     * @ORM\Entity
     * @ORM\Table(name="media__gallery_item")
     */
    class SonataMediaGalleryItem extends BaseGalleryItem
    {
        /**
         * @ORM\Id
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         */
        protected $id;
    }

The only thing left is to update your schema::

    bin/console doctrine:schema:update --force

Doctrine PHPCR Configuration
----------------------------

Add the bundle in the config mapping definition (or enable `auto_mapping`_)::

    # config/packages/doctrine_phpcr.yaml

    doctrine_phpcr:
        odm:
            mappings:
                SonataMediaBundle:
                    prefix: Sonata\MediaBundle\PHPCR

Then you have to create the corresponding documents, ``src/PHPCR/SonataMediaMedia``::

    // src/PHPCR/SonataMediaMedia.php

    use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;
    use Sonata\MediaBundle\PHPCR\BaseMedia;

    /**
     * @PHPCR\Document
     */
    class SonataMediaMedia extends BaseMedia
    {
        /**
         * @PHPCR\Id
         */
        protected $id;
    }

``src/PHPCR/SonataMediaGallery``::

    // src/PHPCR/SonataMediaGallery.php

    use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;
    use Sonata\MediaBundle\PHPCR\BaseGallery;

    /**
     * @PHPCR\Document
     */
    class SonataMediaGallery extends BaseGallery
    {
        /**
         * @PHPCR\Id
         */
        protected $id;
    }

and ``src/PHPCR/SonataMediaGalleryItem``::

    // src/PHPCR/SonataMediaGalleryItem.php

    use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;
    use Sonata\MediaBundle\PHPCR\BaseGalleryItem;

    /**
     * @PHPCR\Document
     */
    class SonataMediaGalleryItem extends BaseGalleryItem
    {
        /**
         * @PHPCR\Id
         */
        protected $id;
    }

And then configure ``SonataMediaBundle`` to use the newly generated classes::

    # config/packages/sonata_media.yaml

    sonata_media:
        db_driver: doctrine_phpcr
        class:
            media: App\PHPCR\SonataMediaMedia
            gallery: App\PHPCR\SonataMediaGallery
            gallery_item: App\PHPCR\SonataMediaGalleryItem

Doctrine MongoDB Configuration
------------------------------

Add the bundle in the config mapping definition (or enable `auto_mapping`_)::

    # config/packages/doctrine_mongodb.yaml

    doctrine_mongodb:
        odm:
            mappings:
                SonataMediaBundle: ~

Then you have to create the corresponding documents, ``src/Document/SonataMediaMedia``::

    // src/Document/SonataMediaMedia.php

    use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
    use Sonata\MediaBundle\Document\BaseMedia;

    /**
     * @MongoDB\Document
     */
    class SonataMediaMedia extends BaseMedia
    {
        /**
         * @MongoDB\Id
         */
        protected $id;
    }

``src/Document/SonataMediaGallery``::

    // src/Document/SonataMediaGallery.php

    use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
    use Sonata\MediaBundle\Document\BaseGallery;

    /**
     * @MongoDB\Document
     */
    class SonataMediaGallery extends BaseGallery
    {
        /**
         * @MongoDB\Id
         */
        protected $id;
    }

and ``src/Document/SonataMediaGalleryItem``::

    // src/Document/SonataMediaGalleryItem.php

    use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
    use Sonata\MediaBundle\Document\BaseGalleryItem;

    /**
     * @MongoDB\Document
     */
    class SonataMediaGalleryItem extends BaseGalleryItem
    {
        /**
         * @MongoDB\Id
         */
        protected $id;
    }

And then configure ``SonataMediaBundle`` to use the newly generated classes::

    # config/packages/sonata_media.yaml

    sonata_media:
        db_driver: doctrine_mongodb
        class:
            media: App\Document\SonataMediaMedia
            gallery: App\Document\SonataMediaGallery
            gallery_item: App\Document\SonataMediaGalleryItem

Add SonataMediaBundle routes
----------------------------

.. code-block:: yaml

    # config/routes.yaml

    gallery:
        resource: '@SonataMediaBundle/Resources/config/routing/gallery.xml'
        prefix: /media/gallery

    media:
        resource: '@SonataMediaBundle/Resources/config/routing/media.xml'
        prefix: /media

Create uploads folder
---------------------

If they are not already created, you need to add specific folder to allow uploads from users,
make sure your http user can write to this directory:

.. code-block:: bash

    mkdir -p public/uploads/media

Next Steps
----------

At this point, your Symfony installation should be fully functional, without errors
showing up from SonataMediaBundle. If, at this point or during the installation,
you come across any errors, don't panic:

    - Read the error message carefully. Try to find out exactly which bundle is causing the error.
      Is it SonataMediaBundle or one of the dependencies?
    - Make sure you followed all the instructions correctly, for both SonataMediaBundle and its dependencies.
    - Still no luck? Try checking the project's `open issues on GitHub`_.

.. _`open issues on GitHub`: https://github.com/sonata-project/SonataMediaBundle/issues
.. _`auto_mapping`: http://symfony.com/doc/4.4/reference/configuration/doctrine.html#configuration-overviews
