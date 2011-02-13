<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Form;

use Symfony\Component\Form\TimezoneField;

class TimezoneFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testTimezonesAreSelectable()
    {
        $field = new TimeZoneField('timezone');
        $choices = $field->getOtherChoices();

        $this->assertArrayHasKey('Africa', $choices);
        $this->assertArrayHasKey('Africa/Kinshasa', $choices['Africa']);
        $this->assertEquals('Kinshasa', $choices['Africa']['Africa/Kinshasa']);

        $this->assertArrayHasKey('America', $choices);
        $this->assertArrayHasKey('America/New_York', $choices['America']);
        $this->assertEquals('New York', $choices['America']['America/New_York']);
    }
}