<?php

namespace Symfony\Bundle\FrameworkBundle\Templating;

use Symfony\Component\Templating\DelegatingEngine as BaseDelegatingEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * DelegatingEngine selects an engine for a given template.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class DelegatingEngine extends BaseDelegatingEngine implements EngineInterface
{
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container The DI container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->engines = array();
    }

    public function setEngineIds($ids)
    {
        $this->engines = $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($name)
    {
        foreach ($this->engines as $i => $engine) {
            if (is_string($engine)) {
                $engine = $this->engines[$i] = $this->container->get($engine);
            }

            if ($engine->supports($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEngine($name)
    {
        foreach ($this->engines as $i => $engine) {
            if (is_string($engine)) {
                $engine = $this->engines[$i] = $this->container->get($engine);
            }

            if ($engine->supports($name)) {
                return $engine;
            }
        }

        throw new \RuntimeException(sprintf('No engine is able to work with the "%s" template.', $name));
    }

    /**
     * Renders a view and returns a Response.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response   A Response instance
     *
     * @return Response A Response instance
     */
    public function renderResponse($view, array $parameters = array(), Response $response = null)
    {
        if (null === $response) {
            $response = $this->container->get('response');
        }

        $response->setContent($this->render($view, $parameters));

        return $response;
    }
}
