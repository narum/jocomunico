<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Main extends REST_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('main_model');
        $this->load->library('Myaudio');
    }
        
    public function content_get()
    {
        //parametros que nos llegan del get
        $section = $this->query("section");
        $idLanguage = $this->query("idLanguage");

        //comprobación de los parametros
        if($section == NULL || $section == "" || $idLanguage == NULL || $idLanguage == "") {
            $this->response("missing argument startswith", 400);
        } 
        else {

            //Petición al modelo
            $saveResult = $this->main_model->getContent($section, $idLanguage);

            
            //Cojemos los datos de las dos columnas de la petición y lo convertimos en un objecto clave:valor
            $array1 = array_column($saveResult, 'tagString');
            $array2 = array_column($saveResult, 'content');

            $keyValue = array_combine($array1, $array2);

            // Convertimos el array en un objeto
            $response = [
                "data" => $keyValue
            ];

            //respuesta
            $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }
    
    public function getConfig_get()
    {
        $ID_SU = $this->query('IdSu');
        $response = $this->main_model->getConfig($ID_SU);
        //respuesta
        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
    
    public function saveSUserNames_post()
    {
        $ID_SU = $this->query('IdSu');
        $data = json_decode($this->query("data"), true); // convertimos el string json del post en array.

        $response = $this->main_model->changeData('SuperUser', 'ID_SU', $ID_SU, $data);
        //reescrivimos la cookies
        $this->main_model->getConfig($ID_SU);
        //respuesta
        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
    public function checkPassword_post()
    {
        $ID_SU = $this->query('IdSu');
        $password = md5($this->query('pass'));
        $response = $this->main_model->checkSingleData('SuperUser', 'ID_SU', $ID_SU, 'pswd', $password);
        //respuesta
        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
    public function savePassword_post()
    {
        $ID_SU = $this->query('IdSu');
        $oldPass = md5($this->query('oldPass'));
        $newPass = md5($this->query('newPass'));
        //Check old password
        $passOk = $this->main_model->checkSingleData('SuperUser', 'ID_SU', $ID_SU, 'pswd', $oldPass);
        if($passOk['data']=='true'){
            $pass = ['pswd'=> $newPass];
            //Save new password
            $response = $this->main_model->changeData('SuperUser', 'ID_SU', $ID_SU, $pass);

            $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }else{
            $this->response("Passwords does not match", 400);
        }
        //reescrivimos la cookies
        $this->main_model->getConfig($ID_SU);
    }
    
    public function changeDefUser_post()
    {
        $ID_SU = $this->query('IdSu');
        $ID_U = $this->query('idU');
        $data = ['cfgDefUser'=> $ID_U]; // convertimos el string json del post en array.

        $response = $this->main_model->changeData('SuperUser', 'ID_SU', $ID_SU, $data);
        //reescrivimos la cookies
        $this->main_model->getConfig($ID_SU);
        //respuesta
        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
    
    public function changeCfgBool_post()
    {
        $ID_SU = $this->query('IdSu');
        $data = ['cfg'.$this->query('data') => $this->query('value')]; // convertimos el string json del post en array.

        $this->main_model->changeData('SuperUser', 'ID_SU', $ID_SU, $data);
        //reescrivimos la cookies
        $response = $this->main_model->getConfig($ID_SU);
        //respuesta
        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
    
    public function changeCfgVoices_post()
    {
        $ID_U = $this->query('IdU');
        $data = ['cfg'.$this->query('data') => $this->query('value')]; // convertimos el string json del post en array.

        $response = $this->main_model->changeData('User', 'ID_User', $ID_U, $data);
        //respuesta
        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
  
    public function addUser_post()
    {
        // convertimos el string json del post en array.
        $data = [
            'ID_USU'=>$this->query('IdSu'),
            'ID_ULanguage'=>$this->query('ID_ULanguage'),
            'cfgExpansionLanguage'=>$this->query('cfgExpansionLanguage')
            ];

        $response = $this->main_model->saveData('User', $data);
        //respuesta
        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
    
    //Myaudio library Access
    public function getVoices_get(){
        $audio = new Myaudio();
        
        $interfaceVoices = $audio->listInterfaceVoices(true);
        $expansionVoices = $audio->listExpansionVoices(true);
        
        $appRunning = $audio->AppLocalOrServer();
        if ($appRunning == 'local'){
            $interfaceVoicesOffline = $audio->listInterfaceVoices(false);
            $expansionVoicesOffline = $audio->listExpansionVoices(false);
        }else{
            $interfaceVoicesOffline = array (
                [0] => 'App on server',
                [1] => false
            );
            $expansionVoicesOffline = array (
                [0] => 'App on server',
                [1] => false
            );
        }
        
        $voices = [
            'interfaceVoices'=>$interfaceVoices,
            'interfaceVoicesOffline'=>$interfaceVoicesOffline,
            'expansionVoices'=>$expansionVoices,
            'expansionVoicesOffline'=>$expansionVoicesOffline
            ];
        $response = [
            'voices'=>$voices,
            'appRunning'=>$appRunning
            ];
        
        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
    
    //Generate audio
    public function generateAudio_post(){
        
        $idusu = $this->query('IdU');
        $text = $this->query('text');
        $voice = $this->query('voice');
        $type = $this->query('type');
        $language = $this->query('language');
        $rate = $this->query('rate');
        
        $audio = new Myaudio();
        
        $response = $audio->selectedVoiceAudio($idusu, $text, $voice, $type, $language, $rate);
        
        $audio->waitForFile($response[0], $response[1]);
        
        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
    public function userValidate2_post(){
        $ID_SU = $this->query('IdSu');
        $data = [$this->query('data') => $this->query('value')]; // convertimos el string json del post en array.

        $response = $this->main_model->changeData('SuperUser', 'ID_SU', $ID_SU, $data);
        //respuesta
        $this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
    //get today,last week and last month historic
    public function getHistoric_get()
    {
        $idusu = $this->session->userdata('idusu');
        
        $this->main_model->deleteHistoric();//delete all historic after last 30 days
        
        $today = $this->main_model->getHistoric($idusu, '1');
        $lastWeek = $this->main_model->getHistoric($idusu, '7');
        $lastMonth = $this->main_model->getHistoric($idusu, '30');

        $response = [
            'today' => $today,
            'lastWeek' => $lastWeek,
            'lastMonth' => $lastMonth
        ];
        
        $this->response($response, REST_Controller::HTTP_OK);
        
    }
    //get today,last week and last month historic
    public function getSentenceFolders_get()
    {
        $idusu = $this->session->userdata('idusu');
        
        $folders = $this->main_model->getData('S_Folder', 'ID_SFUser', $idusu);
        $response = [
            'folders' => $folders
        ];
        
        $this->response($response, REST_Controller::HTTP_OK);
    }
    //Up historic folder Order
    public function upHistoricFolder_post()
    {
        $idusu = $this->session->userdata('idusu');
        $ID_Folder = $this->query('ID_Folder');
        
        $folderToUp = $this->main_model->getSingleData('S_Folder', 'ID_SFUser', $idusu, 'ID_Folder', $ID_Folder);
        $folderToDown = $this->main_model->getSingleData('S_Folder', 'ID_SFUser', $idusu, 'folderOrder', $folderToUp[0]['folderOrder']-1);
        
        $orderUp = ['folderOrder'=> $folderToUp[0]['folderOrder']-1];
        $order = ['folderOrder'=> $folderToUp[0]['folderOrder']];
        
        $this->main_model->changeData('S_Folder', 'ID_Folder', $ID_Folder, $orderUp);
        $this->main_model->changeData('S_Folder', 'ID_Folder', $folderToDown[0]['ID_Folder'], $order);
        
        $this->response($response, REST_Controller::HTTP_OK);
    }
    //Down historic folder Order
    public function downHistoricFolder_post()
    {
        $idusu = $this->session->userdata('idusu');
        $ID_Folder = $this->query('ID_Folder');
        
        $folderToDown = $this->main_model->getSingleData('S_Folder', 'ID_SFUser', $idusu, 'ID_Folder', $ID_Folder);
        $folderToUp = $this->main_model->getSingleData('S_Folder', 'ID_SFUser', $idusu, 'folderOrder', $folderToDown[0]['folderOrder']+1);
        
        $orderDown = ['folderOrder'=> $folderToDown[0]['folderOrder']+1];
        $order = ['folderOrder'=> $folderToDown[0]['folderOrder']];
        
        $this->main_model->changeData('S_Folder', 'ID_Folder', $ID_Folder, $orderDown);
        $this->main_model->changeData('S_Folder', 'ID_Folder', $folderToUp[0]['ID_Folder'], $order);
        
        $this->response($response, REST_Controller::HTTP_OK);
    }
    //
    public function getSentencesOrHistoricFolder_post()
    {
        $idusu = $this->session->userdata('idusu');
        $ID_Folder = $this->query('ID_Folder');

        if($ID_Folder<0){
            $sentences = $this->main_model->getHistoric($idusu, ($ID_Folder * (-1)));
        }else{
            $folder=$this->main_model->getSingleData('S_Folder', 'ID_SFUser', $idusu, 'ID_Folder', $ID_Folder);
            $sentences = $this->main_model->getSentencesWithPictos($idusu, $ID_Folder);
            //Get manual input sentences
            $manualSentences = $this->main_model->getSingleData('S_Sentence', 'ID_SFolder', $ID_Folder, 'isPreRec', '1');
            //Add manual input sentences to sentences
            if ($sentences == null){
                $sentences = array();
            }
            foreach ($manualSentences as $value) {
                array_push($sentences, $value);
            }
        }
        
        $response = [
            'folder' => $folder[0],
            'sentences' => $sentences
        ];
        
        $this->response($response, REST_Controller::HTTP_OK);
    }
    //Copy sentence from historic or folder to other folder
    public function addSentenceOnFolder_post()
    {
        $idusu = $this->session->userdata('idusu');
        $ID_Folder = $this->query('ID_Folder');
        $ID_Sentence = $this->query('ID_Sentence');
        $historicFolder = $this->query('historicFolder');
        
        if($historicFolder=='true'){
            //Get sentence from historic and pictograms from historic pictograms
            $sentence = $this->main_model->getHistoricSentence($idusu, $ID_Sentence);
            $pictograms = $this->main_model->getData('R_S_HistoricPictograms', 'ID_RSHPSentence', $ID_Sentence);
            //Get sentences in folder to know de order number of the new sentence
            $sentencesOrdered = $this->main_model->getSentencesOrdered($idusu, $ID_Folder);
            $size=count($sentencesOrdered);
            if($size > 0){
                $posInFolder=$sentencesOrdered[$size-1]->posInFolder;
            }
            //Add and remove some fields of array
            $sentence['ID_SFolder'] = $ID_Folder;
            $sentence['ID_SSUser'] = $idusu;
            unset($sentence['ID_SHistoric']);
            unset($sentence['ID_SHUser']);
            $sentence['posInFolder'] = $posInFolder + 1;
            
            //Save sentence
            $saved=$this->main_model->saveData('S_Sentence', $sentence);
            //Get sentence ID
            $sentenceID = $this->main_model->getHigherSentenceId($ID_Folder, $idusu);
            
            //Change the folder id of pictograms
            for($i = 0, $size = count($pictograms); $i < $size; ++$i) {
                unset($pictograms[$i]['ID_RSHPSentencePicto']);
                $pictograms[$i]['ID_RSSPSentence'] = $sentenceID;
                unset($pictograms[$i]['ID_RSHPSentence']);
                unset($pictograms[$i]['ID_RSHPUser']);
            }
            //Save sentence pictogrmas
            $this->main_model->saveArrayData('R_S_SentencePictograms', $pictograms);
        }else{
            //Get sentence from folder and pictograms
            $sentence = $this->main_model->getSingleData('S_Sentence', 'ID_SSentence', $ID_Sentence, 'ID_SSUser', $idusu)[0];
            if($sentence['isPreRec']=='0'){
                $pictograms = $this->main_model->getData('R_S_SentencePictograms', 'ID_RSSPSentence', $sentence['ID_SSentence']);
            }
            //Get sentences in folder to know de order number of the new sentence
            $sentencesOrdered = $this->main_model->getSentencesOrdered($idusu, $ID_Folder);
            $size=count($sentencesOrdered);
            if($size > 0){
                $posInFolder=$sentencesOrdered[$size-1]->posInFolder;
            }
            //Add and remove some fields of array
            $sentence['ID_SFolder'] = $ID_Folder;
            unset($sentence['ID_SSentence']);
            $sentence['posInFolder'] = $posInFolder + 1;
            
            //Save sentence
            $saved=$this->main_model->saveData('S_Sentence', $sentence);
            //Get sentence ID
            $sentenceID = $this->main_model->getHigherSentenceId($ID_Folder, $idusu);
            
            //Change the folder id of pictograms
            if($sentence['isPreRec']=='0'){
                for($i = 0, $size = count($pictograms); $i < $size; ++$i) {
                    unset($pictograms[$i]['ID_RSSPSentencePicto']);
                    $pictograms[$i]['ID_RSSPSentence'] = $sentenceID;
                }
                //Save sentence pictogrmas
                $this->main_model->saveArrayData('R_S_SentencePictograms', $pictograms);
            }
        }
        
        $response = [
            'saved' => $saved,
            'sentence' => $sentence,
            'pictograms' => $pictograms,
            'sentenceID' => $sentenceID
        ];
        
        $this->response($response, REST_Controller::HTTP_OK);
    }
    //Delete sentence from folder
    public function deleteSentenceFromFolder_post()
    {
        $idusu = $this->session->userdata('idusu');
        $ID_SSentence = $this->query('ID_SSentence');
        
        //Delete pictograms
        $this->main_model->deleteData('R_S_SentencePictograms', 'ID_RSSPSentence', $ID_SSentence);
        //Delete sentence
        $response = $this->main_model->deleteSingleData('S_Sentence', 'ID_SSentence', $ID_SSentence, 'ID_SSUser', $idusu);
        
        $this->response($response, REST_Controller::HTTP_OK);
    }
    //Create sentence folder
    public function createSentenceFolder_post()
    {
        $idusu = $this->session->userdata('idusu');
        
        $folders = $this->main_model->getHistoricFolders($idusu);
        $folderOrder = $folders[0][folderOrder]+1;
        $data = [
            'ID_SFUser'=>$idusu,
            'folderName'=>$this->query('folderName'),
            'imgSFolder'=>$this->query('imgSFolder'),
            'folderColor'=>$this->query('folderColor'),
            'folderOrder'=>$folderOrder
        ];
        
        //Save folder
        $saved=$this->main_model->saveData('S_Folder', $data);
        
        if($saved){
            $folder = $this->main_model->getSingleData('S_Folder', 'ID_SFUser', $idusu, 'folderOrder', $folderOrder)[0];
        }
        
        $response = [
            'folder'=>$folder
        ];
        
        $this->response($response, REST_Controller::HTTP_OK);
    }
    //Edit sentence folder
    public function editSentenceFolder_post()
    {
        $data = json_decode($this->query("folder"), true); // convertimos el string json del post en array.
        $idusu = $this->session->userdata('idusu');
        $ID_Folder = $data['ID_Folder'];

        $this->main_model->changeHistFolder($idusu, $ID_Folder, $data);
        
        $response = [
            'folder'=>$folder['ID_Folder']
        ];
        
        $this->response($response, REST_Controller::HTTP_OK);
    }
    //delete sentence folder
    public function deleteSentenceFolder_post()
    {
        $data = json_decode($this->query("folder"), true); // convertimos el string json del post en array.
        $idusu = $this->session->userdata('idusu');
        $ID_Folder = $data['ID_Folder'];
        
        //Get sentences ID from folder to delete pictograms
        $sentences = $this->main_model->getSingleData('S_Sentence', 'ID_SSUser', $idusu, 'ID_SFolder', $ID_Folder);
        for($i = 0, $size = count($sentences); $i < $size; ++$i) {
            //Delete pictograms
            $this->main_model->deleteData('R_S_SentencePictograms', 'ID_RSSPSentence', $sentences[$i]['ID_SSentence']);
        }
        
        //delete sentences
        $this->main_model->deleteSingleData('S_Sentence', 'ID_SSUser', $idusu, 'ID_SFolder', $ID_Folder);

        //delete Folder
        $this->main_model->deleteSingleData('S_Folder', 'ID_SFUser', $idusu, 'ID_Folder', $ID_Folder);
        
        $response = [
            'folder'=>$ID_Folder
        ];
        
        $this->response($response, REST_Controller::HTTP_OK);
    }
    //Add manual sentence
    public function addManualSentence_post()
    {
        $pictograms = json_decode($this->query("pictograms"), true); // convertimos el string json del post en array.
        $idusu = $this->session->userdata('idusu');
        $ID_Folder = $this->query('ID_SFolder');
        
        //Get sentences in folder to know de order number of the new sentence
        $sentencesOrdered = $this->main_model->getSentencesOrdered($idusu, $ID_Folder);
        $size=count($sentencesOrdered);
        if($size > 0){
            $posInFolder=$sentencesOrdered[$size-1]->posInFolder;
        }

        $sentence=[
            'ID_SSUser'=>$idusu,
            'ID_SFolder'=>$ID_Folder,
            'posInFolder'=>$posInFolder + 1,
            'generatorString'=>$this->query('sentence'),
            'isPreRec'=>'1',
            'sPreRecText'=>$this->query('sentence'),
            'sPreRecDate'=>date('Y-m-d'),
            'sPreRecImg1'=>$pictograms[0],
            'sPreRecImg2'=>$pictograms[1],
            'sPreRecImg3'=>$pictograms[2]
        ];

        $saved=$this->main_model->saveData('S_Sentence', $sentence);
        
        $response = [
            'sentence'=>$sentence,
            'pictograms'=>$pictograms,
        ];
        
        $this->response($response, REST_Controller::HTTP_OK);
    }
    //Edit manual sentence
    public function editManualSentence_post()
    {
        $pictograms = json_decode($this->query("pictograms"), true); // convertimos el string json del post en array.
        $idusu = $this->session->userdata('idusu');
        $ID_SSentence = $this->query('ID_SSentence');

        $sentence=[
            'generatorString'=>$this->query('sentence'),
            'sPreRecText'=>$this->query('sentence'),
            'sPreRecDate'=>date('Y-m-d'),
            'sPreRecImg1'=>$pictograms[0],
            'sPreRecImg2'=>$pictograms[1],
            'sPreRecImg3'=>$pictograms[2]
        ];

        $saved=$this->main_model->changeData('S_Sentence', 'ID_SSentence', $ID_SSentence, $sentence);
        
        $response = [
            'sentence'=>$sentence,
            'pictograms'=>$pictograms,
        ];
        
        $this->response($response, REST_Controller::HTTP_OK);
    }
    //Up sentence position in folder
    public function upSentenceOrderOnFolder_post()
    {
        $idusu = $this->session->userdata('idusu');
        $ID_SSentence = $this->query('ID_SSentence');
        $ID_SFolder = $this->query('ID_SFolder');
        
        $sentences = $this->main_model->getSentencesOrdered($idusu, $ID_SFolder);
        
        //Change sentences order if it is not the first sentence
        if($sentences[0]->ID_SSentence != $ID_SSentence){
            $count=0;
            
            foreach ($sentences as $value) {
                if($sentences[$count]->ID_SSentence == $ID_SSentence){
                    $this->main_model->changeData('S_Sentence', 'ID_SSentence', $sentences[$count]->ID_SSentence, ['posInFolder'=> $sentences[$count-1]->posInFolder]);
                    $this->main_model->changeData('S_Sentence', 'ID_SSentence', $sentences[$count-1]->ID_SSentence, ['posInFolder'=> $sentences[$count]->posInFolder]);
                }
                $count++;
            }
        }
        $this->response(REST_Controller::HTTP_OK);
    }
    //Down sentence position in folder
    public function downSentenceOrderOnFolder_post()
    {
        $idusu = $this->session->userdata('idusu');
        $ID_SSentence = $this->query('ID_SSentence');
        $ID_SFolder = $this->query('ID_SFolder');
        
        $sentences = $this->main_model->getSentencesOrdered($idusu, $ID_SFolder);
        
        //Change sentences order if it is not the first sentence
        $size=count($sentences);
        if($sentences[$size-1]->ID_SSentence != $ID_SSentence){
            $count=0;
            
            foreach ($sentences as $value) {
                if($sentences[$count]->ID_SSentence == $ID_SSentence){
                    $this->main_model->changeData('S_Sentence', 'ID_SSentence', $sentences[$count]->ID_SSentence, ['posInFolder'=> $sentences[$count+1]->posInFolder]);
                    $this->main_model->changeData('S_Sentence', 'ID_SSentence', $sentences[$count+1]->ID_SSentence, ['posInFolder'=> $sentences[$count]->posInFolder]);
                }
                $count++;
            }
        }
        $this->response(REST_Controller::HTTP_OK);
    }
    
    public function errorVoicesSeen_get()
    {
        $idusu = $this->session->userdata('idusu');
        $this->main_model->restartErrorVoices($idusu);
    }
}
