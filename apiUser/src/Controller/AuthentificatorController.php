<?php
namespace App\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Document\Credentials;
use App\Document\User;
use App\Form\PostTokenType;
use App\Serializer\FormErrorsSerializer;

/**
 * @Route("api/token")
 */
class AuthentificatorController extends AbstractController
{
    const INVALID_CREDENTIALS = 'Your credentials are invalid';
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @Route("", name = "post_token", methods = {"POST"})
     */
    public function postTokenAction(Request $request, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $credentials = new Credentials();
        $postedCredentials = json_decode($request->getContent(), true);
        $form = $this->createForm(PostTokenType::class, $credentials);
        $form->submit($postedCredentials);

        if (!$form->isValid())
        {
			$response = new JsonResponse();
			$response->setStatusCode(400);
			$response->setContent('
        	{
				  "@context": "/api/contexts/Error",
				  "@type": "hydra:Error",
				  "hydra:title": "An error occured",
				  "hydra:description": '.json_encode((new FormErrorsSerializer())->getSerializedErrorsFromForm($form)).'
			}');
			return $response;
        }

        $user = $this->dm->getRepository(User::class)->findByUsernameOrEmail($credentials->getLogin());
        if (empty($user) || $user->getActive() == false)
        {
        	$response = new JsonResponse();
        	$response->setStatusCode(400);
        	$response->setContent('
        	{
				  "@context": "/api/contexts/Error",
				  "@type": "hydra:Error",
				  "hydra:title": "An error occured",
				  "hydra:description": "'.self::INVALID_CREDENTIALS.'"
			}');
        	return $response;
        }

        $isPasswordValid = $encoder->isPasswordValid($user, $credentials->getPassword());
        if (!$isPasswordValid)
        {
            $user->setLastFailedLogin(new \DateTime('now'));
            $this->dm->flush();

			$response = new JsonResponse();
			$response->setStatusCode(400);
			$response->setContent('
        	{
				  "@context": "/api/contexts/Error",
				  "@type": "hydra:Error",
				  "hydra:title": "An error occured",
				  "hydra:description": "'.self::INVALID_CREDENTIALS.'"
			}');
			return $response;
        }

		$secret = $_ENV['APP_SECRET'];

        $date = $credentials->getRememberMe() === true ? '+30 days' :'+1 day';

        $token = [
        	'id' => '/api/users/'.$user->getId(),
			'exp' => date_create($date)->format('U'),
			"roles"=>$user->getRoles(),
			'kind'=>"USER_AUTH"
		];

        $user->setLastLogin(new \DateTime('now'));
        $this->dm->flush();

		$response = new JsonResponse();
        $response->setStatusCode(201);
        $response->setData(['token'=>JWT::encode($token, $secret)]);
        return $response;
    }
}
