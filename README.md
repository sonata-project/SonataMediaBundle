# Prototype to easily manage media

## Installation

* Add MediaBundle to your src/Bundle dir

        git submodule add git@github.com:sonata-project/MediaBundle.git src/Sonata/MediaBundle


* Add the AdminBundle if not yet installed

        git submodule add git@github.com:sonata-project/AdminBundle.git src/Sonata/AdminBundle

* Add the EasyExtendsBundle if not yet installed

        git submodule add git@github.com:sonata-project/EasyExtendsBundle.git src/Sonata/EasyExtendsBundle

* Add the Imagine library (image manipulation)

        git submodule add git://github.com/avalanche123/Imagine.git src/vendor/imagine

* Add the Gaufrette library (filesystem Abstraction)

        git submodule add https://github.com/knplabs/Gaufrette.git src/vendor/gaufrette

* Add MediaBundle to your application kernel

        // app/AppKernel.php
        public function registerBundles()
        {
            return array(
                // ...
                new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
                new Sonata\MediaBundle\MediaBundle(),
                // ...
            );
        }

* Run the command easy-extends:generate to generate the main model files

        php kooqit/console sonata:easy-extends:generate

* Add these lines into your config.yml file

        sonata_media:
            contexts:
                defaults:
                    providers:
                        - sonata.media.provider.dailymotion
                        - sonata.media.provider.youtube
                        - sonata.media.provider.image
                        - sonata.media.provider.file

                user:
                    providers:
                        - sonata.media.provider.dailymotion
                        - sonata.media.provider.youtube
                        - sonata.media.provider.image
                        - sonata.media.provider.file

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
                    formats:
                    resizer:    false
                    filesystem: sonata.media.filesystem.local
                    cdn:        sonata.media.cdn.server

                sonata.media.provider.image:
                    resizer:    sonata.media.resizer.simple
                    filesystem: sonata.media.filesystem.local
                    cdn:        sonata.media.cdn.server
                    formats:
                        small: { width: 100 , quality: 70}
                        big:   { width: 500 , quality: 70}
                        admin: { width: 300}

                sonata.media.provider.youtube:
                    resizer:    sonata.media.resizer.simple
                    filesystem: sonata.media.filesystem.local
                    cdn:        sonata.media.cdn.server
                    formats:
                        small: { width: 100 , quality: 70}
                        big:   { width: 500 , quality: 70}
                        admin: { width: 300}

                sonata.media.provider.dailymotion:
                    resizer:    sonata.media.resizer.simple
                    filesystem: sonata.media.filesystem.local
                    cdn:        sonata.media.cdn.server
                    formats:
                        small: { width: 100 , quality: 70}
                        big:   { width: 500 , quality: 70}
                        admin: { width: 300}

