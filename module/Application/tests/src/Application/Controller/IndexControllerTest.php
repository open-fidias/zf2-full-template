<?php

namespace ApplicationTest\Controller;

use Core\Test\ControllerTestCase;

class IndexControllerTest extends ControllerTestCase {

    /**
     * Namespace completa do Controller
     * @var string
     */
    protected $controllerFQDN = 'Application\Controller\IndexController';

    /**
     * Nome da rota. Geralmente o nome do módulo
     * @var string
     */
    protected $controllerRoute = 'application';

    public function testIndexActionCanBeAccessed() {
        
        $this->mockLogin();
        $auth = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $this->assertTrue($auth->hasIdentity());

        $this->routeMatch->setParam('action', 'index');

        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        
        $this->assertArrayHasKey('acoes', $result->getVariables());
    }

}
