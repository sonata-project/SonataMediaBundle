UPGRADE 3.x
===========

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
