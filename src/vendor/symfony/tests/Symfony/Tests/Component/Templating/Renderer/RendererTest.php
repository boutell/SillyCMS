<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Templating\Renderer;

require_once __DIR__.'/../Fixtures/SimpleHelper.php';

use Symfony\Component\Templating\Engine;
use Symfony\Component\Templating\Renderer\Renderer;
use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\Loader\FilesystemLoader;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    public function testSetEngine()
    {
        $loader = new FilesystemLoader(array(__DIR__.'/../Fixtures/templates/%name%.%renderer%'));
        $engine = new Engine($loader);
        $renderer = new ProjectTemplateRenderer();
        $renderer->setEngine($engine);
        $this->assertTrue($renderer->getEngine() === $engine, '->setEngine() sets the engine instance tied to this renderer');
    }
}
class ProjectTemplateRenderer extends Renderer
{
    public function getEngine()
    {
        return $this->engine;
    }

    public function evaluate(Storage $template, array $parameters = array())
    {
    }
}
