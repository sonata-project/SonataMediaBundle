# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [3.19.1](https://github.com/sonata-project/SonataMediaBundle/compare/3.19.0...3.19.1) - 2019-02-23

### Fixed
- return type for `CategoryManager::find()`
- Crash about `setFormTheme` when viewing the gallery list

## [3.19.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.18.1...3.19.0) - 2019-02-21

### Added
- Added configuration option for the media helper to generate a <picture> instead of <img>

### Changed
- Sort galleryHasMedias on GalleryAdmin::postUpdate

### Fixed
- `SimpleResizer::computeBox()` will return the size of the resulting image
  instead of just the scaled image before the cropping step.
- Fix configuration defaults: omitted dimensions in format definitions must be `null`
- Strict checking of height/width sizes in formats configuration
- Crash about `setFormTheme` when viewing the media list
- `TypeError` on media upload when validation fails for another field

## [3.18.1](https://github.com/sonata-project/SonataMediaBundle/compare/3.18.0...3.18.1) - 2019-01-21
### Fixed
- crash about wrong provider argument type when trying to upload a media

## [3.18.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.17.1...3.18.0) - 2019-01-18

### Added
- Added alias for providers to use autoconfigure via DependencyInjection
- Added `Sonata\MediaBundle\Provider\Metadata` class

### Changed
- Changed `MediaProviderInterface::getProviderMetadata` return type in PHPDoc
- block classes will use the block specific Metadata class

### Removed
- Removed CoreBundle deprecations

## [3.17.1](https://github.com/sonata-project/SonataMediaBundle/compare/3.17.0...3.17.1) - 2019-01-12

### Fixes
- crashes on pages with null medias

## [3.17.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.16.3...3.17.0) - 2019-01-10
### Added
- Added an alias for the pool
- Possiblity to create custom resizers

### Changed
- Allow KnpLabs/Gaufrette 0.8

### Removed
- support for php 5 and php 7.0

### Fixed
- Fix deprecation for symfony/config 4.2+
- Deprecations about `Sonata\CoreBundle\Model\BaseEntityManager`

### Deprecated
- Undeprecated `MediaManagerInterface`

## [3.16.3](https://github.com/sonata-project/SonataMediaBundle/compare/3.16.2...3.16.3) - 2018-11-05
### Fixed
- Added missing methods to `FormatterMediaExtension`

## [3.16.2](https://github.com/sonata-project/SonataMediaBundle/compare/3.16.1...3.16.2) - 2018-10-17
### Fixed
 - Fix dailymotion regex to extract the video reference from the url.

## [3.16.1](https://github.com/sonata-project/SonataMediaBundle/compare/3.16.0...3.16.1) - 2018-10-03
### Fixed
- Prevent incompatible `sonata-project/formatter-bundle` 4 from being installed

## [3.16.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.15.0...3.16.0) - 2018-09-23

### Added
- S3 Connection without use of credentials (access and secret keys) but with
  IAM roles (Amazon instances must have been configured with it).

### Fixed
- Fixed S3 credentials set to false when no credentials are provided
- Bug where an extension config with unresolved parameters was processed in a compiler pass

## [3.15.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.14.0...3.15.0) - 2018-07-06

### Added
- Added getters for `allowedExtensions` and  `allowedMimeTypes` properties

## [3.14.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.13.1...3.14.0) - 2018-06-27
### Changed
- Make service alias `sonata.media.manager.category` public
- Allow Gaufrette 0.6
- Moved a code block from BaseProvider::postRemove to BaseProvider::preRemove

### Fixed
 - Make services public

## [3.13.1](https://github.com/sonata-project/SonataMediaBundle/compare/3.13.0...3.13.1) - 2018-05-25

### Changed
- Force use existing translation strings for Media/Gallery breadcrumbs in Admin panel
- Deprecations from controllers still using deprecated `render` instead of `renderWithExtraParams()`
- Set providers, metadata and manager services public so applications using SonataMediaBundle can upgrade to Symfony 4.0.

## [3.13.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.12.0...3.13.0) - 2018-05-17

### Changed

- A `db_driver` configuration parameter is optional now with `no_driver` default value

### Fixed

- Issue where all medias were removed from a gallery on update.
- Fixed widget template for MediaType as a child form type
- `sonata:media:sync-thumbnails` command when running this command with PHPCR or ODM  document mapper.

## [3.12.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.11.0...3.12.0) - 2018-04-09
### Changed
- Changed Vimeo endpoint to https

