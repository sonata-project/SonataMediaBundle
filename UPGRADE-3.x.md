UPGRADE 3.x
===========

UPGRADE FROM 3.x to 3.x
=======================

### Deprecate API

Integration with FOSRest, JMS Serializer and Nelmio Api Docs is deprecated, the ReST API provided with this bundle will be removed on 4.0.

If you are relying on this, consider moving to other solution like [API Platform](https://api-platform.com/) instead.

### Deprecate Pixlr integration

Integration with Pixlr is deprecated now. There is no replacement since
the bundle is trying to focus on media management and not on the edit part.

### Media and Gallery Controllers

`viewAction()` and `indexAction()` from media and gallery controllers are deprecated now.
There is no provided replacement since those actions are suposed to be implemented in app side if you need them.

### Breadcrumbs classes

Breadcrumbs are deprecated, if your application uses them on the frontend side, you should use `sonata-project/seo-bundle` to implement them instead.

UPGRADE FROM 3.32 to 3.33
=========================

### Sonata\MediaBundle\Listener\BaseMediaEventSubscriber::getMedia() and its inheritances

Method `getMedia()` returns `null` if the related medium does not implement `Sonata\MediaBundle\Model\MediaInterface`.
Before this change, under the same situation this method was returning the invalid object.

### Sonata\MediaBundle\Filesystem\Replicate

Added implementation for `Gaufrette\Adapter\FileFactory` and `Gaufrette\Adapter\StreamFactory`.
Method `createFileStream()` is deprecated in favor of `createStream()`.
Argument 3 (`?array $metadata = null`) in method `write()` is deprecated.

### Sonata\MediaBundle\Thumbnail\ResizableThumbnailInterface

This interface is added to provide a clearer API for `FormatThumbnail`. It allows to know if a `Thumbnail` object can be generated with custom `Resizer` instances, not the ones provided by
the file provider.

If you have custom `Thumbnail` generator, please implement this interface if you need
custom resizers.

### Deprecate OpenStack / Rackspace integration

This integration is deprecated because the php packages are not up to date. On master
this integration will be removed. There is no replacement for it.

### Deprecate direct session injection

`$session` property in `Security/SessionDownloadStrategy` is deprecated. Use `RequestStack` `$requestStack` instead.

Before:

```php
    $downloadStrategy = new SessionDownloadStrategy($translator, $session, $times);
```

After:

```php
    $downloadStrategy = new SessionDownloadStrategy($translator, $requestStack, $times);
```

UPGRADE FROM 3.31 to 3.32
=========================

### Support for NelmioApiDocBundle > 3.6 is added

Controllers for NelmioApiDocBundle v2 were moved under `Sonata\MediaBundle\Controller\Api\Legacy\` namespace and controllers for NelmioApiDocBundle v3 were added as replacement. If you extend them, you must ensure they are using the corresponding inheritance.

### Deprecations

Usages of `Symfony\Component\Translation\TranslatorInterface` are deprecated in favor of `Symfony\Contracts\Translation\TranslatorInterface`. You MUST replace all references to them in your code and inject correct object into:
- `Sonata\MediaBundle\Security\ForbiddenDownloadStrategy`
- `Sonata\MediaBundle\Security\PublicDownloadStrategy`
- `Sonata\MediaBundle\Security\RolesDownloadStrategy`
- `Sonata\MediaBundle\Security\SessionDownloadStrategy`

### Sonata\MediaBundle\CDN\CloudFront

The previous signature of `CloudFront::__construct()` is deprecated.

Before:

```php
public function __construct(string $path, string $key, string $secret, string $distributionId, ?string $region = null, ?string $version = null)
```

After:

```php
public function __construct(Aws\CloudFront\CloudFrontClient $client, string $distributionId, string $path)
```

Returning `false` or any value not present in the `CDNInterface::STATUS_*` constants from `CloudFront::getFlushStatus()` is deprecated.

The methods `CloudFront::setClient()` and `CloudFront::getStatusList()` are deprecated.

### MogileFS filesystem adapter is deprecated

The services `sonata.media.adapter.filesystem.mogilefs`, `sonata.media.filesystem.mogilefs`
and the configuration node "sonata_media.filesystem.mogilefs" are deprecated.

### Configuration node "sonata_media.filesystem.s3.sdk_version"

The configuration node "sonata_media.filesystem.s3.sdk_version" is deprecated. The
version of aws/aws-sdk-php is automatically inferred from the installed package.

### Configuration node "sonata_media.cdn.cloudfront"

The configuration nodes "sonata_media.cdn.cloudfront.region" and "sonata_media.cdn.cloudfront.version"
are required when aws/aws-sdk-php 3.x is installed.

## BaseVideoProvider uses `psr/http-client`

The `Guzzle` and `Buzz` dependencies are deprecated and will be replaced with the abstract `http-client` interface, so you can choose your preferred client implementation. You should adapt to the new `BaseVideoProvider::__construct()` signature.

UPGRADE FROM 3.25 to 3.26
=========================

### Commands

Command classes were updated to inherit from `Command` instead of deprecated `ContainerAwareCommand`. Direct access to container will
no longer be possible. Services that were retrieved from the DIC MUST be injected instead.

### SonataEasyExtends is deprecated

Registering `SonataEasyExtendsBundle` bundle is deprecated, it SHOULD NOT be registered.
Register `SonataDoctrineBundle` bundle instead.

UPGRADE FROM 3.23 to 3.24
=========================

### Generators rename

Some classes have been renamed, the old types are just aliases for the new ones.
You should replace all references to them in your code.

- `Sonata\MediaBundle\Command\DefaultGenerator` is deprecated in favor of `Sonata\MediaBundle\Generator\IdGenerator`
- `Sonata\MediaBundle\Command\ODMGenerator` is deprecated in favor of `Sonata\MediaBundle\Generator\UuidGenerator`
- `Sonata\MediaBundle\Command\PHPCRGenerator` is deprecated in favor of `Sonata\MediaBundle\Generator\PathGenerator`

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
