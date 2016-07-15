<?php 

class Main_model extends CI_Model {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    // Petición del contenido para mostrar en las vistas (textos)
    public function getContent($section, $idLanguage)
    {
        $this->db->select('tagString, content'); // Seleccionar les columnes
        $this->db->from('Content');// Seleccionem la taula
        $this->db->where('section', $section);// filtrem per columnes
        $this->db->where('ID_CLanguage', $idLanguage);// filtrem per columnes
        $this->db->order_by('Content.tagString', 'asc');
        $query = $this->db->get();// Fem la query i la guardem a la variable query

        return $query->result_array();// retornem l'array query amb els resultats
    }
    
    // Idiomas disponibles en la tabla Languages.
    public function getLanguagesAvailable(){
        //Peticion a base de datos
            $this->db->select('ID_Language, languageName, languageabbr'); // Seleccionar les columnes
            $this->db->from('Languages');// Seleccionem la taula
            $query = $this->db->get();

            return $query->result_array();// retornamos el array
    }
    
    // Comprobación de un campo de una columna de una tabla
    public function checkData($table, $column, $data){

        $this->db->where($column, $data);
        $query = $this->db->get($table);

        if ($query->num_rows() > 0)
        {
           $result = "true";
        } else{
           $result = "false";
        }

        return $result;
    }
    // Comprobación de un campo de una celda de una tabla
    public function checkSingleData($table, $columnId, $id, $column, $data){

        $this->db->select($column); // Seleccionar les columnes
        $this->db->from($table);// Seleccionem la taula
        $this->db->where($columnId, $id);// filtrem per columnes
        $this->db->where($column, $data);// filtrem per columnes
        $query = $this->db->get();// Fem la query i la guardem a la variable query
        $array = $query->result_array();

        if ($query->num_rows() == 0)
        {
            $result = "false";
        }else if($array[0][$column]==$data){
            $result = "true";
        }else{
            $result = "false";
        }
        $response = [
                "data" => $result
            ];
        return $response;
    }
    
    // Get first data from table $table where content in column $column are like $data
    public function getFirstData($table, $column, $data){
        $this->db->from($table);// Seleccionem la taula
        $this->db->where($column, $data);// filtrem per columnes
        $data = $this->db->get()->result_array();
        
        return $data[0];
    }
    // Get single data from table $table where content in column $column are like $data
    public function getSingleData($table, $column, $data, $column2, $data2){
        $this->db->from($table);// Seleccionem la taula
        $this->db->where($column, $data);// filtrem per columnes
        $this->db->where($column2, $data2);// filtrem per columnes
        $data = $this->db->get()->result_array();
        
        return $data;
    }
    // Delete data from table $table where content in column $column are like $data
    public function deleteData($table, $column, $data){
        $this->db->where($column, $data);// filtrem per columnes
        $query = $this->db->delete($table);
        return $query;
    }
    // Delete single data from table $table where content in column $column are like $data
    public function deleteSingleData($table, $column, $data, $column2, $data2){
        $this->db->where($column, $data);// filtrem per columnes
        $this->db->where($column2, $data2);// filtrem per columnes
        $query = $this->db->delete($table);
        return $query;
    }
    
    // Get data from table $table where content in column $column are like $data
    public function getData($table, $column, $data){
        $this->db->from($table);// Seleccionem la taula
        $this->db->where($column, $data);// filtrem per columnes
        $data = $this->db->get()->result_array();
        
        return $data;
    }
    
    // Guardar contenido en una tabla.
    public function saveData($table, $data){

        $saved = $this->db->insert($table, $data);

        return $saved;
    }
    // Guardar contenido de un array en una tabla.
    public function saveArrayData($table, $data){

        $saved = $this->db->insert_batch($table, $data);

        return $saved;
    }
    // Cambiar contenido de una tabla.
    public function changeData($table, $column, $id, $data){

        $this->db->where($column, $id);
        $saved = $this->db->update($table, $data);

        return $saved;
    }
    
