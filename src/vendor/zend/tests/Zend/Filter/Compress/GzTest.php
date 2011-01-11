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
 * @package    Zend_Filter
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZendTest\Filter\Compress;

use Zend\Filter\Compress\Gz as GzCompression;

/**
 * @category   Zend
 * @package    Zend_Filter
 * @subpackage UnitTests
 * @group      Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class GzTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!extension_loaded('zlib')) {
            $this->markTestSkipped('This adapter needs the zlib extension');
        }
    }

    public function tearDown()
    {
        if (file_exists(__DIR__ . '/../_files/compressed.gz')) {
            unlink(__DIR__ . '/../_files/compressed.gz');
        }
    }

    /**
     * Basic usage
     *
     * @return void
     */
    public function testBasicUsage()
    {
        $filter  = new GzCompression();

        $content = $filter->compress('compress me');
        $this->assertNotEquals('compress me', $content);

        $content = $filter->decompress($content);
        $this->assertEquals('compress me', $content);
    }

    /**
     * Setting Options
     *
     * @return void
     */
    public function testGzGetSetOptions()
    {
        $filter = new GzCompression();
        $this->assertEquals(array('mode' => 'compress', 'level' => 9, 'archive' => null), $filter->getOptions());

        $this->assertEquals(9, $filter->getOptions('level'));

        $this->assertNull($filter->getOptions('nooption'));
        $filter->setOptions(array('nooption' => 'foo'));
        $this->assertNull($filter->getOptions('nooption'));

        $filter->setOptions(array('level' => 6));
        $this->assertEquals(6, $filter->getOptions('level'));

        $filter->setOptions(array('mode' => 'deflate'));
        $this->assertEquals('deflate', $filter->getOptions('mode'));

        $filter->setOptions(array('archive' => 'test.txt'));
        $this->assertEquals('test.txt', $filter->getOptions('archive'));
    }

    /**
     * Setting Options through constructor
     *
     * @return void
     */
    public function testGzGetSetOptionsInConstructor()
    {
        $filter2= new GzCompression(array('level' => 8));
        $this->assertEquals(array('mode' => 'compress', 'level' => 8, 'archive' => null), $filter2->getOptions());
    }

    /**
     * Setting Level
     *
     * @return void
     */
    public function testGzGetSetLevel()
    {
        $filter = new GzCompression();
        $this->assertEquals(9, $filter->getLevel());
        $filter->setLevel(6);
        $this->assertEquals(6, $filter->getOptions('level'));

        $this->setExpectedException('Zend\Filter\Exception\InvalidArgumentException', 'must be between');
        $filter->setLevel(15);
    }

    /**
     * Setting Mode
     *
     * @return void
     */
    public function testGzGetSetMode()
    {
        $filter = new GzCompression();
        $this->assertEquals('compress', $filter->getMode());
        $filter->setMode('deflate');
        $this->assertEquals('deflate', $filter->getOptions('mode'));

        $this->setExpectedException('Zend\Filter\Exception\InvalidArgumentException', 'mode not supported');
        $filter->setMode('unknown');
    }

    /**
     * Setting Archive
     *
     * @return void
     */
    public function testGzGetSetArchive()
    {
        $filter = new GzCompression();
        $this->assertEquals(null, $filter->getArchive());
        $filter->setArchive('Testfile.txt');
        $this->assertEquals('Testfile.txt', $filter->getArchive());
        $this->assertEquals('Testfile.txt', $filter->getOptions('archive'));
    }

    /**
     * Setting Archive
     *
     * @return void
     */
    public function testGzCompressToFile()
    {
        $filter   = new GzCompression();
        $archive = __DIR__ . '/../_files/compressed.gz';
        $filter->setArchive($archive);

        $content = $filter->compress('compress me');
        $this->assertTrue($content);

        $filter2  = new GzCompression();
        $content2 = $filter2->decompress($archive);
        $this->assertEquals('compress me', $content2);

        $filter3 = new GzCompression();
        $filter3->setArchive($archive);
        $content3 = $filter3->decompress(null);
        $this->assertEquals('compress me', $content3);
    }

    /**
     * Test deflate
     *
     * @return void
     */
    public function testGzDeflate()
    {
        $filter  = new GzCompression(array('mode' => 'deflate'));

        $content = $filter->compress('compress me');
        $this->assertNotEquals('compress me', $content);

        $content = $filter->decompress($content);
        $this->assertEquals('compress me', $content);
    }

    /**
     * testing toString
     *
     * @return void
     */
    public function testGzToString()
    {
        $filter = new GzCompression();
        $this->assertEquals('Gz', $filter->toString());
    }
}
