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
 * @package    Zend_Form
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZendTest\Form\Element;

use Zend\Form\Element\Captcha as CaptchaElement,
    Zend\Form\Form,
    Zend\Captcha\Dumb as DumbCaptcha,
    Zend\View\View;

/**
 * @category   Zend
 * @package    Zend_Form
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Form
 */
class CaptchaTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->element = new CaptchaElement(
            'foo',
            array(
                'captcha' => 'Dumb',
                'captchaOptions' => array(
                    'sessionClass' => 'ZendTest\Form\Element\TestAsset\SessionContainer',
                ),
            )
        );
    }

    public function getCaptcha()
    {
        $captcha = new DumbCaptcha(array(
            'sessionClass' => 'ZendTest\Form\Element\TestAsset\SessionContainer',
        ));
        return $captcha;
    }

    public function testConstructionShouldRequireCaptchaDetails()
    {
        $this->setExpectedException('Zend\Form\Element\Exception\InvalidArgumentException');
        $this->element = new CaptchaElement('foo');
    }

    public function testShouldAllowSettingCaptcha()
    {
        $captcha = $this->getCaptcha();
        $this->assertNotSame($this->element->getCaptcha(), $captcha);
        $this->element->setCaptcha($captcha);
        $this->assertSame($captcha, $this->element->getCaptcha());
    }

    public function testShouldAllowAddingCaptchaPrefixPath()
    {
        $this->element->addPrefixPath('My\Captcha', 'My/Captcha/', 'captcha');
        $loader = $this->element->getPluginLoader('captcha');
        $paths  = $loader->getPaths('My\Captcha');
        $this->assertTrue(is_array($paths));
    }

    public function testAddingNullPrefixPathShouldAddCaptchaPrefixPath()
    {
        $this->element->addPrefixPath('My', 'My');
        $loader = $this->element->getPluginLoader('captcha');
        $paths  = $loader->getPaths('My\Captcha');
        $this->assertTrue(is_array($paths));
    }

    /**
     * @group ZF-4038
     */
    public function testCaptchaShouldRenderFullyQualifiedElementName()
    {
        $form = new Form();
        $form->addElement($this->element)
             ->setElementsBelongTo('bar');
        $html = $form->render(new View);
        $this->assertContains('name="bar[foo', $html, $html);
        $this->assertContains('id="bar-foo-', $html, $html);
        $this->form = $form;
    }

    /**
     * @group ZF-4038
     */
    public function testCaptchaShouldValidateUsingFullyQualifiedElementName()
    {
        $this->testCaptchaShouldRenderFullyQualifiedElementName();
        $word = $this->element->getCaptcha()->getWord();
        $id   = $this->element->getCaptcha()->getId();
        $data = array(
            'bar' => array(
                'foo' => array(
                    'id'    => $id,
                    'input' => $word,
                )
            )
        );
        $valid = $this->form->isValid($data);
        $this->assertTrue($valid, var_export($this->form->getMessages(), 1));
    }

    /**
     * @group ZF-4822
     */
    public function testDefaultDecoratorsShouldIncludeErrorsDescriptionHtmlTagAndLabel()
    {
        $decorators = $this->element->getDecorators();
        $this->assertTrue(is_array($decorators));
        $this->assertTrue(array_key_exists('Zend\Form\Decorator\Errors', $decorators), 'Missing Errors decorator' . var_export(array_keys($decorators), 1));
        $this->assertTrue(array_key_exists('Zend\Form\Decorator\Description', $decorators), 'Missing Description decorator' . var_export(array_keys($decorators), 1));
        $this->assertTrue(array_key_exists('Zend\Form\Decorator\HtmlTag', $decorators), 'Missing HtmlTag decorator' . var_export(array_keys($decorators), 1));
        $this->assertTrue(array_key_exists('Zend\Form\Decorator\Label', $decorators), 'Missing Label decorator' . var_export(array_keys($decorators), 1));
    }

    /**
     * @group ZF-5855
     */
    public function testHelperDoesNotShowUpInAttribs()
    {
        $this->assertFalse(array_key_exists('helper', $this->element->getAttribs()));
    }

    /**
     * Prove the fluent interface on Zend_Form_Element_Captcha::loadDefaultDecorators
     *
     * @link http://framework.zend.com/issues/browse/ZF-9913
     * @return void
     */
    public function testFluentInterfaceOnLoadDefaultDecorators()
    {
        $this->assertSame($this->element, $this->element->loadDefaultDecorators());
    }
}
