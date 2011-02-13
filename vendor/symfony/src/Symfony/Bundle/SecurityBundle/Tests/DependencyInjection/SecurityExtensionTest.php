<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\SecurityBundle\Tests\DependencyInjection;

use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class SecurityExtensionTest extends \PHPUnit_Framework_TestCase
{
    abstract protected function loadFromFile(ContainerBuilder $container, $file);

    public function testRolesHierarchy()
    {
        $container = $this->getContainer('container1');
        $this->assertEquals(array(
            'ROLE_ADMIN'       => array('ROLE_USER'),
            'ROLE_SUPER_ADMIN' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'),
            'ROLE_REMOTE'      => array('ROLE_USER', 'ROLE_ADMIN'),
        ), $container->getParameter('security.role_hierarchy.roles'));
    }

    public function testUserProviders()
    {
        $container = $this->getContainer('container1');

        $providers = array_values(array_filter($container->getServiceIds(), function ($key) { return 0 === strpos($key, 'security.user.provider.'); }));

        $expectedProviders = array(
            'security.user.provider.default',
            'security.user.provider.default_foo',
            'security.user.provider.digest',
            'security.user.provider.digest_foo',
            'security.user.provider.basic',
            'security.user.provider.basic_foo',
            'security.user.provider.basic_bar',
            'security.user.provider.doctrine',
            'security.user.provider.service',
        );

        $this->assertEquals(array(), array_diff($expectedProviders, $providers));
        $this->assertEquals(array(), array_diff($providers, $expectedProviders));
    }

    public function testFirewalls()
    {
        $container = $this->getContainer('container1');

        $arguments = $container->getDefinition('security.firewall.map')->getArguments();
        $listeners = array();
        foreach (array_keys($arguments[1]) as $contextId) {
            $contextDef = $container->getDefinition($contextId);
            $arguments = $contextDef->getArguments();
            $listeners[] = array_map(function ($ref) { return (string) $ref; }, $arguments['index_0']);
        }

        $this->assertEquals(array(
            array(),
            array(
                'security.channel_listener',
                'security.logout_listener.secure',
                'security.authentication.listener.x509.secure',
                'security.authentication.listener.form.secure',
                'security.authentication.listener.basic.secure',
                'security.authentication.listener.digest.secure',
                'security.authentication.listener.anonymous',
                'security.access_listener',
                'security.authentication.switchuser_listener.secure',
            ),
        ), $listeners);
    }

    public function testAccess()
    {
        $container = $this->getContainer('container1');

        $rules = array();
        foreach ($container->getDefinition('security.access_map')->getMethodCalls() as $call) {
            if ($call[0] == 'add') {
                $rules[] = array((string) $call[1][0], $call[1][1], $call[1][2]);
            }
        }

        $matcherIds = array();
        foreach ($rules as $rule) {
            list($matcherId, $roles, $channel) = $rule;

            $this->assertFalse(isset($matcherIds[$matcherId]));
            $matcherIds[$matcherId] = true;

            $i = count($matcherIds);
            if (1 === $i) {
                $this->assertEquals(array('ROLE_USER'), $roles);
                $this->assertEquals('https', $channel);
            } else if (2 === $i) {
                $this->assertEquals(array('IS_AUTHENTICATED_ANONYMOUSLY'), $roles);
                $this->assertNull($channel);
            }
        }
    }

    public function testMerge()
    {
        $container = $this->getContainer('merge');

        $this->assertEquals(array(
            'FOO' => array('MOO'),
            'ADMIN' => array('USER'),
        ), $container->getParameter('security.role_hierarchy.roles'));
    }

    protected function getContainer($file)
    {
        $container = new ContainerBuilder();
        $security = new SecurityExtension();
        $container->registerExtension($security);
        $this->loadFromFile($container, $file);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}
