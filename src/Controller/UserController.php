<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\User;
use App\Repository\UserRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Rest\Route("/api")
 */
class UserController extends AbstractFOSRestController
{
    /**
     * @Rest\Get(
     *     path = "/company/{company_id}/user",
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

}