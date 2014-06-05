<?php
/**
 * BBY Open Play
 *
 * @package     Chrisguitarguy\\BbyOpenPlay
 * @copyright   2014 Christopher Davis <http://christopherdavis.me>
 * @license     http://opensource.org/licenses/MIT MIT
 */

namespace Chrisguitarguy\BbyOpenPlay;

use GuzzleHttp\Client;

/**
 * Abstract away interaction with guzzle and the BBYOPEN API.
 *
 * @since   0.1
 */
class BbyOpenClient
{
    const BASEURL           = 'http://api.remix.bestbuy.com/v1/products';
    const DEFAULT_PERPAGE   = 100;

    private $httpClient;
    private $apiKey;

    public function __construct($apiKey, Client $client=null)
    {
        $this->httpClient = $client ?: new Client();
        $this->apiKey = $apiKey;
    }

    /**
     * Find products by a set of attributes:
     *
     *  - minimum-price - Number that should be the lower end of the product prices returned
     *  - maximum-price - Number that should be the upper end of the product prices returned
     *  - marketplace - Boolean for whether-or-not returned items come from the BBY marketplace
     *  - new - Boolean for whether-or-not to include include "new" items
     *  - keyword - String that searches product name
     *
     * @since   0.1
     * @param   array $searchAttr
     * @param   int $page
     * @param   int $perPage
     * @return  array[]
     */
    public function findProducts(array $searchAttr, $page=1, $perPage=null)
    {
        $parts = array();
        $parts[] = $this->buildPriceSearch($searchAttr);
        $parts[] = $this->buildMarketplaceSearch($searchAttr);
        $parts[] = $this->buildConditionSearch($searchAttr);
        if (!empty($searchAttr['keyword'])) {
            $parts[] = sprintf('name="%s*"', $searchAttr['keyword']);
        }

        $response = $this->httpClient->get(sprintf('%s(%s)', self::BASEURL, implode('&', array_filter($parts))), [
            'query'     => [
                'format'    => 'json',
                'apiKey'    => $this->apiKey,
                'pageSize'  => $perPage ?: self::DEFAULT_PERPAGE,
                'page'      => intval($page) ?: 1,
                'show'      => 'sku,name,salePrice,regularPrice,url,condition'
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    private function buildPriceSearch(array $searchAttr)
    {
        $parts = array();

        if (!empty($searchAttr['minimum-price']) && $this->isFloat($searchAttr['minimum-price'])) {
            $parts[] = sprintf('regularPrice>=%s', $searchAttr['minimum-price']);
        }

        if (!empty($searchAttr['maximum-price']) && $this->isFloat($searchAttr['maximum-price'])) {
            $parts[] = sprintf('regularPrice<=%s', $searchAttr['maximum-price']);
        }

        return $parts ? sprintf('(%s)', implode('&', $parts)) : null;
    }

    private function buildMarketplaceSearch(array $searchAttr)
    {
        if (!empty($searchAttr['marketplace']) && $this->toBool($searchAttr['marketplace'])) {
            return 'marketplace=true';
        }

        return null; // allow anything
    }

    private function buildConditionSearch(array $searchAttr)
    {
        $parts = ['condition=refurbished'];
        if (!empty($searchAttr['new']) && $this->toBool($searchAttr['new'])) {
            $parts[] = 'condition=new';
        }

        return sprintf('(%s)', implode('|', $parts));
    }

    private function isFloat($value)
    {
        return false !== filter_var($value, FILTER_VALIDATE_FLOAT);
    }

    private function toBool($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
