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

use Es\Cache\AbstractCache;
use Es\Modules\ModulesEvent;
use Es\Router\Route;
use Es\Routing\RouterTrait;
use Es\Services\ServicesTrait;
use Es\System\ConfigTrait;
use InvalidArgumentException;

/**
 * Configures the router.
 */
class ConfigureRouterListener
{
    use ConfigTrait, RouterTrait, ServicesTrait;

    /**
     * The cache.
     *
     * @var \Es\Cache\AbstractCache
     */
    protected $cache;

    /**
     * Sets the cache.
     *
     * @param \Es\Cache\AbstractCache $cache The cache
     */
    public function setCache(AbstractCache $cache)
    {
        $this->cache = $cache->withNamespace('system');
    }

    /**
     * Gets the cache.
     *
     * @return \Es\Cache\AbstractCache The cache
     */
    public function getCache()
    {
        if (! $this->cache) {
            $services = $this->getServices();
            $cache    = $services->get('Cache');
            $this->setCache($cache);
        }

        return $this->cache;
    }

    /**
     * Configures the router.
     * If the cache is enabled, restores the router from cache.
     *
     * @param \Es\Modules\ModulesEvent $event The modules event
     *
     * @throws \InvalidArgumentException If invalid route configuration provided
     *
     * - If the path of route is not specified
     * - If the controller of route is not specified
     */
    public function __invoke(ModulesEvent $event)
    {
        $router = $this->getRouter();
        $cache  = $this->getCache();

        $data = $cache->get('router');
        if ($data) {
            $router->merge($data);

            return;
        }
        $systemConfig = $this->getConfig();
        $config       = isset($systemConfig['router']) ? (array) $systemConfig['router'] : [];
        if (isset($config['defaults'])) {
            $router->setDefaultParams((array) $config['defaults']);
        }

        $routes = isset($config['routes']) ? (array) $config['routes'] : [];
        foreach ($routes as $name => $params) {
            if (! isset($params['path'])) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid configuration of route "%s". The route path '
                    . 'is required.',
                    $name
                ));
            }
            if (! isset($params['defaults']['controller'])) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid configuration of route "%s". The controller is '
                    . 'not specified.',
                    $name
                ));
            }
            $path     = $params['path'];
            $defaults = $params['defaults'];

            $constrains = isset($params['constrains']) ? $params['constrains'] : [];
            $schemes    = isset($params['schemes'])    ? $params['schemes']    : [];
            $methods    = isset($params['methods'])    ? $params['methods']    : [];

            $route = new Route($path, $defaults, $constrains, $schemes, $methods);
            $router->add($name, $route);
        }

        $cache->set('router', $router);
    }
}
