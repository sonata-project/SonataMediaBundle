Form Type
=========

Media Type
----------

.. figure:: ../images/sonata_media_type.png
   :align: center
   :alt: The sonata_media_type widget

   The sonata_media_type widget

The ``sonata_media_type`` can be used to assign a Media instance to another entity.
There are required parameters:

``provider``
  sonata.media.provider.youtube, sonata.media.provider.image, etc ...

``context``
  the context to use

And optionals parameters:

``empty_on_new``
  (default is true): the related data transformer will return null instead of an
  empty Media instance if no binary content is provided.
``new_on_update``
  (default is true): create a new media instance if a binary content is set.
  If the value is set to ``false``, then the media will be overwritten and
  related entities can be affected by this change.

The following example shows how to use them::

    $post = new Post();

    // create the form
    $builder = $this->createFormBuilder($post);
    $builder->add('media', 'sonata_media_type', [
         'provider' => 'sonata.media.provider.youtube',
         'context'  => 'default',
    ]);

    $form = $builder->getForm();

    // bind and transform the media's binary content into real content
    if ($request->getMethod() == 'POST')
    {
        $form->bindRequest($request);

        // do stuff ...
    }

You also need to add a new form theme template to twig configuration:

.. code-block:: yaml

    # config/packages/twig.yaml

    twig:
        debug:            '%kernel.debug%'
        strict_variables: '%kernel.debug%'

        form_themes:
            - '@SonataMedia/Form/media_widgets.html.twig'
