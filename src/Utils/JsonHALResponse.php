<?php


namespace App\Utils;


use Symfony\Component\HttpFoundation\JsonResponse;

class JsonHALResponse extends JsonResponse
{
    public function __construct($data = null, int $status = 200, array $headers = array(), bool $json = false)
    {
        parent::__construct($data, $status, $headers, $json);
        $this->headers->set('Content-Type', 'application/hal+json');
    }
}