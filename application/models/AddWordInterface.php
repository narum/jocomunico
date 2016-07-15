<?php

class AddWordInterface extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    function getTypePicto($idPicto) {
        $output = array();
        $this->db->where('Pictograms.pictoid', $idPicto);
        $this->db->select('pictoType as type'); // rename the field like we want
        $query = $this->db->get('Pictograms'); // execute de query

        if ($query->num_rows() > 0) {
            $output = $query->result_array();
        }
        return $output;
    }

    function copyVocabulary($idsuOrigen, $idsuDest) {
        $voc = array();
        $this->db->where('ID_PUser', $idsuOrigen);
        $query = $this->db->get('Pictograms');
        //MODIF: mirar si ya lo tiene
        if ($query->num_rows() > 0) {

            $voc = $query->result();
            foreach ($voc as $word) {
                $data = array(
                    'pictoid' => $word->pictoid,
                    'ID_PUser' => $idsuDest,
                    'pictoType' => $word->pictoType,
                    'supportsExpansion' => $word->supportsExpansion,
                    'imgPicto' => $word->imgPicto);

                $sql = $this->db->insert_string('Pictograms', $data);
                $sql = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $sql);
                $this->db->query($sql);
            }
        } else
            $voc = null;

        return $voc;
    }

    function EditWordNoms($id) {
        $output = array();
        $userlanguage = $this->session->userdata('ulangabbr');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('nameid', $id);
        $this->db->join('Pictograms', 'Name' . $userlanguage . '.nameid = Pictograms.pictoid', 'left');
        $query = $this->db->get('Name' . $userlanguage);

        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;

        return $output;
    }
    function EditWordAdj($id) {
        $output = array();
        $userlanguage = $this->session->userdata('ulangabbr');
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('adjid', $id);
        $this->db->join('Pictograms', 'adjective' . $userlanguage . '.adjid = Pictograms.pictoid', 'left');
        $query = $this->db->get('adjective' . $userlanguage);

        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;

        return $output;
    }

    function getDBClassNames($id) {
        $output = array();
        $userlanguage = $this->session->userdata('ulangabbr');
        $this->db->where('nameid', $id);
        $this->db->select('class');
        $query = $this->db->get('NameClass' . $userlanguage);

        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;

        return $output;
    }
    function getDBClassAdj($id) {
        $output = array();
        $userlanguage = $this->session->userdata('ulangabbr');
        $this->db->where('adjid', $id);
        $this->db->select('class');
        $query = $this->db->get('adjclass' . $userlanguage);

        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;

        return $output;
    }

    
    function getDBVerbs(){
        $user = $this->session->userdata('idusu');
        // Expansion language
        $languageExp = $this->session->userdata('ulangabbr');
        //Interface language
        $languageInt = $this->session->userdata('uinterfacelangauge');

        $output = array();

        $this->db->where_in('Pictograms.ID_PUser', array('1',$user));
        $this->db->where('PictogramsLanguage.languageid', $languageInt); //Get all default and own user pictos
        $this->db->select('verbid as id, PictogramsLanguage.pictotext as text');
        $this->db->from('Verb' . $languageExp);
        $this->db->join('Pictograms', 'Verb' . $languageExp . '.verbid = Pictograms.pictoid', 'left');
        $this->db->join('PictogramsLanguage', 'PictogramsLanguage.pictoid = Pictograms.pictoid', 'left');
        $this->db->where('actiu', '1');
        $this->db->order_by('PictogramsLanguage.pictotext', 'asc');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $output = $query->result_array();
        }

        return $output;
    }

    
    /*
     * Gets all names from ddbb that starts with ($startswith) in the language ($language)
     */

    function getDBNamesLike($startswith, $user) {
        // Expansion language
        $languageExp = $this->session->userdata('ulangabbr');
        //Interface language
        $languageInt = $this->session->userdata('uinterfacelangauge');

        $output = array();
        $this->db->limit(6); // limit up to 6

        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('Pictograms.ID_PUser', $user);
        $this->db->where('PictogramsLanguage.languageid', $languageInt);
        //$this->db->or_where('Pictograms.ID_PUser', $user); //Get all default and own user pictos
        $this->db->select('nameid as id, PictogramsLanguage.pictotext as text, imgPicto, Pictograms.ID_PUser'); // rename the field like we want
        //$this->db->from('Name'. $language);// select the table name+language
        $this->db->join('Pictograms', 'Name' . $languageExp . '.nameid = Pictograms.pictoid', 'left'); // Join the tables name with the picto associate
        $this->db->join('PictogramsLanguage', 'PictogramsLanguage.pictoid = Pictograms.pictoid', 'left');
        $this->db->like('PictogramsLanguage.pictotext', $startswith, 'after'); // select only the names that start with $startswith
        $this->db->order_by('PictogramsLanguage.pictotext', 'asc'); // order the names 
        $query = $this->db->get('Name' . $languageExp); // execute de query

        if ($query->num_rows() > 0) {
            $output = $query->result_array();
        }
        return $output;
    }

    /*
     * Gets all verbs from ddbb that starts with ($startswith) in the language ($language)
     */

    function getDBVerbsLike($startswith, $user) {
        // Expansion language
        $languageExp = $this->session->userdata('ulangabbr');
        //Interface language
        $languageInt = $this->session->userdata('uinterfacelangauge');

        $output = array();

        $this->db->limit(6);
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('Pictograms.ID_PUser', $user);
        $this->db->where('PictogramsLanguage.languageid', $languageInt); //Get all default and own user pictos
        $this->db->select('verbid as id, PictogramsLanguage.pictotext as text, imgPicto');
        $this->db->from('Verb' . $languageExp);
        $this->db->join('Pictograms', 'Verb' . $languageExp . '.verbid = Pictograms.pictoid', 'left');
        $this->db->join('PictogramsLanguage', 'PictogramsLanguage.pictoid = Pictograms.pictoid', 'left');
        $this->db->where('actiu', '1');
        $this->db->like('PictogramsLanguage.pictotext', $startswith, 'after');
        $this->db->order_by('PictogramsLanguage.pictotext', 'asc');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $output = $query->result_array();
        }

        return $output;
    }

    /*
     * Gets all adjectius from ddbb that starts with ($startswith) in the language ($language)
     */

    function getDBAdjLike($startswith, $user) {
        // Expansion language
        $languageExp = $this->session->userdata('ulangabbr');
        //Interface language
        $languageInt = $this->session->userdata('uinterfacelangauge');

        $output = array();

        $this->db->limit(6);
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('Pictograms.ID_PUser', $user);
        $this->db->where('PictogramsLanguage.languageid', $languageInt); //Get all default and own user pictos
        $this->db->select('adjid as id,PictogramsLanguage.pictotext as text, imgPicto');
        $this->db->from('Adjective' . $languageExp);
        $this->db->join('Pictograms', 'Adjective' . $languageExp . '.adjid = Pictograms.pictoid', 'left');
        $this->db->join('PictogramsLanguage', 'PictogramsLanguage.pictoid = Pictograms.pictoid', 'left');
        $this->db->like('PictogramsLanguage.pictotext', $startswith, 'after');
        $this->db->order_by('PictogramsLanguage.pictotext', 'asc');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $output = $query->result_array();
        }
        return $output;
    }

    /*
     * Gets all expressions from ddbb that starts with ($startswith) in the language ($language)
     */

    function getDBExprsLike($startswith, $user) {
        // Expansion language
        $languageExp = $this->session->userdata('ulangabbr');
        //Interface language
        $languageInt = $this->session->userdata('uinterfacelangauge');

        $output = array();

        $this->db->limit(6);
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('Pictograms.ID_PUser', $user);
        $this->db->where('PictogramsLanguage.languageid', $languageInt); //Get all default and own user pictos
        $this->db->select('exprid as id, PictogramsLanguage.pictotext as text, imgPicto');
        $this->db->from('Expressions' . $languageExp);
        $this->db->join('Pictograms', 'Expressions' . $languageExp . '.exprid = Pictograms.pictoid', 'left');
        $this->db->join('PictogramsLanguage', 'PictogramsLanguage.pictoid = Pictograms.pictoid', 'left');
        $this->db->like('PictogramsLanguage.pictotext', $startswith, 'after');
        $this->db->order_by('PictogramsLanguage.pictotext', 'asc');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $output = $query->result_array();
        }
        return $output;
    }

    /*
     * Gets all adverbs from ddbb that starts with ($startswith) in the language ($language)
     */

    function getDBAdvsLike($startswith, $user) {
        // Expansion language
        $languageExp = $this->session->userdata('ulangabbr');
        //Interface language
        $languageInt = $this->session->userdata('uinterfacelangauge');

        $output = array();

        $this->db->limit(6);
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('Pictograms.ID_PUser', $user);
        $this->db->where('PictogramsLanguage.languageid', $languageInt); //Get all default and own user pictos
        $this->db->select('advid as id, PictogramsLanguage.pictotext as text, imgPicto');
        $this->db->from('Adverb' . $languageExp);
        $this->db->join('Pictograms', 'Adverb' . $languageExp . '.advid = Pictograms.pictoid', 'left');
        $this->db->join('PictogramsLanguage', 'PictogramsLanguage.pictoid = Pictograms.pictoid', 'left');
        $this->db->like('PictogramsLanguage.pictotext', $startswith, 'after');
        $this->db->order_by('PictogramsLanguage.pictotext', 'asc');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $output = $query->result_array();
        }

        return $output;
    }

    /*
     * Gets all modifier from ddbb that starts with ($startswith) in the language ($language)
     */

    function getDBModifsLike($startswith, $user) {
        // Expansion language
        $languageExp = $this->session->userdata('ulangabbr');
        //Interface language
        $languageInt = $this->session->userdata('uinterfacelangauge');

        $output = array();

        $this->db->limit(6);
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('Pictograms.ID_PUser', $user);
        $this->db->where('PictogramsLanguage.languageid', $languageInt); //Get all default and own user pictos
        $this->db->select('modid as id, PictogramsLanguage.pictotext as text, imgPicto');
        $this->db->from('Modifier' . $languageExp);
        $this->db->join('Pictograms', 'Modifier' . $languageExp . '.modid = Pictograms.pictoid', 'left');
        $this->db->join('PictogramsLanguage', 'PictogramsLanguage.pictoid = Pictograms.pictoid', 'left');
        $this->db->like('PictogramsLanguage.pictotext', $startswith, 'after');
        $this->db->order_by('PictogramsLanguage.pictotext', 'asc');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $output = $query->result_array();
        }

        return $output;
    }

    /*
     * Gets all QuestionPart from ddbb that starts with ($startswith) in the language ($language)
     */

    function getDBQuestionPartLike($startswith, $user) {
        // Expansion language
        $languageExp = $this->session->userdata('ulangabbr');
        //Interface language
        $languageInt = $this->session->userdata('uinterfacelangauge');

        $output = array();

        $this->db->limit(6);
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('Pictograms.ID_PUser', $user);
        $this->db->where('PictogramsLanguage.languageid', $languageInt);  //Get all default and own user pictos
        $this->db->select('questid as id, PictogramsLanguage.pictotext as text, imgPicto');
        $this->db->from('QuestionPart' . $languageExp);
        $this->db->join('Pictograms', 'QuestionPart' . $languageExp . '.questid = Pictograms.pictoid', 'left');
        $this->db->join('PictogramsLanguage', 'PictogramsLanguage.pictoid = Pictograms.pictoid', 'left');
        $this->db->like('PictogramsLanguage.pictotext', $startswith, 'after');
        $this->db->order_by('PictogramsLanguage.pictotext', 'asc');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $output = $query->result_array();
        }

        return $output;
    }

}
