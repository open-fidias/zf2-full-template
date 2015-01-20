<?php

namespace Core\Test;

use Zend\Http\Request;
use Zend\Mvc\Router\RouteMatch;

abstract class ControllerTestCase extends TestCase {

    /**
     * The ActionController we are testing
     *
     * @var Zend\Mvc\Controller\AbstractActionController
     */
    protected $controller;

    /**
     * A request object
     *
     * @var Zend\Http\Request
     */
    protected $request;

    /**
     * A response object
     *
     * @var Zend\Http\Response
     */
    protected $response;

    /**
     * The matched route for the controller
     *
     * @var Zend\Mvc\Router\RouteMatch
     */
    protected $routeMatch;

    /**
     * An MVC event to be assigned to the controller
     *
     * @var Zend\Mvc\MvcEvent
     */
    protected $event;

    /**
     * The Controller fully qualified domain name, so each ControllerTestCase can create an instance
     * of the tested controller
     *
     * @var string
     */
    protected $controllerFQDN;

    /**
     * The route to the controller, as defined in the configuration files
     *
     * @var string
     */
    protected $controllerRoute;
    
    protected $pluginManager;
    public $pluginManagerPlugins = array();

    public function setup() {
        parent::setup();
        $this->controller = $this->serviceManager->get($this->controllerFQDN);
        $this->request = new Request();
        $this->routeMatch = new RouteMatch(array(
            'router' => array(
                'routes' => array(
                    $this->controllerRoute => $this->routes[$this->controllerRoute]
                )
            )
        ));
        $this->event->setRouteMatch($this->routeMatch);

        $this->controller->setEvent($this->event);
        $this->controller->setServiceLocator($this->serviceManager);
    }

    protected function mockLogin($username = 'admin', $password = '1') {
        $authService = $this->serviceManager->get('Application\Service\Auth');
        $authService->authenticate(
                array('username' => $username, 'password' => $password)
        );
    }

    public function tearDown() {
        parent::tearDown();
        unset($this->controller);
        unset($this->request);
        unset($this->routeMatch);
        unset($this->event);
    }
}
