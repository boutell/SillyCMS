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

namespace ZendTest\Dojo\Form\Element;

use Zend\Dojo\Form\Element\FilteringSelect as FilteringSelectElement,
    Zend\Dojo\View\Helper\Dojo as DojoHelper,
    Zend\Registry,
    Zend\Validator\InArray as InArrayValidator,
    Zend\View\View;

/**
 * Test class for Zend_Dojo_Form_Element_FilteringSelect.
 *
 * @category   Zend
 * @package    Zend_Dojo
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Dojo
 * @group      Zend_Dojo_Form
 */
class FilteringSelectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        Registry::_unsetInstance();
        DojoHelper::setUseDeclarative();

        $this->view    = $this->getView();
        $this->element = $this->getElement();
        $this->element->setView($this->view);
    }

    public function getView()
    {
        $view = new View();
        \Zend\Dojo\Dojo::enableView($view);
        return $view;
    }

    public function getElement()
    {
        $element = new FilteringSelectElement(
            'foo',
            array(
                'value' => 'some text',
                'label' => 'FilteringSelect',
                'class' => 'someclass',
                'style' => 'width: 100px;',
            )
        );
        return $element;
    }

    public function testShouldRenderFilteringSelectDijit()
    {
        $html = $this->element->render();
        $this->assertContains('dojoType="dijit.form.FilteringSelect"', $html);
    }

    /**
     * @group ZF-3286
     */
    public function testShouldRegisterInArrayValidatorWhenNoStoreProvided()
    {
        $options = array(
            'foo' => 'Foo Value',
            'bar' => 'Bar Value',
            'baz' => 'Baz Value',
        );
        $this->element->setMultiOptions($options);
        $this->assertFalse($this->element->getValidator('InArray'));
        $this->element->isValid('test');
        $validator = $this->element->getValidator('InArray');
        $this->assertTrue($validator instanceof InArrayValidator);
    }

    /**
     * @group ZF-3286
     */
    public function testShouldNotRegisterInArrayValidatorWhenStoreProvided()
    {
        $options = array(
            'foo' => 'Foo Value',
            'bar' => 'Bar Value',
            'baz' => 'Baz Value',
        );
        $this->element->setMultiOptions($options);
        $this->element->setStoreId('fooStore')
                      ->setStoreType('dojo.data.ItemFileReadStore')
                      ->setStoreParams(array('url' => '/foo'));
        $this->assertFalse($this->element->getValidator('InArray'));
        $this->element->isValid('test');
        $this->assertFalse($this->element->getValidator('InArray'));
    }
}
