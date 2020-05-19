<?php

namespace App\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Serializer\SerializerInterface;

use App\Document\User;
use App\Form\PatchCurrentUserPasswordType;
use App\Form\PatchCurrentUserType;
use Symfony\Contracts\HttpClient\ResponseInterface;

class UserController extends AbstractController
{
    private $serializer;
    private $dm;

    public function __construct(SerializerInterface $serializer, DocumentManager $dm)
    {
        $this->serializer = $serializer;
        $this->dm = $dm;
    }

    /**
     * @Route(
     *     name="api_get_users_current",
     *     path="/api/users/current",
     *     methods={"GET"},
     *     defaults={
     *         "_api_resource_class"=User::class,
     *         "_api_collection_operation_name"="get_current"
     *     }
     * )
     */
    public function getCurrentUser(Request $request): User
    {
		$credentials = $request->headers->get('Authorization');
		return $this->getCurrentUserWithToken($credentials);
    }

	/**
	 * @Route(
	 *     name="api_patch_users_current",
	 *     path="/api/users/current",
	 *     methods={"PATCH"},
	 *     defaults={
	 *         "_api_resource_class"=User::class,
	 *         "_api_collection_operation_name"="patch_current"
	 *     }
	 * )
	 */
	public function patchCurrentUser(Request $request, UserPasswordEncoderInterface $encoder): User
	{
		$credentials = $request->headers->get('Authorization');

		$user = $this->getCurrentUserWithToken($credentials);
		$data = json_decode($request->getContent(), true);
		$form = $this->createForm(PatchCurrentUserType::class, $user);
		$form->submit($data, false);

		if(!$form->isValid())
		{
			throw new BadRequestHttpException($form->getErrors(true, false));
		}

		if(empty($user->getPlainPassword()))
		{
			throw new BadRequestHttpException("plainPassword: This value is invalid.");
		}

		$passwordIsValid = $encoder->isPasswordValid($user, $user->getPlainPassword());

		if(!$passwordIsValid)
		{
			throw new BadRequestHttpException("plainPassword: This value is invalid.");
		}

		$this->dm->flush();
		return $user;
	}

	/**
	 * @Route(
	 *     name="api_patch_users_current_password",
	 *     path="/api/users/current/password",
	 *     methods={"PATCH"},
	 *     defaults={
	 *         "_api_resource_class"=User::class,
	 *         "_api_collection_operation_name"="patch_current_password"
	 *     }
	 * )
	 */
	public function patchCurrentUserPassword(Request $request, UserPasswordEncoderInterface $encoder): User
	{
		$credentials = $request->headers->get('Authorization');

		$user = $this->getCurrentUserWithToken($credentials);
		$data = json_decode($request->getContent(), true);
		$form = $this->createForm(PatchCurrentUserPasswordType::class, $user);
		$form->submit($data, true);

		if(!$form->isValid())
		{
			throw new BadRequestHttpException($form->getErrors(true, false));
		}

		if(empty($user->getPlainPassword()))
		{
			throw new BadRequestHttpException("password: This value is invalid.");
		}

		$passwordIsValid = $encoder->isPasswordValid($user, $user->getPlainPassword());

		if(!$passwordIsValid)
		{
			throw new BadRequestHttpException("password: This value is invalid.");
		}

		$this->dm->flush();
		return $user;
	}

	/**
	 * @Route(
	 *     name="api_activate_user",
	 *     path="/api/users/activate/{token}",
	 *     methods={"PATCH"}
	 * )
	 */
	public function activateUser(string $token): JsonResponse
	{
		$secret = $_ENV['APP_SECRET'];

		try
		{
			$data = JWT::decode($token, $secret, ["HS256"]);
		}
		catch(\Exception $e)
		{
			throw new CustomUserMessageAuthenticationException("Invalid token");
		}

		if($data->kind !== "USER_ACTIVATION")
		{
			throw new NotFoundHttpException();
		}

		$explodedId = explode('/',$data->id);
		$id = $explodedId[3];

		$user = $this->dm->getRepository(User::class)->findOneBy(['id'=>$id]);

		if(empty($user))
		{
			throw new NotFoundHttpException();
		}

		if($user->getActive() === true)
		{
			throw new NotFoundHttpException();
		}

		$user->setActive(true);
		$this->dm->flush();

		return new JsonResponse(null, 204);
	}

	private function getCurrentUserWithToken(?string $credentials): User
	{
		if(empty($credentials))
		{
			throw new CustomUserMessageAuthenticationException('Authentication is required');
		}

		if(0 !== strpos($credentials, 'Bearer '))
		{
			throw new CustomUserMessageAuthenticationException("Invalid token");
		}

		$credentials = substr($credentials, 7);

		$secret = $_ENV['APP_SECRET'];

		try
		{
			$data = JWT::decode($credentials, $secret, ["HS256"]);
		}
		catch(\Exception $e)
		{
			throw new CustomUserMessageAuthenticationException("Invalid token");
		}

		if($data->kind !== "USER_AUTH")
		{
			throw new CustomUserMessageAuthenticationException("Invalid token");
		}
		$explodedId = explode('/',$data->id);
		$id = $explodedId[3];

		$user = $this->dm->getRepository(User::class)->findOneBy(['id'=>$id]);
		if(empty($user))
		{
			throw new CustomUserMessageAuthenticationException("Invalid token");
		}

		if($user->getActive() === false)
		{
			throw new CustomUserMessageAuthenticationException("Your account is not active.");
		}

		return $user;
	}
}