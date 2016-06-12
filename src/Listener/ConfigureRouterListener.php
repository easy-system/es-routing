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

use Es\Cache\Adapter\AbstractCache;
use Es\Modules\ModulesEvent;
use Es\Router\Route;
use Es\Router\RouterInterface;
use Es\Services\ServicesTrait;
use Es\System\ConfigInterface;
use InvalidArgumentException;

/**
 * Configures the router.
 */
class ConfigureRouterListener
{
    use ServicesTrait;

    /**
     * The router.
     *
     * @var \Es\Router\RouterInterface
     */
    protected $router;

    /**
     * The cache.
     *
     * @var \Es\Cache\Adapter\AbstractCache
     */
    protected $cache;

    /**
     * The system configuration.
     *
     * @var \Es\System\Config
     */
    protected $config;

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
     * @return \Es\Router\RouterInterface The router
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
     * Sets the cache.
     *
     * @param \Es\Cache\Adapter\AbstractCache $cache The cache
     */
    public function setCache(AbstractCache $cache)
    {
        $this->cache = $cache->withNamespace('system');
    }

    /**
     * Gets the cache.
     *
     * @return \Es\Cache\Adapter\AbstractCache The cache
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
     * Sets the system configuration.
     *
     * @param \Es\System\ConfigInterface $config The system configuration
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Gets the system configuration.
     *
     * @return \Es\System\Config The system configuration
     */
    public function getConfig()
    {
        if (! $this->config) {
            $services = $this->getServices();
            $config   = $services->get('Config');
            $this->setConfig($config);
        }

        return $this->config;
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
        $config = isset($systemConfig['router']) ? (array) $systemConfig['router'] : [];
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
