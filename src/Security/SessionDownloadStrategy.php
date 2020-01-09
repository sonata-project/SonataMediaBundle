<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Security;

use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 *
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
class SessionDownloadStrategy implements DownloadStrategyInterface
{
    /**
     * @var ContainerInterface
     *
     * @deprecated since sonata-project/media-bundle 3.1, will be removed in 4.0.
     * NEXT_MAJOR : remove this property
     */
    protected $container;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var int
     */
    protected $times;

    /**
     * @var string
     */
    protected $sessionKey = 'sonata/media/session/times';

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @param ContainerInterface|SessionInterface $session
     * @param int                                 $times
     */
    public function __construct(TranslatorInterface $translator, $session, $times)
    {
        // NEXT_MAJOR : remove this block and set session from parameter.
        if ($session instanceof ContainerInterface) {
            @trigger_error(
                'Using an instance of Symfony\Component\DependencyInjection\ContainerInterface is deprecated since
                version 3.1 and will be removed in 4.0.
                Use Symfony\Component\HttpFoundation\Session\SessionInterface instead.',
                E_USER_DEPRECATED
            );

            $this->session = $session->get('session');
        } elseif ($session instanceof SessionInterface) {
            $this->session = $session;
        } else {
            throw new \InvalidArgumentException(
                '$session should be an instance of Symfony\Component\HttpFoundation\Session\SessionInterface'
            );
        }

        $this->times = $times;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(MediaInterface $media, Request $request)
    {
        $times = $this->session->get($this->sessionKey, 0);

        if ($times >= $this->times) {
            return false;
        }

        ++$times;

        $this->session->set($this->sessionKey, $times);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->translator->transChoice(
            'description.session_download_strategy',
            $this->times,
            ['%times%' => $this->times],
            'SonataMediaBundle'
        );
    }
}
