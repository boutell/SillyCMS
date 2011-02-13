<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\Configuration\Builder\NodeBuilder;

use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * AbstractFactory is the base class for all classes inheriting from
 * AbstractAuthenticationListener
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
abstract class AbstractFactory implements SecurityFactoryInterface
{
    protected $options = array(
        'check_path'                     => '/login_check',
        'login_path'                     => '/login',
        'use_forward'                    => false,
        'always_use_default_target_path' => false,
        'default_target_path'            => '/',
        'target_path_parameter'          => '_target_path',
        'use_referer'                    => false,
        'failure_path'                   => null,
        'failure_forward'                => false,
    );

    public function create(ContainerBuilder $container, $id, $config, $userProviderId, $defaultEntryPointId)
    {
        // authentication provider
        $authProviderId = $this->createAuthProvider($container, $id, $config, $userProviderId);
        $container
            ->getDefinition($authProviderId)
            ->addTag('security.authentication_provider')
        ;

        // authentication listener
        $listenerId = $this->createListener($container, $id, $config, $userProviderId);

        // add remember-me aware tag if requested
        if ($this->isRememberMeAware($config)) {
            $container
                ->getDefinition($listenerId)
                ->addTag('security.remember_me_aware', array('id' => $id, 'provider' => $userProviderId))
            ;
        }

        // create entry point if applicable (optional)
        $entryPointId = $this->createEntryPoint($container, $id, $config, $defaultEntryPointId);

        return array($authProviderId, $listenerId, $entryPointId);
    }

    public function addConfiguration(NodeBuilder $node)
    {
        $node
            ->scalarNode('provider')->end()
            ->booleanNode('remember_me')->defaultTrue()->end()
            ->scalarNode('success_handler')->end()
            ->scalarNode('failure_handler')->end()
        ;

        foreach ($this->options as $name => $default) {
            if (is_bool($default)) {
                $node->booleanNode($name)->defaultValue($default);
            } else {
                $node->scalarNode($name)->defaultValue($default);
            }
        }
    }

    public final function addOption($name, $default = null)
    {
        $this->options[$name] = $default;
    }

    /**
     * Subclasses must return the id of a service which implements the
     * AuthenticationProviderInterface.
     *
     * @param ContainerBuilder $container
     * @param string           $id The unique id of the firewall
     * @param array            $options The options array for this listener
     * @param string           $userProviderId The id of the user provider
     *
     * @return string never null, the id of the authentication provider
     */
    abstract protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId);

    /**
     * Subclasses must return the id of the abstract listener template.
     *
     * Listener definitions should inherit from the AbstractAuthenticationListener
     * like this:
     *
     *    <service id="my.listener.id"
     *             class="My\Concrete\Classname"
     *             parent="security.authentication.listener.abstract"
     *             abstract="true" />
     *
     * In the above case, this method would return "my.listener.id".
     *
     * @return string
     */
    abstract protected function getListenerId();

    /**
     * Subclasses may create an entry point of their as they see fit. The
     * default implementation does not change the default entry point.
     *
     * @param ContainerBuilder $container
     * @param string $id
     * @param array $config
     * @param string $defaultEntryPointId
     *
     * @return string the entry point id
     */
    protected function createEntryPoint($container, $id, $config, $defaultEntryPointId)
    {
        return $defaultEntryPointId;
    }

    /**
     * Subclasses may disable remember-me features for the listener, by
     * always returning false from this method.
     *
     * @param array $config
     *
     * @return Boolean Whether a possibly configured RememberMeServices should be set for this listener
     */
    protected function isRememberMeAware($config)
    {
        return $config['remember_me'];
    }

    protected function createListener($container, $id, $config, $userProvider)
    {
        $listenerId = $this->getListenerId();
        $listener = new DefinitionDecorator($listenerId);
        $listener->setArgument(3, $id);
        $listener->setArgument(4, array_intersect_key($config, $this->options));

        // success handler
        if (isset($config['success_handler'])) {
            $listener->setArgument(5, new Reference($config['success_handler']));
        }

        // failure handler
        if (isset($config['failure_handler'])) {
            $listener->setArgument(6, new Reference($config['failure_handler']));
        }

        $listenerId .= '.'.$id;
        $container->setDefinition($listenerId, $listener);

        return $listenerId;
    }
}
