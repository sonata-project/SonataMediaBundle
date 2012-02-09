Command Line Tools
==================

Media commands
--------------

- Synchronize thumbnail for the provider ``sonata.media.provider.image`` with the ``default`` context :

    php app/console sonata:media:sync-thumbnails sonata.media.provider.image default

- Add a media for the provider ``sonata.media.provider.image`` with the ``default`` context :

    php app/console sonata:media:sync-thumbnails sonata.media.provider.image default path/to/image.jpg

- Add a media for the provider ``sonata.media.provider.youtube`` with the ``default`` context :

    php app/console sonata:media:sync-thumbnails sonata.media.provider.image default http://www.youtube.com/watch?v=BDYAbAtaDzA&feature=g-all-esi&context=asdasdas
    php app/console sonata:media:sync-thumbnails sonata.media.provider.image default BDYAbAtaDzA

    php  app/console sonata:media:add sonata.media.provider.image default path/to/media.png --description="foo bar" --copyright="Sonata Project" --author="Thomas" --enabled=false