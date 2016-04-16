Command Line Tools
==================

Media commands
--------------

Synchronize thumbnails
^^^^^^^^^^^^^^^^^^^^^^

Synchronize thumbnails for the provider ``sonata.media.provider.image`` in the ``default`` context.

.. note::

   There is also an interactive shell for parameters.

.. code-block:: bash

   php app/console sonata:media:sync-thumbnails sonata.media.provider.image default

Remove thumbnails
^^^^^^^^^^^^^^^^^

Remove thumbnails for the provider ``sonata.media.provider.image`` in the ``default`` context and ``small`` format.

.. note::

   There is also an interactive shell for parameters.

.. code-block:: bash

   php app/console sonata:media:remove-thumbnails sonata.media.provider.image default small

Update metadata
^^^^^^^^^^^^^^^

Update metadata for a set of media for the provider ``sonata.media.provider.youtube`` with the ``default`` context.

.. note::

   There is also an interactive shell for parameters.

.. code-block:: bash

   php app/console sonata:media:refresh-metadata sonata.media.provider.youtube default

Add a media
^^^^^^^^^^^

Add a media to the provider ``sonata.media.provider.image`` with the ``default`` context from path.

.. code-block:: bash

   php app/console sonata:media:add sonata.media.provider.image default path/to/image.jpg

Add youtube media from url.

.. code-block:: bash

   php app/console sonata:media:add sonata.media.provider.youtube default http://www.youtube.com/watch?v=BDYAbAtaDzA&feature=g-all-esi&context=asdasdas

Add dailymotion video from provider reference.

.. code-block:: bash

   php app/console sonata:media:add sonata.media.provider.dailymotion default BDYAbAtaDzA

Add image from given path with additional attributes.

.. code-block:: bash

   php app/console sonata:media:add sonata.media.provider.image default path/to/media.png --description="foo bar" --copyright="Sonata Project" --author="Thomas" --enabled=false

Mass import
^^^^^^^^^^^

Add multiple media files from csv file

.. code-block:: bash

   php app/console sonata:media:add-multiple --file=medias.csv

Add multiple media files from stdin

.. code-block:: bash

   cat medias.csv | php app/console sonata:media:add-multiple

The medias.csv file contains the following lines::

   providerName,context,binaryContent
   sonata.media.provider.dailymotion,default,http://www.dailymotion.com/video/xuvt7q_cauet-et-psy-au-trocadero-video-officielle-c-cauet-sur-nrj_music
   sonata.media.provider.dailymotion,default,http://www.dailymotion.com/video/xsbwie_psy-gangnam-style_music
   sonata.media.provider.dailymotion,default,http://www.dailymotion.com/video/xqziut_tutoriel-video-symfony-2-twig_lifestyle
   sonata.media.provider.dailymotion,default,http://www.dailymotion.com/video/x9bgxs_php-tv-4-magento-mysql-symfony-zend_tech
   sonata.media.provider.dailymotion,default,http://www.dailymotion.com/video/xhq4c5_slyblog-tutoriel-video-symfony-1-4-partie-2-2_tech

Fix missing root categories
^^^^^^^^^^^^^^^^^^^^^^^^^^^

Creates default root categories for the ``SonataClassificationBundle`` if they don't exist. This command should be executed when creating a new context under the ``contexts`` config tree.

.. code-block:: bash

   php app/console sonata:media:fix-media-context
