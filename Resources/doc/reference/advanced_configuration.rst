Advanced Configuration
======================

Full configuration options:

.. code-block:: yaml

    sonata_media:
        db_driver: doctrine_orm
        class:
            media:              Application\Sonata\MediaBundle\Entity\Media
            gallery:            Application\Sonata\MediaBundle\Entity\Gallery
            gallery_has_media:  Application\Sonata\MediaBundle\Entity\GalleryHasMedia

        default_context: default
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
                    small: { width: 100 , quality: 70}
                    big:   { width: 500 , quality: 70}

            tv:
                download:
                    strategy: sonata.media.security.superadmin_strategy
                    mode: http
                providers:
                    - sonata.media.provider.dailymotion
                    - sonata.media.provider.youtube
                    - sonata.media.provider.video

                formats:
                    cinema:     { width: 1850 , quality: 768}
                    grandmatv:  { width: 640 , quality: 480}

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
                    small: { width: 150 , quality: 95}
                    big:   { width: 500 , quality: 90}

        cdn:
            server:
                path:      /uploads/media # http://media.sonata-project.org

            panther:
                path:       http://domain.pantherportal.com/uploads/media
                site_id:
                password:
                username:

            fallback:
                master:     sonata.media.cdn.panther
                fallback:   sonata.media.cdn.server

        filesystem:
            local:
                directory:  %kernel.root_dir%/../web/uploads/media
                create:     false

            ftp:
                directory:
                host:
                username:
                password:
                port:     21
                passive:  false
                create:   false
                mode:     2 # this is the FTP_BINARY constant. see: http://php.net/manual/en/ftp.constants.php

            s3:
                bucket:
                accessKey:
                secretKey:
                create:         false
                region:         s3.amazonaws.com # change if not using US Standard region
                storage:        standard # can be one of: standard or reduced
                acl:            public # can be one of: public, private, open, auth_read, owner_read, owner_full_control
                encryption:     aes256 # can be aes256 or not set
                cache_control:  max-age=86400 # or any other
                meta:
                    key1:       value1 #any amount of metas(sent as x-amz-meta-key1 = value1)

            mogilefs:
                hosts:      []
                domain:

            replicate:
                master: sonata.media.adapter.filesystem.s3
                slave: sonata.media.adapter.filesystem.local

            rackspace:
               url:
               secret:
                 username:
                 apiKey:
               region:
               containerName: media
               create_container: false

            openstack:
               url:
               secret:
                 username:
                 password:
               region:
               containerName: media
               create_container: false

        providers:
            file:
                service:    sonata.media.provider.file
                resizer:    false
                filesystem: sonata.media.filesystem.local
                cdn:        sonata.media.cdn.server
                generator:  sonata.media.generator.default
                thumbnail:  sonata.media.thumbnail.format
                allowed_extensions: ['pdf', 'txt', 'rtf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pttx', 'odt', 'odg', 'odp', 'ods', 'odc', 'odf', 'odb', 'csv', 'xml']
                allowed_mime_types: ['application/pdf', 'application/x-pdf', 'application/rtf', 'text/html', 'text/rtf', 'text/plain']

            image:
                service:    sonata.media.provider.image
                resizer:    sonata.media.resizer.simple # sonata.media.resizer.square
                filesystem: sonata.media.filesystem.local
                cdn:        sonata.media.cdn.server
                generator:  sonata.media.generator.default
                thumbnail:  sonata.media.thumbnail.format
                allowed_extensions: ['jpg', 'png', 'jpeg']
                allowed_mime_types: ['image/pjpeg', 'image/jpeg', 'image/png', 'image/x-png']

            youtube:
                service:    sonata.media.provider.youtube
                resizer:    sonata.media.resizer.simple
                filesystem: sonata.media.filesystem.local
                cdn:        sonata.media.cdn.server
                generator:  sonata.media.generator.default
                thumbnail:  sonata.media.thumbnail.format
                html5: false

            dailymotion:
                service:    sonata.media.provider.dailymotion
                resizer:    sonata.media.resizer.simple
                filesystem: sonata.media.filesystem.local
                cdn:        sonata.media.cdn.server
                generator:  sonata.media.generator.default
                thumbnail:  sonata.media.thumbnail.format

        buzz:
            connector:  sonata.media.buzz.connector.file_get_contents # sonata.media.buzz.connector.curl

