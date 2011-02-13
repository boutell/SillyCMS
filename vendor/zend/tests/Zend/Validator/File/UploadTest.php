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

/**
 * @category   Zend
 * @package    Zend_Validator_File
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Validator
 */
class UploadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $_FILES = array(
            'test'  => array(
                'name'     => 'test1',
                'type'     => 'text',
                'size'     => 200,
                'tmp_name' => 'tmp_test1',
                'error'    => 0),
            'test2' => array(
                'name'     => 'test2',
                'type'     => 'text2',
                'size'     => 202,
                'tmp_name' => 'tmp_test2',
                'error'    => 1),
            'test3' => array(
                'name'     => 'test3',
                'type'     => 'text3',
                'size'     => 203,
                'tmp_name' => 'tmp_test3',
                'error'    => 2),
            'test4' => array(
                'name'     => 'test4',
                'type'     => 'text4',
                'size'     => 204,
                'tmp_name' => 'tmp_test4',
                'error'    => 3),
            'test5' => array(
                'name'     => 'test5',
                'type'     => 'text5',
                'size'     => 205,
                'tmp_name' => 'tmp_test5',
                'error'    => 4),
            'test6' => array(
                'name'     => 'test6',
                'type'     => 'text6',
                'size'     => 206,
                'tmp_name' => 'tmp_test6',
                'error'    => 5),
            'test7' => array(
                'name'     => 'test7',
                'type'     => 'text7',
                'size'     => 207,
                'tmp_name' => 'tmp_test7',
                'error'    => 6),
            'test8' => array(
                'name'     => 'test8',
                'type'     => 'text8',
                'size'     => 208,
                'tmp_name' => 'tmp_test8',
                'error'    => 7),
            'test9' => array(
                'name'     => 'test9',
                'type'     => 'text9',
                'size'     => 209,
                'tmp_name' => 'tmp_test9',
                'error'    => 8)
        );

        $validator = new File\Upload();
        $this->assertFalse($validator->isValid('test'));
        $this->assertTrue(array_key_exists('fileUploadErrorAttack', $validator->getMessages()));

        $validator = new File\Upload();
        $this->assertFalse($validator->isValid('test2'));
        $this->assertTrue(array_key_exists('fileUploadErrorIniSize', $validator->getMessages()));

        $validator = new File\Upload();
        $this->assertFalse($validator->isValid('test3'));
        $this->assertTrue(array_key_exists('fileUploadErrorFormSize', $validator->getMessages()));

        $validator = new File\Upload();
        $this->assertFalse($validator->isValid('test4'));
        $this->assertTrue(array_key_exists('fileUploadErrorPartial', $validator->getMessages()));

        $validator = new File\Upload();
        $this->assertFalse($validator->isValid('test5'));
        $this->assertTrue(array_key_exists('fileUploadErrorNoFile', $validator->getMessages()));

        $validator = new File\Upload();
        $this->assertFalse($validator->isValid('test6'));
        $this->assertTrue(array_key_exists('fileUploadErrorUnknown', $validator->getMessages()));

        $validator = new File\Upload();
        $this->assertFalse($validator->isValid('test7'));
        $this->assertTrue(array_key_exists('fileUploadErrorNoTmpDir', $validator->getMessages()));

        $validator = new File\Upload();
        $this->assertFalse($validator->isValid('test8'));
        $this->assertTrue(array_key_exists('fileUploadErrorCantWrite', $validator->getMessages()));

        $validator = new File\Upload();
        $this->assertFalse($validator->isValid('test9'));
        $this->assertTrue(array_key_exists('fileUploadErrorExtension', $validator->getMessages()));

        $validator = new File\Upload();
        $this->assertFalse($validator->isValid('test1'));
        $this->assertTrue(array_key_exists('fileUploadErrorAttack', $validator->getMessages()));

        $validator = new File\Upload();
        $this->assertFalse($validator->isValid('tmp_test1'));
        $this->assertTrue(array_key_exists('fileUploadErrorAttack', $validator->getMessages()));

        $validator = new File\Upload();
        $this->assertFalse($validator->isValid('test000'));
        $this->assertTrue(array_key_exists('fileUploadErrorFileNotFound', $validator->getMessages()));
    }

    /**
     * Ensures that getFiles() returns expected value
     *
     * @return void
     */
    public function testGetFiles()
    {
        $_FILES = array(
            'test'  => array(
                'name'     => 'test1',
                'type'     => 'text',
                'size'     => 200,
                'tmp_name' => 'tmp_test1',
                'error'    => 0),
            'test2' => array(
                'name'     => 'test3',
                'type'     => 'text2',
                'size'     => 202,
                'tmp_name' => 'tmp_test2',
                'error'    => 1));

        $files = array(
            'test'  => array(
                'name'     => 'test1',
                'type'     => 'text',
                'size'     => 200,
                'tmp_name' => 'tmp_test1',
                'error'    => 0));

        $files1 = array(
            'test2' => array(
                'name'     => 'test3',
                'type'     => 'text2',
                'size'     => 202,
                'tmp_name' => 'tmp_test2',
                'error'    => 1));

        $validator = new File\Upload();
        $this->assertEquals($files, $validator->getFiles('test'));
        $this->assertEquals($files, $validator->getFiles('test1'));
        $this->assertEquals($files1, $validator->getFiles('test3'));

        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'was not found');
        $this->assertEquals(array(), $validator->getFiles('test5'));
    }

    /**
     * Ensures that setFiles() returns expected value
     *
     * @return void
     */
    public function testSetFiles()
    {
        $files = array(
            'test'  => array(
                'name'     => 'test1',
                'type'     => 'text',
                'size'     => 200,
                'tmp_name' => 'tmp_test1',
                'error'    => 0),
            'test2' => array(
                'name'     => 'test2',
                'type'     => 'text2',
                'size'     => 202,
                'tmp_name' => 'tmp_test2',
                'error'    => 1));

        $_FILES = array(
            'test'  => array(
                'name'     => 'test3',
                'type'     => 'text3',
                'size'     => 203,
                'tmp_name' => 'tmp_test3',
                'error'    => 2));


        $validator = new File\Upload();
        $validator->setFiles(array());
        $this->assertEquals($_FILES, $validator->getFiles());
        $validator->setFiles();
        $this->assertEquals($_FILES, $validator->getFiles());
        $validator->setFiles($files);
        $this->assertEquals($files, $validator->getFiles());
    }
}
