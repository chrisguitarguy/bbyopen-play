<?php
/**
 * BBY Open Play
 *
 * @package     Chrisguitarguy\\BbyOpenPlay
 * @copyright   2014 Christopher Davis <http://christopherdavis.me>
 * @license     http://opensource.org/licenses/MIT MIT
 */

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Chrisguitarguy\BbyOpenPlay\BbyOpenClient;
use Chrisguitarguy\BbyOpenPlay\Controller;

class Kernel extends Application
{
    private $env;

    public function __construct($env=null)
    {
        parent::__construct();

        $this->env = $env ?: 'dev';

        $this->register(new ServiceControllerServiceProvider());
        $this->register(new TwigServiceProvider(), [
            'twig.path'     => __DIR__.'/views',
            'twig.options'  => function ($app) {
                return [
                    'debug'     => $app['debug'],
                    'cache'     => $app['debug'] ? false : __DIR__.'/cache',
                ];
            },
        ]);

        $this->registerServices();
        $this->get('/', 'controller:indexAction');
    }

    private function registerServices()
    {
        $this['client'] = function () {
            return new BbyOpenClient(getenv('BBY_APIKEY'));
        };

        $this['controller'] = function ($app) {
            return new Controller($app['twig'], $app['client']);
        };
    }
}