### Fixed
- LiipImagine generatePublicUrl updated to work with latest version of that bundle
- Relative path when path it is already an url

### Removed
- Removed compatibility with older versions of FOSRestBundle (<2.1)
- Removed `SonataNotificationBundle` as a required dependency

## [3.11.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.10.2...3.11.0) - 2018-02-23
### Added
- added block title translation domain option
- added block icon option
- added block class option
- Added compatibility with Gaufrette `^0.4` and `^0.5`
- Added compatibility with Buzz `^0.16`

### Fixed
- Commands not working on symfony4
- `AmazonMetadataBuilder` now relies on Psr7 mimeType guesser

### Removed
- Removed default title from blocks
- Removed old `sonata-` classes from templates
- Removed compatibility with Gaufrette `^0.1`and `^0.2`
- Removed compatibility with JMS serializer `^0.13`

## [3.10.2](https://github.com/sonata-project/SonataMediaBundle/compare/3.10.1...3.10.2) - 2018-02-02
### Added
- Added support for latest imagine version

### Changed
- Make services public

## [3.10.1](https://github.com/sonata-project/SonataMediaBundle/compare/3.10.0...3.10.1) - 2018-01-26
### Added
- Added missing SquareResizer::$metadata property
- Added Catalan translations

### Fixed
- Selected gallery context is now translated just like in the dropdown.
- Replaced `getMockBuilder` with `createMock` where it was possible
- Fixed phpdoc

## [3.10.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.9.0...3.10.0) - 2017-11-30
### Added
- Added automatically adding src format to srcset

### Fixed
- Return size after resize in cropping flow, not just the cropped dimensions
- FOSRest-related deprecations
- It is now allowed to install Symfony 4

## [3.9.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.8.0...3.9.0) - 2017-11-23
### Added
- Added option to set the img `srcset` tag by giving it an array of format names.

### Changed
- Rollback to PHP 5.6 as minimum support.

### Fixed
- fixed bug against twig 2.0 as `translationBundle` cannot be null
- Silent `sonata:media:remove-thumbnails` command when running this command without arguments.
- Sf3 compatibility on the sync-thumbnails command (dialog helper)
- Sf3 compatibility on the refresh-metadata and update-cdn-status commands (dialog helper)
- Use FormRenderer runtime to maintain compatibility with Symfony 3.4

## [3.8.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.7.0...3.8.0) - 2017-10-22
### Removed
- Support for old versions of php and Symfony.

## [3.7.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.6.0...3.7.0) - 2017-10-22
### Added
- Added `'onDelete' => 'CASCADE'` for mapping from GalleryHasMedia to Media and Gallery

### Changed
- Use SonataAdminBundle configuration to configure bundle services

### Fixed
- Prevent file from being removed if an error occurred while deleting its database entry.
- deprecation notices related to `addClassesToCompile`

## [3.6.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.5.1...3.6.0) - 2017-08-01
### Added
- The Image Provider returns responsive images to the twig media helper.

### Changed
- Allowed `jms/serializer-bundle ^2.0`

### Fixed
- Change Youtube urls to use https
- The DataTransformers and MediaTypes (both standard and API) now depend on `Psr\Log\LoggerInterface` in order to log any exception that could arise from `$provider->transform()` to get form errors when uploads are too big!
- fix protocol error from image url returned by pixlr when sonata is under https protocol
- Fixed hardcoded paths to classes in `.xml.skeleton` files of config
- Ability to extend the `MediaExtension` class

## [3.5.1](https://github.com/sonata-project/SonataMediaBundle/compare/3.5.0...3.5.1) - 2017-03-31
### Changed
- allow `knplabs/gaufrette v0.3.0`

### Fixed
- Replace missing `providers` column by `providerName` on clean command

## [3.5.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.4.0...3.5.0) - 2017-03-08
### Fixed
- Optional dependency to SonataFormatterBundle is now on `^3.2`
- Fixed issue when using SonataMediaBundle blocks in conjunction with SonataPageBundle and page composer (Sonata sandbox)
- Double padding on media list

### Removed
- Removed an ugly hack to retrieve configuration on `AddProviderCompilerPass`

## [3.4.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.3.1...3.4.0) - 2017-02-28
### Added
- An icon to admin menu (fa-image)
- Added `getRequest` method on controller for BC with Symfony 2.3+
- Added test on `MediaAdminController`

### Changed
- Replaced form types for the FQCN's

