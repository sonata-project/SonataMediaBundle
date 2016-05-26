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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;

class RolesDownloadStrategy implements DownloadStrategyInterface
{
    /**
     * @var string[]
     */
    protected $roles;

    /**
     * @var AuthorizationCheckerInterface|SecurityContextInterface
     */
    protected $security;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface                                    $translator
     * @param AuthorizationCheckerInterface|SecurityContextInterface $security
     * @param string[]                                               $roles
     */
    public function __construct(TranslatorInterface $translator, $security, array $roles = array())
    {
        if (!$security instanceof AuthorizationCheckerInterface && !$security instanceof SecurityContextInterface) {
            throw new \InvalidArgumentException('Argument 2 should be an instance of Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface or Symfony\Component\Security\Core\SecurityContextInterface');
        }

        $this->roles = $roles;
        $this->security = $security;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(MediaInterface $media, Request $request)
    {
        try {
            return $this->security->isGranted($this->roles);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            // The token is not set in an AuthorizationCheckerInterface object
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->translator->trans('description.roles_download_strategy', array('%roles%' => '<code>'.implode('</code>, <code>', $this->roles).'</code>'), 'SonataMediaBundle');
    }
}
