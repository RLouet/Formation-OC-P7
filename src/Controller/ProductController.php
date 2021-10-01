<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\PaginationPage;
use App\Repository\ProductRepository;
use App\Service\PaginationPageService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Rest\Route("/api")
 */
class ProductController extends AbstractFOSRestController
{
    /**
     * BileMo's Products list.
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
     * @OA\Get (
     *     description="Products list",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="Success -> Products list",
     *         @OA\JsonContent(
     *             type= "object",
     *             @OA\Property(
     *                 property="page",
     *                 ref=@Model(type=PaginationPage::class, groups={"products_list"})
     *             ),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                 ref=@Model(type=Product::class, groups={"products_list"}),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="No data found."
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Authentication required."
     *     ),
     *     @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          @OA\Schema(type="integer > 0", minimum=1),
     *     ),
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          @OA\Schema(type="integer > 0", minimum=1),
     *     ),
     *     @OA\Parameter(
     *          name="order",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              enum={"asc", "desc"}
     *          ),
     *     )
     * )
     */
    public function getProductsList(ParamFetcherInterface $paramFetcher, ProductRepository $productRepository, CacheInterface $cache, Request $request, PaginationPageService $paginationPageService)
    {
        return $cache->get(
            'product-list-' . $paramFetcher->get("keyword") . "-" . $paramFetcher->get("order") . "-" . $paramFetcher->get("limit") . "-" .  $paramFetcher->get("page"),
            function (ItemInterface $item) use ($paramFetcher, $productRepository, $request, $paginationPageService) {
                $item->expiresAfter(3600);

                $pager = $productRepository->search(
                    $paramFetcher->get("keyword"),
                    $paramFetcher->get("order"),
                    $paramFetcher->get("limit"),
                    $paramFetcher->get("page")
                );

                $page = $paginationPageService->generatePage($request->get("_route"), $paramFetcher->all(), $pager);

                return [
                    "_page" => $page,
                    "products" => $pager->getCurrentPageResults()
                ];
            }
        );
    }

    /**
     * Product details.
     * @Rest\Get(
     *     path = "/products/{id}",
     *     name = "app_product_show",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View()
     * @OA\Get (
     *     description="Product details",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="Success -> Product details",
     *         @Model(type=Product::class),
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Product not found."
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Authentication required."
     *     ),
     *     @OA\Parameter(
     *          name="id",
     *          required= true,
     *          @OA\Schema(type="integer"),
     *          in="path",
     *          description="ID du produit."
     *     )
     * )
     */
    public function getProductDetails(Product $product): Product
    {
        return $product;
    }
}