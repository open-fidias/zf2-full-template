<?php

/**
 * Descrição de UsuarioTable
 *
 * @author
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class UsuarioTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    /*
     * Para ser utilizado em transações externas, não abre transação.
     */
    public function inserirUsuario($data) {
    	$table = new TableGateway("usuario", $this->tableGateway->getAdapter());
    	$table->insert($data);
    }

    public function editarUsuario($data) {
    	$table = new TableGateway("usuario", $this->tableGateway->getAdapter());

    	$id = (int)$data['id'];
    	if($id){
    		$table->update($data, array('id' => $id));
    	}
    }

    public function salvarUsuarioTransaction(Usuario $usuario){
    	$conn = $this->tableGateway->getAdapter()->getDriver()->getConnection();
    	try {
    		$conn->beginTransaction();

    		$id = (int) $usuario->id;
    		if ($id == 0) {

                // TODO: generate id from another table, e.g. Pessoa.

    			/*$data = array(
    				'id' => $usuario->id,
    				'senha' => $usuario->senha,
    				'role_id' => $usuario->role_id,
    				'login' => $usuario->login,
    			);
    			$this->tableGateway->insert($data);*/
                throw new \Exception("Operation not supported yet.");
    		} else {
    			$data =  array(
    					'role_id'=>$usuario->role_id,
    					'login'=>$usuario->login,
    			);
    			$this->tableGateway->update($data, array('id' => $id));
    		}
    		$conn->commit();
    	} catch (\Exception $ex){
    		$conn->rollback();
    		throw $ex;
    	}
    	return $generatedId;
    }

    public function deletar($id){
    	$table = new TableGateway("usuario", $this->tableGateway->getAdapter());

    	try {
    		$table->delete(array('id'=>(int)$id));
    	} catch (\Exception $ex) {
    		throw $ex;
    	}
    }

    public function buscar($id){
    	$id = (int) $id;
    	$select = new Select($this->tableGateway->getTable());
    	$select->columns(array(
            'login',
            'id',
            'role_id'
        ));
    	$select->where(array('usuario.id'=>$id));

    	$rowset = $this->tableGateway->selectWith($select);
    	$row= $rowset->current();
    	if(!$row){
    		throw new \Exception("Cód. $id não encontrado.");
    	}
    	return $row;
    }

    public function listarUsuario($offset = 0, $restricao = '') {
    	$select = new Select($this->tableGateway->getTable());
    	$select->columns(array('id', 'login'));

    	if (! empty($restricao)) {
    		$select->where
                ->like('login', new Expression('?', array('%' . $restricao . '%')));
    	}

    	$select->order('login ASC');
    	$select->limit(10);
    	$select->offset($offset);

    	$resultSet = $this->tableGateway->selectWith($select);
    	return $resultSet;
    }

    /**Recebe array com senha_atual, senha_repetida, senha, id
     * retorna o objeto encontrado apartir da senha e login
     * @param unknown $params
     * @throws \Exception
     * @return mixed
     */
    public function senhaValida($params){

    	$senha_crip = hash('sha256', $params['senha_atual']);

    	$select = new Select($this->tableGateway->getTable());
    	$select->columns(array('id'));
    	$select->where(array('login'=>$params['login'],'senha'=>$senha_crip));

    	$rowset = $this->tableGateway->selectWith($select);
    	$row = $rowset->current();
    	if(!$row){
    		throw new \Exception("Cód. ". $params['id'] ." não encontrado.");
    	}
    	return $row;
    }

    public function editarSenha($data){
    	$table = new TableGateway("usuario", $this->tableGateway->getAdapter());
    	$table->update($data, array('id' => $data['id']));
    }

    public function deletarUsuario($id){
    	$conn = $this->tableGateway->getAdapter()->getDriver()->getConnection();
    	try {
    		$conn->beginTransaction();
    		$this->tableGateway->delete(array('id'=>(int)$id));
    		$this->deletarFuncionario($id);
			$this->tableGateway->delete(array('id'=>(int)$id));
    		$conn->commit();
    	} catch (\Exception $ex) {
    		$conn->rollback();
    		throw $ex;
    	}
    }
}
