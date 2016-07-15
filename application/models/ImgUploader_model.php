<?php

class ImgUploader_model extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    function insertImg($idusu, $orgiginalName, $md5Name) {
        $data = array(
            'ID_ISU' => $idusu,
            'imgName' => $orgiginalName,
            'imgPath' => "img/users/".$md5Name
        );

        $this->db->insert('Images', $data);
    }

    function getImages($idusu, $name) {
        $output = array();

        $this->db->limit(6);
        $this->db->where('ID_ISU', $idusu);
        $this->db->like('imgName', $name, 'after');
        $this->db->order_by('imgName', 'asc');
        $query = $this->db->get('Images');

        if ($query->num_rows() > 0) {
            $output = $query->result_array();
        }

        return $output;
    }

    function getImagesArasaac($idusu, $name, $languageInt) {
        //Interface language
        

        $output = array();

        $this->db->limit(6); // limit up to 6

        $this->db->where_in('Pictograms.ID_PUser', array('1', $idusu));
        $this->db->where('PictogramsLanguage.languageid', $languageInt);
        //$this->db->or_where_in('Pictograms.ID_PUser', array('1',$user)); //Get all default and own user pictos
        $this->db->select('PictogramsLanguage.pictotext as imgName, imgPicto as imgPath, Pictograms.ID_PUser'); // rename the field like we want
        $this->db->like('PictogramsLanguage.pictotext', $name, 'after'); // select only the names that start with $startswith
        $this->db->order_by('PictogramsLanguage.pictotext', 'asc'); // order the names
         $this->db->join('PictogramsLanguage', 'PictogramsLanguage.pictoid = Pictograms.pictoid', 'left');
        $query = $this->db->get('Pictograms'); // execute de query

        if ($query->num_rows() > 0) {
            $output = $query->result_array();
        }
        return $output;
    }

}
