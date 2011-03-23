<?php

namespace Sonata\MediaBundle\Twig\Node;

class PathNode extends \Twig_Node
{
    public function __construct(\Twig_Node_Expression $media, \Twig_Node_Expression $format, $lineno, $tag = null)
    {
        parent::__construct(array('media' => $media, 'format' => $format), array(), $lineno, $tag);
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
            ->write("echo \$this->env->getExtension('templating')->getContainer()->get('sonata.media.templating.helper')->path(")
            ->subcompile($this->getNode('media'))
            ->raw(', ')
            ->subcompile($this->getNode('format'))
            ->raw(");\n")
        ;
    }
}