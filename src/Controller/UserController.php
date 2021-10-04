<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\User;
use App\Exception\RessourceValidationException;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use App\Service\PaginationPageService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use App\Entity\PaginationPage;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Rest\Route("/api")
 */
class UserController extends AbstractFOSRestController
{
    /**
     * All Users list.
     * @Rest\Get(
     *     path = "/users",
     *     name = "app_all_users_list",
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
     *     description="Max number of user per page."
     * )
     * @Rest\QueryParam(
     *     name="page",
     *     requirements="\d+",
     *     default="1",
     *     description="The pagination page."
     * )
     * @Rest\QueryParam(
     *     name="company",
     *     requirements="\d+",
     *     nullable=true,
     *     description="The users company id."
     * )
     * @OA\Get (
     *     description="All Users list",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="Success -> List of users",
     *         @OA\JsonContent(
     *             type= "object",
     *             @OA\Property(
     *                 property="page",
     *                 ref=@Model(type=PaginationPage::class, groups={"users_list"})
     *             ),
     *             @OA\Property(
     *                 property="users",
     *                 type="array",
     *                 @OA\Items(
     *                 ref=@Model(type=User::class, groups={"users_list"}),
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
     *          name="company",
     *          in="query",
     *          @OA\Schema(type="integer > 0", minimum=1),
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
     *     ),
     * )
     */
    #[Security("is_granted('ROLE_ADMIN')")]
    public function getAllUsersList(UserRepository $userRepository, CompanyRepository $companyRepository, ParamFetcherInterface $paramFetcher, CacheInterface $cache, Request $request, PaginationPageService $paginationPageService): Response
    {
        $response = $cache->get(
            'users-list-' . $paramFetcher->get("company") . "-" . $paramFetcher->get("keyword") . "-" . $paramFetcher->get("order") . "-" . $paramFetcher->get("limit") . "-" .  $paramFetcher->get("page"),
            function (ItemInterface $item) use ($paramFetcher, $userRepository, $companyRepository, $request, $paginationPageService) {
                $item->expiresAfter(3600);

                $company = null;
                if ($paramFetcher->get("company")) {
                    $company = $companyRepository->find($paramFetcher->get("company"));
                    if (!$company) {
                        throw new NotFoundHttpException("Invalid company ID");
                    }
                }

                $pager = $userRepository->search(
                    $company,
                    $paramFetcher->get("keyword"),
                    $paramFetcher->get("order"),
                    $paramFetcher->get("limit"),
                    $paramFetcher->get("page")
                );

                $parameters = $paramFetcher->all();

                return $paginationPageService->generatePage($request->get("_route"), $parameters, $pager, "users");
            }
        );

        return new Response($response);
    }
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
     *     description="Max number of user per page."
     * )
     * @Rest\QueryParam(
     *     name="page",
     *     requirements="\d+",
     *     default="1",
     *     description="The pagination page."
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
     *                 ref=@Model(type=PaginationPage::class, groups={"users_list"})
     *             ),
     *             @OA\Property(
     *                 property="users",
     *                 type="array",
     *                 @OA\Items(
     *                 ref=@Model(type=User::class, groups={"users_list"}),
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
     *     ),
     *     @OA\Parameter(
     *          name="company_id",
     *          required= true,
     *          @OA\Schema(type="integer", minimum=1),
     *          in="path",
     *          description="Company's ID."
     *     )
     * )
     */
    #[ParamConverter("company", options: ['mapping' => ['company_id' => 'id']])]
    public function getUsersList(Company $company, UserRepository $userRepository, ParamFetcherInterface $paramFetcher, CacheInterface $cache, Request $request, PaginationPageService $paginationPageService): Response
    {
        if(!$this->isGranted('ROLE_ADMIN') && $this->getUser()->getCompany() !== $company) {
            throw new AccessDeniedHttpException("Access denied");
        }
        $response = $cache->get(
            'users-list-' . $company->getId() . "-" .$paramFetcher->get("keyword") . "-" . $paramFetcher->get("order") . "-" . $paramFetcher->get("limit") . "-" .  $paramFetcher->get("page"),
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

                return $paginationPageService->generatePage($request->get("_route"), $parameters, $pager, "users");
            }
        );
        return new Response($response);
    }

    /**
     * Create a user.
     * @Rest\Post(
     *     path = "/companies/{company_id}/users",
     *     name = "app_user_create",
     *     requirements = {"company_id"="\d+"}
     * )
     * @Rest\View(
     *     StatusCode = 201,
     *     serializerGroups = {"users_list", "user_details"}
     * )
     * @OA\Post (
     *     description="Create a new User",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=201,
     *         description="Success -> User created",
     *         @Model(type=User::class,  groups={"users_list", "user_details"}),
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
     *          name="company_id",
     *          required= true,
     *          @OA\Schema(type="integer", minimum=1),
     *          in="path",
     *          description="Company's ID."
     *     ),
     *     @OA\RequestBody(
     *          required= true,
     *          description="User",
     *          @Model(type=User::class,  groups={"user_create"}),
     *     ),
     * )
     */
    #[ParamConverter("company", options: ['mapping' => ['company_id' => 'id']])]
    #[ParamConverter(
        "user",
        options: [
            'validator' => [
                'groups' => ['user_create', 'Default']
            ],
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
     * User details.
     * @Rest\Get(
     *     path = "/companies/{company_id}/users/{user_id}",
     *     name = "app_user_show",
     *     requirements = {"company_id"="\d+", "user_id"="\d+"}
     * )
     * @Rest\View(
     *     serializerGroups = {"users_list", "user_details"}
     * )
     * @OA\Get (
     *     description="User details",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="Success -> User details",
     *         @Model(type=User::class,  groups={"users_list", "user_details"}),
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
     *         description="User not found."
     *     ),
     *     @OA\Parameter(
     *          name="company_id",
     *          required= true,
     *          @OA\Schema(type="integer", minimum=1),
     *          in="path",
     *          description="Company's ID."
     *     ),
     *     @OA\Parameter(
     *          name="user_id",
     *          required= true,
     *          @OA\Schema(type="integer", minimum=1),
     *          in="path",
     *          description="User's ID."
     *     )
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
            throw new NotFoundHttpException("Not found");
        }
        return $user;
    }

    /**
     * Edit a User.
     * @Rest\View(StatusCode = 200)
     * @Rest\Put(
     *     path = "/companies/{company_id}/users/{user_id}",
     *     name = "app_user_update",
     *     requirements = {"company_id"="\d+", "user_id"="\d+"}
     * )
     * @OA\Put (
     *     description="Edit a User",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=201,
     *         description="Success -> User updated",
     *         @Model(type=User::class, groups={"users_list", "user_details"}),
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
     *          name="company_id",
     *          required= true,
     *          @OA\Schema(type="integer", minimum=1),
     *          in="path",
     *          description="Company's ID."
     *     ),
     *     @OA\Parameter(
     *          name="user_id",
     *          required= true,
     *          @OA\Schema(type="integer", minimum=1),
     *          in="path",
     *          description="User's ID."
     *     ),
     *     @OA\RequestBody(
     *          required= true,
     *          description="User",
     *          @Model(type=User::class,  groups={"user_create"}),
     *     ),
     * )
     */

    #[ParamConverter("company", options: ['mapping' => ['company_id' => 'id']])]
    #[ParamConverter("user", options: ['mapping' => ['user_id' => 'id']])]
    #[ParamConverter(
        "updatedUser",
        options: [
            'validator' => [
                'groups' => ['user_edit', 'Default']
            ],
            'deserializationContext' => [
                'groups' => ['user_create']
            ]
        ],
        converter: "fos_rest.request_body"
    )]
    public function editUser(User $user, Company $company, User $updatedUser, ConstraintViolationList $violations, EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher): User
    {
        if(!$this->isGranted('ROLE_ADMIN') && $this->getUser()->getCompany() !== $company) {
            throw new AccessDeniedHttpException("Access denied");
        }

        if (count($violations)) {
            $exception = new RessourceValidationException();
            foreach ($violations as $violation) {
                $exception->addError($violation->getPropertyPath(), $violation->getMessage());
            }
            throw $exception;
        }

        $user->update($updatedUser, $passwordHasher);
        $manager->flush();
        return $user;
    }

    /**
     * Delete an User
     * @Rest\View(StatusCode = 204)
     * @Rest\Delete(
     *     path = "/companies/{company_id}/users/{user_id}",
     *     name = "app_user_delete",
     *     requirements = {"company_id"="\d+", "user_id"="\d+"}
     * )
     * @OA\Delete (
     *     description="Delete an User",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=204,
     *         description="Success -> User deleted",
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
     *         description="Invalid User."
     *     ),
     *     @OA\Parameter(
     *          name="company_id",
     *          required= true,
     *          @OA\Schema(type="integer", minimum=1),
     *          in="path",
     *          description="Company's ID."
     *     ),
     *     @OA\Parameter(
     *          name="user_id",
     *          required= true,
     *          @OA\Schema(type="integer", minimum=1),
     *          in="path",
     *          description="User's ID."
     *     )
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