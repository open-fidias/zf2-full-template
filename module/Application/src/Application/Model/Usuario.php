<?php

/**
 * Descrição de Usuario
 *
 * @author
 */

namespace Application\Model;

class Usuario {

    public $id;
    public $login;
    public $senha;
    public $role_id;

    public function exchangeArray($data) {
        $this->id = (!empty($data['id'])) ? $data['id'] : null;
        $this->login = (!empty($data['login'])) ? $data['login'] : null;
        $this->senha = (!empty($data['senha'])) ?  hash('sha256', $data['senha']) : null;
        $this->role_id = (!empty($data['role_id'])) ? $data['role_id'] : null;
    }

    public function getArrayCopy() {
        return get_object_vars($this);
    }
}
