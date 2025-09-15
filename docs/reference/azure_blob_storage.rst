Azure Blob Storage
==================

In order to use Azure Blob Storage, you will need to require the following package:

.. code-block:: bash

    composer require microsoft/azure-storage-blob

Configuration
-------------

This is a sample configuration to enable Azure Blob Storage as a filesystem and provider:

.. code-block:: yaml

    # config/packages/sonata_media.yaml

    sonata_media:
        cdn:
            server:
                path: 'http://%azure_storage_account%.blob.core.windows.net'

        providers:
            image:
                filesystem: sonata.media.filesystem.s3

        filesystem:
            azure:
                container_name: '%azure_container_name%'
                connection_string: '%azure_connection_string%'
                create_container: '%azure_create_container%' # defaults to false
