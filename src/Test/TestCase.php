<?php


namespace App\Test;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TestCase
 * @package App\Test
 */
class TestCase extends WebTestCase
{
    /** @var  Application $application */
    protected static $application;

    /**
     * @var Client
     */
    protected static $client;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::runCommand('doctrine:database:create');
        self::runCommand('doctrine:schema:create');
    }


    public static function tearDownAfterClass()
    {
        self::runCommand('doctrine:database:drop --force');
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        parent::setUp();

        self::runCommand('hautelook:fixtures:load -n');
    }


    protected static function runCommand($command)
    {
        $command = sprintf('%s --quiet', $command);

        return self::getApplication()->run(new StringInput($command));
    }

    protected static function getApplication()
    {
        if (null === self::$application) {
            self::$client = self::createClient();

            self::$application = new Application(self::$client->getKernel());
            self::$application->setAutoExit(false);
        }

        return self::$application;
    }


    /**
     * Calls a URI.
     *
     * @param string $method
     * @param string $uri
     * @param string|null $content
     * @return Response
     */
    protected function sendRequest( string $method, string $uri, string $content = null ){
        self::$client->request($method, $uri, array(), array(), array(), $content );

        $response = self::$client->getResponse();

        //Debug errors
        if (!$response->isSuccessful()) {
            $block = self::$client->getCrawler()->filter('h1.exception-message');
            if ($block->count()) {
                $error = $block->text();
                echo $error;
            }
        }

        return $response;
    }


    /**
     * @param Response $response
     * @return array|null
     */
    protected function getResponseContent( Response $response )
    {
        return json_decode($response->getContent(), true);
    }


    /**
     * @param Response $response
     */
    protected function assertJsonContentType(Response $response)
    {
        $contentType = $response->headers->get('content-type');
        $this->assertNotEmpty($contentType);
        $this->assertEquals('application/json', $contentType);
    }


    /**
     *
     * @param array $productData
     */
    protected function assertAllProductPropertiesExist(array $productData)
    {
        $this->assertAllPropertiesExist($productData, ['name', 'barcode', 'cost', 'vatClass']);
    }


    /**
     *
     * @param array $data
     * @param array $properties
     */
    protected function assertAllPropertiesExist(array $data, array $properties)
    {
        foreach ($properties as $key) {
            $this->assertArrayHasKey($key, $data);
        }
    }


    /**
     * @return array
     */
    protected function getNewProductData()
    {
        return [
            'name' => 'Test Product',
            'barcode' => 9999999999999,
            'cost' => 19.75,
            'vatClass' => 6,
        ];
    }


    /**
     * This product exists in the test database
     *
     * @see fixtures/dummy.yaml
     */
    protected function getDummyProductData()
    {
        return $this->getDummyProductsData()[0];
    }


    /**
     * This products exist in the test database
     *
     * @see fixtures/dummy.yaml
     *
     * */
    protected function getDummyProductsData()
    {
        return [
            [
                'name' => 'Test Product 1',
                'barcode' => 1111111111111,
                'cost' => 11.11,
                'vatClass' => 21,
            ],
            [
                'name' => 'Test Product 2',
                'barcode' => 2222222222222,
                'cost' => 22.22,
                'vatClass' => 21,
            ],
            [
                'name' => 'Test Product 3',
                'barcode' => 3333333333333,
                'cost' => 33.33,
                'vatClass' => 6,
            ],
        ];
    }

    /**
     * This product exists in the test database
     *
     * @see fixtures/dummy.yaml
     */
    protected function getDummyUnfinishedReceiptData()
    {
        return [
            'uuid' => '3f2e511d-f775-4324-9c38-17b93d8a55b0',
            'status' => 'unfinished',
        ];
    }

}
