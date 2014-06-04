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

class Kernel extends Application
{
    private $env;

    public function __construct($env=null)
    {
        parent::__construct();

        $this->env = $env ?: 'dev';

        $this->register(new TwigServiceProvider(), [
            'twig.path'     => __DIR__.'/views',
            'twig.options'  => function ($app) {
                return [
                    'debug'     => $app['debug'],
                    'cache'     => $app['debug'] ? false : __DIR__.'/cache',
                ];
            },
        ]);
    }
}
