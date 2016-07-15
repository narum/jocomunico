<?php

class Audio_model extends CI_Model {
    
    function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 
     * @param bool/int $id if set to false, all voices are returned,
     * else, the voice with the set $id is returned
     * @return array $output a row for each returned voice with all the fields
     * from the database
     */
    public function getOnlineVoices($id) 
    {
        if ($id) $this->db->where('ID_Voice', $id);
        
        $output = array();
        $query = $this->db->get('Voices');
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        
        return $output;
    }
    
    /**
     * 
     * @param int $idusu
     * @return array $output a row with all the fields from the database
     */
    public function getUserInfo($idusu) 
    {
        $this->db->where('ID_User', $idusu);
        
        $output = array();
        $query = $this->db->get('User');
        
        if ($query->num_rows() > 0) {
            $aux = $query->result();
            $output = $aux[0];
        }
        
        return $output;
    }
    
    /**
     * 
     * @param int $md5
     * @return bool/string $output false if the audio is not found, the
     * file name's path if it is found
     */
    public function isAudioInDatabase($md5) 
    {
        $this->db->where('mp3TSMd5Encoded', $md5);
        
        $output = array();
        $query = $this->db->get('MP3');
        
        if ($query->num_rows() > 0) {
            $aux = $query->result();
            $output = $aux[0]->mp3Path;
        }
        else $output = false;
        
        return $output;
    }
    
    /**
     * Inserts the info of a new audio file into the database
     * @param type $text
     * @param type $md5
     * @param type $filename
     */
    public function saveAudioFileToDatabase($text, $md5, $filename) 
    {
        $data = array(
            'mp3TextSentence' => $text,
            'mp3TSMd5Encoded' => $md5,
            'mp3Path' => $filename,
        );
        $this->db->insert('MP3', $data);
    }
    
    
}
?>