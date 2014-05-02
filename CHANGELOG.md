CHANGELOG
=========

A [BC BREAK] means the update will break the project for many reasons:

* new mandatory configuration
* new dependencies
* class refactoring


### 2014-05-02

* [BC BREAK] Add dependency to SonataIntlBundle, just enable the bundle in your AppKernel.

### 2014-04-11

* [BC BREAK] Removed admin show action, now merged with admin edit action. As a result, controller method, route and template no longer exist.

### 2014-03-27

* [BC BREAK] Switched GalleryBlockService from nivo-gallery (no longer maintained) to Bootstrap3 carousel

    If you overrode the GalleryBlockService or its template, the settings have changed:

    * ``animSpeed``, ``directionNav`` and ``progressBar`` have been removed
    * ``wrap`` has been added

    Moreover, the nivogallery jquery plugin has been removed.

### 2014-01-15

* Updated twig template ``SonataMediaBundle:Gallery:view.html.twig``

### 2013-12-16

* GalleryManager & MediaManager now extend DoctrineBaseManager from SonataCoreBundle.
* GalleryManager::update() is now deprecated, please use save() instead.

### 2013-09-16

* YouTube videos can now be inserted in HTML5.

  Add in your config.yml:
  ```yaml
  sonata_media:
      providers:
          youtube:
              html5: true #default value is false
  ```

### 2012-10-29

* [BC BREAK] The provider metadata field now uses the "json" type from sonata-project/doctrine-extensions

  Use the Migrate command to change old provider metadata fields into json:

  app/console sonata:media:migrate-json --table=media__media
