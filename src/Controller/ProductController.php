<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * @Rest\Route("/api")
 */
class ProductController extends AbstractFOSRestController
{
    /**
     * @Rest\Get(
     *     path = "/product/{id}",
     *     name = "app_product_show",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View()
     */
    public function getProduct(Product $product)
    {
        return $product;
    }

    /**
     * @Rest\Get(
     *     path = "/product",
     *     name = "app_products_list"
     * )
     * @Rest\View(
     *     serializerGroups = {"PRODUCT_LIST"}
     * )
     */
    public function getProductsList(ProductRepository $productRepository)
    {
        $products = $productRepository->findBy([], ['brand' => 'ASC', 'name' => 'ASC']);
        return $products;
    }
}