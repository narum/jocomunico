
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Login extends REST_Controller {

    public function __construct()
    {
        parent::__construct('rest', TRUE);
        $this->load->model('login_model');
    }

    public function index_post()
    {
        $user = $this->post('user');
        $pass = $this->post('pass');

        if($user == NULL || $pass == NULL) {
            $this->response("missing arguments", 400);
            return;
        }

        $userLoged = $this->login_model->Login($user,$pass);

        if($userLoged) {

            // Guardamos los resultados como objeto
            $response = [
                "data" => $userLoged
            ];

            $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        } else  {
            $this->response([
                $this->config->item('rest_status_field_name') => FALSE,
                $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_unauthorized')
            ], self::HTTP_UNAUTHORIZED);
        }

    }
}
