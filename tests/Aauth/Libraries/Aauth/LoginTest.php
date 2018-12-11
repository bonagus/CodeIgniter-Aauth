<?php namespace Tests\Aauth\Libraries\Aauth;

use Config\Aauth as AauthConfig;
use Config\App;
use Config\Logger;
use Config\Services;
use Tests\Support\Log\TestLogger;
use Tests\Support\HTTP\MockResponse;
use Tests\Support\Session\MockSession;
use CodeIgniter\Session\Handlers\FileHandler;
use CodeIgniter\Test\CIDatabaseTestCase;
use App\Libraries\Aauth;
use App\Models\Aauth\UserVariableModel;
use App\Models\Aauth\LoginTokenModel;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class LoginTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $basePath = TESTPATH . '../application' . 'Database/Migrations';

	protected $namespace = 'App';

    public function setUp()
    {
        parent::setUp();

        Services::injectMock('response', new MockResponse(new App()));
        $this->response = service('response');
	    $this->library = new Aauth(null, true);
        $_COOKIE = [];
        $_SESSION = [];
    }

    public function tearDown()
    {

    }

    protected function getInstance($options=[])
    {
        $defaults = [
			'sessionDriver' => 'CodeIgniter\Session\Handlers\FileHandler',
			'sessionCookieName' => 'ci_session',
			'sessionExpiration' => 7200,
			'sessionSavePath' => 'null',
			'sessionMatchIP' => false,
			'sessionTimeToUpdate' => 300,
			'sessionRegenerateDestroy' => false,
			'cookieDomain' => '',
			'cookiePrefix' => '',
			'cookiePath' => '/',
			'cookieSecure' => false,
        ];

        $config = (object)$defaults;

        $session = new MockSession(new FileHandler($config, Services::request()->getIPAddress()), $config);
        $session->setLogger(new TestLogger(new Logger()));
        $session->start();

        return $session;
    }

	//--------------------------------------------------------------------

    public function testLoginEmailTrue()
    {
        $session = $this->getInstance();
	    $this->library = new Aauth(null, $session);
	    $this->assertTrue($this->library->login('admin@example.com', 'password123456'));
    }

    public function testLoginFalseEmailFailedEmail()
    {
        $session = $this->getInstance();
	    $this->library = new Aauth(null, $session);
	    $this->assertFalse($this->library->login('adminaexample.com', 'password123456'));
	    $this->assertEquals(lang('Aauth.loginFailedEmail'), $this->library->getErrorsArray()[0]);
    }

    public function testLoginFalseEmailFailedPassword()
    {
        $session = $this->getInstance();
	    $this->library = new Aauth(null, $session);
	    $this->assertFalse($this->library->login('admin@example.com', 'passwor'));
	    $this->assertEquals(lang('Aauth.loginFailedEmail'), $this->library->getErrorsArray()[0]);
    }

    public function testLoginFalseEmailNotFound()
    {
        $session = $this->getInstance();
	    $this->library = new Aauth(null, $session);
	    $this->assertFalse($this->library->login('admina@example.com', 'password123456'));
	    $this->assertEquals(lang('Aauth.notFoundUser'), $this->library->getErrorsArray()[0]);
    }

    public function testLoginUsernameTrue()
    {
        $session = $this->getInstance();
        $config = new AauthConfig();
        $config->loginUseUsername = true;
	    $this->library = new Aauth($config, $session);
	    $this->assertTrue($this->library->login('admin', 'password123456'));
    }

    public function testLoginFalseUsernameFailedPassword()
    {
        $session = $this->getInstance();
        $config = new AauthConfig();
        $config->loginUseUsername = true;
	    $this->library = new Aauth($config, $session);
	    $this->assertFalse($this->library->login('admin', 'passwor'));
	    $this->assertEquals(lang('Aauth.loginFailedUsername'), $this->library->getErrorsArray()[0]);
    }

    public function testLoginFalseUsernameNotFound()
    {
        $session = $this->getInstance();
        $config = new AauthConfig();
        $config->loginUseUsername = true;
	    $this->library = new Aauth($config, $session);
	    $this->assertFalse($this->library->login('user99', 'password123456'));
	    $this->assertEquals(lang('Aauth.notFoundUser'), $this->library->getErrorsArray()[0]);
        $config->loginUseUsername = false;
    }

    public function testLoginFalseNotVerified()
    {
        $session = $this->getInstance();
	    $this->library = new Aauth(null, $session);
    	$userVariableModel = new UserVariableModel();
	    $userVariableModel->save(1, 'verification_code', '12345678', true);
	    $this->assertFalse($this->library->login('admin@example.com', 'password123456'));
	    $this->assertEquals(lang('Aauth.notVerified'), $this->library->getErrorsArray()[0]);
    }

    public function testLoginFalseBanned()
    {
        $session = $this->getInstance();
	    $this->library = new Aauth(null, $session);
	    $this->library->banUser(1);
	    $this->assertFalse($this->library->login('admin@example.com', 'password123456'));
	    $this->assertEquals(lang('Aauth.invalidUserBanned'), $this->library->getErrorsArray()[0]);
    }

    public function testLoginFalse()
    {
        $session = $this->getInstance();
	    $this->library = new Aauth(null, $session);
	    $this->assertFalse($this->library->login('admin@example.com', 'password1234567'));
	    $this->assertEquals(lang('Aauth.loginFailedAll'), $this->library->getErrorsArray()[0]);
    }

    public function testLoginFalseLoginAttemptsExceeded()
    {
        $session = $this->getInstance();
	    $this->library = new Aauth(null, $session);
	    $this->library->login('admina@example.com', 'password123456');
	    $this->library->login('admina@example.com', 'password123456');
	    $this->library->login('admina@example.com', 'password123456');
	    $this->library->login('admina@example.com', 'password123456');
	    $this->library->login('admina@example.com', 'password123456');
	    $this->library->login('admina@example.com', 'password123456');
	    $this->library->login('admina@example.com', 'password123456');
	    $this->library->login('admina@example.com', 'password123456');
	    $this->library->login('admina@example.com', 'password123456');
	    $this->library->clearErrors();
	    $this->assertFalse($this->library->login('admina@example.com', 'password123456'));
	    $this->assertEquals(lang('Aauth.loginAttemptsExceeded'), $this->library->getErrorsArray()[0]);
    }

	public function testIsLoggedIn()
	{
        $session = $this->getInstance();
	    $this->library = new Aauth(null, $session);
		$session->set('user', [
            'loggedIn' => true,
        ]);
	    $this->assertTrue($this->library->isLoggedIn());
	}

	public function testLogout()
	{
        $session = $this->getInstance();
	    $this->library = new Aauth(null, $session);
		$session->set('user', [
            'loggedIn' => true,
        ]);
	    $this->assertTrue($this->library->isLoggedIn());
	    $this->library->logout();
	    $this->library = new Aauth(null, $session);
	    $this->assertFalse($this->library->isLoggedIn());
	}
}