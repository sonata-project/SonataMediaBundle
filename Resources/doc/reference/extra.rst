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
