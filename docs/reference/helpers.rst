Helpers
=======

The bundle comes with different helpers to render the thumbnail or the media
itself. The thumbnail always represents the media's image preview (i.e. the
thumbnail of a flash video). And the media helper generates the media itself
(i.e. the flash video).

PHP Usage
---------

Render the thumbnail::

    echo $view['sonata_media']->thumbnail($media, 'small');

    // or

    echo $view['sonata_media']->thumbnail($media, 'small', [
        'class' => 'myclass',
    ];

Render the media::

    echo $view['sonata_media']->media($media, 'small');

    // or

    echo $view['sonata_media']->media($media, 'small', [
        'class' => 'myclass',
    ];

Render the path::

    echo $view['sonata_media']->path($media, 'small');

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

Render the path to a ``sonata.media.provider.file`` context:

.. code-block:: jinja

    {% path media, 'reference' %}

Media helper for images
-----------------------

The media helper for the ``sonata.media.provider.image`` provider renders a responsive image tag with sensible defaults for ``srcset`` and ``sizes``.
The size configured will be the one used for the default fallback ``src``.

To override the ``sizes`` to fit your particular design, just pass a ``sizes`` option to the helper.

.. code-block:: jinja

    {% media media, 'large' with {'sizes': '(min-width: 20em) 50vw, 100vw'} %}

To override the ``srcset`` attribute, just pass a ``srcset`` option to the
helper. The option expects either a string or an array of formats.

.. code-block:: jinja

    {% media media, 'large' with {'srcset': ['small', 'big']} %}

To render the image as ``<picture>`` element instead of ``<img>``, pass a ``picture`` key instead of ``srcset`` above:

.. code-block:: jinja

    {% media media, 'large' with {'picture': ['small', 'big']} %}

Media queries for ``<source>`` tags will default to a ``max-width`` equal to the image size.
If you need to specify media queries explicitly, do so with an object as follows:

.. code-block:: jinja

    {% media media, 'large' with {'srcset': {'(max-width: 500px)': 'small', '(max-width: 1200px)': 'big'}} %}

The format parameter (``'large'`` above) determines which size is going to be rendered as ``<img>`` inside the ``<picture>`` element.

Thumbnails for files
--------------------

The ``sonata.media.provider.file`` provider does not generate thumbnails.
This provider tries to display a default thumbnail by context and format.

The default thumbnail must be put in the ``web/uploads/media/media_bundle/images/<context-name>_<format-name>/``
directory (be sure to replace ``<context-name>`` and ``<format-name>``).
The file must be called ``file.png`` and must comply with the size configured for this format.
