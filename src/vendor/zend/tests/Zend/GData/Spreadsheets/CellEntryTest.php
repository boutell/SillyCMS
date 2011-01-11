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
 * @package    Zend_GData_Spreadsheets
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\GData\Spreadsheets;
use Zend\GData\Spreadsheets;

/**
 * @category   Zend
 * @package    Zend_GData_Spreadsheets
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_GData
 * @group      Zend_GData_Spreadsheets
 */
class CellEntryTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->cellEntry = new Spreadsheets\CellEntry();
    }

    public function testToAndFromString()
    {
        $this->cellEntry->setCell(new \Zend\GData\Spreadsheets\Extension\Cell('my cell', '1', '2', 'input value', 'numeric value'));
        $this->assertTrue($this->cellEntry->getCell()->getText() == 'my cell');
        $this->assertTrue($this->cellEntry->getCell()->getRow() == '1');
        $this->assertTrue($this->cellEntry->getCell()->getColumn() == '2');
        $this->assertTrue($this->cellEntry->getCell()->getInputValue() == 'input value');
        $this->assertTrue($this->cellEntry->getCell()->getNumericValue() == 'numeric value');

        $newCellEntry = new Spreadsheets\CellEntry();
        $doc = new \DOMDocument();
        $doc->loadXML($this->cellEntry->saveXML());
        $newCellEntry->transferFromDom($doc->documentElement);

        $this->assertTrue($this->cellEntry->getCell()->getText() == $newCellEntry->getCell()->getText());
        $this->assertTrue($this->cellEntry->getCell()->getRow() == $newCellEntry->getCell()->getRow());
        $this->assertTrue($this->cellEntry->getCell()->getColumn() == $newCellEntry->getCell()->getColumn());
        $this->assertTrue($this->cellEntry->getCell()->getInputValue() == $newCellEntry->getCell()->getInputValue());
        $this->assertTrue($this->cellEntry->getCell()->getNumericValue() == $newCellEntry->getCell()->getNumericValue());
    }

}
