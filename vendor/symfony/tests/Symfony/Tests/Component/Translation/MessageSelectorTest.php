<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Translation;

use Symfony\Component\Translation\MessageSelector;

class MessageSelectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getChooseTests
     */
    public function testChoose($expected, $id, $number)
    {
        $selector = new MessageSelector();

        $this->assertEquals($expected, $selector->choose($id, $number, 'en'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testChooseWhenNoEnoughChoices()
    {
        $selector = new MessageSelector();

        $selector->choose('foo', 10, 'en');
    }

    public function getChooseTests()
    {
        return array(
            array('There is no apples', '{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples', 0),
            array('There is one apple', '{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples', 1),
            array('There is %count% apples', '{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples', 10),

            array('There is %count% apples', 'There is one apple|There is %count% apples', 0),
            array('There is one apple', 'There is one apple|There is %count% apples', 1),
            array('There is %count% apples', 'There is one apple|There is %count% apples', 10),

            array('There is %count% apples', 'one: There is one apple|more: There is %count% apples', 0),
            array('There is one apple', 'one: There is one apple|more: There is %count% apples', 1),
            array('There is %count% apples', 'one: There is one apple|more: There is %count% apples', 10),

            array('There is no apples', '{0} There is no apples|one: There is one apple|more: There is %count% apples', 0),
            array('There is one apple', '{0} There is no apples|one: There is one apple|more: There is %count% apples', 1),
            array('There is %count% apples', '{0} There is no apples|one: There is one apple|more: There is %count% apples', 10),
        );
    }
}
