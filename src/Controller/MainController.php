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
     * @IsGranted("ROLE_USER")
     */
    public function showLinks(Request $request)
    {
        $detail = 'You are logged in as '.$this->getUser()->getUsername();

        $data = [
            'title' => 'Logged in',
            'detail' => $detail,
        ];

        $data = $this->links->addLinks($data, $request->getPathInfo(), true);

        return new JsonHALResponse($data);
    }
}
