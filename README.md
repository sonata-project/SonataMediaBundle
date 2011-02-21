# Prototype to easily manage media

## Installation

* Add MediaBundle to your src/Bundle dir

        git submodule add git@github.com:sonata-project/MediaBundle.git src/Sonata/MediaBundle

* add the Imagine bundle (image management) and follow ImagineBundle README

        git submodule add git://github.com/avalanche123/Imagine.git src/vendor/imagine

* Add EasyExtendsBundle to your application kernel

        // app/AppKernel.php
        public function registerBundles()
        {
            return array(
                // ...
                new Sonata\MediaBundle\MediaBundle(),
                // ...
            );
        }


* Add these lines into your config.yml file


        media.config:
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

* Add these lines into your admin.yml file (BaseApplicationBundle)

        media:
            label:      Media
            group:      Media
            class:      Sonata\MediaBundle\Admin\MediaAdmin
            entity:     Application\Sonata\MediaBundle\Entity\Media
            controller: SonataMediaBundle:MediaAdmin

        gallery:
            label:      Gallery
            group:      Media
            class:      Sonata\MediaBundle\Admin\GalleryAdmin
            entity:     Application\Sonata\MediaBundle\Entity\Gallery
            controller: SonataMediaBundle:GalleryAdmin

        gallery_has_media:
            label:      GalleryHasMedia
            group:      Media
            class:      Sonata\MediaBundle\Admin\GalleryHasMediaAdmin
            entity:     Application\Sonata\MediaBundle\Entity\GalleryHasMedia
            controller: SonataMediaBundle:GalleryHasMediaAdmin
            options:
                show_in_dashboard: false