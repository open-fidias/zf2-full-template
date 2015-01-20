<?php

namespace  Application\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class UsuarioEditarSenhaForm extends Form {

	public  function __construct() {
        parent::__construct('email');
		$this->setAttribute('method', 'post');
		$this->add($this->_id());
		$this->add($this->_login());
		$this->add($this->_senha());
		$this->add($this->_senhaAtual());
		$this->add($this->_senhaNova());

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


	protected function _login() {
		$e = new Element\Text('login');
		$e->setLabel('* Login:');
		$e->setAttribute("id", "login");
		$e->setAttribute("class", "form-control");
		$e->setAttribute("placeholder", "Login");
		$e->setAttribute('readonly', 'readonly');

		return $e;
	}
	protected function _senhaNova() {
		$e = new Element\Password('senha_repetida');
		$e->setLabel('* Repetir senha:');
		$e->setAttribute("id", "password");
		$e->setAttribute("class", "form-control");
		$e->setAttribute("placeholder", "Repetir nova senha");

		return $e;
	}

	protected function _senha() {
		$e = new Element\Password('senha');
		$e->setLabel('* Senha nova:');
		$e->setAttribute("id", "password");
		$e->setAttribute("class", "form-control");
		$e->setAttribute("placeholder", "Senha");

		return $e;
	}

	protected function _senhaAtual() {
		$e = new Element\Password('senha_atual');
		$e->setLabel('* Senha atual:');
		$e->setAttribute("id", "password");
		$e->setAttribute("class", "form-control");
		$e->setAttribute("placeholder", "Senha atual");

		return $e;
	}
}
