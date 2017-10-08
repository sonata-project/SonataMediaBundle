<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Twig\Node;

class PathNode extends \Twig_Node
{
    /**
     * @var string
     */
    protected $extensionName;

    /**
     * @param string                $extensionName
     * @param \Twig_Node_Expression $media
     * @param \Twig_Node_Expression $format
     * @param int                   $lineno
     * @param string                $tag
     */
    public function __construct($extensionName, \Twig_Node_Expression $media, \Twig_Node_Expression $format, $lineno, $tag = null)
    {
        $this->extensionName = $extensionName;

        parent::__construct(['media' => $media, 'format' => $format], [], $lineno, $tag);
    }

    /**
     * {@inheritdoc}
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write(sprintf("echo \$this->env->getExtension('%s')->path(", $this->extensionName))
            ->subcompile($this->getNode('media'))
            ->raw(', ')
            ->subcompile($this->getNode('format'))
            ->raw(");\n")
        ;
    }
}
