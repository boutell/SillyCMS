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
 * @package    Zend_Version
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

use Zend\Version;

/**
 * @category   Zend
 * @package    Zend_Version
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Version
 */
class Zend_VersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that version_compare() and its "proxy"
     * Zend_Version::compareVersion() work as expected.
     */
    public function testVersionCompare()
    {
        $expect = -1;
        for ($i=0; $i < 2; $i++) {
            for ($j=0; $j < 12; $j++) {
                for ($k=0; $k < 20; $k++) {
                    foreach (array('dev', 'pr', 'PR', 'alpha', 'a1', 'a2', 'beta', 'b1', 'b2', 'RC', 'RC1', 'RC2', 'RC3', '', 'pl1', 'PL1') as $rel) {
                        $ver = "$i.$j.$k$rel";
                        $normalizedVersion = strtolower(Version::VERSION);
                        if (strtolower($ver) === $normalizedVersion
                            || strtolower("$i.$j.$k-$rel") === $normalizedVersion
                            || strtolower("$i.$j.$k.$rel") === $normalizedVersion
                            || strtolower("$i.$j.$k $rel") === $normalizedVersion
                        ) {
                            if ($expect == -1) {
                                $expect = 1;
                            }
                        } else {
                            $this->assertSame(
                                Version::compareVersion($ver),
                                $expect,
                                "For version '$ver' and Zend_Version::VERSION = '"
                                . Version::VERSION . "': result=" . (Version::compareVersion($ver))
                                . ', but expected ' . $expect);
                        }
                    }
                }
            }
        };
    }

}
