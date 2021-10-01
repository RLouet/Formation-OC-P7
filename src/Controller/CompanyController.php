<?php

namespace App\Controller;

use App\Entity\Company;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Rest\Route("/api")
 */
class CompanyController extends AbstractFOSRestController
{

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