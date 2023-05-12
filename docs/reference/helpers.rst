Helpers
=======

The bundle comes with different helpers to render the thumbnail or the media
itself. The thumbnail always represents the media's image preview (i.e. the
thumbnail of a flash video). And the media helper generates the media itself
(i.e. the flash video).

Twig usage
----------

Render the thumbnail:

.. code-block:: html+twig

    {{ sonata_thumbnail(media, 'small') }}

    {{ sonata_thumbnail(media, 'small', {'class': 'myclass'}) }}

Render the media:

.. code-block:: html+twig

    {{ sonata_media(media, 'small') }}

    {{ sonata_media(media, 'small', {'class': 'myclass'}) }}

Render the path:

.. code-block:: html+twig

    {{ sonata_path(media, 'small') }}

Render the path to a ``sonata.media.provider.file`` context:

.. code-block:: html+twig

    {{ sonata_path(media, 'reference') }}

Media helper for images
-----------------------

The media helper for the ``sonata.media.provider.image`` provider renders a responsive image tag with sensible defaults for ``srcset`` and ``sizes``.
The size configured will be the one used for the default fallback ``src``.

To override the ``sizes`` to fit your particular design, just pass a ``sizes`` option to the helper.

.. code-block:: html+twig

    {{ sonata_media(media, 'large', {'sizes': '(min-width: 20em) 50vw, 100vw'}) }}

To override the ``srcset`` attribute, just pass a ``srcset`` option to the
helper. The option expects either a string or an array of formats.

.. code-block:: html+twig

    {{ sonata_media(media, 'large', {'srcset': ['small', 'big']}) }}

To render the image as ``<picture>`` element instead of ``<img>``, pass a ``picture`` key instead of ``srcset`` above:

.. code-block:: html+twig

    {{ sonata_media(media, 'large', {'picture': ['small', 'big']}) }}

Media queries for ``<source>`` tags will default to a ``max-width`` equal to the image size.
If you need to specify media queries explicitly, do so with an object as follows:

.. code-block:: html+twig

    {{ sonata_media(media, 'large', {'srcset': {'(max-width: 500px)': 'small', '(max-width: 1200px)': 'big'}}) }}

The format parameter (``'large'`` above) determines which size is going to be rendered as ``<img>`` inside the ``<picture>`` element.

Thumbnails for files
--------------------

The ``sonata.media.provider.file`` provider does not generate thumbnails.
This provider tries to display a default thumbnail.

The default thumbnail must be put in ``bundles/sonatamedia/file.png``.
It is automatically available there when you install assets using

.. code-block:: bash

    bin/console assets:install
