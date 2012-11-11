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

    php app/console sonata:media:add sonata.media.provider.image default path/to/media.png --description="foo bar" --copyright="Sonata Project" --author="Thomas" --enabled=false

- Update metadata for a set of media

    php app/console sonata:media:refresh-metadata sonata.media.provider.youtube default

- Add multiple media files from csv file

    php app/console sonata:media:add-multiple --file=medias.csv

- add multiple media files from stdin

    cat medias.csv | php app/console sonata:media:add-multiple

The medias.csv file contains the following lines::

    providerName,context,binaryContent
    sonata.media.provider.dailymotion,default,http://www.dailymotion.com/video/xuvt7q_cauet-et-psy-au-trocadero-video-officielle-c-cauet-sur-nrj_music
    sonata.media.provider.dailymotion,default,http://www.dailymotion.com/video/xsbwie_psy-gangnam-style_music
    sonata.media.provider.dailymotion,default,http://www.dailymotion.com/video/xqziut_tutoriel-video-symfony-2-twig_lifestyle
    sonata.media.provider.dailymotion,default,http://www.dailymotion.com/video/x9bgxs_php-tv-4-magento-mysql-symfony-zend_tech
    sonata.media.provider.dailymotion,default,http://www.dailymotion.com/video/xhq4c5_slyblog-tutoriel-video-symfony-1-4-partie-2-2_tech
