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

use Es\Routing\RouterTrait;
use Es\Server\ServerTrait;
use Es\System\SystemEvent;

/**
 * Handle the requested route.
 */
class RouteMatchListener
{
    use RouterTrait, ServerTrait;

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
