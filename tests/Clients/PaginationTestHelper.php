<?php

namespace Tiendanube\Clients;

use Tiendanube\Context;

trait PaginationTestHelper
{
    /** @var string */
    protected $domain = 'api.tiendanube.com';
    /** @var string */
    protected $store_id = '1';

    /**
     * @param string $path Rest resource. e.g. `products`
     * @param string $queryString Query string `e.g. "per_page=10&fields=test1%2Ctest2"`
     *
     * @return string
     */
    protected function getAdminApiUrl(string $storeId, string $path, string $queryString): string
    {
        return "https://$this->domain/" . Context::$apiVersion . "/$storeId/$path?$queryString";
    }

    /**
     * Products URL link headers with fields: `per_page=10&fields=test1%2Ctest2` and appends the token
     * @param string|null $previousPage Page number used to access previous page
     * @param string|null $nextPage Page number used to access next page
     *
     * @return string
     */
    protected function getProductsLinkHeader(?int $previousPage = null, ?int $nextPage = null): string
    {
        $headers = [];
        if ($previousPage) {
            $previousPageUrl = $this->getProductsAdminApiPaginationUrl($previousPage);
            $headers[] = "<$previousPageUrl>; rel=\"previous\"";
        }
        if ($nextPage) {
            $nextPageUrl = $this->getProductsAdminApiPaginationUrl($nextPage);
            $headers[] = "<$nextPageUrl>; rel=\"next\"";
        }
        return (implode(', ', $headers));
    }

    /**
     * Products next or previous page URL with fields: `per_page=10&fields=test1%2Ctest2` and appends the token
     * @param int $page  Page number to access
     *
     * @return string
     */
    protected function getProductsAdminApiPaginationUrl(int $page): string
    {
        return "https://$this->domain/"
            . Context::$apiVersion
            . "/{$this->store_id}/products?per_page=10&fields=test1%2Ctest2&page={$page}";
    }
}
