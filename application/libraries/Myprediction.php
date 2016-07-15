<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Myprediction {

    public function __construct() {}
    
    function getPrediction() {
        $numpar = $this->getcountElem();
        if ($numpar == 0) {
            return $this->getRecommenderX1();
        }
        else if ($numpar == 1) {
            return $this->getRecommenderX2();
        }
        else {
            return $this->getRecommenderX3();     
        }
    }    
    
    function getRecommenderX1() {  
        $CI = &get_instance();
        $CI->load->model('Recommender');
        $output = $CI->Recommender->getRecommenderX1();
        return $output;                  
    }
    
    function getRecommenderX2() {
        $CI = &get_instance();
        $CI->load->model('Recommender');
        $output = $CI->Recommender->getRecommenderX2();
        return $output;                 
    }
    
    function getRecommenderX3() {
        $CI = &get_instance();
        $CI->load->model('Recommender');
        $output = $CI->Recommender->getRecommenderX3();
        return $output;      
    }     
    
    function getcountElem(){
        $CI = &get_instance();
        $CI->load->model('Recommender');
        $output = $CI->Recommender->getcountElem();
        return $output;  
    }       
}

/* End of file Myprediction.php */