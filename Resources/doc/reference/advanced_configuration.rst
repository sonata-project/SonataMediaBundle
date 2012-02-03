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

            s3:
                bucket:
                accessKey:
                secretKey:
                create:     false
                region:     # this settings does not seems to be implemented with Zend Framework

            replicate:
                master: sonata.media.adapter.filesystem.s3
                slave: sonata.media.adapter.filesystem.local

        providers:
            file:
                resizer:    false
                filesystem: sonata.media.filesystem.local
                cdn:        sonata.media.cdn.server
                generator:  sonata.media.generator.default
                thumbnail:  sonata.media.thumbnail.format

            image:
                resizer:    sonata.media.resizer.simple # sonata.media.resizer.square
                filesystem: sonata.media.filesystem.local
                cdn:        sonata.media.cdn.server
                generator:  sonata.media.generator.default
                thumbnail:  sonata.media.thumbnail.format

            youtube:
                resizer:    sonata.media.resizer.simple
                filesystem: sonata.media.filesystem.local
                cdn:        sonata.media.cdn.server
                generator:  sonata.media.generator.default
                thumbnail:  sonata.media.thumbnail.format

            dailymotion:
                resizer:    sonata.media.resizer.simple
                filesystem: sonata.media.filesystem.local
                cdn:        sonata.media.cdn.server
                generator:  sonata.media.generator.default
                thumbnail:  sonata.media.thumbnail.format

        buzz:
            connector:  sonata.media.buzz.connector.file_get_contents # sonata.media.buzz.connector.curl

