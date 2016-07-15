
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Histo extends REST_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('histo_model');

    }
        
    public function index_get()
    {
        $pictoid = $this->query("pictoid");
        
        if($pictoid == NULL || $pictoid == "") {
            $this->response("missing argument picto id", 400);
            return;
        }
        
        $saveResult = $this->histo_model->HistSearch($pictoid);

        $response = [
            "data" => $saveResult
        ];
        
        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

    }
}
