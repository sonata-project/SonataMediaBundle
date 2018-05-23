Installation
============

Base bundles
------------

This bundle is mainly dependant of:

* Core: https://sonata-project.org/bundles/core

This bundle has optional dependencies of:

 * Admin: https://sonata-project.org/bundles/admin
 * DoctrineOrm: https://sonata-project.org/bundles/doctrine-orm-admin
 * MongoAdmin: https://sonata-project.org/bundles/mongo-admin
 * Classification: https://sonata-project.org/bundles/classification

So be sure you have installed those bundles before starting

Installation
------------

Retrieve the bundle with composer:

.. code-block:: bash

    $ composer require sonata-project/media-bundle
    $ composer require sonata-project/classification-bundle # (optional: if you need media classification)

Register these bundles in your ``bundles.php`` file:

.. code-block:: php

    <?php

    // config/bundles.php

    return [
        //...
        Sonata\MediaBundle\SonataMediaBundle::class => ['all' => true],
        Sonata\EasyExtendsBundle\SonataEasyExtendsBundle::class => ['all' => true],
        JMS\SerializerBundle\JMSSerializerBundle::class => ['all' => true],
    ];

.. note::
    If you are not using Symfony Flex, you should enable bundles in your
    ``AppKernel.php``.


.. code-block:: php

  <?php
  // app/AppKernel.php

  public function registerBundles()
  {
      return array(
          // ...
          new Sonata\MediaBundle\SonataMediaBundle(),
          new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),

          // You need to add this dependency to make media functional
          new JMS\SerializerBundle\JMSSerializerBundle(),
          // ...
      );
  }

Next, add the correct routing files:

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml

        gallery:
            resource: '@SonataMediaBundle/Resources/config/routing/gallery.xml'
            prefix: /media/gallery

        media:
            resource: '@SonataMediaBundle/Resources/config/routing/media.xml'
            prefix: /media

.. note::
    If you are not using Symfony Flex, routes should be added to ``app/config/routing.yml``.

Then, you must configure the interaction with the persistence backend you picked:

If you picked Doctrine ORM:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml

        doctrine:
            orm:
                entity_managers:
                    default:
                        mappings:
                            SonataMediaBundle: ~

            dbal:
                types:
                    json: Sonata\Doctrine\Types\JsonType

.. note::
    If you are not using Symfony Flex, this configuration should be added
    to ``app/config/config.yml``.

If you picked Doctrine PHPCR:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml

        doctrine_phpcr:
            odm:
                auto_mapping: true
                mappings:
                    SonataMediaBundle:
                        prefix: Sonata\MediaBundle\PHPCR

.. note::
    If you are not using Symfony Flex, this configuration should be added
    to ``app/config/config.yml``.

Once you have done that, you can configure the Media bundle itself:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/sonata.yaml

        sonata_media:
            # if you don't use default namespace configuration
            #class:
            #    media:        MyVendor\MediaBundle\Entity\Media
            #    gallery:      MyVendor\MediaBundle\Entity\Gallery
            #    gallery_item: MyVendor\MediaBundle\Entity\GalleryItem
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
                    directory:  "%kernel.root_dir%/../public/uploads/media" #flex
                    #directory:  "%kernel.root_dir%/../web/uploads/media" #non-flex
                    create:     false

.. note::
    If you are not using Symfony Flex, this configuration should be added
    to ``app/config/config.yml``.

.. note::

    You can define formats per provider type. You might want to set
    a transversal ``admin`` format to be used by the ``mediaadmin`` class.

Also, you can determine the resizer to use; the default value is
``sonata.media.resizer.simple`` but you can change it to ``sonata.media.resizer.square``

.. configuration-block::

    .. code-block:: yaml

        # config/packages/sonata.yaml

        sonata_media:
            providers:
                image:
                    resizer: sonata.media.resizer.square

.. note::
    If you are not using Symfony Flex, this configuration should be added
    to ``app/config/config.yml``.

.. note::

    The square resizer works like the simple resizer when the image format has
    only the width. But if you specify the height the resizer crop the image in
    the lower size.

At this point, the bundle is not yet ready. You need to generate the correct
entities for the media::

    bin/console sonata:easy-extends:generate --dest=src SonataMediaBundle --namespace_prefix=App

.. note::
    If you are not using Symfony Flex, use command without ``--namespace_prefix=App``.

.. note::

    To be able to generate domain objects, you need to have a database driver configure in your project.
    If it's not the case, just follow this:
    http://symfony.com/doc/current/book/doctrine.html#configuring-the-database

.. note::

    The command will generate domain objects in an ``App\Application`` namespace.
    So you can point entities' associations to a global and common namespace.
    This will make Entities sharing very easier as your models will allow to
    point to a global namespace. For instance the media will be
    ``App\Application\Sonata\MediaBundle\Entity\Media``.

.. note::
    If you are not using Symfony Flex, the namespace will be ``Application\Sonata\MediaBundle\Entity\Media``.

Now, add the new ``Application`` Bundle into the ``bundles.php``:

.. code-block:: php

    <?php

    // config/bundles.php

    return [
        //...
        App\Application\Sonata\MediaBundle\ApplicationSonataMediaBundle::class => ['all' => true],
    ];

.. note::
    If you are not using Symfony Flex, add the new ``Application`` Bundle into your
    ``AppKernel.php``.

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

Configure SonataMediaBundle to use the newly generated classes:

.. note::
    If you are not using Symfony Flex, add classes without the ``App\``
    part.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/sonata.yaml

        sonata_media:
            # if you don't use default namespace configuration
            class:
                media: App\Application\Sonata\MediaBundle\Entity\Media
                gallery: App\Application\Sonata\MediaBundle\Entity\Gallery
                gallery_has_media: App\Application\Sonata\MediaBundle\Entity\GalleryHasMedia

            #...


.. note::
    If you are not using Symfony Flex, this configuration should be added
    to ``app/config/config.yml``.

If you are not using auto-mapping in doctrine you will have to add it there
too:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml

        doctrine:
            orm:
                entity_managers:
                    default:
                        mappings:
                            ApplicationSonataMediaBundle: ~
                            SonataMediaBundle: ~

.. note::
    If you are not using Symfony Flex, this configuration should be added
    to ``app/config/config.yml``.

You will have to exclude your ``Application`` folder from Symfony service
autowiring:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\:
                resource: '../src/*'
                exclude: '../src/{Entity,Tests,Application}'

.. note::
    If you are not using Symfony Flex, skip this part.

Now, you can build up your database:

.. code-block:: bash

    $ bin/console doctrine:schema:[create|update]


If they are not already created, you need to add specific folder to allow uploads from users,
make sure your http user can write to this directory:

.. code-block:: bash

    $ mkdir -p public/uploads/media

.. note::
    If you are not using Symfony Flex, replace ``public`` with ``web`` folder.

Then you can visit your admin dashboard on http://my-server/admin/dashboard
