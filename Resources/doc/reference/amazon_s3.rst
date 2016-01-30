Amazon S3
=========

In order to use Amazon S3, you will need to add the following dependency to your composer.json:

.. code-block:: bash

    composer require aws/aws-sdk-php

Configuration
-------------

This is a sample config file to enable amazon S3 as a filesystem & provider:

.. code-block:: yaml

    sonata_media:
        cdn:
            server:
                path: "http://%s3_bucket_name%.s3-website-%s3_region%.amazonaws.com"

        providers:
            image:
                filesystem: sonata.media.filesystem.s3

        filesystem:
            s3:
                bucket:    "%s3_bucket_name%"
                accessKey: "%s3_access_key%"
                secretKey: "%s3_secret_key%"
                region:    "%s3_region%"
