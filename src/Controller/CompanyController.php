<?php

namespace App\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Service\PaginationPageService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use App\Entity\PaginationPage;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Rest\Route("/api")
 */
class CompanyController extends AbstractFOSRestController
{
    /**
     * BileMo's Companies list.
     * @Rest\Get(
     *     path = "/companies",
     *     name = "app_companies_list"
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
     *     description="Max number of company per page."
     * )
     * @Rest\QueryParam(
     *     name="page",
     *     requirements="\d+",
     *     default="1",
     *     description="The pagination page."
     * )
     * @OA\Get (
     *     description="<b>Resticted to Admins</b><br>Companies list",
     *     tags={"Companies"},
     *     @OA\Response(
     *         response=200,
     *         description="Success -> Companies list",
     *         @OA\JsonContent(
     *             type= "object",
     *             @OA\Property(
     *                 property="page",
     *                 ref=@Model(type=PaginationPage::class, groups={"companies_list"})
     *             ),
     *             @OA\Property(
     *                 property="companies",
     *                 type="array",
     *                 @OA\Items(
     *                 ref=@Model(type=Company::class, groups={"companies_list"}),
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
    #[Security("is_granted('ROLE_ADMIN')")]
    public function getCompaniesList(ParamFetcherInterface $paramFetcher, CompanyRepository $companyRepository, CacheInterface $cache, Request $request, PaginationPageService $paginationPageService): Response
    {
        $response = $cache->get(
            'companies-list-' . $paramFetcher->get("keyword") . "-" . $paramFetcher->get("order") . "-" . $paramFetcher->get("limit") . "-" .  $paramFetcher->get("page"),
            function (ItemInterface $item) use ($paramFetcher, $companyRepository, $request, $paginationPageService) {
                $item->expiresAfter(3600);

                $pager = $companyRepository->search(
                    $paramFetcher->get("keyword"),
                    $paramFetcher->get("order"),
                    $paramFetcher->get("limit"),
                    $paramFetcher->get("page")
                );

                return $paginationPageService->generatePage($request->get("_route"), $paramFetcher->all(), $pager, "companies");
            }
        );

        return new Response($response);
    }

    /**
     * Company details.
     * @Rest\Get(
     *     path = "/companies/{id}",
     *     name = "app_company_show",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(
     *     serializerGroups = {"company_details"}
     * )
     * @OA\Get (
     *     description="Company details",
     *     tags={"Companies"},
     *     @OA\Response(
     *         response=200,
     *         description="Success -> Company details",
     *         @Model(type=Company::class),
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Company not found."
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
     *          description="Company's ID."
     *     )
     * )
     */
    public function getCompanyDetails(Company $company): Company
    {
        if(!$this->isGranted('ROLE_ADMIN') && $this->getUser()->getCompany() !== $company) {
            throw new AccessDeniedHttpException("Access denied");
        }
        return $company;
    }
}