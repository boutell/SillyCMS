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
 * @package    Zend_Validator_File
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Validator\File;
use Zend\Validator\File;
use Zend\Validator;

/**
 * MimeType testbed
 *
 * @category   Zend
 * @package    Zend_Validator_File
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Validator
 */
class MimeTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $valuesExpected = array(
            array(array('image/jpg', 'image/jpeg'), true),
            array('image', true),
            array('test/notype', false),
            array('image/gif, image/jpg, image/jpeg', true),
            array(array('image/vasa', 'image/jpg', 'image/jpeg'), true),
            array(array('image/jpg', 'image/jpeg', 'gif'), true),
            array(array('image/gif', 'gif'), false),
            array('image/jp', false),
            array('image/jpg2000', false),
            array('image/jpeg2000', false),
        );

        $filetest = __DIR__ . '/_files/picture.jpg';
        $files = array(
            'name'     => 'picture.jpg',
            'type'     => 'image/jpg',
            'size'     => 200,
            'tmp_name' => $filetest,
            'error'    => 0
        );

        foreach ($valuesExpected as $element) {
            $options   = array_shift($element);
            $expected  = array_shift($element);
            $validator = new File\MimeType($options);
            $validator->enableHeaderCheck();
            $this->assertEquals(
                $expected,
                $validator->isValid($filetest, $files),
                "Test expected " . var_export($expected, 1) . " with " . var_export($options, 1)
                . "\nMessages: " . var_export($validator->getMessages(), 1)
            );
        }
    }

    /**
     * Ensures that getMimeType() returns expected value
     *
     * @return void
     */
    public function testGetMimeType()
    {
        $validator = new File\MimeType('image/gif');
        $this->assertEquals('image/gif', $validator->getMimeType());

        $validator = new File\MimeType(array('image/gif', 'video', 'text/test'));
        $this->assertEquals('image/gif,video,text/test', $validator->getMimeType());

        $validator = new File\MimeType(array('image/gif', 'video', 'text/test'));
        $this->assertEquals(array('image/gif', 'video', 'text/test'), $validator->getMimeType(true));
    }

    /**
     * Ensures that setMimeType() returns expected value
     *
     * @return void
     */
    public function testSetMimeType()
    {
        $validator = new File\MimeType('image/gif');
        $validator->setMimeType('image/jpeg');
        $this->assertEquals('image/jpeg', $validator->getMimeType());
        $this->assertEquals(array('image/jpeg'), $validator->getMimeType(true));

        $validator->setMimeType('image/gif, text/test');
        $this->assertEquals('image/gif,text/test', $validator->getMimeType());
        $this->assertEquals(array('image/gif', 'text/test'), $validator->getMimeType(true));

        $validator->setMimeType(array('video/mpeg', 'gif'));
        $this->assertEquals('video/mpeg,gif', $validator->getMimeType());
        $this->assertEquals(array('video/mpeg', 'gif'), $validator->getMimeType(true));
    }

    /**
     * Ensures that addMimeType() returns expected value
     *
     * @return void
     */
    public function testAddMimeType()
    {
        $validator = new File\MimeType('image/gif');
        $validator->addMimeType('text');
        $this->assertEquals('image/gif,text', $validator->getMimeType());
        $this->assertEquals(array('image/gif', 'text'), $validator->getMimeType(true));

        $validator->addMimeType('jpg, to');
        $this->assertEquals('image/gif,text,jpg,to', $validator->getMimeType());
        $this->assertEquals(array('image/gif', 'text', 'jpg', 'to'), $validator->getMimeType(true));

        $validator->addMimeType(array('zip', 'ti'));
        $this->assertEquals('image/gif,text,jpg,to,zip,ti', $validator->getMimeType());
        $this->assertEquals(array('image/gif', 'text', 'jpg', 'to', 'zip', 'ti'), $validator->getMimeType(true));

        $validator->addMimeType('');
        $this->assertEquals('image/gif,text,jpg,to,zip,ti', $validator->getMimeType());
        $this->assertEquals(array('image/gif', 'text', 'jpg', 'to', 'zip', 'ti'), $validator->getMimeType(true));
    }

    public function testSetAndGetMagicFile()
    {
        $validator = new File\MimeType('image/gif');
        if (!empty($_ENV['MAGIC'])) {
            $mimetype  = $validator->getMagicFile();
            $this->assertEquals($_ENV['MAGIC'], $mimetype);
        }

        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'can not be');
        $validator->setMagicFile('/unknown/magic/file');
    }

    public function testSetMagicFileWithinConstructor()
    {
        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'The given magicfile is not accepted by finfo');
        $validator = new File\MimeType(array('image/gif', 'magicfile' => __FILE__));
    }

    public function testOptionsAtConstructor()
    {
        $validator = new File\MimeType(array(
            'image/gif',
            'image/jpg',
            'headerCheck' => true));

        $this->assertTrue($validator->getHeaderCheck());
        $this->assertEquals('image/gif,image/jpg', $validator->getMimeType());
    }

    /**
     * @group ZF-9686
     */
    public function testDualValidation()
    {
        $valuesExpected = array(
            array('image', true),
        );

        $filetest = __DIR__ . '/_files/picture.jpg';
        $files = array(
            'name'     => 'picture.jpg',
            'type'     => 'image/jpg',
            'size'     => 200,
            'tmp_name' => $filetest,
            'error'    => 0
        );

        foreach ($valuesExpected as $element) {
            $options   = array_shift($element);
            $expected  = array_shift($element);
            $validator = new File\MimeType($options);
            $validator->enableHeaderCheck();
            $this->assertEquals(
                $expected,
                $validator->isValid($filetest, $files),
                "Test expected " . var_export($expected, 1) . " with " . var_export($options, 1)
                . "\nMessages: " . var_export($validator->getMessages(), 1)
            );

            $validator = new File\MimeType($options);
            $validator->enableHeaderCheck();
            $this->assertEquals(
                $expected,
                $validator->isValid($filetest, $files),
                "Test expected " . var_export($expected, 1) . " with " . var_export($options, 1)
                . "\nMessages: " . var_export($validator->getMessages(), 1)
            );
        }
    }
}
