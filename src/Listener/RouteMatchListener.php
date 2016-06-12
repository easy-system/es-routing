<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author  Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Routing\Listener;

use Es\Http\ServerInterface;
use Es\Router\RouterInterface;
use Es\Services\ServicesTrait;
use Es\System\SystemEvent;

/**
 * Handle the requested route.
 */
class RouteMatchListener
{
    use ServicesTrait;

    /**
     * The router.
     *
     * @var \Es\Router\RouterInterface
     */
    protected $router;

    /**
     * The server.
     *
     * @var \Es\Http\ServerInterface
     */
    protected $server;

    /**
     * Sets the router.
     *
     * @param \Es\Router\RouterInterface $router The router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Gets the router.
     *
     * @param \Es\Router\RouterInterface The router
     */
    public function getRouter()
    {
        if (! $this->router) {
            $services = $this->getServices();
            $router   = $services->get('Router');
            $this->setRouter($router);
        }

        return $this->router;
    }

    /**
     * Sets the server.
     *
     * @param \Es\Http\ServerInterface $server The server
     */
    public function setServer(ServerInterface $server)
    {
        $this->server = $server;
    }

    /**
     * Gets the server.
     *
     * @return \Es\Http\ServerInterface The server
     */
    public function getServer()
    {
        if (! $this->server) {
            $services = $this->getServices();
            $server   = $services->get('Server');
            $this->setServer($server);
        }

        return $this->server;
    }

    /**
     * Handle the requested route.
     *
     * @param \Es\System\SystemEvent $event The system event
     */
    public function __invoke(SystemEvent $event)
    {
        $router  = $this->getRouter();
        $server  = $this->getServer();
        $request = $server->getRequest();

        $router->match($request);

        $routeMatch = $router->getRouteMatch();
        $server->setRequest(
            $request->withAddedAttributes($routeMatch->getParams())
        );
    }
}
