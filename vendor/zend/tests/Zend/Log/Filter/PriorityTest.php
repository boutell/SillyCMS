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
 * @package    Zend_Log
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZendTest\Log\Filter;

use \Zend\Log\Logger,
    \Zend\Log\Filter\Priority;

/**
 * @category   Zend
 * @package    Zend_Log
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Log
 */
class PriorityTest extends \PHPUnit_Framework_TestCase
{
    public function testComparisonDefaultsToLessThanOrEqual()
    {
        // accept at or below priority 2
        $filter = new Priority(2);

        $this->assertTrue($filter->accept(array('priority' => 2)));
        $this->assertTrue($filter->accept(array('priority' => 1)));
        $this->assertFalse($filter->accept(array('priority' => 3)));
    }

    public function testComparisonOperatorCanBeChanged()
    {
        // accept above priority 2
        $filter = new Priority(2, '>');

        $this->assertTrue($filter->accept(array('priority' => 3)));
        $this->assertFalse($filter->accept(array('priority' => 2)));
        $this->assertFalse($filter->accept(array('priority' => 1)));
    }

    public function testConstructorThrowsOnInvalidPriority()
    {
        $this->setExpectedException('Zend\Log\Exception\InvalidArgumentException', 'must be an integer');
        new Priority('foo');
    }
    
    public function testFactory()
    {
        $cfg = array('log' => array('memory' => array(
            'writerName' => "Mock", 
            'filterName' => "Priority", 
            'filterParams' => array(
                'priority' => '\Zend\Log\Logger::CRIT', 
                'operator' => "<="
             ),        
        )));

        $logger = Logger::factory($cfg['log']);
        $this->assertTrue($logger instanceof Logger);
    }

    public function testFactoryRaisesExceptionWithInvalidPriority()
    {
        $this->setExpectedException('Zend\Log\Exception\InvalidArgumentException', 'must be an integer');
        $logger = Logger::factory(array('Null' => array(
            'writerName'   => 'Mock',
            'filterName'   => 'Priority',
            'filterParams' => array(
                'priority' => 'somestring',
            ),
        )));            
    }
}
