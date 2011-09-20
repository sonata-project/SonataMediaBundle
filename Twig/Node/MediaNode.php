<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Twig\Node;

class MediaNode extends \Twig_Node
{
    protected $extensionName;

    public function __construct($extensionName, \Twig_Node_Expression $media, \Twig_Node_Expression $format, \Twig_Node_Expression $attributes, $lineno, $tag = null)
    {
        $this->extensionName = $extensionName;

        parent::__construct(array('media' => $media, 'format' => $format,'attributes' => $attributes), array(), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param \Twig_Compiler A Twig_Compiler instance
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write(sprintf("echo \$this->env->getExtension('%s')->media(", $this->extensionName))
            ->subcompile($this->getNode('media'))
            ->raw(', ')
            ->subcompile($this->getNode('format'))
            ->raw(', ')
            ->subcompile($this->getNode('attributes'))
            ->raw(");\n")
        ;
    }
}