<?php

namespace ApplicationTest\Controller;

use Core\Test\ControllerTestCase;
use Zend\Mvc\Router\Http\Segment;
use Zend\Mvc\Router\SimpleRouteStack;

class AuthControllerTest extends ControllerTestCase {
    
    /**
     * Namespace completa do Controller
     * @var string
     */
    protected $controllerFQDN = 'Application\Controller\AuthController';

    /**
     * Nome da rota. Geralmente o nome do módulo
     * @var string
     */
    protected $controllerRoute = 'application';
    
    public function mockRouteLogin() {
        $module = new \Application\Module();
        $config = $module->getConfig();
        $route = Segment::factory($config['router']['routes']['login']['options']);
        $router = new SimpleRouteStack();
        $router->addRoute('login', $route);
        $this->event->setRouter($router);
    }
    
    public function mockRouteLogout() {
        $module = new \Application\Module();
        $config = $module->getConfig();
        $route = Segment::factory($config['router']['routes']['logout']['options']);
        $router = new SimpleRouteStack();
        $router->addRoute('logout', $route);
        $this->event->setRouter($router);
    }
    
    public function testAccessLogin() {
        $this->mockRouteLogin();
        $this->routeMatch->setParam('module', 'application');
        $this->routeMatch->setParam('controller', 'auth');
        $this->routeMatch->setParam('action', 'login');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertArrayHasKey('form', $result->getVariables());
    }
    
    public function testAccessLogout() {
        $this->mockLogin();
        $this->mockRouteLogout();
        
        $this->routeMatch->setParam('module', 'application');
        $this->routeMatch->setParam('controller', 'auth');
        $this->routeMatch->setParam('action', 'logout');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        
        $this->assertEquals(302, $response->getStatusCode());
        $headers = $response->getHeaders()->toArray();
        $this->assertEquals('/login', $headers['Location']);
    }
    
    public function testLoginAdmin() {
        $this->mockRouteLogin();
        $this->routeMatch->setParam('module', 'application');
        $this->routeMatch->setParam('controller', 'auth');
        $this->routeMatch->setParam('action', 'login');
        
        $this->request->setMethod('POST');
        $this->request->getPost()->set('username', 'admin');
        $this->request->getPost()->set('password', '1');
        
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        
        $this->assertEquals(302, $response->getStatusCode());
        $headers = $response->getHeaders()->toArray();
        $this->assertEquals('/', $headers['Location']);
    }
    
    public function testFalhaLoginAdmin() {
        $this->mockRouteLogin();
        $this->routeMatch->setParam('module', 'application');
        $this->routeMatch->setParam('controller', 'auth');
        $this->routeMatch->setParam('action', 'login');
        
        $this->request->setMethod('POST');
        $this->request->getPost()->set('username', 'admin');
        $this->request->getPost()->set('password', '123'); // wrong password
        
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $messages = $this->controller->messages()->getMergedMessages();
        $keys = array_keys($messages);
        
        $this->assertEquals(1, count($messages));
        $this->assertEquals('danger', $keys[0]);
        
        $this->controller->messages()->clearMessages();
    }
}
