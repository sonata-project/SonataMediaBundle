Amazon S3
=========

In order to use Amazon S3, you will need to require the following package:

.. code-block:: bash

    composer require aws/aws-sdk-php

Configuration
-------------

This is a sample configuration to enable amazon S3 as a filesystem and provider:

.. code-block:: yaml

    # config/packages/sonata_media.yaml

    sonata_media:
        cdn:
            server:
                path: 'http://%s3_bucket_name%.s3-website-%s3_region%.amazonaws.com'

        providers:
            image:
                filesystem: sonata.media.filesystem.s3

        filesystem:
            s3:
                bucket: '%s3_bucket_name%'
                accessKey: '%s3_access_key%'
                secretKey: '%s3_secret_key%'
                region: '%s3_region%'
                version: '%s3_version%' # defaults to "latest" (cf. https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_configuration.html#cfg-version)
                endpoint: '%s3_endpoint%' # defaults to null (cf. https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_configuration.html#endpoint)

Async adapter
-------------
In order to use async S3 adapter, you will need to require the following package:

.. code-block:: bash

    composer require async-aws/simple-s3

.. code-block:: yaml

    # config/packages/sonata_media.yaml

    sonata_media:
        filesystem:
            s3:
                async: true

.. note::

    This bundle is currently using KNP Gaufrette as S3 adapter.

.. note::

    Using "latest" for "sonata_media.filesystem.s3.version" in a production environment is not recommended
    because pulling in a new minor version of the SDK that includes an API update could break your production application.
