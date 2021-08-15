UPGRADE FROM 3.x to 4.0
=======================

### Sonata\MediaBundle\Thumbnail\ResizableThumbnailInterface

  This interface is added to provide a clearer API for `FormatThumbnail`. It allows to know if a `Thumbnail` object can be generated with custom `Resizer` instances, not the ones provided by
  the file provider.

### Removed OpenStack / Rackspace integration

  This integration is removed because the php packages are not up to date.

### Integration with classification-bundle

  In SonataMediaBundle 4.0 we improved the integration with SonataClassificationBundle, you don't need
  to configure the `classification_manager` anymore.

### Container parameters

  We have been using container parameters as undocumented extension points for things like classes and configurations.

  In SonataMediaBundle 4.0 those are completely removed and we encourage you to use the default
  dependency injection override to change the default values for the removed service configurations if you need to.

  If you need to change something that you believe it should be handled somehow in configuration,
  please open an issue and we will discuss it.

### SimpleResizer and SquareResizer

  Previously the mode that could be configured for those resizer was a string, now it is an int.
  The default configuration is still the same but with the int value that represents `inset`.

  You can take a look at this class constants: `Imagine\Image\ManipulatorInterface` to see the
  available options.

### Sonata\DatagridBundle\Pager\PageableInterface

  Usages of `Sonata\Doctrine\Model\PageableManagerInterface` were replaced in favor of `Sonata\DatagridBundle\Pager\PageableInterface`.

### Dependencies

- Drop support for `kriswallsmith/buzz`

  If you are using media provider services with Buzz, you MUST create a custom service based on the Buzz client and add it to configuration:

       sonata_media:
           http:
               client: 'your_custom.buzz_client' # Psr\Http\Client\ClientInterface
               message_factory: 'your_custom.message_facory' # Psr\Http\Message\RequestFactoryInterface

- Drop support for `sonata-project/datagrid-bundle` < 3.0.

  If you are extending these methods, you MUST add argument and return type declarations:

    - `Sonata\MediaBundle\Entity\GalleryManager::getPager()`
    - `Sonata\MediaBundle\Entity\MediaManager::getPager()`
    - `Sonata\MediaBundle\Model\GalleryManager::getPager()`
    - `Sonata\MediaBundle\Model\MediaManager::getPager()`

- Drop support for `nelmio/api-doc-bundle` < 3.9

## Deprecations

All the deprecated code introduced on 3.x is removed on 4.0.

Please read [3.x](https://github.com/sonata-project/SonataMediaBundle/tree/3.x) upgrade guides for more information.

See also the [diff code](https://github.com/sonata-project/SonataMediaBundle/compare/3.x...4.0.0).

## Controllers

`MediaController` actions were changed to introduce `Request $request` as first parameter.
You must update those signatures if you want to still extend this class:
`downloadAction`, `listAction` and `liipImagineFilterAction`

## Blocks

The property `wrap` in `GalleryBlockService` was removed. You must create a custom block, if you still want to use it.

## Models

If you have implemented a custom model, you must adapt the signature of the following new methods:
 * `GalleryHasMediaInterface::getId`
 * `GalleryInterface::getId`

If you have overridden some date-related methods (`setUpdatedAt()`, `setCreatedAt()`, `setCdnFlushAt()`) in model classes (`Gallery`, `Media`, `GalleryHasMedia`),
you need to change the arguments' type declarations to `\DateTimeInterface`.

## Renamed GalleryHasMedia to GalleryItem

All Actions, Controllers, Interfaces and anything related to this is renamed accordingly.

## Removed classification dependency

The category feature is now optional and can be disabled in the configuration.
If you need this feature you have to require `sonata-project/classifcation-bundle` via composer.
