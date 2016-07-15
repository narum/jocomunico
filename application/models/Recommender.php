<?php

class Recommender extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        
        $this->load->library('Myword');
        $this->load->library('Mymatching');
    }
    
    private function SUMcount() {
        $output = null;     
        
        $this->db->select('SUM(P_StatsUserPicto.countx1) as count');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPicto.ID_PSUPUser', $this->session->userdata('idusu'));                             
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));
        $this->db->from('P_StatsUserPicto');
        $this->db->join('PictogramsLanguage', 'P_StatsUserPicto.pictoid = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPicto.pictoid = Pictograms.pictoid', 'left'); 
        $query = $this->db->get();     
       
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;
    }           

    private function unique_multidim_array($array, $key) { 
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if (!in_array($val->$key, $key_array)) {
                $key_array[$i] = $val->$key;
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array; 
    }
    
    private function getIdsElem(){
        $output = array();
        $output = null;
        
        $this->db->select('pictoid');
        $this->db->from('R_S_TempPictograms');
        $this->db->where('ID_RSTPUser', $this->session->userdata('idusu'));
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output; 
    }
    
    private function getTypesElem($pictoid){
        $output = array();
        $output = null;
        
        $this->db->select('pictoType');
        $this->db->from('Pictograms');
        $this->db->where('pictoid', $pictoid);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output; 
    }
    
    private function getNameClass($pictoid){
        $output = array();
        $output = null;
        
        $this->db->select('class');
        $this->db->from('NameClass'.$this->session->userdata('ulangabbr'));
        $this->db->where('nameid', $pictoid);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output; 
    }
    
    private function getAdvType($pictoid){
        $output = array();
        $output = null;
        
        $this->db->select('type');
        $this->db->from('AdvType'.$this->session->userdata('ulangabbr'));
        $this->db->where('advid', $pictoid);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output; 
    }
    
    private function getSubj() {     
        $output = array();
        $output = null;
        
        // Ids of the Pictograms for "I" and "you" in all languages
        $subjList = array(444, 466);
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext');
        $this->db->from('PictogramsLanguage');
        $this->db->join('Pictograms', 'PictogramsLanguage.pictoid = Pictograms.pictoid', 'left');                             
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));
        $this->db->where_in('Pictograms.pictoid', $subjList);
        $query = $this->db->get();     
                
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }        
        return $output;
    }    

    private function getfreqUsuariX2($inputid1) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext');
        $this->db->from('P_StatsUserPictox2');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox2.picto2id = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPictox2.picto2id = Pictograms.pictoid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox2.ID_PSUP2User', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                   
        $this->db->where('P_StatsUserPictox2.picto1id', $inputid1);  
        $this->db->order_by('countx2', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $this->db->limit(3);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;   
    }
    
    private function getfreqUsuariX2NV($inputid1) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext');
        $this->db->from('P_StatsUserPictox2');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox2.picto2id = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPictox2.picto2id = Pictograms.pictoid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox2.ID_PSUP2User', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                   
        $this->db->where('P_StatsUserPictox2.picto1id', $inputid1);  
        $this->db->where('Pictograms.pictoType !=', 'verb');  
        $this->db->order_by('countx2', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $this->db->limit(3);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;   
    }
    
    private function getfreqUsuariX2NonExpan($inputid1) {                            
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext');
        $this->db->from('P_StatsUserPictox2');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox2.picto2id = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPictox2.picto2id = Pictograms.pictoid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox2.ID_PSUP2User', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                   
        $this->db->where('P_StatsUserPictox2.picto1id', $inputid1);  
        $this->db->order_by('countx2', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;   
    } 
    
    private function getfreqUsuariNameX2($inputid1, $fits) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext');
        $this->db->from('P_StatsUserPictox2');       
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox2.picto2id = PictogramsLanguage.pictoid', 'left');
        $this->db->join('Pictograms', 'P_StatsUserPictox2.picto2id = Pictograms.pictoid', 'left'); 
        $this->db->join('NameClass'.$this->session->userdata('ulangabbr'), 'P_StatsUserPictox2.picto2id = NameClass'.$this->session->userdata('ulangabbr').'.nameid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox2.ID_PSUP2User', $this->session->userdata('idusu'));        
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                             
        $this->db->where('P_StatsUserPictox2.picto1id', $inputid1);  
        $this->db->where_in('NameClass'.$this->session->userdata('ulangabbr').'.class', $fits);
        $this->db->order_by('countx2', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output; 
    }
    
    private function getDbSearchQuant($pictoType) {
        $output = array();
        $output = null;           
        
        $this->db->select('P_StatsUserPicto.pictoid, P_StatsUserPicto.countx1 as repes, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->from('P_StatsUserPicto');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPicto.pictoid = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'PictogramsLanguage.pictoid = Pictograms.pictoid', 'left'); 
        $this->db->join('Modifier'.$this->session->userdata('ulangabbr'), 'Pictograms.pictoid = Modifier'.$this->session->userdata('ulangabbr').'.modid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPicto.ID_PSUPUser', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                           
        $this->db->where('Modifier'.$this->session->userdata('ulangabbr').'.type', $pictoType);               
        $this->db->group_by('P_StatsUserPicto.pictoid, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->order_by('repes', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;        
    }    
    
    private function getfreqUsuariQuantX2($inputid1, $fits) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext');
        $this->db->from('P_StatsUserPictox2');       
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox2.picto2id = PictogramsLanguage.pictoid', 'left');
        $this->db->join('Pictograms', 'P_StatsUserPictox2.picto2id = Pictograms.pictoid', 'left'); 
        $this->db->join('Modifier'.$this->session->userdata('ulangabbr'), 'Pictograms.pictoid = Modifier'.$this->session->userdata('ulangabbr').'.modid', 'left');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox2.ID_PSUP2User', $this->session->userdata('idusu'));        
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                             
        $this->db->where('P_StatsUserPictox2.picto1id', $inputid1);  
        $this->db->where('Modifier'.$this->session->userdata('ulangabbr').'.type', $fits);  
        $this->db->order_by('countx2', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output; 
    }
    
    private function getfreqUsuariQuantX3($inputid1, $inputid2, $fits) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext');
        $this->db->from('P_StatsUserPictox3');       
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox3.picto3id = PictogramsLanguage.pictoid', 'left');
        $this->db->join('Pictograms', 'P_StatsUserPictox3.picto3id = Pictograms.pictoid', 'left'); 
        $this->db->join('Modifier'.$this->session->userdata('ulangabbr'), 'Pictograms.pictoid = Modifier'.$this->session->userdata('ulangabbr').'.modid', 'left');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox3.ID_PSUP3User', $this->session->userdata('idusu'));        
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                             
        $this->db->where('P_StatsUserPictox3.picto1id', $inputid1);  
        $this->db->where('P_StatsUserPictox3.picto2id', $inputid2);  
        $this->db->where('Modifier'.$this->session->userdata('ulangabbr').'.type', $fits);  
        $this->db->order_by('countx3', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output; 
    }
    
    private function getfreqUsuariAdvManeraX2($inputid1, $fits) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext');
        $this->db->from('P_StatsUserPictox2');       
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox2.picto2id = PictogramsLanguage.pictoid', 'left');
        $this->db->join('Pictograms', 'P_StatsUserPictox2.picto2id = Pictograms.pictoid', 'left'); 
        $this->db->join('AdvType'.$this->session->userdata('ulangabbr'), 'Pictograms.pictoid = AdvType'.$this->session->userdata('ulangabbr').'.advid', 'left');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox2.ID_PSUP2User', $this->session->userdata('idusu'));        
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                             
        $this->db->where('P_StatsUserPictox2.picto1id', $inputid1);  
        $this->db->where('AdvType'.$this->session->userdata('ulangabbr').'.type', $fits);  
        $this->db->order_by('countx2', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output; 
    }
    
    private function getfreqUsuariAdvManeraX3($inputid1, $inputid2, $fits) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext');
        $this->db->from('P_StatsUserPictox3');       
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox3.picto3id = PictogramsLanguage.pictoid', 'left');
        $this->db->join('Pictograms', 'P_StatsUserPictox3.picto3id = Pictograms.pictoid', 'left'); 
        $this->db->join('AdvType'.$this->session->userdata('ulangabbr'), 'Pictograms.pictoid = AdvType'.$this->session->userdata('ulangabbr').'.advid', 'left');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox3.ID_PSUP3User', $this->session->userdata('idusu'));        
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                             
        $this->db->where('P_StatsUserPictox3.picto1id', $inputid1);  
        $this->db->where('P_StatsUserPictox3.picto2id', $inputid2);  
        $this->db->where('AdvType'.$this->session->userdata('ulangabbr').'.type', $fits);  
        $this->db->order_by('countx3', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output; 
    }
    
    private function getfreqUsuariAdjAdvX2($inputid1, $fits) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext');
        $this->db->from('P_StatsUserPictox2');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox2.picto2id = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPictox2.picto2id = Pictograms.pictoid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox2.ID_PSUP2User', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                   
        $this->db->where('P_StatsUserPictox2.picto1id', $inputid1);  
        $this->db->where('Pictograms.pictoType', $fits);
        $this->db->order_by('countx2', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output; 
    }
    
    private function getfreqUsuariAdjAdvX3($inputid1, $inputid2, $fits) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext');
        $this->db->from('P_StatsUserPictox3');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox3.picto3id = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPictox3.picto2id = Pictograms.pictoid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox3.ID_PSUP3User', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                   
        $this->db->where('P_StatsUserPictox3.picto1id', $inputid1);  
        $this->db->where('P_StatsUserPictox3.picto2id', $inputid2);  
        $this->db->where('Pictograms.pictoType', $fits);
        $this->db->order_by('countx3', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output; 
    }
    
    private function getfreqUsuariOrdinalX2($inputid1, $fits) {
        $output = array();
        $output = null;
                
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext');
        $this->db->from('P_StatsUserPictox2');       
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox2.picto2id = PictogramsLanguage.pictoid', 'left');
        $this->db->join('Pictograms', 'P_StatsUserPictox2.picto2id = Pictograms.pictoid', 'left'); 
        $this->db->join('AdjClass'.$this->session->userdata('ulangabbr'), 'P_StatsUserPictox2.picto2id = AdjClass'.$this->session->userdata('ulangabbr').'.adjid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox2.ID_PSUP2User', $this->session->userdata('idusu'));        
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                             
        $this->db->where('P_StatsUserPictox2.picto1id', $inputid1);  
        $this->db->where('AdjClass'.$this->session->userdata('ulangabbr').'.class', $fits);
        $this->db->order_by('countx2', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output; 
    }
    
    private function getfreqUsuariNameX3($inputid1, $inputid2, $fits) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext');
        $this->db->from('P_StatsUserPictox3');       
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox3.picto3id = PictogramsLanguage.pictoid', 'left');
        $this->db->join('Pictograms', 'P_StatsUserPictox3.picto3id = Pictograms.pictoid', 'left'); 
        $this->db->join('NameClass'.$this->session->userdata('ulangabbr'), 'P_StatsUserPictox3.picto3id = NameClass'.$this->session->userdata('ulangabbr').'.nameid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox3.ID_PSUP3User', $this->session->userdata('idusu'));        
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                             
        $this->db->where('P_StatsUserPictox3.picto1id', $inputid1);  
        $this->db->where('P_StatsUserPictox3.picto2id', $inputid2);  
        $this->db->where_in('NameClass'.$this->session->userdata('ulangabbr').'.class', $fits);
        $this->db->order_by('countx3', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();        
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output; 
    }
        
    private function getfreqUsuariOrdinalX3($inputid1, $inputid2, $fits) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext');
        $this->db->from('P_StatsUserPictox3');       
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox3.picto3id = PictogramsLanguage.pictoid', 'left');
        $this->db->join('Pictograms', 'P_StatsUserPictox3.picto3id = Pictograms.pictoid', 'left'); 
        $this->db->join('AdjClass'.$this->session->userdata('ulangabbr'), 'P_StatsUserPictox3.picto3id = AdjClass'.$this->session->userdata('ulangabbr').'.adjid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox3.ID_PSUP3User', $this->session->userdata('idusu'));        
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                             
        $this->db->where('P_StatsUserPictox3.picto1id', $inputid1);  
        $this->db->where('P_StatsUserPictox3.picto2id', $inputid2);  
        $this->db->where('AdjClass'.$this->session->userdata('ulangabbr').'.class', $fits);
        $this->db->order_by('countx3', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output; 
    }
    
    private function getDbSearchOrdinal($pictoType) {
        $output = array();
        $output = null;

        $this->db->select('P_StatsUserPicto.pictoid, P_StatsUserPicto.countx1 as repes, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->from('P_StatsUserPicto');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPicto.pictoid = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPicto.pictoid = Pictograms.pictoid', 'left'); 
        $this->db->join('AdjClass'.$this->session->userdata('ulangabbr'), 'PictogramsLanguage.pictoid = AdjClass'.$this->session->userdata('ulangabbr').'.adjid', 'left');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPicto.ID_PSUPUser', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                           
        $this->db->where('AdjClass'.$this->session->userdata('ulangabbr').'.class', $pictoType);               
        $this->db->group_by('P_StatsUserPicto.pictoid, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->order_by('repes', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output; 
    }
    
    private function getContextType2Days($pictoType) {  
                
        $output = null;
        $date = array(date("Y-m-d"), date("Y-m-d", strtotime("yesterday")));

        $this->db->select('P_StatsUserPicto.pictoid, P_StatsUserPicto.countx1 as repes, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->from('P_StatsUserPicto');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPicto.pictoid = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPicto.pictoid = Pictograms.pictoid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPicto.ID_PSUPUser', $this->session->userdata('idusu'));               
        $this->db->where_in('P_StatsUserPicto.lastdate', $date);
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                           
        $this->db->where('Pictograms.pictoType', $pictoType);               
        $this->db->group_by('P_StatsUserPicto.pictoid, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->order_by('repes', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;   
    }
    
    private function getContextFitsNClass2Days($fits) {                  
        $output = null;
        $date = array(date("Y-m-d"), date("Y-m-d", strtotime("yesterday")));    
                
        $this->db->select('P_StatsUserPicto.pictoid, P_StatsUserPicto.countx1 as repes, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->from('P_StatsUserPicto');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPicto.pictoid = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPicto.pictoid = Pictograms.pictoid', 'left'); 
        $this->db->join('NameClass'.$this->session->userdata('ulangabbr'), 'Pictograms.pictoid = NameClass'.$this->session->userdata('ulangabbr').'.nameid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPicto.ID_PSUPUser', $this->session->userdata('idusu'));               
        $this->db->where_in('P_StatsUserPicto.lastdate', $date);
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                           
        $this->db->where_in('NameClass'.$this->session->userdata('ulangabbr').'.class', $fits);        
        $this->db->group_by('P_StatsUserPicto.pictoid, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->order_by('repes', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;   
    } 
    
    private function getContextFitsNClassAll($fits) {                            
        $output = null;

        $this->db->select('P_StatsUserPicto.pictoid, P_StatsUserPicto.countx1 as repes, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->from('P_StatsUserPicto');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPicto.pictoid = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPicto.pictoid = Pictograms.pictoid', 'left'); 
        $this->db->join('NameClass'.$this->session->userdata('ulangabbr'), 'Pictograms.pictoid = NameClass'.$this->session->userdata('ulangabbr').'.nameid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPicto.ID_PSUPUser', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                           
        $this->db->where_in('NameClass'.$this->session->userdata('ulangabbr').'.class', $fits);        
        $this->db->group_by('P_StatsUserPicto.pictoid, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->order_by('repes', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;   
    }  
    
    private function getContextTypeAdvManeraAll($pictoType) {       
        $output = array();
        $output = null;

        $this->db->select('P_StatsUserPicto.pictoid, P_StatsUserPicto.countx1 as repes, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->from('P_StatsUserPicto');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPicto.pictoid = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPicto.pictoid = Pictograms.pictoid', 'left'); 
        $this->db->join('AdvType'.$this->session->userdata('ulangabbr'), 'P_StatsUserPicto.pictoid = AdvType'.$this->session->userdata('ulangabbr').'.advid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPicto.ID_PSUPUser', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                           
        $this->db->where('AdvType'.$this->session->userdata('ulangabbr').'.type', $pictoType);               
        $this->db->group_by('P_StatsUserPicto.pictoid, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->order_by('repes', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();                

        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;   
    } 
    
    private function getContextTypeAll($pictoType) {                            
        $output = null;

        $this->db->select('P_StatsUserPicto.pictoid, P_StatsUserPicto.countx1 as repes, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->from('P_StatsUserPicto');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPicto.pictoid = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPicto.pictoid = Pictograms.pictoid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPicto.ID_PSUPUser', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                           
        $this->db->where('Pictograms.pictoType', $pictoType);               
        $this->db->group_by('P_StatsUserPicto.pictoid, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->order_by('repes', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;   
    }
    
    private function getContextTypeAllSE($pictoType, $inputid) {
        $output = null;
        $this->db->select('P_StatsUserPicto.pictoid, P_StatsUserPicto.countx1 as repes, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->from('P_StatsUserPicto');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPicto.pictoid = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPicto.pictoid = Pictograms.pictoid', 'left'); 
        $this->db->join('Adjective'.$this->session->userdata('ulangabbr'), 'Pictograms.pictoid = Adjective'.$this->session->userdata('ulangabbr').'.adjid', 'left');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPicto.ID_PSUPUser', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                           
        $this->db->where('Pictograms.pictoType', $pictoType);
        $this->db->where('Adjective'.$this->session->userdata('ulangabbr').'.defaultverb', $inputid);
        $this->db->group_by('P_StatsUserPicto.pictoid, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->order_by('repes', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;   
    }
    
    public function startsWith($haystack, $needle) {
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }
    
    private function boolDetPos($pictoid) {
        $output = null;
        
        $this->db->select('type');
        $this->db->from('Modifier'.$this->session->userdata('ulangabbr'));              
        $this->db->where('modid', $pictoid);  
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        
        $res = false;
        if ($output[0]->type == 'det' || $this->startsWith($output[0]->type, 'pos')) $res = true;
        return $res;
    }
   
    private function getMMFits($tipus, $case){      
        $output = array();
        $output = null;
        $caseTipus = $case."tipus";
        // puede haber locfrom opt sin locfromtipus
        if($tipus[0]->$caseTipus != null) {   
            $matching = new Mymatching();
            $key = $matching->nounsFitKeys[$tipus[0]->$caseTipus];        
            $keyw = array_keys($matching->nounsFit[$key], 0);
            for ($i = 0; $i < sizeof($keyw); $i++) {
                $output[] = array_keys($matching->nounsFitKeys, $keyw[$i])[0];
            }
        }
        return $output;
    }
    
    private function get1Opt($picto1id, $case) {
        $output = array();
        $output = null;
        $this->db->select($case);    
        $this->db->from('Pattern'.$this->session->userdata('ulangabbr'));        
        $this->db->where('verbid', $picto1id);     
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;                   
    }
        
    private function getCaseTipus($picto1id, $case, $b) {
        $output = array();
        $output = null;
        $this->db->select($case.'tipus');    
        $this->db->from('Pattern'.$this->session->userdata('ulangabbr'));        
        $this->db->where('verbid', $picto1id);
        $this->db->where($case, $b);     
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;                   
    }
    
    private function get1OptFitsX2($inputid1, $case, $VF, $TSize, $fits) {
        if ($case == "locat") $fits = "lloc";
        else if ($case == "acomp") $fits = "human";
        
        if ($case == "theme") {
            if ($fits == 'adj' || $fits == 'adv') {
                // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. usuari)
                $res = $this->getfreqUsuariAdjAdvX2($inputid1, $fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }
            else if ($fits == 'ordinal') {
                // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. usuari)
                $res = $this->getfreqUsuariOrdinalX2($inputid1, $fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }
            else if ($fits != null && $fits != 'modif' && $fits != 'quant') {
                // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. usuari)
                $res = $this->getfreqUsuariNameX2($inputid1, $fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }
        }
        else if ($case == "manera") {
            if ($fits == 'quant') { // (case: manera)
                // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. usuari)
                $res = $this->getfreqUsuariQuantX2($inputid1, $fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }
            else if ($fits == 'manera') {  // (case: manera)
                // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. usuari)
                $res = $this->getfreqUsuariAdvManeraX2($inputid1, $fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }
            else if ($fits != null && $fits != 'ordinal' && $fits != 'modif' && $fits != 'adj') {
                // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. usuari)
                $res = $this->getfreqUsuariNameX2($inputid1, $fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }
        } 
        else if ($fits != null && $fits != 'ordinal' && $fits != 'modif' && $fits != 'adj' && $fits != 'adv' && $fits != 'quant') {
            // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. usuari)
            $res = $this->getfreqUsuariNameX2($inputid1, $fits);
            $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
        }            

        if ($case == "theme") {
            if ($fits == 'adj' || $fits == 'adv') {
                // Algorisme V6 - Predictor de context (adj i adv) total
                if ($fits == 'adj' && $inputid1 == '86') $res = $this->getContextTypeAllSE($fits, $inputid1);
                else if ($fits == 'adj' && $inputid1 == '100') $res = $this->getContextTypeAllSE($fits, $inputid1);
                else $res = $this->getContextTypeAll($fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }
            else if ($fits == 'ordinal') { // (case: theme)
                // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. context)
                $res = $this->getDbSearchOrdinal($fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }
            else if ($fits != null && $fits != 'modif' && $fits != 'quant') {
                // Algorisme V6 - Predictor de context ($fits) últims 2 dies
                $res = $this->getContextFitsNClass2Days($fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);

                // Algorisme V6 - Predictor de context ($fits) total              
                $res = $this->getContextFitsNClassAll($fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            } 
        }
        else if ($case == "manera") {
           if ($fits == 'quant') { // (case: manera)
                // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. context)
                $res = $this->getDbSearchQuant($fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
                //return $res;
            }
            if ($fits == 'manera') {  // (case: manera)
                // Algorisme V6 - Predictor de context (adv manera) total    
                $res = $this->getContextTypeAdvManeraAll($fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
                //return $contextTypeAdvManeraAll;
            }
            else if ($fits != null && $fits != 'ordinal' && $fits != 'modif' && $fits != 'adj') { // ¿ algun caso ?
                // Algorisme V6 - Predictor de context (name) últims 2 dies
                $res = $this->getContextType2Days('name');
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);

                // Algorisme V6 - Predictor de context (name) total              
                $res = $this->getContextTypeAll('name');
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }  
        }                       
        else if ($fits != null && $fits != 'ordinal' && $fits != 'modif' && $fits != 'adj' && $fits != 'adv' && $fits != 'quant') {
            // Algorisme V6 - Predictor de context ($fits) últims 2 dies
            $res = $this->getContextFitsNClass2Days($fits);
            $VF = $this->rellenaVFX2X3($VF, $res, $TSize);

            // Algorisme V6 - Predictor de context ($fits) total              
            $res = $this->getContextFitsNClassAll($fits);
            $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
        }           
        return $VF;
    }
    
    private function get1OptFitsX3($inputid1, $inputid2, $case, $VF, $TSize, $fits, $inputid) {
        if ($case == "locat") $fits = "lloc";
        else if ($case == "acomp") $fits = "human";
        
        if ($case == "theme") {
            if ($fits == 'adj' || $fits == 'adv') {
                // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. usuari)
                $res = $this->getfreqUsuariAdjAdvX3($inputid1, $inputid2, $fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }
            else if ($fits == 'ordinal') {
                // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. usuari)
                $res = $this->getfreqUsuariOrdinalX3($inputid1, $inputid2, $fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }
            else if ($fits != null && $fits != 'modif' && $fits != 'quant') {
                // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. usuari)
                $res = $this->getfreqUsuariNameX3($inputid1, $inputid2, $fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);                        
            }
        }
        else if ($case == "manera") {
            if ($fits == 'quant') { // (case: manera)
                // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. usuari)
                $res = $this->getfreqUsuariQuantX3($inputid1, $inputid2, $fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }
            else if ($fits == 'manera') {  // (case: manera)
                // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. usuari)
                $res = $this->getfreqUsuariAdvManeraX3($inputid1, $inputid2, $fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }
            else if ($fits != null && $fits != 'ordinal' && $fits != 'modif' && $fits != 'adj') {
                // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. usuari)
                $res = $this->getfreqUsuariNameX3($inputid1, $inputid2, $fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }
        } 
        else if ($fits != null && $fits != 'ordinal' && $fits != 'modif' && $fits != 'adj' && $fits != 'adv' && $fits != 'quant') {
            // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. usuari)
            $res = $this->getfreqUsuariNameX3($inputid1, $inputid2, $fits);
            $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
        }            

        if ($case == "theme") {
            if ($fits == 'adj' || $fits == 'adv') {
                // Algorisme V6 - Predictor de context (adj i adv) total 
                if ($fits == 'adj' && $inputid == '86') $res = $this->getContextTypeAllSE($fits, $inputid);
                else if ($fits == 'adj' && $inputid == '100') $res = $this->getContextTypeAllSE($fits, $inputid);
                else $res = $this->getContextTypeAll($fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }
            else if ($fits == 'ordinal') { // (case: theme)
                // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. context)
                $res = $this->getDbSearchOrdinal($fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }
            else if ($fits != null && $fits != 'modif' && $fits != 'quant') {
                // Algorisme V6 - Predictor de context ($fits) últims 2 dies
                $res = $this->getContextFitsNClass2Days($fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);

                // Algorisme V6 - Predictor de context ($fits) total              
                $res = $this->getContextFitsNClassAll($fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            } 
        }
        else if ($case == "manera") {
           if ($fits == 'quant') { // (case: manera)
                // Algorismes V3 i V4 - Predictor verbs I i II (basat en freq. context)
                $res = $this->getDbSearchQuant($fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
                //return $res;
            }
            if ($fits == 'manera') {  // (case: manera)
                // Algorisme V6 - Predictor de context (adv manera) total
                $res = $this->getContextTypeAdvManeraAll($fits);
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
                //return $contextTypeAdvManeraAll;
            }
            else if ($fits != null && $fits != 'ordinal' && $fits != 'modif' && $fits != 'adj') { // ¿ algun caso ?
                // Algorisme V6 - Predictor de context (name) últims 2 dies
                $res = $this->getContextType2Days('name');
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);

                // Algorisme V6 - Predictor de context (name) total              
                $res = $this->getContextTypeAll('name');
                $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
            }  
        }                       
        else if ($fits != null && $fits != 'ordinal' && $fits != 'modif' && $fits != 'adj' && $fits != 'adv' && $fits != 'quant') {
            // Algorisme V6 - Predictor de context ($fits) últims 2 dies
            $res = $this->getContextFitsNClass2Days($fits);
            $VF = $this->rellenaVFX2X3($VF, $res, $TSize);

            // Algorisme V6 - Predictor de context ($fits) total              
            $res = $this->getContextFitsNClassAll($fits);
            $VF = $this->rellenaVFX2X3($VF, $res, $TSize);
        }
        return $VF;
    }

    private function get1OptFits($inputid1, $case, $b) {
        $fits = null;
        $tipus = $this->getCaseTipus($inputid1, $case, $b);
        $caseTipus = $case."tipus";
        if ($tipus != null && $tipus[0]->$caseTipus != 'adj' && $tipus[0]->$caseTipus != 'adv' && $tipus[0]->$caseTipus != 'modif' && $tipus[0]->$caseTipus != 'quant' && $tipus[0]->$caseTipus != 'verb' && $tipus[0]->$caseTipus != 'ordinal') {
            $fits = $this->getMMFits($tipus, $case);
        }
        
        if ($tipus != null && $tipus[0]->$caseTipus == 'verb') {
            $fits = 'verb';
        }
        else if ($tipus != null && $tipus[0]->$caseTipus == 'adj') {
            $fits = 'adj';
        }
        else if ($tipus != null && $case == "manera" && $tipus[0]->$caseTipus == 'adv') {
            $fits = 'manera';
        }
        else if ($tipus != null && $tipus[0]->$caseTipus == 'adv') {
            $fits = 'adv';
        }
        else if ($tipus != null && $tipus[0]->$caseTipus == 'quant') {
            $fits = 'quant';
        }
        else if ($tipus != null && $tipus[0]->$caseTipus == 'ordinal') {
            $fits = 'ordinal';
        }
        else if ($tipus != null && $case == 'locto') {
            $fits = 'lloc';
        }
        else if ($tipus != null && $case == 'locfrom') {
            $fits = 'lloc';
        }
        return $fits;
    }
	
	private function getHora (){
            return date('G', time());
	}
	
	private function getDia() {
            return date('D', time());
	}
        
    private function getContextTypeAllDeep($pictoType, $minCount) {                            
        $output = null;

        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext, (P_StatsUserPicto.'.($this->getHora()-1).'h+P_StatsUserPicto.'.$this->getHora().'h+P_StatsUserPicto.'.($this->getHora()+1).'h+P_StatsUserPicto.'.$this->getDia().'+P_StatsUserPicto.'.$this->getDia().') as repes');
        $this->db->from('P_StatsUserPicto');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPicto.pictoid = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPicto.pictoid = Pictograms.pictoid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPicto.ID_PSUPUser', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                           
        $this->db->where('Pictograms.pictoType', $pictoType);    
        $this->db->group_by('P_StatsUserPicto.pictoid, PictogramsLanguage.pictotext, Pictograms.imgPicto');
        $this->db->having('repes >=', $minCount);
        $this->db->order_by('repes', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
            foreach($output as $value) {
                $value->repes = $value->repes*10;
            }
        }
        return $output;   
    }
    
    private function getfreqUsuariX3NonExpanDeep($inputid1, $inputid2, $minCount) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext, (P_StatsUserPictox3.'.($this->getHora()-1).'h+P_StatsUserPictox3.'.$this->getHora().'h+P_StatsUserPictox3.'.($this->getHora()+1).'h+P_StatsUserPictox3.'.$this->getDia().'+P_StatsUserPictox3.'.$this->getDia().') as count');
        $this->db->from('P_StatsUserPictox3');       
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox3.picto3id = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPictox3.picto3id = Pictograms.pictoid', 'left');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox3.ID_PSUP3User', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                              
        $this->db->where('P_StatsUserPictox3.picto1id', $inputid1);  
        $this->db->where('P_StatsUserPictox3.picto2id', $inputid2);  
        $this->db->having('count >=', $minCount);
        $this->db->order_by('count', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
            foreach($output as $value) {
                $value->count = $value->count*10;
            }
        }
        return $output;   
    }
    
    private function getfreqUsuariX3Deep($inputid1, $inputid2, $minCount) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext, (P_StatsUserPictox3.'.($this->getHora()-1).'h+P_StatsUserPictox3.'.$this->getHora().'h+P_StatsUserPictox3.'.($this->getHora()+1).'h+P_StatsUserPictox3.'.$this->getDia().'+P_StatsUserPictox3.'.$this->getDia().') as count');
        $this->db->from('P_StatsUserPictox3');       
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox3.picto3id = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPictox3.picto3id = Pictograms.pictoid', 'left');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox3.ID_PSUP3User', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                              
        $this->db->where('P_StatsUserPictox3.picto1id', $inputid1);  
        $this->db->where('P_StatsUserPictox3.picto2id', $inputid2);  
        $this->db->having('count >=', $minCount);
        $this->db->order_by('count', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $this->db->limit(3);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
            foreach($output as $value) {
                $value->count = $value->count*10;
            }
        }
        return $output;   
    }
    
    private function getfreqUsuariX2DeepNV($inputid1, $minCount) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext, (P_StatsUserPictox2.'.($this->getHora()-1).'h+P_StatsUserPictox2.'.$this->getHora().'h+P_StatsUserPictox2.'.($this->getHora()+1).'h+P_StatsUserPictox2.'.$this->getDia().'+P_StatsUserPictox2.'.$this->getDia().') as count');
        $this->db->from('P_StatsUserPictox2');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox2.picto2id = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPictox2.picto2id = Pictograms.pictoid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox2.ID_PSUP2User', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                   
        $this->db->where('P_StatsUserPictox2.picto1id', $inputid1);
        $this->db->where('Pictograms.pictoType', 'verb');
        $this->db->having('count >=', $minCount);   
        $this->db->order_by('count', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $this->db->limit(3);     
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
            foreach($output as $value) {
                $value->count = $value->count*10;
            }
        }
        return $output;   
    }
    
    private function getfreqUsuariX2Deep($inputid1, $minCount) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext, (P_StatsUserPictox2.'.($this->getHora()-1).'h+P_StatsUserPictox2.'.$this->getHora().'h+P_StatsUserPictox2.'.($this->getHora()+1).'h+P_StatsUserPictox2.'.$this->getDia().'+P_StatsUserPictox2.'.$this->getDia().') as count');
        $this->db->from('P_StatsUserPictox2');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox2.picto2id = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPictox2.picto2id = Pictograms.pictoid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox2.ID_PSUP2User', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                   
        $this->db->where('P_StatsUserPictox2.picto1id', $inputid1);  
        $this->db->having('count >=', $minCount);   
        $this->db->order_by('count', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $this->db->limit(3);     
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
            foreach($output as $value) {
                $value->count = $value->count*10;
            }
        }
        return $output;   
    }
    
    private function getfreqUsuariX2NonExpanDeep($inputid1, $minCount) {                            
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext, (P_StatsUserPictox2.'.($this->getHora()-1).'h+P_StatsUserPictox2.'.$this->getHora().'h+P_StatsUserPictox2.'.($this->getHora()+1).'h+P_StatsUserPictox2.'.$this->getDia().'+P_StatsUserPictox2.'.$this->getDia().') as count');
        $this->db->from('P_StatsUserPictox2');              
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox2.picto2id = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPictox2.picto2id = Pictograms.pictoid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox2.ID_PSUP2User', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                                   
        $this->db->where('P_StatsUserPictox2.picto1id', $inputid1);  
        $this->db->having('count >=', $minCount);
        $this->db->order_by('count', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
            foreach($output as $value) {
                $value->count = $value->count*10;
            }
        }
        return $output;   
    } 
    
    private function getfreqUsuariX1DeepNV($minCount) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext, (P_StatsUserPicto.'.($this->getHora()-1).'h+P_StatsUserPicto.'.$this->getHora().'h+P_StatsUserPicto.'.($this->getHora()+1).'h+P_StatsUserPicto.'.$this->getDia().'+P_StatsUserPicto.'.$this->getDia().') as count');
        $this->db->from('P_StatsUserPicto');
        $this->db->join('PictogramsLanguage', 'P_StatsUserPicto.pictoid = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPicto.pictoid = Pictograms.pictoid', 'left');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPicto.ID_PSUPUser', $this->session->userdata('idusu'));                             
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                             
        $this->db->where('Pictograms.pictoType !=', 'verb');                             
        $this->db->having('count >=', $minCount);
        $this->db->order_by('count', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();      

        if ($query->num_rows() > 0) {
            $output = $query->result();
            foreach($output as $value) {
                $value->count = $value->count*10;
            }
        }

        return $output;
    }
    
    private function getfreqUsuariX1Deep($minCount) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext, (P_StatsUserPicto.'.($this->getHora()-1).'h+P_StatsUserPicto.'.$this->getHora().'h+P_StatsUserPicto.'.($this->getHora()+1).'h+P_StatsUserPicto.'.$this->getDia().'+P_StatsUserPicto.'.$this->getDia().') as count');
        $this->db->from('P_StatsUserPicto');
        $this->db->join('PictogramsLanguage', 'P_StatsUserPicto.pictoid = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPicto.pictoid = Pictograms.pictoid', 'left');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPicto.ID_PSUPUser', $this->session->userdata('idusu'));                             
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                             
        $this->db->having('count >=', $minCount);
        $this->db->order_by('count', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();      

        if ($query->num_rows() > 0) {
            $output = $query->result();
            foreach($output as $value) {
                $value->count = $value->count*10;
            }
        }

        return $output;
    }

    private function getfreqUsuariX1() {
        $output = array();
        $output = null;     
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext, P_StatsUserPicto.countx1 as count');
        $this->db->from('P_StatsUserPicto');
        $this->db->join('PictogramsLanguage', 'P_StatsUserPicto.pictoid = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPicto.pictoid = Pictograms.pictoid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPicto.ID_PSUPUser', $this->session->userdata('idusu'));                             
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));
        $this->db->order_by('count', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();     
       
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;
    }
    
    private function getfreqUsuariX1NV() {
        $output = array();
        $output = null;     
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext, P_StatsUserPicto.countx1 as count');
        $this->db->from('P_StatsUserPicto');
        $this->db->join('PictogramsLanguage', 'P_StatsUserPicto.pictoid = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPicto.pictoid = Pictograms.pictoid', 'left'); 
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPicto.ID_PSUPUser', $this->session->userdata('idusu'));                             
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));
        $this->db->where('Pictograms.pictoType !=', 'verb');                             
        $this->db->order_by('count', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();     
       
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;
    }
    
    private function getfreqUsuariX3($inputid1, $inputid2) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext');
        $this->db->from('P_StatsUserPictox3');       
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox3.picto3id = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPictox3.picto3id = Pictograms.pictoid', 'left');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox3.ID_PSUP3User', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                              
        $this->db->where('P_StatsUserPictox3.picto1id', $inputid1);  
        $this->db->where('P_StatsUserPictox3.picto2id', $inputid2);  
        $this->db->order_by('countx3', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $this->db->limit(3);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;   
    }
    
    private function getfreqUsuariX3NonExpan($inputid1, $inputid2) {
        $output = array();
        $output = null;
        
        $this->db->select('Pictograms.imgPicto, Pictograms.pictoid, PictogramsLanguage.pictotext');
        $this->db->from('P_StatsUserPictox3');       
        $this->db->join('PictogramsLanguage', 'P_StatsUserPictox3.picto3id = PictogramsLanguage.pictoid', 'left'); 
        $this->db->join('Pictograms', 'P_StatsUserPictox3.picto3id = Pictograms.pictoid', 'left');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('P_StatsUserPictox3.ID_PSUP3User', $this->session->userdata('idusu'));               
        $this->db->where('PictogramsLanguage.languageid', $this->session->userdata('ulanguage'));                                              
        $this->db->where('P_StatsUserPictox3.picto1id', $inputid1);  
        $this->db->where('P_StatsUserPictox3.picto2id', $inputid2);  
        $this->db->order_by('countx3', 'desc');
        $this->db->order_by('PictogramsLanguage.pictofreq', 'desc');
        $this->db->order_by('Pictograms.pictoid', 'random');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        return $output;   
    }

    private function insertFloorVF($VF, $Prediction, $FSize) {
        $k = 0;
        foreach($Prediction as $value) {
            if (sizeof($VF) == 0) {
                $VF = array();
                $VF[] = $value;
            }
                                    
            $repe = false;
            $paraulesFrase = $this->getIdsElem();
            for ($i = 0; $i < sizeof($paraulesFrase); $i++) {
                if ($value->pictoid == $paraulesFrase[$i]->pictoid) {
                    $repe = true;
                    break;
                }
            }
            
            if (!$repe) {   
                for ($i = 0; $i < sizeof($VF); $i++) {
                    if($k == floor($FSize/2) || $value->pictoid == $VF[$i]->pictoid) { break; }
                    else if ($value->pictoid != $VF[$i]->pictoid && $i+1 === sizeof($VF)) {
                        $VF[] = $value;
                        $k++;
                    }
                }
            }
        }
        return $VF;
    }
    
    private function insertCeilVF($VF, $Prediction, $FSize) {
        $k = 0;
        foreach($Prediction as $value) {
            $repe = false;
            $paraulesFrase = $this->getIdsElem();
            for ($i = 0; $i < sizeof($paraulesFrase); $i++) {
                if ($value->pictoid == $paraulesFrase[$i]->pictoid) {
                    $repe = true;
                    break;
                }
            }
            
            if (!$repe) {  
                if (sizeof($VF) == 0) {
                    $VF = array();
                    array_push($VF,$value);
                } 
                for ($i = 0; $i < sizeof($VF); $i++) {
                    if($k == ceil($FSize/2) || $value->pictoid == $VF[$i]->pictoid) { break; }
                    else if ($value->pictoid != $VF[$i]->pictoid && $i+1 === sizeof($VF)) {
                        array_push($VF,$value);
                        $k++;
                    }
                }            
            }
        }
        return $VF;
    }
    
    private function rellenaVFX2X3($VF, $Prediction, $TSize) {
        foreach($Prediction as $value) {            
            $repe = false;
            $paraulesFrase = $this->getIdsElem();
            for ($i = 0; $i < sizeof($paraulesFrase); $i++) {
                if ($value->pictoid == $paraulesFrase[$i]->pictoid) {
                    $repe = true;
                    break;
                }
            }
            
            if (!$repe) {
                if (sizeof($VF) == 0) {
                    $VF = array();
                    array_push($VF,$value);
                } 
                for ($i = 0; sizeof($VF) < $TSize && $i < sizeof($VF); $i++) {
                    if ($value->pictoid == $VF[$i]->pictoid) { break; }
                    else if ($value->pictoid != $VF[$i]->pictoid && $i+1 === sizeof($VF)) {
                        array_push($VF,$value);
                    }
                }
            }
        }
        return $VF;
    }
    
    private function rellenaVFX1($VF, $Prediction, $TSize) {
        foreach($Prediction as $value) {
                                    
            $repe = false;
            $paraulesFrase = $this->getIdsElem();
            for ($i = 0; $i < sizeof($paraulesFrase); $i++) {
                if ($value->pictoid == $paraulesFrase[$i]->pictoid) {
                    $repe = true;
                    break;
                }
            }            
            
            if (!$repe) {            
                for ($i = 0; sizeof($VF) < $TSize &&  $i < sizeof($VF); $i++) {
                    if ($value->pictoid == $VF[$i]->pictoid) { break; }
                    else if ($value->pictoid != $VF[$i]->pictoid && $i+1 === sizeof($VF)) {
                        array_push($VF,$value);
                    }
                }
            }
        }
        return $VF;
    }

    function getRecommenderX1() {
        $pred = null;
        if ($this->session->userdata('cfgExpansionOnOff')) $pred = $this->getRecommenderX1Expan(1);
        else $pred = $this->getRecommenderX1NonExpan();
        return $pred;
    }

    function getRecommenderX2() {
        $pred = null;
        if ($this->session->userdata('cfgExpansionOnOff')) $pred = $this->getRecommenderX2Expan();
        else $pred = $this->getRecommenderX2NonExpan();
        return $pred;
    }   

    function getRecommenderX3() {   
        $pred = null;
        if ($this->session->userdata('cfgExpansionOnOff')) $pred = $this->getRecommenderX3Expan();
        else $pred = $this->getRecommenderX3NonExpan();
        return $pred;
    }

    function getcountElem(){
        $output = 0;
        $this->db->where('ID_RSTPUser', $this->session->userdata('idusu'));        
        $query = $this->db->get('R_S_TempPictograms');
        
        if ($query->num_rows() > 0) {
            $output = $query->num_rows();
        }
        return $output; 
    }
    
    private function getRecommenderX1Expan($primera) {
        $TSize = $this->session->userdata('cfgPredBarNumPred');        

        // Algorisme V5 - Predictor inicial (cas 00 no hi ha res (fix jo i tu)  
        $VF = $this->getSubj();
        if (!$primera) {
            unset($VF[0]);
            unset($VF[1]);
        }

        // Algorisme V6 - Predictor de context (name) últims 2 dies
        $contextTypeName2Days = $this->getContextType2Days('name');
        $k = 0;
        foreach($contextTypeName2Days as $value) {
            for ($i = 0; $i < sizeof($VF); $i++) {
                if($k == ceil(($TSize-2)/2) || $value->pictoid == $VF[$i]->pictoid) { break; }
                else if ($value->pictoid != $VF[$i]->pictoid && $i+1 === sizeof($VF)) {
                    array_push($VF,$value);
                    $k++;
                }
            }
        }

        // Algorisme V2 - Predictor freqüència II (d'usuari)
        $contextTypeNamesAll = $this->getContextTypeAll('name');
        $contextTypeNamesAllDeep = $this->getContextTypeAllDeep('name', round(($this->SUMcount()[0]->count-sizeof($contextTypeNamesAll))*0.01));      
        if (empty($contextTypeNamesAllDeep)) $freqTotal = $contextTypeNamesAll;
        else {
            $freqTemp = array_merge($contextTypeNamesAllDeep, $contextTypeNamesAll);
            usort($freqTemp, function ($a, $b) {  
                if ($a->repes == $b->repes) return 0;
                else return ($a->repes < $b->repes) ? 1 : -1;
            });
            $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');            
        }
        $k = 0;
        foreach($freqTotal as $value) {
            for ($i = 0; $i < sizeof($VF); $i++) {
                if($k == floor(($TSize-2)/2) || $value->pictoid == $VF[$i]->pictoid) { break; }
                else if ($value->pictoid != $VF[$i]->pictoid && $i+1 === sizeof($VF)) {
                    array_push($VF,$value);
                    $k++;
                }
            }
        }
        // rellena
        if (sizeof($VF) < $TSize) $VF = $this->rellenaVFX1($VF, $contextTypeName2Days, $TSize);
        if (sizeof($VF) < $TSize) $VF = $this->rellenaVFX1($VF, $freqTotal, $TSize);
        // rellena - Algorisme V6 - Predictor de context (name) total                      
        if (sizeof($VF) < $TSize) {
            $freqUsuari = $this->getfreqUsuariX1();
            $freqUsuariDeep = $this->getfreqUsuariX1Deep(round(($this->SUMcount()[0]->count-sizeof($freqUsuari))*0.01));
            if (empty($freqUsuariDeep)) $freqTotal = $freqUsuari;
            else {
                $freqTemp = array_merge($freqUsuariDeep, $freqUsuari);
                usort($freqTemp, function ($a, $b) {  
                    if ($a->count == $b->count) return 0;
                    else return ($a->count < $b->count) ? 1 : -1;
                });
                $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
            }
            $VF = $this->rellenaVFX2X3($VF, $freqTotal, $TSize);
        }
        return $VF;
    }

    private function getRecommenderX1NonExpan() {
        $TSize = $this->session->userdata('cfgPredBarNumPred');

        // Algorisme V5 - Predictor inicial (cas 00 no hi ha res (fix jo i tu)  
        $VF = $this->getSubj();        

        // Algorisme V2 - Predictor freqüència II (d'usuari)                   
        $freqUsuari = $this->getfreqUsuariX1();
        $freqUsuariDeep = $this->getfreqUsuariX1Deep(round(($this->SUMcount()[0]->count-sizeof($freqUsuari))*0.01));
        if (empty($freqUsuariDeep)) $freqTotal = $freqUsuari;
        else {            
            $freqTemp = array_merge($freqUsuariDeep, $freqUsuari);
            usort($freqTemp, function ($a, $b) {  
                if ($a->count == $b->count) return 0;
                else return ($a->count < $b->count) ? 1 : -1;
            });
            $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
        }
        $VF = $this->rellenaVFX1($VF, $freqTotal, $TSize);           

        return $VF;
    }
    
    private function getRecommenderX2Expan() {
        $paraulesFrase = $this->getIdsElem();
        $inputid1 = $paraulesFrase[sizeof($paraulesFrase)-1]->pictoid;        
        $inputType = $this->getTypesElem($inputid1);

        // Algorisme V2 - Predictor freqüència II (d'usuari)
        $VF = array();
        $freqUsuari = $this->getfreqUsuariX2($inputid1);
        $freqUsuariDeep = $this->getfreqUsuariX2Deep($inputid1, round(($this->SUMcount()[0]->count-sizeof($freqUsuari))*0.01));
        if (empty($freqUsuariDeep)) $freqTotal = $freqUsuari;
        else {
            $freqTemp = array_merge($freqUsuariDeep, $freqUsuari);
            usort($freqTemp, function ($a, $b) {
                if ($a->count == $b->count)
                    return 0;
                else
                    return ($a->count < $b->count) ? 1 : -1;
            });
            $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
        }
        $VF = array_merge($VF, $freqTotal);
        $TSize = $this->session->userdata('cfgPredBarNumPred');
        $FSize = $TSize - sizeof($VF);
        
        if ($inputType[0]->pictoType == 'name') {
            // Algorisme V6 - Predictor de context (verb) últims 2 dies
            $contextTypeVerb2Day = $this->getContextType2Days('verb');
            $VF = $this->insertFloorVF($VF, $contextTypeVerb2Day, $FSize);

            // Algorisme V6 - Predictor de context (verb) total
            $contextTypeVerbsAll = $this->getContextTypeAll('verb');
            $contextTypeVerbsAllDeep = $this->getContextTypeAllDeep('verb', round(($this->SUMcount()[0]->count-sizeof($contextTypeVerbsAll))*0.01));
            if (empty($contextTypeVerbsAllDeep)) $freqTotal = $contextTypeVerbsAll;
            else {
                $freqTemp = array_merge($contextTypeVerbsAllDeep, $contextTypeVerbsAll);
                usort($freqTemp, function ($a, $b) {  
                    if ($a->repes == $b->repes) return 0;
                    else return ($a->repes < $b->repes) ? 1 : -1;
                });
                $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
            }
            $VF = $this->rellenaVFX2X3($VF, $freqTotal, $TSize);

            // rellena
            if (sizeof($VF) < $TSize) $VF = $this->rellenaVFX2X3($VF, $contextTypeVerbAll, $TSize);
            if (sizeof($VF) < $TSize) $VF = $this->rellenaVFX2X3($VF, $contextTypeVerb2Day, $TSize);
        }
        else if ($inputType[0]->pictoType == 'verb') {
            $caseList = array("theme",  "manera", "locat", "locto", "locfrom", "time", "tool", "acomp");
            foreach ($caseList as $case) {
                if ($case == "time" || $case == "tool" || $case == "locat" || $case == "acomp") {
                    if (sizeof($VF) < $TSize && $this->get1Opt($inputid1, $case)[0]->$case == 1) $VF = $this->get1OptFitsX2($inputid1, $case, $VF, $TSize, $case);
                }
                else {
                    if (sizeof($VF) < $TSize) {
                        $fits = $this->get1OptFits($inputid1, $case, 1);                        
                        $VF = $this->get1OptFitsX2($inputid1, $case, $VF, $TSize, $fits);
                    }
                }
            }
            foreach ($caseList as $case) {
                if ($case == "time" || $case == "tool" || $case == "locat" || $case == "acomp") {
                    if (sizeof($VF) < $TSize && $this->get1Opt($inputid1, $case)[0]->$case == 'opt') {
                        $VF = $this->get1OptFitsX2($inputid1, $case, $VF, $TSize, $case);
                    }
                }
                else {
                    if (sizeof($VF) < $TSize) {
                        $fits = $this->get1OptFits($inputid1, $case, 'opt');                        
                        $VF = $this->get1OptFitsX2($inputid1, $case, $VF, $TSize, $fits);
                    }
                }
            }
        }
        else if ($inputType[0]->pictoType == 'modifier' && $this->boolDetPos($inputid1)) {
            // Algorisme V6 - Predictor de context (name) últims 2 dies
            $contextTypeName2Days = $this->getContextType2Days('name');
            $VF = $this->insertCeilVF($VF, $contextTypeName2Days, $FSize);

            // Algorisme V6 - Predictor de context (name) total
            $contextTypeNamesAll = $this->getContextTypeAll('name');
            $contextTypeNamesAllDeep = $this->getContextTypeAllDeep('name', round(($this->SUMcount()[0]->count-sizeof($contextTypeNamesAll))*0.01));
            if (empty($contextTypeNamesAllDeep)) $freqTotal = $contextTypeNamesAll;
            else {
                $freqTemp = array_merge($contextTypeNamesAllDeep, $contextTypeNamesAll);
                usort($freqTemp, function ($a, $b) {  
                    if ($a->repes == $b->repes) return 0;
                    else return ($a->repes < $b->repes) ? 1 : -1;
                });
                $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
            }
            $VF = $this->insertFloorVF($VF, $freqTotal, $FSize);

            // rellena
            if (sizeof($VF) < $TSize) $VF = $this->rellenaVFX2X3($VF, $contextTypeName2Days, $TSize);
            if (sizeof($VF) < $TSize) $VF = $this->rellenaVFX2X3($VF, $freqTotal, $TSize);
        }
        else {
            // Algorisme V6 - Predictor de context (name) últims 2 dies                                
            $contextTypeName2Days = $this->getContextType2Days('name');
            $VF = $this->insertCeilVF($VF, $contextTypeName2Days, $FSize);                   

            // Algorisme V6 - Predictor de context (verb) total                      
            $contextTypeVerbsAll = $this->getContextTypeAll('verb');
            $contextTypeVerbsAllDeep = $this->getContextTypeAllDeep('verb', round(($this->SUMcount()[0]->count-sizeof($contextTypeVerbsAll))*0.01));
            if (empty($contextTypeVerbsAllDeep)) $freqTotal = $contextTypeVerbsAll;
            else {
                $freqTemp = array_merge($contextTypeVerbsAllDeep, $contextTypeVerbsAll);
                usort($freqTemp, function ($a, $b) {  
                    if ($a->repes == $b->repes) return 0;
                    else return ($a->repes < $b->repes) ? 1 : -1;
                });
                $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
            }
            $VF = $this->insertFloorVF($VF, $freqTotal, $FSize);                  

            // rellena
            if (sizeof($VF) < $TSize) $VF = $this->rellenaVFX2X3($VF, $contextTypeName2Days, $TSize);
            if (sizeof($VF) < $TSize) $VF = $this->rellenaVFX2X3($VF, $contextTypeVerbsAll, $TSize);
        }
        
        // rellena
        if (sizeof($VF) < $TSize) {
            $freqX1 = $this->getRecommenderX1();
            unset($freqX1[0]);
            unset($freqX1[1]);
            $VF = $this->rellenaVFX2X3($VF, $freqX1, $TSize);
        }
        
        // Algorisme V6 - Predictor de context (name) total
        if (sizeof($VF) < $TSize) {
            $contextTypeNamesAll = $this->getContextTypeAll('name');
            $contextTypeNamesAllDeep = $this->getContextTypeAllDeep('name', round(($this->SUMcount()[0]->count-sizeof($contextTypeNamesAll))*0.01));
            if (empty($contextTypeNamesAllDeep)) $freqTotal = $contextTypeNamesAll;
            else {
                $freqTemp = array_merge($contextTypeNamesAllDeep, $contextTypeNamesAll);
                usort($freqTemp, function ($a, $b) {  
                    if ($a->repes == $b->repes) return 0;
                    else return ($a->repes < $b->repes) ? 1 : -1;
                });
                $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
            }
            $VF = $this->rellenaVFX1($VF, $freqTotal, $TSize); 
        }
        return $VF;
    }
    
    private function getRecommenderX2NonExpan() {
        
        $paraulesFrase = $this->getIdsElem();
        $inputid1 = $paraulesFrase[sizeof($paraulesFrase)-1]->pictoid;        

        // Algorisme V2 - Predictor freqüència II (d'usuari)
        $VF = array();
        $freqUsuari = $this->getfreqUsuariX2NonExpan($inputid1);
        $freqUsuariDeep = $this->getfreqUsuariX2NonExpanDeep($inputid1, round(($this->SUMcount()[0]->count-sizeof($freqUsuari))*0.01));
        if (empty($freqUsuariDeep)) $freqTotal = $freqUsuari;
        else {
            $freqTemp = array_merge($freqUsuariDeep, $freqUsuari);
            usort($freqTemp, function ($a, $b) {  
                if ($a->count == $b->count) return 0;
                else return ($a->count < $b->count) ? 1 : -1;
            });
            $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
        }
        $VF = array_merge($VF,$freqTotal);
        $TSize = $this->session->userdata('cfgPredBarNumPred');
        
        // rellena
        if (sizeof($VF) < $TSize) {
            $freqX1 = $this->getfreqUsuariX1();
            unset($freqX1[0]);
            unset($freqX1[1]);
            $freqUsuariDeep = $this->getfreqUsuariX1Deep(round(($this->SUMcount()[0]->count-sizeof($freqX1))*0.01));
            if (empty($freqUsuariDeep)) $freqTotal = $freqX1;
            else {
                $freqTemp = array_merge($freqUsuariDeep, $freqX1);
                usort($freqTemp, function ($a, $b) {
                    if ($a->count == $b->count) return 0;
                    else return ($a->count < $b->count) ? 1 : -1;
                });
                $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
            }
            $VF = $this->rellenaVFX2X3($VF, $freqTotal, $TSize);
        }

        return $VF;                
    }
    
    private function getRecommenderX3Expan() {
        $paraulesFrase = $this->getIdsElem();
        $inputid1 = $paraulesFrase[sizeof($paraulesFrase)-2]->pictoid;
        $inputid2 = $paraulesFrase[sizeof($paraulesFrase)-1]->pictoid;
        
        $inputType1 = $this->getTypesElem($inputid1);
        $inputType2 = $this->getTypesElem($inputid2);
        
        $verb = false;
        for ($i = sizeof($paraulesFrase); $i > 0; $i--) {
            if ($this->getTypesElem($paraulesFrase[$i-1]->pictoid)[0]->pictoType == 'verb')
            {
                $verb = true;
                break;
            }
        }

        // Algorisme V2 - Predictor freqüència II (d'usuari)
        $VF = array();
        $freqUsuari = $this->getfreqUsuariX3($inputid1, $inputid2);
        $freqUsuariDeep = $this->getfreqUsuariX3Deep($inputid1, $inputid2, round(($this->SUMcount()[0]->count-sizeof($freqUsuari))*0.01));
        if (empty($freqUsuariDeep)) $freqTotal = $freqUsuari;
        else {
            $freqTemp = array_merge($freqUsuariDeep, $freqUsuari);
            usort($freqTemp, function ($a, $b) {
                if ($a->count == $b->count)
                    return 0;
                else
                    return ($a->count < $b->count) ? 1 : -1;
            });
            $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
        }
        $VF = array_merge($VF,$freqTotal);              
        // rellena 1ra mitad
        if (sizeof($VF) < 3) {
            $freqX2 = $this->getfreqUsuariX2($inputid2);
            if ($verb) {
                $freqX2 = $this->getfreqUsuariX2NV($inputid2);
                $freqUsuariDeep = $this->getfreqUsuariX2DeepNV($inputid2, round(($this->SUMcount()[0]->count-sizeof($freqX2))*0.01));
            }
            else {
                $freqX2 = $this->getfreqUsuariX2($inputid2);
                $freqUsuariDeep = $this->getfreqUsuariX2Deep($inputid2, round(($this->SUMcount()[0]->count-sizeof($freqX2))*0.01));
            }
            if (empty($freqUsuariDeep)) $freqTotal = $freqX2;
            else {
                $freqTemp = array_merge($freqUsuariDeep, $freqX2);
                usort($freqTemp, function ($a, $b) {
                    if ($a->count == $b->count)
                        return 0;
                    else
                        return ($a->count < $b->count) ? 1 : -1;
                });
                $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
            }
            $VF = $this->rellenaVFX2X3($VF, $freqTotal, 3);
        }        
        // rellena 1ra mitad
        if (sizeof($VF) < 3) {
            if ($verb) {
                $freqX1 = $this->getfreqUsuariX1NV();
                $freqUsuariDeep = $this->getfreqUsuariX1DeepNV(round(($this->SUMcount()[0]->count-sizeof($freqX1))*0.01));
            }
            else {
                $freqX1 = $this->getfreqUsuariX1();
                $freqUsuariDeep = $this->getfreqUsuariX1Deep(round(($this->SUMcount()[0]->count-sizeof($freqX1))*0.01));
            }
            if (empty($freqUsuariDeep)) $freqTotal = $freqX1;
            else {
                $freqTemp = array_merge($freqUsuariDeep, $freqX1);
                usort($freqTemp, function ($a, $b) {  
                    if ($a->count == $b->count) return 0;
                    else return ($a->count < $b->count) ? 1 : -1;
                });
                $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
            }
            $VF = $this->rellenaVFX2X3($VF, $freqTotal, 3);
        }
        
        $TSize = $this->session->userdata('cfgPredBarNumPred');
        $FSize = $TSize - sizeof($VF);       
              
        if ($inputType2[0]->pictoType == 'modifier' && $this->boolDetPos($inputid2)) {
            
            // Algorisme V6 - Predictor de context (name) últims 2 dies                                
            $contextTypeName2Days = $this->getContextType2Days('name');
            $VF = $this->insertCeilVF($VF, $contextTypeName2Days, $FSize);                   
            
            // Algorisme V6 - Predictor de context (name) total                      
            $contextTypeNamesAll = $this->getContextTypeAll('name');
            $contextTypeNamesAllDeep = $this->getContextTypeAllDeep('name', round(($this->SUMcount()[0]->count-sizeof($contextTypeNamesAll))*0.01));
            if (empty($contextTypeNamesAllDeep)) $freqTotal = $contextTypeNamesAll;
            else {
                $freqTemp = array_merge($contextTypeNamesAllDeep, $contextTypeNamesAll);
                usort($freqTemp, function ($a, $b) {  
                    if ($a->repes == $b->repes) return 0;
                    else return ($a->repes < $b->repes) ? 1 : -1;
                });
                $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
            }
            $VF = $this->insertFloorVF($VF, $freqTemp, $FSize);   
            
            // rellena
            if (sizeof($VF) < $TSize) $VF = $this->rellenaVFX2X3($VF, $contextTypeName2Days, $TSize);
            if (sizeof($VF) < $TSize) $VF = $this->rellenaVFX2X3($VF, $freqTotal, $TSize);            
        }
        else if (!$verb && ($inputType1[0]->pictoType == 'name' || $inputType2[0]->pictoType == 'name')) {
            // Algorisme V6 - Predictor de context (verb) últims 2 dies                       
            $contextTypeVerbs2Days = $this->getContextType2Days('verb');
            $VF = $this->insertCeilVF($VF, $contextTypeVerbs2Days, $FSize);
			
			// Algorisme V6 - Predictor de context (verb) total  
            $contextTypeVerbsAll = $this->getContextTypeAll('verb');
            $contextTypeVerbsAllDeep = $this->getContextTypeAllDeep('verb', round(($this->SUMcount()[0]->count-sizeof($contextTypeVerbsAll))*0.01));
            if (empty($contextTypeVerbsAllDeep)) $freqTotal = $contextTypeVerbsAll;
            else {
                $freqTemp = array_merge($contextTypeNamesAllDeep, $contextTypeVerbsAll);
                usort($freqTemp, function ($a, $b) {  
                    if ($a->repes == $b->repes) return 0;
                    else return ($a->repes < $b->repes) ? 1 : -1;
                });
                $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
            }
            $VF = $this->insertFloorVF($VF, $freqTotal, $FSize);
            
            // rellena
            if (sizeof($VF) < $TSize) $VF = $this->rellenaVFX2X3($VF, $contextTypeVerbs2Days, $TSize);
            if (sizeof($VF) < $TSize) $VF = $this->rellenaVFX2X3($VF, $contextTypeVerbsAll, $TSize);

            if (sizeof($VF) < $TSize) {
                $freqX2 = $this->getRecommenderX2();    
                $VF = $this->rellenaVFX2X3($VF, $freqX2, $TSize);
            }
        }
        else if (!$verb && $inputType2[0]->pictoType != 'name') {
            // Algorisme V6 - Predictor de context (name) últims 2 dies                                
            $contextTypeName2Days = $this->getContextType2Days('name');
            $VF = $this->insertCeilVF($VF, $contextTypeName2Days, $FSize);                   

            // Algorisme V6 - Predictor de context (verb) total                      
            $contextTypeVerbsAll = $this->getContextTypeAll('verb');
            $contextTypeVerbsAllDeep = $this->getContextTypeAllDeep('verb', round(($this->SUMcount()[0]->count-sizeof($contextTypeVerbsAll))*0.01));
            if (empty($contextTypeVerbsAllDeep)) $freqTotal = $contextTypeVerbsAll;
            else {
                $freqTemp = array_merge($contextTypeVerbsAllDeep, $contextTypeVerbsAll);
                usort($freqTemp, function ($a, $b) {
                    if ($a->repes == $b->repes) return 0;
                    else return ($a->repes < $b->repes) ? 1 : -1;
                });
                $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
            }
            $VF = $this->insertFloorVF($VF, $freqTotal, $FSize);                  

            // rellena
            if (sizeof($VF) < $TSize) $VF = $this->rellenaVFX2X3($VF, $contextTypeName2Days, $TSize);
            if (sizeof($VF) < $TSize) $VF = $this->rellenaVFX2X3($VF, $contextTypeVerbsAll, $TSize);
        }
        else { // ni name ni verb
            if ($verb) {
                $caseList = array("theme",  "manera", "locat", "locto", "locfrom", "tool", "acomp");
                for ($j = sizeof($paraulesFrase); $j > 0; $j--) {
                    if ($this->getTypesElem($paraulesFrase[$j-1]->pictoid)[0]->pictoType == 'verb') {
                        $inputid1 = $paraulesFrase[$j-1]->pictoid;
                        foreach ($caseList as $case) {
                            if ($case == "time" || $case == "tool" || $case == "locat" || $case == "acomp") {
                                if (sizeof($VF) < $TSize && $this->get1Opt($inputid1, $case)[0]->$case == 1) {
                                    $repe = false;
                                    for ($k = sizeof($paraulesFrase); $k > 0; $k--) {
                                        $inputid2 = $paraulesFrase[$k-1]->pictoid;
                                        if (($case == 'time' || $case == 'tool') && $this->getTypesElem($inputid2)[0]->pictoType == 'name' && $this->getNameClass($inputid2)[0]->class == $case) {
                                            $repe = true;
                                            break;
                                        }
                                        else if ($case == 'locat' && $this->getTypesElem($inputid2)[0]->pictoType == 'name' && $this->getNameClass($inputid2)[0]->class == 'lloc') {
                                            $repe = true;
                                            break;
                                        }
                                        else if ($case == 'acomp' && $this->getTypesElem($inputid2)[0]->pictoType == 'name' && $this->getNameClass($inputid2)[0]->class == 'human' && $inputid2 != 444) {
                                            $repe = true;
                                            break;
                                        }
                                    }
                                    if ($repe) continue;
                                    $VF = $this->get1OptFitsX3($inputid1, $inputid2, $case, $VF, $TSize, $case, $inputid1);
                                }
                            }
                            else {
                                if (sizeof($VF) < $TSize) {
                                    $fits = $this->get1OptFits($inputid1, $case, 1);
                                    $repe = false;
                                    for ($w = sizeof($paraulesFrase); $w > 0; $w--) {
                                        $inputid2 = $paraulesFrase[$w-1]->pictoid;
                                        if ($case == 'theme' && $this->getTypesElem($inputid2)[0]->pictoType == 'name' && $this->getNameClass($inputid2)[0]->class == $fits[0]) {
                                            $repe = true;
                                            break;
                                        }
                                        if ($case == 'manera' && $this->getTypesElem($inputid2)[0]->pictoType == 'adv' && $this->getAdvType($inputid2)[0]->class == $fits) {
                                            $repe = true;
                                            break;
                                        }
                                        else if (($case == 'locto' || $case == 'locfrom') && $this->getTypesElem($inputid2)[0]->pictoType == 'name' && $this->getNameClass($inputid2)[0]->class == $fits) {
                                            $repe = true;
                                            break;
                                        }
                                    }
                                    if ($repe) continue;
                                    $VF = $this->get1OptFitsX3($inputid1, $inputid2, $case, $VF, $TSize, $fits, $inputid1);
                                }
                            }
                        }
                        foreach ($caseList as $case) {
                            if ($case == "time" || $case == "tool" || $case == "locat" || $case == "acomp") {
                                if (sizeof($VF) < $TSize && $this->get1Opt($inputid1, $case)[0]->$case == 'opt') {
                                    $repe = false;
                                    for ($k = sizeof($paraulesFrase); $k > 0; $k--) {
                                        $inputid2 = $paraulesFrase[$k-1]->pictoid;
                                        if (($case == 'time' || $case == 'tool') && $this->getTypesElem($inputid2)[0]->pictoType == 'name' && $this->getNameClass($inputid2)[0]->class == $case) {
                                            $repe = true;
                                            break;
                                        }
                                        else if ($case == 'locat' && $this->getTypesElem($inputid2)[0]->pictoType == 'name' && $this->getNameClass($inputid2)[0]->class == 'lloc') {
                                            $repe = true;
                                            break;
                                        }
                                        else if ($case == 'acomp' && $this->getTypesElem($inputid2)[0]->pictoType == 'name' && $this->getNameClass($inputid2)[0]->class == 'human' && $inputid2 != 444) {
                                            $repe = true;
                                            break;
                                        }
                                    }
                                    if ($repe) continue;
                                    $VF = $this->get1OptFitsX3($inputid1, $inputid2, $case, $VF, $TSize, $case, $inputid1);
                                }
                            }
                            else {
                                if (sizeof($VF) < $TSize) {
                                    $fits = $this->get1OptFits($inputid1, $case, 'opt');
                                    $repe = false;
                                    for ($w = sizeof($paraulesFrase); $w > 0; $w--) {
                                        $inputid2 = $paraulesFrase[$w-1]->pictoid;
                                        if ($case == 'theme' && $this->getTypesElem($inputid2)[0]->pictoType == 'name' && $this->getNameClass($inputid2)[0]->class == $fits[0]) {
                                            $repe = true;
                                            break;
                                        }
                                        if ($case == 'manera' && $this->getTypesElem($inputid2)[0]->pictoType == 'adv' && $this->getAdvType($inputid2)[0]->class == $fits) {
                                            $repe = true;
                                            break;
                                        }
                                        else if (($case == 'locto' || $case == 'locfrom') && $this->getTypesElem($inputid2)[0]->pictoType == 'name' && $this->getNameClass($inputid2)[0]->class == $fits) {
                                            $repe = true;
                                            break;
                                        }
                                    }
                                    if ($repe) continue;
                                    $VF = $this->get1OptFitsX3($inputid1, $inputid2, $case, $VF, $TSize, $fits, $inputid1);
                                }
                            }
                        }
                    }
                }
            }
            else {
                // Algorisme V6 - Predictor de context (name) últims 2 dies          
                $contextTypeName2Days = $this->getContextType2Days('name');
                $VF = $this->insertCeilVF($VF, $contextTypeName2Days, $FSize);

                // Algorisme V6 - Predictor de context (verb) total  
                $contextTypeVerbsAll = $this->getContextTypeAll('verb');
                $contextTypeVerbsAllDeep = $this->getContextTypeAllDeep('verb', round(($this->SUMcount()[0]->count-sizeof($contextTypeVerbsAll))*0.01));
                if (empty($contextTypeVerbsAllDeep)) $freqTotal = $contextTypeVerbsAll;
                else {
                    $freqTemp = array_merge($contextTypeVerbsAllDeep, $contextTypeVerbsAll);
                    usort($freqTemp, function ($a, $b) {  
                        if ($a->repes == $b->repes) return 0;
                        else return ($a->repes < $b->repes) ? 1 : -1;
                    });
                    $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
                }
                $VF = $this->insertFloorVF($VF, $freqTotal, $FSize);

                // rellena
                if (sizeof($VF) < $TSize) $VF = $this->rellenaVFX2X3($VF, $contextTypeName2Days, $TSize);
                if (sizeof($VF) < $TSize) $VF = $this->rellenaVFX2X3($VF, $contextTypeVerbsAll, $TSize);

                if (sizeof($VF) < $TSize) {
                    $freqX2 = $this->getRecommenderX2();    
                    $VF = $this->rellenaVFX2X3($VF, $freqX2, $TSize);
                }
            }
        }
        
        // rellena
        if (sizeof($VF) < $TSize) {
            $freqX1 = $this->getRecommenderX1Expan(0);
            $VF = $this->rellenaVFX2X3($VF, $freqX1, $TSize);
        }
        
        return $VF;
    }
    
    private function getRecommenderX3NonExpan() {
        $paraulesFrase = $this->getIdsElem();
        $inputid1 = $paraulesFrase[sizeof($paraulesFrase)-2]->pictoid;
        $inputid2 = $paraulesFrase[sizeof($paraulesFrase)-1]->pictoid;
        
        $inputType1 = $this->getTypesElem($inputid1);
        $inputType2 = $this->getTypesElem($inputid2);
        
        // Algorisme V2 - Predictor freqüència II (d'usuari)
        $VF = array();
        $freqUsuari = $this->getfreqUsuariX3NonExpan($inputid1, $inputid2);
        $freqUsuariDeep = $this->getfreqUsuariX3NonExpanDeep($inputid1, $inputid2, round(($this->SUMcount()[0]->count-sizeof($freqUsuari))*0.01));
        if (empty($freqUsuariDeep)) $freqTotal = $freqUsuari;
        else {
            $freqTemp = array_merge($freqUsuariDeep, $freqUsuari);
            usort($freqTemp, function ($a, $b) {  
                if ($a->count == $b->count) return 0;
                else return ($a->count < $b->count) ? 1 : -1;
            });
            $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
        }
        $VF = array_merge($VF,$freqTotal);        
        $TSize = $this->session->userdata('cfgPredBarNumPred');
                        
        // rellena
        if (sizeof($VF) < $TSize) {
            $freqX2 = $this->getfreqUsuariX2NonExpan($inputid2);
            $freqUsuariDeep = $this->getfreqUsuariX2NonExpanDeep(round(($this->SUMcount()[0]->count-sizeof($freqX2))*0.01));
            if (empty($freqUsuariDeep)) $freqTotal = $freqX2;
            else {
                $freqTemp = array_merge($freqUsuariDeep, $freqX2);
                usort($freqTemp, function ($a, $b) {  
                    if ($a->count == $b->count) return 0;
                    else return ($a->count < $b->count) ? 1 : -1;
                });
                $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
            }
            $VF = $this->rellenaVFX2X3($VF, $freqTotal, $TSize);
        }

        // rellena
        if (sizeof($VF) < $TSize) {
            $freqX1 = $this->getfreqUsuariX1();
            unset($freqX1[0]);
            unset($freqX1[1]);
            $freqUsuariDeep = $this->getfreqUsuariX1Deep(round(($this->SUMcount()[0]->count-sizeof($freqX1))*0.01));
            if (empty($freqUsuariDeep)) $freqTotal = $freqX1;
            else {               
                $freqTemp = array_merge($freqUsuariDeep, $freqX1);
                usort($freqTemp, function ($a, $b) {  
                    if ($a->count == $b->count) return 0;
                    else return ($a->count < $b->count) ? 1 : -1;
                });
                $freqTotal = $this->unique_multidim_array($freqTemp, 'pictoid');
            }
            $VF = $this->rellenaVFX2X3($VF, $freqTotal, $TSize);
        }
        
        return $VF;
    }
    
    private function delfreqUsuariX1() {
        $this->db->where('ID_PSUPUser', $this->session->userdata('idusu'));                             
        $this->db->delete('P_StatsUserPicto');
    }
    
    public function delfreqUsuariX2() {
        $this->db->where('ID_PSUP2User', $this->session->userdata('idusu'));                             
        $this->db->delete('P_StatsUserPictox2');
    }
    
    public function delfreqUsuariX3() {
        $this->db->where('ID_PSUP3User', $this->session->userdata('idusu'));                             
        $this->db->delete('P_StatsUserPictox3');
    }
}

?>