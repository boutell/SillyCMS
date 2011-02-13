<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\HttpKernel\Profiler;

use Symfony\Component\HttpKernel\DataCollector\RequestDataCollector;
use Symfony\Component\HttpKernel\Profiler\SQLiteProfilerStorage;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    public function testCollect()
    {
        $request = new Request();
        $request->query->set('foo', 'bar');
        $response = new Response();
        $collector = new RequestDataCollector();

        $tmp = tempnam(sys_get_temp_dir(), 'sf2_profiler');
        if (file_exists($tmp)) {
            @unlink($tmp);
        }
        $storage = new SQLiteProfilerStorage($tmp);
        $storage->purge();

        $profiler = new Profiler($storage);
        $profiler->add($collector);
        $profiler->setToken('foobar');
        $profiler->collect($request, $response);

        $profiler = new Profiler($storage);
        $profiler->setToken('foobar');
        $this->assertEquals(array('foo' => 'bar'), $profiler->get('request')->getRequestQuery()->all());

        @unlink($tmp);
    }
}
