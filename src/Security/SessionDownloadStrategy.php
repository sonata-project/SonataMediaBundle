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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
final class SessionDownloadStrategy implements DownloadStrategyInterface
{
    private string $sessionKey = 'sonata/media/session/times';

    public function __construct(
        private TranslatorInterface $translator,
        private RequestStack $requestStack,
        private int $times
    ) {
    }

    public function isGranted(MediaInterface $media, Request $request): bool
    {
        $session = $this->requestStack->getSession();

        $times = $session->get($this->sessionKey, 0);

        if ($times >= $this->times) {
            return false;
        }

        ++$times;

        $session->set($this->sessionKey, $times);

        return true;
    }

    public function getDescription(): string
    {
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
