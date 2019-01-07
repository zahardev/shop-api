<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Common functions for controllers
 * */
abstract class BaseController extends AbstractController
{
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
            throw new HttpException(400, 'Invalid JSON sent', Response::HTTP_BAD_REQUEST);
        }


        return $content;
    }
}