# Prototype to easily manage media

## Installation

* Add MediaBundle to your src/Bundle dir

        git submodule add git@github.com:sonata-project/MediaBundle.git src/Sonata/MediaBundle


* Add the AdminBundle if not yet installed

        git submodule add git@github.com:sonata-project/AdminBundle.git src/Sonata/AdminBundle

* Add the EasyExtendsBundle if not yet installed

        git submodule add git@github.com:sonata-project/EasyExtendsBundle.git src/Sonata/EasyExtendsBundle

* Add the Imagine bundle (image management) and follow ImagineBundle README

        git submodule add git://github.com/avalanche123/Imagine.git src/vendor/imagine

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
            class: Sonata\MediaBundle\Provider\Service

            settings:
                cdn_enabled: false
                cdn_path:     http://media.sonata-project.org
                public_path: /uploads/media
                private_path: /web/uploads/media

            providers:
                image:
                    class: Sonata\MediaBundle\Provider\ImageProvider
                    formats:
                        small: { width: 100 }
                        big:   { width: 500 }

                youtube:
                    class: Sonata\MediaBundle\Provider\YouTubeProvider
                    formats:
                        small: { width: 100 }
                        big:   { width: 500 }

                dailymotion:
                    class: Sonata\MediaBundle\Provider\DailyMotionProvider
                    formats:
                        small: { width: 100 }
                        big:   { width: 500 }
