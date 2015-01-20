<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Application\View\Helper\FormatarNumero;
use Application\View\Helper\LabelStatus;
use Zend\Mvc\I18n\Translator;
use Zend\Validator\AbstractValidator;
use Application\Model\UsuarioTable;
use Application\Model\Usuario;
use Zend\Authentication\AuthenticationService;

class Module {

    const PRIORIDADE_DISPATCH = 100;

    public function onBootstrap($e) {
        $translator = new Translator();
        $translator->addTranslationFile(
                'phpArray', './vendor/zendframework/zendframework/resources/languages/pt_BR/Zend_Validate.php'
        );
        AbstractValidator::setDefaultTranslator($translator);

        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        /** @var \Zend\ModuleManager\ModuleManager $moduleManager */
        $moduleManager = $e->getApplication()->getServiceManager()->get('modulemanager');
        /** @var \Zend\EventManager\SharedEventManager $sharedEvents */
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();

        //adiciona eventos ao módulo
        $sharedEvents->attach(
                'Zend\Mvc\Controller\AbstractActionController',
                \Zend\Mvc\MvcEvent::EVENT_DISPATCH,
                array(
                    $this, 'mvcPreDispatch'
                ),
                Module::PRIORIDADE_DISPATCH);

        \Locale::setDefault('pt_BR');
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * Verifica se precisa fazer a autorização do acesso
     * @param MvcEvent $event Evento
     * @return boolean
     */
    public function mvcPreDispatch($event) {
        $di = $event->getTarget()->getServiceLocator();
        $routeMatch = $event->getRouteMatch();
        $moduleName = $routeMatch->getParam('module');
        $controllerName = $routeMatch->getParam('controller');
        $actionName = $routeMatch->getParam('action');

        $authService = $di->get('Application\Service\Auth');
        if (!$authService->authorize($moduleName, $controllerName, $actionName)) {
            $auth = new AuthenticationService();
            $redirect = $event->getTarget()->redirect();
            if ($auth->hasIdentity()) {
                return $redirect->toUrl('/permissao-negada');
            } else {
                return $redirect->toUrl('/login');
            }
        }
        return true;
    }

    public function getServiceConfig() {
        return array(
            'factories' => array(
                'Application\Model\UsuarioTable' =>  function($sm) {
                	$tableGateway = $sm->get('UsuarioTableGateway');
                	$table = new UsuarioTable($tableGateway);
                	return $table;
                },
                'UsuarioTableGateway' => function ($sm) {
                	$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                	$resultSetPrototype = new ResultSet();
                	$resultSetPrototype->setArrayObjectPrototype(new Usuario());
                	return new TableGateway('usuario', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }

    public function getViewHelperConfig() {
        return array(
            'factories' => array(
                'formatarNumero' => function ($sm) {
                    return new FormatarNumero();
                },
                'labelStatus' => function ($sm) {
                    return new LabelStatus();
                },
            ),
        );
    }

}
