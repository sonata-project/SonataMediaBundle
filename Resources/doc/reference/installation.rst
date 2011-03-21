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

Add the ``Gaufrette`` file abstraction library

  git submodule add git://github.com/knplabs/Gaufrette.git src/vendor/gaufrette

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
        contexts:
            defaults:
                providers:
                    - sonata.media.provider.dailymotion
                    - sonata.media.provider.youtube
                    - sonata.media.provider.image
                    - sonata.media.provider.file

        # the CDN defined how the media is accessible from the web
        cdn:
            sonata.media.cdn.server:
                path: /uploads/media # http://media.sonata-project.org

        # the filesystem defined how the media is accessible from the PHP
        filesystem:
            sonata.media.adapter.filesystem.local:
                directory:  %kernel.root_dir%/../web/uploads/media
                create:     false

        # configure the different providers
        providers:
            sonata.media.provider.file:
                formats:
                resizer:    false

            sonata.media.provider.image:
                resizer:    sonata.media.resizer.simple             # optional key, default value : sonata.media.resizer.simple
                cdn:        sonata.media.cdn.server                 # optional key, default value : sonata.media.cdn.server
                filesystem: sonata.media.adapter.filesystem.local   # optional key, default value : sonata.media.adapter.filesystem.local
                formats:
                    small: { width: 100 , quality: 70}
                    big:   { width: 500 , quality: 70}
                    admin: { width: 300}

            sonata.media.provider.youtube:
                formats:
                    small: { width: 100 , quality: 70}
                    big:   { width: 500 , quality: 70}
                    admin: { width: 300}

            sonata.media.provider.dailymotion:
                formats:
                    small: { width: 100 , quality: 70}
                    big:   { width: 500 , quality: 70}
                    admin: { width: 300}

.. note::

    You can define formats per provider type. You might want to set
    an transversal ``admin`` format to be used by the ``MediaAdmin`` class.

Defining provider service
-------------------------

You can declare new Provider service by using the tag ``sonata.media.provider``, as :

.. code-block:: xml

        <service id="sonata.media.provider.dailymotion" class="MyClass">
            <tag name="sonata.media.provider" />
            <argument>mycode</argument>
            <argument type="service" id="the_default_orm_service" />

            <call method="setTemplates">
                <argument type="collection">
                    <argument key='admin_edit'>SonataMediaBundle:MediaAdmin:provider_edit_youtube.html.twig</argument>
                    <argument key='admin_create'>SonataMediaBundle:MediaAdmin:provider_create_youtube.html.twig</argument>
                    <argument key='helper_thumbnail'>SonataMediaBundle:Provider:thumbnail.html.twig</argument>
                    <argument key='helper_view'>SonataMediaBundle:Provider:view_youtube.html.twig</argument>
                </argument>
            </call>
        </service>
