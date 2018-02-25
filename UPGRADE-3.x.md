UPGRADE 3.x
===========

UPGRADE FROM 3.6 to 3.7
=======================

### Doctrine schema update for GalleryHasMedia
Doctrine ORM join columns from GalleryHasMedia entity towards both Gallery and Media entities has been changed. Now
they include the `onDelete="CASCADE"` option: this allows to delete a media if included in a gallery (and vice-versa).
You should upgrade your database in a safe way after upgrading your vendors.

UPGRADE FROM 3.4 to 3.5
=======================

### Deprecations

Sonata\MediaBundle\DependencyInjection\Compiler\AddProviderCompilerPass::fixSettings($container)
is deprecated. Please avoid using this method, use ``getExtensionConfig($container)`` instead.

Sonata\MediaBundle\Controller\Controller\MediaController::liipImagineFilterAction($path, $filter)
is deprecated. Please avoid using this method.
If you define controller_action in liip_imagine configs please remove it.


UPGRADE FROM 3.2 to 3.3
=======================

### Providing a 2nd parameter for Sonata\MediaBundle\Metadata\ProxyMetadataBuilder::__construct() is deprecated

Before:

```php
public function __construct(ContainerInterface $container, array $map = null)
```

After:

```php
public function __construct(ContainerInterface $container)
```

### Sonata\MediaBundle\Command\AddMediaCommand::$output is depredated

Please avoid using this property!

### Not providing the 4th argument for Sonata\MediaBundle\Thumbnail\ConsumerThumbail::__construct() is deprecated

Before:

```php
__construct($id, ThumbnailInterface $thumbnail, BackendInterface $backend, EventDispatcherInterface $dispatcher = null)
```

After:

```php
__construct($id, ThumbnailInterface $thumbnail, BackendInterface $backend, EventDispatcherInterface $dispatcher)
```

### Custom video provider

When creating a custom video provider, you have to implement the ``getReferenceUrl`` method to establish
the media url.

UPGRADE FROM 3.0 to 3.1
=======================

### Tests

All files under the ``Tests`` directory are now correctly handled as internal test classes.
You can't extend them anymore, because they are only loaded when running internal tests.
More information can be found in the [composer docs](https://getcomposer.org/doc/04-schema.md#autoload-dev).

### Deprecated

`$container` property in `Security/SessionDownloadStrategy` is deprecated. Use `SessionInterface` `$session` instead.

Before:

```php
    $downloadStrategy = new SessionDownloadStrategy($translator, $container, $times);
```

After:

```php
    $downloadStrategy = new SessionDownloadStrategy($translator, $session, $times);
```
