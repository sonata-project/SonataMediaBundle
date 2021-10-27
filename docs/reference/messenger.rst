Messenger
=========

The bundle provides a specific thumbnail class to generate thumbnails through an asynchronous task. So there no processing
time for the user after uploading a file.

It is recommended to read about `Symfony Messenger`_ if it is your first time using it.

First, you need to install Symfony Messenger:

.. code-block:: bash

    composer require symfony/messenger

After installing the dependency, you need to enable the integration and change the thumbnail configuration
for each provider:

.. code-block:: yaml

    # config/packages/sonata_media.yaml

    sonata_media:
        messenger:
            enabled: true
        providers:
            image:
                thumbnail: sonata.media.thumbnail.messenger
            vimeo:
                thumbnail: sonata.media.thumbnail.messenger
            youtube:
                thumbnail: sonata.media.thumbnail.messenger
            dailymotion:
                thumbnail: sonata.media.thumbnail.messenger

To handle async messages, make sure you configure messenger with an async transport:

.. code-block:: yaml

    # config/packages/messenger.yaml

    framework:
        messenger:
            transports:
                async: '%env(MESSENGER_TRANSPORT_DSN)%'
            routing:
                'Sonata\MediaBundle\Messenger\GenerateThumbnailsMessage': async

You can also change the default bus for generate thumbnails:

.. code-block:: yaml

    # config/packages/sonata_media.yaml

    sonata_media:
        messenger:
            enabled: true
            generate_thumbnails_bus: my.defined.bus

I which case, make sure you define that bus on messenger configuration:

.. code-block:: yaml

    # config/packages/messenger.yaml

    framework:
        messenger:
            buses:
                my.defined.bus:

.. _`Symfony Messenger`: https://symfony.com/doc/current/messenger.html
