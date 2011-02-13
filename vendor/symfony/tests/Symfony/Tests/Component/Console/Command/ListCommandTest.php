<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;

class ListCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $application = new Application();

        $commandTester = new CommandTester($command = $application->get('list'));
        $commandTester->execute(array('command' => $command->getFullName()));
        $this->assertRegExp('/help   Displays help for a command/', $commandTester->getDisplay(), '->execute() returns a list of available commands');

        $commandTester->execute(array('command' => $command->getFullName(), '--xml' => true));
        $this->assertRegExp('/<command id="list" namespace="_global" name="list">/', $commandTester->getDisplay(), '->execute() returns a list of available commands in XML if --xml is passed');
    }
}
