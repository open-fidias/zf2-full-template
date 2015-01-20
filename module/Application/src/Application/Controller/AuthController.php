<?php

namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\ActionController;
use Application\Form\Login;
use Application\Form\LoginFilter;

/**
 * 
 */
class AuthController extends ActionController {

    /**
     * Mapped as
     *   /login
     * @return void
     */
    public function loginAction() {
        $form = new Login();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = new LoginFilter();
            $form->setInputFilter($inputFilter->getInputFilter());
            $data = $request->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $service = $this->getService('Application\Service\Auth');
                try {
                    $service->authenticate(array(
                        'username' => $data['username'],
                        'password' => $data['password']
                    ));
                    return $this->redirect()->toUrl('/');
                } catch (\Exception $e) {
                    $this->messages()->error($e->getMessage());
                }
            }
        }

        $this->layout('layout/login');
        return new ViewModel(array(
            'form' => $form
        ));
    }

    /**
     * Mapped as
     *   /logout
     * @return void
     */
    public function logoutAction() {
        $service = $this->getService('Application\Service\Auth');
        $service->logout();
        return $this->redirect()->toUrl('/login');
    }
    
    public function permissaoNegadaAction() {
        $this->layout('layout/login');
    }

    public function limparCacheAclAction() {
        $auth = $this->getService('Application\Service\Auth');
        try {
            $auth->clearAclCache();
            $this->messages()->flashSuccess('Limpeza do Cache de ACL concluído.');
        } catch (\Exception $e) {
            $this->messages()->error($e->getMessage());
        }
        return $this->redirect()->toUrl('/');
    }
}
