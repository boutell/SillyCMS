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
 * @package    Zend_View
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\View\Helper;
use Zend\Controller;
use Zend\View;


/**
 * Test class for Zend_View_Helper_Partial.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_View
 * @group      Zend_View_Helper
 */
class PartialTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Zend_View_Helper_Partial
     */
    public $helper;

    /**
     * @var string
     */
    public $basePath;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->basePath = __DIR__ . '/_files/modules';
        $this->helper = new \Zend\View\Helper\Partial();
        Controller\Front::getInstance()->resetInstance();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->helper);
    }

    /**
     * @return void
     */
    public function testPartialRendersScript()
    {
        $view = new View\View(array(
            'scriptPath' => $this->basePath . '/application/views/scripts'
        ));
        $this->helper->setView($view);
        $return = $this->helper->direct('partialOne.phtml');
        $this->assertContains('This is the first test partial', $return);
    }

    /**
     * @return void
     */
    public function testPartialRendersScriptWithVars()
    {
        $view = new View\View(array(
            'scriptPath' => $this->basePath . '/application/views/scripts'
        ));
        $view->message = 'This should never be read';
        $this->helper->setView($view);
        $return = $this->helper->direct('partialThree.phtml', array('message' => 'This message should be read'));
        $this->assertNotContains($view->message, $return);
        $this->assertContains('This message should be read', $return, $return);
    }

    /**
     * @return void
     */
    public function testPartialRendersScriptInDifferentModuleWhenRequested()
    {
        Controller\Front::getInstance()->addModuleDirectory($this->basePath);
        $view = new View\View(array(
            'scriptPath' => $this->basePath . '/application/views/scripts'
        ));
        $this->helper->setView($view);
        $return = $this->helper->direct('partialTwo.phtml', 'foo');
        $this->assertContains('This is the second partial', $return, $return);
    }

    /**
     * @return void
     */
    public function testPartialThrowsExceptionWithInvalidModule()
    {
        Controller\Front::getInstance()->addModuleDirectory($this->basePath);
        $view = new View\View(array(
            'scriptPath' => $this->basePath . '/application/views/scripts'
        ));
        $this->helper->setView($view);

        try {
            $return = $this->helper->direct('partialTwo.phtml', 'barbazbat');
            $this->fail('Partial should throw exception if module does not exist');
        } catch (\Exception $e) {
        }
    }

    /**
     * @return void
     */
    public function testSetViewSetsViewProperty()
    {
        $view = new View\View();
        $this->helper->setView($view);
        $this->assertSame($view, $this->helper->view);
    }

    /**
     * @return void
     */
    public function testCloneViewReturnsDifferentViewInstance()
    {
        $view = new View\View();
        $this->helper->setView($view);
        $clone = $this->helper->cloneView();
        $this->assertNotSame($view, $clone);
        $this->assertTrue($clone instanceof View\View);
    }

    /**
     * @return void
     */
    public function testCloneViewClearsViewVariables()
    {
        $view = new View\View();
        $view->foo = 'bar';
        $this->helper->setView($view);

        $clone = $this->helper->cloneView();
        $clonedVars = $clone->getVars();

        $this->assertTrue(empty($clonedVars));
        $this->assertNull($clone->foo);
    }

    public function testObjectModelWithPublicPropertiesSetsViewVariables()
    {
        $model = new \stdClass();
        $model->foo = 'bar';
        $model->bar = 'baz';

        $view = new View\View(array(
            'scriptPath' => $this->basePath . '/application/views/scripts'
        ));
        $this->helper->setView($view);
        $return = $this->helper->direct('partialVars.phtml', $model);

        foreach (get_object_vars($model) as $key => $value) {
            $string = sprintf('%s: %s', $key, $value);
            $this->assertContains($string, $return);
        }
    }

    public function testObjectModelWithToArraySetsViewVariables()
    {
        $model = new Aggregate();

        $view = new View\View(array(
            'scriptPath' => $this->basePath . '/application/views/scripts'
        ));
        $this->helper->setView($view);
        $return = $this->helper->direct('partialVars.phtml', $model);

        foreach ($model->toArray() as $key => $value) {
            $string = sprintf('%s: %s', $key, $value);
            $this->assertContains($string, $return);
        }
    }

    public function testObjectModelSetInObjectKeyWhenKeyPresent()
    {
        $this->helper->setObjectKey('foo');
        $model = new \stdClass();
        $model->footest = 'bar';
        $model->bartest = 'baz';

        $view = new View\View(array(
            'scriptPath' => $this->basePath . '/application/views/scripts'
        ));
        $this->helper->setView($view);
        $return = $this->helper->direct('partialObj.phtml', $model);

        $this->assertNotContains('No object model passed', $return);

        foreach (get_object_vars($model) as $key => $value) {
            $string = sprintf('%s: %s', $key, $value);
            $this->assertContains($string, $return, "Checking for '$return' containing '$string'");
        }
    }

    public function testPassingNoArgsReturnsHelperInstance()
    {
        $test = $this->helper->direct();
        $this->assertSame($this->helper, $test);
    }

    public function testObjectKeyIsNullByDefault()
    {
        $this->assertNull($this->helper->getObjectKey());
    }

    public function testCanSetObjectKey()
    {
        $this->testObjectKeyIsNullByDefault();
        $this->helper->setObjectKey('foo');
        $this->assertEquals('foo', $this->helper->getObjectKey());
    }

    public function testCanSetObjectKeyToNullValue()
    {
        $this->testCanSetObjectKey();
        $this->helper->setObjectKey(null);
        $this->assertNull($this->helper->getObjectKey());
    }

    public function testSetObjectKeyImplementsFluentInterface()
    {
        $test = $this->helper->setObjectKey('foo');
        $this->assertSame($this->helper, $test);
    }
}

class Aggregate
{
    public $vars = array(
        'foo' => 'bar',
        'bar' => 'baz'
    );

    public function toArray()
    {
        return $this->vars;
    }
}
