<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @Rest\Route("/api")
 */
class ProductController extends AbstractFOSRestController
{
    /**
     * @Rest\Get(
     *     path = "/products/{id}",
     *     name = "app_product_show",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View()
     */
    public function getProductDetails(Product $product, CacheInterface $cache)
    {
        return $cache->get(
            'product-' . $product->getId(),
            function (ItemInterface $item) use ($product) {
                $item->expiresAfter(3600);
                return $product;
            }
        );
    }

    /**
     * @Rest\Get(
     *     path = "/products",
     *     name = "app_products_list"
     * )
     * @Rest\QueryParam(
     *     name="keyword",
     *     requirements="[a-zA-Z0-9]+",
     *     nullable=true,
     *     description="The keyword to search for."
     * )
     * @Rest\QueryParam(
     *     name="order",
     *     requirements="asc|desc",
     *     default="asc",
     *     description="Sort order (asc or desc)"
     * )
     * @Rest\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     default="20",
     *     description="Max number of product per page."
     * )
     * @Rest\QueryParam(
     *     name="page",
     *     requirements="\d+",
     *     default="1",
     *     description="The pagination page."
     * )
     * @Rest\View(
     *     serializerGroups = {"products_list"}
     * )
     */
    public function getProductsList(ParamFetcherInterface $paramFetcher, ProductRepository $productRepository, CacheInterface $cache)
    {

        return $cache->get(
            'product-list-' . $paramFetcher->get("keyword") . "-" . $paramFetcher->get("order") . "-" . $paramFetcher->get("limit") . "-" .  $paramFetcher->get("page"),
            function (ItemInterface $item) use ($paramFetcher, $productRepository) {
                $item->expiresAfter(3600);

                $pager = $productRepository->search(
                    $paramFetcher->get("keyword"),
                    $paramFetcher->get("order"),
                    $paramFetcher->get("limit"),
                    $paramFetcher->get("page")
                );

                return [
                    "data" => $pager->getCurrentPageResults(),
                    "meta" => [
                        "limit" => $paramFetcher->get("limit"),
                        "current items" => count($pager->getCurrentPageResults()),
                        "total items" => $pager->getNbResults(),
                        "current page" => $pager->getCurrentPage(),
                        "total pages" => $pager->getNbPages()
                    ]
                ];
            }
        );
    }
}