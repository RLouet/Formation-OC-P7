<?php

namespace App\Controller;

use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class SecurityController extends AbstractFOSRestController
{
/**
 * Get Authentication Token
 * @Rest\Post(
 *      path = "/api/login_check",
 *      name = "api_login_check"
 * )
 * @OA\Post (
 *     description="Generate Token",
 *     tags={"Authentication"},
 *     @OA\Response(
 *         response=200,
 *         description="Success -> Token",
 *         @OA\JsonContent(
 *             type= "object",
 *             @OA\Property(
 *                 property="token",
 *                 type="string"
 *             ),
 *         )
 *     ),
 *     @OA\Response(
 *         response="400",
 *         description="Bad request."
 *     ),
 *     @OA\Response(
 *         response="401",
 *         description="Invalid credentials."
 *     ),
 *     @OA\RequestBody(
 *          required= true,
 *          description="User",
 *          @Model(type=User::class,  groups={"user_login"}),
 *     ),
 * )
 */
    public function loginCheck()
    {
    }
}