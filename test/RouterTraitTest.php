<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Routing\Test;

use Es\Router\Router;
use Es\Services\Services;
use Es\Services\ServicesTrait;

class RouterTraitTest extends \PHPUnit_Framework_TestCase
{
    use ServicesTrait;

    public function setUp()
    {
        require_once 'RouterTraitTemplate.php';
    }

    public function testSetRouter()
    {
        $services = new Services();
        $this->setServices($services);

        $router   = new Router();
        $template = new RouterTraitTemplate();
        $template->setRouter($router);
        $this->assertSame($router, $services->get('Router'));
    }

    public function testGetRouter()
    {
        $services = new Services();
        $router   = new Router();
        $services->set('Router', $router);

        $this->setServices($services);
        $template = new RouterTraitTemplate();
        $this->assertSame($router, $template->getRouter());
    }
}
