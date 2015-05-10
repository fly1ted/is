<?php
class UserInfo extends AdminAppModel {
	public $useTable = 'users_information';

	public $validate = array(
        'email' => array(
			'rule' => 'email',
            'message' => 'Введіть коректну e-mail адресу',
            'allowEmpty' => true
        ),
	);
}