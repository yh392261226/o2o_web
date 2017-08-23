<?php
namespace App\Controller;

class Index extends \CLASSES\ManageBase
{
	public function __construct($swoole)
    {
		parent::__construct($swoole);
		$this->managers_dao = new \MDAO\Managers();
	}

	public function test()
    {


        if (DEBUG)
        {
            $this->db->debug = 1;
            //$this->showTrace(1);
            //$this->showTime();
        }
		//$data   = array('pager' => true, 'page' => 1);
        //$list = $this->managers_dao->listManagers($data);
		//print_r($list);

        //$info = $this->managers_dao->infoManager('1');
        //print_r($info);

        //$data   = array(); //array('m_status' => -2);
        //$counts = $this->managers_dao->countManagers($data);
        //print_r($counts);

        //$delete = $this->managers_dao->delManagers(array(4,5,6,7));
        //$delete = $this->managers_dao->delManagers('4');
        //var_dump($delete);

        //$data = array(
        //    'm_name' => 'test',
        //    'm_pass' => 'test',
        //    'm_status' => 0,
        //    'm_in_time' => time(),
        //    'm_inip' => '123456789',
        //    'm_author' => 1,
        //    'mpg_id' => 0,
        //    'm_start_time' => time(),
        //    'm_end_time' => time()
        //    );
        //$add  = $this->managers_dao->addManager($data);
        //var_dump($add);

        $data = array(
            'm_name' => 'test',
            'm_pass' => encyptPassword('test'),
        );
        $login  = $this->managers_dao->login($data);
        $_SESSION['manager'] = $login;
        print_r($_SESSION);

	}
}
