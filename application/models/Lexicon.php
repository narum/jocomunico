<?php
class Lexicon extends CI_Model {
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        
        $this->load->library('Myword');
    }
    /* CHECKS IF THE USER EXISTS
     * 
     * Per la interfície: Funcions de validar usuari,
     * per fer els LLISTATS de PARAULES i per CONSTRUIR
     * la FRASE
     */
    
    function validar_usuari()
    {
        $usuari = $this->input->post('usuari', true);
        $pass = md5($this->input->post('pass', true));
        $output = array();
        $this->db->where('SUname', $usuari);
        $this->db->where('pswd', $pass);
        
        $query = $this->db->get('SuperUser');
        
        if ($query->num_rows() > 0) {
            
            // Get user data and user config data
            $this->db->from('SuperUser');
            $this->db->join('User', 'SuperUser.cfgDefUser = User.ID_User');
            $this->db->join('Languages', 'SuperUser.cfgDefUser = User.ID_User AND User.ID_ULanguage = Languages.ID_Language', 'right');
            $this->db->where('SUname', $usuari);
            $query2 = $this->db->get()->result_array();
            $userConfig = $query2[0];
            
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
                        
            return true;
        }
        else return false;
    }
    /*
     * GETS THE NOUNS OF THE TYPE $type FROM THE DATABASE
     */
    function getNoms($tipus)
    {
        $output = array();
        $userlanguage = $this->session->userdata('ulangabbr');
        for ($i=0; $i<count($tipus); $i++) {
            $this->db->or_where('class', $tipus[$i]);
        }
        $this->db->order_by('Name'.$userlanguage.'.nomtext', 'asc');
        $this->db->join('NameClass'.$userlanguage, 'NameClass'.$userlanguage.'.nameid = Name'.$userlanguage.'.nameid', 'left');
        $this->db->join('Pictograms', 'Name'.$userlanguage.'.nameid = Pictograms.pictoid', 'left');
        $this->db->group_by('Pictograms.pictoid');
        $query = $this->db->get('Name'.$userlanguage);
        
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        else $output = null;
        
        return $output;
    }
    
    function getVerbs()
    {
        $output = array();
        $userlanguage = $this->session->userdata('ulangabbr');
        $this->db->where('actiu', '1');
        $this->db->order_by('verbtext', 'asc');
        $this->db->join('Pictograms', 'Verb'.$userlanguage.'.verbid = Pictograms.pictoid', 'left');
        $this->db->group_by('Pictograms.pictoid');
        $query = $this->db->get('Verb'.$userlanguage);
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        else $output = null;
        return $output;
    }
    
    function getAdjs($tipus)
    {
        $output = array();
        $userlanguage = $this->session->userdata('ulangabbr');
        for ($i=0; $i<count($tipus); $i++) {
            $this->db->or_where('class', $tipus[$i]);
        }
        $this->db->order_by('Adjective'.$userlanguage.'.masc', 'asc');
        $this->db->join('AdjClass'.$userlanguage, 'AdjClass'.$userlanguage.'.adjid = Adjective'.$userlanguage.'.adjid', 'left');
        $this->db->join('Pictograms', 'Adjective'.$userlanguage.'.adjid = Pictograms.pictoid', 'left');
        $this->db->group_by('Pictograms.pictoid');
        $query = $this->db->get('Adjective'.$userlanguage);
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        else $output = null;
        return $output;
    }
    
    function getAdvs($tipus)
    {
        $output = array();
        $userlanguage = $this->session->userdata('ulangabbr');
        for ($i=0; $i<count($tipus); $i++) {
            $this->db->or_where('type', $tipus[$i]);
        }
        $this->db->order_by('Adverb'.$userlanguage.'.advtext', 'asc');
        $this->db->join('AdvType'.$userlanguage, 'AdvType'.$userlanguage.'.advid = Adverb'.$userlanguage.'.advid', 'left');
        $this->db->join('Pictograms', 'Adverb'.$userlanguage.'.advid = Pictograms.pictoid', 'left');
        $this->db->group_by('Pictograms.pictoid');
        $query = $this->db->get('Adverb'.$userlanguage);
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        else $output = null;
        return $output;
    }
    
    function getModifs($tipus)
    {
        $output = array();
        $userlanguage = $this->session->userdata('ulangabbr');
        for ($i=0; $i<count($tipus); $i++) {
            $this->db->or_where('scope', $tipus[$i]);
        }
        $this->db->order_by('masc', 'asc');
        $this->db->join('Pictograms', 'Modifier'.$userlanguage.'.modid = Pictograms.pictoid', 'left');
        $this->db->group_by('Pictograms.pictoid');
        $query = $this->db->get('Modifier'.$userlanguage);
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        else $output = null;
        return $output;
    }
    
    function getExprs($tipus)
    {
        $output = array();
        $userlanguage = $this->session->userdata('ulangabbr');
        for ($i=0; $i<count($tipus); $i++) {
            $this->db->or_where('type', $tipus[$i]);
        }
        $this->db->order_by('Expressions'.$userlanguage.'.exprtext', 'asc');
        $this->db->join('ExprType'.$userlanguage, 'ExprType'.$userlanguage.'.exprid = Expressions'.$userlanguage.'.exprid', 'left');
        $this->db->join('Pictograms', 'Expressions'.$userlanguage.'.exprid = Pictograms.pictoid', 'left');
        $this->db->group_by('Pictograms.pictoid');
        $query = $this->db->get('Expressions'.$userlanguage);
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        else $output = null;
        return $output;
    }
    
    function getPartPregunta()
    {
        $output = array();
        $userlanguage = $this->session->userdata('ulangabbr');
        $this->db->order_by('parttext', 'asc');
        $this->db->join('Pictograms', 'QuestionPart'.$userlanguage.'.questid = Pictograms.pictoid', 'left');
        $this->db->group_by('Pictograms.pictoid');
        $query = $this->db->get('QuestionPart'.$userlanguage);
        if ($query->num_rows() > 0) {
            $output = $query->result();
        }
        else $output = null;
        return $output;
    }
    
    /**
     * FUNCIONS PELS DIFERENTS SISTEMES D'INPUT
     */
    function insertarFraseDesDArxiu($frase)
    {
        /*
         * Fer split, per cada paraula: passar els caracters a minúscula
         * mirar si és un modificador de noms ($),
         * de tipus de frase (#) o de temps verbals (@). Aquests dos últims els guardem
         * per introduïr-los després com a propietats de la frase.
         * Les paraules normals les busquem i fem un afegirParaula
         * Els modificadors de nom, fem un afegirModifNom
         * Un cop introduïdes totes les paraules fem un insertarFrase amb els canvis
         * que calgui fer al codi
         */
        
        $idusu = $this->session->userdata('idusu');
        $userlanguage = $this->session->userdata('ulanguage'); // en número i no en abbr 'CA'...
        
        $frase = strtolower($frase);
        // eliminem tots els espais
        $frase = preg_replace('/\s+/', '', $frase);
        $paraules = explode("/", $frase);
        
        // Després de l'última barra / no hi ha cap paraula
        $numparaules = count($paraules)-1;
        $paraulesbones = 0;
        
        $tipusfrase = "defecte";
        $tempsverbal = "defecte";
        $negativa = false;
        
        $nounentered = false;
        $queuedmodif = false;
        $queuedmodifs = array();
                
        for ($i=0; $i<$numparaules; $i++) {
            
            $paraula = $paraules[$i];
            $primercaracter = $paraula[0];
               
            // si és un número vol dir que utilitza el format d'entrada d'ID's de pictograma
            if ($primercaracter == "{") {
                
				$paraula = substr($paraula, 1); // treiem el primer caràcter que és {
				$paraula = substr($paraula, 0, -1); // treiem l'últim caràcter que és }
				
                $pictoid = (int)$paraula; // l'id és la paraula introduïda
                                
                $this->db->where('pictoid', $pictoid);
                $query = $this->db->get('Pictograms');
                
                if ($query->num_rows() > 0) {
                    
                    $aux = $query->result();
                    $infoparaula = $aux[0];
                
                    $taula = $infoparaula->pictoType;
                    // afegim la paraula a la frase de l'usuari
                    $this->afegirParaula($idusu, $pictoid, $taula);
                    $paraulesbones++;
                    if ($taula == "name" || $taula == "adj") {
                        // si hi havia modificadors en espera que s'havien introduït abans del nom o adj
                        if ($queuedmodif) {
                            for ($j=0; $j < count($queuedmodifs); $j++) {
                                $this->afegirModifNom($idusu, $queuedmodifs[$j]);
                            }
                            // reiniciem l'array
                            $queuedmodif = false;
                            unset($queuedmodifs);
                            $queuedmodifs = array();
                        }
                        else {
                            // indiquem que hi ha un nom a on s'hi poden afegir els modificadors
                            $nounentered = true;
                        }
                    }
                    // si és un altre tipus de paraula
                    else {
                        // els modificadors de nom han d'anar engatxats al nom, així que si la paraula
                        // anterior és diferent d'un nom, no volem que s'hi associïn els modificadors de nom
                        $nounentered = false;
                    }
                }
            }
            
            // si no és un número poden ser modificadors, tipus de frase, temps verbals, negacions
            // o poden ser pictogrames introduïts en format text
            else {
                
                switch ($primercaracter) {
                                
                    case "$":
                        $paraula = substr($paraula, 1);
                        // si ja s'ha introduït un nom o adj, hi associem el modificador
                        if ($nounentered) {
                            $this->afegirModifNom($idusu, $paraula);
                        }
                        // si no esperarem a que hi hagi algun nom per associar-hi 
                        // els modificadors que estiguin a la cua
                        else {
                            $queuedmodif = true;
                            $queuedmodifs[] = $paraula;
                        }
                        break;
                    case "#":
                        $paraula = substr($paraula, 1);
                        $tipusfrase = $paraula;
                        break;
                    case "@":
                        $paraula = substr($paraula, 1);
                        $tempsverbal = $paraula;
                        break;
                    case "%":
                        $paraula = substr($paraula, 1);
                        $negativa = true;
                        break;
                    default:
                        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
                        $this->db->where('pictotext', $paraula);
                        $this->db->where('languageid', $userlanguage);
                        $this->db->join('Pictograms', 'Pictograms.pictoid = PictogramsLanguage.pictoid', 'left');
                        $query = $this->db->get('PictogramsLanguage');
                        if ($query->num_rows() > 0) {
                            $aux = $query->result();
                            $infoparaula = $aux[0];
                            // si hi ha més d'una paraula que fa match (2), fem les comparacions
                            // per veure amb quina de les dues ens quedem
                            if (count($aux) > 1) {
                                $type1 = $aux[0]->pictoType;
                                $type2 = $aux[1]->pictoType;
                                switch ($type1) {
                                    case "name":
                                        switch ($type2) {
                                            case "name":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "verb":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "adj":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "adv":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "expression":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "modifier":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "questpart":
                                                $infoparaula = $aux[1];
                                                break;
                                            default:
                                                $infoparaula = $aux[0];
                                                break;
                                        }
                                        break;
                                    case "verb":
                                        switch ($type2) {
                                            case "name":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "verb":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "adj":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "adv":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "expression":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "modifier":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "questpart":
                                                $infoparaula = $aux[0];
                                                break;
                                            default:
                                                $infoparaula = $aux[0];
                                                break;
                                        }
                                        break;
                                    case "adj":
                                        switch ($type2) {
                                            case "name":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "verb":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "adj":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "adv":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "expression":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "modifier":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "questpart":
                                                $infoparaula = $aux[1];
                                                break;
                                            default:
                                                $infoparaula = $aux[0];
                                                break;
                                        }
                                        break;
                                    case "adv":
                                        switch ($type2) {
                                            case "name":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "verb":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "adj":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "adv":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "expression":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "modifier":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "questpart":
                                                $infoparaula = $aux[0];
                                                break;
                                            default:
                                                $infoparaula = $aux[0];
                                                break;
                                        }
                                        break;
                                    case "expression":
                                        switch ($type2) {
                                            case "name":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "verb":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "adj":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "adv":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "expression":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "modifier":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "questpart":
                                                $infoparaula = $aux[1];
                                                break;
                                            default:
                                                $infoparaula = $aux[0];
                                                break;
                                        }
                                        break;
                                    case "modifier":
                                        switch ($type2) {
                                            case "name":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "verb":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "adj":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "adv":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "expression":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "modifier":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "questpart":
                                                $infoparaula = $aux[0];
                                                break;
                                            default:
                                                $infoparaula = $aux[0];
                                                break;
                                        }
                                        break;
                                    case "questpart":
                                        switch ($type2) {
                                            case "name":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "verb":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "adj":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "adv":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "expression":
                                                $infoparaula = $aux[0];
                                                break;
                                            case "modifier":
                                                $infoparaula = $aux[1];
                                                break;
                                            case "questpart":
                                                $infoparaula = $aux[0];
                                                break;
                                            default:
                                                $infoparaula = $aux[0];
                                                break;
                                        }
                                        break;
                                    default:
                                        $infoparaula = $aux[0];
                                        break;
                                }
                            }
                            $pictoid = $infoparaula->pictoid;
                            $taula = $infoparaula->pictoType;
                            $this->afegirParaula($idusu, $pictoid, $taula);
                            $paraulesbones++;
                            if ($taula == "name" || $taula == "adj") {
                                // si hi havia modificadors en espera que s'havien introduït abans del nom o adj
                                if ($queuedmodif) {
                                    for ($j=0; $j < count($queuedmodifs); $j++) {
                                        $this->afegirModifNom($idusu, $queuedmodifs[$j]);
                                    }
                                    // reiniciem l'array
                                    $queuedmodif = false;
                                    unset($queuedmodifs);
                                    $queuedmodifs = array();
                                }
                                else {
                                    // indiquem que hi ha un nom a on s'hi poden afegir els modificadors
                                    $nounentered = true;
                                }
                            }
                            // si és un altre tipus de paraula
                            else {
                                // els modificadors de nom han d'anar engatxats al nom, així que si la paraula
                                // anterior és diferent d'un nom, no volem que s'hi associïn els modificadors de nom
                                $nounentered = false;
                            }
                        }
                        break;
                }
            }
        } // Fi del for per cada paraula
        
        // si s'han introduït paraules, aleshores afegim la frase a la BBDD per expandir-la
        if ($paraulesbones > 0) {
            $this->passarFraseABBDD($idusu, $tipusfrase, $tempsverbal, $negativa);
        }
    }
    
    /**
     * INSERTS A PICTOGRAM TO THE DB. AVOIDS ENTERING TWO EQUAL CONSECUTIVE PICTOGRAMS
     * @param type $idusu
     * @param type $idparaula
     * @param type $imgtemp Es fa servir amb la nova interfície, per si l'usuari ha modificat 
     * la img per defecte del pictograma es guarda aquesta imatge.
     */
    function afegirParaula($idusu, $idparaula, $imgtemp)
    {
        $paraula = array();
        $pictoid = -1;
        $coord = '0';
        
        // gets the last inserted pictogram
        $this->db->where('ID_RSTPUser', $idusu);
        $this->db->order_by('ID_RSTPSentencePicto', 'desc');
        $query = $this->db->get('R_S_TempPictograms', 1, 0);
        
        if ($query->num_rows() > 0) {
            $paraula = $query->result();
            $pictoid = $paraula[0]->pictoid;
            $coord = $paraula[0]->coordinated;
        }
        
        // if it's not equal to the last inserted pictogram
        if (($idparaula != $pictoid) || $coord != '0') {
            $data = array(
                'pictoid' => $idparaula,
                'ID_RSTPUser' => $idusu,
                'imgtemp' => $imgtemp,
            );
            $this->db->insert('R_S_TempPictograms', $data);
        }
    }
    /*
     * GET THE WORDS ALREADY ENTERED IN THE USER INTERFACE
     */
    function recuperarFrase($idusu) // Per la interfície d'introduir la frase
    {
        $output = array();
        $userlanguage = $this->session->userdata('ulangabbr');
        
        $paraules = array();
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('ID_RSTPUser', $idusu);
        $this->db->join('Pictograms', 'Pictograms.pictoid = R_S_TempPictograms.pictoid', 'left');
        $this->db->order_by('ID_RSTPSentencePicto', 'asc');
        $query = $this->db->get('R_S_TempPictograms');
        $beforeverb = true;
        $beforeverb2 = true;
        $countverb = 0;
        if ($query->num_rows() > 0) {
            $paraules = $query->result();
            for ($i=0; $i<count($paraules); $i++) {
                $output[$i] = array();
                // L'estructura de dades és una array multidimensional. A cada casella
                // hi ha una tupla (array): a l'element 0 hi ha el tipus de paraula (Nom, Adjectiu, etc),
                // a l'element 1 un array amb la info de la paraula (pot ser que tingui més d'una entrada
                // si la paraula té vàries classes), a l'element 2 hi ha l'id de la Word Entry, al 3
                // hi ha si té modificador de plural i al 4 si en té de femení. El 3 i 4 són només per Noms
                // i Adjectius (ja que de vegades poden actuar com a noms). El 5 és per si està coordinat
                // amb la següent paraula (només si aquesta és del mateix tipus)
                switch($paraules[$i]->pictoType)
                {
                    case 'name':
                        $this->db->where('Name'.$userlanguage.'.nameid', $paraules[$i]->pictoid);
                        $this->db->join('NameClass'.$userlanguage, 'NameClass'.$userlanguage.'.nameid = Name'.$userlanguage.'.nameid', 'left');
                        $query2 = $this->db->get('Name'.$userlanguage);
                        if ($query2->num_rows() > 0) {
                            $word = new Myword();
                            $word->initialise($paraules[$i], $query2->result(), $i, $beforeverb, $beforeverb2, true);
                            $output[$i] = $word;
                        }
                        else $output[$i] = null;
                        break;
                    case 'verb':
                        $countverb += 1;
                        
                        if ($countverb == 1) $beforeverb = false;
                        else if ($countverb == 2) $beforeverb2 = false;
                        
                        $this->db->where('Verb'.$userlanguage.'.verbid', $paraules[$i]->pictoid);
                        $this->db->join('Pattern'.$userlanguage, 'Pattern'.$userlanguage.'.verbid = Verb'.$userlanguage.'.verbid', 'left');
                        $query2 = $this->db->get('Verb'.$userlanguage);
                        if ($query2->num_rows() > 0) {
                            $word = new Myword();
                            $word->initialise($paraules[$i], $query2->result(), $i, $beforeverb, $beforeverb2, true);
                            $output[$i] = $word;
                        }
                        else $output[$i] = null;
                        break;
                    
                    case 'adj':
                        $this->db->where('Adjective'.$userlanguage.'.adjid', $paraules[$i]->pictoid);
                        $this->db->join('AdjClass'.$userlanguage, 'AdjClass'.$userlanguage.'.adjid = Adjective'.$userlanguage.'.adjid', 'left');
                        $query2 = $this->db->get('Adjective'.$userlanguage);
                        if ($query2->num_rows() > 0) {
                            $word = new Myword();
                            $word->initialise($paraules[$i], $query2->result(), $i, $beforeverb, $beforeverb2, true);
                            $output[$i] = $word;
                        }
                        else $output[$i] = null;
                        break;
                    
                    case 'adv':
                        $this->db->where('Adverb'.$userlanguage.'.advid', $paraules[$i]->pictoid);
                        $this->db->join('AdvType'.$userlanguage, 'AdvType'.$userlanguage.'.advid = Adverb'.$userlanguage.'.advid', 'left');
                        $query2 = $this->db->get('Adverb'.$userlanguage);
                        if ($query2->num_rows() > 0) {
                            $word = new Myword();
                            $word->initialise($paraules[$i], $query2->result(), $i, $beforeverb, $beforeverb2, true);
                            $output[$i] = $word;
                        }
                        else $output[$i] = null;
                        
                        break;
                    
                    case 'expression':
                        $this->db->where('Expressions'.$userlanguage.'.exprid', $paraules[$i]->pictoid);
                        $this->db->join('ExprType'.$userlanguage, 'ExprType'.$userlanguage.'.exprid = Expressions'.$userlanguage.'.exprid', 'left');
                        $query2 = $this->db->get('Expressions'.$userlanguage);
                        if ($query2->num_rows() > 0) {
                            $word = new Myword();
                            $word->initialise($paraules[$i], $query2->result(), $i, $beforeverb, $beforeverb2, true);
                            $output[$i] = $word;
                        }
                        else $output[$i] = null;
                        break;
                    
                    case 'modifier':
                        $this->db->where('modid', $paraules[$i]->pictoid);
                        $query2 = $this->db->get('Modifier'.$userlanguage);
                        if ($query2->num_rows() > 0) {
                            $word = new Myword();
                            $word->initialise($paraules[$i], $query2->result(), $i, $beforeverb, $beforeverb2, true);
                            $output[$i] = $word;
                        }
                        else $output[$i] = null;
                        break;
                    
                    case 'questpart':
                        $this->db->where('questid', $paraules[$i]->pictoid);
                        $query2 = $this->db->get('QuestionPart'.$userlanguage);
                        if ($query2->num_rows() > 0) {
                            $word = new Myword();
                            $word->initialise($paraules[$i], $query2->result(), $i, $beforeverb, $beforeverb2, true);
                            $output[$i] = $word;
                        }
                        else $output[$i] = null;
                        break;
                    default:
                        $output[$i] = null;
                        break;
                }
            }
        }
        else $output = null;
        return $output;
    }
    /*
     * DELETE A WORD PREVIOUSLY ENTERED
     */
    function eliminarParaula($identry)
    {
        $this->db->where('ID_RSTPSentencePicto', $identry);
        $this->db->delete('R_S_TempPictograms');
    }
    /*
     * ADD MODIFIER TO A NOUN THAT WAS JUST ENTERED
     */
    function afegirModifNom($idusu, $modif)
    {
        $this->db->where('ID_RSTPUser', $idusu);
        $query = $this->db->get('R_S_TempPictograms');
        if ($query->num_rows() > 0) {
            $aux = $query->result();
            $nrows = $query->num_rows();
            $identry = $aux[$nrows-1]->ID_RSTPSentencePicto;
            if($modif=='pl') {
                $data = array(
                    'isplural' => '1',
                );
            }
            if($modif=='fem') {
                $data = array(
                    'isfem' => '1',
                );
            }
            if($modif=='i') {
                $data = array(
                    'coordinated' => '1',
                );
            }
            $this->db->where('ID_RSTPSentencePicto', $identry);
            $this->db->update('R_S_TempPictograms', $data);
        }
    }
    /*
     * SEND WORDS ENTERED BY THE USER TO THE DATABASE
     */
    function insertarFrase($idusu, $tipusfrase, $tense, $negativa)
    {
        $datestring = "%Y/%m/%d";
        $time = time();
        $avui = mdate($datestring, $time);
        if ($negativa) $negativa = '1';
        else $negativa = '0';
        
        // calculem l'string d'inputwords: és el llistat de paraules com apareixien
        // a Elements Seleccionats, just abans de prémer Generar
        $paraulesFrase = $this->recuperarFrase($idusu);
        
        // guardem les estadístiques de la frase
        $this->addStatsX1($paraulesFrase, $idusu);
        $this->addStatsX2($paraulesFrase, $idusu);
        $this->addStatsX3($paraulesFrase, $idusu);
        
        $inputwords = "";
        
        // Hi afegirem també els ids, modifs, tipus de frases i tenses a l'string
        // per imprimir per pantalla per DEBUG
        $inputids = "";
        for ($i=0; $i<count($paraulesFrase); $i++) {
            if ($paraulesFrase[$i] != null) {
                $word = $paraulesFrase[$i];
                $inputwords .= $word->text;
                $inputids .= "{".$word->id."}";
                
                /*switch($word->pictoType)
                {
                    case 'name':
                        $inputids .= $word->nameid;
                        break;
                    case 'verb':
                        $inputids .= $word->verbid;
                        break;
                    case 'adj':
                        $inputids .= $word->adjid;
                        break;
                    case 'adv':
                        $inputids .= $word->advid;
                        break;
                    case 'expression':
                        $inputids .= $word->exprid;
                        break;
                    case 'modifier':
                        $inputids .= $word->modid;
                        break;
                    case 'questpart':
                        $inputids .= $word->questid;
                        break;
                    default:
                        $inputids .= "";
                        break;
                }*/
                
                // SEGUIR AQUÍ AMB ELS PL FEM COORD I TIPUS DE FRASE I TENSES
                if ($word->plural) $inputids .= " / \$pl";
                if ($word->fem) $inputids .= " / \$fem";
                if ($word->coord) $inputids .= " / \$i";
                
                if($word->plural || $word->fem || $word->coord) {
                    $inputwords .= '(';
                    if ($word->plural) $inputwords .= 'pl';
                    if ($word->plural && ($word->fem || $word->coord)) $inputwords .= ', ';
                    if ($word->fem) $inputwords .= 'fem';
                    if ($word->fem && $word->coord) $inputwords .= ', ';
                    if ($word->coord) $inputwords .= 'i';
                    $inputwords .= ')';
                } 
                if ($i < (count($paraulesFrase) - 1)) $inputwords .= " / ";
                if ($i < (count($paraulesFrase) - 1)) $inputids .= " / ";
            }
        }
        $inputids .= " / #".$tipusfrase;
        $inputids .= " / @".$tense;
        if ($negativa) $inputids .= " / %no";
        $inputids .= " /";
        
        $inputwords .="<br /><br />".$inputids;
        $data = array(
            'ID_SHUser' => $idusu,
            'sentenceType' => $tipusfrase,
            'isNegative' => $negativa,
            'sentenceTense' => $tense,
            'sentenceDate' => $avui,
            'intendedSentence' => $this->input->post('fraseobj', true),
            'sentenceFinished' => '1',
            'inputWords' => $inputwords,
            'inputIds' => $inputids,
        );
        $this->db->insert('S_Historic', $data);
        $identry = $this->db->insert_id();
        $this->db->where('ID_RSTPUser', $idusu);
        $query = $this->db->get('R_S_TempPictograms');
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data2 = array(
                    'ID_RSHPSentence' => $identry,
                    'pictoid' => $row->pictoid,
                    'isplural' => $row->isplural,
                    'isfem' => $row->isfem,
                    'coordinated' => $row->coordinated,
                    'imgtemp' => $row->imgtemp,
                );
                $this->db->insert('R_S_HistoricPictograms', $data2);
            }
        }
        // Eliminar les paraules de la taula provisional
        if ($this->session->userdata('cfgAutoEraseSentenceBar') == '1') {
            $this->db->where('ID_RSTPUser', $idusu);
            $this->db->delete('R_S_TempPictograms');
        }
    }
    
    /*
     * SEND WORDS ENTERED FROM FILE BY THE USER TO THE DATABASE
     */
    function passarFraseABBDD($idusu, $tipusfrase, $tempsverbal, $negativa)
    {
        $datestring = "%Y/%m/%d";
        $time = time();
        $avui = mdate($datestring, $time);
        if ($negativa) $negativa = '1';
        else $negativa = '0';
        
        // calculem l'string d'inputwords: és el llistat de paraules com apareixien
        // a Elements Seleccionats, just abans de prémer Generar
        $paraulesFrase = $this->recuperarFrase($idusu);
        $inputwords = "";
        $inputids = "";
        for ($i=0; $i<count($paraulesFrase); $i++) {
            if ($paraulesFrase[$i] != null) {
                $word = $paraulesFrase[$i];
                $inputwords .= $word->text;
                
                $inputids .= "{".$word->id."}";
                if ($word->plural) $inputids .= " / \$pl";
                if ($word->fem) $inputids .= " / \$fem";
                if ($word->coord) $inputids .= " / \$i";
                
                if($word->plural || $word->fem || $word->coord) {
                    $inputwords .= '(';
                    if ($word->plural) $inputwords .= 'pl';
                    if ($word->plural && ($word->fem || $word->coord)) $inputwords .= ', ';
                    if ($word->fem) $inputwords .= 'fem';
                    if ($word->fem && $word->coord) $inputwords .= ', ';
                    if ($word->coord) $inputwords .= 'i';
                    $inputwords .= ')';
                } 
                if ($i < (count($paraulesFrase))) $inputwords .= " / ";
                if ($i < (count($paraulesFrase) - 1)) $inputids .= " / ";
            }
        }
        
        $inputids .= " / #".$tipusfrase;
        $inputids .= " / @".$tempsverbal;
        if ($negativa) $inputids .= " / %no";
        $inputids .= " /";
        
        $inputwords .="<br /><br />".$inputids;
        
        $data = array(
            'ID_SHUser' => $idusu,
            'sentenceType' => $tipusfrase,
            'isNegative' => $negativa,
            'sentenceTense' => $tempsverbal,
            'sentenceDate' => $avui,
            'intendedSentence' => "",
            'sentenceFinished' => '1',
            'inputWords' => $inputwords,
            'inputIds' => $inputids,
        );
        $this->db->insert('S_Historic', $data);
        $identry = $this->db->insert_id();
        $this->db->where('ID_RSTPUser', $idusu);
        $query = $this->db->get('R_S_TempPictograms');
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data2 = array(
                    'ID_RSHPSentence' => $identry,
                    'pictoid' => $row->pictoid,
                    'isplural' => $row->isplural,
                    'isfem' => $row->isfem,
                    'coordinated' => $row->coordinated,
                    'imgtemp' => $row->imgtemp,
                );
                $this->db->insert('R_S_HistoricPictograms', $data2);
            }
        }
        // Eliminar les paraules de la taula provisional
        $this->db->where('ID_RSTPUser', $idusu);
        $this->db->delete('R_S_TempPictograms');
    }
    /*
     * Funcions pel PARSER
     */
    
    /*
     * GETS THE WORDS ENTERED BY THE USER IN THE LAST INPUT
     */
    function getLastSentence($idusu)
    {
        $output = array();
        $userlanguage = $this->session->userdata('ulangabbr');
        
        $paraules = array();
        $identry;
        $beforeverb = true;
        $beforeverb2 = true;
        $countverb = 0;
        
        // per tractar les coordinacions de paraules amb "i" que seran transparents
        // i quedaran enganxades a la paraula anterior
        $paraulaprevia = null;
        $ordre = -1;
        $itrobada = false;
        $ibona = false;
        
        $this->db->where('ID_SHUser', $idusu);
        $this->db->order_by('ID_SHistoric', 'desc');
        $query = $this->db->get('S_Historic');
        
        if ($query->num_rows() > 0) {
            $aux = $query->result();
            
            $identry = $aux[0]->ID_SHistoric;
        }
        else return null;
            
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('ID_RSHPSentence', $identry);
        $this->db->join('Pictograms', 'Pictograms.pictoid = R_S_HistoricPictograms.pictoid', 'left');
        $this->db->order_by('ID_RSHPSentencePicto', 'asc');
        $query = $this->db->get('R_S_HistoricPictograms');
        if ($query->num_rows() > 0) {
            $paraules = $query->result();
            
            for ($i=0; $i<count($paraules); $i++) {
                
                $word = null;
                                
                if (!$itrobada) $ordre += 1;
                // L'estructura de dades de MyWord és una array multidimensional. A cada casella
                // hi ha una tupla (array): a l'element 0 hi ha el tipus de paraula (Nom, Adjectiu, etc),
                // a l'element 1 un array amb la info de la paraula (pot ser que tingui més d'una entrada
                // si la paraula té vàries classes), a l'element 2 hi ha l'id de la Word Entry, al 3
                // hi ha si té modificador de plural i al 4 si en té de femení. El 3 i 4 són només per Noms
                // i Adjectius (ja que de vegades poden actuar com a noms). El 5 és per si està coordinat
                // amb la següent paraula (només si aquesta és del mateix tipus).
                // El 10 és per dir si la paraula ja està adjudicada a un slot o no.
                switch($paraules[$i]->pictoType)
                {
                    case 'name':
                        $this->db->where('Name'.$userlanguage.'.nameid', $paraules[$i]->pictoid);
                        $this->db->join('NameClass'.$userlanguage, 'NameClass'.$userlanguage.'.nameid = Name'.$userlanguage.'.nameid', 'left');
                        $query2 = $this->db->get('Name'.$userlanguage);
                        if ($query2->num_rows() > 0) {
                             $word = new Myword();
                             $word->initialise($paraules[$i], $query2->result(), $ordre, $beforeverb, $beforeverb2, false);
                        }
                        break;
                    case 'verb':
                        $countverb += 1;
                        
                        if ($countverb == 1) $beforeverb = false;
                        else if ($countverb == 2) $beforeverb2 = false;
                                                
                        $this->db->where('Verb'.$userlanguage.'.verbid', $paraules[$i]->pictoid);
                        $this->db->join('Pattern'.$userlanguage, 'Pattern'.$userlanguage.'.verbid = Verb'.$userlanguage.'.verbid', 'left');
                        $query2 = $this->db->get('Verb'.$userlanguage);
                        if ($query2->num_rows() > 0) {
                            $word = new Myword();
                            $word->initialise($paraules[$i], $query2->result(), $ordre, $beforeverb, $beforeverb2, false);
                        }
                        break;
                    
                    case 'adj':
                        $this->db->where('Adjective'.$userlanguage.'.adjid', $paraules[$i]->pictoid);
                        $this->db->join('AdjClass'.$userlanguage, 'AdjClass'.$userlanguage.'.adjid = Adjective'.$userlanguage.'.adjid', 'left');
                        $query2 = $this->db->get('Adjective'.$userlanguage);
                        if ($query2->num_rows() > 0) {
                            $word = new Myword();
                            $word->initialise($paraules[$i], $query2->result(), $ordre, $beforeverb, $beforeverb2, false);
                        }
                        break;
                    
                    case 'adv':
                        $this->db->where('Adverb'.$userlanguage.'.advid', $paraules[$i]->pictoid);
                        $this->db->join('AdvType'.$userlanguage, 'AdvType'.$userlanguage.'.advid = Adverb'.$userlanguage.'.advid', 'left');
                        $query2 = $this->db->get('Adverb'.$userlanguage);
                        if ($query2->num_rows() > 0) {
                            $word = new Myword();
                            $word->initialise($paraules[$i], $query2->result(), $ordre, $beforeverb, $beforeverb2, false);
                        }                        
                        break;
                    
                    case 'expression':
                        $this->db->where('Expressions'.$userlanguage.'.exprid', $paraules[$i]->pictoid);
                        $this->db->join('ExprType'.$userlanguage, 'ExprType'.$userlanguage.'.exprid = Expressions'.$userlanguage.'.exprid', 'left');
                        $query2 = $this->db->get('Expressions'.$userlanguage);
                        if ($query2->num_rows() > 0) {
                            $word = new Myword();
                            $word->initialise($paraules[$i], $query2->result(), $ordre, $beforeverb, $beforeverb2, false);
                        }
                        break;
                    
                    case 'modifier':
                        $this->db->where('modid', $paraules[$i]->pictoid);
                        $query2 = $this->db->get('Modifier'.$userlanguage);
                        if ($query2->num_rows() > 0) {
                            $word = new Myword();
                            $word->initialise($paraules[$i], $query2->result(), $ordre, $beforeverb, $beforeverb2, false);
                        }
                        break;
                    
                    case 'questpart':
                        $this->db->where('questid', $paraules[$i]->pictoid);
                        $query2 = $this->db->get('QuestionPart'.$userlanguage);
                        if ($query2->num_rows() > 0) {
                            $word = new Myword();
                            $word->initialise($paraules[$i], $query2->result(), $ordre, $beforeverb, $beforeverb2, false);
                        }
                        break;
                    default:
                        break;
                }
                
                if ($word != null) {
                    
                    // DEBUG
                    // if ($paraulaprevia != null) echo $paraulaprevia->text." - ".$word->text."<br />";
                                        
                    if ($itrobada) {
                        
                        if ($paraulaprevia->tipus == $word->tipus) $ibona = true;
                        // cas especial d'hores (name) i números (adjs)
                        else if (($paraulaprevia->tipus == "name" && $paraulaprevia->isClass("hora")) &&
                                ($word->tipus == "adj" && $word->isClass("numero"))) {
                            $ibona = true;
                        }
                        else $ibona = false;
                        // mirem si la paraula actual també té una coordinació
                        if ($word->coord) $itrobada = true;
                        else $itrobada = false;
                        if ($ibona) {
                            $ibona = false;
                            $paraulaprevia->paraulacoord[] = unserialize(serialize($word));
                        }
                        else {
                            $ordre += 1; // perquè si ibona, al principi no s'havia incrementat
                            $word->inputorder += 1; // xq quan itrobada, tenia el mateix inputorder, a l'espera de si la i era bona
                            $output[$ordre] = $word;
                        }
                    }
                    // si no hi ha cap "i" posem la paraula a la llista de paraules
                    else {
                        $output[$ordre] = $word;
                        
                        if ($word->coord) {
                            $itrobada = true;
                            $paraulaprevia = $word;
                        }
                    }
                }
                else $output[$ordre] = null;
            }
        }
        else $output = null;
        return $output;
    }
    
    // Retorna l'identry, el tipus de frase, si és negativa, el tense, la data alta i la frase objectiu
    function getLastSentenceProperties($idusu)
    {
        $output = array();
        $identry = 0;
        $tipusfrase = "defecte";
        $negativa = false;
        $tense = "defecte";
        $dataalta = null;
        $fraseobjectiu = null;
        $inputwords = null;
        
        $this->db->where('ID_SHUser', $idusu);
        $this->db->order_by('ID_SHistoric', 'desc');
        $query = $this->db->get('S_Historic');
        
        if ($query->num_rows() > 0) {
            $aux = $query->result();
            
            $identry = $aux[0]->ID_SHistoric;
            $tipusfrase = $aux[0]->sentenceType;
            if ($aux[0]->isNegative == '1') $negativa = true;
            $tense = $aux[0]->sentenceTense;
            $dataalta = $aux[0]->sentenceDate;
            $fraseobjectiu = $aux[0]->intendedSentence;
            $inputwords = $aux[0]->inputWords;
        }
        
        $output['identry'] = $identry;
        $output['tipusfrase'] = $tipusfrase;
        $output['negativa'] = $negativa;
        $output['tense'] = $tense;
        $output['dataalta'] = $dataalta;
        $output['fraseobjectiu'] = $fraseobjectiu;
        $output['inputwords'] = $inputwords;
        
        return $output;   
    }
    
    // Retorna el text de l'última frase generada, si no n'hi havia cap de feta retorna null
    function getLastGeneratedText($idusu)
    {
        $text = null;
        
        $this->db->where('ID_SHUser', $idusu);
        $this->db->order_by('ID_SHistoric', 'desc');
        $query = $this->db->get('S_Historic');
        
        // tal com està fet el codi, quan s'apreta generar frase, crea una entrada mig buida a 
        // S_Historic, per tant la frase anterior es troba en la segona posició
        // aleshores borrem aquesta entrada mig buida
        if ($query->num_rows() > 1) {
            $aux = $query->result();
            
            $text = $aux[1]->generatorString;
            $idsentence = $aux[0]->ID_SHistoric;
            
            if ($aux[0]->generatorString == null) {
                $this->db->where('ID_SHistoric', $idsentence);
                $this->db->delete('S_Historic');
            }
            
        }
        
        return $text;   
    }
    
    // Retorna l'estructura de la paraula Verbless o de qualsevol Verb amb els seus patterns
    function getPatternsVerb($verbid, $withsubject)
    {
        $output = array();
        $userlanguage = $this->session->userdata('ulangabbr');
        
        // només retorna patrons amb subjecte, és a dir, no impersonals
        if ($withsubject) $this->db->where('Pattern'.$userlanguage.'.subj !=', '0');
        
        $this->db->where_in('Pictograms.ID_PUser', array('1', $this->session->userdata('idusu')));
        $this->db->where('Verb'.$userlanguage.'.verbid', $verbid);
        $this->db->join('Pictograms', 'Pictograms.pictoid = Verb'.$userlanguage.'.verbid', 'left');
        $this->db->join('Pattern'.$userlanguage, 'Pattern'.$userlanguage.'.verbid = Verb'.$userlanguage.'.verbid', 'left');
        $this->db->order_by('Pictograms.pictoid', 'asc');
        $query2 = $this->db->get('Verb'.$userlanguage);
        
        if ($query2->num_rows() > 0) {
            $word = new Myword();
            $word->initSingleVerbWord($verbid, $query2->result());
            // ANTIGA LÍNIA SUPERIOR: $word->initSingleVerbWord($verbid, $query2->result(), -1, false);
            $output = $word;
        }
        else $output = null;
        
        return $output;
    }
    
    // conjuga el verb: retorna un string
    // ÉS ESPECÍFIC PER CATALÀ
    public function conjugar($verbid, $tense, $persona, $numero, $pronominal)
    {
        // DEBUG
        // echo "CONJ: ".$verbid." ".$tense." ".$persona." ".$numero."<br />";
        $tenseaux = null;
        $idverbaux = null;
        $tensemain = $tense;
        $personamain = $persona;
        $numeromain = $numero;
        
        $formafinal = "";
        $userlanguage = $this->session->userdata('ulangabbr');
        
        // si el verb és pronominal, a la taula de conjugacions ja ve amb el verb auxiliar i 
        // el pronom corresponent
        if (!$pronominal) {
        
            if ($tense == "perfet") {
                $tenseaux = "present";
                $tensemain = "participi";
                $personamain = 0;
                $numeromain = 'sing';
                $idverbaux = 101; // id del verb haver
            }
            else if ($tense == "perifrastic") {
                $tenseaux = "perifrastic";
                $tensemain = "infinitiu";
                $personamain = 0;
                $numeromain = 'sing';
                $idverbaux = 102; // id del verb anar quan fa d'auxiliar als perifràstics
            }
        
            if ($idverbaux != null) {
                $this->db->where('verbid', $idverbaux);
                $this->db->where('tense', $tenseaux);
                $this->db->where('pers', $persona);
                $this->db->where('singpl', $numero);
                $query2 = $this->db->get('VerbConjugation'.$userlanguage);
                if ($query2->num_rows() > 0) {
                    $aux = $query2->result();
                    $formafinal .= $aux[0]->verbconj." ";
                }
            }
        }
        // si és infinitiu, gerundi o participi, no té persona
        if ($tensemain == 'infinitiu' || $tensemain == 'gerundi' 
                || $tensemain == 'participi') {
            $personamain = 0;
            $numeromain = 'sing';
        }
        
        $this->db->where('verbid', $verbid);
        $this->db->where('tense', $tensemain);
        $this->db->where('pers', $personamain);
        $this->db->where('singpl', $numeromain);
        $query = $this->db->get('VerbConjugation'.$userlanguage);
        if ($query->num_rows() > 0) {
            $aux = $query->result();
            $formafinal .= $aux[0]->verbconj;
        }
        
        // el pronom de darrere l'infinitiu dels verbs pronominals ha de concordar amb el subjecte
        if ($tensemain == "infinitiu" && $pronominal) {
            $patterns[0] = '/-se/u';
            
            if ($persona == 1 && $numero == 'sing') $replacements[0] = "-me";
            else if ($persona == 2 && $numero == 'sing') $replacements[0] = "-te";
            else if ($persona == 3) $replacements[0] = "-se";
            else if ($persona == 1 && $numero == 'pl') $replacements[0] = "-nos";
            else if ($persona == 2 && $numero == 'pl') $replacements[0] = "-vos";
            
            $formafinal = preg_replace($patterns, $replacements, $formafinal);
        }
        
        return $formafinal;
    }
    
    // conjuga el verb: retorna un string
    // ÉS ESPECÍFIC PER CASTELLÀ
    public function conjugarES($verbid, $tense, $persona, $numero, $pronominal)
    {
        // DEBUG
        // echo "CONJ: ".$verbid." ".$tense." ".$persona." ".$numero."<br />";
        $tenseaux = null;
        $idverbaux = null;
        $tensemain = $tense;
        $personamain = $persona;
        $numeromain = $numero;
        
        $formafinal = "";
        $userlanguage = $this->session->userdata('ulangabbr');
        
        // si el verb és pronominal, a la taula de conjugacions ja ve amb el verb auxiliar i 
        // el pronom corresponent
        if (!$pronominal) {
        
            if ($tense == "perfet") {
                $tenseaux = "present";
                $tensemain = "participi";
                $personamain = 0;
                $numeromain = 'sing';
                $idverbaux = 101; // id del verb haver
            }
        
            if ($idverbaux != null) {
                $this->db->where('verbid', $idverbaux);
                $this->db->where('tense', $tenseaux);
                $this->db->where('pers', $persona);
                $this->db->where('singpl', $numero);
                $query2 = $this->db->get('VerbConjugation'.$userlanguage);
                if ($query2->num_rows() > 0) {
                    $aux = $query2->result();
                    $formafinal .= $aux[0]->verbconj." ";
                }
            }
        }
        // si és infinitiu, gerundi o participi, no té persona
        if ($tensemain == 'infinitiu' || $tensemain == 'gerundi' 
                || $tensemain == 'participi') {
            $personamain = 0;
            $numeromain = 'sing';
        }
        
        $this->db->where('verbid', $verbid);
        $this->db->where('tense', $tensemain);
        $this->db->where('pers', $personamain);
        $this->db->where('singpl', $numeromain);
        $query = $this->db->get('VerbConjugation'.$userlanguage);
        if ($query->num_rows() > 0) {
            $aux = $query->result();
            $formafinal .= $aux[0]->verbconj;
        }
        
        // el pronom de darrere l'infinitiu dels verbs pronominals ha de concordar amb el subjecte
        if ($tensemain == "infinitiu" && $pronominal) {
            $patterns[0] = '/se$/u';
            
            if ($persona == 1 && $numero == 'sing') $replacements[0] = "me";
            else if ($persona == 2 && $numero == 'sing') $replacements[0] = "te";
            else if ($persona == 3) $replacements[0] = "se";
            else if ($persona == 1 && $numero == 'pl') $replacements[0] = "nos";
            else if ($persona == 2 && $numero == 'pl') $replacements[0] = "os";
            
            $formafinal = preg_replace($patterns, $replacements, $formafinal);
        }
        
        return $formafinal;
    }
    
    public function guardarParseIFraseResultat($identry, $parsetree, $frasefinal)
    {
        if ($identry >= 0) {
          
            $data = array(
                'parsestring' => $parsetree,
                'generatorstring' => $frasefinal,
            );
            $this->db->where('ID_SHistoric', $identry);
            $this->db->update('S_Historic', $data);  
        }    
    }
    
    public function addEntryScores($identry, $scoreparser, $scoregen, $comments)
    {
        $parsescore = intval($scoreparser);
        $generatorscore = intval($scoregen);
        
        $data = array(
            'parsescore' => $parsescore,
            'generatorscore' => $generatorscore,
            'comments' => $comments,
        );
        $this->db->where('ID_SHistoric', $identry);
        $this->db->update('S_Historic', $data);  
    }
    
    
    /*
     * Inserts individually each pictogram in P_StatsUserPicto.
     * If this picto already exists increment count
     */
    function addStatsX1($paraulesFrase, $iduser) {
        for ($i = 0; $i < count($paraulesFrase); $i++) {
            if ($paraulesFrase[$i] != null) {//esto se podria quitar...
                $word = $paraulesFrase[$i];
                $inputid = $word->id;
                
                $this->addWordStatsX1($inputid, $iduser, false);
            }
        }
    }
    
    /*
     * Adds or updates the stats (countx1 and hour and day of the week) of the pictogram with pictoid
     * for the user with id=iduser. If first is set to true, it only
     * sets the counter to 1 if that user had never used that pictogram
     * before.
     */
    public function addWordStatsX1($pictoid, $iduser, $first) 
    {
        $datestring = "%Y/%m/%d";
        $daystring = 'D';
        $hourstring = 'G';
        $time = time();
        $avui = mdate($datestring, $time);
        $dia = date($daystring, $time);
        $hora = date($hourstring, $time);
        $hora .= "h";
        
        $this->db->where('pictoid', $pictoid);
        $this->db->where('ID_PSUPUser', $iduser);
        $query = $this->db->get('P_StatsUserPicto');
        if ($query->num_rows() > 0) {
            if (!$first) {
                $stat = $query->result();
                $num = $stat[0]->countx1 + 1;
                $numdia = $stat[0]->$dia + 1;
                $numhora = $stat[0]->$hora + 1;
                $this->db->where('pictoid', $pictoid);
                $this->db->where('ID_PSUPUser', $iduser);
                $data = array(
                    'countx1' => $num,
                    'lastdate' => $avui,
                    $dia => $numdia,
                    $hora => $numhora
                );
                $query = $this->db->update('P_StatsUserPicto', $data);
            }
        } else {
            $data = array(
                'countx1' => '1',
                'lastdate' => $avui,
                'pictoid' => $pictoid,
                'ID_PSUPUser' => $iduser
            );
            $query = $this->db->insert('P_StatsUserPicto', $data);
        }
    }
    
    /*
     * Inserts, in pairs, each pictogram in P_StatsUserPicto.
     * If this combination of pictograms already exists increment count
     */
    function addStatsX2($paraulesFrase, $iduser) {
        
        $datestring = "%Y/%m/%d";
        $daystring = 'D';
        $hourstring = 'G';
        $time = time();
        $avui = mdate($datestring, $time);
        $dia = date($daystring, $time);
        $hora = date($hourstring, $time);
        $hora .= "h";
        
        for ($i = 1; $i < count($paraulesFrase); $i++) {
            $word1 = $paraulesFrase[$i - 1];
            $word2 = $paraulesFrase[$i];
            $inputid1 = $word1->id;
            $inputid2 = $word2->id;
            $this->db->where('picto1id', $inputid1);
            $this->db->where('picto2id', $inputid2);
            $this->db->where('ID_PSUP2User', $iduser);
            $query = $this->db->get('P_StatsUserPictox2');
            if ($query->num_rows() > 0) {
                $stat = $query->result();
                $num = $stat[0]->countx2 + 1;
                $numdia = $stat[0]->$dia + 1;
                $numhora = $stat[0]->$hora + 1;
                $this->db->where('picto2id', $inputid2);
                $this->db->where('picto1id', $inputid1);
                $this->db->where('ID_PSUP2User', $iduser);
                $data = array(
                    'countx2' => $num,
                    'lastdate' => $avui,
                    $dia => $numdia,
                    $hora => $numhora
                );
                $query = $this->db->update('P_StatsUserPictox2', $data);
            } else {
                $data = array(
                    'countx2' => '1',
                    'lastdate' => $avui,
                    $dia => '1',
                    $hora => '1',
                    'picto2id' => $inputid2,
                    'picto1id' => $inputid1,
                    'ID_PSUP2User' => $iduser
                );
                $query = $this->db->insert('P_StatsUserPictox2', $data);
            }
        }
    }
    /*
     * Inserts, in trios, each pictogram in P_StatsUserPicto.
     * If this combination of pictograms already exists increment count
     */
    function addStatsX3($paraulesFrase, $iduser) {
        
        $datestring = "%Y/%m/%d";
        $daystring = 'D';
        $hourstring = 'G';
        $time = time();
        $avui = mdate($datestring, $time);
        $dia = date($daystring, $time);
        $hora = date($hourstring, $time);
        $hora .= "h";
        
        for ($i = 2; $i < count($paraulesFrase); $i++) {
            $word1 = $paraulesFrase[$i - 2];
            $word2 = $paraulesFrase[$i - 1];
            $word3 = $paraulesFrase[$i];
            $inputid1 = $word1->id;
            $inputid2 = $word2->id;
            $inputid3 = $word3->id;
            $this->db->where('picto1id', $inputid1);
            $this->db->where('picto2id', $inputid2);
            $this->db->where('picto3id', $inputid3);
            $this->db->where('ID_PSUP3User', $iduser);
            $query = $this->db->get('P_StatsUserPictox3');
            if ($query->num_rows() > 0) {
                $stat = $query->result();
                $num = $stat[0]->countx3 + 1;
                $numdia = $stat[0]->$dia + 1;
                $numhora = $stat[0]->$hora + 1;
                $this->db->where('picto3id', $inputid3);
                $this->db->where('picto2id', $inputid2);
                $this->db->where('picto1id', $inputid1);
                $this->db->where('ID_PSUP3User', $iduser);
                $data = array(
                    'countx3' => $num,
                    'lastdate' => $avui,
                    $dia => $numdia,
                    $hora => $numhora
                );
                $query = $this->db->update('P_StatsUserPictox3', $data);
            } else {
                $data = array(
                    'countx3' => '1',
                    'lastdate' => $avui,
                    $dia => '1',
                    $hora => '1',
                    'picto3id' => $inputid3,
                    'picto2id' => $inputid2,
                    'picto1id' => $inputid1,
                    'ID_PSUP3User' => $iduser
                );
                $query = $this->db->insert('P_StatsUserPictox3', $data);
            }
        }        
    }
    
    /*
    * Adds or updates the stats of the pictogram with pictoid
    * for the user with id=iduser. If first is set to true, it only
    * sets the counter to 1 if that user had never used that pictogram
    * before.
    */
   public function addImgTempStatsX1($pictoid, $iduser, $imgtemp) 
   {
       $this->db->where('pictoid', $pictoid);
       $this->db->where('ID_PSUPUser', $iduser);
       $query = $this->db->get('P_StatsUserPicto');
       
       if ($query->num_rows() > 0) {
            $this->db->where('pictoid', $pictoid);
            $this->db->where('ID_PSUPUser', $iduser);
            $data = array(
                'imgtemp' => $imgtemp
            );
            $query = $this->db->update('P_StatsUserPicto', $data);
       }
   }
    
}
?>