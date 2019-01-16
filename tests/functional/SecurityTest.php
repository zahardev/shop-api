<?php


namespace App\Tests;

use App\Test\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SecurityTest
 * @package App\Tests
 */
class SecurityTest extends TestCase
{
    public function testObtainToken()
    {
        $dummy = $this->getDummyUsersData()['admin'];

        $data = [
            'username' => $dummy['username'],
            'password' => $dummy['password'],
        ];

        $response = $this->sendRequest('POST', '/token', json_encode($data));

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonContentType($response);

        $data = $this->getResponseContent($response);
        $this->assertInternalType('array', $data);
        $this->assertAllPropertiesExist($data, ['token']);
        $this->assertNotEmpty($data['token']);
    }

    public function testObtainTokenWrongCredentials()
    {
        $dummy = $this->getDummyUsersData()['admin'];

        $data = [
            'username' => $dummy['username'],
            'password' => $dummy['password'].'!wrong!',
        ];

        $response = $this->sendRequest('POST', '/token', json_encode($data));

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertJsonProblemContentType($response);

        $data = $this->getResponseContent($response);
        $this->assertInternalType('array', $data);
        $this->assertAllPropertiesExist($data, ['status', 'type', 'title', 'detail']);
    }


    public function testEndpointsRequireRoleCashRegister()
    {
        $endpoints = $this->getCashRegisterEndpoints();

        foreach ($endpoints as $endpoint) {
            $response = $this->sendUnauthorizedRequest($endpoint);
            $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode(), 'Endpoint problem: ' . print_r($endpoint, true) );

            $response = $this->sendAdminRequest($endpoint);
            $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode(), 'Endpoint problem: ' . print_r($endpoint, true) );

            $response = $this->sendCashRegisterRequest($endpoint);
            $this->assertNotEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode(), 'Endpoint problem: ' . print_r($endpoint, true) );
            $this->assertNotEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode(), 'Endpoint problem: ' . print_r($endpoint, true) );
        }
    }


    public function testEndpointsRequireRoleAdmin()
    {
        $endpoints = $this->getAdminEndpoints();

        foreach ($endpoints as $endpoint) {
            $response = $this->sendUnauthorizedRequest($endpoint);
            $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode(), 'Endpoint problem: ' . print_r($endpoint, true) );

            $response = $this->sendCashRegisterRequest($endpoint);
            $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode(), 'Endpoint problem: ' . print_r($endpoint, true) );

            $response = $this->sendAdminRequest($endpoint);
            $this->assertNotEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode(), 'Endpoint problem: ' . print_r($endpoint, true) );
            $this->assertNotEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode(), 'Endpoint problem: ' . print_r($endpoint, true) );
        }
    }


    private function sendUnauthorizedRequest(array $endpoint)
    {
        self::$client = self::createClient(); //fix saving client information
        return $this->sendEndpointRequest($endpoint, '');
    }

    private function sendCashRegisterRequest(array $endpoint)
    {
        if( empty($this->cashRegisterToken) ){
            $this->cashRegisterToken = $this->generateToken('cash_register');
        }

        return $this->sendEndpointRequest($endpoint, $this->cashRegisterToken);
    }

    private function sendAdminRequest(array $endpoint)
    {
        if( empty($this->adminToken) ){
            $this->adminToken = $this->generateToken('admin');
        }

        return $this->sendEndpointRequest($endpoint, $this->adminToken);
    }


    private function sendEndpointRequest(array $endpoint, $token = null)
    {
        $headers = ['HTTP_Authorization' => 'Bearer '.$token];

        if ('PATCH' === $endpoint['method']) {
            $response = $this->sendPatchRequest($endpoint['uri'], $endpoint['op'], $endpoint['path'], $endpoint['value'], $headers);
        } else {
            $content = isset($endpoint['content']) ? json_encode($endpoint['content']) : null;
            $response = $this->sendRequest($endpoint['method'], $endpoint['uri'], $content, $headers);
        }

        return $response;
    }



    private function getCashRegisterEndpoints()
    {
        $productData = $this->getDummyProductData();
        $receiptData = $this->getDummyReceiptData()['receipt_empty'];

        return [
            [
                'method' => 'GET',
                'uri' => '/products/'.$productData['barcode'],
            ],
            [
                'method' => 'POST',
                'uri' => '/receipts',
            ],
            [
                'method' => 'PATCH',
                'uri' => '/receipts/'.$receiptData['uuid'],
                'op' => 'add',
                'path' => '/items',
                'value' => [
                    'barcode' => $productData['barcode'],
                    'quantity' => 3,
                ],
            ],
            [
                'method' => 'GET',
                'uri' => '/receipts/'.$receiptData['uuid'],
            ],
            [
                'method' => 'PATCH',
                'uri' => '/receipts/'.$receiptData['uuid'],
                'op' => 'replace',
                'path' => '/status',
                'value' => 'finished',
            ],
        ];
    }


    private function getAdminEndpoints()
    {
        return [
            [
                'method' => 'GET',
                'uri' => '/products',
            ],
            [
                'method' => 'POST',
                'uri' => '/products',
                'content' => $this->getNewProductData(),
            ],
        ];
    }
}
