<?php
namespace  Application\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Select;

class UsuarioForm extends Form {

	protected $_dbAdapter;


	public  function  __construct(AdapterInterface $dbAdapter = NULL){
		parent::__construct();
		$this->_dbAdapter = $dbAdapter;

		$this->setAttribute('method', 'post');

		$this->add($this->_id());
		$this->add($this->_login());
		$this->add($this->_role_id());

		$this->add($this->_submit());
	}

	protected function _submit() {
		$e = new Element\Submit('submit');
		$e->setValue("Salvar");
		$e->setAttribute("class", "btn btn-primary");

		return $e;
	}

	protected function _id() {
		$e = new Element\Text('id');
		$e->setAttribute('id', 'id');
		$e->setAttribute('class', 'form-control');
		$e->setLabel('CÓD.:');
		$e->setAttribute('readonly', 'readonly');

		return $e;
	}


	protected function _role_id() {
		$e = new Element\Select('role_id');
		$e->setLabel('* Grupo:');
		$e->setAttribute('class', 'form-control select2');
		$e->setAttribute('id', 'role_id');

		$model = new TableGateway('acl_roles', $this->_dbAdapter);
		$select = new Select();
		$select->columns(array('id', 'role'));
		$select->from('acl_roles');
		$select->order('role ASC');
		$select->where('acl_roles.id > 2');
		$rowset = $model->selectWith($select);
		$options = array();
		foreach ($rowset as $row) {
			$options[$row['id']] = $row['role'];
		}
		$e->setValueOptions($options);

		return $e;
	}
    protected function _login() {
        $e = new Element\Text('login');
        $e->setLabel('* Login:');
        $e->setAttribute("id", "login")
                ->setAttribute("class", "form-control")
                ->setAttribute("placeholder", "Login");

        return $e;
    }


}
