<?php
/**
 * BBY Open Play
 *
 * @package     Chrisguitarguy\\BbyOpenPlay
 * @copyright   2014 Christopher Davis <http://christopherdavis.me>
 * @license     http://opensource.org/licenses/MIT MIT
 */

namespace Chrisguitarguy\BbyOpenPlay;

use Symfony\Component\HttpFoundation\Request;

class Controller
{
    private $twig;
    private $client;

    public function __construct(\Twig_Environment $twig, BbyOpenClient $client)
    {
        $this->twig = $twig;
        $this->client = $client;
    }

    public function indexAction(Request $request)
    {
        $searchAttr = $this->extractSearch($request);
        $page = $request->query->get('page');
        $pages = null;
        $products = array();
        if (!empty($searchAttr)) {
            list($products, $pages) = $this->fetchProducts($searchAttr);
        }

        return 'hello, world';
    }

    private function extractSearch(Request $request)
    {
        $qs = $request->query->all();
        $exists = array_intersect([
            'new',
            'minimum-price',
            'maximum-price',
            'marketplace',
        ], array_keys($qs));

        $rv = array();
        foreach ($exists as $key) {
            $rv[$key] = $qs[$key];
        }

        return $rv;
    }

    private function fetchProducts(array $searchAttr)
    {
        try {
            $response = $this->client->findProducts($searchAttr); // risky?
        } catch (\Exception $e) {
            return [array(), null];
        }

        return [$response['products'], $response['totalPages']];
    }
}
