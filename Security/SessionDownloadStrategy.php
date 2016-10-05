<?php

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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
class SessionDownloadStrategy implements DownloadStrategyInterface
{
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
     * @param TranslatorInterface $translator
     * @param SessionInterface    $session
     * @param int                 $times
     */
    public function __construct(TranslatorInterface $translator, SessionInterface $session, $times)
    {
        $this->times = $times;
        $this->session = $session;
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
            array('%times%' => $this->times),
            'SonataMediaBundle'
        );
    }
}