### Fixed
- Support for Twig 2.0
- Callback contraint is not a valid callable on Symfony 3
- `BaseAdmin` incorrectly retrieved `providerName`
- Fixed BlockBundle deprecation messages
- Fixed pager test with DatagridBundle 2.2.1
- Media List is now filterable by Category again
- Incorrect access to providerName parameter in request in `Admin/BaseMediaAdmin.php`
- Wrong FQCN's and added missing end() on GalleryAdmin
- Calling a macro without importing it is an error on twig 2.0
- Remove deprecations from non FQCNs on form types on `MediaAdmin`

### Removed
- `cascade_validation` from `GalleryAdmin`
- ClassificationBundle is now an optional dependency

## [3.3.1](https://github.com/sonata-project/SonataMediaBundle/compare/3.3.0...3.3.1) - 2017-02-02
### Added
- Added filesize check to upload
- Added empty filename check to upload
- Generate thumbnails asynchronously if creating Media on console commands.

### Changed
- translation in twig templates now uses the twig translation filter
- Moved ApiDoc groups from string to array.

### Fixed
- Deprecation warning for `addDownloadSecurity`
- Moved FosRest groups from string to array and reimplemented the orderBy parameter enabling support for FosRestBundle>=2.0.
- Missing italian translations

## [3.3.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.2.0...3.3.0) - 2016-09-08
### Added
- Added config key to define default resizer
- Added config key to define default resizer adapter

### Fixed
- The `provider` and `context` options are now required
- Use `$request` instead of  `$this->get('request')`

### Removed
- Ability to provide custom attributes for a thumbnail

## [3.2.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.1.0...3.2.0) - 2016-08-18
### Added
- Created `getReferenceUrl` method for all video providers
- Add parameter to specify aws_sdk version

### Changed
- Allow `knplabs/gaufrette` `^0.2`
- Update configuration and metadatabuilder to comply with AWS SDK 3.x

### Fixed
- Fixed wrong block inheritance in edit template
- Fixed wrong html usage in edit template
- Fixed loop in `Pool::getDownloadSecurity`
- Fixed deprecated call of `downloadSecurity` in `Resources/views/MediaAdmin/edit.html.twig` template.

## [3.1.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.0.0...3.1.0) - 2016-07-22
### Added
- Added `Sonata\MediaBundle\Listener\ORM\MediaEventSubscriber::onClear` to clear the `rootCategories` cache when the EntityManager is cleared
- Added `region` key to S3Client config
- Added `alt` attribute to thumbnail twig tag

### Changed
- Injection of `Session` instead of the whole `Container` in `Security/SessionDownloadStrategy`
- `Sonata\MediaBundle\Listener\ORM\MediaEventSubscriber::onClear` now subscribes to `onClear` too

### Deprecated
- `$container` property in `Security/SessionDownloadStrategy`
- Deprecated `Pool::$downloadSecurities` for `Pool::$downloadStrategies` property
- Deprecated `Pool::addDownloadSecurity` for `Pool::addDownloadStrategy` method
- Deprecated `Pool::getDownloadSecurity` for `Pool::getDownloadStrategy` method

### Fixed
- Restored `ApiDoc` and `QueryParam` use statements in `Api/GalleryController`
- Added missing `sonata-project/block-bundle` dependency
- Fixed media widget spanish translations
- Support for FOSRestBundle 2.0
- Fixed `ApiMediaType::getParent` compatibility with Symfony3 forms
- Fixed `MediaType::buildForm` compatibility with Symfony3 forms
- Fixed `MediaType::getParent` compatibility with Symfony3 forms
- Fixed `BaseVideoProvider::buildEditForm` compatibility with Symfony3 forms
- Fixed `BaseVideoProvider::buildCreateForm` compatibility with Symfony3 forms
- Fixed `BaseVideoProvider:: buildMediaType` compatibility with Symfony3 forms
- Fixed `FileProvider::buildEditForm` compatibility with Symfony3 forms
- Fixed `FileProvider::buildCreateForm` compatibility with Symfony3 forms
- Fixed `FileProvider::buildMediaType` compatibility with Symfony3 forms
- Fixed mixed-content error when loading Pixlr editor under https
- Gaufrette compatibility with Symfony 3
- Fix deprecated usage of `Admin` class
- Added missing `BaseProvider::$name` property
- Removed double translation in gallery edit form
- Reuse of root categories instances after the entity manager has been cleared

### Removed
- Internal test classes are now excluded from the autoloader
