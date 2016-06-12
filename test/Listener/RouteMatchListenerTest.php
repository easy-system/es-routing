<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author  Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Routing\Test;

use Es\Http\Server;
use Es\Http\Uri;
use Es\Router\Route;
use Es\Router\Router;
use Es\Routing\Listener\RouteMatchListener;
use Es\Services\Services;
use Es\System\SystemEvent;

class RouteMatchListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetRouter()
    {
        $router   = new Router();
        $listener = new RouteMatchListener();
        $listener->setRouter($router);
        $this->assertSame($router, $listener->getRouter());
    }

    public function testGetRouter()
    {
        $router   = new Router();
        $services = new Services();
        $services->set('Router', $router);

        $listener = new RouteMatchListener();
        $listener->setServices($services);
        $this->assertSame($router, $listener->getRouter());
    }

    public function testSetServer()
    {
        $server   = new Server();
        $listener = new RouteMatchListener();
        $listener->setServer($server);
        $this->assertSame($server, $listener->getServer());
    }

    public function testGetServer()
    {
        $server   = new Server();
        $services = new Services();
        $services->set('Server', $server);

        $listener = new RouteMatchListener();
        $listener->setServices($services);
        $this->assertSame($server, $listener->getServer());
    }

    public function testInvoke()
    {
        $params = [
            'foo' => 'bar',
            'bat' => 'baz',
        ];
        $route  = new Route('/foo', $params);
        $router = new Router();
        $router->add('foo', $route);
        $server  = new Server();
        $request = $server->getRequest();
        $uri     = new Uri('/foo');
        $server->setRequest($request->withUri($uri));

        $listener = new RouteMatchListener();
        $listener->setRouter($router);
        $listener->setServer($server);

        $listener(new SystemEvent());

        $request  = $server->getRequest();
        $expected = [
            'foo'            => 'bar',
            'bat'            => 'baz',
            'request_method' => 'GET',
            'request_scheme' => '',
            'route'          => 'foo',
        ];
        $this->assertSame($expected, $request->getAttributes());
    }
}
