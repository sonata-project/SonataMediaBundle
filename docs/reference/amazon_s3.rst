Amazon S3
=========

In order to use Amazon S3, you will need to require the following library:

.. code-block:: bash

    composer require aws/aws-sdk-php

Configuration
-------------

This is a sample config file to enable amazon S3 as a filesystem & provider:

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
                bucket:      '%s3_bucket_name%'
                accessKey:   '%s3_access_key%'
                secretKey:   '%s3_secret_key%'
                region:      '%s3_region%'
                version:     '%s3_version%' # latest by default (cf. https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_configuration.html#cfg-version)
                endpoint:    '%s3_endpoint%' # null by default (cf. https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_configuration.html#endpoint)
                sdk_version: '%s3_sdk_version%' # 2 by default

.. note::

   This bundle is currently using KNP Gaufrette as S3 adapter and the default SDK used is version 2.
   Changes have been made in the bundle to allow you to use version 3, update `sdk_version` parameter for this.
