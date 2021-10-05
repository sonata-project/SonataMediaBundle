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

use Sonata\MediaBundle\Twig\Node\ThumbnailNode;
use Twig\Node\Expression\ArrayExpression;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * NEXT_MAJOR: Remove this class.
 */
class ThumbnailTokenParser extends AbstractTokenParser
{
    /**
     * @var string
     */
    protected $extensionName;

    /**
     * @param string $extensionName
     */
    public function __construct($extensionName)
    {
        $this->extensionName = $extensionName;
    }

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

        return new ThumbnailNode($this->extensionName, $media, $format, $attributes, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'thumbnail';
    }
}
