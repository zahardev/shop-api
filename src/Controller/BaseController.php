<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $data = json_decode($request->getContent(), true);

        if (is_null($data)) {
            throw new \Exception('Invalid JSON sent', Response::HTTP_BAD_REQUEST);
        }

        $form->submit($data, false);
    }

}