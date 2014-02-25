Media Context
=============

When a site has to handle pictures, you can have different type of pictures:
news pictures, users pictures etc. But in the end pictures require the same
features: resize, cdn and database relationship with entities.

The ``MediaBundle`` tries to solve this situation by introducing ``context``:
a context has its own set of media providers and its own set of formats.
That means you can have a ``small`` user picture format and a ``small`` news
picture format with different sizes and providers. For example:

.. code-block:: yaml

    contexts:
        default:  # the default context is mandatory
            providers:
                - sonata.media.provider.dailymotion
                - sonata.media.provider.youtube
                - sonata.media.provider.image
                - sonata.media.provider.file

            formats:
                small: { width: 100 , quality: 70}
                big:   { width: 500 , quality: 70}

        news:
            providers:
                - sonata.media.provider.youtube
                - sonata.media.provider.image

            formats:
                small: { width: 150 , quality: 95}
                big:   { width: 500 , quality: 90}

``AdminBundle`` Integration
---------------------------

When you create a new blog post, you might want to link an image to that post.

Doctrine ORM:

.. code-block:: xml

        <many-to-one
            field="image"
            target-entity="Application\Sonata\MediaBundle\Entity\Media"
            >
            <cascade>
                <cascade-all/>
            </cascade>
        </many-to-one>

Doctrine PHPCR:

.. code-block:: xml

        <reference-one
            fieldName="media"
            strategy="weak"
            target-document="Application\Sonata\MediaBundle\Document\Media"
        />

In the ``PostAdmin``, you can add a new field ``image`` with a ``link_parameters``
option. This option will add an extra parameter into the ``add`` link. This
parameter will be used by the related controller.

.. code-block:: php

    <?php
    public function configureFormFields(FormMapper $form)
    {
        // ...
        $form->add('image', 'sonata_type_model_list', array(), array('link_parameters' => array('context' => 'news')));
        // ...
    }

If you look in the ``MediaAdmin`` class, the class defined a ``getPersistentParameters``
method. This method allows you to define persistent parameters across the
``MediaAdminController``. Depending on the action, the parameter can change
the Admin behaviors:

* *list*: filters the list to display only one ``context``

* *create*: creates a new media asset with the provided ``context``

.. code-block:: php

    <?php
    public function getPersistentParameters()
    {
        if (!$this->getRequest()) {
            return array();
        }

        return array(
            'provider' => $this->getRequest()->get('provider'),
            'context'  => $this->getRequest()->get('context'),
        );
    }
