<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\User;
use App\Entity\PaginationPage;
use App\Exception\RessourceValidationException;
use App\Repository\UserRepository;
use App\Service\PaginationPageService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Rest\Route("/api")
 */
class UserController extends AbstractFOSRestController
{
    /**
     * Company's Users list.
     * @Rest\Get(
     *     path = "/companies/{company_id}/users",
     *     name = "app_users_list",
     *     requirements = {"company_id"="\d+"}
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
     *     serializerGroups = {"user_list"}
     * )
     * @OA\Get (
     *     description="Users list",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="Success -> List of users",
     *         @OA\JsonContent(
     *             type= "object",
     *             @OA\Property(
     *                 property="page",
     *                 ref=@Model(type=PaginationPage::class, groups={"user_list"})
     *             ),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                 ref=@Model(type=User::class, groups={"user_list"}),
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
    #[ParamConverter("company", options: ['mapping' => ['company_id' => 'id']])]
    public function getUsersList(Company $company, UserRepository $userRepository, ParamFetcherInterface $paramFetcher, CacheInterface $cache, Request $request, PaginationPageService $paginationPageService)
    {
        if(!$this->isGranted('ROLE_ADMIN') && $this->getUser()->getCompany() !== $company) {
            throw new AccessDeniedHttpException("Access denied");
        }
        return $cache->get(
            'product-list-' . $paramFetcher->get("keyword") . "-" . $paramFetcher->get("order") . "-" . $paramFetcher->get("limit") . "-" .  $paramFetcher->get("page"),
            function (ItemInterface $item) use ($paramFetcher, $userRepository, $company, $request, $paginationPageService) {
                $item->expiresAfter(3600);

                $pager = $userRepository->search(
                    $company,
                    $paramFetcher->get("keyword"),
                    $paramFetcher->get("order"),
                    $paramFetcher->get("limit"),
                    $paramFetcher->get("page")
                );

                $parameters = $paramFetcher->all();
                $parameters["company_id"] = $company->getId();

                $page = $paginationPageService->generatePage($request->get("_route"), $parameters, $pager);

                return [
                    "_page" => $page,
                    "products" => $pager->getCurrentPageResults()
                ];
            }
        );
    }

    /**
     * @Rest\Get(
     *     path = "/companies/{company_id}/users/{user_id}",
     *     name = "app_user_show",
     *     requirements = {"company_id"="\d+", "user_id"="\d+"}
     * )
     * @Rest\View(
     *     serializerGroups = {"user_list", "user_details"}
     * )
     */
    #[ParamConverter("company", options: ['mapping' => ['company_id' => 'id']])]
    #[ParamConverter("user", options: ['mapping' => ['user_id' => 'id']])]
    public function getUserDetails(Company $company, User $user): User
    {
        if(!$this->isGranted('ROLE_ADMIN') && $this->getUser()->getCompany() !== $company) {
            throw new AccessDeniedHttpException("Access denied");
        }
        if ($user->getCompany() !== $company) {
            throw new BadRequestHttpException("Not found");
        }
        return $user;
    }

    /**
     * @Rest\Post(
     *     path = "/companies/{company_id}/users",
     *     name = "app_user_create",
     *     requirements = {"company_id"="\d+"}
     * )
     * @Rest\View(
     *     StatusCode = 201,
     *     serializerGroups = {"user_list", "user_details"}
     * )
     */
    #[ParamConverter("company", options: ['mapping' => ['company_id' => 'id']])]
    #[ParamConverter(
        "user",
        options: [
            'deserializationContext' => [
                'groups' => ['user_create']
            ]
        ],
        converter: "fos_rest.request_body")
    ]
    public function createUser(Company $company, User $user, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, ConstraintViolationList $violations): View
    {
        if(!$this->isGranted('ROLE_ADMIN') && $this->getUser()->getCompany() !== $company) {
            throw new AccessDeniedHttpException("Access denied");
        }

        $user
            ->setCompany($company)
            ->setRegistrationDate(new \DateTime())
            ->setPassword($passwordHasher->hashPassword($user, $user->getPassword()))
            ->setRoles(["ROLE_USER"])
        ;

        if (count($violations)) {
            $exception = new RessourceValidationException();
            foreach ($violations as $violation) {
                $exception->addError($violation->getPropertyPath(), $violation->getMessage());
            }
            throw $exception;
        }

        $entityManager->persist($user);
        $entityManager->flush();
        return $this->view(
            $user,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_user_show', ['company_id' => $user->getCompany()->getId(), 'user_id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL)]
        );
    }

    /**
     * @Rest\View(StatusCode = 204)
     * @Rest\Delete(
     *     path = "/companies/{company_id}/users/{user_id}",
     *     name = "app_user_delete",
     *     requirements = {"company_id"="\d+", "user_id"="\d+"}
     * )
     */
    #[ParamConverter("company", options: ['mapping' => ['company_id' => 'id']])]
    #[ParamConverter("user", options: ['mapping' => ['user_id' => 'id']])]
    public function deleteUser(Company $company, User $user, EntityManagerInterface $manager)
    {
        if(!$this->isGranted('ROLE_ADMIN') && $this->getUser()->getCompany() !== $company) {
            throw new AccessDeniedHttpException("Access denied");
        }
        if ($user->getCompany() === $company) {
            $manager->remove($user);
            $manager->flush();
            return null;
        }
        throw new BadRequestHttpException("Invalid User");
    }
}