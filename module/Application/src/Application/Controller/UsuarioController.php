<?php

namespace  Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\Form\UsuarioForm;
use Application\Form\UsuarioEditarSenhaForm;
use Application\Form\UsuarioFilter;
use Zend\View\Model\ViewModel;
use Zend\Permissions\Acl\Acl;
use Core\Controller\ActionController;
use Application\Model\Usuario;
use Application\Form\UsuarioEditarSenhaFilter;

class UsuarioController extends ActionController {

	protected $usuarioTable;

	public function getUsuarioTable(){
		if(!$this->usuarioTable){
			$sm = $this->getServiceLocator();
			$this->usuarioTable = $sm->get('Application\Model\UsuarioTable');
		}
		return $this->usuarioTable;
	}

	public function indexAction(){
		$acoes['criar'] = $this->temPermissao("criar");
		$acoes['editar'] = $this->temPermissao("editar");
		$acoes['deletar'] = $this->temPermissao("deletar");

		$pagina = $this->params()->fromQuery('pagina', 1);
		$paginacao = $this->getServiceLocator()->get('Core\Controller\Paginacao');
		$offset = $paginacao->getOffset($pagina);

		$restricao = $this->params()->fromQuery('restricao', '');

		return new ViewModel(array(
			'list' => $this->getUsuarioTable()->listarUsuario($offset, $restricao),
			'pagina' => $pagina,
			'restricao' => $restricao,
			'acoes' => $acoes,
		));
	}

	public function criarAction(){

		$sm = $this->getServiceLocator();
		$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
		$form = new UsuarioForm($dbAdapter);

		$request = $this->getRequest();
		if($request->isPost()){
			$usuario = new Usuario();
			$filter = new UsuarioFilter();
			$form->setInputFilter($filter->getInputFilter());
			$form->setData($request->getPost());

			if($form->isValid()){
				$usuario->exchangeArray($form->getData());

				$session = $this->getServiceLocator()->get('Session');
				$user = $session->offsetGet('user');
				$usuario->cadastro_usuario_id = $user->id;
				$data = $form->getData();
				try {
					$usuario->senha ='a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3';
					$generatedId = $this->getUsuarioTable()->salvarUsuarioTransaction($usuario);

					$this->messages()->flashSuccess('Usuário criado(a) com sucesso.');
					return $this->redirect()->toRoute('usuario',array('action'=>'editar','id'=>$generatedId));
				}catch (\Exception $e) {
    				$this->messages()->flashError('Ocorreu um erro ao criar. Detalhes: ' . $e->getMessage());
    			}
			}

		}

		$viewModel = new ViewModel(array(
				'form'=>$form
		));
		$viewModel->setVariable('title', 'Criar Usuário');
		$viewModel->setTemplate('application/usuario/salvar.phtml');

		return $viewModel;
	}
	public function editarAction(){
		$id = $this->params()->fromRoute('id',0);
		if(!$id){
			return $this->redirect()->toRoute('usuario', array('action' => 'criar'));
		}

		try {
			$usuario = $this->getUsuarioTable()->buscar($id);
		} catch (\Exception $e) {
			$this->messages()->flashWarning('Usuário não encontrado(a). ' . $e->getMessage());
			return $this->redirect()->toRoute('usuario', array('action' => 'criar'));
		}

		$sm = $this->getServiceLocator();
		$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
		$form = new UsuarioForm($dbAdapter);
		$form->bind($usuario);

		$request = $this->getRequest();
		if($request->isPost()){
			$filter = new UsuarioFilter();
			$form->setInputFilter($filter->getInputFilter());
			$form->setData($request->getPost());

			$session = $this->getServiceLocator()->get('Session');
			$user = $session->offsetGet('user');
			$usuario->cadastro_usuario_id = $user->id;

			if($form->isValid()){
				try {
					$generatedId = $this->getUsuarioTable()->salvarUsuarioTransaction($usuario);
					$this->messages()->flashSuccess('Usuário atualizado com sucesso.');
					return $this->redirect()->toRoute('usuario');
				}catch (\Exception $e) {
					$this->messages()->flashError('Ocorreu um erro ao atualizar. Detalhes: ' . $e->getMessage());
				}
			}

		}

		$route = 'usuario';
		$viewModel = new ViewModel(array(
			'form'=>$form,
			'id' => $id,
			'route'=>$route,
		));
		$viewModel->setVariable('title', 'Editar Usuário');
		$viewModel->setTemplate('application/usuario/salvar.phtml');

		return $viewModel;
	}

	public function editarSenhaAction(){
		$session = $this->getServiceLocator()->get('Session');
		$user = $session->offsetGet('user');

		if(!$user){
			$this->messages()->flashWarning("Nenhum usuário logado.");
			return $this->redirect()->toRoute('login');
		}
		try {
			$usuario = $this->getUsuarioTable()->buscar($user->id);
		} catch (\Exception $e) {
			$this->messages()->flashWarning('Usuário não encontrado(a).'. $e->getMessage());
            return $this->redirect()->toUrl('/');
		}

		$form = new UsuarioEditarSenhaForm();
		$form->bind($usuario);
		$request = $this->getRequest();
		if($request->isPost()){
			$filter = new UsuarioEditarSenhaFilter();
			$form->setInputFilter($filter->getInputFilter());
			$form->setData($request->getPost());

			$data = $request->getPost()->toArray();

			if($form->isValid()){
				try {
					$usuEncontrado = $this->getUsuarioTable()->senhaValida($data);

					if (!$usuEncontrado
							|| !($usuEncontrado
							&& (int) $usuEncontrado->id == (int) $data['id'])
					){
						$this->messages()
							->flashError("Senha atual não confere, tente novamente.");
					} else if ( $data['senha'] != $data['senha_repetida'] ){
						$this->messages()
							->flashError("Verifique se a nova senha foi digitada
								corretamente, e repita a nova senha.");
					} else {
						unset($data['senha_atual']);
						unset($data['senha_repetida']);
						unset($data['submit']);
						$data['senha'] = hash('sha256', $data['senha']);

						$this->getUsuarioTable()->editarSenha($data);
						$this->messages()->flashSuccess('Senha atualizada com sucesso.');
						return $this->redirect()->toUrl('/');
					}
				} catch (\Exception $e) {
					$this->messages()->flashError('Ocorreu um erro ao editar senha. Detalhes: ' . $e->getMessage());
				}
			}
		}

		$viewModel = new ViewModel(array(
			'form' => $form,
			'id' => $user->id,
		));
		$viewModel->setVariable('title', 'Editar Senha');
    	$viewModel->setTemplate('application/usuario/editar-senha.phtml');

		return $viewModel;
	}

	public function deletarAction(){
		$id = (int) $this->params()->fromRoute("id", 0);
		try {
			$this->getUsuarioTable()->deletarUsuario($id);
			$this->messages()->flashSuccess("Usuário deletado com sucesso.");
		} catch (\Exception $e) {
			if(preg_match('/23503/', $e->getMessage())){
				$this->messages()->flashWarning('Usuário é referenciado e não pode ser deletado.');
			}else{
				$this->messages()->flashError('Ocorreu um erro ao deletar. Detalhes: ' . $e->getMessage());
			}
		}
		$this->redirect()->toRoute('usuario');
	}
}
