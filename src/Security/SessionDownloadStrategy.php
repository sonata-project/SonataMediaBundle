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
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
     * @var ContainerInterface
     *
     * @deprecated since sonata-project/media-bundle 3.1, will be removed in 4.0.
     * NEXT_MAJOR : remove this property
     */
    protected $container;

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
     *
     * @deprecated since sonata-project/media-bundle 3.x, will be removed in 4.0.
     * NEXT_MAJOR : remove this property
     */
    private $session;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param ContainerInterface|SessionInterface|RequestStack $sessionOrContainerOrRequestStack
     * @param int                                              $times
     */
    public function __construct(object $translator, object $sessionOrContainerOrRequestStack, $times)
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

        // NEXT_MAJOR: Remove these checks and declare `RequestStack` for argument 2.
        if ($sessionOrContainerOrRequestStack instanceof RequestStack) {
            $this->requestStack = $sessionOrContainerOrRequestStack;
        } elseif ($sessionOrContainerOrRequestStack instanceof ContainerInterface) {
            @trigger_error(sprintf(
                'Passing other type than "%s" as argument 2 to "%s()" is deprecated since sonata-project/media-bundle 3.x'
                .' and will throw a "\TypeError" error in 4.0.',
                RequestStack::class,
                __METHOD__
            ), \E_USER_DEPRECATED);

            $this->session = $sessionOrContainerOrRequestStack->get('session');
        } elseif ($sessionOrContainerOrRequestStack instanceof SessionInterface) {
            @trigger_error(sprintf(
                'Passing other type than "%s" as argument 2 to "%s()" is deprecated since sonata-project/media-bundle 3.x'
                .' and will throw a "\TypeError" error in 4.0.',
                RequestStack::class,
                __METHOD__
            ), \E_USER_DEPRECATED);

            $this->session = $sessionOrContainerOrRequestStack;
        } else {
            throw new \TypeError(sprintf(
                'Argument 2 passed to "%s()" MUST be an instance of "%s" or "%s" or "%s", "%s" given.',
                __METHOD__,
                RequestStack::class,
                SessionInterface::class,
                ContainerInterface::class,
                \get_class($sessionOrContainerOrRequestStack)
            ));
        }

        $this->translator = $translator;
        $this->times = $times;
    }

    public function isGranted(MediaInterface $media, Request $request)
    {
        $session = $this->getSession();

        $times = $session->get($this->sessionKey, 0);

        if ($times >= $this->times) {
            return false;
        }

        ++$times;

        $session->set($this->sessionKey, $times);

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

    private function getSession(): SessionInterface
    {
        // NEXT_MAJOR: Remove this condition, we will always have RequestStack
        if (null !== $this->requestStack) {
            // TODO: Remove this condition when Symfony < 5.3 support is removed
            if (method_exists($this->requestStack, 'getSession')) {
                return $this->requestStack->getSession();
            }

            $currentRequest = $this->requestStack->getCurrentRequest();

            if (null === $currentRequest) {
                throw new SessionNotFoundException();
            }

            return $currentRequest->getSession();
        }

        return $this->session;
    }
}
