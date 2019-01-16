<?php


namespace App\Utils;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class Links
{
    private $authorizationChecker;


    public function addLinks(array $data, string $selfLink, $isLoggedIn = true)
    {
        $links = [
            '_links' => $this->getLinks($selfLink, $isLoggedIn),
        ];

        return array_merge($data, $links);
    }

    public function getLinks(string $selfLink, bool $isLoggedIn)
    {
        $links = $isLoggedIn ? $this->getUserLinks() : $this->getAnonymousLinks();

        return $this->addSelfLink($links, $selfLink);
    }

    public function addSelfLink($links, string $selfLink)
    {
        return array_merge(
            $links,
            [
                'self' => [
                    'href' => $selfLink,
                ],
            ]
        );
    }

    public function getAnonymousLinks()
    {
        return [
            'token' => [
                'href' => '/token',
                'methods' => [
                    'method' => 'POST',
                    'content' => [
                        'username' => '%username%',
                        'password' => '%password%',
                    ],
                ],
            ],
        ];

    }


    public function getUserLinks()
    {
        $userLinks = [
            'products' => [
                'href' => '/products',
                'methods' => [
                    [
                        'method' => 'GET',
                    ],
                    [
                        'method' => 'POST',
                        'content' => [
                            'name' => '%name%',
                            'barcode' => '%barcode%',
                            'cost' => '%cost%',
                            'vatClass' => '%vatClass%',
                        ],
                    ],
                ],
            ],
            'product' => [
                'href' => '/products{?barcode}',
                'templated' => true,
                'methods' => [
                    [
                        'method' => 'GET',
                    ],
                ],
            ],
            'receipts' => [
                'href' => '/receipts',
                'methods' => [
                    [
                        'method' => 'POST',
                    ],
                ],
            ],
            'receipt' => [
                'href' => '/receipts{?uuid}',
                'templated' => true,
                'methods' => [
                    [
                        'method' => 'GET',
                    ],
                    [
                        'method' => 'PATCH',
                        'content' => [
                            'op' => 'add',
                            'path' => '/items',
                            'value' => [
                                'barcode' => '%barcode_value%',
                                'quantity' => '%quantity%',
                            ],
                        ],
                    ],
                    [
                        'method' => 'PATCH',
                        'content' => [
                            'op' => 'replace',
                            'path' => '/items/last/quantity',
                            'value' => '%quantity%',
                        ],
                    ],
                ],

            ],
        ];

        $userLinks = array_merge($this->getAnonymousLinks(), $userLinks);

        return $userLinks;
    }
}