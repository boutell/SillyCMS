<?php

namespace Symfony\Bundle\TwigBundle\Node;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represents a render node.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class RenderNode extends \Twig_Node
{
    public function __construct(\Twig_Node_Expression $expr, \Twig_Node_Expression $attributes, \Twig_Node_Expression $options, $lineno, $tag = null)
    {
        parent::__construct(array('expr' => $expr, 'attributes' => $attributes, 'options' => $options), array(), $lineno, $tag);
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
            ->write("echo \$this->env->getExtension('templating')->renderAction(")
            ->subcompile($this->getNode('expr'))
            ->raw(', ')
            ->subcompile($this->getNode('attributes'))
            ->raw(', ')
            ->subcompile($this->getNode('options'))
            ->raw(");\n")
        ;
    }
}
