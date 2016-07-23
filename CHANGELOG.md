# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

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
