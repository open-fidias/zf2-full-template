<?php
namespace Core\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Core\Db\TableGateway;

class ActionController extends AbstractActionController
{
	/**
     * Returns a TableGateway
     *
     * @param  string $table
     * @return TableGateway
     */
	protected function getTable($table)
    {
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('DbAdapter');
        $tableGateway = new TableGateway($dbAdapter, $table, new $table);
        $tableGateway->initialize();

        return $tableGateway;
    }

    /**
     * Returns a Service
     *
     * @param  string $service
     * @return Service
     */
    protected function getService($service)
    {
        return $this->getServiceLocator()->get($service);
    }

    /**
     * @deprecated
     * @param type $actionName
     * @param type $controllerName
     * @param type $module
     * @param type $role
     * @return boolean
     */
    protected function temPermissao( $actionName, $controllerName = null, $module = null, $role = null ) {
		if ($actionName == 'ver') {
            return true;
        }
        
        $rota = $this->getEvent()->getRouteMatch()->getParam('controller');
    	//Application\Controller\Orcamento
    	//Array ( [0] => Application [1] => Controller [2] => Orcamento )

    	$session = $this->getServiceLocator()->get('Session');
    	$user = $session->offsetGet('user');
    	
    	$url = explode('\\', $rota);
    	$auth = $this->getService('Application\Service\Auth');
    	return $auth->temPermissao(($module)?$module:$url[0],$user->nome_grupo, ($controllerName)?$controllerName:$url[2], $actionName);
    }
    
    /**
     * Percorre as ações no array e modifica o valor temPermissao para o valor
     * adequado, true ou false
     * TODO: mudar todos de temPermissao para temPermissaoTeste e renomear método removendo Teste!
     * @param array $acoes referência para array de acoes
     */
    protected function temPermissaoTeste(&$acoes = array()) {
        
        $session = $this->getServiceLocator()->get('Session');
    	$user = $session->offsetGet('user');
        $acl = $this->getServiceLocator()->get('Core\Acl\Builder')->build();
        foreach ($acoes as &$valor) {
            $resource = "{$valor['module']}\\Controller\\{$valor['controller']}.{$valor['action']}";
            $valor['temPermissao'] = $acl->isAllowed($user->nome_grupo, $resource);
        }
    }
}