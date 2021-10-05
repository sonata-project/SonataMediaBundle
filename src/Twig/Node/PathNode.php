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

namespace Sonata\MediaBundle\Twig\Node;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;

/**
 * NEXT_MAJOR: Remove this class.
 */
class PathNode extends Node
{
    /**
     * @var string
     */
    protected $extensionName;

    /**
     * @param string $extensionName
     * @param int    $lineno
     * @param string $tag
     */
    public function __construct($extensionName, AbstractExpression $media, AbstractExpression $format, $lineno, $tag = null)
    {
        $this->extensionName = $extensionName;

        parent::__construct(['media' => $media, 'format' => $format], [], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write(sprintf("echo \$this->env->getExtension('%s')->path(", $this->extensionName))
            ->subcompile($this->getNode('media'))
            ->raw(', ')
            ->subcompile($this->getNode('format'))
            ->raw(");\n");
    }
}
