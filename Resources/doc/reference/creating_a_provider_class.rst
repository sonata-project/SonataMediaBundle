Creating a Media Provider: A Vimeo Provider
===========================================

A provider class solves a simple use case: the management of a very specific
type of media.

A youtube video and an image file are two very different types of media and
each cannot be managed by a single class. In the ``MediaBundle``, each is
represented by two provider classes: ``YoutubeProvider`` and ``ImageProvider``.

A provider class is responsible for handling common things related to a media
asset:

* thumbnails
* path
* editing the media with a form
* storing the media information (metadata)

A provider class is always linked to a ``Filesystem`` and a ``CDN``. The
filesystem abstraction uses the ``Gaufrette`` library. For now there is
only 2 abstracted filesystem available : ``Local`` and ``FTP``. The ``CDN``
is used to generated the media asset public url.

By default the filesystem and the CDN use the local filesystem and the current
server for the CDN.

In other words, when you create a provider, you don't need to worry about
how media assets are going to be store on the filesystem.

Media Entity
------------

The ``Media`` entity comes with common media fields: ``size``, ``length``,
``width`` and ``height``. However the provider might require you to add more
information. As it not possible to store all of the possible information
into database columns, the provider class can use the ``provider_metadata``
field to store metadata as a serialize array.

The ``Media`` entity has 3 other provider fields:

* ``provider_name``: the service name linked to the media
* ``provider_status``: the media status
* ``provider_reference``: the internal provider reference (the video sig for instance)

Case Study
----------

Before starting, we need to collect information about some asset on vimeo.
Take this video, for example:

* video identifier format : 21216091
* video player documentation : http://vimeo.com/api/docs/moogaloop
* metadata : http://vimeo.com/api/oembed.json?url=http://vimeo.com/21216091

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

    <?php
    namespace Sonata\MediaBundle\Provider;

    use Sonata\MediaBundle\Provider\BaseProvider;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\MediaBundle\Entity\BaseMedia as Media;
    use Symfony\Component\Form\Form;

    class VimeoProvider extends BaseProvider
    {
    }

Next, we need to define the create and edit forms. There are two forms because
the workflow is not the same:

* *create*: displays only one input text representing the video identifier;

* *edit*: displays all information about the media: the edit form should not
  be used to edit the metadata information as the metadata is only used
  internally by the provider class.

The ``MediaAdmin`` class, used by the ``AdminBundle``, does not know how
to create the form as the form is unique per provider. So the ``MediaAdmin``
delegates this definition to the related provider.

.. code-block:: php

    <?php
    public function buildCreateForm(FormMapper $formMapper)
    {
        $formMapper->add('binaryContent', array(), array('type' => 'string'));
    }

    public function buildEditForm(FormMapper $formMapper)
    {
        $formMapper->add('name');
        $formMapper->add('enabled');
        $formMapper->add('authorName');
        $formMapper->add('cdnIsFlushable');
        $formMapper->add('description');
        $formMapper->add('copyright');
        $formMapper->add('binaryContent', array(), array('type' => 'string'));
    }

Once the form is submitted, we retrieve the video metadata. The metadata
is going to be used to store ``Media`` information :

.. code-block:: php

    <?php
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

Now, we need to code the logic for the create mode. The ``$media`` contains
data from the ``POST``. The ``AdminBundle`` always calls specific methods
while saving an object :

* ``prePersist`` / ``postPersist``
* ``preUpdate`` / ``postUpdate``

The ``MediaAdmin`` delegates this management to the media provider.

.. code-block:: php

    <?php
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

The update method should only update data that cannot be managed by the user.

.. code-block:: php

    <?php
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

At this point, the ``Media`` object is populated with data from the vimeo's
JSON definition and is ready to be saved. However once saved, the provider
needs to generate the correct thumbnails.

The ``postPersist`` and ``postUpdate`` must be implemented to generate valid
thumbnails.

.. code-block:: php

    <?php
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


The ``generateThumbnails`` method is defined in the ``BaseProvider`` class.
This method required a ``getReferenceImage`` method that returns the reference
image.

.. code-block:: php

    <?php
    public function getReferenceImage(Media $media)
    {
        return $media->getMetadataValue('thumbnail_url');
    }

At this point, the provider class is almost finish: we can add and remove
a vimeo video : thanks to the ``AdminBundle`` integration and the ``VimeoProvider``
service.

Register the Class with the Service Container
---------------------------------------------

If you use the tag ``sonata.media.provider``, the provider service will be
added to the provider pool.

.. code-block:: xml

    <service id="sonata.media.provider.vimeo" class="Sonata\MediaBundle\Provider\VimeoProvider">
        <tag name="sonata.media.provider" />
        <argument>sonata.media.provider.vimeo</argument>
        <argument type="service" id="sonata.media.filesystem.local" />
        <argument type="service" id="sonata.media.cdn.server" />
        <argument type="service" id="sonata.media.generator.default" />
        <argument type="service" id="sonata.media.thumbnail.format" />
        <argument type="service" id="sonata.media.buzz.browser" />
        <argument type="service" id="sonata.media.metadata.proxy" />
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

The ``MediaBundle`` comes with 2 helper methods:

* *thumbnail*: This method displays the thumbnail depending on the requested
  format. The thumbnail path generation uses the CDN service injected into
  the provider. By default, the ``sonata.media.cdn.server`` service is used.
  The server is just the local http server.

* *media*: This methods displays the media. In the current case, the media
  is the vimeo player. Depending on the provider, the method ``getHelperProperties``
  is called to normalize the available options.

The thumbnail template is common to all media and it is quite simple:

.. code-block:: html+jinja

    <img {% for name, value in options %}{{name}}="{{value}}" {% endfor %} />

The media template and media helper are a bit more tricky. Each provider might
provide a rich set of options to embed the media. The
``VideoProvider::getHelperProperties()`` method generates the correct set
of options that need to be passed to the ``view_vimeo.html.twig`` template file.

.. code-block:: php

    <?php
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

From the vimeo's documentation, a video can be included like this:

.. code-block:: html+jinja

    <iframe
        id="{{ options.id }}"
        src="http://player.vimeo.com/video/{{ media.providerreference }}?{{ options.src }}"
        width="{{ options.width }}"
        height="{{ options.height }}"
        frameborder="{{ options.frameborder }}">
    </iframe>

Et voilà! Of course you should test the provider class. There are many examples
in the ``Tests`` folder. The source code is available in the class
``Sonata\MediaBundle\Provider\VimeoProvider``.
