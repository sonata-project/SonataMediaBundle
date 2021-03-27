UPGRADE FROM 3.x to 4.0
=======================

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

## Renamed GalleryHasMedia to GalleryItem

All Actions, Controllers, Interfaces and anything related to this is renamed accordingly.

## Removed classification dependency

The category feature is now optional and can be disabled in the configuration.
If you need this feature you have to require `sonata-project/classifcation-bundle` via composer.
