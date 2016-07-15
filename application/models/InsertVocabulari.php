<?php

class InsertVocabulari extends CI_Model {
 
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }        

    private function insertAdjClass($new, $adjid, $class) {
        if ($new) $this->insertIntoAdjClass($adjid, $class);
        else {
            $this->db->where('adjid', $adjid);
            $this->db->delete('AdjClass'.$this->session->userdata('ulangabbr'));
            $this->insertIntoAdjClass($adjid, $class);
        }
    }
    
    private function insertIntoAdjClass($adjid, $class) {
        for ($i = 0; $i < sizeof($class); $i++) {
            $data = array(
                'adjid' => $adjid,
                'class' => $class[$i]->classType
            );
            $this->db->insert('AdjClass'.$this->session->userdata('ulangabbr'), $data);
        }
    }        
    
    private function insertAdjective($new, $adjid, $fem, $masc, $mascpl, $fempl, $defaultverb, $subjdef) {
        $data = array(
            'adjid' => $adjid,
            'fem' => $fem,
            'masc' => $masc,
            'mascpl' => $mascpl,
            'fempl' => $fempl,
            'defaultverb' => $defaultverb,
            'subjdef' => $subjdef
        );
        if ($new) $this->db->insert('Adjective'.$this->session->userdata('ulangabbr'), $data);
        else {
            $this->db->where('adjid', $adjid);
            $this->db->update('Adjective'.$this->session->userdata('ulangabbr'), $data); 
        }
    }
    
    private function insertAdverb($new, $advid, $advtext) {
        $data = array(
            'advid' => $advid,
            'class' => $advtext
        );
        if ($new) $this->db->insert('Adverb'.$this->session->userdata('ulangabbr'), $data);
        else {
            $this->db->where('advid', $advid);
            $this->db->update('Adverb'.$this->session->userdata('ulangabbr'), $data);
        }
    }
    
    private function insertAdvType($new, $advid, $type) {
        $data = array(
            'advid' => $advid,
            'type' => $type
        );
        if ($new) $this->db->insert('AdvType'.$this->session->userdata('ulangabbr'), $data);
        else {
            $this->db->where('advid', $advid);
            $this->db->update('AdvType'.$this->session->userdata('ulangabbr'), $data);
        }
    }
    
    private function insertModifier($new, $modid, $masc, $fem, $mascpl, $fempl, $negatiu, $type, $scope) {
        $data = array(
            'modid' => $modid,
            'masc' => $masc,
            'fem' => $fem,
            'mascpl' => $mascpl,
            'fempl' => $fempl,
            'negatiu' => $negatiu,
            'type' => $type,
            'scope' => $scope
        );
        if ($new) $this->db->insert('Modifier'.$this->session->userdata('ulangabbr'), $data);
        else {
            $this->db->where('modid', $modid);
            $this->db->update('Modifier'.$this->session->userdata('ulangabbr'), $data);
        }
    }
    
    private function insertName($new, $nameid, $nomtext, $mf, $singpl, $contabincontab, $determinat, $ispropernoun, $defaultverb, $plural, $femeni, $fempl) {
        $data = array(
            'nameid' => $nameid,
            'nomtext' => $nomtext,
            'mf' => $mf,
            'singpl' => $singpl,
            'contabincontab' => $contabincontab,
            'determinat' => $determinat,
            'ispropernoun' => $ispropernoun,
            'defaultverb' => $defaultverb,
            'plural' => $plural,
            'femeni' => $femeni,
            'fempl' => $fempl
        );
        if ($new) $this->db->insert('Name'.$this->session->userdata('ulangabbr'), $data);
        else {
            $this->db->where('nameid', $nameid);
            $this->db->update('Name'.$this->session->userdata('ulangabbr'), $data);
        }
    }
    
    private function insertNameClass($new, $nameid, $class) {
        if ($new) $this->insertIntoNameClass($nameid, $class);
        else {
            $this->db->where('nameid', $nameid);
            $this->db->delete('NameClass'.$this->session->userdata('ulangabbr'));
            $this->insertIntoNameClass($nameid, $class);
        }
    }
    
    private function insertIntoNameClass($nameid, $class) {
        for ($i = 0; $i < sizeof($class); $i++) {
            $data = array(
                'nameid' => $nameid,
                'class' => $class[$i]->classType
            );
            $this->db->insert('NameClass'.$this->session->userdata('ulangabbr'), $data);
        }
    }
    
    private function insertPattern($new, $patternid, $verbid) { // falta param pattern
        $data = array(
            'patternid' => $patternid,
            'verbid' => $verbid
        );
        if ($new) $this->db->insert('Pattern'.$this->session->userdata('ulangabbr'), $data);
        else {
            $this->db->where('patternid', $patternid);
            $this->db->update('Pattern'.$this->session->userdata('ulangabbr'), $data);
        }
    }
    
    private function insertVerb($new, $verbid, $verbtext, $actiu) {
        $data = array(
            'verbid' => $verbid,
            'verbtext' => $verbtext,
            'actiu' => $actiu
        );
        if ($new) $this->db->insert('Verb'.$this->session->userdata('ulangabbr'), $data);
        else {
            $this->db->where('verbid', $verbid);
            $this->db->update('Verb'.$this->session->userdata('ulangabbr'), $data);
        }
    }
    
    private function insertVerbConjugation($new, $verbid, $tense, $pers, $singpl, $verbconj) {
        $data = array(
            'verbid' => $verbid,
            'tense' => $tense,
            'pers' => $pers,
            'singpl' => $singpl,
            'verbconj' => $verbconj
        );
        if ($new) $this->db->insert('VerbConjugation'.$this->session->userdata('ulangabbr'), $data);
        else {
            $this->db->where('verbid', $verbid);
            $this->db->update('VerbConjugation'.$this->session->userdata('ulangabbr'), $data);
        }
    }
    
    private function insertVerbPattern($new, $verbid, $patternid) {
        $data = array(
            'verbid' => $verbid,
            'patternid' => $patternid
        );
        if ($new) $this->db->insert('VerbPattern'.$this->session->userdata('ulangabbr'), $data);
        else {
            $this->db->where('verbid', $verbid);
            $this->db->update('VerbPattern'.$this->session->userdata('ulangabbr'), $data);
        }
    }

    private function insertP_StatsUserPicto($ID_PSUPUser, $pictoid) {
        $data = array(
            'ID_PSUPUser' => $ID_PSUPUser,
            'pictoid' => $pictoid,
            'countx1' => 1,
            'lastdate' => mdate("%Y/%m/%d", time())
        );
        $this->db->insert('P_StatsUserPicto', $data);
    }
    
    private function insertPictograms($new, $pictoid, $ID_PUSer, $pictoType, $supportsExpansion, $imgPicto ) {
        if($new) {
            $data = array(
                'ID_PUser' => $ID_PUSer,
                'pictoType' => $pictoType,
                'supportsExpansion' => $supportsExpansion,
                'imgPicto' => $imgPicto
            );
            $this->db->insert('Pictograms', $data);
            $pictoid = $this->db->insert_id();
        }
        else {
            $data = array(
                'ID_PUser' => $ID_PUSer,
                'pictoType' => $pictoType,
                'supportsExpansion' => $supportsExpansion,
                'imgPicto' => $imgPicto
            );
            $this->db->where('pictoid', $pictoid);
            $this->db->update('Pictograms', $data);
        }
        return $pictoid;
    }
    
    private function insertPictogramsLanguage($new, $pictoid, $languageid, $pictotext, $pictofreq ) {
        if ($new){
            $data = array(
            'pictoid' => $pictoid,
            'languageid' => $languageid,
            'insertdate' => mdate("%Y/%m/%d", time()),
            'pictotext' => $pictotext,
            'pictofreq' => $pictofreq
            );
            $this->db->insert('PictogramsLanguage', $data);
        }
        else {
            $data = array(
            'insertdate' => mdate("%Y/%m/%d", time()),
            'pictotext' => $pictotext,
            'pictofreq' => $pictofreq
            );
            $this->db->where('pictoid', $pictoid);
            $this->db->update('PictogramsLanguage', $data);
        }
    }

    public function insertPicto($objAdd) {
        $ID_PUSer = $this->session->userdata('idusu');
        $ID_PSUPUser = $this->session->userdata('idusu');
        $languageid = ($this->session->userdata('ulangabbr') == 'CA' ? 1:  2);
        $pictoid = $this->insertPictograms($objAdd->new, $objAdd->pictoid, $ID_PUSer, $objAdd->type, $objAdd->supExp, $objAdd->imgPicto);
        var_dump($pictoid);        
         //pictofreq a modificar
        
        if ($objAdd->type == 'name') {
            $this->insertPictogramsLanguage($objAdd->new, $pictoid, $languageid, $objAdd->nomtext, $pictofreq = 10.000);
            $this->insertName($objAdd->new, $pictoid, $objAdd->nomtext, $objAdd->mf, $objAdd->singpl, $objAdd->contabincontab, $objAdd->determinat, $objAdd->ispropernoun, $objAdd->defaultverb, $objAdd->plural, $objAdd->femeni, $objAdd->fempl);
            $this->insertNameClass($objAdd->new, $pictoid, $objAdd->class);            
        }
        else if ($objAdd->type == 'adj') {
            $this->insertPictogramsLanguage($objAdd->new, $pictoid, $languageid, $objAdd->masc, $pictofreq = 10.000);
            $this->insertAdjective($objAdd->new, $pictoid, $objAdd->fem, $objAdd->masc, $objAdd->mascpl, $objAdd->fempl, $objAdd->defaultverb, $objAdd->subjdef);
            $this->insertAdjClass($objAdd->new, $pictoid, $objAdd->class);
        }
    }

    public function deletePictogram($pictoid) {
        $this->db->where('ID_PUser', $this->session->userdata('idusu'));                             
        $this->db->where('pictoid', $pictoid);                             
        $this->db->delete('Pictograms');
    }
}
?>