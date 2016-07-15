<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class AddWord extends REST_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->library('session');
        $this->load->model('panelInterface');
        $this->load->model('BoardInterface');
        $this->load->model('AddWordInterface');
        $this->load->model('InsertVocabulari');
    }

    public function index_get() {
        // CHECK COOKIES
        if (!$this->session->userdata('uname')) {
            redirect(base_url(), 'location');
        } else {
            if (!$this->session->userdata('cfguser')) {
                $this->BoardInterface->loadCFG($this->session->userdata('uname'));
                $this->load->view('MainBoard', true);
            } else {
                $this->load->view('MainBoard', true);
            }
        }
    }

    private static function cmp($a, $b) {
        $a = strtolower($a['text']);
        $b = strtolower($b['text']);
        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }
        private static function cmpclass($a, $b) {
        $a = strtolower($a['class']);
        $b = strtolower($b['class']);
        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }
    
    public function EditWordRemove_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $idPicto = $request->id;
        $Type = $this->InsertVocabulari->deletePictogram($idPicto);

        $response = [
            "data" => $Type
        ];

        $this->response($response, REST_Controller::HTTP_OK);
    }
    public function EditWordType_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $idPicto = $request->id;
        $Type = $this->AddWordInterface->getTypePicto($idPicto);

        $response = [
            "data" => $Type
        ];

        $this->response($response, REST_Controller::HTTP_OK);
    }
    public function EditWordGetData_post(){
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $idPicto = $request->id;
        $type = $request->type;
        switch($type){
            case('name'):
                $data = $this->AddWordInterface->EditWordNoms($idPicto);
                break;
            case('adj'):
                $data = $this->AddWordInterface->EditWordAdj($idPicto);
                break;
        }
        $response = [
            "data" => $data
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }
    public function EditWordGetClass_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $idPicto = $request->id;
        $type = $request->type;
        switch ($type) {
            case('name'):
                $data = $this->AddWordInterface->getDBClassNames($idPicto);
                break;
            case('adj'):
                $data = $this->AddWordInterface->getDBClassAdj($idPicto);
                break;
        }
        usort($data, array('SearchWord', 'cmpclass'));
        
        $response = [
            "data" => $data
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function InsertWordData_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $objAdd = $request->objAdd;
        $this->InsertVocabulari->insertPicto($objAdd);
    }
    
    public function getAllVerbs_post(){
    
        
        $Verbs = $this->AddWordInterface->getDBVerbs();
        
        $response = [
            "data" => $Verbs
        ];
        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        
    }
    
    public function getDBAll_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $startswith = $request->id;
        $language = $this->session->userdata('ulangabbr');
        
        $user = $this->session->userdata('idusu');

        // Controller search all names from all picto table
        $Names = $this->AddWordInterface->getDBNamesLike($startswith, $user);
        $Verbs = $this->AddWordInterface->getDBVerbsLike($startswith, $user);
        $Adj = $this->AddWordInterface->getDBAdjLike($startswith, $user);
        $Exprs = $this->AddWordInterface->getDBExprsLike($startswith, $user);
        $Advs = $this->AddWordInterface->getDBAdvsLike($startswith, $user);
        $Modifs = $this->AddWordInterface->getDBModifsLike($startswith, $user);
        $QuestionPart = $this->AddWordInterface->getDBQuestionPartLike($startswith, $user);

        // Marge all arrays to one
        $DataArray = array_merge($Names, $Verbs, $Adj, $Exprs, $Advs, $Modifs, $QuestionPart);

        usort($DataArray, array('SearchWord', 'cmp'));

        $response = [
            "data" => $DataArray
        ];

        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }

    public function getDBNames_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $startswith = $request->id;
        $language = $this->session->userdata('ulangabbr');
        $user = $this->session->userdata('idusu');
        // Controller search all names from all picto table
        $DataArray = $this->AddWordInterface->getDBNamesLike($startswith, $user);
        usort($DataArray, array('SearchWord', 'cmp'));
        $response = [
            "data" => $DataArray
        ];

        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }

    public function getDBVerbs_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $startswith = $request->id;
        $language = $this->session->userdata('ulangabbr');
        $user = $this->session->userdata('idusu');


        // Controller search all names from all picto table
        $DataArray = $this->AddWordInterface->getDBVerbsLike($startswith, $user);
        usort($DataArray, array('SearchWord', 'cmp'));
        $response = [
            "data" => $DataArray
        ];

        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }

    public function getDBAdj_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $startswith = $request->id;
        $language = $this->session->userdata('ulangabbr');
        $user = $this->session->userdata('idusu');


        // Controller search all names from all picto table
        $DataArray = $this->AddWordInterface->getDBAdjLike($startswith, $user);
        usort($DataArray, array('SearchWord', 'cmp'));
        $response = [
            "data" => $DataArray
        ];

        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }

    public function getDBExprs_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $startswith = $request->id;
        $language = $this->session->userdata('ulangabbr');
        $user = $this->session->userdata('idusu');


        // Controller search all names from all picto table
        $DataArray = $this->AddWordInterface->getDBExprsLike($startswith, $user);
        usort($DataArray, array('SearchWord', 'cmp'));
        $response = [
            "data" => $DataArray
        ];

        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }

    public function getDBOthers_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $startswith = $request->id;
        $language = $this->session->userdata('ulangabbr');
        $user = $this->session->userdata('idusu');


        // Controller search all names from all picto table
        $Advs = $this->AddWordInterface->getDBAdvsLike($startswith, $user);
        $Modifs = $this->AddWordInterface->getDBModifsLike($startswith, $user);
        $QuestionPart = $this->AddWordInterface->getDBQuestionPartLike($startswith, $user);

        $DataArray = array_merge($Advs, $Modifs, $QuestionPart);
        usort($DataArray, array('SearchWord', 'cmp'));
        $response = [
            "data" => $DataArray
        ];

        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }

    public function EditWordSelect_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $ID_GBoard = $request->idGroupBoard;

        $primaryBoard = $this->BoardInterface->getInfoGroupBoard($ID_GBoard);

        $response = [
            'ID_GB' => $primaryBoard[0]->ID_GB,
            'ID_GBUser' => $primaryBoard[0]->ID_GBUser,
            'GBname' => $primaryBoard[0]->GBname,
            'primaryGroupBoard' => $primaryBoard[0]->primaryGroupBoard,
            'defWidth' => $primaryBoard[0]->defWidth,
            'defHeight' => $primaryBoard[0]->defHeight,
            'imgGB' => $primaryBoard[0]->imgGB
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function GetWordSelect_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $ID_GBoard = $request->idGroupBoard;

        $primaryBoard = $this->BoardInterface->getInfoGroupBoard($ID_GBoard);

        $response = [
            'ID_GB' => $primaryBoard[0]->ID_GB,
            'ID_GBUser' => $primaryBoard[0]->ID_GBUser,
            'GBname' => $primaryBoard[0]->GBname,
            'primaryGroupBoard' => $primaryBoard[0]->primaryGroupBoard,
            'defWidth' => $primaryBoard[0]->defWidth,
            'defHeight' => $primaryBoard[0]->defHeight,
            'imgGB' => $primaryBoard[0]->imgGB
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }
    public function copyUserVocabulary_post() {

        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $idusu = $request->user;

        $this->BoardInterface->initTrans();
        $idusuorigen = $this->session->userdata('idusu');
        $vocabulary = $this->AddWordInterface->copyVocabulary($idusuorigen,$idusu);
        $this->BoardInterface->commitTrans();
        $response = [
            "data" => $vocabulary
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }
}