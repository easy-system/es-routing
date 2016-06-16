<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Routing;

use Es\Router\RouterInterface;
use Es\Services\Provider;

/**
 * The accessors of Router.
 */
trait RouterTrait
{
    /**
     * Sets the router.
     *
     * @param \Es\Router\RouterInterface $router The router
     */
    public function setRouter(RouterInterface $router)
    {
        Provider::getServices()->set('Router', $router);
    }

    /**
     * Gets the router.
     *
     * @return \Es\Router\RouterInterface The router
     */
    public function getRouter()
    {
        return Provider::getServices()->get('Router');
    }
}
