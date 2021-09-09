<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

class SecurityController extends AbstractFOSRestController
{
/**
 * @Rest\Post(
 *      path = "/api/login_check",
 *      name = "api_login_check"
 * )
 */
    public function loginCheck()
    {
    }
}