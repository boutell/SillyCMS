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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTest extends \PHPUnit_Framework_TestCase
{
    static protected $fixturesPath;

    static public function setUpBeforeClass()
    {
        self::$fixturesPath = __DIR__.'/../Fixtures/';
        require_once self::$fixturesPath.'/TestCommand.php';
    }

    public function testConstructor()
    {
        try {
            $command = new Command();
            $this->fail('__construct() throws a \LogicException if the name is null');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\LogicException', $e, '__construct() throws a \LogicException if the name is null');
            $this->assertEquals('The command name cannot be empty.', $e->getMessage(), '__construct() throws a \LogicException if the name is null');
        }
        $command = new Command('foo:bar');
        $this->assertEquals('foo:bar', $command->getFullName(), '__construct() takes the command name as its first argument');
    }

    public function testSetApplication()
    {
        $application = new Application();
        $command = new \TestCommand();
        $command->setApplication($application);
        $this->assertEquals($application, $command->getApplication(), '->setApplication() sets the current application');
    }

    public function testSetGetDefinition()
    {
        $command = new \TestCommand();
        $ret = $command->setDefinition($definition = new InputDefinition());
        $this->assertEquals($command, $ret, '->setDefinition() implements a fluent interface');
        $this->assertEquals($definition, $command->getDefinition(), '->setDefinition() sets the current InputDefinition instance');
        $command->setDefinition(array(new InputArgument('foo'), new InputOption('bar')));
        $this->assertTrue($command->getDefinition()->hasArgument('foo'), '->setDefinition() also takes an array of InputArguments and InputOptions as an argument');
        $this->assertTrue($command->getDefinition()->hasOption('bar'), '->setDefinition() also takes an array of InputArguments and InputOptions as an argument');
        $command->setDefinition(new InputDefinition());
    }

    public function testAddArgument()
    {
        $command = new \TestCommand();
        $ret = $command->addArgument('foo');
        $this->assertEquals($command, $ret, '->addArgument() implements a fluent interface');
        $this->assertTrue($command->getDefinition()->hasArgument('foo'), '->addArgument() adds an argument to the command');
    }

    public function testAddOption()
    {
        $command = new \TestCommand();
        $ret = $command->addOption('foo');
        $this->assertEquals($command, $ret, '->addOption() implements a fluent interface');
        $this->assertTrue($command->getDefinition()->hasOption('foo'), '->addOption() adds an option to the command');
    }

    public function testGetNamespaceGetNameGetFullNameSetName()
    {
        $command = new \TestCommand();
        $this->assertEquals('namespace', $command->getNamespace(), '->getNamespace() returns the command namespace');
        $this->assertEquals('name', $command->getName(), '->getName() returns the command name');
        $this->assertEquals('namespace:name', $command->getFullName(), '->getNamespace() returns the full command name');
        $command->setName('foo');
        $this->assertEquals('foo', $command->getName(), '->setName() sets the command name');

        $command->setName(':bar');
        $this->assertEquals('bar', $command->getName(), '->setName() sets the command name');
        $this->assertEquals('', $command->getNamespace(), '->setName() can set the command namespace');

        $ret = $command->setName('foobar:bar');
        $this->assertEquals($command, $ret, '->setName() implements a fluent interface');
        $this->assertEquals('bar', $command->getName(), '->setName() sets the command name');
        $this->assertEquals('foobar', $command->getNamespace(), '->setName() can set the command namespace');

        try {
            $command->setName('');
            $this->fail('->setName() throws an \InvalidArgumentException if the name is empty');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e, '->setName() throws an \InvalidArgumentException if the name is empty');
            $this->assertEquals('A command name cannot be empty.', $e->getMessage(), '->setName() throws an \InvalidArgumentException if the name is empty');
        }

        try {
            $command->setName('foo:');
            $this->fail('->setName() throws an \InvalidArgumentException if the name is empty');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e, '->setName() throws an \InvalidArgumentException if the name is empty');
            $this->assertEquals('A command name cannot be empty.', $e->getMessage(), '->setName() throws an \InvalidArgumentException if the name is empty');
        }
    }

    public function testGetSetDescription()
    {
        $command = new \TestCommand();
        $this->assertEquals('description', $command->getDescription(), '->getDescription() returns the description');
        $ret = $command->setDescription('description1');
        $this->assertEquals($command, $ret, '->setDescription() implements a fluent interface');
        $this->assertEquals('description1', $command->getDescription(), '->setDescription() sets the description');
    }

    public function testGetSetHelp()
    {
        $command = new \TestCommand();
        $this->assertEquals('help', $command->getHelp(), '->getHelp() returns the help');
        $ret = $command->setHelp('help1');
        $this->assertEquals($command, $ret, '->setHelp() implements a fluent interface');
        $this->assertEquals('help1', $command->getHelp(), '->setHelp() sets the help');
    }

    public function testGetSetAliases()
    {
        $command = new \TestCommand();
        $this->assertEquals(array('name'), $command->getAliases(), '->getAliases() returns the aliases');
        $ret = $command->setAliases(array('name1'));
        $this->assertEquals($command, $ret, '->setAliases() implements a fluent interface');
        $this->assertEquals(array('name1'), $command->getAliases(), '->setAliases() sets the aliases');
    }

    public function testGetSynopsis()
    {
        $command = new \TestCommand();
        $command->addOption('foo');
        $command->addArgument('foo');
        $this->assertEquals('namespace:name [--foo] [foo]', $command->getSynopsis(), '->getSynopsis() returns the synopsis');
    }

    public function testGetHelper()
    {
        $application = new Application();
        $command = new \TestCommand();
        $command->setApplication($application);
        $formatterHelper = new FormatterHelper();
        $this->assertEquals($formatterHelper->getName(), $command->getHelper('formatter')->getName(), '->getHelper() returns the correct helper');
    }

    public function testGet()
    {
        $application = new Application();
        $command = new \TestCommand();
        $command->setApplication($application);
        $formatterHelper = new FormatterHelper();
        $this->assertEquals($formatterHelper->getName(), $command->formatter->getName(), '->__get() returns the correct helper');
    }

    public function testMergeApplicationDefinition()
    {
        $application1 = new Application();
        $application1->getDefinition()->addArguments(array(new InputArgument('foo')));
        $application1->getDefinition()->addOptions(array(new InputOption('bar')));
        $command = new \TestCommand();
        $command->setApplication($application1);
        $command->setDefinition($definition = new InputDefinition(array(new InputArgument('bar'), new InputOption('foo'))));
        $command->mergeApplicationDefinition();
        $this->assertTrue($command->getDefinition()->hasArgument('foo'), '->mergeApplicationDefinition() merges the application arguments and the command arguments');
        $this->assertTrue($command->getDefinition()->hasArgument('bar'), '->mergeApplicationDefinition() merges the application arguments and the command arguments');
        $this->assertTrue($command->getDefinition()->hasOption('foo'), '->mergeApplicationDefinition() merges the application options and the command options');
        $this->assertTrue($command->getDefinition()->hasOption('bar'), '->mergeApplicationDefinition() merges the application options and the command options');

        $command->mergeApplicationDefinition();
        $this->assertEquals(3, $command->getDefinition()->getArgumentCount(), '->mergeApplicationDefinition() does not try to merge twice the application arguments and options');

        $command = new \TestCommand();
        $command->mergeApplicationDefinition();
    }

    public function testRun()
    {
        $command = new \TestCommand();
        $tester = new CommandTester($command);
        try {
            $tester->execute(array('--bar' => true));
            $this->fail('->run() throws a \InvalidArgumentException when the input does not validate the current InputDefinition');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e, '->run() throws a \InvalidArgumentException when the input does not validate the current InputDefinition');
            $this->assertEquals('The "--bar" option does not exist.', $e->getMessage(), '->run() throws a \InvalidArgumentException when the input does not validate the current InputDefinition');
        }

        $this->assertEquals('interact called'.PHP_EOL.'execute called'.PHP_EOL, $tester->execute(array(), array('interactive' => true)), '->run() calls the interact() method if the input is interactive');
        $this->assertEquals('execute called'.PHP_EOL, $tester->execute(array(), array('interactive' => false)), '->run() does not call the interact() method if the input is not interactive');

        $command = new Command('foo');
        try {
            $command->run(new StringInput(''), new NullOutput());
            $this->fail('->run() throws a \LogicException if the execute() method has not been overriden and no code has been provided');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\LogicException', $e, '->run() throws a \LogicException if the execute() method has not been overriden and no code has been provided');
            $this->assertEquals('You must override the execute() method in the concrete command class.', $e->getMessage(), '->run() throws a \LogicException if the execute() method has not been overriden and no code has been provided');
        }
    }

    public function testSetCode()
    {
        $command = new \TestCommand();
        $ret = $command->setCode(function (InputInterface $input, OutputInterface $output)
        {
            $output->writeln('from the code...');
        });
        $this->assertEquals($command, $ret, '->setCode() implements a fluent interface');
        $tester = new CommandTester($command);
        $tester->execute(array());
        $this->assertEquals('interact called'.PHP_EOL.'from the code...'.PHP_EOL, $tester->getDisplay());
    }

    public function testAsText()
    {
        $command = new \TestCommand();
        $command->setApplication(new Application());
        $tester = new CommandTester($command);
        $tester->execute(array('command' => $command->getFullName()));
        $this->assertStringEqualsFile(self::$fixturesPath.'/command_astext.txt', $command->asText(), '->asText() returns a text representation of the command');
    }

    public function testAsXml()
    {
        $command = new \TestCommand();
        $command->setApplication(new Application());
        $tester = new CommandTester($command);
        $tester->execute(array('command' => $command->getFullName()));
        $this->assertXmlStringEqualsXmlFile(self::$fixturesPath.'/command_asxml.txt', $command->asXml(), '->asXml() returns an XML representation of the command');
    }
}
