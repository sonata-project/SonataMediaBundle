Helpers
=======

The bundle comes with different helpers to render the thumbnail or the media itself. The thumbnail always
represents the media's image preview (ie the thumbnail of a flash video). And the media helper generates
the media itself (ie the flash video).


PHP Usage
---------

Render the thumbnail :

.. code-block:: php

    <?php echo $view['sonata_media']->thumbnail($media, 'small_format') ?>
    
    <?php echo $view['sonata_media']->thumbnail($media, 'small_format', array(
        'class' => 'myclass'
    ) ?>

Render the media :

.. code-block:: php

    <?php echo $view['sonata_media']->media($media, 'small_format') ?>

    <?php echo $view['sonata_media']->media($media, 'small_format', array(
        'class' => 'myclass'
    ) ?>


Twig usage
----------

Render the thumbnail :

.. code-block:: twig

    {% thumbnail media, 'small_format' %}

    {% thumbnail media, 'small_format', {'class': 'myclass'} %}

Render the media :

.. code-block:: twig

    {% media media, 'small_format' %}

    {% media media, 'small_format' %}
