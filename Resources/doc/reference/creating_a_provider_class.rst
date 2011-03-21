Creating a media provider : a vimeo provider
============================================

Introduction
------------

A provider class try to resolve a simple use case : specific media management.

A youtube video and an image file are two different kind of media which cannot
be managed by only one class. So in the ``MediaBundle`` there are represented
by a provider class : ``YoutubeProvider`` and ``ImageProvider``.

A provider class is responsable to build common elements linked to a media
 - thumbnails
 - path
 - editing the media with form
 - storing the media information (metadata)

A provider class is always linked to a ``Filesystem`` and a ``CDN``. The
filesystem abstraction uses the ``Gaufrette`` library, for now there is
only 2 abstracted filesystem available : ``Local`` and ``FTP``. The ``CDN``
is used to generated the public url of a media.

By default the filesystem and the CDN uses the local filesystem and the current
server for the CDN.

So when you create a provider you don't need to worry about how media are going
to be store.

Media Entity
------------

The ``Media`` entity comes with common media fields : size, length, width and height.
However the provider might required to add more information, as it not possible to
store all information into columns, the provider class can use the ``provider_metadata``
field to store metadata as a serialize array.

The ``Media`` entity has 3 other provider fields:
 - ``provider_name`` : the service name linked to the media
 - ``provider_status`` : the status of the media
 - ``provider_reference`` : the internal provider reference (the video sig for instance)

Case Study
----------

Before starting to code we need to collect information about vimeo

 - video identifier format : 21216091
 - video player documentation : http://vimeo.com/api/docs/moogaloop
 - metadata : http://vimeo.com/api/oembed.json?url=http://vimeo.com/21216091
 
   {
     "type":"video",
     "version":"1.0",
     "provider_name":"Vimeo",
     "provider_url":"http:\/\/vimeo.com\/",
     "title":"Blinky\u2122",
     "author_name":"Ruairi Robinson",
     "author_url":"http:\/\/vimeo.com\/ruairirobinson",
     "is_plus":"1",
     "html":"<iframe src=\"http:\/\/player.vimeo.com\/video\/21216091\" width=\"1920\" height=\"1080\" frameborder=\"0\"><\/iframe>",
     "width":"1920",
     "height":"1080",
     "duration":"771",
     "description":"Soon every home will have a robot helper. \n\nDon't worry. \n\nIt's perfectly safe.\n\n\n\nWritten & Directed by Ruairi Robinson\n\nStarring Max Records from \"Where The Wild Things Are\".\n\nCinematography by Macgregor",
     "thumbnail_url":"http:\/\/b.vimeocdn.com\/ts\/136\/375\/136375440_1280.jpg",
     "thumbnail_width":1280,
     "thumbnail_height":720,
     "video_id":"21216091"
   }

 The metadata contains all information we want.


Initialize the class
--------------------

Let's initialize the ``VimeoProvider`` class.


.. code-block:: php

    namespace Sonata\MediaBundle\Provider;

    use Sonata\MediaBundle\Provider\BaseProvider;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\MediaBundle\Entity\BaseMedia as Media;
    use Symfony\Component\Form\Form;

    class VimeoProvider extends BaseProvider
    {


    }

Now we need to create the create and edit form. There is 2 forms because the
workflow is not the same :

 - create : display only one input text representing the video identifier
 - edit : display all information about the media, the edit form should not be
          used to edit metadata information as the metadata are only used
          internally by the provider class

The ``MediaAdmin`` class used by the ``AdminBundle`` does not know how to create
the form as the form is unique per provider, so the ``MediaAdmin`` delegates this
definition to the related provider.


.. code-block:: php

    function buildCreateForm(FormMapper $formMapper)
    {
        $formMapper->add('binaryContent', array(), array('type' => 'string'));
    }

    function buildEditForm(FormMapper $formMapper)
    {
        $formMapper->add('name');
        $formMapper->add('enabled');
        $formMapper->add('authorName');
        $formMapper->add('cdnIsFlushable');
        $formMapper->add('description');
        $formMapper->add('copyright');
        $formMapper->add('binaryContent', array(), array('type' => 'string'));
    }

Once the form will be submitted we will need to retrieve the video metadata. The metadata
are going to be used to store Media information :


.. code-block:: php

    public function getMetadata(Media $media)
    {
        if (!$media->getBinaryContent()) {

            return;
        }

        $url = sprintf('http://vimeo.com/api/oembed.json?url=http://vimeo.com/%s', $media->getBinaryContent());
        $metadata = @file_get_contents($url);

        if (!$metadata) {
            throw new \RuntimeException('Unable to retrieve vimeo video information for :' . $url);
        }

        $metadata = json_decode($metadata, true);

        if (!$metadata) {
            throw new \RuntimeException('Unable to decode vimeo video information for :' . $url);
        }

        return $metadata;
    }


Now, we need to code the logic for the create  mode, the ``$media`` contains data from the ``POST``.
A bit of ``AdminBundle`` the always calls some method while saving an object :
 - prePersist / postPersist
 - preUpdate / postUpdate

The ``MediaAdmin`` delegates this management to the provider.

