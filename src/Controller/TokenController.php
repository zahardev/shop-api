<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class TokenController extends BaseController
{
    const TOKEN_TTL = 3600;

    private $userRepository;
    private $passEncoder;
    private $jwtEncoder; // 1 hour expiration

    public function __construct(
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passEncoder,
        JWTEncoderInterface $jwtEncoder
    ) {
        $this->userRepository = $userRepository;
        $this->passEncoder = $passEncoder;
        $this->jwtEncoder = $jwtEncoder;
    }

    /**
     * @Route("/token", methods={"POST"});
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function newTokenAction(Request $request)
    {
        $data = $this->getJSONContent($request);

        if (empty($data['username'] || empty($data['password']))) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Please provide username and password.');
        }

        $user = $this->userRepository->findOneBy(['username' => $data['username']]);

        if (empty($user)) {
            $this->createNotFoundException();
        }

        if (!$this->passEncoder->isPasswordValid($user, $data['password'])) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Invalid credentials.');
        }

        $tokenData = [
            'username' => $user->getUsername(),
            'exp' => time() + self::TOKEN_TTL,
        ];

        $token = $this->jwtEncoder->encode($tokenData);

        return new JsonResponse(['token' => $token]);
    }
}
