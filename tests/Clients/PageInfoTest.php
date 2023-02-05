<?php

namespace Tiendanube\Clients;

use Tiendanube\Clients\PageInfo;
use Tiendanube\BaseTestCase;

class PageInfoTest extends BaseTestCase
{
    use PaginationTestHelper;

    public function testParsePreviousAndNextUrlsFromLinkHeaderAndFields()
    {
        $link = $this->getProductsLinkHeader(1, 3);

        $pageInfo = PageInfo::fromLinkHeader($link);

        $this->assertEquals(
            new PageInfo(
                ['test1', 'test2'],
                $this->getProductsAdminApiPaginationUrl(1),
                $this->getProductsAdminApiPaginationUrl(3)
            ),
            $pageInfo
        );
        $this->assertEquals(['test1', 'test2'], $pageInfo->getFields());
    }

    public function testPreviousAndNextPageQueries()
    {
        $pageInfo = new PageInfo(
            ['test1', 'test2'],
            $this->getProductsAdminApiPaginationUrl(1),
            $this->getProductsAdminApiPaginationUrl(3)
        );

        $this->assertEquals(
            ["per_page" => "10", "fields" => 'test1,test2', 'page' => 1],
            $pageInfo->getPreviousPageQuery()
        );
        $this->assertEquals(
            ["per_page" => "10", "fields" => 'test1,test2', 'page' => 3],
            $pageInfo->getNextPageQuery()
        );
    }
}
