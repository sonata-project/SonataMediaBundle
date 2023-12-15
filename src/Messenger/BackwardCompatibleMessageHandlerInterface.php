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

namespace Sonata\MediaBundle\Messenger;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

// `Symfony\Component\Messenger\Handler\MessageHandlerInterface` was removed in "symfony/messenger" 7.
// @todo: Remove this interface when dropping support for "symfony/messenger" < 7.
/** @psalm-suppress UndefinedClass */
if (interface_exists(MessageHandlerInterface::class)) {
    class_alias(MessageHandlerInterface::class, __NAMESPACE__.'\BackwardCompatibleMessageHandlerInterface');
} else {
    /**
     * @internal
     *
     * @psalm-suppress UnrecognizedStatement
     */
    interface BackwardCompatibleMessageHandlerInterface
    {
    }
}
