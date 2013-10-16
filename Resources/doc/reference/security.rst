Security
========

Access to the original media is not possible as the file can be private or/and unsecure for the end user. However
we still need an action to download the file from the server. To solve this issue, the ``SonataMediaBundle`` introduces
a download strategy interface, which can be set per context and authorize the media retrieval.

Built-in security strategy:

* ``sonata.media.security.superadmin_strategy`` : DEFAULT - the user need to have one of the following roles : ``ROLE_SUPER_ADMIN`` or ``ROLE_ADMIN``
* ``sonata.media.security.public_strategy`` : no restriction, files are public
* ``sonata.media.security.forbidden_strategy`` : not possible to retrieve the original file
* ``sonata.media.security.connected_strategy`` : the need to have one of the following roles : ``IS_AUTHENTICATED_FULLY`` or ``IS_AUTHENTICATED_REMEMBERED``

On top of that, there is 3 download modes which can be configured to download the media. The download mode depends on
the HTTP server you used:

* http : DEFAULT - use php to send the file
* X-Sendfile : use the ``X-Sendfile`` flag (Apache + mod_xsendfile : https://tn123.org/mod_xsendfile/)
* X-Accel-Redirect : use the ``X-Accel-Redirect`` flag (Nginx : http://wiki.nginx.org/X-accel)

.. note::

    Some file storage abstractions might not be compatible with some specific server flag,
    if you are not sure always use ``http``.

.. note::

    If you use ``X-Sendfile`` or ``X-Accel-Redirect`` download mode, don't forget to specify that you trust this
    header by adding ``BinaryFileResponse::trustXSendfileTypeHeader();`` in your app controller.


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

    <a href="{{ path('sonata_media_download', {'id': media|sonata_urlsafeid }) }}">Download file</a>

Creating your own Security Download Strategy
--------------------------------------------

The Strategy class must implement the ``DownloadStrategyInterface`` which contains 2 main methods :

* isGranted : return true or false depends on the strategy logic
* getDescription : explains the strategy

Let's create the following strategy : a media can be downloaded only once per session.


.. code-block:: php

    <?php
    namespace Sonata\MediaBundle\Security;

    use Sonata\MediaBundle\Model\MediaInterface;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Security\Core\SecurityContextInterface;
    use Symfony\Component\Translation\TranslatorInterface;
    use Symfony\Component\DependencyInjection\ContainerInterface;

    class SessionDownloadStrategy implements DownloadStrategyInterface
    {
        protected $container;

        protected $translator;

        protected $times;

        protected $sessionKey = 'sonata/media/session/times';

        /**
         * @param \Symfony\Component\Translation\TranslatorInterface $translator
         * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
         * @param int $times
         */
        public function __construct(TranslatorInterface $translator, ContainerInterface $container, $times)
        {
            $this->times    = $times;
            $this->container = $container;
            $this->translator = $translator;
        }

        /**
         * @param \Sonata\MediaBundle\Model\MediaInterface $media
         * @param \Symfony\Component\HttpFoundation\Request $request
         * @return bool
         */
        public function isGranted(MediaInterface $media, Request $request)
        {
            if (!$this->container->has('session')) {
                return false;
            }

            $times = $this->getSession()->get($this->sessionKey, 0);

            if ($times >= $this->times) {
                return false;
            }

            $this->getSession()->set($this->sessionKey, $times++);

            return true;
        }

        /**
         * @return string
         */
        public function getDescription()
        {
            return $this->translator->trans('description.session_download_strategy', array('%times%' => $this->times), 'SonataMediaBundle');
        }

        /**
         * @return \Symfony\Component\HttpFoundation\Session
         */
        private function getSession()
        {
            return $this->container->get('session');
        }
    }

Let's explain a bit :

* ``__construct`` : the constructor get the different dependency. As the session belongs to the request scope, it is not possible to inject the service session, so the Container is injected.
* ``isGranted`` : the method test the number of time the file has been downloaded
* ``getDescription`` : return a translated message to explain what the current strategy does
* ``getSession`` : return the session from the container.


The last important part is declaring the service. Open the ``service.xml`` file and add the following lines.

.. code-block:: xml

        <service id="sonata.media.security.session_strategy" class="Sonata\MediaBundle\Security\SessionDownloadStrategy" >
            <argument type="service" id="translator" />
            <argument type="service" id="service_container" />
            <argument>1</argument>
        </service>

Now the service can be used with a context:

.. code-block:: yaml

    sonata_media:
        db_driver:  doctrine_orm
        contexts:
            contents:
                download:
                    strategy: sonata.media.security.session_strategy

                providers:
                    - sonata.media.provider.file

                formats: []
