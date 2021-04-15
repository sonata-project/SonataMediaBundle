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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class RolesDownloadStrategy implements DownloadStrategyInterface
{
    /**
     * @var string[]
     */
    protected $roles;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $security;

    /**
     * @var LegacyTranslatorInterface|TranslatorInterface
     */
    protected $translator;

    /**
     * @param string[] $roles
     */
    public function __construct(object $translator, AuthorizationCheckerInterface $security, array $roles = [])
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

        $this->roles = $roles;
        $this->security = $security;
        $this->translator = $translator;
    }

    public function isGranted(MediaInterface $media, Request $request)
    {
        try {
            return $this->security->isGranted($this->roles);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            // The token is not set in an AuthorizationCheckerInterface object
            return false;
        }
    }

    public function getDescription()
    {
        return $this->translator->trans('description.roles_download_strategy', ['%roles%' => '<code>'.implode('</code>, <code>', $this->roles).'</code>'], 'SonataMediaBundle');
    }
}
