Installation
============

Make sure you have a ``Sonata`` directory, if not create it::

  mkdir src/Sonata

To begin, add the dependent bundles to the ``src/`` directory. If using
git, you can add them as submodules::

  git submodule add git@github.com:sonata-project/MediaBundle.git src/Sonata/MediaBundle

  // dependency bundles
  git submodule add git@github.com:sonata-project/AdminBundle.git src/Sonata/AdminBundle
  git submodule add git@github.com:sonata-project/EasyExtendsBundle.git src/Sonata/EasyExtendsBundle

  
Add the ``Imagine`` image processing library

  git submodule add git://github.com/avalanche123/Imagine.git src/vendor/imagine


Next, be sure to enable the bundles in your application kernel:

.. code-block:: php

  // app/AppKernel.php
  public function registerBundles()
  {
      return array(
          // ...
          new Sonata\MediaBundle\SonataMediaBundle(),
          new Sonata\AdminBundle\SonataAdminBundle(),
          new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
          // ...
      );
  }

Configuration
-------------

To use the ``AdminBundle``, add the following to your application
configuration file.

.. code-block:: yaml

    # app/config/config.yml
    sonata_media:
        class: Sonata\MediaBundle\Provider\Service

        settings:
            cdn_enabled: false
            cdn_path:     http://media.sonata-project.org
            public_path: /uploads/media
            private_path: /web/uploads/media

        providers:

            file:
                class: Sonata\MediaBundle\Provider\FileProvider
                
            image:
                class: Sonata\MediaBundle\Provider\ImageProvider
                formats:
                    small: { width: 100 }
                    big:   { width: 500 }

            youtube:
                class: Sonata\MediaBundle\Provider\YouTubeProvider
                formats:
                    small: { width: 100 }
                    big:   { width: 500 }

            dailymotion:
                class: Sonata\MediaBundle\Provider\DailyMotionProvider
                formats:
                    small: { width: 100 }
                    big:   { width: 500 }

.. note::

    You can define formats per provider type. You might want to set
    an transversal ``admin`` format to be used by the ``MediaAdmin`` class.
