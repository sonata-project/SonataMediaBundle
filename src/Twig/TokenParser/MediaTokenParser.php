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

use Sonata\MediaBundle\Twig\Node\MediaNode;
use Twig\Node\Expression\ArrayExpression;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

final class MediaTokenParser extends AbstractTokenParser
{
    /**
     * @var string
     */
    private $extensionName;

    /**
     * @param string $extensionName
     */
    public function __construct($extensionName)
    {
        $this->extensionName = $extensionName;
    }

    /**
     * @psalm-suppress InternalMethod
     *
     * @see https://github.com/twigphp/Twig/issues/3443
     */
    public function parse(Token $token)
    {
        $media = $this->parser->getExpressionParser()->parseExpression();

        $this->parser->getStream()->next();

        $format = $this->parser->getExpressionParser()->parseExpression();

        // attributes
        if ($this->parser->getStream()->test(Token::NAME_TYPE, 'with')) {
            $this->parser->getStream()->next();

            $attributes = $this->parser->getExpressionParser()->parseExpression();
        } else {
            $attributes = new ArrayExpression([], $token->getLine());
        }

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new MediaNode($this->extensionName, $media, $format, $attributes, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'media';
    }
}
