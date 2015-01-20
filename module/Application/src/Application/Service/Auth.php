<?php

namespace Application\Service;

use Core\Service\Service;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;

/**
 * Serviço responsável pela autenticação da aplicação
 *
 * @category Admin
 * @package Service
 */
class Auth extends Service {

    const DEFAULT_ROLE = "visitante";
    const DEFAULT_ROLE_ID = 1;
    const ROLE_ID_USUARIO = 2;
    const ROLE_ID_ADMIN = 3;

    /**
     * Adapter usado para a autenticação
     * @var Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

    /**
     * Construtor da classe
     *
     * @return void
     */
    public function __construct($dbAdapter = null) {
        $this->dbAdapter = $dbAdapter;
    }

    /**
     * Faz a autenticação dos usuários
     *
     * @param array $params
     * @return array
     */
    public function authenticate($params) {
        if (!isset($params['username']) || !isset($params['password'])) {
            throw new \Exception("Parâmetros inválidos");
        }

        $password = hash('sha256', $params['password']);
        $auth = new AuthenticationService();
        $authAdapter = new AuthAdapter($this->dbAdapter);
        $authAdapter->setTableName('usuario')
                ->setIdentityColumn('login')
                ->setCredentialColumn('senha')
                ->setIdentity($params['username'])
                ->setCredential($password);
        $select = $authAdapter->getDbSelect();
        $select->join(array('ro' => 'acl_roles'), 'usuario.role_id = ro.id', array('nome_grupo' => 'role'));


        $result = $auth->authenticate($authAdapter);
        if (!$result->isValid()) {
            throw new \Exception("Login ou senha inválidos");
        }

        //salva o user na sessão
        $session = $this->getServiceManager()->get('Session');
        $session->offsetSet('user', $authAdapter->getResultRowObject());

        return true;
    }

    /**
     * Faz o logout do sistema
     *
     * @return void
     */
    public function logout() {
        $auth = new AuthenticationService();
        $session = $this->getServiceManager()->get('Session');
        $session->offsetUnset('user');
        $auth->clearIdentity();
        return true;
    }

    /**
     * Faz a autorização do usuário para acessar o recurso
     * @param string $moduleName Nome do módulo sendo acessado
     * @param string $controllerName Nome do controller
     * @param string $actionName Nome da ação
     * @return boolean
     */
    public function authorize($moduleName, $controllerName, $actionName) {
        $auth = new AuthenticationService();
        $role = Auth::DEFAULT_ROLE;
        if ($auth->hasIdentity()) {
            $session = $this->getServiceManager()->get('Session');
            $user = $session->offsetGet('user');
            $role = $user->nome_grupo;
        }

        $resource = $controllerName . '.' . $actionName;
        $acl = $this->getServiceManager()->get('Core\Acl\Builder')->build();
        //$acl = $this->_getCachedAcl();
        if ($acl->isAllowed($role, $resource)) {
            return true;
        }
        return false;
    }

    public function temPermissao($moduleName, $role, $controllerName, $actionName) {

        $resource = $moduleName . "\\Controller\\" . $controllerName . "." . $actionName;
        $acl = $this->getServiceManager()->get('Core\Acl\Builder')->build();
        //$acl = $this->_getCachedAcl();
        if ($acl->isAllowed($role, $resource)) {
            return true;
        }
        return false;
    }

    protected function _getCachedAcl() {
        $cacheAdapter = $this->getServiceManager()->get('Zend\Cache\Storage\Filesystem');
        $acl = $cacheAdapter->getItem('acl');
        if ($acl == FALSE) {
            $acl = $this->getServiceManager()->get('Core\Acl\Builder')->build();
            $cacheAdapter->setItem('acl', $acl);
        }
        return $acl;
    }

    public function clearAclCache() {
        $cacheAdapter = $this->getServiceManager()->get('Zend\Cache\Storage\Filesystem');
        $cacheAdapter->removeItem('acl');
    }
}
