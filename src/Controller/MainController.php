<?php


namespace App\Controller;

use App\Utils\JsonHALResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Common functions for controllers
 * */
class MainController extends BaseController
{

    /**
     * @Route("/", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function showLinks(Request $request)
    {
        $user = $this->getUser();

        if(empty($user)){
            $data = [
                'title' => 'Welcome!',
                'detail' => 'Welcome to the shop API! Please get token to be able sending requests.'
            ];
        } else {
            $data = [
                'title' => 'Logged in',
                'detail' => 'You are logged in as '.$user->getUsername(),
            ];
        }

        $data = $this->links->addLinks($data, $request->getPathInfo(), $this->isGranted('ROLE_USER'));

        return new JsonHALResponse($data);
    }
}
