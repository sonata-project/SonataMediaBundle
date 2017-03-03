UPGRADE FROM 3.x to 4.0
=======================

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