    // Escrivir en la tabla Usuario
    public function saveUser($SUname, $ID_ULanguage){

        $this->db->select('ID_SU'); // Seleccionar les columnes
        $this->db->from('SuperUser');// Seleccionem la taula
        $this->db->where('SUname', $SUname);// filtrem per columnes
        $ID_SU = $this->db->get()->result_array();

        $id = array_column($ID_SU, 'ID_SU');

        $data = [
            "ID_USU" => $id[0],
            "ID_ULanguage" => $ID_ULanguage,
            "cfgExpansionLanguage" => $ID_ULanguage,
        ];

        $saved = $this->db->insert('User', $data);
        
        $this->db->select('ID_User'); // Seleccionar les columnes
        $this->db->from('User');// Seleccionem la taula
        $this->db->where('ID_USU', $id[0]);// filtrem per columnes
        $ID_User = $this->db->get()->result_array();

        $idU = array_column($ID_User, 'ID_User');

        //Retornamos el ID_SUser y el ID_User
        $dataSaved = [
            "ID_SU" => $id[0],
            "ID_U" => $idU[0],
            "saved" => $saved,
        ];

        return $dataSaved;
    }
    
    // Validar usuario al registrarse
    public function userValidation($emailKey, $ID_SU){

        $this->db->select('pswd, UserValidated'); // Seleccionar les columnes
        $this->db->from('SuperUser');// Seleccionem la taula
        $this->db->where('ID_SU', $ID_SU);// filtrem per columnes
        $query = $this->db->get()->result_array();

        $pass = array_column($query, 'pswd');
        $userValidated = array_column($query, 'UserValidated');

        $hash = md5($pass[0] . $ID_SU);

        $userExist=false;
        $validated=false;

        if($hash == $emailKey){
            $userExist=true;
        }
        if($userValidated[0] == 0){
            $this->db->set('UserValidated', '1');
            $this->db->where('ID_SU', $ID_SU);
            $validated = $this->db->update('SuperUser');
        }

        $response = [
                "validated" => $validated,
                "userExist" => $userExist
            ];
        return $response;
    }
    //Get user configuratión
    public function getConfig($ID_SU)
    {
        // Get user data and user config data
        $this->db->from('SuperUser');
        $this->db->join('User', 'SuperUser.cfgDefUser = User.ID_User');
        $this->db->join('Languages', 'SuperUser.cfgDefUser = User.ID_User AND User.ID_ULanguage = Languages.ID_Language', 'right');
        $this->db->where('ID_USU', $ID_SU);
        $query1 = $this->db->get()->result_array();
        $userConfig = $query1[0];

        //Get Users
        $this->db->select('ID_User, ID_ULanguage, cfgExpansionLanguage');
        $this->db->from('User');
        $this->db->where('ID_USU', $ID_SU);
        $this->db->order_by('User.ID_ULanguage', 'asc');
        $query2 = $this->db->get()->result_array();

        //Get Languages
        $this->db->select('ID_Language, languageName');
        $this->db->from('Languages');
        $this->db->order_by('Languages.ID_Language', 'asc');
        $query3 = $this->db->get()->result_array();

        
        // Guardamos los datos como objeto
        $Array = [
            'userConfig' => $userConfig,
            'users' => $query2,
            'languages' => $query3,
        ];
        
        // Save user config data in the COOKIES
        $this->session->set_userdata('idusu', $userConfig["ID_User"]);
        $this->session->set_userdata('uname', $userConfig["SUname"]);
        $this->session->set_userdata('ulanguage', $userConfig["cfgExpansionLanguage"]);
        //MODIF: Cuando lo juntemos con jose dará fallo. Jose tiene que cambiar "uinterfacelangauge" por este
        $this->session->set_userdata('uinterfacelangauge', $userConfig["ID_ULanguage"]);
        $this->session->set_userdata('uinterfacelangtype', $userConfig["type"]);
        $this->session->set_userdata('uinterfacelangnadjorder', $userConfig["nounAdjOrder"]);
        $this->session->set_userdata('uinterfacelangncorder', $userConfig["nounComplementOrder"]);
        $this->session->set_userdata('uinterfacelangabbr', $userConfig["languageabbr"]);
        $this->session->set_userdata('cfgAutoEraseSentenceBar', $userConfig["cfgAutoEraseSentenceBar"]);
        $this->session->set_userdata('isfem', $userConfig["cfgIsFem"]);
        $this->session->set_userdata('cfgExpansionOnOff', $userConfig["cfgExpansionOnOff"]);
        $this->session->set_userdata('cfgPredBarNumPred', $userConfig["cfgPredBarNumPred"]);

        // Save Expansion language in the COOKIES
        $this->db->select('canExpand');
        $this->db->where('ID_Language', $userConfig["cfgExpansionLanguage"]);
        $query3 = $this->db->get('Languages');

        if ($query3->num_rows() > 0) {
            $aux = $query3->result();
            $canExpand = $aux[0]->canExpand;

            if ($canExpand == '1'){
                $this->session->set_userdata('ulangabbr', $userConfig["languageabbr"]);
            }else{
                $this->session->set_userdata('ulangabbr', 'ES');
            }
        }

        return $Array;
    }
    //Return last $day days from historic table
    function getHistoric($idusu, $day){
        $date = date('Y-m-d', strtotime("-".$day." day"));
        $this->db->from('S_Historic');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('sentenceDate >', $date);
        $this->db->where('ID_SHUser', $idusu);
        $this->db->join('R_S_HistoricPictograms', 'S_Historic.ID_SHistoric = R_S_HistoricPictograms.ID_RSHPSentence');
        $this->db->join('Pictograms', 'R_S_HistoricPictograms.pictoid = Pictograms.pictoid');
        $this->db->order_by('sentenceDate', 'desc');
        $this->db->order_by('ID_SHistoric', 'desc');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;

        return $output;
    }
    //delete all historic after last 30 days
    function deleteHistoric(){
        $date = date('Y-m-d', strtotime("-30 day"));
        $this->db->from('S_Historic');
        $this->db->join('R_S_HistoricPictograms', 'S_Historic.ID_SHistoric = R_S_HistoricPictograms.ID_RSHPSentence');
        $this->db->where('sentenceDate <', $date);
        $query = $this->db->delete();
        return;
    }
    //get historic sentence
    function getHistoricSentence($idusu, $ID_SHistoric){
        $this->db->where('ID_SHistoric', $ID_SHistoric);
        $this->db->where('ID_SHUser', $idusu);
        $query = $this->db->get('S_Historic');
        return $query->result_array()[0];
    }
    //get historic sentences with pictos
    function getSentencesWithPictos($idusu, $ID_Folder){
        $this->db->from('S_Sentence');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('ID_SFolder', $ID_Folder);
        $this->db->where('ID_SSUser', $idusu);
        $this->db->join('R_S_SentencePictograms', 'S_Sentence.ID_SSentence = R_S_SentencePictograms.ID_RSSPSentence');
        $this->db->join('Pictograms', 'R_S_SentencePictograms.pictoid = Pictograms.pictoid');
        $this->db->order_by('R_S_SentencePictograms.ID_RSSPSentencePicto', 'asc');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;

        return $output;
    }
    //Get higher sentence Id from S_Sentence table
    public function getHigherSentenceId($ID_Folder, $idusu){
        $this->db->from('S_Sentence');
        $this->db->where('ID_SFolder', $ID_Folder);
        $this->db->where('ID_SSUser', $idusu);
        $this->db->order_by('ID_SSentence', 'desc');
        $query = $this->db->get()->result_array();

        return $query[0]['ID_SSentence'];
    }
    //get historic folders ordered by folderOrder descended
    public function getHistoricFolders($idusu){
        $this->db->from('S_Folder');// Seleccionem la taula
        $this->db->where('ID_SFUser', $idusu);// filtrem per columnes
        $this->db->order_by('folderOrder', 'desc');
        $data = $this->db->get()->result_array();
        
        return $data;
    }
    // Change historic folder data.
    public function changeHistFolder($idusu, $ID_Folder, $data){

        $this->db->where('ID_Folder', $ID_Folder);
        $this->db->where('ID_SFUser', $idusu);
        $saved = $this->db->update('S_Folder', $data);

        return $saved;
    }
    //get sentences in folder ordered by posInFolder
    function getSentencesOrdered($idusu, $ID_SFolder){
        $this->db->from('S_Sentence');
        $this->db->where('ID_SFolder', $ID_SFolder);
        $this->db->where('ID_SSUser', $idusu);
        $this->db->order_by('posInFolder', 'asc');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;

        return $output;
    }
    
    function restartErrorVoices($idusu) {
        
        $data = array(
            'errorTemp' => '0',
        );
        
        $this->db->where('ID_User', $idusu);
        $this->db->update('User', $data);
    }
}
