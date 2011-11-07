Security
========

Access to the original media is not possible as the file can be private or/and unsecure for the end user. However
we still need an action to download the file from the server. To solve this issue, the ``SonataMediaBundle`` introduces
a download strategy interface, which can be set per context and authorize the media retrieval.

Built-in security strategy:

* sonata.media.security.superadmin_strategy : DEFAULT - the user need to have one of the following roles : ROLE_SUPER_ADMIN or ROLE_ADMIN
* sonata.media.security.public_strategy : no restriction, files are public
* sonata.media.security.forbidden_strategy : not possible to retrieve the original file
* sonata.media.security.connected_strategy : the need to have one of the following roles : IS_AUTHENTICATED_FULLY or IS_AUTHENTICATED_REMEMBERED

On top of that, there is 3 download modes which can be configured to download the media. The download mode depends on
the HTTP server you used:

* http : DEFAULT - use php to send the file
* X-Sendfile : use the X-Sendfile flag (Apache + mod_xsendfile : https://tn123.org/mod_xsendfile/)
* X-Accel-Redirect : use the X-Accel-Redirect flag (Nginx : http://wiki.nginx.org/X-accel)

.. note::

    Some file storage abstractions might not be compatible with some specific server flag,
    if you are not sure always use ``http``.


Configuration Example
---------------------

For the context ``default`` the user need to be a Super Admin to retrieve the file in ``http`` mode.

.. code-block:: yaml

    sonata_media:
        db_driver: doctrine_orm
        contexts:
            default:  # the default context is mandatory
                download:
                    strategy: sonata.media.security.superadmin_strategy
                    mode: http
                providers:
                    - sonata.media.provider.dailymotion
                    - sonata.media.provider.youtube
                    - sonata.media.provider.image
                    - sonata.media.provider.file

The related download route name is ``sonata_media_download``.

.. code-block:: jinja

    <a href="{{ path('sonata_media_download', {'id': media.id}) }}">Download file</a>

Creating your own Security Download Strategy
--------------------------------------------

The Strategy class must implement the ``