<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Security;

use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SessionDownloadStrategy implements DownloadStrategyInterface
{
    protected $container;

    protected $translator;

    protected $times;

    protected $sessionKey = 'sonata/media/session/times';

    /**
     * @param \Symfony\Component\Translation\TranslatorInterface        $translator
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param int                                                       $times
     */
    public function __construct(TranslatorInterface $translator, ContainerInterface $container, $times)
    {
        $this->times      = $times;
        $this->container  = $container;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
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

        $times++;

        $this->getSession()->set($this->sessionKey, $times);

        return true;
    }

    /**
     * {@inheritdoc}
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
