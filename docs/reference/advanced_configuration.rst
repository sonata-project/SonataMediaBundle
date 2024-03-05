Advanced Configuration
======================

Full configuration options:

.. code-block:: yaml

    sonata_media:
        db_driver: doctrine_orm
        class:
            media: App\Entity\SonataMediaMedia
            gallery: App\Entity\SonataMediaGallery
            gallery_item: App\Entity\SonataMediaGalleryItem
            category: null # App\Entity\SonataClassificationCategory if exists

        force_disable_category: false # true, if you really want to disable the relation with category

        default_context: default
        admin_format: { width: 200, quality: 90, format: 'jpg' }
        contexts:
            default:  # the default context is mandatory
                download:
                    strategy: sonata.media.security.superadmin_strategy
                    mode: http
                providers:
                    - sonata.media.provider.dailymotion
                    - sonata.media.provider.youtube
                    - sonata.media.provider.image
                    - sonata.media.provider.file

                formats:
                    small: { width: 100, quality: 70 }
                    big: { width: 500, quality: 70, resizer: sonata.media.resizer.square }
                    # You can pass through any custom option to resizer by using the resizer_options key
                    icon: { width: 32, quality: 70, resizer: your.custom.resizer, resizer_options: { custom_crop: true } }

            tv:
                download:
                    strategy: sonata.media.security.superadmin_strategy
                    mode: http
                providers:
                    - sonata.media.provider.dailymotion
                    - sonata.media.provider.youtube
                    - sonata.media.provider.video

                formats:
                    cinema: { width: 1850, height: 768 }
                    grandmatv: { width: 640, height: 480 }

            news:
                download:
                    strategy: sonata.media.security.superadmin_strategy
                    mode: http
                providers:
                    - sonata.media.provider.dailymotion
                    - sonata.media.provider.youtube
                    - sonata.media.provider.image
                    - sonata.media.provider.file

                formats:
                    small: { width: 150, quality: 95 }
                    big: { width: 500, quality: 90 }

        cdn:
            server:
                path: /uploads/media

            panther:
                path: http://domain.pantherportal.com/uploads/media
                site_id:
                password:
                username:

            cloudfront:
                path: https://xxxxxxxxxxxxxx.cloudfront.net/uploads/media
                distribution_id:
                key:
                secret:
                region:
                version:

            fallback:
                primary: sonata.media.cdn.panther
                fallback: sonata.media.cdn.server

        filesystem:
            local:
                directory: '%kernel.project_dir%/web/uploads/media'
                create: false

            ftp:
                directory:
                host:
                username:
                password:
                port: 21
                passive: false
                create: false
                mode: !php/const FTP_BINARY # @see: https://www.php.net/manual/en/ftp.constants.php

            s3:
                bucket:
                accessKey:
                secretKey:
                create: false
                region: s3.amazonaws.com # change if not using US Standard region
                version: 2006-03-01 # change according the API version you are using
                storage: standard # can be one of: standard or reduced
                acl: public # can be one of: public, private, open, auth_read, owner_read, owner_full_control
                encryption: aes256 # can be aes256 or not set
                cache_control: max-age=86400 # or any other
                meta:
                    key1: value1 #any amount of metas(sent as x-amz-meta-key1 = value1)
                async: false

            replicate:
                primary: sonata.media.adapter.filesystem.s3
                secondary: sonata.media.adapter.filesystem.local

        providers:
            file:
                service: sonata.media.provider.file
                resizer: null
                filesystem: sonata.media.filesystem.local
                cdn: sonata.media.cdn.server
                generator: sonata.media.generator.default
                thumbnail: sonata.media.thumbnail.format
                allowed_extensions: ['pdf', 'txt', 'rtf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pttx', 'odt', 'odg', 'odp', 'ods', 'odc', 'odf', 'odb', 'csv', 'xml']
                allowed_mime_types: ['application/pdf', 'application/x-pdf', 'application/rtf', 'text/html', 'text/rtf', 'text/plain']

            image:
                service: sonata.media.provider.image
                resizer: sonata.media.resizer.simple # sonata.media.resizer.square, sonata.media.resizer.crop
                filesystem: sonata.media.filesystem.local
                cdn: sonata.media.cdn.server
                generator: sonata.media.generator.default
                thumbnail: sonata.media.thumbnail.format
                allowed_extensions: ['jpg', 'png', 'jpeg']
                allowed_mime_types: ['image/pjpeg', 'image/jpeg', 'image/png', 'image/x-png']

            youtube:
                service: sonata.media.provider.youtube
                resizer: sonata.media.resizer.simple
                filesystem: sonata.media.filesystem.local
                cdn: sonata.media.cdn.server
                generator: sonata.media.generator.default
                thumbnail: sonata.media.thumbnail.format
                html5: false

            dailymotion:
                service: sonata.media.provider.dailymotion
                resizer: sonata.media.resizer.simple
                filesystem: sonata.media.filesystem.local
                cdn: sonata.media.cdn.server
                generator: sonata.media.generator.default
                thumbnail: sonata.media.thumbnail.format

        http:
            client: sonata.media.http.base_client # You need symfony/http-client for this
            message_factory: sonata.media.http.base_message_factory # You need nyholm/psr7 for this

        messenger:
            enabled: false
            generate_thumbnails_bus: messenger.default_bus
