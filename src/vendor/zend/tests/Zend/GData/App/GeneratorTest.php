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
 * @package    Zend_GData_App
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\GData\App;
use Zend\GData\App\Extension;

/**
 * @category   Zend
 * @package    Zend_GData_App
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_GData
 * @group      Zend_GData_App
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{

    public function setUp() {
        $this->generatorText = file_get_contents(
                'Zend/GData/App/_files/GeneratorElementSample1.xml',
                true);
        $this->generator = new Extension\Generator();
    }

    public function testEmptyGeneratorShouldHaveEmptyExtensionsList() {
        $this->assertTrue(is_array($this->generator->extensionElements));
        $this->assertTrue(count($this->generator->extensionElements) == 0);
    }

    public function testEmptyGeneratorToAndFromStringShouldMatch() {
        $generatorXml = $this->generator->saveXML();
        $newGenerator = new Extension\Generator();
        $newGenerator->transferFromXML($generatorXml);
        $newGeneratorXml = $newGenerator->saveXML();
        $this->assertTrue($generatorXml == $newGeneratorXml);
    }

    public function testGeneratorToAndFromStringShouldMatch() {
        $this->generator->uri = 'http://code.google.com/apis/gdata/';
        $this->generator->version = '1.0';
        $this->generator->text = 'Google data APIs';
        $generatorXml = $this->generator->saveXML();
        $newGenerator = new Extension\Generator();
        $newGenerator->transferFromXML($generatorXml);
        $newGeneratorXml = $newGenerator->saveXML();
        $this->assertEquals($newGeneratorXml, $generatorXml);
        $this->assertEquals('http://code.google.com/apis/gdata/',
                $newGenerator->uri);
        $this->assertEquals('1.0', $newGenerator->version);
        $this->assertEquals('Google data APIs', $newGenerator->text);
    }

    public function testConvertGeneratorWithDraftToAndFromString() {
        $this->generator->transferFromXML($this->generatorText);
        $this->assertEquals('http://code.google.com/apis/gdata/',
                $this->generator->uri);
        $this->assertEquals('1.0', $this->generator->version);
        $this->assertEquals('Google data APIs', $this->generator->text);
    }

}
