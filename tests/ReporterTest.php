<?php

use PHPUnit\Framework\TestCase;

use App\Reporter;


class ReporterTest extends TestCase
{
    public static $reporter, $username, $password, $tokenFile, $id;

    public static function setUpBeforeClass()
    {
        self::$reporter = new Reporter();
        self::$username = getenv('APP_USERNAME');
        self::$password = getenv('APP_PASSWORD');
        self::$tokenFile = dirname(__FILE__) . '/token.json';
        self::$id = getenv('APP_USER_ID');
    }

    public function testUsernamePassword()
    {
        self::$reporter->setUsername(self::$username);
        self::$reporter->setPassword(self::$password);

        $this->assertEquals(self::$username, self::$reporter->getUsername());
        $this->assertEquals(self::$password, self::$reporter->getPassword());
        self::$reporter->setTokenFile(self::$tokenFile);

        $this->assertEquals(self::$tokenFile, self::$reporter->getTokenFile());

    }

    public function testLogin()
    {
        $this->assertTrue(self::$reporter->doLogin());
    }

    public function testTokenFile()
    {
        $this->assertFileExists(self::$tokenFile);
    }

    public function testClient()
    {

        $result = self::$reporter->getClient(self::$id);
        $this->assertTrue(isset($result["customerInfo"]));
    }

    public function testTransAction()
    {
        $result = self::$reporter->transAction(self::$id);
        $this->assertTrue(isset($result["transaction"]));
    }

    public function testTransActionReport()
    {
        $report = array(
            "fromDate" => "2018-04-10",
            "toDate" => "2019-04-10",
        );
        $result = self::$reporter->transActionReport($report);
        $this->assertEquals("APPROVED", $result["status"]);


    }

    public function testTransActionList()
    {

        $list = array(
            "fromDate" => "2018-04-10",
            "toDate" => "2019-04-10",
        );
        $result = self::$reporter->transActionList($list);
        $this->assertTrue(isset($result["from"]));


    }

}