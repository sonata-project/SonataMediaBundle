Advanced Configuration
======================

Full configuration option


.. code-block:: php

    sonata_media:
        contexts:
            default:  # the default context is mandatory
                providers:
                    - sonata.media.provider.dailymotion
                    - sonata.media.provider.youtube
                    - sonata.media.provider.image
                    - sonata.media.provider.file

                formats:
                    small: { width: 100 , quality: 70}
                    big:   { width: 500 , quality: 70}

            tv:
                providers:
                    - sonata.media.provider.dailymotion
                    - sonata.media.provider.youtube
                    - sonata.media.provider.video

                formats:
                    cinema:     { width: 1850 , quality: 768}
                    grandmatv:  { width: 640 , quality: 480}

            news:
                providers:
                    - sonata.media.provider.dailymotion
                    - sonata.media.provider.youtube
                    - sonata.media.provider.image
                    - sonata.media.provider.file

                formats:
                    small: { width: 150 , quality: 95}
                    big:   { width: 500 , quality: 90}

        cdn:
            sonata.media.cdn.server:
                path: /uploads/media # http://media.sonata-project.org

            sonata.media.cdn.panther:
                path:       http://domain.pantherportal.com/uploads/media
                site_id:
                password:
                username:

        filesystem:
            sonata.media.adapter.filesystem.local:
                directory:  %kernel.root_dir%/../web/uploads/media
                create:     false

            sonata.media.adapter.filesystem.ftp:
                directory:
                host:
                username:
                password:
                port:     21
                passive:  false
                create:   false

        providers:
            sonata.media.provider.file:
                resizer:    false
                filesystem: sonata.media.filesystem.local
                cdn:        sonata.media.cdn.server

            sonata.media.provider.image:
                resizer:    sonata.media.resizer.simple
                filesystem: sonata.media.filesystem.local
                cdn:        sonata.media.cdn.server

            sonata.media.provider.youtube:
                resizer:    sonata.media.resizer.simple
                filesystem: sonata.media.filesystem.local
                cdn:        sonata.media.cdn.server

            sonata.media.provider.dailymotion:
                resizer:    sonata.media.resizer.simple
                filesystem: sonata.media.filesystem.local
                cdn:        sonata.media.cdn.server