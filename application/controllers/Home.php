<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

	public function __construct()
        {
            parent::__construct();

            $this->load->model('Lexicon');
        }

	public function index()
	{
            // CHECK COOKIES
            if (!$this->session->userdata('uname')) {

                $this->load->library('form_validation');

                $this->form_validation->set_rules('usuari', 'Usuari', 'trim|required|callback_usuari_correcte');
                $this->form_validation->set_rules('pass', 'Password', 'trim|required');

                $this->form_validation->set_message('required', '- El camp %s és obligatori.');

                $this->form_validation->set_error_delimiters('<div class="formerrorlogin">', '</div>');

                if ($this->form_validation->run() == false) {

                    $this->load->view('login');
                }
                else {
                    // SET COOKIES: Cookies are set within the user validation function in the Lexicon model
                    redirect(base_url().'frase', 'location');
                }
            }
            else {
                redirect(base_url().'frase', 'location');
            }
	}

        function usuari_correcte()
        {
            $this->form_validation->set_message('usuari_correcte', "- L'usuari o el password no són correctes.");
            return $this->Lexicon->validar_usuari();
        }

        public function logout()
        {
            $this->session->unset_userdata('idusu');
            $this->session->unset_userdata('uname');
            redirect(base_url(), 'location');
        }

        public function texttospeech()
        {
            // TEST CODE FOR USE WITHIN BROWSERS
            $this->load->library('texttospeech');
            $this->texttospeech->initialize();
            $this->texttospeech->vox();
            $this->texttospeech->wsave();
            $this->texttospeech->play_web();
        }
}
