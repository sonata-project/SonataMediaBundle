Helpers
=======

The bundle comes with different helpers to render the thumbnail or the media
itself. The thumbnail always represents the media's image preview (i.e. the
thumbnail of a flash video). And the media helper generates the media itself
(i.e. the flash video).

PHP Usage
---------

Render the thumbnail:

.. code-block:: php

    <?php echo $view['sonata_media']->thumbnail($media, 'small') ?>

    <?php echo $view['sonata_media']->thumbnail($media, 'small', array(
        'class' => 'myclass'
    ) ?>

Render the media:

.. code-block:: php

    <?php echo $view['sonata_media']->media($media, 'small') ?>

    <?php echo $view['sonata_media']->media($media, 'small', array(
        'class' => 'myclass'
    ) ?>

Render the path:

.. code-block:: php

    <?php echo $view['sonata_media']->path($media, 'small') ?>

Twig usage
----------

Render the thumbnail:

.. code-block:: jinja

    {% thumbnail media, 'small' %}

    {% thumbnail media, 'small' with {'class': 'myclass'} %}

Render the media:

.. code-block:: jinja

    {% media media, 'small' %}

    {% media media, 'small' with {'class': 'myclass'} %}

Render the path:

.. code-block:: jinja

    {% path media, 'small' %}

Thumbnails for files
--------------------

The ``sonata.media.provider.file`` provider does not generate thumbnails. This provider tries to display a default thumbnail by context and format.

The default thumbnail must be put in the ``web/uploads/media/media_bundle/images/<context-name>_<format-name>/`` directory (be sure to replace ``<context-name>`` and ``<format-name>``).
The file must be called ``file.png`` and must comply with the size configured for this format.
