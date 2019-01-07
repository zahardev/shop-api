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
}
