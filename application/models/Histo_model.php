<?php 

class Histo_model extends CI_Model {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    public function Login()
    {
        $userlanguage = 1;         //modificar
        $userid = 1;               //modificar
        
        $data = array(
        'userid' => $userid,
        'pictoid' => $pictoid,
        'languageid' => $userlanguage
        );

        $this->db->insert('HistorialPicSearch', $data);
        // Produces: INSERT INTO mytable (title, name, date) VALUES ('My title', 'My name', 'My date')
        $save = "saved!";
        
        return $save;
    }
        
}