.. code-block:: php

    public function prePersist(Media $media)
    {

        if (!$media->getBinaryContent()) {

            return;
        }

        // retrieve metadata
        $metadata = $this->getMetadata($media);

        // store provider information
        $media->setProviderName($this->name);
        $media->setProviderReference($media->getBinaryContent());
        $media->setProviderMetadata($metadata);

        // update Media common field from metadata
        $media->setName($metadata['title']);
        $media->setDescription($metadata['description']);
        $media->setAuthorName($metadata['author_name']);
        $media->setHeight($metadata['height']);
        $media->setWidth($metadata['width']);
        $media->setLength($metadata['duration']);
        $media->setContentType('video/x-flv');
        $media->setProviderStatus(Media::STATUS_OK);

        $media->setCreatedAt(new \Datetime());
        $media->setUpdatedAt(new \Datetime());
    }


The update method only update data that cannot be managed by the user.

.. code-block:: php

    public function preUpdate(Media $media)
    {
        if (!$media->getBinaryContent()) {

            return;
        }

        $metadata = $this->getMetadata($media);

        $media->setProviderReference($media->getBinaryContent());
        $media->setProviderMetadata($metadata);
        $media->setHeight($metadata['height']);
        $media->setWidth($metadata['width']);
        $media->setProviderStatus(Media::STATUS_OK);

        $media->setUpdatedAt(new \Datetime());
    }


At this point, the ``Media`` object is populated with data from the json vimeo's definition
and ready to be saved. However once saved, the provider need to generated the correct thumbnails.

The ``postPersist`` and ``postUpdate`` must be implemented to generate valid thumbnails.


.. code-block:: php

    public function postUpdate(Media $media)
    {
        $this->postPersist($media);
    }

    public function postPersist(Media $media)
    {
        if (!$media->getBinaryContent()) {

            return;
        }

        $this->generateThumbnails($media);
    }


The ``generateThumbnails`` is defined in the ``BaseProvider`` class. This method required an
``getReferenceImage`` that returns the reference image.

.. code-block:: php

    public function getReferenceImage(Media $media)
    {
        return $media->getMetadataValue('thumbnail_url');
    }

At this point, the provider class is almost finish, we can add and remove a vimeo video thanks to the ``AdminBundle``
integration and to the ``VimeoProvider``.


Register the class to the DIC
-----------------------------

If you use the tag ``sonata.media.provider``, the provider service will be added to the provider pool.

.. code-block:: xml

        <service id="sonata.media.provider.dailymotion" class="MyClass">
            <tag name="sonata.media.provider" />
            <argument>mycode</argument>
            <argument type="service" id="the_default_orm_service" />

            <call method="setTemplates">
                <argument type="collection">
                    <argument key='helper_thumbnail'>SonataMediaBundle:Provider:thumbnail.html.twig</argument>
                    <argument key='helper_view'>SonataMediaBundle:Provider:view_vimeo.html.twig</argument>
                </argument>
            </call>
        </service>


The last important part is how the vimeo media should be displayed.

View Helper
-----------

The ``MediaBundle`` comes with 2 helpers method :
  - thumbnails : this method displays the thumbnail depends on the requested format. The thumbnail path generation
                 uses the CDN service injected into the provider. By default, the ``sonata.media.cdn.server``
                 service is used. The server is just the local http server.
  - media : this methods displays the media, in the current case the media is the vimeo player. Depends on the
            provider the method ``getHelperProperties`` is called to normalize the available options.


The thumbnail template is common to all media and it is quite simple :

.. code-block:: twig

    <img {% for name, value in options %}{{name}}="{{value}}" {% endfor %} />

The media template and media helper is a bit more tricky, each provider might provide a rich set of options to
embeded the media. The ``VideoProvider::getHelperProperties()`` generates a correct set of options to be
passed to the ``view_vimeo.html.twig`` template file.


.. code-block:: php

    public function getHelperProperties(Media $media, $format, $options = array())
    {

        // documentation : http://vimeo.com/api/docs/moogaloop
        $defaults = array(
            // (optional) Flash Player version of app. Defaults to 9 .NEW!
            // 10 - New Moogaloop. 9 - Old Moogaloop without newest features.
            'fp_version'      => 10,

            // (optional) Enable fullscreen capability. Defaults to true.
            'fullscreen' => true,

            // (optional) Show the byline on the video. Defaults to true.
            'title' => true,

            // (optional) Show the title on the video. Defaults to true.
            'byline' => 0,

            // (optional) Show the user's portrait on the video. Defaults to true.
            'portrait' => true,

            // (optional) Specify the color of the video controls.
            'color' => null,

            // (optional) Set to 1 to disable HD.
            'hd_off' => 0,

            // Set to 1 to enable the Javascript API.
            'js_api' => null,

            // (optional) JS function called when the player loads. Defaults to vimeo_player_loaded.
            'js_onLoad' => 0,

            // Unique id that is passed into all player events as the ending parameter.
            'js_swf_id' => uniqid('vimeo_player_'),
        );


        $player_parameters =  array_merge($defaults, isset($options['player_parameters']) ? $options['player_parameters'] : array());

        $params = array(
            'src'         => http_build_query($player_parameters),
            'id'          => $player_parameters['js_swf_id'],
            'frameborder' => isset($options['frameborder']) ? $options['frameborder'] : 0,
            'width'       => isset($options['width'])             ? $options['width']  : $media->getWidth(),
            'height'      => isset($options['height'])            ? $options['height'] : $media->getHeight(),
        );

        return $params;
    }

.. code-block:: twig

    <iframe
        id="{{ options.id }}"
        src="http://player.vimeo.com/video/{{ media.providerreference }}?{{ options.src }}"
        width="{{ options.width }}"
        height="{{ options.height }}"
        frameborder="{{ options.frameborder }}">
    </iframe>
