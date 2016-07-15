<?php
defined("BASEPATH" or die("El acceso al script no está permitido"));
 
/*
 *clase para hacer de puente entre angularjs y codeigniter
*/
 
class Initial extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
	}
	public function index()
	{
		$this->load->view('../../angular_templates/index', TRUE);
	}
}