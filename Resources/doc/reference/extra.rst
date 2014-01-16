Extra
=====

The SonataMediaBundle provides some integration with externals services. For now only Pixlr is available.

Pixlr Integration
-----------------

Edit the ``sonata_media`` configuration:

.. code-block:: yaml

    # app/config/sonata_media.yml
    sonata_media:
        # ...
        pixlr:
            enabled:  true
            secret:   theSecretHash
            referrer: Application Name


Add the pixlr routing file

.. code-block:: yaml

    # app/config/routing.yml
    sonata_media_pixlr:
        resource: '@SonataMediaBundle/Resources/config/routing/pixlr.xml'
        prefix: /admin/media

And now, you can edit any pictures from the admin section.

Sonata Notification Bundle Integration
======================================

The bundle provides a specific consumer to generate thumbnails through an asynchronous task. So there no processing
time for the user after uploading a file.

In order to use this feature, you need to install the Sonata Notification Bundle and change the thumbnail configuration
for each provider:

.. code-block:: yaml

    sonata_media:
        # ...
        providers:
            # ...
            image:
                thumbnail:  sonata.media.thumbnail.consumer.format
            vimeo:
                thumbnail:  sonata.media.thumbnail.consumer.format
            youtube:
                thumbnail:  sonata.media.thumbnail.consumer.format
            dailymotion:
                thumbnail:  sonata.media.thumbnail.consumer.format


Liip Imagine Bundle Integration
===============================

The bundle provides a support for LiipImagineBundle through a specific Thumbnail service. The service generates a valid
url handles by the bundle. The main advantage of LiipImageBundle is that no thumbnail images will be stored along the
uploaded image.

The first step is to install the LiipImagineBundle, then you need to configure it by creating custom filter sets.
Each set name must be composed of CONTEXTNAME_FORMATNAME, so for the context: default and the format: small, you must
have a set named default_small.


.. code-block:: yaml

    liip_imagine:
        filter_sets:
            default_small:
                quality: 75
                controller_action: 'SonataMediaBundle:Media:liipImagineFilter'
                filters:
                    thumbnail: { size: [100, 70], mode: outbound }


            default_big:
                quality: 75
                controller_action: 'SonataMediaBundle:Media:liipImagineFilter'
                filters:
                    thumbnail: { size: [500, 70], mode: outbound }

You also need to alter the ``sonata_media`` configuration to use the ``sonata.media.thumbnail.liip_imagine`` thumbnail service

.. code-block:: yaml

    sonata_media:
        # ...
        providers:
            # ...
            image:
                thumbnail:  sonata.media.thumbnail.liip_imagine
            vimeo:
                thumbnail:  sonata.media.thumbnail.liip_imagine
            youtube:
                thumbnail:  sonata.media.thumbnail.liip_imagine
            dailymotion:
                thumbnail:  sonata.media.thumbnail.liip_imagine


        cdn:
            # The CDN part must point to the base root of your application with a valid htaccess to match non existant
            # file. The non existant image will be send to the SonataMediaBundle:Media:liipImagineFilter controller.
            server:
                path:      http://mydomain.com


.. note::

    The ``SonataMediaBundle:Media:liipImagineFilter`` is a specific controller to link the MediaBundle with LiipImagineBundle

