Media Bundle
============

The ``SonataMediaBundle`` is a media library based on a dedicated ``provider``
which handles different ``type`` of media: files, videos or images.

Each ``type`` is managed by a ``provider`` service which is in charge of:

  - retrieving media metadata
  - generating media thumbnail
  - tweaking the edit form
  - rendering the media

Each ``media`` can be linked to a ``context``. A context can be ``news``,
``user`` or any name you want. A context allows you to group a set of pictures
together. As requirements can be different for each context, a context
is defined by a set of ``formats`` and a set of ``providers``.

As the infrastructure is not standard, the ``MediaBundle`` abstracts the
``filesystem`` layer and the ``cdn`` layer.

.. toctree::
   :caption: Reference Guide
   :name: reference-guide
   :maxdepth: 1
   :numbered:

   reference/installation
   reference/helpers
   reference/creating_a_provider_class
   reference/media_context
   reference/usage
   reference/form
   reference/security
   reference/command_line
   reference/advanced_configuration
   reference/amazon_s3
   reference/messenger
   reference/extra
   reference/troubleshooting

Available services
------------------

 - Providers

    - ``sonata.media.provider.image``: Image
    - ``sonata.media.provider.file``: File
    - ``sonata.media.provider.dailymotion``: [Dailymotion](https://www.dailymotion.com/)
    - ``sonata.media.provider.vimeo``: [Vimeo](https://vimeo.com/)
    - ``sonata.media.provider.youtube``: [YouTube](https://www.youtube.com/)

 -  Filesystem

    - ``sonata.media.filesystem.local``: The local filesystem (default)
    - ``sonata.media.filesystem.ftp``: FTP
    - ``sonata.media.filesystem.s3``: Amazon S3
    - ``sonata.media.filesystem.replicate``: Replicate file to a primary and a secondary

 - CDN

    - ``sonata.media.cdn.server``: The local HTTP server (default)
    - ``sonata.media.cdn.panther``: Panther Portal
    - ``sonata.media.cdn.cloudfront``: [AWS CloudFront](https://aws.amazon.com/cloudfront/)
    - ``sonata.media.cdn.fallback``: Fallback, use the fallback (the HTTP server) if the media is not yet flushed on the CDN

More services will be available in the future depending on your contributions! :)

