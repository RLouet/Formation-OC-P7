<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @Rest\Route("/api")
 */
class UserController extends AbstractFOSRestController
{
    /**
     * @Rest\Get(
     *     path = "/companies/{company_id}/users",
     *     name = "app_users_list",
     *     requirements = {"company_id"="\d+"}
     * )
     * @Rest\View(
     *     serializerGroups = {"USER_LIST"}
     * )
     */
    #[ParamConverter("company", options: ['mapping' => ['company_id' => 'id']])]
    public function getUsersList(Company $company, UserRepository $userRepository)
    {
        $users = $userRepository->findBy(['company' => $company], ['lastName' => 'ASC', 'firstName' => 'ASC']);
        return $users;
    }

    /**
     * @Rest\Get(
     *     path = "/companies/{company_id}/users/{user_id}",
     *     name = "app_user_show",
     *     requirements = {"company_id"="\d+", "user_id"="\d+"}
     * )
     * @Rest\View(
     *     serializerGroups = {"USER_LIST", "USER_DETAILS"}
     * )
     */
    #[ParamConverter("company", options: ['mapping' => ['company_id' => 'id']])]
    #[ParamConverter("user", options: ['mapping' => ['user_id' => 'id']])]
    public function getUserDetails(Company $company, User $user, UserRepository $userRepository)
    {
        if ($user->getCompany() === $company) {
            return $user;
        }
        return null;
    }

    /**
     * @Rest\Post(
     *     path = "/companies/{company_id}/users",
     *     name = "app_user_create",
     *     requirements = {"company_id"="\d+"}
     * )
     * @Rest\View(
     *     StatusCode = 201,
     *     serializerGroups = {"USER_LIST", "USER_DETAILS"}
     * )
     */
    #[ParamConverter("company", options: ['mapping' => ['company_id' => 'id']])]
    #[ParamConverter(
        "user",
        options: [
            'deserializationContext' => [
                'groups' => ['USER_CREATE']
            ]
        ],
        converter: "fos_rest.request_body")
    ]
    public function createUser(Company $company, User $user, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, ConstraintViolationList $violations)
    {
        $user
            ->setCompany($company)
            ->setRegistrationDate(new \DateTime())
            ->setPassword($passwordHasher->hashPassword($user, $user->getPassword()))
            ->setRoles(["ROLE_USER"])
        ;

        if (count($violations)) {
            return $this->view($violations, Response::HTTP_BAD_REQUEST);
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
        if ($user->getCompany() === $company) {
            $manager->remove($user);
            $manager->flush();
            return null;
        }
        return $this->view("Invalid User", Response::HTTP_BAD_REQUEST);
    }
}