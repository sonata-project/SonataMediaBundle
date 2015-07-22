Troubleshooting
===============

Media Formats
-------------

You can define formats per provider type, there is something important that you should bear in mind, the quality for each format type should get a numeric value no higher than 100, this is the maximun value you should set. Do not confuse the value belonging to the format quality by the format height.

#### Prevent this possible issue:

> **imagepng(): gd-png error: compression level must be 0 through 9**

#### Use case:

For example, let's suppose you got a format called hq, and you want to set 1920 as the width value in your format, then the quality should be 100 as maximun value. Don't make the mistake of setting 1080 in the quality value.

Please take a look at how the images are compressed by this function in the image class:

.. code-block:: php

    /**
     * Internal
     *
     * Performs save or show operation using one of GD's image... functions
     *
     * @param string $format
     * @param array  $options
     * @param string $filename
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function saveOrOutput($format, array $options, $filename = null)
    {
        if (!$this->supported($format)) {
            throw new InvalidArgumentException(sprintf(
                'Saving image in "%s" format is not supported, please use one '.
                'of the following extension: "%s"', $format,
                implode('", "', $this->supported())
            ));
        }

        $save = 'image'.$format;
        $args = array(&$this->resource, $filename);

        if (($format === 'jpeg' || $format === 'png') &&
            isset($options['quality'])) {
            // Png compression quality is 0-9, so here we get the value from percent.
            // Beaware that compression level for png works the other way around.
            // For PNG 0 means no compression and 9 means highest compression level.
            if ($format === 'png') {
                $options['quality'] = round((100 - $options['quality']) * 9 / 100);
            }
            $args[] = $options['quality'];
        }

        if ($format === 'png' && isset($options['filters'])) {
            $args[] = $options['filters'];
        }

        if (($format === 'wbmp' || $format === 'xbm') &&
            isset($options['foreground'])) {
            $args[] = $options['foreground'];
        }

        $this->setExceptionHandler();

        if (false === call_user_func_array($save, $args)) {
            throw new RuntimeException('Save operation failed');
        }

        $this->resetExceptionHandler();
    }

Finally your settings in your sonataMedia parameters will look like this:

.. code-block:: yaml

    # app/config/config.yml
    sonata_media:
        # if you don't use default namespace configuration
        #class:
        #    media: MyVendor\MediaBundle\Entity\Media
        #    gallery: MyVendor\MediaBundle\Entity\Gallery
        #    gallery_has_media: MyVendor\MediaBundle\Entity\GalleryHasMedia
        default_context: default
        db_driver: doctrine_orm # or doctrine_mongodb, doctrine_phpcr
        contexts:
            default:  # the default context is mandatory
                providers:
                    - sonata.media.provider.dailymotion
                    - sonata.media.provider.youtube
                    - sonata.media.provider.image
                    - sonata.media.provider.file

                formats:
                    small: { width: 100 , quality: 70  }
                    big:   { width: 500 , quality: 70  }
                    hq:    { width: 1920, quality: 100 }

        cdn:
            server:
                path: /uploads/media # http://media.sonata-project.org/

        filesystem:
            local:
                directory:  %kernel.root_dir%/../web/uploads/media
                create:     false
