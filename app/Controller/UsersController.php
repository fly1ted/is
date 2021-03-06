<?php
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');
class UsersController extends AppController {
	
	function beforeFilter() {
		parent::beforeFilter();
		
		$this->Auth->allow(array('login', 'register', 'all', 'view', 'files'));
	}
	
    function login() {
        $this->layout = 'login';
		
		if($this->request->is('post')) {
			$data = $this->User->find('first',
				array(
					'recursive' => 1,
					'conditions' => array('User.login' => $this->request->data['User']['login']),
				)
			);
			
			if(!empty($data)){
				if($this->Auth->login()) {
					unset($data['User']['password']);
					$this->Auth->login($data);
					
					switch($data['User']['role']) {
						case 'guest' :
							$this->Session->setFlash('Помилка. Недостатньо прав. Зверніться до адміністратора', 'flash', array('class' => 'alert-warning'));
							$this->redirect('/users/logout');
						break;
						case 'user' :
							$this->redirect('/user');
						break;
						case 'admin' :
							$this->redirect('/admin');
						break;
					}
				} else {
					$this->Session->setFlash('Помилка. Логін або(та) пароль не правильний(-і)', 'flash', array('class' => 'alert-danger'));
				}
			} else {
				$this->Session->setFlash('Помилка. Логін або(та) пароль не правильний(-і)', 'flash', array('class' => 'alert-danger'));
			}
		}
        
        $this->set(array(
            'page_title' => 'Вхід'
        ));
    }
    
	function register() {
		$this->layout = 'login';
		
        if($this->request->is('post')) {
            if($this->User->saveAll($this->request->data, array('validate' => 'only'))) {
                $this->request->data['User']['hash_id'] = md5(date('YmdHis').rand(1, 1000).Configure::read('Security.salt'));
				$this->request->data['User']['password'] = Security::hash($this->request->data['User']['password'], 'Blowfish');
                $this->request->data['User']['role'] = 'guest';
				$this->request->data['UserInfo']['user_id'] = $this->User->id;
				
				if($this->User->saveAssociated($this->request->data, array('validate' => false))) {
					$last = $this->User->read(null, $this->User->id);
					if(new Folder(WWW_ROOT.'files'.DS.'users'.DS.$last['User']['hash_id'], true, 0755)) {
						$this->Session->setFlash('Користувач зареєстрований', 'flash', array('class' => 'alert-success'));
						$this->redirect('/users/login');
					} else {
						$this->Session->setFlash('Помилка. Папка користувача не створена', 'flash', array('class' => 'alert-danger'));
					}
                } else {
                    $this->Session->setFlash('Помилка. Користувач не зареєстрований', 'flash', array('class' => 'alert-danger'));
                }
            } else {
				$this->Session->setFlash('Виникла непередбачувана помилка', 'flash', array('class' => 'alert-danger'));
            }
		}

        $this->set(array(
            'page_title' => 'Реєстрація'
        ));		
	}
	
    function logout() {
		$this->autoRender = false;
		
		$_SESSION['KCFINDER']['disabled'] = true;
		
		$this->Auth->logout();
		$this->redirect($this->Auth->logoutRedirect);
	}
	
    function all() {
		$users = $this->User->find('all', array(
			'contain' => 'UserInfo',
			'conditions' => array(
				'User.role' => 'user'
			)
		));
		
        $this->set(array(
            'page_title' => 'Викладацький склад',
			'users' => $users
        ));	
	}
	
    function view($slug = null) {
		if($slug === null) throw new NotFoundException();
		
		$user = $this->User->find('first', array(
			'contain' => array(
				'UserInfo' => array(
					'order' => 'UserInfo.name ASC'
				)
			),
			'conditions' => array(
				'User.login' => $slug
			)
		));
		if(empty($user)) throw new NotFoundException();
		
        $this->set(array(
            'page_title' => $user['UserInfo']['name'],
			'user' => $user,
        ));	
	}
	
	public function files($slug = null) {
		if($slug === null) throw new NotFoundException();
		
		$user = $this->User->find('first', array(
			'contain' => array(
				'UserInfo',
				'File' => array(
					'order' => 'File.created DESC'
				)
			),
			'conditions' => array(
				'User.login' => $slug
			)
		));
		if(empty($user)) throw new NotFoundException();
		
        $this->set(array(
            'page_title' => 'Файли співробітника - '.$user['UserInfo']['name'],
			'user' => $user,
        ));	
	}
}