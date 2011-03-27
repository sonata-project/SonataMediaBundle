Media Context
=============

When a site has to handle pictures, you can have different type of images : news pictures, users pictures or
any others kind of picture. But at the end  pictures require the same feature : resize, cdn and database
relationship with entities.

The ``MediaBundle`` try to solve this situation by introducing ``context`` : a context has its own set of media providers
and its own set of formats. That means you can have a ``small`` user picture format and a ``small`` news pictures
formats with different size and providers.

Example :

.. code-block:: yml

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


``AdminBundle`` integration
---------------------------

When you create a new blog post, you might want to link an image to that post.

.. code-block:: xml

        <many-to-one
            field="image"
            target-entity="Application\Sonata\MediaBundle\Entity\Media"
            >
            <cascade>
                <cascade-all/>
            </cascade>
        </many-to-one>

In the ``PostAdmin``, you can add a new field ``image`` with a ``link_parameters`` option. This option will add an extra
parameter into the ``add`` link, this parameter will be used by the related controller.

.. code-block:: php

    public function configureFormFields(FormMapper $form)
    {
        // [... ]
        $form->add('image', array(), array('edit' => 'list', 'link_parameters' => array('context' => 'news')));
        // [... ]
    }

If you have a look to the ``MediaAdmin`` class, the class defined a ``getPersistentParameters`` method. This method
allows to define persistent parameters across the ``MediaAdminController``. Depends on the action the parameter can
change the Admin behaviors :

 - list : filter the list to display only one ``context``
 - create : create a new media with the provided ``context``

.. code-block:: php

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