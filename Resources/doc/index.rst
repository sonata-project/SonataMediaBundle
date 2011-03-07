Welcome to MediaBundle documentation
===================================

The ``MediaBundle`` is a media library based on dedicated provider which handle different
``type`` of media :

  - file
  - image
  - youtube
  - dailymotion
  - more to come : vimeo, pdf ...

Each ``type`` is managed by a service which is in charge of :

  - retrieving media metadata
  - generating media thumbnail
  - tweaking the edit form
  - rendering the media

Moreover a ``Media`` can be linked to a ``Gallery``.


La cerise sur le gateau ! ("Cherry on the top")
-----------------------------------------------

The ``MediaBundle`` can also manage CDN state information for the different medias.


Reference Guide
---------------

.. toctree::
   :maxdepth: 1
   :numbered:

   reference/installation
   reference/helpers
