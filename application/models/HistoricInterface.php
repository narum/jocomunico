<?php

class HistoricInterface extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    function getSFolders($idusu) {
        $this->db->order_by('folderOrder', 'asc');
        $this->db->where('ID_SFUser', $idusu);
        $query = $this->db->get('S_Folder');

        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;

        return $output;
    }
    function getHistoric($idusu, $day){
        $date = date('Y-m-d', strtotime("-".$day." day"));
        
        $this->db->where('sentenceDate >', $date);
        $this->db->where('ID_SHUser', $idusu);
        $this->db->where('generatorString IS NOT NULL', null, false);
        $this->db->order_by('sentenceDate', 'desc');
        $this->db->order_by('ID_SHistoric', 'desc');
        $query = $this->db->get('S_Historic');

        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;

        return $output;
    }
    
    function getPictosHistoric($IDHistoric){
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('ID_SHistoric', $IDHistoric);
        $this->db->join('R_S_HistoricPictograms', 'S_Historic.ID_SHistoric = R_S_HistoricPictograms.ID_RSHPSentence', 'left');
        $this->db->join('Pictograms', 'R_S_HistoricPictograms.pictoid = Pictograms.pictoid', 'left');
        $query = $this->db->get('S_Historic');
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;

        return $output;
    }
    
    function getCountHistoric($idusu, $day){
        $date = date('Y-m-d', strtotime("-".$day." day"));
        $this->db->where('sentenceDate >', $date);
        $this->db->where('ID_SHUser', $idusu);
        $this->db->where('generatorString IS NOT NULL', null, false);
        $query = $this->db->get('S_Historic');

        return $query->num_rows();
    }
    
    function getSentenceFolder($idusu, $folder){
        $this->db->where('ID_SSUser', $idusu);
        $this->db->where('ID_SFolder', $folder);
        $this->db->order_by('posInFolder', 'asc');
        $query = $this->db->get('S_Sentence');

        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;

        return $output;
    }
    
    function getCountSentenceFolder($idusu, $folder){
        $this->db->where('ID_SSUser', $idusu);
        $this->db->where('ID_SFolder', $folder);
        $query = $this->db->get('S_Sentence');

        return $query->num_rows();
    }
    
    function getPictosFolder($IDSentence){
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('ID_SSentence', $IDSentence);
        $this->db->join('R_S_SentencePictograms', 'S_Sentence.ID_SSentence = R_S_SentencePictograms.ID_RSSPSentence', 'left');
        $this->db->join('Pictograms', 'R_S_SentencePictograms.pictoid = Pictograms.pictoid', 'left');
        $query = $this->db->get('S_Sentence');
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;

        return $output;
    }

}
