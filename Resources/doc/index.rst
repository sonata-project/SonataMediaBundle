Welcome to MediaBundle documentation
====================================

The ``MediaBundle`` is a media library based on dedicated ``provider`` which handle different
``type`` of media: file, video or image.

Each ``type`` is managed by a ``provider`` service which is in charge of :

  - retrieving media metadata
  - generating media thumbnail
  - tweaking the edit form
  - rendering the media

Each ``media`` can be linked to a ``context``. A context can be ``news``, ``user`` or any
name you need. A context allows to regroup a set of picture into one group. As requirement
can be different for each context, a context is defined by a set of ``formats`` and a set of
``providers``.

As the infrastructure is not standard, the ``MediaBundle`` abstracts the ``filesystem`` layer
and the ``cdn`` layer. 


Available services
------------------

 Providers

    - sonata.media.provider.dailymotion   : Dailymotion
    - sonata.media.provider.file          : File
    - sonata.media.provider.image         : Image
    - sonata.media.provider.vimeo         : Vimeo
    - sonata.media.provider.youtube       : Youtube

 Filesystem

    - sonata.media.filesystem.local       : The local filesystem (default)
    - sonata.media.filesystem.ftp         : FTP

 CDN

    - sonata.media.cdn.server             : The local http server (default)
    - sonata.media.cdn.panther            : Panther Portal


Reference Guide
---------------

.. toctree::
   :maxdepth: 1
   :numbered:

   reference/installation
   reference/helpers
   reference/creating_a_provider_class
   reference/media_context
   reference/advanced_configuration
