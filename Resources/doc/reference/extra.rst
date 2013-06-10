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

The Liip Imagine Bundle will perform the transformation called *thumbnail*, which you can define to do a number of
different things, such as resizing, cropping, drawing, masking, etc. The difference with the default behaviour is that
the thumbnail is generated when a user hits the media url the first time.

Refer to the bundles documentation for more information: `LiipImagineBundle <https://github.com/liip/LiipImagineBundle>`_

The helpers remain the same, fe. rendering an image using the format small:

.. code-block:: jinja

    <img src="{% path media, 'small' %}" />

In order to use this feature, you need to install the Liip Imagine Bundle and change the configuration:

.. code-block:: yaml

    # app/config/config.yml

    sonata_media:
        # ...
        contexts:
            default:  # the default context is mandatory
                providers:
                    # ...
                    - sonata.media.provider.image
                    # ...

                formats:
                    # if using liip_imagine these formats are only used in the view of the admin
                    # define a format as filter_set in liip_imagine: {context}_{format}
                    small: { width: 100 , quality: 70}
                    big:   { width: 500 , quality: 100}

        cdn:
            # define the base url for the media
            server:
                path: ~ # if using liip_imagine leave this empty to have correct thumbnail urls generated

        filesystem:
            # define where the uploaded file will be stored and it's relative path from web
            # if using liip_imagine only the reference file will be saved here
            local:
                directory: %kernel.root_dir%/../web/uploads/media
                create: true
                relative_web_path: /uploads/media

        # ...
        providers:
            # disable the file resizer
            file:
                resizer: false
            # change the thumbnail to liip_imagine for each provider
            image:
                thumbnail: sonata.media.thumbnail.liip_imagine


    liip_imagine:
        # ...
        filter_sets:
            # sonata media admin list thumbs
            admin:
                quality: 70 # default is 100
                controller_action: 'SonataMediaBundle:Media:liipImagineFilter'
                filters:
                    thumbnail: { size: [75, 60], mode: outbound }

            # sonata media admin context default filters
            default_small:
                quality: 70
                controller_action: 'SonataMediaBundle:Media:liipImagineFilter'
                filters:
                    thumbnail: { size: [100, 75], mode: outbound }

            default_big:
                controller_action: 'SonataMediaBundle:Media:liipImagineFilter'
                filters:
                    thumbnail: { size: [500, 200], mode: outbound }
