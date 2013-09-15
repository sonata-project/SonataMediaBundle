CHANGELOG
=========

A [BC BREAK] means the update will break the project for many reasons:

* new mandatory configuration
* new dependencies
* class refactoring

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