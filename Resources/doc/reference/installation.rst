Installation
============

Make sure you have a ``Sonata`` directory. If you don't, create it::

  mkdir src/Sonata

To begin, add the dependent bundles to the ``src/`` directory. If you're
using git, you can add them as submodules::

  git submodule add git@github.com:Sonata-project/MediaBundle.git src/Sonata/MediaBundle

  // dependency bundles
  git submodule add git@github.com:Sonata-project/AdminBundle.git src/Sonata/AdminBundle
  git submodule add git@github.com:Sonata-project/EasyExtendsBundle.git src/Sonata/EasyExtendsBundle

Add the ``imagine`` image processing library

  git submodule add git://github.com/avalanche123/Imagine.git src/vendor/imagine

Add the ``gaufrette`` file abstraction library

  git submodule add git://github.com/knplabs/Gaufrette.git src/vendor/gaufrette

Next, be sure to enable the bundles in your application kernel:

.. code-block:: php

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

    php app/console Sonata:easy-extends:generate

.. note::

    The command will generate domain objects in an ``Application`` namespace.
    So you can point entities' associations to a global and common namespace.
    This will make Entities sharing very easier has your models will allows
    point to a global namespace. For instance the media will be
    ``Application\Sonata\MediaBundle\Entity\Media``.

Now, add the new `Application` Bundle into the kernel

.. code-block:: php

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

Update the ``autoload.php`` to add a new namespaces :

.. code-block:: php

    $loader->registerNamespaces(array(
        'Sonata'                             => __DIR__,
        'Application'                        => __DIR__,
        'Imagine'                            => __DIR__.'/vendor/imagine/lib',
        'Gaufrette'                          => __DIR__.'/vendor/gaufrette/src',

        // ... other declarations
    ));

Configuration
-------------

To use the ``AdminBundle``, add the following to your application configuration
file.

.. code-block:: yaml

    # app/config/config.yml
    Sonata_media:
        contexts:
            default:  # the default context is mandatory
                providers:
                    - Sonata.media.provider.dailymotion
                    - Sonata.media.provider.youtube
                    - Sonata.media.provider.image
                    - Sonata.media.provider.file

                formats:
                    small: { width: 100 , quality: 70}
                    big:   { width: 500 , quality: 70}

        cdn:
            Sonata.media.cdn.server:
                path: /uploads/media # http://media.Sonata-project.org/

        filesystem:
            Sonata.media.adapter.filesystem.local:
                directory:  %kernel.root_dir%/../web/uploads/media
                create:     false

.. note::

    You can define formats per provider type. You might want to set
    a transversal ``admin`` format to be used by the ``mediaadmin`` class.
