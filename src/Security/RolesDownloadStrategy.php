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
use Symfony\Contracts\Translation\TranslatorInterface;

final class RolesDownloadStrategy implements DownloadStrategyInterface
{
    /**
     * @var string[]
     */
    private array $roles;

    private AuthorizationCheckerInterface $security;

    private TranslatorInterface $translator;

    /**
     * @param string[] $roles
     */
    public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $security, array $roles = [])
    {
        $this->roles = $roles;
        $this->security = $security;
        $this->translator = $translator;
    }

    public function isGranted(MediaInterface $media, Request $request): bool
    {
        try {
            foreach ($this->roles as $role) {
                if ($this->security->isGranted($role)) {
                    return true;
                }
            }
        } catch (AuthenticationCredentialsNotFoundException $e) {
            // The token is not set in an AuthorizationCheckerInterface object
        }

        return false;
    }

    public function getDescription(): string
    {
        return $this->translator->trans('description.roles_download_strategy', ['%roles%' => '<code>'.implode('</code>, <code>', $this->roles).'</code>'], 'SonataMediaBundle');
    }
}
