
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Historic extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('HistoricInterface');
    }

    public function index_get() {

    }

    public function getSFolder_post() {
        $idusu = $this->session->userdata('idusu');
        $sFolder = $this->HistoricInterface->getSFolders($idusu);

        $response = [
            'sFolder' => $sFolder
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function getHistoric_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $day = $request->day;
        $pagHistoric = $request->pagHistoric;

        $historicArray = array();
        $idusu = $this->session->userdata('idusu');
        $historic = $this->HistoricInterface->getHistoric($idusu, $day);
        for ($i = $pagHistoric; $i < count($historic) && $i < $pagHistoric + 10; $i++){
            $arrayProv[0] = $historic[$i];
            $arrayProv[1] = $this->HistoricInterface->getPictosHistoric($historic[$i]->ID_SHistoric);
            array_push($historicArray,$arrayProv);
        }
        $count = $this->HistoricInterface->getCountHistoric($idusu, $day);

        $response = [
            'historic' => $historicArray,
            'count' => $count
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }
    
    function getFolder_post(){
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $idfolder = $request->folder;
        $pagHistoric = $request->pagHistoric;
        
        $sentenceArray = array();
        $idusu = $this->session->userdata('idusu');
        $sentenece = $this->HistoricInterface->getSentenceFolder($idusu, $idfolder);

        for ($i = $pagHistoric; $i < count($sentenece) && $i < $pagHistoric + 10; $i++){
            $arrayProv[0] = $sentenece[$i];
            $arrayProv[1] = $this->HistoricInterface->getPictosFolder($sentenece[$i]->ID_SSentence);
            array_push($sentenceArray,$arrayProv);
        }
        $count = $this->HistoricInterface->getCountSentenceFolder($idusu, $idfolder);

        $response = [
            'historic' => $sentenceArray,
            'count' => $count
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }
    
}
