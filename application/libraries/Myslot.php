<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Myslot {
    
    
    // PROPIETATS
    var $category; // Tipus d'slot -> Theme, Subj... No té els 1|2 del final que tenen les keys de l'slotarray del pattern si hi ha subverbs
    var $grade; // Si és obligatori, opt o no pot ser-hi
    var $type = null; // Si vol qualsevol nom, joguines, adverbis, verbs... (si és obligatori o optatiu)
    var $defvalue = null; // Valor per defecte si és de grade obligatori
    var $prep = null; // Preposició que precedeix a l'slot
    var $art = null;
    var $defvalueused = false; // indica si l'slot s'ha omplert amb el def value
    var $verbless = false; // per quan no han introduït verb i s'afegeix després amb les columnes
                           // defaultverb o els verbless patterns
    
    var $full = false; // Si l'slot ja està ple/bloquejat
    var $paraulafinal = null; // Paraula que acaba omplint l'slot (classe Myword)
    var $puntsfinal; // punts segons com de bo és el fit de la paraula a l'slot
    var $indexclassfinalword = 0; // L'index de la classe per saber quina classe s'ha agafat si una paraula en tenia vàries 
    var $puntsguanyats = -1000;
    
    var $paraulestemp = array(); // Array de les paraules/myword [0], els seus punts [1] que poden omplir l'slot i l'index de la classe [2]
    
    var $level = 1; // Nivell on es troba l'slot, si és d'un slot de subverb serà 2
    var $parent; // Si l'slot és de nivell 2, aquí hi ha el nom de l'slot original que substitueixen
                 // en general el que era subverb
    
    var $complements = array(); // ARRAY de slots pels complements de nom (NC) que siguin noms.
    var $NCassigned = false;
    var $NCassignedkey = null;
    
    var $cmpAdjs = array(); // ARRAY de slots pels complements de nom (NC) que siguin adjectius.
    var $CAdjassigned = false;
    var $CAdjassignedkey = null;
    
    var $cmpAdvs = array(); // ARRAY de slots pels complements de nom (NC) que siguin adverbis (com ara pels de lloc, "davant" la taula).
    var $CAdvassigned = false;
    var $CAdvassignedkey = null;
    
    var $cmpMod = array(); // ARRAY de slots pels complements de nom (NC) que siguin modificadors (quantificadors...).
    var $CModassigned = false;
    var $CModassignedkey = array();
    
    /*
     * VARIABLES PEL GENERADOR
     */
    
    var $slotstring = array(); // array amb la representació escrita de l'slot on a cada 
                               // posició hi té una tupla amb [0] la forma escrita de la paraula
                               // i [1] una referència a la paraula (myWord) si no és una
                               // prep, un article,  conj... [2] si el nucli és un nom [3] si masc [4] si plural
                               // [5] si no necessita article -> quan hi ha un numero davant del nom o un quantificador
                               // [6] si el nom és un complement de nom [7] si el nucli té un possessiu, que l'article
                               // anirà davant del possessiu
    
    var $isInfinitive = false; // only for verbs. If after conjugating them, they are in infinitive form.
    
         
    function __construct() {}
    
    public function isFull()
    {
        return $this->full;
    }
        
    public function nounFitsSlot($word, $keyslot)
    {
        $CI = &get_instance();
        $CI->load->library('Mymatching');
        
        $matching = new Mymatching();
        
        $langnouncorder = $CI->session->userdata('uinterfacelangncorder');
        
        $numclasses = count($word->classes);
        
        $matchscore = 1000;
        $matchindexclass = -1;
        $output = 0;
        $isPronoun = false;
                       
        for ($i=0; $i<$numclasses; $i++) {
            
            if ($word->classes[$i] == "pronoun") $isPronoun = true;
            // comprovem que la classe de nom existeixi i que el type de l'slot sigui de nom
            if ($matching->isSetKeyNoun($word->classes[$i]) && $matching->isSetKeyNoun($this->type)) {
                $tipusx = $matching->nounsFitKeys[$this->type]; // agafem l'index del tipus de nom de l'slot
                $tipusy = $matching->nounsFitKeys[$word->classes[$i]];
                
                if ($matching->nounsFit[$tipusx][$tipusy] < $matchscore) {
                    $matchscore = $matching->nounsFit[$tipusx][$tipusy];
                    $matchindexclass = $i;
                }
                
            }
        }
                        
        // mirar si el nom pot fer de complement de nom d'algun dels noms que estiguin de provisionals a l'slot
        // Si és un slot de tipus nom i la paraula no és un pronom (que no poden fer de NC)
        if (!$isPronoun && $matching->isSetKeyNoun($this->type)) { // HO MIREM SEMPRE, JA QUE una paraula POT SER AL LLISTAT PROV DE L'SLOT I al llistat DE NC D'UN ALTRE NOM
            
            $i=0;
            $found = false;
            $numparaulestemp = count($this->paraulestemp); 
            
            // si l'slot està ja ple, mirem si pot fer de complement de nom i
            // si l'idioma posa després o abans del nom els NC i el posem
            // no es pot fer de complement de nom de pronoms
            if ($this->full) {
                if ((($langnouncorder == '1' && $this->paraulafinal->inputorder == $word->inputorder - 1) 
                        || ($langnouncorder == '0' && $this->paraulafinal->inputorder == $word->inputorder + 1)) && !$this->paraulafinal->isClass("pronoun")) {
                    $numcomplements = count($this->complements);
                                        
                    $newslot = new Myslot();
                    $word->slotstemps[] = $keyslot." NC ".$numcomplements; // per dir que està de compl. de nom
                    $newslot->category = $this->category." NC";
                    $newslot->grade = "opt";
                    $newslot->prep = "de";
                    $newslot->full = true;
                    $newslot->paraulafinal = $word;
                    $newslot->level = $this->level + 1;
                    $newslot->parent = $keyslot;
                    $newslot->puntsfinal = 7; // Són els punts que resten un NC sobre 100
                    $newslot->indexclassfinalword = 0;

                    $this->complements[$keyslot." NC ".$numcomplements] = $newslot;
                    $output = 1;
                }
            }
            // si està buit, mirem si pot fer de complement per totes les paraules que poden omplir l'slot
            else {
                while (($i<$numparaulestemp) && !$found) {

                    // mirem si pot fer de complement de nom i
                    // si l'idioma posa després o abans del nom els NC i el posem
                    // no es pot fer de complement de nom de pronoms
                    if ((($langnouncorder == '1' && $this->paraulestemp[$i][0]->inputorder == $word->inputorder - 1) 
                        || ($langnouncorder == '0' && $this->paraulestemp[$i][0]->inputorder == $word->inputorder + 1)) && !$this->paraulestemp[$i][0]->isClass("pronoun")) {
                        // POT FER EL MATCH I ES POSA A LA LLISTA DE COMPLEMENTS PROVISIONAL EN UN NOU SLOT
                        
                        $numcomplements = count($this->complements);

                        $newslot = new Myslot();
                        $word->slotstemps[] = $keyslot." NC ".$numcomplements; // per dir que està de compl. de nom
                        $newslot->category = $this->category." NC";
                        $newslot->grade = "opt";
                        $newslot->prep = "de";
                        $newslot->full = true;
                        $newslot->paraulafinal = $word;
                        $newslot->level = $this->level + 1;
                        $newslot->parent = $keyslot;
                        $newslot->puntsfinal = 7; // Són els punts que resten un NC sobre 100
                        $newslot->indexclassfinalword = 0;

                        $this->complements[$keyslot." NC ".$numcomplements] = $newslot;
                        $found = true;
                        $output = 1;
                    }
                    $i++;
                } // FI WHILE
            }
        }
        // si podia entrar-hi i l'slot no està ple
        if ($matchscore != 1000 && !$this->full) {
            // POSEM LA PARAULA AL LLISTAT DE FILL TEMPORAL
            $this->fillSlotTemp($word, $matchscore, $matchindexclass, $keyslot);
            $output = 1;
        }
        return $output;
    }
    
    public function adverbFitsSlot($word, $keyslot)
    {
        $CI = &get_instance();
        $CI->load->library('Mymatching');
                
        $matching = new Mymatching();
        
        $numclasses = count($word->classes);
        
        $matchscore = 1000;
        $matchindexclass = -1;
        $output = 0;
        $isadvlloc = false;
        
        for ($i=0; $i<$numclasses; $i++) {
                                                
            // comprovem que la classe d'adverbi existeixi i que el type de l'slot accepti adverbis
            if ($matching->isSetKeyAdv($word->classes[$i]) && $matching->isSetKeyAdv($this->type)) {
                                
                $tipusx = $matching->advQuantFitKeys[$this->type]; // agafem l'index del tipus d'adverbi de l'slot
                $tipusy = $matching->advQuantFitKeys[$word->classes[$i]];
                
                if ($matching->advQuantFit[$tipusx][$tipusy] < $matchscore) {
                    $matchscore = $matching->advQuantFit[$tipusx][$tipusy];
                    $matchindexclass = $i;
                }
                
            }
            if ($word->classes[$i] == "lloc") $isadvlloc = true;
        }
                
        // Fins aquí hem vist si la paraula podia anar a un slot
        // Si podia anar a un slot, l'slot està buit i l'slot no és de lloc fem un fill temporal normal
        if (($matchscore != 1000) && (!$this->full) && ($this->type != "lloc")) {
            $this->fillSlotTemp($word, $matchscore, $matchindexclass, $keyslot);
            $output = 1;
        }
        // Pels tots els slots plens amb noms, mirem com de bé fa de CAdv l'adverbi de lloc
        if ($this->full && $isadvlloc) {
            // només poden anar els adverbis com a complement del nom que està omplint l'slot
            // per tant l'slot ha d'estar ple
                $numcomplements = count($this->cmpAdvs);
                
                $noun = $this->paraulafinal;
                $matchscore = 1000;
                
                // busquem com de bé fa fit l'adverbi de lloc a la classe del nom
                for ($j=0; $j<count($noun->classes); $j++) {
                    $nounclassindex = $matching->nounsFitKeys[$noun->classes[$j]];
                    $matchscore = $matching->advLocNC[0][$nounclassindex];
                }
                
                // si no fa un mal fit, l'afegim al llistat de complements temporals
                if ($matchscore < 5) {
                    $newslot = new Myslot();
                    $word->slotstemps[] = $keyslot." ADV ".$numcomplements; // per dir que està de compl. de nom
                    $newslot->category = $this->category." ADV";
                    $newslot->grade = "opt";
                    $newslot->prep = "de";
                    $newslot->full = true;
                    $newslot->paraulafinal = $word;
                    $newslot->level = $this->level + 1;
                    $newslot->parent = $keyslot;
                    $newslot->puntsfinal = $matchscore; // Són els punts del fit amb l'slot de lloc
                                                // deixarien un slot opt buit
                    $newslot->indexclassfinalword = 0;

                    $this->cmpAdvs[$keyslot." ADV ".$numcomplements] = $newslot;
                    $output = 1;
                }
        }
                        
        return $output;
    }
    
    public function adjFitsSlot($word, $keyslot)
    {
        $CI = &get_instance();
        $CI->load->library('Mymatching');
        
        // we get the usual order of adjectives that complement nouns for the given user interface language
        $langnounadjorder = $CI->session->userdata('uinterfacelangnadjorder');
        
        $matching = new Mymatching();
        
        $numclasses = count($word->classes);
        
        $matchscore = 1000;
        $matchindexclass = -1;
        $output = 0;
        
        for ($i=0; $i<$numclasses; $i++) {
                                                            
            // comprovem que la classe d'adjectiu existeixi i que el type de l'slot accepti adjectius
            if ($matching->isSetKeyAdj($word->classes[$i]) && $matching->isSetKeyAdjSmall($this->type)) {

                $tipusx = $matching->adjFitKeys[$this->type]; // agafem l'index del tipus de l'slot
                $tipusy = $matching->adjFitKeys[$word->classes[$i]];
                
                if ($matching->adjFit[$tipusx][$tipusy] < $matchscore) {
                    $matchscore = $matching->adjFit[$tipusx][$tipusy];
                    $matchindexclass = $i;
                }
                
            }
        }

        // mirar si l'adjectiu pot fer de complement de nom del nom que estigui fent fill a l'slot
        if ($this->full && $this->paraulafinal->tipus == "name") { // HO MIREM SEMPRE, JA QUE una paraula POT SER AL LLISTAT PROV DE L'SLOT I al llistat DE NC D'UN ALTRE NOM
                      
                // POT FER EL MATCH I ES POSA A LA LLISTA DE COMPLEMENTS ADJ PROVISIONAL EN UN NOU SLOT
                    
                $numcomplements = count($this->cmpAdjs);

                $newslot = new Myslot();
                
                $newslot->category = $this->category." ADJ";
                $newslot->grade = "opt";
                $newslot->full = true;
                $newslot->paraulafinal = $word;
                $newslot->level = $this->level + 1;
                $newslot->parent = $keyslot;
                $word->slotstemps[] = $keyslot." ADJ ".$numcomplements;
                $scoreadjcmp = 1000;
                $indexclassadj = 0;
                
                $numclassesnom = count($this->paraulafinal->classes);
                for ($i=0; $i<$numclasses; $i++) {
                    $classeadjaux = $word->classes[$i];
                                        
                    for ($j=0; $j<$numclassesnom; $j++) {
                        
                        $classenomaux = $this->paraulafinal->classes[$j];
                        
                        if ($matching->isSetKeyAdjNoun($classeadjaux) && $matching->isSetKeyNoun($classenomaux)) {
                                                        
                            $tipusx = $matching->adjNounFitKeys[$classeadjaux];
                            $tipusy = $matching->nounsFitKeys[$classenomaux];
                                                        
                            if ($matching->adjNounFit[$tipusx][$tipusy] < $scoreadjcmp) {
                                $scoreadjcmp = $matching->adjNounFit[$tipusx][$tipusy];
                                $indexclassadj = $i;
                            }
                        }
                    }
                }
                
                // nom - adjectiu 
                $distance = $this->paraulafinal->inputorder - $word->inputorder;
                                
                // com més lluny i com menys fit facin, pitjor
                $newslot->puntsfinal = 7 - $scoreadjcmp - abs($distance);
                
                if ($langnounadjorder == '0' && $distance == -1) $newslot->puntsfinal += 1; // Si l'adjectiu va just darrere el nom és la millor opció
                if ($langnounadjorder == '1' && $distance == 1) $newslot->puntsfinal += 1; // Si l'adjectiu va just abans del nom és la millor opció
                
                $aux = array();
                $aux[0] = $keyslot." ADJ ".$numcomplements; // la clau de l'slot on pot fer de complement
                $aux[1] = $newslot->puntsfinal; // els punts d'aquest slot: és per desambiguar si un adj pot complementar a dues paraules, per escollir la millor
                
                $word->slotstempsext[] = $aux; // key de l'slot on e'adj fa de complement [0] i punts [1]
                
                $newslot->indexclassfinalword = $indexclassadj;

                $this->cmpAdjs[$keyslot." ADJ ".$numcomplements] = $newslot;
                $output = 1;                
        }
        
        // si havia fet fit d'un slot (no de complement)
        if ($matchscore != 1000) {
            // POSEM LA PARAULA AL LLISTAT DE FILL TEMPORAL
            $this->fillSlotTemp($word, $matchscore, $matchindexclass, $keyslot);
            $output = 1;
        }
        return $output;
    }
    
    
    public function modifFitsSlot($word, $keyslot)
    {
        $CI = &get_instance();
        $CI->load->library('Mymatching');
        
        $matching = new Mymatching();
        
        $numclasses = count($word->classes);
        
        $matchscore = 1000;
        $matchindexclass = -1;
        $output = 0;
                
        // com que els quant poden omplir slots on tb hi pot haver ja un adv, només volem que l'ompli
        // si no hi ha cap adv, es a dir, si està buit
        if (!$this->full) {
            
            for ($i=0; $i<$numclasses; $i++) {

                $classe = "modif";
                if ($word->classes[$i] == "quant") $classe = "quant";
                else if ($word->classes[$i] == "numero") $classe = "numero";
                
                // comprovem que la classe del modificador existeixi i que el type de l'slot accepti adjectius
                if ($matching->isSetKeyModif($classe) && $matching->isSetKeyModif($this->type)) {

                    $tipusx = $matching->modifFitKeys[$this->type]; // agafem l'index del tipus de l'slot
                    $tipusy = $matching->modifFitKeys[$classe];

                    if ($matching->modifFit[$tipusx][$tipusy] < $matchscore) {
                        $matchscore = $matching->modifFit[$tipusx][$tipusy];
                        $matchindexclass = $i;
                    }

                }
            }
        }
        
        // els quant i similar poden fer de complement d'advs, noms (si no són pronoms), adjs i modifs
        // els altres modificadors (possessius o determinants) de paraula i els números només van amb noms
        $potferdecmp = false;
        
        if ($this->full) {
            $tipusparaulafinal = $this->paraulafinal->tipus;
            if ($word->classes[0] == "quant" || $word->classes[0] == "similar") {
                if (($tipusparaulafinal == "name" 
                    || $tipusparaulafinal == "adv" || $tipusparaulafinal == "adj" 
                        || $tipusparaulafinal == "modifier") && !$this->paraulafinal->isClass("pronoun"))
                $potferdecmp = true;
            }
            else if ($tipusparaulafinal == "name") $potferdecmp = true;
            
        }
        
        if ($potferdecmp) {
                        
            $numcomplements = count($this->cmpMod);

            $newslot = new Myslot();
            $aux = array();
            $aux[0] = $keyslot." MOD ".$numcomplements; // la clau de l'slot on pot fer complement
            
            // nom - modificador 
            $distance = $this->paraulafinal->inputorder - $word->inputorder;

            // com més lluny pitjor
            $newslot->puntsfinal = 7 - abs($distance);
            
            // fem que els quantificadors amb els adjectius tinguin més punts que els slots opcionals
            // de manera que puguin tenir un quantificador com a fit (els opcionals valen 7 punts)
            if ($tipusparaulafinal == "adj" && $word->isClass("quant")) {
                if (abs($distance) == 1) {
                    $newslot->puntsfinal += 1;
                }
            }
            else if ($distance == 1) {
                $newslot->puntsfinal += 1; // Si el modficador va just abans de la paraula és la millor opció
                if ($word->isClass("quant") || $word->isClass("det") || 
                        $word->isClass("similar")) $newslot->puntsfinal += 1; // i si és un quant, det o similar li sumem un punt extra
            }
            
            $aux[1] = $newslot->puntsfinal; // els punts d'aquest slot: és per desambiguar si un modif pot complementar a dues paraules, per escollir la millor
            
            $word->slotstemps[] = $keyslot." MOD ".$numcomplements;
            $word->slotstempsext[] =  $aux; // key de l'slot on el modif fa de complement [0] i punts [1]
            $newslot->category = $this->category." MOD";
            $newslot->grade = "opt";
            $newslot->full = true;
            $newslot->paraulafinal = $word;
            $newslot->level = $this->level + 1;
            $newslot->parent = $keyslot;
            
            $newslot->indexclassfinalword = 0;

            $this->cmpMod[$keyslot." MOD ".$numcomplements] = $newslot;
            
            $output = 1; 
        }

        // si havia fet fit d'un slot (no de complement)
        if ($matchscore != 1000) {
            // POSEM LA PARAULA AL LLISTAT DE FILL TEMPORAL
            $this->fillSlotTemp($word, $matchscore, $matchindexclass, $keyslot);
            $output = 1;
        }
        return $output;
    }
    
    
    public function fillSlotTemp($word, $penalty, $classindexword, $keyslot) 
    {        
        $word->slotstemps[] = $keyslot;
        
        $punts = $this->slotPuntuation($word, $penalty);
                        
        $aux = array();
        $aux[0] = $word;
        $aux[1] = $punts;
        $aux[2] = $classindexword;
                
        if (($this->puntsfinal - $punts) > $this->puntsguanyats) {
            $this->puntsguanyats = $this->puntsfinal - $punts;
        }
        
        $this->paraulestemp[] = $aux;       
    }
    
    public function slotPuntuation ($word, $penalty)
    {
        
        $CI = &get_instance();
        
        // agafem el tipus d'idioma, ja que per desambiguar si l'idioma és svo
        // les paraules que vagin abans del verb tindran punts extra per fer de subjecte
        // i les que vagin darrere per fer dels altres slots
        $langtype = $CI->session->userdata('uinterfacelangtype');
        
        $svo = true;
        if ($langtype != 'svo') $svo = false;
        
        $punts = 0;
        if ($this->category == "Subject") {
            $punts = $penalty;
            
            if ($svo) {
                // si la paraula va abans del verb, el patró no era verbless, ni amb una pregunta 
                // (que el subjecte pot anar davant o darrere) i el fit no és horrible, li donem un bonus per 
                // igualar als altres camps obligatoris en l'ordre de prioritat
                // pel subjecte principal
                if ($this->level == 1 && $word->beforeverb && $penalty < 5 && !$this->verbless) $punts = $punts - 18 + $penalty*4;
                else if ($this->level == 1 && !$word->beforeverb && !$this->verbless && !$CI->session->userdata('preguntapattern')) $punts += 1;
                // pel secundari si n'hi ha
                if ($this->level == 2 && $word->beforeverb2 && $penalty < 5 && !$this->verbless) $punts = $punts - 18 + $penalty*4;
                else if ($this->level == 2 && !$word->beforeverb2 && !$this->verbless && !$CI->session->userdata('preguntapattern')) $punts += 1;
            }
            else {
                // igualem el grade del subjecte a slot obligatori si no era un fit terrible i no era verbless
                if ($this->level == 1 && $penalty < 5 && !$this->verbless) $punts = $punts - 18 + $penalty*4;
                // pel secundari si n'hi ha
                if ($this->level == 2 && $penalty < 5 && !$this->verbless) $punts = $punts - 18 + $penalty*4;
            }
        }
        else if ($this->category == "Main Verb") $punts = 0;
        else if ($this->grade == '1') {
            $punts = $penalty*5;
            // si l'idioma és svo, el que va abans del verb perd punts si estava introduït abans del verb
            if ($svo) {
                if ($this->level == 1 && $word->beforeverb) $punts += 1;
                else if ($this->level == 2 && ($word->beforeverb || $word->beforeverb2)) $punts += 1;
            }
            // els adjs que acompanyen a noms quan estan enganxats a ells (darrere en idiomes nadj i davant en idiomes
            // adjn) tenen un punt extra per fer de complement, per compensar-ho els perfect fits d'adj a slots de
            // tipus adj han de tenir un punt extra 
            if ($penalty == 0 && $this->type == "adj") $punts -= 1;
        }
        else if ($this->grade == 'opt') $punts = $penalty;
        else $punts = 7; // serà un de NC, tot i que aquests ja es tracten abans i en prinicipi no
                         // arriben aquí
        // DEBUG: echo $this->category." ".$this->level." --> ".$word->text." = ".$punts.'/'.$penalty.'<br /><br />';
                
        return $punts;
    }
    
    public function slotPuntsInicials()
    {
        if ($this->category == "Subject") $this->puntsfinal = 7;
        else if ($this->category == "Main Verb") $this->puntsfinal = 0;
        else if ($this->grade == '1') $this->puntsfinal = 25;
        else if ($this->grade == 'opt') $this->puntsfinal = 7;
        else $this->puntsfinal = 1;
    }

    // retorna l'index d'on es troba la paraula dins de les paraulestemp que poden fill l'slot
    public function searchIndexWordInSlot($word)
    {
        $index = -1;
        
        $found = false;
        
        $i=0;
        
        while ($i<count($this->paraulestemp) && !$found) {
            
            // l'inputorder és necessari per si hi ha dues paraules iguals que poden fer fill a l'slot
            // per exemple, dos subjecte "jo" quan hi ha més d'un verb a la frase
            if (($word->id == $this->paraulestemp[$i][0]->id) && ($word->tipus == $this->paraulestemp[$i][0]->tipus)
                    && ($word->inputorder == $this->paraulestemp[$i][0]->inputorder)) {
                $index = $i;
                $found = true;
            }
            $i++;
        }
        
        return $index;
    }
    
    public function slotCalcPuntsGuanyats()
    {
        $puntsaux = -1000;
        
        $puntsinicialsaux;
        
        if ($this->category == "Subject") $puntsinicialsaux = 7;
        else if ($this->category == "Main Verb") $puntsinicialsaux = 0;
        else if ($this->grade == '1') $puntsinicialsaux = 25;
        else if ($this->grade == 'opt') $puntsinicialsaux = 7;
        else $puntsinicialsaux = 1;
        
        for($i=0; $i<count($this->paraulestemp); $i++) {
            
            if (!$this->paraulestemp[$i][0]->used) {
                $penalty = $this->paraulestemp[$i][1];

                if (($puntsinicialsaux - $penalty) > $puntsaux) {
                    $puntsaux = $puntsinicialsaux - $penalty;
                }
            }
        }
        
        // $this->puntsguanyats = $puntsaux;
        
        return $puntsaux;
    }
    
    
    /*
     * FUNCIONS PEL GENERADOR
     */
    
    // ordena els slots internament i fa concordar els elements
    public function ordenarSlot($subjmasc, $subjpl, $copulatiu, $impersonal)
    {
        $nucli = $this->paraulafinal;
        $elementaux = array();
        
        if ($nucli != null) {
            
            switch ($nucli->tipus) {
                case "name":
                    
                    $numeros = array();
                    $quantifieradj = array(); // stores the quantifiers that complement the adj instead of the nucleus
                    $hasquantifieradj = false;
                    $hasnumorquant = false;
                    $haspossessive = 0; // al nostre sistema només els nuclis poden tenir possessius
                                            // també l'activarem si té ordinals o altres elements que van
                                            // entre l'article i el nom
                    
                    // calculem els valors per la concordància general
                    $masc = true;
                    $plural = false;
                    
                    // si el verb és copulatiu (amb un patró no impersonal) el nom del theme concorda amb el subjecte
                    if ($this->category == "Theme" && $copulatiu && !$impersonal) {
                        
                        // si el nom és femení
                        if ($nucli->propietats->mf == "fem") $masc = false;
                        // si el nom és plural
                        if ($nucli->propietats->singpl == "pl") $plural = true;
                        // si té modificadors de femení i l'accepta
                        if ($nucli->propietats->femeni != "" && $nucli->fem) $masc = false;
                        // si té modificador de plural
                        if ($nucli->plural) $plural = true;
                        
                        
                        // sobreescrivim els valors si cal
                        if (!$subjmasc && $subjpl) {
                            if ($nucli->propietats->femeni != "") {
                                $masc = false;
                            }
                            $plural = true;
                        }
                        else if (!$subjmasc && !$subjpl && $nucli->propietats->femeni != "") {
                            $masc = false;
                        }
                        else if ($subjmasc && $subjpl) $plural = true;
                    }
                    else {
                        // si el nom és femení
                        if ($nucli->propietats->mf == "fem") $masc = false;
                        // si el nom és plural
                        if ($nucli->propietats->singpl == "pl") $plural = true;
                        // si té modificadors de femení i l'accepta
                        if ($nucli->propietats->femeni != "" && $nucli->fem) $masc = false;
                        // si té modificador de plural
                        if ($nucli->plural) $plural = true;
                    }
                    
                    // PREPOSICIÓ
                    // posem la preposició, si n'hi ha
                    if ($this->prep != null) {
                            $elementaux[0] = $this->prep;
                            $elementaux[1] = null;
                            $this->slotstring[] = $elementaux;                        
                    }
                    
                    // Posem, si cal, la preposició "a", davant dels slots de temps
                    if ($this->category == "Time") {
                        $CI = &get_instance();
                        $CI->load->library('Mymatching');
                        
                        $matching = new Mymatching();
                        
                        if ($matching->isTimePrep($nucli->text)) {
                            $elementaux[0] = $matching->tempsPrep[$nucli->text];
                            $elementaux[1] = null;
                            $this->slotstring[] = $elementaux;
                        }
                    }
                    
                    // QUANTIFICADOR O POSSESSIUS
                    // si el nom té un modificador, que només pot ser un quantificador o possessiu
                    if ($this->CModassigned) {
                        
                        for ($i=0; $i<count($this->CModassignedkey); $i++) {
                            
                            $quantifierslot = $this->cmpMod[$this->CModassignedkey[$i]];
                            // si és un número
                            if ($quantifierslot->paraulafinal->isClass("numero")) {
                                $numeros[] = $quantifierslot;
                                // si és un número diferent d'1, tot passarà a ser plural
                                if ($quantifierslot->paraulafinal->text != "un") $plural = true;
                            }
                            // si és un quantificador o possessiu
                            else {
                                // si el nucli també té un adjectiu assignat, mirem si va millor amb l'adjectiu
                                if ($this->CAdjassigned) {
                                    // si és quantifier i anava just davant de l'adjectiu, el guardem per després
                                    if ($quantifierslot->paraulafinal->inputorder - 
                                            $this->cmpAdjs[$this->CAdjassignedkey]->paraulafinal->inputorder == -1
                                            && $quantifierslot->paraulafinal->classes[0] == "quant") {
                                        $quantifieradj[] = $quantifierslot;
                                        $hasquantifieradj = true;
                                    }
                                    // si no, el tractem com un quantificador o possessiu normal
                                    else {
                                        if ($masc && !$plural) $elementaux[0] = $quantifierslot->paraulafinal->propietats->masc;
                                        else if ($masc && $plural) $elementaux[0] = $quantifierslot->paraulafinal->propietats->mascpl;
                                        else if (!$masc && !$plural) $elementaux[0] = $quantifierslot->paraulafinal->propietats->fem;
                                        else $elementaux[0] = $quantifierslot->paraulafinal->propietats->fempl;
                                        $elementaux[1] = $quantifierslot->paraulafinal;

                                        $this->slotstring[] = $elementaux;
                                        $hasnumorquant = true;
                                        if (strpos($quantifierslot->paraulafinal->classes[0], "pos") === 0) {
                                            $haspossessive++; 
                                            $hasnumorquant = false;
                                        }
                                        // el nom precedit per semblant a portarà article
                                        if (strpos($quantifierslot->paraulafinal->text, "semblant")) $hasnumorquant = false;
                                    }
                                }
                                else {
                                    if ($masc && !$plural) $elementaux[0] = $quantifierslot->paraulafinal->propietats->masc;
                                    else if ($masc && $plural) $elementaux[0] = $quantifierslot->paraulafinal->propietats->mascpl;
                                    else if (!$masc && !$plural) $elementaux[0] = $quantifierslot->paraulafinal->propietats->fem;
                                    else $elementaux[0] = $quantifierslot->paraulafinal->propietats->fempl;
                                    $elementaux[1] = $quantifierslot->paraulafinal;

                                    $this->slotstring[] = $elementaux;
                                    $hasnumorquant = true;
                                    if (strpos($quantifierslot->paraulafinal->classes[0], "pos") === 0) {
                                        $haspossessive++; 
                                        $hasnumorquant = false;
                                    }
                                    // el nom precedit per semblant a portarà article
                                    if (strpos($quantifierslot->paraulafinal->text, "semblant")) $hasnumorquant = false;
                                }
                            }
                        }                        
                    }
                    
                    // ADVERBI DE LLOC
                    if ($this->CAdvassigned) {
                        
                        $adverbistring = $this->cmpAdvs[$this->CAdvassignedkey]->paraulafinal;
                        // dividir-lo en cada una de les paraules que formen l'adverbi
                        $auxstring = explode(" ", $adverbistring->text);
                        // afegir cada paraula en ordre a slotstring
                        for ($i=0; $i<count($auxstring); $i++) {
                            $elementaux[0] = $auxstring[$i];
                            $elementaux[1] = $adverbistring;
                            
                            $this->slotstring[] = $elementaux;
                        }
                    }
                    
                    // NUMEROS
                    for ($i=0; $i<count($numeros); $i++) {
                        if ($masc) $elementaux[0] = $numeros[$i]->paraulafinal->propietats->masc;
                        else $elementaux[0] = $numeros[$i]->paraulafinal->propietats->fem;
                        $elementaux[1] = $numeros[$i]->paraulafinal;
                        
                        $this->slotstring[] = $elementaux;
                        $hasnumorquant = true;
                    }
                    
                    // ORDINALS
                    if ($this->CAdjassigned) {
                        $adjectiu = $this->cmpAdjs[$this->CAdjassignedkey]->paraulafinal;
                        if ($adjectiu->isClass("ordinal")) {
                            if ($masc && !$plural) $elementaux[0] = $adjectiu->propietats->masc;
                            else if ($masc && $plural) $elementaux[0] = $adjectiu->propietats->mascpl;
                            else if (!$masc && !$plural) $elementaux[0] = $adjectiu->propietats->fem;
                            else $elementaux[0] = $adjectiu->propietats->fempl;
                            $elementaux[1] = $adjectiu;

                            $this->slotstring[] = $elementaux;
                            $haspossessive++;
                            $this->CAdjassigned = false; // indiquem que ja s'ha vist l'adjectiu
                        }
                    }
                    
                    // NOM + COORDINACIÓ
                    if ($plural) {
                        if ($nucli->propietats->mf != "fem" && !$masc) $elementaux[0] = $nucli->propietats->fempl;
                        else $elementaux[0] = $nucli->propietats->plural;
                    }
                    else if ($masc && !$plural) $elementaux[0] = $nucli->propietats->nomtext;
                    else {
                        if ($nucli->propietats->mf == "fem") $elementaux[0] = $nucli->propietats->nomtext;
                        else $elementaux[0] = $nucli->propietats->femeni;
                    }
                    $elementaux[1] = $nucli;
                    // com que el nucli és un nom, afegim la informació extra
                    $elementaux[2] = true;
                    $elementaux[3] = $masc;
                    $elementaux[4] = $plural;
                    $elementaux[5] = $hasnumorquant;
                    $elementaux[6] = false;
                    $elementaux[7] = $haspossessive;
                    
                    $this->slotstring[] = $elementaux;
                    
                    // variables auxiliars que ens ajudaran a concordar l'adjectiu amb 
                    // l'últim nom coordinat, si el nucli no té cap element coordinat, 
                    // tenen els valors per concordar amb el nucli
                    $masccoord = $masc;
                    $pluralcoord = $plural;
                    
                    // si té element coordinat
                    if (count($nucli->paraulacoord) > 0) {
                        
                        for ($k=0; $k<count($nucli->paraulacoord); $k++) {
                            
                            $paraulacoord = $nucli->paraulacoord[$k];
                            
                            $paraulacoordisadj = false;
                            if ($paraulacoord->tipus == "adj") $paraulacoordisadj = true;

                            // afegim la "i"
                            $elementaux[0] = "i";
                            $elementaux[1] = null;
                            $this->slotstring[] = $elementaux;
                            
                            if ($paraulacoordisadj) {
                                $elementaux[0] = $paraulacoord->propietats->masc;
                                $elementaux[1] = $paraulacoord;
                                $this->slotstring[] = $elementaux;
                            }
                            else {
                                // afegim la paraula coordinada, el plural es passa, però el femení
                                // s'ha de mirar si la paraula ho era o no el tenia el modificador
                                $masccoord = true;
                                $pluralcoord = false;

                                if ($paraulacoord->propietats->mf == "fem" || $paraulacoord->fem) $masccoord = false;
                                // el plural només pot canviar si plural era false i la paraulacoord sempre és plural
                                // que aleshores ha de passar a true o si volíem que la paraulacoord fos plural
                                if ($paraulacoord->propietats->singpl == "pl" || $paraulacoord->plural) $pluralcoord = true;

                                if ($pluralcoord) $elementaux[0] = $paraulacoord->propietats->plural;
                                else if ($masccoord && !$pluralcoord) $elementaux[0] = $paraulacoord->propietats->nomtext;
                                else {
                                    if ($paraulacoord->propietats->mf == "fem") $elementaux[0] = $paraulacoord->propietats->nomtext;
                                    else $elementaux[0] = $paraulacoord->propietats->femeni;
                                }
                                $elementaux[1] = $paraulacoord;
                                // com que la paraula coordinada ha de ser un nom, afegim la info extra
                                $elementaux[2] = true;
                                $elementaux[3] = $masccoord;
                                $elementaux[4] = $pluralcoord;
                                $elementaux[5] = $hasnumorquant;
                                $elementaux[6] = false;
                                $elementaux[7] = $haspossessive;

                                $this->slotstring[] = $elementaux;
                            }
                        }
                    }
                    
                    // ADJECTIUS I COMPLEMENTS DE NOM
                    // si té un adjectiu i no un complement de nom
                    if ($this->CAdjassigned && !$this->NCassigned) {
                        
                        // si l'adjectiu tenia un quantificador l'afegim
                        if ($hasquantifieradj) {
                            for ($i=0; $i<count($quantifieradj); $i++) {
                                // aquí els quantificadors tenen forma invariable
                                $elementaux[0] = $quantifieradj[$i]->paraulafinal->propietats->masc;
                                $elementaux[1] = $quantifieradj[$i]->paraulafinal;
                                
                                $this->slotstring[] = $elementaux;
                            }
                        }
                        // tant si tenia quantificador com si no, posem l'adjectiu
                        $adjectiu = $this->cmpAdjs[$this->CAdjassignedkey]->paraulafinal;
                        if ($masccoord && !$pluralcoord) $elementaux[0] = $adjectiu->propietats->masc;
                        else if ($masccoord && $pluralcoord) $elementaux[0] = $adjectiu->propietats->mascpl;
                        else if (!$masccoord && !$pluralcoord) $elementaux[0] = $adjectiu->propietats->fem;
                        else $elementaux[0] = $adjectiu->propietats->fempl;
                        $elementaux[1] = $adjectiu;
                        
                        $this->slotstring[] = $elementaux;
                        
                        if (count($adjectiu->paraulacoord) > 0) {
                        
                            for ($k=0; $k<count($adjectiu->paraulacoord); $k++) {

                                $paraulacoord = $adjectiu->paraulacoord[$k];
                                
                                // afegim la "i"
                                $elementaux[0] = "i";
                                $elementaux[1] = null;
                                $this->slotstring[] = $elementaux;

                                // afegim la paraula coordinada amb la mateixa concordància
                                if ($masccoord && !$pluralcoord) $elementaux[0] = $paraulacoord->propietats->masc;
                                else if ($masccoord && $pluralcoord) $elementaux[0] = $paraulacoord->propietats->mascpl;
                                else if (!$masccoord && !$pluralcoord) $elementaux[0] = $paraulacoord->propietats->fem;
                                else $elementaux[0] = $paraulacoord->propietats->fempl;
                                $elementaux[1] = $paraulacoord;

                                $this->slotstring[] = $elementaux;
                            
                            }     
                        }
                    }
                    // si té complement de nom i no adjectiu
                    else if ($this->NCassigned && !$this->CAdjassigned) {
                        
                        $nouncmpslot = $this->complements[$this->NCassignedkey];
                        $nouncmp = $nouncmpslot->paraulafinal;
                        
                        // afegim la preposició "de"
                        $elementaux[0] = $nouncmpslot->prep;
                        $elementaux[1] = null;
                        $this->slotstring[] = $elementaux;
                        
                        // afegim el nom
                        $masccmp = true;
                        $pluralcmp = false;
                        // si el nom és femení
                        if ($nouncmp->propietats->mf == "fem") $masccmp = false;
                        // si el nom és plural
                        if ($nouncmp->propietats->singpl == "pl") $pluralcmp = true;
                        // si té modificadors de femení i l'accepta
                        if ($nouncmp->propietats->femeni != "" && $nouncmp->fem) $masccmp = false;
                        // si té modificador de plural
                        if ($nouncmp->plural) $pluralcmp = true;
                        
                        if ($pluralcmp) $elementaux[0] = $nouncmp->propietats->plural;
                        else if ($masccmp && !$pluralcmp) $elementaux[0] = $nouncmp->propietats->nomtext;
                        else {
                            if ($nouncmp->propietats->mf == "fem") $elementaux[0] = $nouncmp->propietats->nomtext;
                            else $elementaux[0] = $nouncmp->propietats->femeni;
                        }
                        $elementaux[1] = $nouncmp;
                        // com que el nucli és un nom, afegim la informació extra
                        $elementaux[2] = true;
                        $elementaux[3] = $masccmp;
                        $elementaux[4] = $pluralcmp;
                        $elementaux[5] = false;
                        $elementaux[6] = true;
                        $elementaux[7] = $haspossessive;
                        
                        $this->slotstring[] = $elementaux;
                        
                        $masccmpcoord = $masccmp;
                        $pluralcmpcoord = $pluralcmp;
                        
                        // si té element coordinat
                        if (count($nouncmp->paraulacoord) > 0) {
                        
                            for ($k=0; $k<count($nouncmp->paraulacoord); $k++) {

                                $paraulacoord = $nouncmp->paraulacoord[$k];
                                
                                $paraulacoordisadj = false;
                                if ($paraulacoord->tipus == "adj") $paraulacoordisadj = true;

                                // afegim la "i"
                                $elementaux[0] = "i";
                                $elementaux[1] = null;
                                $this->slotstring[] = $elementaux;

                                if ($paraulacoordisadj) {
                                    $elementaux[0] = $paraulacoord->propietats->masc;
                                    $elementaux[1] = $paraulacoord;
                                    $this->slotstring[] = $elementaux;
                                }
                                else {
                                    // afegim la paraula coordinada amb la seva concordància
                                    $masccmpcoord = true;
                                    $pluralcmpcoord = false;
                                    // si el nom és femení
                                    if ($paraulacoord->propietats->mf == "fem") $masccmpcoord = false;
                                    // si el nom és plural
                                    if ($paraulacoord->propietats->singpl == "pl") $pluralcmpcoord = true;
                                    // si té modificadors de femení i l'accepta
                                    if ($paraulacoord->propietats->femeni != "" && $paraulacoord->fem) $masccmpcoord = false;
                                    // si té modificador de plural
                                    if ($paraulacoord->plural) $pluralcmpcoord = true;

                                    if ($pluralcmpcoord) $elementaux[0] = $paraulacoord->propietats->plural;
                                    else if ($masccmpcoord && !$pluralcmpcoord) $elementaux[0] = $paraulacoord->propietats->nomtext;
                                    else {
                                        if ($paraulacoord->propietats->mf == "fem") $elementaux[0] = $paraulacoord->propietats->nomtext;
                                        else $elementaux[0] = $paraulacoord->propietats->femeni;
                                    }
                                    $elementaux[1] = $paraulacoord;
                                    // com que el nucli és un nom, afegim la informació extra
                                    $elementaux[2] = true;
                                    $elementaux[3] = $masccmpcoord;
                                    $elementaux[4] = $pluralcmpcoord;
                                    $elementaux[5] = false;
                                    $elementaux[6] = true;
                                    $elementaux[7] = $haspossessive;

                                    $this->slotstring[] = $elementaux;
                                }
                            }
                        }
                    }
                    // si té complement de nom i adjectiu
                    else if ($this->NCassigned && $this->CAdjassigned) {
                        $nouncmpslot = $this->complements[$this->NCassignedkey];
                        $adjectiuslot = $this->cmpAdjs[$this->CAdjassignedkey];
                        $puntsFitAdjWithNucli = $adjectiuslot->puntsfinal;
                                                
                        // calculem els punts que tindria fent fit al complement
                        
                        $CI = &get_instance();
                        $CI->load->library('Mymatching');
                        
                        $matching = new Mymatching();
                        
                        $classeadj = $adjectiuslot->paraulafinal->classes[$adjectiuslot->indexclassfinalword];
                        
                        $numclassesnomcmp = count($nouncmpslot->paraulafinal->classes);
                        
                        $puntsFitAdjWithCMP = 1000;

                        for ($j=0; $j<$numclassesnomcmp; $j++) {

                            $classenomaux = $nouncmpslot->paraulafinal->classes[$j];

                            if ($matching->isSetKeyAdjNoun($classeadj) && $matching->isSetKeyNoun($classenomaux)) {
                                
                                $tipusx = $matching->adjNounFitKeys[$classeadj];
                                $tipusy = $matching->nounsFitKeys[$classenomaux];

                                if ($matching->adjNounFit[$tipusx][$tipusy] < $puntsFitAdjWithCMP) {
                                    $puntsFitAdjWithCMP = $matching->adjNounFit[$tipusx][$tipusy];
                                }
                            }
                        }
                        // nom - adjectiu 
                        $distance = $nouncmpslot->paraulafinal->inputorder - $adjectiuslot->paraulafinal->inputorder;
                        // com més lluny i com menys fit facin, pitjor
                        $puntsFitAdjWithCMP = 7 - $puntsFitAdjWithCMP - abs($distance);
                        if ($distance == -1) $puntsFitAdjWithCMP += 1;
                                                
                        // si fa millor fit al NUCLI
                        if ($puntsFitAdjWithNucli >= $puntsFitAdjWithCMP) {
                            
                            // insertem primer l'adjectiu
                            // si l'adjectiu tenia un quantificador l'afegim
                            if ($hasquantifieradj) {
                                for ($i=0; $i<count($quantifieradj); $i++) {
                                    // aquí els quantificadors tenen forma invariable
                                    $elementaux[0] = $quantifieradj[$i]->paraulafinal->propietats->masc;
                                    $elementaux[1] = $quantifieradj[$i]->paraulafinal;

                                    $this->slotstring[] = $elementaux;
                                }
                            }
                            // tant si tenia quantificador com si no, posem l'adjectiu
                            $adjectiu = $adjectiuslot->paraulafinal;
                            if ($masccoord && !$pluralcoord) $elementaux[0] = $adjectiu->propietats->masc;
                            else if ($masccoord && $pluralcoord) $elementaux[0] = $adjectiu->propietats->mascpl;
                            else if (!$masccoord && !$pluralcoord) $elementaux[0] = $adjectiu->propietats->fem;
                            else $elementaux[0] = $adjectiu->propietats->fempl;
                            $elementaux[1] = $adjectiu;

                            $this->slotstring[] = $elementaux;
                            
                            // si té element adjectiu coordinat l'afegim
                            if (count($adjectiu->paraulacoord) > 0) {

                                for ($k=0; $k<count($adjectiu->paraulacoord); $k++) {

                                    $paraulacoord = $adjectiu->paraulacoord[$k];
                                    
                                    // afegim la "i"
                                    $elementaux[0] = "i";
                                    $elementaux[1] = null;
                                    $this->slotstring[] = $elementaux;

                                    // afegim la paraula coordinada amb la mateixa concordància
                                    if ($masccoord && !$pluralcoord) $elementaux[0] = $paraulacoord->propietats->masc;
                                    else if ($masccoord && $pluralcoord) $elementaux[0] = $paraulacoord->propietats->mascpl;
                                    else if (!$masccoord && !$pluralcoord) $elementaux[0] = $paraulacoord->propietats->fem;
                                    else $elementaux[0] = $paraulacoord->propietats->fempl;
                                    $elementaux[1] = $paraulacoord;

                                    $this->slotstring[] = $elementaux;
                                    
                                }   
                            }

                            // després insertem el nom que fa de complement
                            $nouncmp = $nouncmpslot->paraulafinal;

                            // afegim la preposició "de"
                            $elementaux[0] = $nouncmpslot->prep;
                            $elementaux[1] = null;
                            $this->slotstring[] = $elementaux;

                            // afegim el nom
                            $masccmp = true;
                            $pluralcmp = false;
                            // si el nom és femení
                            if ($nouncmp->propietats->mf == "fem") $masccmp = false;
                            // si el nom és plural
                            if ($nouncmp->propietats->singpl == "pl") $pluralcmp = true;
                            // si té modificadors de femení i l'accepta
                            if ($nouncmp->propietats->femeni != "" && $nucli->fem) $masccmp = false;
                            // si té modificador de plural
                            if ($nouncmp->plural) $pluralcmp = true;

                            if ($pluralcmp) $elementaux[0] = $nouncmp->propietats->plural;
                            else if ($masccmp && !$pluralcmp) $elementaux[0] = $nouncmp->propietats->nomtext;
                            else {
                                if ($nouncmp->propietats->mf == "fem") $elementaux[0] = $nouncmp->propietats->nomtext;
                                else $elementaux[0] = $nouncmp->propietats->femeni;
                            }
                            $elementaux[1] = $nouncmp;
                            // com que el nucli és un nom, afegim la informació extra
                            $elementaux[2] = true;
                            $elementaux[3] = $masccmp;
                            $elementaux[4] = $pluralcmp;
                            $elementaux[5] = false;
                            $elementaux[6] = true;
                            $elementaux[7] = $haspossessive;
                            
                            $this->slotstring[] = $elementaux;
                            
                            // si té element coordinat
                            if (count($nouncmp->paraulacoord) > 0) {

                                for ($k=0; $k<count($nouncmp->paraulacoord); $k++) {

                                    $paraulacoord = $nouncmp->paraulacoord[$k];
                                    
                                    $paraulacoordisadj = false;
                                    if ($paraulacoord->tipus == "adj") $paraulacoordisadj = true;

                                    // afegim la "i"
                                    $elementaux[0] = "i";
                                    $elementaux[1] = null;
                                    $this->slotstring[] = $elementaux;

                                    if ($paraulacoordisadj) {
                                        $elementaux[0] = $paraulacoord->propietats->masc;
                                        $elementaux[1] = $paraulacoord;
                                        $this->slotstring[] = $elementaux;
                                    }
                                    else {
                                        // afegim la paraula coordinada amb la seva concordància
                                        $masccmp = true;
                                        $pluralcmp = false;
                                        // si el nom és femení
                                        if ($paraulacoord->propietats->mf == "fem") $masccmp = false;
                                        // si el nom és plural
                                        if ($paraulacoord->propietats->singpl == "pl") $pluralcmp = true;
                                        // si té modificadors de femení i l'accepta
                                        if ($paraulacoord->propietats->femeni != "" && $paraulacoord->fem) $masccmp = false;
                                        // si té modificador de plural
                                        if ($paraulacoord->plural) $pluralcmp = true;

                                        if ($pluralcmp) $elementaux[0] = $paraulacoord->propietats->plural;
                                        else if ($masccmp && !$pluralcmp) $elementaux[0] = $paraulacoord->propietats->nomtext;
                                        else {
                                            if ($paraulacoord->propietats->mf == "fem") $elementaux[0] = $paraulacoord->propietats->nomtext;
                                            else $elementaux[0] = $paraulacoord->propietats->femeni;
                                        }
                                        $elementaux[1] = $paraulacoord;
                                        // com que el nucli és un nom, afegim la informació extra
                                        $elementaux[2] = true;
                                        $elementaux[3] = $masccmp;
                                        $elementaux[4] = $pluralcmp;
                                        $elementaux[5] = false;
                                        $elementaux[6] = true;
                                        $elementaux[7] = $haspossessive;

                                        $this->slotstring[] = $elementaux;
                                    }
                                }
                            }
                            
                        }
                        // si fa millor fit al COMPLEMENT
                        else {
                            
                            // primer insertem el nom que fa de complement
                            $nouncmp = $nouncmpslot->paraulafinal;

                            // afegim la preposició "de"
                            $elementaux[0] = $nouncmpslot->prep;
                            $elementaux[1] = null;
                            $this->slotstring[] = $elementaux;

                            // afegim el nom
                            $masccmp = true;
                            $pluralcmp = false;
                            // si el nom és femení
                            if ($nouncmp->propietats->mf == "fem") $masccmp = false;
                            // si el nom és plural
                            if ($nouncmp->propietats->singpl == "pl") $pluralcmp = true;
                            // si té modificadors de femení i l'accepta
                            if ($nouncmp->propietats->femeni != "" && $nucli->fem) $masccmp = false;
                            // si té modificador de plural
                            if ($nouncmp->plural) $pluralcmp = true;

                            if ($pluralcmp) $elementaux[0] = $nouncmp->propietats->plural;
                            else if ($masccmp && !$pluralcmp) $elementaux[0] = $nouncmp->propietats->nomtext;
                            else {
                                if ($nouncmp->propietats->mf == "fem") $elementaux[0] = $nouncmp->propietats->nomtext;
                                else $elementaux[0] = $nouncmp->propietats->femeni;
                            }
                            $elementaux[1] = $nouncmp;
                            // com que el nucli és un nom, afegim la informació extra
                            $elementaux[2] = true;
                            $elementaux[3] = $masccmp;
                            $elementaux[4] = $pluralcmp;
                            $elementaux[5] = false;
                            $elementaux[6] = true;
                            $elementaux[7] = $haspossessive;
                            
                            $this->slotstring[] = $elementaux;
                            
                            $masccmpcoord = $masccmp;
                            $pluralcmpcoord = $pluralcmp;
                            
                            // si té element coordinat
                            if (count($nouncmp->paraulacoord) > 0) {

                                for ($k=0; $k<count($nouncmp->paraulacoord); $k++) {

                                    $paraulacoord = $nouncmp->paraulacoord[$k];
                                    
                                    $paraulacoordisadj = false;
                                    if ($paraulacoord->tipus == "adj") $paraulacoordisadj = true;

                                    // afegim la "i"
                                    $elementaux[0] = "i";
                                    $elementaux[1] = null;
                                    $this->slotstring[] = $elementaux;

                                    if ($paraulacoordisadj) {
                                        $elementaux[0] = $paraulacoord->propietats->masc;
                                        $elementaux[1] = $paraulacoord;
                                        $this->slotstring[] = $elementaux;
                                    }
                                    else {
                                        // afegim la paraula coordinada amb la seva concordància
                                        $masccmpcoord = true;
                                        $pluralcmpcoord = false;
                                        // si el nom és femení
                                        if ($paraulacoord->propietats->mf == "fem") $masccmpcoord = false;
                                        // si el nom és plural
                                        if ($paraulacoord->propietats->singpl == "pl") $pluralcmpcoord = true;
                                        // si té modificadors de femení i l'accepta
                                        if ($paraulacoord->propietats->femeni != "" && $paraulacoord->fem) $masccmpcoord = false;
                                        // si té modificador de plural
                                        if ($paraulacoord->plural) $pluralcmpcoord = true;

                                        if ($pluralcmpcoord) $elementaux[0] = $paraulacoord->propietats->plural;
                                        else if ($masccmpcoord && !$pluralcmpcoord) $elementaux[0] = $paraulacoord->propietats->nomtext;
                                        else {
                                            if ($paraulacoord->propietats->mf == "fem") $elementaux[0] = $paraulacoord->propietats->nomtext;
                                            else $elementaux[0] = $paraulacoord->propietats->femeni;
                                        }
                                        $elementaux[1] = $paraulacoord;
                                        // com que el nucli és un nom, afegim la informació extra
                                        $elementaux[2] = true;
                                        $elementaux[3] = $masccmpcoord;
                                        $elementaux[4] = $pluralcmpcoord;
                                        $elementaux[5] = false;
                                        $elementaux[6] = true;
                                        $elementaux[7] = $haspossessive;

                                        $this->slotstring[] = $elementaux;
                                    }                                    
                                }
                            }
                            
                            // després afegim l'adjectiu
                            // si l'adjectiu tenia un quantificador l'afegim
                            if ($hasquantifieradj) {
                                for ($i=0; $i<count($quantifieradj); $i++) {
                                    // aquí els quantificadors tenen forma invariable
                                    $elementaux[0] = $quantifieradj[$i]->paraulafinal->propietats->masc;
                                    $elementaux[1] = $quantifieradj[$i]->paraulafinal;

                                    $this->slotstring[] = $elementaux;
                                }
                            }
                            // tant si tenia quantificador com si no, posem l'adjectiu que concorda amb el complement
                            $adjectiu = $adjectiuslot->paraulafinal;
                            if ($masccmpcoord && !$pluralcmpcoord) $elementaux[0] = $adjectiu->propietats->masc;
                            else if ($masccmpcoord && $pluralcmpcoord) $elementaux[0] = $adjectiu->propietats->mascpl;
                            else if (!$masccmpcoord && !$pluralcmpcoord) $elementaux[0] = $adjectiu->propietats->fem;
                            else $elementaux[0] = $adjectiu->propietats->fempl;
                            $elementaux[1] = $adjectiu;

                            $this->slotstring[] = $elementaux;
                            
                            // si té element adjectiu coordinat l'afegim
                            if (count($adjectiu->paraulacoord) > 0) {

                                for ($k=0; $k<count($adjectiu->paraulacoord); $k++) {

                                    $paraulacoord = $adjectiu->paraulacoord[$k];
                                    
                                    // afegim la "i"
                                    $elementaux[0] = "i";
                                    $elementaux[1] = null;
                                    $this->slotstring[] = $elementaux;

                                    // afegim la paraula coordinada amb la mateixa concordància
                                    if ($masccmpcoord && !$pluralcmpcoord) $elementaux[0] = $paraulacoord->propietats->masc;
                                    else if ($masccmpcoord && $pluralcmpcoord) $elementaux[0] = $paraulacoord->propietats->mascpl;
                                    else if (!$masccmpcoord && !$pluralcmpcoord) $elementaux[0] = $paraulacoord->propietats->fem;
                                    else $elementaux[0] = $paraulacoord->propietats->fempl;
                                    $elementaux[1] = $paraulacoord;

                                    $this->slotstring[] = $elementaux;
                                }
                            }
                        }
                    } // Fi si té complement de nom i adjectiu

                    break;
                
                case "adj":
                    
                    // PREPOSICIÓ
                    // posem la preposició, si n'hi ha
                    if ($this->prep != null) {
                            $elementaux[0] = $this->prep;
                            $elementaux[1] = null;
                            $this->slotstring[] = $elementaux;                        
                    }
                    
                    // QUANTIFICADOR
                    // si l'adjectiu té un modificador, que només pot ser un quantificador
                    if ($this->CModassigned) {
                        
                        for ($i=0; $i<count($this->CModassignedkey); $i++) {
                            
                            $quantifierslot = $this->cmpMod[$this->CModassignedkey[$i]];
                            
                            // el quantificador és invariable
                            $elementaux[0] = $quantifierslot->paraulafinal->propietats->masc;
                            $elementaux[1] = $quantifierslot->paraulafinal;

                            $this->slotstring[] = $elementaux;
                        }                        
                    }
                    
                    
                    // POSEM L'ADJECTIU, HA DE CONCORDAR AMB EL SUBJECTE, ja que l'adjectiu
                    // només té slots per fer de nucli en verbs copulatius o verbless patterns
                    // tenint en compte els modificadors de subjecte
                    // si estan definits, tenen preferència els modificadors de l'adjectiu
                    // Si és d'un slot de MANNER no ha de concordar
                                        
                    // si és de manera posem l'adjectiu en masculí
                    if ($this->category == "Manner") {
                        $elementaux[0] = $nucli->text;
                        $elementaux[1] = $nucli;
                        $elementaux[2] = false;
                        
                        $this->slotstring[] = $elementaux;
                        
                        // si té element adjectiu coordinat
                        if (count($nucli->paraulacoord) > 0) {

                            for ($k=0; $k<count($nucli->paraulacoord); $k++) {

                                $paraulacoord = $nucli->paraulacoord[$k];
                                
                                // afegim la "i"
                                $elementaux[0] = "i";
                                $elementaux[1] = null;
                                $this->slotstring[] = $elementaux;

                                // afegim la paraula coordinada amb la mateixa concordància
                                $elementaux[0] = $paraulacoord->text;
                                $elementaux[1] = $paraulacoord;

                                $this->slotstring[] = $elementaux;
                            }
                        }
                    }
                    // si no era de manera (en principi és de verb copulatiu o verbless
                    else {
                        // posem l'adjectiu que concordi
                        if (!$subjmasc && $subjpl) $elementaux[0] = $nucli->propietats->fempl;
                        else if (!$subjmasc && !$subjpl) $elementaux[0] = $nucli->propietats->fem;
                        else if ($subjmasc && $subjpl) $elementaux[0] = $nucli->propietats->mascpl;
                        else $elementaux[0] = $nucli->propietats->masc;
                        
                        // sobreescrivim si l'adjectiu tenia modificadors activats
                        // ho fem perquè si és verbless no té subjecte
                        if ($nucli->fem && $nucli->plural) $elementaux[0] = $nucli->propietats->fempl;
                        else if ($nucli->fem) $elementaux[0] = $nucli->propietats->fem;
                        else if ($nucli->plural) $elementaux[0] = $nucli->propietats->mascpl;
                        
                        $elementaux[1] = $nucli;
                        $elementaux[2] = false;
                        
                        $this->slotstring[] = $elementaux;
                        
                        // si té element adjectiu coordinat
                        if (count($nucli->paraulacoord) > 0) {

                            for ($k=0; $k<count($nucli->paraulacoord); $k++) {

                                $paraulacoord = $nucli->paraulacoord[$k];
                                
                                // afegim la "i"
                                $elementaux[0] = "i";
                                $elementaux[1] = null;
                                $this->slotstring[] = $elementaux;

                                // afegim la paraula coordinada amb la mateixa concordància
                                if (!$subjmasc && $subjpl) $elementaux[0] = $paraulacoord->propietats->fempl;
                                else if (!$subjmasc && !$subjpl) $elementaux[0] = $paraulacoord->propietats->fem;
                                else if ($subjmasc && $subjpl) $elementaux[0] = $paraulacoord->propietats->mascpl;
                                else $elementaux[0] = $paraulacoord->propietats->masc;

                                // sobreescrivim si l'adjectiu tenia modificadors activats
                                // ho fem perquè si és verbless no té subjecte
                                if ($nucli->fem && $nucli->plural) $elementaux[0] = $paraulacoord->propietats->fempl;
                                else if ($nucli->fem) $elementaux[0] = $paraulacoord->propietats->fem;
                                else if ($nucli->plural) $elementaux[0] = $paraulacoord->propietats->mascpl;

                                $elementaux[1] = $paraulacoord;

                                $this->slotstring[] = $elementaux;
                            }
                        }
                    }
                    
                    break;
                
                case "verb":
                    
                    // MODIFICADOR, NOMÉS ELS QUE NO VAN A L'INICI DE LA FRASE
                    // més endavant, si hi ha pronoms febles amb el verb, l'ordre potser es canviarà
                    
                    // NO FEM RES: HO FARAN EL CONJUGADOR I EL CLEANER POSARÀ ELS MODIFICADORS QUE FALTIN
                    // A ON CALGUI DE LA FRASE
                    
                    /* if ($this->CModassigned) {
                        
                        $CI = &get_instance();
                        $CI->load->library('Mymatching');
                        
                        $matching = new Mymatching();
                        
                        for ($i=0; $i<count($this->CModassignedkey); $i++) {
                            
                            $quantifier = $this->cmpMod[$this->CModassignedkey[$i]]->paraulafinal;
                            
                            // si és dels que va entre subjecte i verb
                            if ($matching->isModAfterSubj($quantifier->text)) {
                                $elementaux[0] = $quantifier->text;
                                $elementaux[1] = $quantifier;

                                $this->slotstring[] = $elementaux;
                            }
                        }                        
                    }
                    
                    // POSEM EL VERB, de moment en infinitiu
                    $elementaux[0] = $nucli->text;
                    $elementaux[1] = $nucli;
                    $elementaux[2] = false;
                    
                    $this->slotstring[] = $elementaux; */

                    break;
                
                case "modifier":

                    // PREPOSICIÓ
                    // posem la preposició, si n'hi ha
                    if ($this->prep != null) {
                        $elementaux[0] = $this->prep;
                        $elementaux[1] = null;
                        $this->slotstring[] = $elementaux;                        
                    }
                    
                    // NUCLI -> que és un modificador
                    $elementaux[0] = $nucli->text;
                    $elementaux[1] = $nucli;
                    $elementaux[2] = false;
                    
                    $this->slotstring[] = $elementaux;
                    
                    // QUANTIFICADORS, si n'hi ha
                    if ($this->CModassigned) {
                       for ($i=0; $i<count($this->CModassignedkey); $i++) {
                            
                            $quantifier = $this->cmpMod[$this->CModassignedkey[$i]]->paraulafinal;
                            $elementaux[0] = $quantifier->text;
                            $elementaux[1] = $quantifier;

                            $this->slotstring[] = $elementaux;
                        } 
                    }
                    
                    break;
                    
                case "adv":

                    // PREPOSICIÓ
                    // posem la preposició, si n'hi ha
                    if ($this->prep != null) {
                        $elementaux[0] = $this->prep;
                        $elementaux[1] = null;
                        $this->slotstring[] = $elementaux;                        
                    }
                    
                    // QUANTIFICADORS, si n'hi ha
                    if ($this->CModassigned) {
                       for ($i=0; $i<count($this->CModassignedkey); $i++) {
                            
                            $quantifier = $this->cmpMod[$this->CModassignedkey[$i]]->paraulafinal;
                            $elementaux[0] = $quantifier->text;
                            $elementaux[1] = $quantifier;

                            $this->slotstring[] = $elementaux;
                        } 
                    }
                    
                    // NUCLI -> que és un adverbi
                    $elementaux[0] = $nucli->text;
                    $elementaux[1] = $nucli;
                    $elementaux[2] = false;
                    
                    $this->slotstring[] = $elementaux;
                                        
                    break;
                    
                case "questpart":

                    // Va sense preposició
                    
                    // NUCLI -> que és una partícula de pregunta
                    $elementaux[0] = $nucli->text;
                    $elementaux[1] = $nucli;
                    $elementaux[2] = false;
                    
                    $this->slotstring[] = $elementaux;
                    
                    break;
                
                // Per qualsevol altra mena de nucli
                // posar primer la preposició, si n'hi ha, i després el valor per defecte
                default:
                    
                    // PREPOSICIÓ
                    // posem la preposició, si n'hi ha
                    if ($this->prep != null) {
                        $elementaux[0] = $this->prep;
                        $elementaux[1] = null;
                        $this->slotstring[] = $elementaux;                        
                    }
                    
                    // NUCLI
                    $elementaux[0] = $nucli->text;
                    $elementaux[1] = $nucli;
                    $elementaux[2] = false;
                    
                    $this->slotstring[] = $elementaux; 
                    
                    // si té element element coordinat
                    if (count($nucli->paraulacoord) > 0) {

                        for ($k=0; $k<count($nucli->paraulacoord); $k++) {

                            $paraulacoord = $nucli->paraulacoord[$k];

                            // afegim la "i"
                            $elementaux[0] = "i";
                            $elementaux[1] = null;
                            $this->slotstring[] = $elementaux;

                            // afegim la paraula coordinada
                            $elementaux[0] = $paraulacoord->text;
                            $elementaux[1] = $paraulacoord;

                            $this->slotstring[] = $elementaux;
                        }
                    }
                    
                    break;
            }
            
        }
        // si l'slot tenia posat el valor per defecte
        // posar primer la preposició, si n'hi ha, i després el valor per defecte
        else {
            // PREPOSICIÓ
            // posem la preposició, si n'hi ha, excepte si l'slot obligatori s'ha quedat buit ->
            // ha utilitzat el defvalue i aquest era null            
            if ($this->prep != null 
                    && !($this->defvalueused && ($this->defvalue == null || $this->defvalue == ""))) {
                $elementaux[0] = $this->prep;
                $elementaux[1] = null;
                
                $this->slotstring[] = $elementaux;                        
            }
            
            $elementaux[0] = $this->defvalue;
            $elementaux[1] = null;
            $elementaux[2] = false;
            
            $this->slotstring[] = $elementaux; 
        }
        
        // print_r($this->slotstring); echo "<br /><br />";
    }
    
    // ordena els slots internament i fa concordar els elements
    public function ordenarSlotES($subjmasc, $subjpl, $copulatiu, $impersonal)
    {
        $nucli = $this->paraulafinal;
        $elementaux = array();
        
        if ($nucli != null) {
            
            switch ($nucli->tipus) {
                case "name":
                    
                    $numeros = array();
                    $quantifieradj = array(); // stores the quantifiers that complement the adj instead of the nucleus
                    $hasquantifieradj = false;
                    $hasnumorquant = false;
                    $haspossessive = 0; // al nostre sistema només els nuclis poden tenir possessius
                                            // també l'activarem si té ordinals o altres elements que van
                                            // entre l'article i el nom
                    
                    // calculem els valors per la concordància general
                    $masc = true;
                    $plural = false;
                    
                    // si el verb és copulatiu el nom del theme concorda amb el subjecte
                    if ($this->category == "Theme" && $copulatiu && !$impersonal) {
                        
                        // si el nom és femení
                        if ($nucli->propietats->mf == "fem") $masc = false;
                        // si el nom és plural
                        if ($nucli->propietats->singpl == "pl") $plural = true;
                        // si té modificadors de femení i l'accepta
                        if ($nucli->propietats->femeni != "" && $nucli->fem) $masc = false;
                        // si té modificador de plural
                        if ($nucli->plural) $plural = true;
                        
                        // sobreescrivim els valors si cal
                        if (!$subjmasc && $subjpl) {
                            if ($nucli->propietats->femeni != "") {
                                $masc = false;
                            }
                            $plural = true;
                        }
                        else if (!$subjmasc && !$subjpl && $nucli->propietats->femeni != "") {
                            $masc = false;
                        }
                        else if ($subjmasc && $subjpl) $plural = true;
                    }
                    else {
                        // si el nom és femení
                        if ($nucli->propietats->mf == "fem") $masc = false;
                        // si el nom és plural
                        if ($nucli->propietats->singpl == "pl") $plural = true;
                        // si té modificadors de femení i l'accepta
                        if ($nucli->propietats->femeni != "" && $nucli->fem) $masc = false;
                        // si té modificador de plural
                        if ($nucli->plural) $plural = true;
                    }
                    
                    // PREPOSICIÓ
                    // posem la preposició, si n'hi ha, excepte: Si el nom és el nucli d'un slot de LocAt i 
                    // el complementa un adverbi de lloc. Per evitar ex: "Está en debajo la mesa." SI és un
                    // Theme amb un pronom i la preposició "a" davant, que no la posarem -> Per evitar: A te quiero.
                    if ($this->prep != null) {
                        if (!(($this->category == "LocAt" && $this->CAdvassigned && 
                                $this->cmpAdvs[$this->CAdvassignedkey]->paraulafinal->isClass("lloc"))
                                || ($this->category == "Theme" && $this->paraulafinal->isClass("pronoun") &&
                                    $this->prep == "a"))) {
                            $elementaux[0] = $this->prep;
                            $elementaux[1] = null;
                            $this->slotstring[] = $elementaux; 
                        }
                    }
                    
                    // Posem, si cal, la preposició "en" o "por", davant dels slots de temps
                    if ($this->category == "Time") {
                        $CI = &get_instance();
                        $CI->load->library('Mymatching');
                        
                        $matching = new Mymatching();
                        
                        if ($matching->isTimePrepES($nucli->text)) {
                            $elementaux[0] = $matching->tempsPrepES[$nucli->text];
                            $elementaux[1] = null;
                            $this->slotstring[] = $elementaux;
                        }
                    }
                    
                    // QUANTIFICADOR O POSSESSIUS
                    // si el nom té un modificador, que només pot ser un quantificador o possessiu
                    if ($this->CModassigned) {
                                                
                        for ($i=0; $i<count($this->CModassignedkey); $i++) {
                            
                            $quantifierslot = $this->cmpMod[$this->CModassignedkey[$i]];
                            // si és un número
                            if ($quantifierslot->paraulafinal->isClass("numero")) {
                                $numeros[] = $quantifierslot;
                                // si és un número diferent d'1, tot passarà a ser plural
                                if ($quantifierslot->paraulafinal->text != "un") $plural = true;
                            }
                            // si és un quantificador o possessiu
                            else {
                                // si el nucli també té un adjectiu assignat, mirem si va millor amb l'adjectiu
                                if ($this->CAdjassigned) {
                                    // si és quantifier i anava just davant de l'adjectiu, el guardem per després
                                    if ($quantifierslot->paraulafinal->inputorder - 
                                            $this->cmpAdjs[$this->CAdjassignedkey]->paraulafinal->inputorder == -1
                                            && $quantifierslot->paraulafinal->isClass("quant")) {
                                        $quantifieradj[] = $quantifierslot;
                                        $hasquantifieradj = true;
                                    }
                                    // si no, el tractem com un quantificador o possessiu normal
                                    else {
                                        if ($masc && !$plural) $elementaux[0] = $quantifierslot->paraulafinal->propietats->masc;
                                        else if ($masc && $plural) $elementaux[0] = $quantifierslot->paraulafinal->propietats->mascpl;
                                        else if (!$masc && !$plural) $elementaux[0] = $quantifierslot->paraulafinal->propietats->fem;
                                        else $elementaux[0] = $quantifierslot->paraulafinal->propietats->fempl;
                                        $elementaux[1] = $quantifierslot->paraulafinal;

                                        $this->slotstring[] = $elementaux;
                                        $hasnumorquant = true;
                                        if (strpos($quantifierslot->paraulafinal->classes[0], "pos") === 0) {
                                            $haspossessive++; 
                                            $hasnumorquant = false;
                                        }
                                        // el nom precedit per parecido a portarà article
                                        if (strpos($quantifierslot->paraulafinal->text, "parecido")) $hasnumorquant = false;
                                    }
                                }
                                else {
                                    if ($masc && !$plural) $elementaux[0] = $quantifierslot->paraulafinal->propietats->masc;
                                    else if ($masc && $plural) $elementaux[0] = $quantifierslot->paraulafinal->propietats->mascpl;
                                    else if (!$masc && !$plural) $elementaux[0] = $quantifierslot->paraulafinal->propietats->fem;
                                    else $elementaux[0] = $quantifierslot->paraulafinal->propietats->fempl;
                                    $elementaux[1] = $quantifierslot->paraulafinal;

                                    $this->slotstring[] = $elementaux;
                                    $hasnumorquant = true;
                                    if (strpos($quantifierslot->paraulafinal->classes[0], "pos") === 0) {
                                        $haspossessive++; 
                                        $hasnumorquant = false;
                                    }
                                    // el nom precedit per parecido a portarà article
                                    if (strpos($quantifierslot->paraulafinal->text, "parecido")) $hasnumorquant = false;
                                }
                            }
                        }                        
                    }
                    
                    // ADVERBI DE LLOC
                    if ($this->CAdvassigned) {
                        
                        $adverbistring = $this->cmpAdvs[$this->CAdvassignedkey]->paraulafinal;
                        // dividir-lo en cada una de les paraules que formen l'adverbi
                        $auxstring = explode(" ", $adverbistring->text);
                        // afegir cada paraula en ordre a slotstring
                        for ($i=0; $i<count($auxstring); $i++) {
                            $elementaux[0] = $auxstring[$i];
                            $elementaux[1] = $adverbistring;
                            
                            $this->slotstring[] = $elementaux;
                        }
                    }
                    
                    // NUMEROS
                    for ($i=0; $i<count($numeros); $i++) {
                        if ($masc) $elementaux[0] = $numeros[$i]->paraulafinal->propietats->masc;
                        else $elementaux[0] = $numeros[$i]->paraulafinal->propietats->fem;
                        $elementaux[1] = $numeros[$i]->paraulafinal;
                        
                        $this->slotstring[] = $elementaux;
                        $hasnumorquant = true;
                    }
                    
                    // ORDINALS
                    if ($this->CAdjassigned) {
                        $adjectiu = $this->cmpAdjs[$this->CAdjassignedkey]->paraulafinal;
                        if ($adjectiu->isClass("ordinal")) {
                            if ($masc && !$plural) {
                                // excepció per ex: El primer coche llega tarde.
                                if ($adjectiu->propietats->masc == "primero") $elementaux[0] = "primer";
                                else $elementaux[0] = $adjectiu->propietats->masc;
                            }
                            else if ($masc && $plural) $elementaux[0] = $adjectiu->propietats->mascpl;
                            else if (!$masc && !$plural) $elementaux[0] = $adjectiu->propietats->fem;
                            else $elementaux[0] = $adjectiu->propietats->fempl;
                            $elementaux[1] = $adjectiu;

                            $this->slotstring[] = $elementaux;
                            $haspossessive++;
                            $this->CAdjassigned = false; // indiquem que ja s'ha vist l'adjectiu
                        }
                    }
                    
                    // NOM + COORDINACIÓ
                    if ($plural) {
                        if ($nucli->propietats->mf != "fem" && !$masc) $elementaux[0] = $nucli->propietats->fempl;
                        else $elementaux[0] = $nucli->propietats->plural;
                    }
                    else if ($masc && !$plural) $elementaux[0] = $nucli->propietats->nomtext;
                    else {
                        if ($nucli->propietats->mf == "fem") $elementaux[0] = $nucli->propietats->nomtext;
                        else $elementaux[0] = $nucli->propietats->femeni;
                    }
                    $elementaux[1] = $nucli;
                    // com que el nucli és un nom, afegim la informació extra
                    $elementaux[2] = true;
                    $elementaux[3] = $masc;
                    $elementaux[4] = $plural;
                    $elementaux[5] = $hasnumorquant;
                    $elementaux[6] = false;
                    $elementaux[7] = $haspossessive;
                    
                    $this->slotstring[] = $elementaux;
                    
                    // variables auxiliars que ens ajudaran a concordar l'adjectiu amb 
                    // l'últim nom coordinat, si el nucli no té cap element coordinat, 
                    // tenen els valors per concordar amb el nucli
                    $masccoord = $masc;
                    $pluralcoord = $plural;
                    
                    // si té element coordinat
                    if (count($nucli->paraulacoord) > 0) {
                        
                        for ($k=0; $k<count($nucli->paraulacoord); $k++) {
                            
                            $paraulacoord = $nucli->paraulacoord[$k];

                            $paraulacoordisadj = false;
                            if ($paraulacoord->tipus == "adj") $paraulacoordisadj = true;

                            // afegim la "y"
                            $elementaux[0] = "y";
                            $elementaux[1] = null;
                            $this->slotstring[] = $elementaux;
                            
                            if ($paraulacoordisadj) {
                                $elementaux[0] = $paraulacoord->propietats->masc;
                                $elementaux[1] = $paraulacoord;
                                $this->slotstring[] = $elementaux;
                            }
                            else {
                                // afegim la paraula coordinada, el plural es passa, però el femení
                                // s'ha de mirar si la paraula ho era o no el tenia el modificador
                                $masccoord = true;
                                $pluralcoord = false;

                                if ($paraulacoord->propietats->mf == "fem" || $paraulacoord->fem) $masccoord = false;
                                // el plural només pot canviar si plural era false i la paraulacoord sempre és plural
                                // que aleshores ha de passar a true o si volíem que la paraulacoord fos plural
                                if ($paraulacoord->propietats->singpl == "pl" || $paraulacoord->plural) $pluralcoord = true;

                                if ($pluralcoord) $elementaux[0] = $paraulacoord->propietats->plural;
                                else if ($masccoord && !$pluralcoord) $elementaux[0] = $paraulacoord->propietats->nomtext;
                                else {
                                    if ($paraulacoord->propietats->mf == "fem") $elementaux[0] = $paraulacoord->propietats->nomtext;
                                    else $elementaux[0] = $paraulacoord->propietats->femeni;
                                }
                                $elementaux[1] = $paraulacoord;
                                // com que la paraula coordinada ha de ser un nom, afegim la info extra
                                $elementaux[2] = true;
                                $elementaux[3] = $masccoord;
                                $elementaux[4] = $pluralcoord;
                                $elementaux[5] = $hasnumorquant;
                                $elementaux[6] = false;
                                $elementaux[7] = $haspossessive;

                                $this->slotstring[] = $elementaux;
                            }
                        }
                    }
                    
                    // ADJECTIUS I COMPLEMENTS DE NOM
                    // si té un adjectiu i no un complement de nom
                    if ($this->CAdjassigned && !$this->NCassigned) {
                        
                        // si l'adjectiu tenia un quantificador l'afegim
                        if ($hasquantifieradj) {
                            for ($i=0; $i<count($quantifieradj); $i++) {
                                // aquí els quantificadors tenen forma invariable
                                // excepte "mucho" que pasa a ser "muy"
                                if ($quantifieradj[$i]->paraulafinal->propietats->masc == "mucho") {
                                    $elementaux[0] = "muy";
                                }
                                else {
                                    $elementaux[0] = $quantifieradj[$i]->paraulafinal->propietats->masc;
                                }
                                $elementaux[1] = $quantifieradj[$i]->paraulafinal;
                                
                                $this->slotstring[] = $elementaux;
                            }
                        }
                        // tant si tenia quantificador com si no, posem l'adjectiu
                        $adjectiu = $this->cmpAdjs[$this->CAdjassignedkey]->paraulafinal;
                        if ($masccoord && !$pluralcoord) $elementaux[0] = $adjectiu->propietats->masc;
                        else if ($masccoord && $pluralcoord) $elementaux[0] = $adjectiu->propietats->mascpl;
                        else if (!$masccoord && !$pluralcoord) $elementaux[0] = $adjectiu->propietats->fem;
                        else $elementaux[0] = $adjectiu->propietats->fempl;
                        $elementaux[1] = $adjectiu;
                        
                        $this->slotstring[] = $elementaux;
                        
                        // si té element adjectiu coordinat
                        if (count($adjectiu->paraulacoord) > 0) {
                        
                            for ($k=0; $k<count($adjectiu->paraulacoord); $k++) {

                                $paraulacoord = $adjectiu->paraulacoord[$k];
                                
                                // afegim la "y"
                                $elementaux[0] = "y";
                                $elementaux[1] = null;
                                $this->slotstring[] = $elementaux;

                                // afegim la paraula coordinada amb la mateixa concordància
                                if ($masccoord && !$pluralcoord) $elementaux[0] = $paraulacoord->propietats->masc;
                                else if ($masccoord && $pluralcoord) $elementaux[0] = $paraulacoord->propietats->mascpl;
                                else if (!$masccoord && !$pluralcoord) $elementaux[0] = $paraulacoord->propietats->fem;
                                else $elementaux[0] = $paraulacoord->propietats->fempl;
                                $elementaux[1] = $paraulacoord;

                                $this->slotstring[] = $elementaux;
                            
                            }     
                        }
                    }
                    // si té complement de nom i no adjectiu
                    else if ($this->NCassigned && !$this->CAdjassigned) {
                        
                        $nouncmpslot = $this->complements[$this->NCassignedkey];
                        $nouncmp = $nouncmpslot->paraulafinal;
                        
                        // afegim la preposició "de"
                        $elementaux[0] = $nouncmpslot->prep;
                        $elementaux[1] = null;
                        $this->slotstring[] = $elementaux;
                        
                        // afegim el nom
                        $masccmp = true;
                        $pluralcmp = false;
                        // si el nom és femení
                        if ($nouncmp->propietats->mf == "fem") $masccmp = false;
                        // si el nom és plural
                        if ($nouncmp->propietats->singpl == "pl") $pluralcmp = true;
                        // si té modificadors de femení i l'accepta
                        if ($nouncmp->propietats->femeni != "" && $nouncmp->fem) $masccmp = false;
                        // si té modificador de plural
                        if ($nouncmp->plural) $pluralcmp = true;
                        
                        if ($pluralcmp) $elementaux[0] = $nouncmp->propietats->plural;
                        else if ($masccmp && !$pluralcmp) $elementaux[0] = $nouncmp->propietats->nomtext;
                        else {
                            if ($nouncmp->propietats->mf == "fem") $elementaux[0] = $nouncmp->propietats->nomtext;
                            else $elementaux[0] = $nouncmp->propietats->femeni;
                        }
                        $elementaux[1] = $nouncmp;
                        // com que el nucli és un nom, afegim la informació extra
                        $elementaux[2] = true;
                        $elementaux[3] = $masccmp;
                        $elementaux[4] = $pluralcmp;
                        $elementaux[5] = false;
                        $elementaux[6] = true;
                        $elementaux[7] = $haspossessive;
                        
                        $this->slotstring[] = $elementaux;
                        
                        $masccmpcoord = $masccmp;
                        $pluralcmpcoord = $pluralcmp;
                        
                        // si té element coordinat
                        if (count($nouncmp->paraulacoord) > 0) {
                        
                            for ($k=0; $k<count($nouncmp->paraulacoord); $k++) {

                                $paraulacoord = $nouncmp->paraulacoord[$k];
                                
                                $paraulacoordisadj = false;
                                if ($paraulacoord->tipus == "adj") $paraulacoordisadj = true;

                                // afegim la "y"
                                $elementaux[0] = "y";
                                $elementaux[1] = null;
                                $this->slotstring[] = $elementaux;

                                if ($paraulacoordisadj) {
                                    $elementaux[0] = $paraulacoord->propietats->masc;
                                    $elementaux[1] = $paraulacoord;
                                    $this->slotstring[] = $elementaux;
                                }
                                else {
                                   // afegim la paraula coordinada amb la seva concordància
                                    $masccmpcoord = true;
                                    $pluralcmpcoord = false;
                                    // si el nom és femení
                                    if ($paraulacoord->propietats->mf == "fem") $masccmpcoord = false;
                                    // si el nom és plural
                                    if ($paraulacoord->propietats->singpl == "pl") $pluralcmpcoord = true;
                                    // si té modificadors de femení i l'accepta
                                    if ($paraulacoord->propietats->femeni != "" && $paraulacoord->fem) $masccmpcoord = false;
                                    // si té modificador de plural
                                    if ($paraulacoord->plural) $pluralcmpcoord = true;

                                    if ($pluralcmpcoord) $elementaux[0] = $paraulacoord->propietats->plural;
                                    else if ($masccmpcoord && !$pluralcmpcoord) $elementaux[0] = $paraulacoord->propietats->nomtext;
                                    else {
                                        if ($paraulacoord->propietats->mf == "fem") $elementaux[0] = $paraulacoord->propietats->nomtext;
                                        else $elementaux[0] = $paraulacoord->propietats->femeni;
                                    }
                                    $elementaux[1] = $paraulacoord;
                                    // com que el nucli és un nom, afegim la informació extra
                                    $elementaux[2] = true;
                                    $elementaux[3] = $masccmpcoord;
                                    $elementaux[4] = $pluralcmpcoord;
                                    $elementaux[5] = false;
                                    $elementaux[6] = true;
                                    $elementaux[7] = $haspossessive;

                                    $this->slotstring[] = $elementaux; 
                                }                                
                            }
                        }
                    }
                    // si té complement de nom i adjectiu
                    else if ($this->NCassigned && $this->CAdjassigned) {
                        $nouncmpslot = $this->complements[$this->NCassignedkey];
                        $adjectiuslot = $this->cmpAdjs[$this->CAdjassignedkey];
                        $puntsFitAdjWithNucli = $adjectiuslot->puntsfinal;
                                                
                        // calculem els punts que tindria fent fit al complement
                        
                        $CI = &get_instance();
                        $CI->load->library('Mymatching');
                        
                        $matching = new Mymatching();
                        
                        $classeadj = $adjectiuslot->paraulafinal->classes[$adjectiuslot->indexclassfinalword];
                        
                        $numclassesnomcmp = count($nouncmpslot->paraulafinal->classes);
                        
                        $puntsFitAdjWithCMP = 1000;

                        for ($j=0; $j<$numclassesnomcmp; $j++) {

                            $classenomaux = $nouncmpslot->paraulafinal->classes[$j];

                            if ($matching->isSetKeyAdjNoun($classeadj) && $matching->isSetKeyNoun($classenomaux)) {
                                
                                $tipusx = $matching->adjNounFitKeys[$classeadj];
                                $tipusy = $matching->nounsFitKeys[$classenomaux];

                                if ($matching->adjNounFit[$tipusx][$tipusy] < $puntsFitAdjWithCMP) {
                                    $puntsFitAdjWithCMP = $matching->adjNounFit[$tipusx][$tipusy];
                                }
                            }
                        }
                        // nom - adjectiu 
                        $distance = $nouncmpslot->paraulafinal->inputorder - $adjectiuslot->paraulafinal->inputorder;
                        // com més lluny i com menys fit facin, pitjor
                        $puntsFitAdjWithCMP = 7 - $puntsFitAdjWithCMP - abs($distance);
                        if ($distance == -1) $puntsFitAdjWithCMP += 1;
                                                
                        // si fa millor fit al NUCLI
                        if ($puntsFitAdjWithNucli >= $puntsFitAdjWithCMP) {
                            
                            // insertem primer l'adjectiu
                            // si l'adjectiu tenia un quantificador l'afegim
                            if ($hasquantifieradj) {
                                for ($i=0; $i<count($quantifieradj); $i++) {
                                    // aquí els quantificadors tenen forma invariable
                                    // excepte "mucho" que pasa a ser "muy"
                                    if ($quantifieradj[$i]->paraulafinal->propietats->masc == "mucho") {
                                        $elementaux[0] = "muy";
                                    }
                                    else {
                                        $elementaux[0] = $quantifieradj[$i]->paraulafinal->propietats->masc;
                                    }
                                    $elementaux[1] = $quantifieradj[$i]->paraulafinal;

                                    $this->slotstring[] = $elementaux;
                                }
                            }
                            // tant si tenia quantificador com si no, posem l'adjectiu
                            $adjectiu = $adjectiuslot->paraulafinal;
                            if ($masccoord && !$pluralcoord) $elementaux[0] = $adjectiu->propietats->masc;
                            else if ($masccoord && $pluralcoord) $elementaux[0] = $adjectiu->propietats->mascpl;
                            else if (!$masccoord && !$pluralcoord) $elementaux[0] = $adjectiu->propietats->fem;
                            else $elementaux[0] = $adjectiu->propietats->fempl;
                            $elementaux[1] = $adjectiu;

                            $this->slotstring[] = $elementaux;
                            
                            // si té element adjectiu coordinat l'afegim
                            if (count($adjectiu->paraulacoord) > 0) {

                                for ($k=0; $k<count($adjectiu->paraulacoord); $k++) {

                                    $paraulacoord = $adjectiu->paraulacoord[$k];
                                    
                                    // afegim la "y"
                                    $elementaux[0] = "y";
                                    $elementaux[1] = null;
                                    $this->slotstring[] = $elementaux;

                                    // afegim la paraula coordinada amb la mateixa concordància
                                    if ($masccoord && !$pluralcoord) $elementaux[0] = $paraulacoord->propietats->masc;
                                    else if ($masccoord && $pluralcoord) $elementaux[0] = $paraulacoord->propietats->mascpl;
                                    else if (!$masccoord && !$pluralcoord) $elementaux[0] = $paraulacoord->propietats->fem;
                                    else $elementaux[0] = $paraulacoord->propietats->fempl;
                                    $elementaux[1] = $paraulacoord;

                                    $this->slotstring[] = $elementaux;
                                    
                                }   
                            }

                            // després insertem el nom que fa de complement
                            $nouncmp = $nouncmpslot->paraulafinal;

                            // afegim la preposició "de"
                            $elementaux[0] = $nouncmpslot->prep;
                            $elementaux[1] = null;
                            $this->slotstring[] = $elementaux;

                            // afegim el nom
                            $masccmp = true;
                            $pluralcmp = false;
                            // si el nom és femení
                            if ($nouncmp->propietats->mf == "fem") $masccmp = false;
                            // si el nom és plural
                            if ($nouncmp->propietats->singpl == "pl") $pluralcmp = true;
                            // si té modificadors de femení i l'accepta
                            if ($nouncmp->propietats->femeni != "" && $nucli->fem) $masccmp = false;
                            // si té modificador de plural
                            if ($nouncmp->plural) $pluralcmp = true;

                            if ($pluralcmp) $elementaux[0] = $nouncmp->propietats->plural;
                            else if ($masccmp && !$pluralcmp) $elementaux[0] = $nouncmp->propietats->nomtext;
                            else {
                                if ($nouncmp->propietats->mf == "fem") $elementaux[0] = $nouncmp->propietats->nomtext;
                                else $elementaux[0] = $nouncmp->propietats->femeni;
                            }
                            $elementaux[1] = $nouncmp;
                            // com que el nucli és un nom, afegim la informació extra
                            $elementaux[2] = true;
                            $elementaux[3] = $masccmp;
                            $elementaux[4] = $pluralcmp;
                            $elementaux[5] = false;
                            $elementaux[6] = true;
                            $elementaux[7] = $haspossessive;
                            
                            $this->slotstring[] = $elementaux;
                            
                            // si té element coordinat
                            if (count($nouncmp->paraulacoord) > 0) {

                                for ($k=0; $k<count($nouncmp->paraulacoord); $k++) {

                                    $paraulacoord = $nouncmp->paraulacoord[$k];
                                    
                                    $paraulacoordisadj = false;
                                    if ($paraulacoord->tipus == "adj") $paraulacoordisadj = true;

                                    // afegim la "y"
                                    $elementaux[0] = "y";
                                    $elementaux[1] = null;
                                    $this->slotstring[] = $elementaux;

                                    if ($paraulacoordisadj) {
                                        $elementaux[0] = $paraulacoord->propietats->masc;
                                        $elementaux[1] = $paraulacoord;
                                        $this->slotstring[] = $elementaux;
                                    }
                                    else {
                                        // afegim la paraula coordinada amb la seva concordància
                                        $masccmp = true;
                                        $pluralcmp = false;
                                        // si el nom és femení
                                        if ($paraulacoord->propietats->mf == "fem") $masccmp = false;
                                        // si el nom és plural
                                        if ($paraulacoord->propietats->singpl == "pl") $pluralcmp = true;
                                        // si té modificadors de femení i l'accepta
                                        if ($paraulacoord->propietats->femeni != "" && $paraulacoord->fem) $masccmp = false;
                                        // si té modificador de plural
                                        if ($paraulacoord->plural) $pluralcmp = true;

                                        if ($pluralcmp) $elementaux[0] = $paraulacoord->propietats->plural;
                                        else if ($masccmp && !$pluralcmp) $elementaux[0] = $paraulacoord->propietats->nomtext;
                                        else {
                                            if ($paraulacoord->propietats->mf == "fem") $elementaux[0] = $paraulacoord->propietats->nomtext;
                                            else $elementaux[0] = $paraulacoord->propietats->femeni;
                                        }
                                        $elementaux[1] = $paraulacoord;
                                        // com que el nucli és un nom, afegim la informació extra
                                        $elementaux[2] = true;
                                        $elementaux[3] = $masccmp;
                                        $elementaux[4] = $pluralcmp;
                                        $elementaux[5] = false;
                                        $elementaux[6] = true;
                                        $elementaux[7] = $haspossessive;

                                        $this->slotstring[] = $elementaux;
                                    }
                                }
                            }
                        }
                        // si fa millor fit al COMPLEMENT
                        else {
                            
                            // primer insertem el nom que fa de complement
                            $nouncmp = $nouncmpslot->paraulafinal;

                            // afegim la preposició "de"
                            $elementaux[0] = $nouncmpslot->prep;
                            $elementaux[1] = null;
                            $this->slotstring[] = $elementaux;

                            // afegim el nom
                            $masccmp = true;
                            $pluralcmp = false;
                            // si el nom és femení
                            if ($nouncmp->propietats->mf == "fem") $masccmp = false;
                            // si el nom és plural
                            if ($nouncmp->propietats->singpl == "pl") $pluralcmp = true;
                            // si té modificadors de femení i l'accepta
                            if ($nouncmp->propietats->femeni != "" && $nucli->fem) $masccmp = false;
                            // si té modificador de plural
                            if ($nouncmp->plural) $pluralcmp = true;

                            if ($pluralcmp) $elementaux[0] = $nouncmp->propietats->plural;
                            else if ($masccmp && !$pluralcmp) $elementaux[0] = $nouncmp->propietats->nomtext;
                            else {
                                if ($nouncmp->propietats->mf == "fem") $elementaux[0] = $nouncmp->propietats->nomtext;
                                else $elementaux[0] = $nouncmp->propietats->femeni;
                            }
                            $elementaux[1] = $nouncmp;
                            // com que el nucli és un nom, afegim la informació extra
                            $elementaux[2] = true;
                            $elementaux[3] = $masccmp;
                            $elementaux[4] = $pluralcmp;
                            $elementaux[5] = false;
                            $elementaux[6] = true;
                            $elementaux[7] = $haspossessive;
                            
                            $this->slotstring[] = $elementaux;
                            
                            $masccmpcoord = $masccmp;
                            $pluralcmpcoord = $pluralcmp;
                            
                            // si té element coordinat
                            if (count($nouncmp->paraulacoord) > 0) {

                                for ($k=0; $k<count($nouncmp->paraulacoord); $k++) {

                                    $paraulacoord = $nouncmp->paraulacoord[$k];
                                    
                                    $paraulacoordisadj = false;
                                    if ($paraulacoord->tipus == "adj") $paraulacoordisadj = true;

                                    // afegim la "y"
                                    $elementaux[0] = "y";
                                    $elementaux[1] = null;
                                    $this->slotstring[] = $elementaux;

                                    if ($paraulacoordisadj) {
                                        $elementaux[0] = $paraulacoord->propietats->masc;
                                        $elementaux[1] = $paraulacoord;
                                        $this->slotstring[] = $elementaux;
                                    }
                                    else {
                                        // afegim la paraula coordinada amb la seva concordància
                                        $masccmpcoord = true;
                                        $pluralcmpcoord = false;
                                        // si el nom és femení
                                        if ($paraulacoord->propietats->mf == "fem") $masccmpcoord = false;
                                        // si el nom és plural
                                        if ($paraulacoord->propietats->singpl == "pl") $pluralcmpcoord = true;
                                        // si té modificadors de femení i l'accepta
                                        if ($paraulacoord->propietats->femeni != "" && $paraulacoord->fem) $masccmpcoord = false;
                                        // si té modificador de plural
                                        if ($paraulacoord->plural) $pluralcmpcoord = true;

                                        if ($pluralcmpcoord) $elementaux[0] = $paraulacoord->propietats->plural;
                                        else if ($masccmpcoord && !$pluralcmpcoord) $elementaux[0] = $paraulacoord->propietats->nomtext;
                                        else {
                                            if ($paraulacoord->propietats->mf == "fem") $elementaux[0] = $paraulacoord->propietats->nomtext;
                                            else $elementaux[0] = $paraulacoord->propietats->femeni;
                                        }
                                        $elementaux[1] = $paraulacoord;
                                        // com que el nucli és un nom, afegim la informació extra
                                        $elementaux[2] = true;
                                        $elementaux[3] = $masccmpcoord;
                                        $elementaux[4] = $pluralcmpcoord;
                                        $elementaux[5] = false;
                                        $elementaux[6] = true;
                                        $elementaux[7] = $haspossessive;

                                        $this->slotstring[] = $elementaux;
                                    }
                                }
                            }
                            
                            // després afegim l'adjectiu
                            // si l'adjectiu tenia un quantificador l'afegim
                            if ($hasquantifieradj) {
                                for ($i=0; $i<count($quantifieradj); $i++) {
                                    // aquí els quantificadors tenen forma invariable
                                    // excepte "mucho" que pasa a ser "muy"
                                    if ($quantifieradj[$i]->paraulafinal->propietats->masc == "mucho") {
                                        $elementaux[0] = "muy";
                                    }
                                    else {
                                        $elementaux[0] = $quantifieradj[$i]->paraulafinal->propietats->masc;
                                    }
                                    $elementaux[1] = $quantifieradj[$i]->paraulafinal;

                                    $this->slotstring[] = $elementaux;
                                }
                            }
                            // tant si tenia quantificador com si no, posem l'adjectiu que concorda amb el complement
                            $adjectiu = $adjectiuslot->paraulafinal;
                            if ($masccmpcoord && !$pluralcmpcoord) $elementaux[0] = $adjectiu->propietats->masc;
                            else if ($masccmpcoord && $pluralcmpcoord) $elementaux[0] = $adjectiu->propietats->mascpl;
                            else if (!$masccmpcoord && !$pluralcmpcoord) $elementaux[0] = $adjectiu->propietats->fem;
                            else $elementaux[0] = $adjectiu->propietats->fempl;
                            $elementaux[1] = $adjectiu;

                            $this->slotstring[] = $elementaux;
                            
                            // si té element adjectiu coordinat l'afegim
                            if (count($adjectiu->paraulacoord) > 0) {

                                for ($k=0; $k<count($adjectiu->paraulacoord); $k++) {

                                    $paraulacoord = $adjectiu->paraulacoord[$k];
                                    
                                    // afegim la "y"
                                    $elementaux[0] = "y";
                                    $elementaux[1] = null;
                                    $this->slotstring[] = $elementaux;

                                    // afegim la paraula coordinada amb la mateixa concordància
                                    if ($masccmpcoord && !$pluralcmpcoord) $elementaux[0] = $paraulacoord->propietats->masc;
                                    else if ($masccmpcoord && $pluralcmpcoord) $elementaux[0] = $paraulacoord->propietats->mascpl;
                                    else if (!$masccmpcoord && !$pluralcmpcoord) $elementaux[0] = $paraulacoord->propietats->fem;
                                    else $elementaux[0] = $paraulacoord->propietats->fempl;
                                    $elementaux[1] = $paraulacoord;

                                    $this->slotstring[] = $elementaux;
                                }
                            }
                        }
                    } // Fi si té complement de nom i adjectiu

                    break;
                
                case "adj":
                    
                    // PREPOSICIÓ
                    // posem la preposició, si n'hi ha
                    if ($this->prep != null) {
                            $elementaux[0] = $this->prep;
                            $elementaux[1] = null;
                            $this->slotstring[] = $elementaux;                        
                    }
                    
                    // QUANTIFICADOR
                    // si l'adjectiu té un modificador, que només pot ser un quantificador
                    if ($this->CModassigned) {
                        
                        for ($i=0; $i<count($this->CModassignedkey); $i++) {
                            
                            $quantifierslot = $this->cmpMod[$this->CModassignedkey[$i]];
                            
                            // el quantificador és invariable
                            // excepte "mucho" que pasa a ser "muy"
                            if ($quantifierslot->paraulafinal->propietats->masc == "mucho") {
                                // si té més d'un quantificador ja no. Ex: mucho más alto
                                if (count($this->CModassignedkey) == 1) $elementaux[0] = "muy";
                                else $elementaux[0] = "mucho";
                            }
                            else {
                                $elementaux[0] = $quantifierslot->paraulafinal->propietats->masc;
                            }
                            $elementaux[1] = $quantifierslot->paraulafinal;

                            $this->slotstring[] = $elementaux;
                        }                        
                    }
                    
                    
                    // POSEM L'ADJECTIU, HA DE CONCORDAR AMB EL SUBJECTE, ja que l'adjectiu
                    // només té slots per fer de nucli en verbs copulatius o verbless patterns
                    // tenint en compte els modificadors de subjecte
                    // si estan definits, tenen preferència els modificadors de l'adjectiu
                    // Si és d'un slot de MANNER no ha de concordar
                                        
                    // si és de manera posem l'adjectiu en masculí
                    if ($this->category == "Manner") {
                        $elementaux[0] = $nucli->text;
                        $elementaux[1] = $nucli;
                        $elementaux[2] = false;
                        
                        $this->slotstring[] = $elementaux;
                        
                        // si té element adjectiu coordinat
                        if (count($nucli->paraulacoord) > 0) {

                            for ($k=0; $k<count($nucli->paraulacoord); $k++) {

                                $paraulacoord = $nucli->paraulacoord[$k];
                                
                                // afegim la "y"
                                $elementaux[0] = "y";
                                $elementaux[1] = null;
                                $this->slotstring[] = $elementaux;

                                // afegim la paraula coordinada amb la mateixa concordància
                                $elementaux[0] = $paraulacoord->text;
                                $elementaux[1] = $paraulacoord;

                                $this->slotstring[] = $elementaux;
                            }
                        }
                    }
                    // si no era de manera (en principi és de verb copulatiu o verbless
                    else {
                        // posem l'adjectiu que concordi
                        if (!$subjmasc && $subjpl) $elementaux[0] = $nucli->propietats->fempl;
                        else if (!$subjmasc && !$subjpl) $elementaux[0] = $nucli->propietats->fem;
                        else if ($subjmasc && $subjpl) $elementaux[0] = $nucli->propietats->mascpl;
                        else $elementaux[0] = $nucli->propietats->masc;
                        
                        // sobreescrivim si l'adjectiu tenia modificadors activats
                        // ho fem perquè si és verbless no té subjecte
                        if ($nucli->fem && $nucli->plural) $elementaux[0] = $nucli->propietats->fempl;
                        else if ($nucli->fem) $elementaux[0] = $nucli->propietats->fem;
                        else if ($nucli->plural) $elementaux[0] = $nucli->propietats->mascpl;
                        
                        $elementaux[1] = $nucli;
                        $elementaux[2] = false;
                        
                        $this->slotstring[] = $elementaux;
                        
                        // si té element adjectiu coordinat
                        if (count($nucli->paraulacoord) > 0) {

                            for ($k=0; $k<count($nucli->paraulacoord); $k++) {

                                $paraulacoord = $nucli->paraulacoord[$k];
                                
                                // afegim la "y"
                                $elementaux[0] = "y";
                                $elementaux[1] = null;
                                $this->slotstring[] = $elementaux;

                                // afegim la paraula coordinada amb la mateixa concordància
                                if (!$subjmasc && $subjpl) $elementaux[0] = $paraulacoord->propietats->fempl;
                                else if (!$subjmasc && !$subjpl) $elementaux[0] = $paraulacoord->propietats->fem;
                                else if ($subjmasc && $subjpl) $elementaux[0] = $paraulacoord->propietats->mascpl;
                                else $elementaux[0] = $paraulacoord->propietats->masc;

                                // sobreescrivim si l'adjectiu tenia modificadors activats
                                // ho fem perquè si és verbless no té subjecte
                                if ($nucli->fem && $nucli->plural) $elementaux[0] = $paraulacoord->propietats->fempl;
                                else if ($nucli->fem) $elementaux[0] = $paraulacoord->propietats->fem;
                                else if ($nucli->plural) $elementaux[0] = $paraulacoord->propietats->mascpl;

                                $elementaux[1] = $paraulacoord;

                                $this->slotstring[] = $elementaux;
                            }
                        }
                    }
                    
                    break;
                
                case "verb":
                    
                    // MODIFICADOR, NOMÉS ELS QUE NO VAN A L'INICI DE LA FRASE
                    // més endavant, si hi ha pronoms febles amb el verb, l'ordre potser es canviarà
                    
                    // NO FEM RES: HO FARAN EL CONJUGADOR I EL CLEANER POSARÀ ELS MODIFICADORS QUE FALTIN
                    // A ON CALGUI DE LA FRASE
                    
                    /* if ($this->CModassigned) {
                        
                        $CI = &get_instance();
                        $CI->load->library('Mymatching');
                        
                        $matching = new Mymatching();
                        
                        for ($i=0; $i<count($this->CModassignedkey); $i++) {
                            
                            $quantifier = $this->cmpMod[$this->CModassignedkey[$i]]->paraulafinal;
                            
                            // si és dels que va entre subjecte i verb
                            if ($matching->isModAfterSubj($quantifier->text)) {
                                $elementaux[0] = $quantifier->text;
                                $elementaux[1] = $quantifier;

                                $this->slotstring[] = $elementaux;
                            }
                        }                        
                    }
                    
                    // POSEM EL VERB, de moment en infinitiu
                    $elementaux[0] = $nucli->text;
                    $elementaux[1] = $nucli;
                    $elementaux[2] = false;
                    
                    $this->slotstring[] = $elementaux; */

                    break;
                
                case "modifier":

                    // PREPOSICIÓ
                    // posem la preposició, si n'hi ha
                    if ($this->prep != null) {
                        $elementaux[0] = $this->prep;
                        $elementaux[1] = null;
                        $this->slotstring[] = $elementaux;                        
                    }
                    
                    // NUCLI -> que és un modificador
                    $elementaux[0] = $nucli->text;
                    $elementaux[1] = $nucli;
                    $elementaux[2] = false;
                    
                    $this->slotstring[] = $elementaux;
                    
                    // QUANTIFICADORS, si n'hi ha
                    if ($this->CModassigned) {
                       for ($i=0; $i<count($this->CModassignedkey); $i++) {
                            
                            $quantifier = $this->cmpMod[$this->CModassignedkey[$i]]->paraulafinal;
                            $elementaux[0] = $quantifier->text;
                            $elementaux[1] = $quantifier;

                            $this->slotstring[] = $elementaux;
                        } 
                    }
                    
                    break;
                    
                case "adv":

                    // PREPOSICIÓ
                    // posem la preposició, si n'hi ha
                    if ($this->prep != null) {
                        $elementaux[0] = $this->prep;
                        $elementaux[1] = null;
                        $this->slotstring[] = $elementaux;                        
                    }
                    
                    // QUANTIFICADORS, si n'hi ha
                    if ($this->CModassigned) {
                       for ($i=0; $i<count($this->CModassignedkey); $i++) {
                            
                            $quantifier = $this->cmpMod[$this->CModassignedkey[$i]]->paraulafinal;
                            // Excepció: Ex: "Muy arriba".
                            if ($quantifier->text == "mucho") {
                                // si té més d'un quantificador ja no. Ex: mucho más arriba
                                if (count($this->CModassignedkey) == 1) $elementaux[0] = "muy";
                                else $elementaux[0] = "mucho";
                            }
                            else $elementaux[0] = $quantifier->text;
                            $elementaux[1] = $quantifier;

                            $this->slotstring[] = $elementaux;
                        } 
                    }
                    
                    // NUCLI -> que és un adverbi
                    $elementaux[0] = $nucli->text;
                    $elementaux[1] = $nucli;
                    $elementaux[2] = false;
                    
                    $this->slotstring[] = $elementaux;
                                        
                    break;
                    
                case "questpart":

                    // Va sense preposició
                    
                    // NUCLI -> que és una partícula de pregunta
                    $elementaux[0] = $nucli->text;
                    $elementaux[1] = $nucli;
                    $elementaux[2] = false;
                    
                    $this->slotstring[] = $elementaux;
                    
                    break;
                
                // Per qualsevol altra mena de nucli
                // posar primer la preposició, si n'hi ha, i després el valor per defecte
                default:
                    
                    // PREPOSICIÓ
                    // posem la preposició, si n'hi ha
                    if ($this->prep != null) {
                        $elementaux[0] = $this->prep;
                        $elementaux[1] = null;
                        $this->slotstring[] = $elementaux;                        
                    }
                    
                    // NUCLI
                    $elementaux[0] = $nucli->text;
                    $elementaux[1] = $nucli;
                    $elementaux[2] = false;
                    
                    $this->slotstring[] = $elementaux; 
                    
                    // si té element element coordinat
                    if (count($nucli->paraulacoord) > 0) {

                        for ($k=0; $k<count($nucli->paraulacoord); $k++) {

                            $paraulacoord = $nucli->paraulacoord[$k];

                            // afegim la "y"
                            $elementaux[0] = "y";
                            $elementaux[1] = null;
                            $this->slotstring[] = $elementaux;

                            // afegim la paraula coordinada
                            $elementaux[0] = $paraulacoord->text;
                            $elementaux[1] = $paraulacoord;

                            $this->slotstring[] = $elementaux;
                        }
                    }
                    
                    break;
            }
            
        }
        // si l'slot tenia posat el valor per defecte
        // posar primer la preposició, si n'hi ha, i després el valor per defecte
        else {
            // PREPOSICIÓ
            // posem la preposició, si n'hi ha, excepte si l'slot obligatori s'ha quedat buit ->
            // ha utilitzat el defvalue i aquest era null            
            if ($this->prep != null 
                    && !($this->defvalueused && ($this->defvalue == null || $this->defvalue == ""))) {
                $elementaux[0] = $this->prep;
                $elementaux[1] = null;
                
                $this->slotstring[] = $elementaux;                        
            }
            
            $elementaux[0] = $this->defvalue;
            $elementaux[1] = null;
            $elementaux[2] = false;
            
            $this->slotstring[] = $elementaux; 
        }
        
        // print_r($this->slotstring); echo "<br /><br />";
    }
    
    // Posa els articles necessaris a tots els noms de l'slot
    public function putArticles($tipusfrase, $partQuant)
    {
        $i=0;
        $numelements = count($this->slotstring);
        
        // recorrem slotstring per tractar tots els noms que hi ha a l'slot
        while ($i<count($this->slotstring)) { 
            
            $definite = false;
            $indefinite = false;
            $noarticle = false;
            $auxstring = $this->slotstring[$i];
                        
            $article = "";
            
            // agafem la paraula
            $wordaux = $auxstring[1];
            
            // si és un nom
            if ($wordaux != null && $wordaux->isType("name")) {
                // si no necessita article (perquè té quantificadors a davant) o perquè
                // és un pronom personal
                
                if ($auxstring[5] || $wordaux->isClass("pronoun")) $noarticle = true;
                // si és un nom propi va amb article determinat
                else if ($wordaux->propietats->ispropernoun == '1') $definite = true;
                // si no té quantificador davant, procedim amb l'algoritme normal
                else {
                    // si són complements (al nostre sistema no poden tenir possessius)
                    // si no porten article, sense article, i si són del grup dels animats amb determinat
                    // i si no sense, JA QUE ÉS EL CAS MÉS COMÚ
                    if ($auxstring[6]) {
                        if ($wordaux->propietats->determinat == 'sense') $noarticle = true;
                        else if ($wordaux->isClass("material")) $noarticle = true;
                        else if ($wordaux->isClass("animate") ||
                                $wordaux->isClass("animal") ||
                                $wordaux->isClass("vehicle") ||
                                $wordaux->isClass("human") ||
                                $wordaux->isClass("event") ||
                                $wordaux->isClass("objecte") ||
                                $wordaux->isClass("lloc") ||
                                $wordaux->isClass("time") ||
                                $wordaux->isClass("planta")) $definite = true;
                        else $noarticle = true;
                    }
                    // si no són complements, dependrà de quina categoria sigui l'slot
                    else {
                        // si tenen un possessiu l'article és sempre determinat
                        if ($auxstring[7] > 0) $definite = true;
                        // si no hi ha possessiu
                        else {
                            // si és subjecte és determinat, excepte si és un lloc
                            if ($this->category == "Subject") {
                                // si és una ordre, el subjecte no porta article
                                if ($tipusfrase == "ordre") $noarticle = true;
                                else {
                                    if ($wordaux->isClass("lloc")) {
                                        // mirar propietats
                                        if ($wordaux->propietats->determinat == 'sense') $noarticle = true;
                                        else $definite = true;
                                    }
                                    else $definite = true;
                                }
                            }
                            // si és un locatiu
                            else if ($this->category == "LocAt" || $this->category == "LocTo" 
                                    || $this->category == "LocFrom") {
                                if ($wordaux->isClass("lloc") || $wordaux->isClass("joc")) {
                                    // si no porten article, sense article, i si no determinat
                                    if ($wordaux->propietats->determinat == 'sense') $noarticle = true;
                                    else $definite = true;
                                }
                                else $definite = true;
                            }
                            // si és un theme
                            else if ($this->category == "Theme") {
                                // si hi ha la pregunta "quant", el theme va sense article
                                if ($partQuant) $noarticle = true;
                                // si és una resposta, si el nom és humà, article determinat
                                else if ($tipusfrase == "resposta" && $wordaux->isClass("human")) {
                                    $definite = true;
                                }
                                // si hi ha definit un article determinat pel theme i la paraula no
                                // preferia no portar article, l'agafem
                                else if ($this->art != null && $wordaux->propietats->determinat != 'sense') {
                                    if ($this->art == '1') $definite = true;
                                    else if ($this->art == '0') $indefinite = true;
                                    else $noarticle = true;
                                }
                                // si no, mirem les propietats
                                else {
                                    if ($wordaux->propietats->determinat == '1') $definite = true;
                                    else if ($wordaux->propietats->determinat == '0') $indefinite = true;
                                    else $noarticle = true;
                                }
                            }
                            // si és un receiver
                            else if ($this->category == "Receiver") {
                                $definite = true;
                            }
                            // en tots els altres slots i casos, mirem les propietats del nom
                            else {
                                if ($wordaux->propietats->determinat == '1') $definite = true;
                                else if ($wordaux->propietats->determinat == '0') $indefinite = true;
                                else $noarticle = true;
                            }
                        } // Fi si no hi ha possessiu
                    } // Fi si no és complement
                } // Fi si no té quantificador
            }
            // si no és un nom
            else $noarticle = true;
            
            // POSEM ELS ARTICLES
            //si ha de portar article
            if (!$noarticle) {
                if ($definite) {
                    // si té possessius i/o ordinals davant, no volem que posi apòstrofs si el nom és singular,
                    // ja que concorda amb el nom, però va davant dels possessius o ordinals que mai 
                    // comencen en vocal
                    if ($auxstring[7] > 0 && !$auxstring[4]) {
                        if ($auxstring[3]) $article = "el";
                        else $article = "la";
                    }
                    else $article = $wordaux->giveDefiniteArticleContext($auxstring[3], $auxstring[4]);

                }

                else if ($indefinite) {
                    $article = $wordaux->giveIndefiniteArticleContext($auxstring[3], $auxstring[4]);
                }
                
                // fer l'insert, tenir en compte si és POSSESSIU
                $elementaux = array();
                $elementaux[0] = $article;
                $elementaux[1] = null;
                
                $indexinsert = $i;
                // si té un possessiu i/o un ordinal, l'hem de posar abans del possessiu i/o l'ordinal
                if ($auxstring[7] > 0) $indexinsert -= $auxstring[7];
                
                // fem l'insert... aquí array_splice donava problemes
                $slotstringaux = array();
                for ($j=0; $j<count($this->slotstring); $j++) {
                    if ($j == $indexinsert) $slotstringaux[] = $elementaux;
                    $slotstringaux[] = $this->slotstring[$j];
                }
                $this->slotstring = $slotstringaux;
                                                
                // si insertem un element, com que no volem tornar a tractar el nom que ara està una posició
                // més endavant de slotstring, incrementem la "i"
                $i++; 
            }
            
            $i++;
        } // Fi while per cada element de slotstring
    }
    
    // Posa els articles necessaris a tots els noms de l'slot
    public function putArticlesES($tipusfrase, $partQuant)
    {
        $i=0;
        
        // recorrem slotstring per tractar tots els noms que hi ha a l'slot
        while ($i<count($this->slotstring)) { 
            
            $definite = false;
            $indefinite = false;
            $noarticle = false;
            $auxstring = $this->slotstring[$i];
                        
            $article = "";
            
            // agafem la paraula
            $wordaux = $auxstring[1];
            
            // si és un nom
            if ($wordaux != null && $wordaux->isType("name")) {
                // si no necessita article (perquè té quantificadors a davant) o perquè
                // és un pronom personal o un nom propi
                if ($auxstring[5] || $wordaux->isClass("pronoun") || $wordaux->propietats->ispropernoun == '1') $noarticle = true;
                // si no té quantificador davant, procedim amb l'algoritme normal
                else {
                    // si són complements (al nostre sistema no poden tenir possessius)
                    // si no porten article, sense article, i si són del grup dels animats amb determinat
                    // i si no sense, JA QUE ÉS EL CAS MÉS COMÚ
                    if ($auxstring[6]) {
                        if ($wordaux->propietats->determinat == 'sense') $noarticle = true;
                        else if ($wordaux->isClass("material")) $noarticle = true;
                        else if ($wordaux->isClass("animate") ||
                                $wordaux->isClass("animal") ||
                                $wordaux->isClass("vehicle") ||
                                $wordaux->isClass("human") ||
                                $wordaux->isClass("event") ||
                                $wordaux->isClass("objecte") ||
                                $wordaux->isClass("lloc") ||
                                $wordaux->isClass("time") ||
                                $wordaux->isClass("planta")) $definite = true;
                        else $noarticle = true;
                    }
                    // si no són complements, dependrà de quina categoria sigui l'slot
                    else {
                        // si tenen un possessiu no porten article en castellà
                        if ($auxstring[7] > 0) $noarticle = true;
                        // si no hi ha possessiu
                        else {
                            // si és subjecte és determinat, excepte si és un lloc
                            if ($this->category == "Subject") {
                                // si és una ordre, el subjecte no porta article
                                if ($tipusfrase == "ordre") $noarticle = true;
                                else {
                                    if ($wordaux->isClass("lloc")) {
                                        // mirar propietats
                                        if ($wordaux->propietats->determinat == 'sense') $noarticle = true;
                                        else $definite = true;
                                    } 
                                    else $definite = true;
                                }
                            }
                            // si és un locatiu
                            else if ($this->category == "LocAt" || $this->category == "LocTo" 
                                    || $this->category == "LocFrom") {
                                if ($wordaux->isClass("lloc") || $wordaux->isClass("joc")) {
                                    // si no porten article, sense article, i si no determinat
                                    if ($wordaux->propietats->determinat == 'sense') $noarticle = true;
                                    else $definite = true;
                                }
                                else $definite = true;
                            }
                            // si és un theme
                            else if ($this->category == "Theme") {
                                // si hi ha la pregunta "quant", el theme va sense article
                                if ($partQuant) $noarticle = true;
                                // si és una resposta, si el nom és humà, article determinat
                                else if ($tipusfrase == "resposta" && $wordaux->isClass("human")) {
                                    $definite = true;
                                }
                                // si hi ha definit un article determinat pel theme l'agafem
                                else if ($this->art != null) {
                                    if ($this->art == '1') $definite = true;
                                    else if ($this->art == '0') $indefinite = true;
                                    else $noarticle = true;
                                }
                                // si no, mirem les propietats
                                else {
                                    if ($wordaux->propietats->determinat == '1') $definite = true;
                                    else if ($wordaux->propietats->determinat == '0') $indefinite = true;
                                    else $noarticle = true;
                                }
                            }
                            // si és un receiver
                            else if ($this->category == "Receiver") {
                                $definite = true;
                            }
                            // en tots els altres slots i casos, mirem les propietats del nom
                            else {
                                if ($wordaux->propietats->determinat == '1') $definite = true;
                                else if ($wordaux->propietats->determinat == '0') $indefinite = true;
                                else $noarticle = true;
                            }
                        } // Fi si no hi ha possessiu
                    } // Fi si no és complement
                } // Fi si no té quantificador
            }
            // SI NO ÉS UN NOM
            else $noarticle = true;
            
            // POSEM ELS ARTICLES
            //si ha de portar article
            if (!$noarticle) {
                if ($definite) {
                    if ($auxstring[3] && !$auxstring[4]) $article = "el";
                    else if (!$auxstring[3] && !$auxstring[4]) $article = "la";
                    else if ($auxstring[3] && $auxstring[4]) $article = "los";
                    else if (!$auxstring[3] && $auxstring[4]) $article = "las";
                }

                else if ($indefinite) {
                    if ($auxstring[3] && !$auxstring[4]) $article = "un";
                    else if (!$auxstring[3] && !$auxstring[4]) $article = "una";
                    else if ($auxstring[3] && $auxstring[4]) $article = "unos";
                    else if (!$auxstring[3] && $auxstring[4]) $article = "unas";
                }
                
                // fer l'insert, tenir en compte si té un ORDINAL, ex: "el segundo jugador"
                $elementaux = array();
                $elementaux[0] = $article;
                $elementaux[1] = null;
                
                $indexinsert = $i;
                // si té un ordinal, l'hem de posar abans de l'ordinal
                if ($auxstring[7] > 0) $indexinsert -= $auxstring[7];
                
                // fem l'insert... aquí array_splice donava problemes
                $slotstringaux = array();
                for ($j=0; $j<count($this->slotstring); $j++) {
                    if ($j == $indexinsert) $slotstringaux[] = $elementaux;
                    $slotstringaux[] = $this->slotstring[$j];
                }
                $this->slotstring = $slotstringaux;
                                                
                // si insertem un element, com que no volem tornar a tractar el nom que ara està una posició
                // més endavant de slotstring, incrementem la "i"
                $i++; 
            }
            
            $i++;
        } // Fi while per cada element de slotstring
    }
    
}

/* End of file Myslot.php */