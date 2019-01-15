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
            'password' => $dummy['password'] . '!wrong!',
        ];

        $response = $this->sendRequest('POST', '/token', json_encode($data));

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertJsonProblemContentType($response);

        $data = $this->getResponseContent($response);
        $this->assertInternalType('array', $data);
        $this->assertAllPropertiesExist($data, ['status', 'type', 'title', 'detail']);
    }
}
