# Prototype to easily manage media

## Installation

* Add MediaBundle to your src/Bundle dir

        git submodule add git@github.com:sonata-project/MediaBundle.git src/Bundle/MediaBundle

* add the Imagine bundle (image management) and follow ImagineBundle README

        git submodule add git://github.com/avalanche123/Imagine.git src/vendor/imagine

* Add EasyExtendsBundle to your application kernel

        // app/AppKernel.php
        public function registerBundles()
        {
            return array(
                // ...
                new Bundle\MediaBundle\MediaBundle(),
                // ...
            );
        }


* Add this line into your config.yml file 


        media.config:
            class: Bundle\MediaBundle\Provider\Service

            settings:
                cdn_enabled: false
                cdn_path:     http://media.sonata-project.org
                public_path: /uploads/media
                private_path: /web/uploads/media


            providers:
                image:
                    class: Bundle\MediaBundle\Provider\ImageProvider
                    formats:
                        small: { width: 100 }
                        big:   { width: 500 }

                youtube:
                    class: Bundle\MediaBundle\Provider\YouTubeProvider
                    formats:
                        small: { width: 100 }
                        big:   { width: 500 }

                dailymotion:
                    class: Bundle\MediaBundle\Provider\DailyMotionProvider
                    formats:
                        small: { width: 100 }
                        big:   { width: 500 }
