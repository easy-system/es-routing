<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author  Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Routing\Test\Listener;

use Es\Cache\Adapter\FileCache;
use Es\Modules\ModulesEvent;
use Es\Router\RouteInterface;
use Es\Router\Router;
use Es\Routing\Listener\ConfigureRouterListener;
use Es\Services\Services;
use Es\System\SystemConfig;

class ConfigureRouterListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetRouter()
    {
        $router   = new Router();
        $listener = new ConfigureRouterListener();
        $listener->setRouter($router);
        $this->assertSame($router, $listener->getRouter());
    }

    public function testGetRouter()
    {
        $router   = new Router();
        $services = new Services();
        $services->set('Router', $router);

        $listener = new ConfigureRouterListener();
        $listener->setServices($services);
        $this->assertSame($router, $listener->getRouter());
    }

    public function testSetCache()
    {
        $adapter  = new FileCache();
        $listener = new ConfigureRouterListener();
        $listener->setCache($adapter);
        $cache = $listener->getCache();
        $this->assertInstanceOf(FileCache::CLASS, $cache);
        $this->assertSame('system', $cache->getNamespace());
    }

    public function testGetCache()
    {
        $adapter  = new FileCache();
        $services = $this->getMock(Services::CLASS);
        $listener = new ConfigureRouterListener();
        $listener->setServices($services);

        $services
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo('Cache'))
            ->will($this->returnValue($adapter));

        $cache = $listener->getCache();
        $this->assertInstanceOf(FileCache::CLASS, $cache);
        $this->assertSame('system', $cache->getNamespace());
    }

    public function testSetConfig()
    {
        $config   = new SystemConfig();
        $listener = new ConfigureRouterListener();
        $listener->setConfig($config);
        $this->assertSame($config, $listener->getConfig());
    }

    public function testGetConfig()
    {
        $config   = new SystemConfig();
        $services = new Services();
        $services->set('Config', $config);

        $listener = new ConfigureRouterListener();
        $listener->setServices($services);
        $this->assertSame($config, $listener->getConfig());
    }

    public function testInvokeRestoreRouterFromCache()
    {
        $stored = new Router();
        $router = $this->getMock(Router::CLASS);
        $cache  = $this->getMock(FileCache::CLASS);

        $listener = $this->getMock(ConfigureRouterListener::CLASS, ['getCache']);
        $listener->setRouter($router);

        $listener
            ->expects($this->once())
            ->method('getCache')
            ->will($this->returnValue($cache));

        $cache
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo('router'))
            ->will($this->returnValue($stored));

        $router
            ->expects($this->once())
            ->method('merge')
            ->with($this->identicalTo($stored));

        $router
            ->expects($this->never())
            ->method('add');

        $listener(new ModulesEvent());
    }

    public function testInvokeBuildRoutesFromConfig()
    {
        $systemConfig = [
            'router' => [
                'routes' => [
                    'foo' => [
                        'path'     => '/foo/~:foo',
                        'defaults' => [
                            'controller' => 'FooController',
                        ],
                        'constrains' => [
                            'foo' => '[a-zA-Z]*',
                        ],
                        'schemes' => ['https'],
                        'methods' => ['GET'],
                    ],
                ],
            ],
        ];
        $config = new SystemConfig();
        $config->merge($systemConfig);
        $router = $this->getMock(Router::CLASS);
        $cache  = $this->getMock(FileCache::CLASS);

        $listener = $this->getMock(ConfigureRouterListener::CLASS, ['getCache']);
        $listener->setConfig($config);
        $listener->setRouter($router);

        $listener
            ->expects($this->once())
            ->method('getCache')
            ->will($this->returnValue($cache));

        $router
            ->expects($this->once())
            ->method('add')
            ->with(
                $this->identicalTo('foo'),
                $this->callback(function ($route) {
                    $this->assertInstanceOf(RouteInterface::CLASS, $route);
                    $this->assertSame($route->getDefaults(), ['controller' => 'FooController']);
                    $this->assertSame($route->getConstraints(), ['foo' => '[a-zA-Z]*']);
                    $this->assertSame($route->getSchemes(), ['https']);
                    $this->assertSame($route->getMethods(), ['GET']);

                    return true;
                })
            );

        $listener(new ModulesEvent());
    }

    public function testInvokeSetsDefaultsToRouter()
    {
        $systemConfig = [
            'router' => [
                'defaults' => [
                    'foo' => 'bar',
                    'bat' => 'baz',
                ],
            ],
        ];
        $config = new SystemConfig();
        $config->merge($systemConfig);
        $router = $this->getMock(Router::CLASS);
        $cache  = $this->getMock(FileCache::CLASS);

        $listener = $this->getMock(ConfigureRouterListener::CLASS, ['getCache']);
        $listener->setConfig($config);
        $listener->setRouter($router);

        $listener
            ->expects($this->once())
            ->method('getCache')
            ->will($this->returnValue($cache));

        $router
            ->expects($this->once())
            ->method('setDefaultParams')
            ->with($this->identicalTo($systemConfig['router']['defaults']));

        $listener(new ModulesEvent());
    }

    public function testInvokeSaveRouterToCache()
    {
        $config = new SystemConfig();
        $router = $this->getMock(Router::CLASS);
        $cache  = $this->getMock(FileCache::CLASS);

        $listener = $this->getMock(ConfigureRouterListener::CLASS, ['getCache']);
        $listener->setConfig($config);
        $listener->setRouter($router);

        $listener
            ->expects($this->once())
            ->method('getCache')
            ->will($this->returnValue($cache));

        $cache
            ->expects($this->once())
            ->method('set')
            ->with(
                $this->identicalTo('router'),
                $this->identicalTo($router)
            );

        $listener(new ModulesEvent());
    }

    public function testInvokeRaiseExceptionIfPathOfRouteIsNotSpecified()
    {
        $systemConfig = [
            'router' => [
                'routes' => [
                    'foo' => [
                        'defaults' => [
                            'controller' => 'FooController',
                        ],
                    ],
                ],
            ],
        ];
        $config = new SystemConfig();
        $config->merge($systemConfig);
        $router = $this->getMock(Router::CLASS);
        $cache  = $this->getMock(FileCache::CLASS);

        $listener = $this->getMock(ConfigureRouterListener::CLASS, ['getCache']);
        $listener->setConfig($config);
        $listener->setRouter($router);

        $listener
            ->expects($this->once())
            ->method('getCache')
            ->will($this->returnValue($cache));

        $this->setExpectedException('InvalidArgumentException');
        $listener(new ModulesEvent());
    }

    public function testInvokeRaiseExceptionIfControllerOfRouteIsNotSpecified()
    {
        $systemConfig = [
            'router' => [
                'routes' => [
                    'foo' => [
                        'path' => '/',
                    ],
                ],
            ],
        ];
        $config = new SystemConfig();
        $config->merge($systemConfig);
        $router = $this->getMock(Router::CLASS);
        $cache  = $this->getMock(FileCache::CLASS);

        $listener = $this->getMock(ConfigureRouterListener::CLASS, ['getCache']);
        $listener->setConfig($config);
        $listener->setRouter($router);

        $listener
            ->expects($this->once())
            ->method('getCache')
            ->will($this->returnValue($cache));

        $this->setExpectedException('InvalidArgumentException');
        $listener(new ModulesEvent());
    }
}
