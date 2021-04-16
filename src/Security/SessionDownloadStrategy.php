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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 *
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
class SessionDownloadStrategy implements DownloadStrategyInterface
{
    /**
     * @var LegacyTranslatorInterface|TranslatorInterface
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
     * @param int $times
     */
    public function __construct(object $translator, SessionInterface $session, $times)
    {
        if (!$translator instanceof TranslatorInterface) {
            if (!$translator instanceof LegacyTranslatorInterface) {
                throw new \TypeError(
                    sprintf(
                        'Argument 1 passed to "%s()" MUST be an instance of "%s" or "%s", "%s" given.',
                        __METHOD__,
                        LegacyTranslatorInterface::class,
                        TranslatorInterface::class,
                        \get_class($translator)
                    )
                );
            }

            @trigger_error(
                sprintf(
                    'Passing other type than "%s" as argument 1 to "%s()" is deprecated since sonata-project/media-bundle 3.31'
                    .' and will throw a "%s" error in 4.0.',
                    TranslatorInterface::class,
                    __METHOD__,
                    \TypeError::class
                ),
                \E_USER_DEPRECATED
            );
        }

        $this->translator = $translator;
        $this->session = $session;
        $this->times = $times;
    }

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

    public function getDescription()
    {
        // NEXT_MAJOR: remove this if
        if ($this->translator instanceof LegacyTranslatorInterface) {
            return $this->translator->transChoice(
                'description.session_download_strategy',
                $this->times,
                ['%times%' => $this->times],
                'SonataMediaBundle'
            );
        }

        return $this->translator->trans(
            'description.session_download_strategy',
            [
                '%count%' => $this->times,
                '%times%' => $this->times,
            ],
            'SonataMediaBundle'
        );
    }
}
