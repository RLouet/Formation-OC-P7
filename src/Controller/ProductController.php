<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\PaginationPage;
use App\Exception\RessourceValidationException;
use App\Repository\ProductRepository;
use App\Service\PaginationPageService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
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
     * @OA\Get (
     *     description="<b>Resticted to Users and Admins</b><br>Products list",
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
     *         response="401",
     *         description="Authentication required."
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Access denied."
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="No data found."
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
    public function getProductsList(ParamFetcherInterface $paramFetcher, ProductRepository $productRepository, CacheInterface $cache, Request $request, PaginationPageService $paginationPageService): Response
    {
        $response = $cache->get(
            'products-list-' . $paramFetcher->get("keyword") . "-" . $paramFetcher->get("order") . "-" . $paramFetcher->get("limit") . "-" .  $paramFetcher->get("page"),
            function (ItemInterface $item) use ($paramFetcher, $productRepository, $request, $paginationPageService) {
                $item->expiresAfter(3600);

                $pager = $productRepository->search(
                    $paramFetcher->get("keyword"),
                    $paramFetcher->get("order"),
                    $paramFetcher->get("limit"),
                    $paramFetcher->get("page")
                );

                return $paginationPageService->generatePage($request->get("_route"), $paramFetcher->all(), $pager, "products");
            }
        );

        return new Response($response);
    }

    /**
     * Create a product.
     * @Rest\Post(
     *     path = "/products",
     *     name = "app_product_create",
     * )
     * @Rest\View(
     *     StatusCode = 201,
     *     serializerGroups = {"product_details"}
     * )
     * @OA\Post (
     *     description="<b>Resticted to Admins</b><br>Create a new Product",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=201,
     *         description="Success -> Product created",
     *         @Model(type=Product::class, groups={"product_details"}),
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad request."
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Authentication required."
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Access denied."
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Invalid Url."
     *     ),
     *     @OA\RequestBody(
     *          required= true,
     *          description="Product",
     *          @Model(type=Product::class,  groups={"product_create"}),
     *     ),
     * )
     */
    #[ParamConverter(
        "product",
        options: [
            'validator' => [
                'groups' => ['product_create', 'Default']
            ],
            'deserializationContext' => [
                'groups' => ['product_create']
            ]
        ],
        converter: "fos_rest.request_body"
    )]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function createProduct(Product $product, ConstraintViolationList $violations, EntityManagerInterface $entityManager): View
    {
        if (count($violations)) {
            $exception = new RessourceValidationException();
            foreach ($violations as $violation) {
                $exception->addError($violation->getPropertyPath(), $violation->getMessage());
            }
            throw $exception;
        }

        $entityManager->persist($product);
        $entityManager->flush();
        return $this->view(
            $product,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_product_show', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL)]
        );
    }

    /**
     * Product details.
     * @Rest\Get(
     *     path = "/products/{id}",
     *     name = "app_product_show",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(
     *     serializerGroups = {"product_details"}
     * )
     * @OA\Get (
     *     description="<b>Resticted to Users and Admins</b><br>Product details",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="Success -> Product details",
     *         @Model(type=Product::class, groups={"product_details"}),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Authentication required."
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Access denied."
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Product not found."
     *     ),
     *     @OA\Parameter(
     *          name="id",
     *          required= true,
     *          @OA\Schema(type="integer"),
     *          in="path",
     *          description="Product's ID."
     *     )
     * )
     */
    public function getProductDetails(Product $product): Product
    {
        return $product;
    }

    /**
     * Edit a Product.
     * @Rest\View(
     *     StatusCode = 200,
     *     serializerGroups = {"product_details"}
     *     )
     * @Rest\Put(
     *     path = "/products/{id}",
     *     name = "app_product_update",
     *     requirements = {"id"="\d+"}
     * )
     * @OA\Put (
     *     description="<b>Resticted to Admins</b><br>Edit a Product",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="Success -> Product updated",
     *         @Model(type=Product::class, groups={"product_details"}),
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad request."
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Authentication required."
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Access denied."
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Invalid Url."
     *     ),
     *     @OA\Parameter(
     *          name="id",
     *          required= true,
     *          @OA\Schema(type="integer", minimum=1),
     *          in="path",
     *          description="Product's ID."
     *     ),
     *     @OA\RequestBody(
     *          required= true,
     *          description="Product",
     *          @Model(type=Product::class,  groups={"product_create"}),
     *     ),
     * )
     */
    #[ParamConverter(
        "updatedProduct",
        options: [
            'validator' => [
                'groups' => ['product_edit', 'Default']
            ],
            'deserializationContext' => [
                'groups' => ['product_create']
            ]
        ],
        converter: "fos_rest.request_body"
    )]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function editProduct(Product $product, Product $updatedProduct, ConstraintViolationList $violations, EntityManagerInterface $manager): Product
    {
        if (count($violations)) {
            $exception = new RessourceValidationException();
            foreach ($violations as $violation) {
                $exception->addError($violation->getPropertyPath(), $violation->getMessage());
            }
            throw $exception;
        }

        $product->update($updatedProduct);
        $manager->flush();
        return $product;
    }

    /**
     * Delete a product.
     * @Rest\View(StatusCode = 204)
     * @Rest\Delete(
     *     path = "/products/{id}",
     *     name = "app_product_delete",
     *     requirements = {"id"="\d+"}
     * )
     * @OA\Delete (
     *     description="<b>Resticted to Admins</b><br>Delete a Product",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=204,
     *         description="Success -> Product deleted",
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad request."
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Authentication required."
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Access denied."
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Invalid Product."
     *     ),
     *     @OA\Parameter(
     *          name="id",
     *          required= true,
     *          @OA\Schema(type="integer", minimum=1),
     *          in="path",
     *          description="Product's ID."
     *     )
     * )
     */
    #[Security("is_granted('ROLE_ADMIN')")]
    public function deleteProduct(Product $product, EntityManagerInterface $manager)
    {
        $manager->remove($product);
        $manager->flush();
        return null;
    }
}