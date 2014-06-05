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
        $page = intval($request->query->get('page'));
        $perPage = intval($request->query->get('per-page'));

        if ($page <= 0) {
            $page = 1;
        }
        if ($perPage <= 0) {
            $perPage = null;
        }
        $nextLink = null;
        $prevLink = null;
        $pages = null;
        $products = array();
        if (!empty($searchAttr)) {
            list($products, $pages) = $this->fetchProducts($searchAttr, $page, $perPage);
            if ($page < $pages) {
                $nextLink = $this->pageLink($searchAttr, $page+1, $perPage);
            }
            if ($page > 1) {
                $prevLink = $this->pageLink($searchAttr, $page-1, $perPage);
            }
        }

        return $this->twig->render('products.html.twig', [
            'nextLink'  => $nextLink,
            'prevLink'  => $prevLink,
            'products'  => $products,
            'search'    => $this->safeSearchAttr($searchAttr, $perPage),
        ]);
    }

    private function extractSearch(Request $request)
    {
        $qs = $request->query->all();
        $valid = [
            'keyword',
            'new',
            'minimum-price',
            'maximum-price',
            'marketplace',
        ];

        $rv = array();
        foreach ($valid as $key) {
            if (!empty($qs[$key])) {
                $rv[$key] = $qs[$key];
            }
        }

        return $rv;
    }

    private function pageLink(array $searchAttr, $pageno, $perPage)
    {
        $searchAttr['page'] = $pageno;
        if ($perPage) {
            $searchAttr['per-page'] = $perPage;
        }

        return sprintf('/?%s', http_build_query($searchAttr));
    }

    private function fetchProducts(array $searchAttr, $pageno, $perPage)
    {
        try {
            $response = $this->client->findProducts($searchAttr, $pageno, $perPage);
        } catch (\Exception $e) {
            return [array(), null];
        }

        return [$response['products'], $response['totalPages']];
    }

    private function safeSearchAttr(array $searchAttr, $perPage)
    {
        if ($perPage) {
            $searchAttr['per-page'] = $perPage;
        }

        return array_replace([
            'keyword'       => null,
            'new'           => null,
            'minimum-price' => null,
            'maximum-price' => null,
            'marketplace'   => null,
            'per-page'      => 100,
        ], $searchAttr);
    }
}
