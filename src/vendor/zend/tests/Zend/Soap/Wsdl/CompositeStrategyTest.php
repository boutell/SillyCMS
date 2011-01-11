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
 * @package    Zend_Soap
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Soap\Wsdl;
use Zend\Soap\Wsdl\Strategy,
    Zend\Soap\Wsdl,
    Zend\Soap\WsdlException;

/**
 * @package Zend_Soap
 * @subpackage UnitTests
 */


/** Zend_Soap_Wsdl */


/**
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Soap
 * @group      Zend_Soap_Wsdl
 */
class CompositeStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testCompositeApiAddingStragiesToTypes()
    {
        $strategy = new Strategy\Composite(array(), '\Zend\Soap\Wsdl\Strategy\ArrayOfTypeSequence');
        $strategy->connectTypeToStrategy('Book', '\Zend\Soap\Wsdl\Strategy\ArrayOfTypeComplex');

        $bookStrategy = $strategy->getStrategyOfType('Book');
        $cookieStrategy = $strategy->getStrategyOfType('Cookie');

        $this->assertTrue( $bookStrategy instanceof Strategy\ArrayOfTypeComplex );
        $this->assertTrue( $cookieStrategy instanceof Strategy\ArrayOfTypeSequence );
    }

    public function testConstructorTypeMapSyntax()
    {
        $typeMap = array('Book' => '\Zend\Soap\Wsdl\Strategy\ArrayOfTypeComplex');

        $strategy = new Strategy\Composite($typeMap, '\Zend\Soap\Wsdl\Strategy\ArrayOfTypeSequence');

        $bookStrategy = $strategy->getStrategyOfType('Book');
        $cookieStrategy = $strategy->getStrategyOfType('Cookie');

        $this->assertTrue( $bookStrategy instanceof Strategy\ArrayOfTypeComplex );
        $this->assertTrue( $cookieStrategy instanceof Strategy\ArrayOfTypeSequence );
    }

    public function testCompositeThrowsExceptionOnInvalidType()
    {
        $strategy = new Strategy\Composite();
        
        $this->setExpectedException('Zend\Soap\Exception\InvalidArgumentException', 'Invalid type given to Composite Type Map');
        $strategy->connectTypeToStrategy(array(), 'strategy');
    }

    public function testCompositeThrowsExceptionOnInvalidStrategy()
    {
        $strategy = new Strategy\Composite(array(), 'invalid');
        $strategy->connectTypeToStrategy('Book', 'strategy');

        $this->setExpectedException('Zend\Soap\Exception\InvalidArgumentException', 'Strategy for Complex Type \'Book\' is not a valid strategy');
        $book = $strategy->getStrategyOfType('Book');
    }
    
    public function testCompositeThrowsExceptionOnInvalidStrategyPart2()
    {
        $strategy = new Strategy\Composite(array(), 'invalid');
        $strategy->connectTypeToStrategy('Book', 'strategy');

        $this->setExpectedException('Zend\Soap\Exception\InvalidArgumentException', 'Default Strategy for Complex Types is not a valid strategy object');
        $book = $strategy->getStrategyOfType('Anything');
    }
    
    

    public function testCompositeDelegatesAddingComplexTypesToSubStrategies()
    {
        $strategy = new Strategy\Composite(array(), '\Zend\Soap\Wsdl\Strategy\AnyType');
        $strategy->connectTypeToStrategy('\ZendTest\Soap\Wsdl\Book',   '\Zend\Soap\Wsdl\Strategy\ArrayOfTypeComplex');
        $strategy->connectTypeToStrategy('\ZendTest\Soap\Wsdl\Cookie', '\Zend\Soap\Wsdl\Strategy\DefaultComplexType');

        $wsdl = new Wsdl('SomeService', 'http://example.com');
        $strategy->setContext($wsdl);

        $this->assertEquals('tns:ZendTest.Soap.Wsdl.Book',   $strategy->addComplexType('\ZendTest\Soap\Wsdl\Book'));
        $this->assertEquals('tns:ZendTest.Soap.Wsdl.Cookie', $strategy->addComplexType('\ZendTest\Soap\Wsdl\Cookie'));
        $this->assertEquals('xsd:anyType', $strategy->addComplexType('\ZendTest\Soap\Wsdl\Anything'));
    }

    public function testCompositeRequiresContextForAddingComplexTypesOtherwiseThrowsException()
    {
        $strategy = new Strategy\Composite();
        
        $this->setExpectedException('Zend\Soap\Exception\InvalidArgumentException', 'Cannot add complex type \'Test\'');
        $strategy->addComplexType('Test');
    }
}

class Book
{
    /**
     * @var int
     */
    public $somevar;
}
class Cookie
{
    /**
     * @var int
     */
    public $othervar;
}
class Anything
{
}
