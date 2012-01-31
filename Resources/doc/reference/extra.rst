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
    media_pixlr:
        resource: '@SonataMediaBundle/Resources/config/routing/pixlr.xml'
        prefix: /admin/media

And now, you can edit any pictures from the admin section.