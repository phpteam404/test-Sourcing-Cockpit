<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
   /* public function index()
    {
        $this->load->library('AES');
        $aesObj = new AES();
        $data = ['name' => 'mohanbabu', 'email' => 'mohanbabu.p@thresholdsoft.com'];
        echo $aesObj->encrypt(json_encode($data),'threshold');
        echo 123; exit;
    }*/
	public function oauth()
	{
		$this->load->library('Oauth');
        $oauth = $this->oauth;
        $token = $oauth->generateAccessToken();
        echo json_encode($token);
	}

	public function test()
	{
		print_r(PDO::getAvailableDrivers());
	}

	
}
