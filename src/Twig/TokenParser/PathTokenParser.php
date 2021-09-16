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

namespace Sonata\MediaBundle\Twig\TokenParser;

use Sonata\MediaBundle\Twig\Node\PathNode;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * @internal
 */
final class PathTokenParser extends AbstractTokenParser
{
    /**
     * @var string
     */
    private $extensionName;

    public function __construct(string $extensionName)
    {
        $this->extensionName = $extensionName;
    }

    /**
     * @psalm-suppress InternalMethod
     *
     * @see https://github.com/twigphp/Twig/issues/3443
     */
    public function parse(Token $token): Node
    {
        $media = $this->parser->getExpressionParser()->parseExpression();

        $this->parser->getStream()->next();

        $format = $this->parser->getExpressionParser()->parseExpression();

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new PathNode($this->extensionName, $media, $format, $token->getLine(), $this->getTag());
    }

    public function getTag(): string
    {
        return 'path';
    }
}
