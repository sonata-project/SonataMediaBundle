Installation
============

Base bundles
------------

This bundle is mainly dependant of:

* Classification: https://sonata-project.org/bundles/classification
* Core: https://sonata-project.org/bundles/core
* Intl: https://sonata-project.org/bundles/intl

This bundle has optional dependancies of:

 * Admin: https://sonata-project.org/bundles/admin
 * DoctrineOrm: https://sonata-project.org/bundles/doctrine-orm-admin
 * MongoAdmin: https://sonata-project.org/bundles/mongo-admin

So be sure you have installed those bundles before starting

Installation
------------

Retrieve the bundle with composer:

.. code-block:: bash

    $ composer require sonata-project/media-bundle

Register these bundles in your AppKernel:

.. code-block:: php

  <?php
  // app/AppKernel.php

  public function registerBundles()
  {
      return array(
          // ...
          new Sonata\MediaBundle\SonataMediaBundle(),
          new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
          new Sonata\IntlBundle\SonataIntlBundle(),

          // You need to add this dependency to make media functional
          new JMS\SerializerBundle\JMSSerializerBundle(),
          // ...
      );
  }

Next, add the correct routing files:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml

        gallery:
            resource: '@SonataMediaBundle/Resources/config/routing/gallery.xml'
            prefix: /media/gallery

        media:
            resource: '@SonataMediaBundle/Resources/config/routing/media.xml'
            prefix: /media


Then you must configure the interaction with the orm and add the mediaBundles settings:

Doctrine ORM:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        doctrine:
            orm:
                entity_managers:
                    default:
                        mappings:
                            SonataMediaBundle: ~

            dbal:
                types:
                    json: Sonata\Doctrine\Types\JsonType

Doctrine PHPCR:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        doctrine_phpcr:
            odm:
                auto_mapping: true
                mappings:
                    SonataMediaBundle:
                        prefix: Sonata\MediaBundle\PHPCR

    .. code-block:: yaml

        # app/config/config.yml

        sonata_media:
            # if you don't use default namespace configuration
            #class:
            #    media: MyVendor\MediaBundle\Entity\Media
            #    gallery: MyVendor\MediaBundle\Entity\Gallery
            #    gallery_has_media: MyVendor\MediaBundle\Entity\GalleryHasMedia
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
                        big:   { width: 500 , quality: 70}

            cdn:
                server:
                    path: /uploads/media # http://media.sonata-project.org/

            filesystem:
                local:
                    directory:  "%kernel.root_dir%/../web/uploads/media"
                    create:     false

.. note::

    You can define formats per provider type. You might want to set
    a transversal ``admin`` format to be used by the ``mediaadmin`` class.

Also, you can determine the resizer to use; the default value is
``sonata.media.resizer.simple`` but you can change it to ``sonata.media.resizer.square``

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        sonata_media:
            providers:
                image:
                    resizer: sonata.media.resizer.square

.. note::

    The square resizer works like the simple resizer when the image format has
    only the width. But if you specify the height the resizer crop the image in
    the lower size.

At this point, the bundle is not yet ready. You need to generate the correct
entities for the media::

    php app/console sonata:easy-extends:generate --dest=src SonataMediaBundle

.. note::

    To be able to generate domain objects, you need to have a database driver configure in your project.
    If it's not the case, just follow this:
    http://symfony.com/doc/current/book/doctrine.html#configuring-the-database

.. note::

    The command will generate domain objects in an ``Application`` namespace.
    So you can point entities' associations to a global and common namespace.
    This will make Entities sharing very easier as your models will allow to
    point to a global namespace. For instance the media will be
    ``Application\Sonata\MediaBundle\Entity\Media``.


Now that your module is generated, you can register it

.. code-block:: php

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        return array(
            // ...
            new Application\Sonata\MediaBundle\ApplicationSonataMediaBundle(),
            // ...
        );
    }

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        doctrine:
            orm:
                entity_managers:
                    default:
                        mappings:
                            ApplicationSonataMediaBundle: ~
                            SonataMediaBundle: ~

Now, you can build up your database:

.. code-block:: bash

    $ app/console doctrine:schema:[create|update]


If they are not already created, you need to add specific folder to allow uploads from users:

.. code-block:: bash

    $ mkdir web/uploads
    $ mkdir web/uploads/media
    $ chmod -R 0777 web/uploads

Then you can visit your admin dashboard on http://my-server/admin/dashboard
