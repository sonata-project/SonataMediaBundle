# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [4.14.0](https://github.com/sonata-project/SonataMediaBundle/compare/4.13.0...4.14.0) - 2024-08-27
### Added
- [[#2461](https://github.com/sonata-project/SonataMediaBundle/pull/2461)] Support for psr/http-message 2 ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#2455](https://github.com/sonata-project/SonataMediaBundle/pull/2455)] Symfony 7.1 deprecation about `Symfony\Component\HttpKernel\DependencyInjection\Extension` usage ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#2452](https://github.com/sonata-project/SonataMediaBundle/pull/2452)] Remove format restriction on `FileThumbnail` service ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.13.0](https://github.com/sonata-project/SonataMediaBundle/compare/4.12.0...4.13.0) - 2024-03-17
### Added
- [[#2448](https://github.com/sonata-project/SonataMediaBundle/pull/2448)] Compatibility with ORM 3 ([@dmaicher](https://github.com/dmaicher))

## [4.12.0](https://github.com/sonata-project/SonataMediaBundle/compare/4.11.1...4.12.0) - 2024-03-08
### Changed
- [[#2444](https://github.com/sonata-project/SonataMediaBundle/pull/2444)] Throw TransformationFailedException when reverse transformation fails in ProviderDataTransformer. ([@jorrit](https://github.com/jorrit))

## [4.11.1](https://github.com/sonata-project/SonataMediaBundle/compare/4.11.0...4.11.1) - 2024-02-18
### Fixed
- [[#2442](https://github.com/sonata-project/SonataMediaBundle/pull/2442)] Resolve "Admin has no subject" error from the getMediaBuilder ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#2442](https://github.com/sonata-project/SonataMediaBundle/pull/2442)] Resolve "Admin has no subject" error from the getGalleryBuilder ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.11.0](https://github.com/sonata-project/SonataMediaBundle/compare/4.10.5...4.11.0) - 2023-12-15
### Added
- [[#2430](https://github.com/sonata-project/SonataMediaBundle/pull/2430)] Support for packages from `symfony/*` 7.x ([@phansys](https://github.com/phansys))

## [4.10.5](https://github.com/sonata-project/SonataMediaBundle/compare/4.10.4...4.10.5) - 2023-11-21
### Changed
- [[#2351](https://github.com/sonata-project/SonataMediaBundle/pull/2351)] How `picture` element twig options are merged, making them work same way as `srcset` ([@haivala](https://github.com/haivala))

### Fixed
- [[#2420](https://github.com/sonata-project/SonataMediaBundle/pull/2420)] Fix doctrine mapping for media length ([@core23](https://github.com/core23))
- [[#2423](https://github.com/sonata-project/SonataMediaBundle/pull/2423)] Usage of deprecated `renderWithExtraParams` method ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.10.4](https://github.com/sonata-project/SonataMediaBundle/compare/4.10.3...4.10.4) - 2023-09-11
### Fixed
- [[#2414](https://github.com/sonata-project/SonataMediaBundle/pull/2414)] Improve French translation ([@antoinemineau](https://github.com/antoinemineau))

## [4.10.3](https://github.com/sonata-project/SonataMediaBundle/compare/4.10.2...4.10.3) - 2023-08-01
### Fixed
- [[#2411](https://github.com/sonata-project/SonataMediaBundle/pull/2411)] Deprecation of Event Subscribers on Symfony 6.3. The MediaEventSubscriber now uses Event Listeners ([@Hanmac](https://github.com/Hanmac))

## [4.10.2](https://github.com/sonata-project/SonataMediaBundle/compare/4.10.1...4.10.2) - 2023-07-29
### Changed
- [[#2402](https://github.com/sonata-project/SonataMediaBundle/pull/2402)] Resole "Admin has no subject" error from the getMediaBuilder ([@Elkawaie](https://github.com/Elkawaie))

## [4.10.1](https://github.com/sonata-project/SonataMediaBundle/compare/4.10.0...4.10.1) - 2023-07-22
### Fixed
- [[#2405](https://github.com/sonata-project/SonataMediaBundle/pull/2405)] * `round()` can return 0 which is invalid for image resizing. ([@silasjoisten](https://github.com/silasjoisten))

## [4.10.0](https://github.com/sonata-project/SonataMediaBundle/compare/4.9.0...4.10.0) - 2023-06-04
### Added
- [[#2396](https://github.com/sonata-project/SonataMediaBundle/pull/2396)] Support for SonataBlockBundle 5.0 ([@jordisala1991](https://github.com/jordisala1991))

## [4.9.0](https://github.com/sonata-project/SonataMediaBundle/compare/4.8.1...4.9.0) - 2023-05-13
### Added
- [[#2393](https://github.com/sonata-project/SonataMediaBundle/pull/2393)] Support for `sonata-project/form-extensions` 2.0 ([@jordisala1991](https://github.com/jordisala1991))

## [4.8.1](https://github.com/sonata-project/SonataMediaBundle/compare/4.8.0...4.8.1) - 2023-04-25
### Fixed
- [[#2384](https://github.com/sonata-project/SonataMediaBundle/pull/2384)] `sonata.media.metadata.amazon` is removed from the container if the configuration for S3 is not enabled ([@jordisala1991](https://github.com/jordisala1991))

## [4.8.0](https://github.com/sonata-project/SonataMediaBundle/compare/4.7.1...4.8.0) - 2023-04-24
### Removed
- [[#2382](https://github.com/sonata-project/SonataMediaBundle/pull/2382)] Support for Symfony 4.4 ([@jordisala1991](https://github.com/jordisala1991))
- [[#2382](https://github.com/sonata-project/SonataMediaBundle/pull/2382)] Support for Twig 2 ([@jordisala1991](https://github.com/jordisala1991))

## [4.7.1](https://github.com/sonata-project/SonataMediaBundle/compare/4.7.0...4.7.1) - 2023-04-12
### Changed
- [[#2364](https://github.com/sonata-project/SonataMediaBundle/pull/2364)] Italian translations updated (missing keys, keys ordering) ([@gremo](https://github.com/gremo))

### Fixed
- [[#2363](https://github.com/sonata-project/SonataMediaBundle/pull/2363)] `gallery_item` translation key (english) ([@gremo](https://github.com/gremo))
- [[#2379](https://github.com/sonata-project/SonataMediaBundle/pull/2379)] ORM schema mapping does not give errors about validation root ([@jordisala1991](https://github.com/jordisala1991))
- [[#2374](https://github.com/sonata-project/SonataMediaBundle/pull/2374)] Deprecation for boolean field on MongoDB ODM ([@jordisala1991](https://github.com/jordisala1991))

## [4.7.0](https://github.com/sonata-project/SonataMediaBundle/compare/4.6.0...4.7.0) - 2023-03-09
### Added
- [[#2360](https://github.com/sonata-project/SonataMediaBundle/pull/2360)] Added support for `knplabs/gaufrette` ^0.11. ([@jordisala1991](https://github.com/jordisala1991))

### Removed
- [[#2360](https://github.com/sonata-project/SonataMediaBundle/pull/2360)] Drop support for `doctrine/persistence` ^2.0. ([@jordisala1991](https://github.com/jordisala1991))
- [[#2361](https://github.com/sonata-project/SonataMediaBundle/pull/2361)] Support for PHP 7.4
- [[#2361](https://github.com/sonata-project/SonataMediaBundle/pull/2361)] Support for Symfony 6.0 and 6.1

## [4.6.0](https://github.com/sonata-project/SonataMediaBundle/compare/4.5.1...4.6.0) - 2023-02-19
### Added
- [[#2358](https://github.com/sonata-project/SonataMediaBundle/pull/2358)] Added new configuration parameter `sonata_media.filesystem.s3.async` ([@ozahorulia](https://github.com/ozahorulia))

## [4.5.1](https://github.com/sonata-project/SonataMediaBundle/compare/4.5.0...4.5.1) - 2022-08-31
### Fixed
- [[#2333](https://github.com/sonata-project/SonataMediaBundle/pull/2333)] Allow CloudFront to compress files passing ContentLength in the headers. ([@dmitryuk](https://github.com/dmitryuk))

## [4.5.0](https://github.com/sonata-project/SonataMediaBundle/compare/4.4.0...4.5.0) - 2022-08-04
### Added
- [[#2326](https://github.com/sonata-project/SonataMediaBundle/pull/2326)] Support for sonata-project/doctrine-extensions ^2 ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#2326](https://github.com/sonata-project/SonataMediaBundle/pull/2326)] Support for sonata-project/twig-extensions ^2 ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.4.0](https://github.com/sonata-project/SonataMediaBundle/compare/4.3.0...4.4.0) - 2022-06-28
### Added
- [[#2317](https://github.com/sonata-project/SonataMediaBundle/pull/2317)] Add support for `doctrine/persistence` ^3.0. ([@jordisala1991](https://github.com/jordisala1991))

## [4.3.0](https://github.com/sonata-project/SonataMediaBundle/compare/4.2.0...4.3.0) - 2022-06-25
### Changed
- [[#2313](https://github.com/sonata-project/SonataMediaBundle/pull/2313)] `MediaType` form type will now allow you to pass your own `data_class` instead of relying on the provided `$this->class`. ([@esserj](https://github.com/esserj))

### Removed
- [[#2314](https://github.com/sonata-project/SonataMediaBundle/pull/2314)] Avoid deprecations for console commands on Symfony 6.1. ([@jordisala1991](https://github.com/jordisala1991))

## [4.2.0](https://github.com/sonata-project/SonataMediaBundle/compare/4.1.5...4.2.0) - 2022-06-12
### Changed
- [[#2310](https://github.com/sonata-project/SonataMediaBundle/pull/2310)] Bump `imagine/imagine` to ^1.3. ([@jordisala1991](https://github.com/jordisala1991))

### Removed
- [[#2308](https://github.com/sonata-project/SonataMediaBundle/pull/2308)] Support of Symfony 5.3 ([@franmomu](https://github.com/franmomu))

## [4.1.5](https://github.com/sonata-project/SonataMediaBundle/compare/4.1.4...4.1.5) - 2022-04-27
### Fixed
- [[#2299](https://github.com/sonata-project/SonataMediaBundle/pull/2299)] Check for allowed extensions and mimetypes are case insensitive. ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.1.4](https://github.com/sonata-project/SonataMediaBundle/compare/4.1.3...4.1.4) - 2022-03-02
### Fixed
- [[#2291](https://github.com/sonata-project/SonataMediaBundle/pull/2291)] Translate providerName filter choices ([@jordisala1991](https://github.com/jordisala1991))

## [4.1.3](https://github.com/sonata-project/SonataMediaBundle/compare/4.1.2...4.1.3) - 2022-02-28
### Fixed
- [[#2289](https://github.com/sonata-project/SonataMediaBundle/pull/2289)] Deprecations introduces by SonataAdminBundle 4.9.0 ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.1.2](https://github.com/sonata-project/SonataMediaBundle/compare/4.1.1...4.1.2) - 2022-02-25
### Fixed
- [[#2285](https://github.com/sonata-project/SonataMediaBundle/pull/2285)] Fixed auto detect extension if the file does not provide it, for example when dealing with temporal filenames. ([@jordisala1991](https://github.com/jordisala1991))

## [4.1.1](https://github.com/sonata-project/SonataMediaBundle/compare/4.1.0...4.1.1) - 2022-02-13
### Fixed
- [[#2280](https://github.com/sonata-project/SonataMediaBundle/pull/2280)] Fix root category check ([@core23](https://github.com/core23))

## [4.1.0](https://github.com/sonata-project/SonataMediaBundle/compare/4.0.1...4.1.0) - 2022-02-12
### Added
- [[#2275](https://github.com/sonata-project/SonataMediaBundle/pull/2275)] Added support for `psr/log` ^3.0 ([@jordisala1991](https://github.com/jordisala1991))
- [[#2275](https://github.com/sonata-project/SonataMediaBundle/pull/2275)] Added support for `symfony/translation-contracts` ^3.0. ([@jordisala1991](https://github.com/jordisala1991))

## [4.0.1](https://github.com/sonata-project/SonataMediaBundle/compare/4.0.0...4.0.1) - 2022-02-08
### Fixed
- [[#2273](https://github.com/sonata-project/SonataMediaBundle/pull/2273)] Fixed admin media translations ([@jordisala1991](https://github.com/jordisala1991))
- [[#2273](https://github.com/sonata-project/SonataMediaBundle/pull/2273)] Fixed admin gallery translations ([@jordisala1991](https://github.com/jordisala1991))

## [4.0.0](https://github.com/sonata-project/SonataMediaBundle/compare/4.0.0-RC2...4.0.0) - 2022-01-25
### Added
- [[#2222](https://github.com/sonata-project/SonataMediaBundle/pull/2222)] Added support for Symfony 6. ([@jordisala1991](https://github.com/jordisala1991))

### Changed
- [[#2227](https://github.com/sonata-project/SonataMediaBundle/pull/2227)] Argument 1 in order to allow any iterable type at `MediaInterface::setGalleryItems()` and `GalleryInterface::setGalleryItems()`. ([@phansys](https://github.com/phansys))

### Fixed
- [[#2267](https://github.com/sonata-project/SonataMediaBundle/pull/2267)] Fixed usage of this bundle with PostgreSQL. ([@jordisala1991](https://github.com/jordisala1991))
- [[#2266](https://github.com/sonata-project/SonataMediaBundle/pull/2266)] Fixed placeholder for files on media list. ([@jordisala1991](https://github.com/jordisala1991))
- [[#2264](https://github.com/sonata-project/SonataMediaBundle/pull/2264)] Loading Media / Gallery object from the database. ([@jordisala1991](https://github.com/jordisala1991))
- [[#2225](https://github.com/sonata-project/SonataMediaBundle/pull/2225)] Fixed `CRUDController` registration for GalleryItemAdmin. ([@jordisala1991](https://github.com/jordisala1991))
- [[#2225](https://github.com/sonata-project/SonataMediaBundle/pull/2225)] Fixed default values for models to match the explanations on the documentation. ([@jordisala1991](https://github.com/jordisala1991))

### Removed
- [[#2264](https://github.com/sonata-project/SonataMediaBundle/pull/2264)] Final modifier on all the getters / setters of the entities. ([@jordisala1991](https://github.com/jordisala1991))

## [4.0.0-RC2](https://github.com/sonata-project/SonataMediaBundle/compare/4.0.0-RC1...4.0.0-RC2) - 2021-11-23
### Fixed
- [[#2219](https://github.com/sonata-project/SonataMediaBundle/pull/2219)] Fix block service registration ([@core23](https://github.com/core23))

## [4.0.0-RC1](https://github.com/sonata-project/SonataMediaBundle/compare/4.0.0-alpha1...4.0.0-RC1) - 2021-11-17
### Changed
- [[#2204](https://github.com/sonata-project/SonataMediaBundle/pull/2204)] Make "sonata-project/admin-bundle" dependency optional for blocks ([@core23](https://github.com/core23))
- [[#2201](https://github.com/sonata-project/SonataMediaBundle/pull/2201)] Changed visibility for public methods on Admin classes. ([@jordisala1991](https://github.com/jordisala1991))

### Fixed
- [[#2214](https://github.com/sonata-project/SonataMediaBundle/pull/2214)] Fix block service registration ([@core23](https://github.com/core23))

## [4.0.0-alpha1](https://github.com/sonata-project/SonataMediaBundle/compare/3.35.1...4.0.0-alpha1) - 2021-10-21

## [3.38.1](https://github.com/sonata-project/SonataMediaBundle/compare/3.38.0...3.38.1) - 2022-02-24
### Added
- [[#2285](https://github.com/sonata-project/SonataMediaBundle/pull/2285)] Fixed auto detect extension if the file does not provide it, for example when dealing with temporal filenames. ([@jordisala1991](https://github.com/jordisala1991))

## [3.38.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.37.1...3.38.0) - 2021-02-12
### Added
- [[#2271](https://github.com/sonata-project/SonataMediaBundle/pull/2271)] `ImageProviderInterface` so `ImageProvider` can be replaced without extending the class. ([@jorrit](https://github.com/jorrit))

## [3.37.1](https://github.com/sonata-project/SonataMediaBundle/compare/3.37.0...3.37.1) - 2021-12-09
### Fixed
- [[#2245](https://github.com/sonata-project/SonataMediaBundle/pull/2245)] `src/Resources/views/MediaAdmin/inner_row_media.html.twig` ([@cezar77](https://github.com/cezar77))

## [3.37.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.356...3.37.0) - 2021-11-17
### Added
- [[#2164](https://github.com/sonata-project/SonataMediaBundle/pull/2164)] Added property type hints ([@jordisala1991](https://github.com/jordisala1991))

### Changed
- [[#2157](https://github.com/sonata-project/SonataMediaBundle/pull/2157)] `cdnIsFlushable` property on Media entity is non nullable now. ([@jordisala1991](https://github.com/jordisala1991))
- [[#2137](https://github.com/sonata-project/SonataMediaBundle/pull/2137)] Changed `FeatureMediaBlockService` to not extend `MediaBlockService`. ([@jordisala1991](https://github.com/jordisala1991))
- [[#2137](https://github.com/sonata-project/SonataMediaBundle/pull/2137)] Changed `MediaBlockService` to be final. ([@jordisala1991](https://github.com/jordisala1991))
- [[#2125](https://github.com/sonata-project/SonataMediaBundle/pull/2125)] Block templates are suposed to be overriden by the application to add its specific design. ([@jordisala1991](https://github.com/jordisala1991))

### Fixed
- [[#2156](https://github.com/sonata-project/SonataMediaBundle/pull/2156)] Missing CDN part when generating public URL for FileProvider with reference format ([@nicolas-anaxago](https://github.com/nicolas-anaxago))
- [[#2150](https://github.com/sonata-project/SonataMediaBundle/pull/2150)] Fixed load controllers only when SonataAdminBundle is enabled. ([@jordisala1991](https://github.com/jordisala1991))

### Removed
- [[#2164](https://github.com/sonata-project/SonataMediaBundle/pull/2164)] Removed support for PHP 7.3 ([@jordisala1991](https://github.com/jordisala1991))

## [3.36.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.35.1...3.36.0) - 2021-11-04
### Changed
- [[#2194](https://github.com/sonata-project/SonataMediaBundle/pull/2194)] Final annotation to methods that will be final on 4.x ([@jordisala1991](https://github.com/jordisala1991))

### Deprecated
- [[#2187](https://github.com/sonata-project/SonataMediaBundle/pull/2187)] PantherPortal CDN ([@jordisala1991](https://github.com/jordisala1991))
- [[#2186](https://github.com/sonata-project/SonataMediaBundle/pull/2186)] Using `master` and `slave` for filesystem and cdn configuration, use `primary` and `secondary` instead ([@jordisala1991](https://github.com/jordisala1991))

### Removed
- [[#2197](https://github.com/sonata-project/SonataMediaBundle/pull/2197)] Conflict against "nelmio/api-doc-bundle" >= 4.0 ([@phansys](https://github.com/phansys))
- [[#2197](https://github.com/sonata-project/SonataMediaBundle/pull/2197)] The ReST API endpoints and documentation if "nelmio/api-doc-bundle" >= 4.0 is installed ([@phansys](https://github.com/phansys))

## [3.35.1](https://github.com/sonata-project/SonataMediaBundle/compare/3.35.0...3.35.1) - 2021-10-18
### Fixed
- [[#2175](https://github.com/sonata-project/SonataMediaBundle/pull/2175)] Fixed calling `media_thumbnail` instead of `sonata_thumbnail` ([@jordisala1991](https://github.com/jordisala1991))

## [3.35.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.34.2...3.35.0) - 2021-10-18
### Deprecated
- [[#2158](https://github.com/sonata-project/SonataMediaBundle/pull/2158)] Deprecated passing null value for the media parameter to twig functions ([@jordisala1991](https://github.com/jordisala1991))
- [[#2159](https://github.com/sonata-project/SonataMediaBundle/pull/2159)] Deprecated `MigrateToJsonTypeCommand` ([@jordisala1991](https://github.com/jordisala1991))
- [[#2159](https://github.com/sonata-project/SonataMediaBundle/pull/2159)] Deprecated `ServiceProviderDataTransformer` ([@jordisala1991](https://github.com/jordisala1991))

### Fixed
- [[#2171](https://github.com/sonata-project/SonataMediaBundle/pull/2171)] Docblock for `CategoryManagerInterface::getRootCategory()` to tell it can return `null` ([@phansys](https://github.com/phansys))
- [[#2169](https://github.com/sonata-project/SonataMediaBundle/pull/2169)] Call to an undefined method `Connection::fetchAll()` when using `doctrine/dbal:3.x` ([@phansys](https://github.com/phansys))
- [[#2166](https://github.com/sonata-project/SonataMediaBundle/pull/2166)] Fixed usage of deprecated custom twig tags ([@jordisala1991](https://github.com/jordisala1991))

### Removed
- [[#2169](https://github.com/sonata-project/SonataMediaBundle/pull/2169)] Support for `doctrine/dbal` < 2.11 ([@phansys](https://github.com/phansys))

## [3.34.2](https://github.com/sonata-project/SonataMediaBundle/compare/3.34.1...3.34.2) - 2021-10-08
### Fixed
- [[#2152](https://github.com/sonata-project/SonataMediaBundle/pull/2152)] Fixed safe output of `sonata_media` and `sonata_thumbnail` functions ([@jordisala1991](https://github.com/jordisala1991))
- [[#2152](https://github.com/sonata-project/SonataMediaBundle/pull/2152)] Fixed calling `sonata_media` and `sonata_thumbnail` functions with null media ([@jordisala1991](https://github.com/jordisala1991))

## [3.34.1](https://github.com/sonata-project/SonataMediaBundle/compare/3.34.0...3.34.1) - 2021-10-06
### Fixed
- [[#2149](https://github.com/sonata-project/SonataMediaBundle/pull/2149)] Fixed MediaRuntime definition ([@jordisala1991](https://github.com/jordisala1991))

## [3.34.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.33.1...3.34.0) - 2021-10-05
### Added
- [[#2145](https://github.com/sonata-project/SonataMediaBundle/pull/2145)] Added twig functions to load medias, thumbnails or paths ([@jordisala1991](https://github.com/jordisala1991))
- [[#2138](https://github.com/sonata-project/SonataMediaBundle/pull/2138)] Added new Action to download medias ([@jordisala1991](https://github.com/jordisala1991))
- [[#2131](https://github.com/sonata-project/SonataMediaBundle/pull/2131)] Added optional integration with Symfony messenger to handle async thumbnail generation ([@jordisala1991](https://github.com/jordisala1991))

### Deprecated
- [[#2145](https://github.com/sonata-project/SonataMediaBundle/pull/2145)] Deprecated using twig tags to load media, thumbnails or paths ([@jordisala1991](https://github.com/jordisala1991))
- [[#2138](https://github.com/sonata-project/SonataMediaBundle/pull/2138)] Deprecated download medias through `MediaController` downloadAction. ([@jordisala1991](https://github.com/jordisala1991))
- [[#2139](https://github.com/sonata-project/SonataMediaBundle/pull/2139)] Deprecated SonataNotificationBundle integration. Use Symfony Messenger integration instead. ([@jordisala1991](https://github.com/jordisala1991))
- [[#2118](https://github.com/sonata-project/SonataMediaBundle/pull/2118)] Deprecated ReST API with FOSRest, Nelmio Api Docs and JMS Serializer. ([@jordisala1991](https://github.com/jordisala1991))
- [[#2113](https://github.com/sonata-project/SonataMediaBundle/pull/2113)] Deprecated Pixlr integration. ([@jordisala1991](https://github.com/jordisala1991))
- [[#2105](https://github.com/sonata-project/SonataMediaBundle/pull/2105)] Deprecate controller actions for showing media and galleries. ([@jordisala1991](https://github.com/jordisala1991))
- [[#2105](https://github.com/sonata-project/SonataMediaBundle/pull/2105)] Deprecate breadcrumb classes. ([@jordisala1991](https://github.com/jordisala1991))

### Fixed
- [[#2116](https://github.com/sonata-project/SonataMediaBundle/pull/2116)] Fixed deprecation when downloading media through `downloadAction()` ([@jordisala1991](https://github.com/jordisala1991))

## [3.33.1](https://github.com/sonata-project/SonataMediaBundle/compare/3.33.0...3.33.1) - 2021-09-10
### Fixed
- [[#2101](https://github.com/sonata-project/SonataMediaBundle/pull/2101)] Fixed missing upload after submit of MediaType field ([@jordisala1991](https://github.com/jordisala1991))

## [3.33.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.32.0...3.33.0) - 2021-09-08
### Added
- [[#2096](https://github.com/sonata-project/SonataMediaBundle/pull/2096)] Missing method declarations in interfaces and abstract classes, through `@method` annotation in order to respect BC ([@phansys](https://github.com/phansys))
- [[#2044](https://github.com/sonata-project/SonataMediaBundle/pull/2044)] Method `Sonata\MediaBundle\Filesystem\Replicate::createStream()` ([@phansys](https://github.com/phansys))
- [[#2044](https://github.com/sonata-project/SonataMediaBundle/pull/2044)] Implemented `Gaufrette\Adapter\StreamFactory` in `Sonata\MediaBundle\Filesystem\Replicate` ([@phansys](https://github.com/phansys))
- [[#2042](https://github.com/sonata-project/SonataMediaBundle/pull/2042)] Added name for blocks so they can be recognized on SonataPageBundle ([@jordisala1991](https://github.com/jordisala1991))
- [[#2040](https://github.com/sonata-project/SonataMediaBundle/pull/2040)] Added `ResizableThumbnailInterface` ([@jordisala1991](https://github.com/jordisala1991))
- [[#2036](https://github.com/sonata-project/SonataMediaBundle/pull/2036)] PHPStan configuration and dependencies backported from `master` branch ([@phansys](https://github.com/phansys))

### Changed
- [[#2082](https://github.com/sonata-project/SonataMediaBundle/pull/2082)] Return `null` from `BaseMediaEventSubscriber::getMedia()` if the related media does not implement `MediaInterface` ([@phansys](https://github.com/phansys))

### Deprecated
- [[#2077](https://github.com/sonata-project/SonataMediaBundle/pull/2077)] Unused argument 3 in `Replicate::write()` ([@phansys](https://github.com/phansys))
- [[#2044](https://github.com/sonata-project/SonataMediaBundle/pull/2044)] `Sonata\MediaBundle\Filesystem\Replicate::createFileStream()` in favor of `createStream()` ([@phansys](https://github.com/phansys))
- [[#2072](https://github.com/sonata-project/SonataMediaBundle/pull/2072)] Direct injection of `SessionInterface` on `SessionDownloadStrategy`, we use `RequestStack` to avoid deprecations ([@jordisala1991](https://github.com/jordisala1991))
- [[#2043](https://github.com/sonata-project/SonataMediaBundle/pull/2043)] Deprecated integration with openStack / rackSpace ([@jordisala1991](https://github.com/jordisala1991))
- [[#2021](https://github.com/sonata-project/SonataMediaBundle/pull/2021)] Deprecate `getContext()` from Pool without first having that context ([@jordisala1991](https://github.com/jordisala1991))

### Fixed
- [[#2082](https://github.com/sonata-project/SonataMediaBundle/pull/2082)] Return type at `BaseMediaEventSubscriber::getProvider()` ([@phansys](https://github.com/phansys))
- [[#2088](https://github.com/sonata-project/SonataMediaBundle/pull/2088)] Removed placeholder for translations ([@jordisala1991](https://github.com/jordisala1991))
- [[#2083](https://github.com/sonata-project/SonataMediaBundle/pull/2083)] Call to undefined function `Buzz\Browser::sendRequest()` with "kriswallsmith/buzz" <0.16 ([@phansys](https://github.com/phansys))
- [[#2075](https://github.com/sonata-project/SonataMediaBundle/pull/2075)] Fix display of choices form types inside blocks ([@jordisala1991](https://github.com/jordisala1991))
- [[#2044](https://github.com/sonata-project/SonataMediaBundle/pull/2044)] Missing implementation for `Gaufrette\Adapter\FileFactory` interface at `Sonata\MediaBundle\Filesystem\Replicate` ([@phansys](https://github.com/phansys))
- [[#2044](https://github.com/sonata-project/SonataMediaBundle/pull/2044)] Call to undefined method `Adapter::createFileStream()` ([@phansys](https://github.com/phansys))
- [[#2044](https://github.com/sonata-project/SonataMediaBundle/pull/2044)] Call to undefined method `Adapter::listDirectory()` ([@phansys](https://github.com/phansys))
- [[#2076](https://github.com/sonata-project/SonataMediaBundle/pull/2076)] Fixed adding new Media with Pixlr enabled ([@jordisala1991](https://github.com/jordisala1991))

## [3.32.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.31.2...3.32.0) - 2021-06-13
### Added
- [[#1974](https://github.com/sonata-project/SonataMediaBundle/pull/1974)] Addded support for `nelmio/api-doc-bundle` >= 3.9 ([@jordisala1991](https://github.com/jordisala1991))

### Changed
- [[#1974](https://github.com/sonata-project/SonataMediaBundle/pull/1974)] Updated docs in order to expose how to configure custom serialization paths under jms_serializer configuration node (jms_serializer.metadata.directories). ([@jordisala1991](https://github.com/jordisala1991))
- [[#1969](https://github.com/sonata-project/SonataMediaBundle/pull/1969)] Updated Dutch translations ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#1871](https://github.com/sonata-project/SonataMediaBundle/pull/1871)] `MediaManager` implements `MediaManagerInterface` ([@mrcmorales](https://github.com/mrcmorales))

## [3.31.2](https://github.com/sonata-project/SonataMediaBundle/compare/3.31.1...3.31.2) - 2021-05-18
### Fixed
- [[#1955](https://github.com/sonata-project/SonataMediaBundle/pull/1955)] CDN invalidation from CloudFront when submitting paths that were previously invalidated. ([@phansys](https://github.com/phansys))
- [[#1956](https://github.com/sonata-project/SonataMediaBundle/pull/1956)] Arguments passed to `sprintf()` in `flushPaths()` methods at `CloudFront` and `CloudFrontVersion3`. ([@phansys](https://github.com/phansys))

## [3.31.1](https://github.com/sonata-project/SonataMediaBundle/compare/3.31.0...3.31.1) - 2021-04-19
### Fixed
- [[#1953](https://github.com/sonata-project/SonataMediaBundle/pull/1953)] Remove superfluous deprecation message when translator class is registered that implements both the legacy and new `TranslatorInterface` ([@jorrit](https://github.com/jorrit))

## [3.31.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.30.0...3.31.0) - 2021-03-26
### Added
- [[#1923](https://github.com/sonata-project/SonataMediaBundle/pull/1923)] Added support for `symfony/translation-contracts` ^1.1 || ^2.0 ([@wbloszyk](https://github.com/wbloszyk))
- [[#1943](https://github.com/sonata-project/SonataMediaBundle/pull/1943)] Conflict against nelmio/api-doc-bundle <2.13.5 || >=4.0 ([@phansys](https://github.com/phansys))
- [[#1939](https://github.com/sonata-project/SonataMediaBundle/pull/1939)] Stricter type checks for arguments at `RolesDownloadStrategy::__construct()` ([@phansys](https://github.com/phansys))
- [[#1939](https://github.com/sonata-project/SonataMediaBundle/pull/1939)] Stricter type checks for arguments at `SessionDownloadStrategy::__construct()` ([@phansys](https://github.com/phansys))
- [[#1906](https://github.com/sonata-project/SonataMediaBundle/pull/1906)] Added support for PHP 8.x ([@Yozhef](https://github.com/Yozhef))
- [[#1930](https://github.com/sonata-project/SonataMediaBundle/pull/1930)] Support for "symfony/templating:^5.2" ([@phansys](https://github.com/phansys))
- [[#1930](https://github.com/sonata-project/SonataMediaBundle/pull/1930)] Support for "symfony/http-foundation:^5.2" ([@phansys](https://github.com/phansys))
- [[#1930](https://github.com/sonata-project/SonataMediaBundle/pull/1930)] Support for "symfony/dependency-injection:^5.2" ([@phansys](https://github.com/phansys))
- [[#1930](https://github.com/sonata-project/SonataMediaBundle/pull/1930)] Support for "symfony/config:^5.2" ([@phansys](https://github.com/phansys))
- [[#1930](https://github.com/sonata-project/SonataMediaBundle/pull/1930)] Support for "symfony/routing:^5.2" ([@phansys](https://github.com/phansys))
- [[#1940](https://github.com/sonata-project/SonataMediaBundle/pull/1940)] Added `CropResizer` ([@core23](https://github.com/core23))

### Deprecated
- [[#1778](https://github.com/sonata-project/SonataMediaBundle/pull/1778)] `Replicate::$master` and `Replicate::$slave`, in favor of `Replicate::$primary` and `Replicate::$secondary` ([@dps910](https://github.com/dps910))

### Removed
- [[#1933](https://github.com/sonata-project/SonataMediaBundle/pull/1933)] Removed support for `aws/aws-sdk-php` < 3.0 ([@wbloszyk](https://github.com/wbloszyk))

## [3.30.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.29.0...3.30.0) - 2021-02-15
### Added
- [[#1907](https://github.com/sonata-project/SonataMediaBundle/pull/1907)] Support `sonata-project/datagrid-bundle:^3.0` ([@Yozhef](https://github.com/Yozhef))

## [3.29.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.28.0...3.29.0) - 2020-11-30
### Fixed
- [[#1866](https://github.com/sonata-project/SonataMediaBundle/pull/1866)] Guessing the content type of a file stored on Amazon S3 ([@jorrit](https://github.com/jorrit))
- [[#1863](https://github.com/sonata-project/SonataMediaBundle/pull/1863)] `SonataMediaBundle.it.xliff` fixed "gallery" and "Gallery" translations (case) ([@gremo](https://github.com/gremo))

## [3.28.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.27.0...3.28.0) - 2020-10-30
### Added
- [[#1823](https://github.com/sonata-project/SonataMediaBundle/pull/1823)]
  Added CDN flush status check at `BaseProvider::flushCdn()` in order to
resolve previous flushing processes. ([@phansys](https://github.com/phansys))
- [[#1848](https://github.com/sonata-project/SonataMediaBundle/pull/1848)]
  Missing translations ([@gremo](https://github.com/gremo))
- [[#1834](https://github.com/sonata-project/SonataMediaBundle/pull/1834)]
  Support for symfony/validator ^5.1 ([@jorrit](https://github.com/jorrit))
- [[#1664](https://github.com/sonata-project/SonataMediaBundle/pull/1664)]
  Added support for `psr/http-client` in `BaseVideoProvider`
([@core23](https://github.com/core23))

### Changed
- [[#1848](https://github.com/sonata-project/SonataMediaBundle/pull/1848)]
  `gallery` translation key into `Gallery` ([@gremo](https://github.com/gremo))
- [[#1821](https://github.com/sonata-project/SonataMediaBundle/pull/1821)] When
  using Doctrine ORM or MongoDB the service `sonata.media.admin.media.manager`
is now an alias of `sonata.admin.manager.orm` or
`sonata.admin.manager.doctrine_mongodb` instead of a separate service
implemented by the same class. ([@jorrit](https://github.com/jorrit))

### Deprecated
- [[#1815](https://github.com/sonata-project/SonataMediaBundle/pull/1815)]
  Deprecated method signature for `CloudFront::__construct()`;
([@phansys](https://github.com/phansys))
- [[#1815](https://github.com/sonata-project/SonataMediaBundle/pull/1815)]
  Deprecated methods `CloudFront::setClient()` and
`CloudFront::getStatusList()`; ([@phansys](https://github.com/phansys))
- [[#1815](https://github.com/sonata-project/SonataMediaBundle/pull/1815)]
  Deprecated returning `false` or any value not present in the
`CDNInterface::STATUS_*` constants from `CloudFront::getFlushStatus()`.
([@phansys](https://github.com/phansys))
- [[#1841](https://github.com/sonata-project/SonataMediaBundle/pull/1841)]
  `sonata.media.adapter.filesystem.mogilefs` and
`sonata.media.filesystem.mogilefs` services;
([@phansys](https://github.com/phansys))
- [[#1841](https://github.com/sonata-project/SonataMediaBundle/pull/1841)]
  `sonata_media.filesystem.mogilefs` configuration node.
([@phansys](https://github.com/phansys))
- [[#1664](https://github.com/sonata-project/SonataMediaBundle/pull/1664)]
  Deprecate `Guzzle` and `Buzz` usage in `BaseVideoProvider`
([@core23](https://github.com/core23))
- [[#1814](https://github.com/sonata-project/SonataMediaBundle/pull/1814)]
  Deprecated `sonata_media.filesystem.s3.sdk_version` configuration node.
([@phansys](https://github.com/phansys))

### Fixed
- [[#1815](https://github.com/sonata-project/SonataMediaBundle/pull/1815)]
  Fixed getting values returned by `CloudFrontClient::createInvalidation()` and
`CloudFrontClient::getInvalidation()` methods when using
"aws/aws-sdk-php:^3.0". ([@phansys](https://github.com/phansys))
- [[#1823](https://github.com/sonata-project/SonataMediaBundle/pull/1823)]
  Fixed marking the medium as not CDN synced (`Media::$cdnIsFlushable`) in
`BaseProvider::flushCdn()` and `UpdateCdnStatusCommand`.
([@phansys](https://github.com/phansys))
- [[#1847](https://github.com/sonata-project/SonataMediaBundle/pull/1847)]
  Error on YouTube media creation in `ImageUploadDimensionValidator`
([@tambait](https://github.com/tambait))
- [[#1832](https://github.com/sonata-project/SonataMediaBundle/pull/1832)]
  Fixed validity of `*.orm.xml` mapping files.
([@jorrit](https://github.com/jorrit))
- [[#1821](https://github.com/sonata-project/SonataMediaBundle/pull/1821)]
  Deprecation notice in `Sonata\DoctrineORMAdminBundle\Model\ModelManager` and
`Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager`.
([@jorrit](https://github.com/jorrit))
- [[#1716](https://github.com/sonata-project/SonataMediaBundle/pull/1716)]
  Invalid mongodb xml mapping for BaseGallery.mongodb.xml and
BaseMedia.mongodb.xml files ([@SylvanoTombo](https://github.com/SylvanoTombo))
- [[#1816](https://github.com/sonata-project/SonataMediaBundle/pull/1816)]
  Controller reference of some media API routes.
([@jorrit](https://github.com/jorrit))
- [[#1814](https://github.com/sonata-project/SonataMediaBundle/pull/1814)]
  Fixed calls to deprecated method `AwsClient::factory()` when using
aws/aws-sdk-php:^3.0; ([@phansys](https://github.com/phansys))
- [[#1814](https://github.com/sonata-project/SonataMediaBundle/pull/1814)]
  Fixed passing required arguments to "sonata.media.cdn.cloudfront" service
when using aws/aws-sdk-php:^3.0; ([@phansys](https://github.com/phansys))
- [[#1814](https://github.com/sonata-project/SonataMediaBundle/pull/1814)]
  Fixed respecting `sonata_media.filesystem.s3.region`,
`sonata_media.filesystem.s3.version` and `sonata_media.filesystem.s3.endpoint`
configuration nodes when using aws/aws-sdk-php:^2.0.
([@phansys](https://github.com/phansys))

### Removed
- [[#1836](https://github.com/sonata-project/SonataMediaBundle/pull/1836)] Dev
  dependency on sonata-project/formatter-bundle.
([@jorrit](https://github.com/jorrit))
- [[#1818](https://github.com/sonata-project/SonataMediaBundle/pull/1818)]
  Remove support for `doctrine/mongodb-odm` <2.0
([@franmomu](https://github.com/franmomu))

## [3.27.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.26.0...3.27.0) - 2020-09-02
### Added
- [[#1786](https://github.com/sonata-project/SonataMediaBundle/pull/1786)]
  Added support for symfony/filesystem:^5.1
([@phansys](https://github.com/phansys))
- [[#1786](https://github.com/sonata-project/SonataMediaBundle/pull/1786)]
  Added support for symfony/options-resolver:^5.1
([@phansys](https://github.com/phansys))
- [[#1774](https://github.com/sonata-project/SonataMediaBundle/pull/1774)]
  Added configuration option filesystem.s3.endpoint
([@michalpicpauer](https://github.com/michalpicpauer))

### Fixed
- [[#1800](https://github.com/sonata-project/SonataMediaBundle/pull/1800)]
  Fixed support for string model identifiers at Open API definitions
([@wbloszyk](https://github.com/wbloszyk))

### Removed
- [[#1800](https://github.com/sonata-project/SonataMediaBundle/pull/1800)]
  Removed requirements that were only allowing integers for model identifiers
at Open API definitions ([@wbloszyk](https://github.com/wbloszyk))

## [3.26.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.25.0...3.26.0) - 2020-08-04
### Added
- [[#1761](https://github.com/sonata-project/SonataMediaBundle/pull/1761)]
  Added public alias `Sonata\MediaBundle\Controller\Api\GalleryController` for
`sonata.media.controller.api.gallery` service
([@wbloszyk](https://github.com/wbloszyk))
- [[#1761](https://github.com/sonata-project/SonataMediaBundle/pull/1761)]
  Added public alias `Sonata\MediaBundle\Controller\Api\MediaController` for
`sonata.media.controller.api.media` service
([@wbloszyk](https://github.com/wbloszyk))
- [[#1767](https://github.com/sonata-project/SonataMediaBundle/pull/1767)]
  Added support for `friendsofsymfony/rest-bundle:^3.0`
([@wbloszyk](https://github.com/wbloszyk))
- [[#1771](https://github.com/sonata-project/SonataMediaBundle/pull/1771)]
  Added french translation for `image_too_small` error message
([@romainjanvier](https://github.com/romainjanvier))
- [[#1756](https://github.com/sonata-project/SonataMediaBundle/pull/1756)]
  Added `twig/string-extra` dependency.
([@franmomu](https://github.com/franmomu))
- [[#1757](https://github.com/sonata-project/SonataMediaBundle/pull/1757)] Add
  missing `MediaManagerInterface` to `NoDriverManager`
([@core23](https://github.com/core23))

### Change
- [[#1767](https://github.com/sonata-project/SonataMediaBundle/pull/1767)]
  Support for deprecated `rest` routing type in favor for xml
([@wbloszyk](https://github.com/wbloszyk))

### Changed
- [[#1765](https://github.com/sonata-project/SonataMediaBundle/pull/1765)]
  Change based command class from `ContainerAwareCommand` to `Command` and
inject services instead container ([@wbloszyk](https://github.com/wbloszyk))
- [[#1753](https://github.com/sonata-project/SonataMediaBundle/pull/1753)]
  SonataEasyExtendsBundle is now optional, using SonataDoctrineBundle is
preferred ([@jordisala1991](https://github.com/jordisala1991))
- [[#1756](https://github.com/sonata-project/SonataMediaBundle/pull/1756)]
  Changed use of `truncate` filter with `u` filter.
([@franmomu](https://github.com/franmomu))

### Deprecated
- [[#1753](https://github.com/sonata-project/SonataMediaBundle/pull/1753)]
  Using SonataEasyExtendsBundle to add Doctrine mapping information
([@jordisala1991](https://github.com/jordisala1991))

### Fixed
- [[#1761](https://github.com/sonata-project/SonataMediaBundle/pull/1761)] Fix
  RestFul API - `Class could not be determined for Controller identified` Error
([@wbloszyk](https://github.com/wbloszyk))

### Removed
- [[#1763](https://github.com/sonata-project/SonataMediaBundle/pull/1763)]
  Support for PHP < 7.2 ([@wbloszyk](https://github.com/wbloszyk))
- [[#1763](https://github.com/sonata-project/SonataMediaBundle/pull/1763)]
  Support for Symfony < 4.4 ([@wbloszyk](https://github.com/wbloszyk))

## [3.25.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.24.0...3.25.0) - 2020-06-19
### Added
- Add image size validation
- Added support for `symfony/mime:^5.0`
- adding `maxFileSize` as a parameter in the validation message

### Changed
- Changed the validation message for the validation rule

### Fixed
- Fix missing translation of gallery context
- Removed all calls to container inside the `CleanMediaCommand`
- Fix `bin/console lint:container` command and pass an object of class
  `NoDriverManager` of the expected `GalleryManagerInterface` interface

### Removed
- remove SonataCoreBundle dependencies

## [3.24.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.23.1...3.24.0) - 2020-03-15
### Fixed
- BlockBundle deprecations
- API routes config, made them public for the FOSRest routeloader.

### Changed
- Use Mime Component to guess extension
- Renamed class `Sonata\MediaBundle\Command\DefaultGenerator` into `Sonata\MediaBundle\Command\IdGenerator`
- Renamed class `Sonata\MediaBundle\Command\ODMGenerator` into `Sonata\MediaBundle\Command\UuidGenerator`
- Renamed class `Sonata\MediaBundle\Command\PHPCRGenerator` into `Sonata\MediaBundle\Command\PathGenerator`
- Made width setting non-mandatory but made width or height setting mandatory on the resizer.

### Removed
- support for Symfony < 4.3

## [3.23.1](https://github.com/sonata-project/SonataMediaBundle/compare/3.23.0...3.23.1) - 2020-02-06
### Fixed
- Generating path when using VO representing UUID
- Deprecations about commands not returning an exit code

## [3.23.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.22.0...3.23.0) - 2020-01-07
### Added
- Added support for "knplabs/gaufrette:0.9".

### Fixed
- Fixed passing the default values to the `sonata.media.metadata.amazon` service.

## [3.22.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.21.0...3.22.0) - 2019-12-15
### Added
- Imagine `^1.0` support

### Changed
- `SimpleResizer` and `SquareResizer` resizers now use `ImagineCompatibleResizerTrait`

### Fixed
- Fixed using old string types to create forms
- Add the `addResizer` method call on the thumbnail service only if the method exists.
- Add missing default service argument for `AmazonMetadataBuilder`

### Removed
- Support for Symfony < 3.4
- Support for Symfony >= 4, < 4.2

## [3.21.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.20.1...3.21.0) - 2019-10-21
### Added
- Add missing translation for admin menu

### Changed
- Changed parameter type in `MediaInterface::setCdnFlushIdentifier`
- Add `breadcrumb` as default context for seo blocks
- the alt tag of media picture elements to the media description, falling back
  to the name when no description is present
- Bumped "twig/twig" dependency to "^2.9";
- Changed usages of `{% spaceless %}` tag, which is deprecated as of Twig 1.38
  with `{% apply spaceless %}` filter;
- Changed usages of `{% for .. if .. %}`, which is deprecated as of Twig 2.10
  with `filter` filter'.

### Fixed
- Using deprecated `\Twig_` classes without namespace.
- Respect "field_description.options.route.name" value at
  `list_image.html.twig` instead of using hardcoded "edit".
- Possibility to resolve Twig dependency to versions that don't support arrow
  functions on Twig filters.

### Deprecated
- Extending classes marked as final:
 * `Sonata\MediaBundle\Admin\GalleryAdmin`
 * `Sonata\MediaBundle\Admin\GalleryHasMediaAdmin`
 * `Sonata\MediaBundle\Admin\ODM\MediaAdmin`
 * `Sonata\MediaBundle\Admin\ORM\MediaAdmin`
 * `Sonata\MediaBundle\Admin\PHPCR\GalleryAdmin`
 * `Sonata\MediaBundle\Admin\PHPCR\MediaAdmin`
 * `Sonata\MediaBundle\Block\Breadcrumb\GalleryIndexBreadcrumbBlockService`
 * `Sonata\MediaBundle\Block\Breadcrumb\GalleryViewBreadcrumbBlockService`
 * `Sonata\MediaBundle\Block\Breadcrumb\MediaViewBreadcrumbBlockService`
 * `Sonata\MediaBundle\Block\FeatureMediaBlockService`
 * `Sonata\MediaBundle\Block\GalleryBlockService`
 * `Sonata\MediaBundle\Block\GalleryListBlockService`
 * `Sonata\MediaBundle\Block\MediaBlockService`
 * `Sonata\MediaBundle\CDN\CloudFront`
 * `Sonata\MediaBundle\CDN\Fallback`
 * `Sonata\MediaBundle\CDN\PantherPortal`
 * `Sonata\MediaBundle\CDN\Server`
 * `Sonata\MediaBundle\Command\AddMassMediaCommand`
 * `Sonata\MediaBundle\Command\AddMediaCommand`
 * `Sonata\MediaBundle\Command\CleanMediaCommand`
 * `Sonata\MediaBundle\Command\FixMediaContextCommand`
 * `Sonata\MediaBundle\Command\MigrateToJsonTypeCommand`
 * `Sonata\MediaBundle\Command\RefreshMetadataCommand`
 * `Sonata\MediaBundle\Command\RemoveThumbsCommand`
 * `Sonata\MediaBundle\Command\SyncThumbsCommand`
 * `Sonata\MediaBundle\Command\UpdateCdnStatusCommand`
 * `Sonata\MediaBundle\Consumer\CreateThumbnailConsumer`
 * `Sonata\MediaBundle\Controller\Api\GalleryController`
 * `Sonata\MediaBundle\Controller\Api\MediaController`
 * `Sonata\MediaBundle\Controller\GalleryAdminController`
 * `Sonata\MediaBundle\Controller\GalleryController`
 * `Sonata\MediaBundle\Controller\MediaAdminController`
 * `Sonata\MediaBundle\Controller\MediaController`
 * `Sonata\MediaBundle\DependencyInjection\Compiler\AddProviderCompilerPass`
 * `Sonata\MediaBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass`
 * `Sonata\MediaBundle\DependencyInjection\Compiler\SecurityContextCompilerPass`
 * `Sonata\MediaBundle\DependencyInjection\Compiler\ThumbnailCompilerPass`
 * `Sonata\MediaBundle\DependencyInjection\Configuration`
 * `Sonata\MediaBundle\DependencyInjection\SonataMediaExtension`
 * `Sonata\MediaBundle\Document\GalleryManager`
 * `Sonata\MediaBundle\Document\MediaManager`
 * `Sonata\MediaBundle\Entity\GalleryManager`
 * `Sonata\MediaBundle\Entity\MediaManager`
 * `Sonata\MediaBundle\Extra\ApiMediaFile`
 * `Sonata\MediaBundle\Extra\Pixlr`
 * `Sonata\MediaBundle\Filesystem\Local`
 * `Sonata\MediaBundle\Filesystem\Replicate`
 * `Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer`
 * `Sonata\MediaBundle\Form\DataTransformer\ServiceProviderDataTransformer`
 * `Sonata\MediaBundle\Form\Type\ApiDoctrineMediaType`
 * `Sonata\MediaBundle\Form\Type\ApiGalleryHasMediaType`
 * `Sonata\MediaBundle\Form\Type\ApiGalleryType`
 * `Sonata\MediaBundle\Form\Type\ApiMediaType`
 * `Sonata\MediaBundle\Form\Type\MediaType`
 * `Sonata\MediaBundle\Generator\DefaultGenerator`
 * `Sonata\MediaBundle\Generator\ODMGenerator`
 * `Sonata\MediaBundle\Generator\PHPCRGenerator`
 * `Sonata\MediaBundle\Listener\ODM\MediaEventSubscriber`
 * `Sonata\MediaBundle\Listener\ORM\MediaEventSubscriber`
 * `Sonata\MediaBundle\Listener\PHPCR\MediaEventSubscriber`
 * `Sonata\MediaBundle\Metadata\AmazonMetadataBuilder`
 * `Sonata\MediaBundle\Metadata\NoopMetadataBuilder`
 * `Sonata\MediaBundle\Metadata\ProxyMetadataBuilder`
 * `Sonata\MediaBundle\PHPCR\BaseGalleryHasMediaRepository`
 * `Sonata\MediaBundle\PHPCR\BaseGalleryRepository`
 * `Sonata\MediaBundle\PHPCR\BaseMediaRepository`
 * `Sonata\MediaBundle\PHPCR\GalleryManager`
 * `Sonata\MediaBundle\PHPCR\MediaManager`
 * `Sonata\MediaBundle\Provider\DailyMotionProvider`
 * `Sonata\MediaBundle\Provider\FileProvider`
 * `Sonata\MediaBundle\Provider\ImageProvider`
 * `Sonata\MediaBundle\Provider\Pool`
 * `Sonata\MediaBundle\Provider\VimeoProvider`
 * `Sonata\MediaBundle\Provider\YouTubeProvider`
 * `Sonata\MediaBundle\Resizer\SimpleResizer`
 * `Sonata\MediaBundle\Resizer\SquareResizer`
 * `Sonata\MediaBundle\Security\ForbiddenDownloadStrategy`
 * `Sonata\MediaBundle\Security\PublicDownloadStrategy`
 * `Sonata\MediaBundle\Security\RolesDownloadStrategy`
 * `Sonata\MediaBundle\Security\SessionDownloadStrategy`
 * `Sonata\MediaBundle\Serializer\GallerySerializerHandler`
 * `Sonata\MediaBundle\Serializer\MediaSerializerHandler`
 * `Sonata\MediaBundle\SonataMediaBundle`
 * `Sonata\MediaBundle\Thumbnail\ConsumerThumbnail`
 * `Sonata\MediaBundle\Thumbnail\FormatThumbnail`
 * `Sonata\MediaBundle\Thumbnail\LiipImagineThumbnail`
 * `Sonata\MediaBundle\Twig\Extension\FormatterMediaExtension`
 * `Sonata\MediaBundle\Twig\Extension\MediaExtension`
 * `Sonata\MediaBundle\Twig\GlobalVariables`
 * `Sonata\MediaBundle\Twig\Node\MediaNode`
 * `Sonata\MediaBundle\Twig\Node\PathNode`
 * `Sonata\MediaBundle\Twig\Node\ThumbnailNode`
 * `Sonata\MediaBundle\Twig\TokenParser\MediaTokenParser`
 * `Sonata\MediaBundle\Twig\TokenParser\PathTokenParser`
 * `Sonata\MediaBundle\Twig\TokenParser\ThumbnailTokenParser`
 * `Sonata\MediaBundle\Validator\Constraints\ValidMediaFormat`
 * `Sonata\MediaBundle\Validator\FormatValidator`

## [3.20.1](https://github.com/sonata-project/SonataMediaBundle/compare/3.20.0...3.20.1) - 2019-06-13

### Fixed
- Value of code to throw RuntimeException when url of video throw an exception

### Changed
- Updated `_controller` attribute for routes which were using deprecated syntax.

## [3.20.0](https://github.com/sonata-project/SonataMediaBundle/compare/3.19.1...3.20.0) - 2019-05-16

### Added
- Added compatibility with `jms/serializer-bundle:^3.0`
- In context settings right now you can add the custom array named `resizer_options`.

### Fixed
- Fix error 500 when max post size is exceeded on multi providers context
- Fix file too big message not displayed when
  `$media->getBinaryContent()->getClientSize()` return `null`

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
