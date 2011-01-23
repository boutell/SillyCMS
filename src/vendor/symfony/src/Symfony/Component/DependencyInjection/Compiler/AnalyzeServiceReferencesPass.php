<?php

namespace Symfony\Component\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Run this pass before passes that need to know more about the relation of
 * your services.
 *
 * This class will populate the ServiceReferenceGraph with information. You can
 * retrieve the graph in other passes from the compiler.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class AnalyzeServiceReferencesPass implements RepeatablePassInterface, CompilerAwareInterface
{
    protected $graph;
    protected $container;
    protected $currentId;
    protected $currentDefinition;
    protected $repeatedPass;

    public function setRepeatedPass(RepeatedPass $repeatedPass) {
        $this->repeatedPass = $repeatedPass;
    }

    public function setCompiler(Compiler $compiler)
    {
        $this->graph = $compiler->getServiceReferenceGraph();
    }

    public function process(ContainerBuilder $container)
    {
        $this->container = $container;

        if (null === $this->graph) {
            $this->graph = $this->repeatedPass->getCompiler()->getServiceReferenceGraph();
        }
        $this->graph->clear();

        foreach ($container->getDefinitions() as $id => $definition) {
            $this->currentId = $id;
            $this->currentDefinition = $definition;
            $this->processArguments($definition->getArguments());
            $this->processArguments($definition->getMethodCalls());
        }

        foreach ($container->getAliases() as $id => $alias) {
            $this->graph->connect($id, $alias, (string) $alias, $this->getDefinition((string) $alias), null);
        }
    }

    protected function processArguments(array $arguments)
    {
        foreach ($arguments as $k => $argument) {
            if (is_array($argument)) {
                $this->processArguments($argument);
            } else if ($argument instanceof Reference) {
                $this->graph->connect(
                    $this->currentId,
                    $this->currentDefinition,
                    (string) $argument,
                    $this->getDefinition((string) $argument),
                    $argument
                );
            } else if ($argument instanceof Definition) {
                $this->processArguments($argument->getArguments());
                $this->processArguments($argument->getMethodCalls());
            }
        }
    }

    protected function getDefinition($id)
    {
        while ($this->container->hasAlias($id)) {
            $id = (string) $this->container->getAlias($id);
        }

        if (!$this->container->hasDefinition($id)) {
            return null;
        }

        return $this->container->getDefinition($id);
    }
}