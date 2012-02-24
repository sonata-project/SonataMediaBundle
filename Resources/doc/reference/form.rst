Form Type
=========

Media Type
----------

The ``sonata_media_type`` can be used to assign a Media instance to another entity. There are 2 required parameters :
 * the provider : sonata.media.provider.youtube, sonata.media.provider.image, etc ...
 * context : the context to use

.. code-block:: php

    <?php
    // create the target object
    $post = new Post();

    // create the form
    $builder = $this->createFormBuilder($post);
    $builder->add('media', 'sonata_media_type', array(
         'provider' => 'sonata.media.provider.youtube',
         'context'  => 'default'
    ));

    $form = $builder->getForm();

    // bind and transform the media's binary content into real content
    if ($request->getMethod() == 'POST') {
        $form->bindRequest($request);

        // do stuff ...
    }
