<?php


namespace App\Controller;


use App\Entity\User;
use App\Utils\JsonHALResponse;
use App\Utils\Links;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Common functions for controllers
 * */
abstract class BaseController extends AbstractController
{

    protected $links;

    public function __construct(Links $links)
    {

        $this->links = $links;
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     * @throws \Exception
     */
    protected function processForm(Request $request, FormInterface $form)
    {
        $form->submit($this->getJSONContent($request), false);
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    protected function getJSONContent(Request $request)
    {
        $content = $request->getContent();

        $content = json_decode($content, true);

        if (is_null($content)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid JSON sent');
        }


        return $content;
    }

    /**
     * @return User
     * */
    protected function getUser()
    {
        return parent::getUser();
    }
}