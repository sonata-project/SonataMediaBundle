Usages
======

Saving a media file
-------------------

Saving a media file required at least 3 informations :

- the ``context`` which is used as a main category : user picture, news or any
- the ``provider name`` : the provider code which handle the media processing while saving
- the ``binary content`` : the provider information source : the unique id for a video, a File instance, etc ...

For instance, a youtube video can be created and saved like this :

.. code-block:: php

    <?php

    $media = new Media;
    $media->setBinaryContent('13142153');
    $media->setContext('user'); // video related to the user
    $media->setProviderName('sonata.media.provider.youtube');

    $mediaManager->save($media);

    // or
    $media = new Media;
    $media->setBinaryContent('13142153');

    $mediaManager->save($media, 'user', 'sonata.media.provider.youtube');


Retrieving metadata information
-------------------------------

The providers has a dedicated field where extra information can be saved : the providerMetadata field.

You can retrieve a value very easily with the ``getMetadataValue`` method.

For instance you can retrieve the original youtube video title with :

.. code-block:: php

    <?php

    $media = $mediaManager->findOneBy(array('id' => 132));

    echo $media->getMetadataValue('title', 'if none use this string');



