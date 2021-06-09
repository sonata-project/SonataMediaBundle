API
===

SonataMediaBundle embeds a Controller to provide an API through FOSRestBundle, with its documentation provided by NelmioApiDocBundle.

Setup
-----

If you wish to use it, you must first follow the installation instructions of both bundles:

* `FOSRestBundle <https://github.com/FriendsOfSymfony/FOSRestBundle>`_
* `NelmioApiDocBundle <https://github.com/nelmio/NelmioApiDocBundle>`_

Here's the configuration we used, you may adapt it to your needs:

.. code-block:: yaml

    fos_rest:
        param_fetcher_listener: true
        body_listener: true
        format_listener: true
        view:
            view_response_listener: force
        body_converter:
            enabled: true
            validate: true
        exception:
            messages:
                'Symfony\Component\HttpKernel\Exception\NotFoundHttpException': true

    sensio_framework_extra:
        view: { annotations: true }
        router: { annotations: true }
        request: { converters: true }

    jms_serializer:
        metadata:
            directories:
                - { name: 'sonata_datagrid', path: '%kernel.project_dir%/vendor/sonata-project/datagrid-bundle/src/Resources/config/serializer', namespace_prefix: 'Sonata\DatagridBundle' }
                - { name: 'sonata_media', path: '%kernel.project_dir%/vendor/sonata-project/media-bundle/src/Resources/config/serializer', namespace_prefix: 'Sonata\MediaBundle' }

    twig:
        exception_controller: null

    framework:
        error_controller: 'FOS\RestBundle\Controller\ExceptionController::showAction'

In order to activate the API's, you'll also need to add this to your routing:

.. code-block:: yaml

    sonata_api_media:
        prefix: /api/media
        resource: "@SonataMediaBundle/Resources/config/routing/api_nelmio_v3.xml"

Serialization
-------------

We're using JMSSerializationBundle's serialization groups to customize the inputs and outputs.

The taxonomy is as follows:
* ``sonata_api_read`` is the group used to display entities
* ``sonata_api_write`` is the group used for input entities (when used instead of forms)

If you wish to customize the outputted data, feel free to set up your own serialization options by configuring JMSSerializer with those groups.

Sending a media file
--------------------

Some providers (file or image for instance) require that you send a file upon the medium creation. To do so through the API, you will need to send the data as a ``multipart/form-data`` query.

This would look like this for the cURL call:

.. code-block:: bash

    curl 'http://demo.sonata-project.org/api/media/providers/sonata.media.provider.image/media.json' -H 'Authorization: Basic YWRtaW46YWRtaW4=' -H 'Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryFhX9k2FPT3sQos00' -H 'Accept: */*' --compressed

And like this for the request body::

    ------WebKitFormBoundaryFhX9k2FPT3sQos00
    Content-Disposition: form-data; name="name"

    medium name
    ------WebKitFormBoundaryFhX9k2FPT3sQos00
    Content-Disposition: form-data; name="description"

    medium description
    ------WebKitFormBoundaryFhX9k2FPT3sQos00
    Content-Disposition: form-data; name="enabled"

    1
    ------WebKitFormBoundaryFhX9k2FPT3sQos00
    Content-Disposition: form-data; name="copyright"

    copyright information
    ------WebKitFormBoundaryFhX9k2FPT3sQos00
    Content-Disposition: form-data; name="authorName"

    medium author name
    ------WebKitFormBoundaryFhX9k2FPT3sQos00
    Content-Disposition: form-data; name="cdnIsFlushable"

    1
    ------WebKitFormBoundaryFhX9k2FPT3sQos00
    Content-Disposition: form-data; name="binaryContent"; filename="my-awesome-image.jpg"
    Content-Type: image/jpeg

    ------WebKitFormBoundaryFhX9k2FPT3sQos00--

You may of course still use JSON body for creating a video media (you only have to set the ``binaryContent`` argument to the video URL).
