Installation
============

To begin, add the dependent bundles to the ``src/`` directory. If you're
using git, you can add them as submodules::

  git submodule add git://github.com/sonata-project/SonataMediaBundle.git  vendor/bundles/Sonata/MediaBundle

  // dependency bundles
  git submodule add git://github.com/sonata-project/SonataAdminBundle.git vendor/bundles/Sonata/AdminBundle
  git submodule add git://github.com/sonata-project/SonataEasyExtendsBundle.git vendor/bundles/Sonata/EasyExtendsBundle

Add the ``Imagine`` image processing library::

  git submodule add git://github.com/avalanche123/Imagine.git vendor/imagine

Add the ``Gaufrette`` file abstraction library::

  git submodule add git://github.com/knplabs/Gaufrette.git vendor/gaufrette

Next, be sure to enable the bundles in your application kernel:

.. code-block:: php

  <?php
  // app/appkernel.php
  public function registerbundles()
  {
      return array(
          // ...
          new Sonata\MediaBundle\SonataMediaBundle(),
          new Sonata\AdminBundle\SonataAdminBundle(),
          new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
          // ...
      );
  }

At this point, the bundle is not yet ready. You need to generate the correct
entities for the media::

    php app/console sonata:easy-extends:generate SonataMediaBundle

.. note::

    The command will generate domain objects in an ``Application`` namespace.
    So you can point entities' associations to a global and common namespace.
    This will make Entities sharing very easier as your models will allow to
    point to a global namespace. For instance the media will be
    ``Application\Sonata\MediaBundle\Entity\Media``.

Now, add the new `Application` Bundle into the kernel

.. code-block:: php

  <?php
  public function registerbundles()
  {
      return array(
          // Application Bundles
          new Application\Sonata\MediaBundle\ApplicationSonataMediaBundle(),

          // Vendor specifics bundles
          new Sonata\MediaBundle\SonataMediaBundle(),
          new Sonata\AdminBundle\SonataAdminBundle(),
          new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
      );
  }

Update the ``autoload.php`` to add a new namespace:

.. code-block:: php

  <?php
  $loader->registerNamespaces(array(
    'Sonata'        => __DIR__,
    'Application'   => __DIR__,
    'Imagine'       => __DIR__.'/../vendor/imagine/lib',
    'Gaufrette'     => __DIR__.'/../vendor/gaufrette/src',

    // ... other declarations
  ));

Then add these bundles in the doctrine mapping definition:

.. code-block:: yaml

    # app/config/config.yml

    doctrine:
        orm:
            entity_managers:
                default:
                    mappings:
                        ApplicationSonataMediaBundle: ~
                        SonataMediaBundle: ~


Configuration
-------------

To use the ``AdminBundle``, add the following to your application configuration
file.

.. code-block:: yaml

    # app/config/config.yml
    sonata_media:
        db_driver: doctrine_orm # or doctrine_mongodb
        contexts:
            default:  # the default context is mandatory
                providers:
                    - sonata.media.provider.dailymotion
                    - sonata.media.provider.youtube
                    - sonata.media.provider.image
                    - sonata.media.provider.file

                formats:
                    small: { width: 100 , quality: 70}
                    big:   { width: 500 , quality: 70}

        cdn:
            sonata.media.cdn.server:
                path: /uploads/media # http://media.sonata-project.org/

        filesystem:
            sonata.media.adapter.filesystem.local:
                directory:  %kernel.root_dir%/../web/uploads/media
                create:     false

.. note::

    You can define formats per provider type. You might want to set
    a transversal ``admin`` format to be used by the ``mediaadmin`` class.
