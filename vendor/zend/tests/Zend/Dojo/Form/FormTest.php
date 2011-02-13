<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Dojo
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZendTest\Dojo\Form;

use Zend\Dojo\Form\Form as DojoForm,
    Zend\View\View;

/**
 * Test class for Zend_Dojo_Form and Zend_Dojo_Form_DisplayGroup
 *
 * @category   Zend
 * @package    Zend_Dojo
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Dojo
 * @group      Zend_Dojo_Form
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->form = new DojoForm();
        $this->form->addElement('TextBox', 'foo')
                   ->addDisplayGroup(array('foo'), 'dg')
                   ->setView(new View());
    }

    public function testDojoFormDecoratorPathShouldBeRegisteredByDefault()
    {
        $paths = $this->form->getPluginLoader('decorator')->getPaths('Zend\Dojo\Form\Decorator');
        $this->assertTrue(is_array($paths));
    }

    public function testDojoFormElementPathShouldBeRegisteredByDefault()
    {
        $paths = $this->form->getPluginLoader('element')->getPaths('Zend\Dojo\Form\Element');
        $this->assertTrue(is_array($paths));
    }

    public function testDojoFormElementDecoratorPathShouldBeRegisteredByDefault()
    {
        $paths = $this->form->foo->getPluginLoader('decorator')->getPaths('Zend\Dojo\Form\Decorator');
        $this->assertTrue(is_array($paths));
    }

    public function testDojoFormDisplayGroupDecoratorPathShouldBeRegisteredByDefault()
    {
        $paths = $this->form->dg->getPluginLoader()->getPaths('Zend\Dojo\Form\Decorator');
        $this->assertTrue(is_array($paths));
    }

    public function testDefaultDisplayGroupClassShouldBeDojoDisplayGroupByDefault()
    {
        $this->assertEquals('Zend\Dojo\Form\DisplayGroup', $this->form->getDefaultDisplayGroupClass());
    }

    public function testDefaultDecoratorsShouldIncludeDijitForm()
    {
        $this->assertNotNull($this->form->getDecorator('DijitForm'));
    }

    public function testShouldRegisterDojoViewHelperPath()
    {
        $view   = $this->form->getView();
        $loader = $view->getPluginLoader('helper');
        $paths  = $loader->getPaths('Zend\Dojo\View\Helper');
        $this->assertTrue(is_array($paths));
    }

    public function testDisplayGroupShouldRegisterDojoViewHelperPath()
    {
        $this->form->dg->setView(new View());
        $view   = $this->form->dg->getView();
        $loader = $view->getPluginLoader('helper');
        $paths  = $loader->getPaths('Zend\Dojo\View\Helper');
        $this->assertTrue(is_array($paths));
    }

    /**
     * @group ZF-4748
     */
    public function testHtmlTagDecoratorShouldHaveZendFormDojoClassByDefault()
    {
        $decorator = $this->form->getDecorator('HtmlTag');
        $this->assertEquals('zend_form_dojo', $decorator->getOption('class'));
    }
}
