Installation
============

Base bundles
------------

This bundle is mainely dependant of the SonataAdminBundle and the SonataDoctrineORMAdminBundle. So be sure you have install those two bundles before start:

 * http://sonata-project.org/bundles/admin/master/doc/reference/installation.html
 * http://sonata-project.org/bundles/doctrine-orm-admin/master/doc/reference/installation.html

Installation
------------

Retrieve the bundle with composer:

.. code-block:: sh

    php composer.phar require sonata-project/media-bundle --no-update
    php composer.phar require sonata-project/doctrine-orm-admin-bundle --no-update


Register the new bundle into your AppKernel:

.. code-block:: php

  <?php
  // app/AppKernel.php
  public function registerBundles()
  {
      return array(
          // ...
          new Sonata\CoreBundle\SonataCoreBundle(),
          new Sonata\MediaBundle\SonataMediaBundle(),
          new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
          // ...
      );
  }

Next, add the correct routing files:

.. code-block:: yaml

    gallery:
        resource: '@SonataMediaBundle/Resources/config/routing/gallery.xml'
        prefix: /media/gallery

    media:
        resource: '@SonataMediaBundle/Resources/config/routing/media.xml'
        prefix: /media


Then you must configure the interaction with the orm and add the mediaBundles settings:

Doctrine ORM:

.. code-block:: yaml

    # app/config/config.yml

    doctrine:
        orm:
            entity_managers:
                default:
                    mappings:
                        SonataMediaBundle: ~

        dbal:
            types:
                json: Sonata\Doctrine\Types\JsonType

Doctrine PHPCR:

.. code-block:: yaml

    # app/config/config.yml

    doctrine_phpcr:
        odm:
            auto_mapping: true
            mappings:
                SonataMediaBundle:
                    prefix: Sonata\MediaBundle\PHPCR

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
                    small: { width: 100 , quality: 70}
                    big:   { width: 500 , quality: 70}

        cdn:
            server:
                path: /uploads/media # http://media.sonata-project.org/

        filesystem:
            local:
                directory:  %kernel.root_dir%/../web/uploads/media
                create:     false

.. note::

    You can define formats per provider type. You might want to set
    a transversal ``admin`` format to be used by the ``mediaadmin`` class.

Also, you can determine the resizer to use; the default value is
``sonata.media.resizer.simple`` but you can change it to ``sonata.media.resizer.square``

.. code-block:: yaml

    # app/config/config.yml

    sonata_media:
        providers:
            image:
                resizer: sonata.media.resizer.square

.. note::

    The square resizer works like the simple resizer when the image format has
    only the width. But if you specify the height the resizer crop the image in
    the lower size.

At this point, the bundle is not yet ready. You need to generate the correct
entities for the media::

    php app/console sonata:easy-extends:generate SonataMediaBundle

.. note::

    To be able to generate domain objects, you need to have a database driver configure in your project.
    If it's not the case, just follow this:
    http://symfony.com/doc/current/book/doctrine.html#configuring-the-database

.. note::

    The command will generate domain objects in an ``Application`` namespace.
    So you can point entities' associations to a global and common namespace.
    This will make Entities sharing very easier as your models will allow to
    point to a global namespace. For instance the media will be
    ``Application\Sonata\MediaBundle\Entity\Media``.


Now that your module is generated, you can register it

.. code-block:: php

    <?php
    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            ...
            new Application\Sonata\MediaBundle\ApplicationSonataMediaBundle(),
            ...
        );
    }

    # app/config/config.yml
      doctrine:
          orm:
              entity_managers:
                  default:
                      mappings:
                          ApplicationSonataMediaBundle: ~
                          SonataMediaBundle: ~
                          # add your own bundles here


Now, you can build up your database:

.. code-block:: sh

    app/console doctrine:schema:[create|update]


If they are not already created, you need to add specific folder to allow uploads from users:

.. code-block:: sh

    mkdir web/uploads
    mkdir web/uploads/media
    chmod -R 0777 web/uploads

Then you can visit your admin dashboard on http://my-server/admin/dashboard
