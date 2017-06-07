<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Mypattern {
    
    var $id;
    var $idverb;
    var $pronominal = false;
    var $pseudoimpersonal = false;
    var $copulatiu = false;
    var $verbless = false; // per quan no han introduït verb i s'afegeix després amb les columnes
                           // defaultverb o els verbless patterns
    var $impersonal = false;
    
    var $tipusfrase;
    var $defaulttense = null;
    var $verbpeticio;
    var $partpreguntabona = false;
    
    var $subverb = false; // Tindrà el nom de l'slot que accepta el subverb
    var $allowstime = false; // diu si el pattern accepta expressions de temps
    var $timeexpr = array(); // Array amb les expressions de temps
    var $exprsarray = array(); // array amb les expressions introduïdes que omplen el pattern [0] text [1] front
    var $needexpr = false; // indica si una expressió és obligatòria al pattern
    
    var $exemple;
    
    var $slotarray = array(); // És un array de slots
    var $hasslot = array(); // Array amb booleans que diuen si aquest pattern té una categoria d'slot
    var $virtualslotsort = array(); // ordenats per la importància de fer fill
    
    var $puntuaciofinal = 100; // Guarda la puntuació final del pattern
    
    // PER SI HI HA SLOTS DE SUBVERB; PROPIETATS DE L?ESTRUCTURA DEL SUBVERB
    var $id2 = null;
    var $idverb2 = null;
    var $pronominal2 = null;
    var $pseudoimpersonal2 = null;
    
    var $tipusfrase2 = null;
    var $defaulttense2 = null;
    var $verbpeticio2 = null;
    
    var $paraules = array();
    
    /*
     * VARIABLES PEL GENERADOR
     */
    
    var $ordrefrase = array(); // array amb els keys dels slots plens (o obligatoris no plens amb els default value) que formaran la frase final ja ordenats
    var $frasefinal = " "; // string final construït a partir dels slotstring de cada slot
    var $questiontypequant = false; // ens indica si hi ha partícules de pregunta que es comporten com "quant"
    var $frasenegativa = false; // serveix per conjugar l'imperatiu quan és negatiu com a present de subjuntiu 
    
    var $isfem = false; // ens diu si l'usuari és una dona
    
    var $perssubj1 = 1;
    var $genmascsubj1 = true;
    var $plsubj1 = false;
    
    var $perssubj2 = 1;
    var $genmascsubj2 = true;
    var $plsubj2 = false;
    
    var $subjsiguals = false; // si hi ha dos subjectes, diu si són iguals
    
    function __construct() {}
    
    public function initialise($patternbbdd, $verbless, $subjdef) 
    {
        $CI = &get_instance();
        $CI->load->library('Myslot');
        
        $this->id = $patternbbdd->patternid;
        $this->idverb = $patternbbdd->verbid;
        $this->verbless = $verbless;
        // els verbless patterns quan competeixen amb patrons no verbless, que comencin amb menys punts
        // que només els agafi si no en troba de millors
        if ($this->idverb == '0') $this->puntuaciofinal = 90;
        
        if ($patternbbdd->pronominal == '1') $this->pronominal = true;
        if ($patternbbdd->pseudoimpersonal == '1') $this->pseudoimpersonal = true;
        if ($patternbbdd->copulatiu == '1') $this->copulatiu = true;
        
        $this->tipusfrase = $patternbbdd->tipusfrase;
        $this->defaulttense = $patternbbdd->defaulttense;
        $this->verbpeticio = $patternbbdd->verbpeticio;
        
        if ($patternbbdd->subverb == '1') $this->subverb = true;
        
        $this->exemple = $patternbbdd->exemple;
        
        // Slot SUBJECT
        if ($patternbbdd->subj != '0') {
            $subjecte = new Myslot();
            $subjecte->category = "Subject";
            $subjecte->grade = "1";
            $subjecte->type = $patternbbdd->subj;
            $subjecte->defvalue = $patternbbdd->subjdef;
            $subjecte->slotPuntsInicials();
            $subjecte->verbless = $verbless;
            
            // sobreescrivim el subjecte per defecte, si s'envia des de la columna subdef dels adjectius
            if ($subjdef) {
                $subjecte->defvalue = $subjdef;
            }

            if ($subjecte->type == "verb") $this->subverb = "Subject";
            $this->hasslot["Subject"] = true;

            $this->slotarray["Subject"] = $subjecte;
        }
        // si no té subjecte
        else {
            $this->impersonal = true;
        }
        
        // Slot ROOT
        $rootslot = new Myslot();
        $rootslot->category = "Main Verb";
        $rootslot->grade = "1";
        $rootslot->type = "verb";
        $rootslot->slotPuntsInicials();
        
        $this->slotarray["Main Verb"] = $rootslot;
        $this->hasslot["Main Verb"] = true;
        
        // Després d'inicialitzar tot el pattern, s'omple l'slot de Main Verb amb el Verb principal
                        
        // Slot THEME
        if ($patternbbdd->theme != '0') {
            
            $theme = new Myslot();
            $theme->category = "Theme";
            $theme->grade = $patternbbdd->theme;
            $theme->slotPuntsInicials();

            $theme->type = $patternbbdd->themetipus;
            if ($theme->grade == '1') $theme->defvalue = $patternbbdd->themedef;
            if ($patternbbdd->themeprep != "") $theme->prep = $patternbbdd->themeprep;
            if ($patternbbdd->themeart != "NULL") $theme->art = $patternbbdd->themeart;
            
            if ($theme->type == "verb") $this->subverb = "Theme";
            $this->hasslot["Theme"] = true;
            
            $this->slotarray["Theme"] = $theme;
        }
        
        // Slot RECEIVER
        if ($patternbbdd->receiver != '0') {
            
            $receiver = new Myslot();
            $receiver->category = "Receiver";
            $receiver->grade = $patternbbdd->receiver;
            $receiver->slotPuntsInicials();

            $receiver->type = "human";
            if ($receiver->grade == '1') $receiver->defvalue = $patternbbdd->receiverdef;
            if ($patternbbdd->receiverprep != "") $receiver->prep = $patternbbdd->receiverprep;
            
            if ($receiver->type == "verb") $this->subverb = "Receiver";
            $this->hasslot["Receiver"] = true;
            
            $this->slotarray["Receiver"] = $receiver;
        }
        
        // Slot BENEFICIARY
        if ($patternbbdd->benef != '0') {
            
            $benef = new Myslot();
            $benef->category = "Beneficiary";
            $benef->grade = $patternbbdd->benef;
            $benef->slotPuntsInicials();

            $benef->type = $patternbbdd->beneftipus;
            if ($benef->grade == '1') $benef->defvalue = $patternbbdd->benefdef;
            if ($patternbbdd->benefprep != "") $benef->prep = $patternbbdd->benefprep;
            
            if ($benef->type == "verb") $this->subverb = "Beneficiary";
            $this->hasslot["Beneficiary"] = true;
            
            $this->slotarray["Beneficiary"] = $benef;
        }
        
        // Slot ACOMP
        if ($patternbbdd->acomp != '0') {
            
            $acomp = new Myslot();
            $acomp->category = "Company";
            $acomp->grade = $patternbbdd->acomp;
            $acomp->slotPuntsInicials();

            $acomp->type = "animate";
            if ($acomp->grade == '1') $acomp->defvalue = $patternbbdd->acompdef;
            if ($patternbbdd->acompprep != "") $acomp->prep = $patternbbdd->acompprep;
            
            if ($acomp->type == "verb") $this->subverb = "Company";
            $this->hasslot["Company"] = true;
            
            $this->slotarray["Company"] = $acomp;
        }
        
        // Slot TOOL
        if ($patternbbdd->tool != '0') {
            
            $tool = new Myslot();
            $tool->category = "Tool";
            $tool->grade = $patternbbdd->tool;
            $tool->slotPuntsInicials();

            $tool->type = "tool";
            if ($tool->grade == '1') $tool->defvalue = $patternbbdd->tooldef;
            if ($patternbbdd->toolprep != "") $tool->prep = $patternbbdd->toolprep;
            
            if ($tool->type == "verb") $this->subverb = "Tool";
            $this->hasslot["Tool"] = true;
            
            $this->slotarray["Tool"] = $tool;
        }
        
        // Slot MANNER
        if ($patternbbdd->manera != '0') {
            
            $manera = new Myslot();
            $manera->category = "Manner";
            $manera->grade = $patternbbdd->manera;
            $manera->slotPuntsInicials();

            if ($patternbbdd->maneratipus == 'adv') $manera->type = "manera";
            else $manera->type = $patternbbdd->maneratipus;
            if ($manera->grade == '1') $manera->defvalue = $patternbbdd->maneradef;
            
            if ($manera->type == "verb") $this->subverb = "Manner";
            $this->hasslot["Manner"] = true;
            
            $this->slotarray["Manner"] = $manera;
        }
        
        // Slot LOCTO
        if ($patternbbdd->locto != '0') {
            
            $locto = new Myslot();
            $locto->category = "LocTo";
            $locto->grade = $patternbbdd->locto;
            $locto->slotPuntsInicials();

            if ($patternbbdd->loctotipus != "verb") $locto->type = "lloc";
            else $locto->type = $patternbbdd->loctotipus;
            if ($locto->grade == '1') $locto->defvalue = $patternbbdd->loctodef;
            if ($patternbbdd->loctoprep != "") $locto->prep = $patternbbdd->loctoprep;
            
            if ($locto->type == "verb") $this->subverb = "LocTo";
            $this->hasslot["LocTo"] = true;
            
            $this->slotarray["LocTo"] = $locto;
        }
        
        // Slot LOCFROM
        if ($patternbbdd->locfrom != '0') {
            
            $locfrom = new Myslot();
            $locfrom->category = "LocFrom";
            $locfrom->grade = $patternbbdd->locfrom;
            $locfrom->slotPuntsInicials();

            if ($patternbbdd->locfromtipus != "verb") $locfrom->type = "lloc";
            else $locfrom->type = $patternbbdd->locfromtipus;
            if ($locfrom->grade == '1') $locfrom->defvalue = $patternbbdd->locfromdef;
            if ($patternbbdd->locfromprep != "") $locfrom->prep = $patternbbdd->locfromprep;
            
            if ($locfrom->type == "verb") $this->subverb = "LocFrom";
            $this->hasslot["LocFrom"] = true;
            
            $this->slotarray["LocFrom"] = $locfrom;
        }
        
        // Slot LOCAT
        if ($patternbbdd->locat != '0') {
            
            $locat = new Myslot();
            $locat->category = "LocAt";
            $locat->grade = $patternbbdd->locat;
            $locat->slotPuntsInicials();

            $locat->type = "lloc";
            if ($locat->grade == '1') $locat->defvalue = $patternbbdd->locatdef;
            if ($patternbbdd->locatprep != "") $locat->prep = $patternbbdd->locatprep;
            
            if ($locat->type == "verb") $this->subverb = "LocAt";
            $this->hasslot["LocAt"] = true;
            
            $this->slotarray["LocAt"] = $locat;
        }
        
        // Slot TIME: Només es fa servir pels noms, estil dies de la setmana, mesos...
        // Pels adverbis de temps fem servir l'array timeexpr
        if ($patternbbdd->time != '0') {
            $this->allowstime = true;
            $time = new Myslot();
            $time->category = "Time";
            $time->grade = "opt";
            $time->type = "time";
            $time->slotPuntsInicials();
            
            $this->hasslot["Time"] = true;
            $this->slotarray["Time"] = $time;
        }
        
        // Slot EXPRESSIONS
        if ($patternbbdd->expressio == '1') $this->needexpr = true;
        else if ($patternbbdd->expressio != "") {
            $aux = array();
            $aux[0] = $patternbbdd->expressio;
            $aux[1] = '0';
            $this->exprsarray[] = $aux;
        }
        $this->hasslot["Expression"] = true;
                                
    }
    
    public function forceFillSlot($slotname, $word, $penalty, $indexclassfinalword)
    {       
        // DEBUG
        // echo $slotname." ".$word->text."<br /><br />";
        
        $word->slotfinal = $slotname;
                
        $word->used = true; // marquem la paraula com ja adjudicada
        
        $slotaux = new Myslot();
        
        $slotaux = &$this->slotarray[$slotname];
                
        $slotaux->paraulafinal = $word; 
                
        $slotaux->puntsguanyats = $slotaux->puntsfinal - $penalty;
        
        $slotaux->puntsfinal = $penalty;
        
        $slotaux->full = true;
        
        $slotaux->indexclassfinalword = $indexclassfinalword;
                
        // $this->slotarray[$slotname]->paraulestemp = array();
    }
    
    // Les noves keys de l'array de slots acabaran en 1 ó 2, 
    // depenent de si venen del pattern principal o el secundari
    public function fusePatterns($pattern2)
    {        
        $CI = &get_instance();
        $CI->load->library('Myslot');
                
        $auxpatternresultat = new Mypattern();
                        
        foreach ($this->slotarray as $slots1) {
                        
            // Si l'slot és el subverb, insertem els slots del 2on pattern
            if ($slots1->type == "verb" && $slots1->category != "Main Verb") {
                
                foreach ($pattern2->slotarray as $slots2) {
                    $slots2->level = 2;
                    $slots2->parent = $slots1->category;
                    if ($slots2->category == "Main Verb") {
                        $slots2->category = "Secondary Verb";
                        // i passem, si n'hi ha, la preposició que ha d'anar davant del subverb
                        if ($slots1->prep != null) {
                            $slots2->prep = $slots1->prep;
                        }
                    }
                    $auxpatternresultat->slotarray[$slots2->category." 2"] = $slots2;
                    $auxpatternresultat->hasslot[$slots2->category." 2"] = true;
                }
            }
            else {
                $auxpatternresultat->slotarray[$slots1->category." 1"] = $slots1;
                $auxpatternresultat->hasslot[$slots1->category." 1"] = true;
            }
        }
                
        $this->slotarray = array();
        $this->slotarray = $auxpatternresultat->slotarray;
                        
        $this->hasslot = array();
        $this->hasslot = $auxpatternresultat->hasslot;
        
        // Passem les propietats del pattern del subverb
        $this->id2 = $pattern2->id;
        $this->idverb2 = $pattern2->idverb;
        $this->pronominal2 = $pattern2->pronominal;
        $this->pseudoimpersonal2 = $pattern2->pseudoimpersonal;
        
        $this->tipusfrase2 = $pattern2->tipusfrase;
        $this->defaulttense2 = $pattern2->defaulttense;
        $this->verbpeticio2 = $pattern2->verbpeticio;
        
        $this->allowstime = ($this->allowstime || $pattern2->allowstime);
        $this->needexpr = ($this->needexpr || $pattern2->needexpr);
        
        // S'acumulen les expressions, si n'hi ha
        foreach ($pattern2->exprsarray as $expr) {
            $this->exprsarray[] = $expr;
        }
                
    }
    
    // Posa la partícula a l'slot corresponent
    public function fillPartPregunta($particula)
    {  
        $CI = &get_instance();
        $CI->load->library('Myslot');
        
        $classe1 = null;
        $classe2 = null;
        $classeaux1 = "null";
        $classeaux2 = "null";
        $particulabona = false;
        
        $numclasses = count($particula->classes);
        
        for($i=0; $i<$numclasses; $i++) {
            if ($i==0) $classe1 = $particula->classes[0];
            else if ($i==1) $classe2 = $particula->classes[1];
        }
        
        if ($classe1 == null && $classe2 == null) {
            $particulabona = true; // és una partícula que no va a un slot
                        
            // creem un slot per la particula
            $slotpartpregunta = new Myslot();
            $slotpartpregunta->category = "PartPreguntaNoSlot";
            $slotpartpregunta->grade = '1';
            $slotpartpregunta->type = "questpart";
            $slotpartpregunta->full = true;
            $slotpartpregunta->paraulafinal = $particula;
            $slotpartpregunta->puntsfinal = -7;
            $slotpartpregunta->level = 1;
            
            $keyaux = "PartPreguntaNoSlot";
            if (isset($this->slotarray["Secondary Verb 2"])) $keyaux .= " 1";
            $this->slotarray[$keyaux] = $slotpartpregunta;
            
        }
        else if ($numclasses >= 1) {
            if ($this->subverb) { 
                $classeaux1 = $classe1." 2";
                $classe1 .= " 1";
                $classeaux2 = $classe2." 2";
                $classe2 .= " 1";
            }
            
            // Provem si hi ha la mena d'slot i omplim l'slot, primer del subverb, si n'hi ha, i primer de la 1era classe
            if (array_key_exists($classeaux1, $this->slotarray)) {
                    $this->forceFillSlot($classeaux1, $particula, 0, 0);
                    $particulabona = true;
            }
            else if (array_key_exists($classe1, $this->slotarray)) {
                $this->forceFillSlot($classe1, $particula, 0, 0);
                $particulabona = true;
            }
            else if (array_key_exists($classeaux2, $this->slotarray)) {
                $this->forceFillSlot($classeaux2, $particula, 0, 1);
                $particulabona = true;
            }
            else if (array_key_exists($classe2, $this->slotarray)) {
                $this->forceFillSlot($classe2, $particula, 0, 1);
                $particulabona = true;
            }
        }
                
        $this->partpreguntabona = $particulabona;
        return $particulabona;
    }
    
    
    public function solveNouns($arrayNouns)
    {        
        $unusedNouns = array(); // noms no utilitzats a la primera ronda d'intents de fit
        $usedNouns = array();
        
        $numNouns = count($arrayNouns);
                
        for ($i=0; $i<$numNouns; $i++) {
            
            $word = $arrayNouns[$i];
                        
            foreach ($this->slotarray as $keyslot => $slot) {
                                
                $fittype = 0; // 1, si fit slot, 0 si no
                
                // passem el key de l'slot per si de cas hi ha subverb i els slots són de l'estil "Theme 1|2"
                $fittype = $slot->nounFitsSlot($word, $keyslot);
                
                if ($fittype == 0) $unusedNouns[] = $word; // si no ha pogut anar a cap slot, no el fem servir
                else $usedNouns[] = $word;
                
            }
            
            // MIREM SI LA PARAULA NOMÉS HA POGUT FER FILL D'UN ÚNIC SLOT PER FER EL BLOCK CHAINING
            // SI L'ÚNIC SLOT ÉS DE NC JA SE LI ASSIGNA
            $this->chainBlockingSlotsType1($word, "NC");
        }
                
        // QUAN JA TENIM POSADES TOTES LES PARAULES, RESOLEM ELS SLOTS ENCARA NO RESOLTS
        // HI HAURÂ SLOTS AMB JA NOMÉS UNA PARAULA O ALTRES SLOTS AMB VÀRIES PARAULES
        
        $mand2level = array();
        $mand1level = array();
        $subjectes = array();
        $opts = array();
        
        // Ordenem els slots en differents arrays Mandatory 2on nivell, Mandatory 1er nivell, subjecte, opts
        foreach ($this->slotarray as $keyslot => $slot) {
            
            if ($slot->level == 2) {
                if ($slot->category == 'Subject') $subjectes[$keyslot] = $slot;
                else if ($slot->grade == '1') $mand2level[$keyslot] = $slot;
                else $opts[] = $slot;
            }
            else {
                if ($slot->category == 'Subject') $subjectes[$keyslot] = $slot;
                else if ($slot->grade == '1') $mand1level[$keyslot] = $slot;
                else $opts[$keyslot] = $slot;
            }
        }
        
        // ordenem els slots per importància, per anar-los desambiguant per ordre
        $this->calculateVirtualOrder();
        $this->disambiguateSlotsNew("NC");
        
        // Primer els obligatoris de 2on nivell
        // $this->disambiguateSlots($mand2level, "NC");
        // Després els obligatoris del 1er nivell
        // $this->disambiguateSlots($mand1level, "NC");
        // Després els de subjecte
        // $this->disambiguateSlots($subjectes, "NC");
        // Finalment els optatius
        // $this->disambiguateSlots($opts, "NC");
        
        $CI = &get_instance();
        $langnouncorder = $CI->session->userdata('uinterfacelangncorder');
        
        $langtype = $CI->session->userdata('uinterfacelangtype');
        $svo = true;
        if ($langtype != 'svo') $svo = false;
                
        // un cop desambiguats tots els slots, veiem si alguna paraula aniria millor com a NC
        // en comptes de com a opt.
        // Per cada slot optatiu mirem si la paraula que fa el fit podia fer de NC
        foreach ($opts as $keyslotopt => $slotopt) {
            // Només si l'slot és full i no té un altre complement assignat, que aleshores no el podem eliminar
            if ($slotopt->full && !$slotopt->NCassigned) {
                
                // si no era un perfect fill o si els dos noms anaven contigus abans del verb (en cas de SVO)
                // i la frase no era pseudoimpersonal o de pregunta que tenen els valors de subverb invertits
                if ($slotopt->puntsfinal > 0  || 
                        ($svo && $slotopt->paraulafinal->beforeverb && !$this->pseudoimpersonal && !$this->partpreguntabona)) {
                    
                    $wordfill = $slotopt->paraulafinal;
                                                            
                    $i=0;
                    $chosencompl = false;
                    
                    // trobem si aquella paraula podia fer de NC d'una altra paraula
                    while($i<count($wordfill->slotstemps) && !$chosencompl) {
                        
                        $keycompl = -1;
                        $keyaux = null;
                    
                        $keyaux = $wordfill->slotstemps[$i];
                        
                        // DEBUG
                        // echo $wordfill->text." ".$keyaux."<br /><br />";
                                                
                        // Si és de compl. de nom (cada paraula només pot ser NC d'una altra paraula: de la precedent)
                        if(strpos($keyaux, "NC")) $keycompl = $i;
                        $i++;
                                            
                        if ($keycompl != -1) {
                            $auxstring = explode(" ", $keyaux);

                            // agafem el key de l'slot que podia tenir el NC
                            $numpartskeycompl = count($auxstring);
                            if ($numpartskeycompl > 0) {
                                $keyparent = null;
                                // per si el compl era d'un slot de 2on nivell
                                if ($numpartskeycompl == 4) $keyparent = $auxstring[0]." ".$auxstring[1];
                                else $keyparent = $auxstring[0];

                                // mirem si és d'un slot que està ple i que la paraula que l'omple sigui la que complia la condició
                                // de ser l'anterior a la paraula que fa de complement (si langnouncorder == 1) o posterior (si langnouncorder == 0)
                                if ($this->slotarray[$keyparent]->full && 
                                        (($langnouncorder == '1' && ($this->slotarray[$keyparent]->paraulafinal->inputorder - $wordfill->inputorder == -1))
                                        || ($langnouncorder == '0' && ($this->slotarray[$keyparent]->paraulafinal->inputorder - $wordfill->inputorder == 1)))) {

                                    // fem el canvi: dessassignem slotopt
                                    $slotopt->full = false;
                                    $slotopt->paraulafinal = null;
                                    $slotopt->puntsfinal = 7;
                                    $slotopt->indexclassfinalword = 0;

                                    // posem que complementa a l'altra slot com a NC
                                    $wordfill->slotfinal = $keyparent;
                                    $wordfill->used = true; // en principi ja estava a true
                                    // indiquem que ara aquest slot té un NC assignat
                                    $this->slotarray[$keyparent]->NCassigned = true;
                                    $this->slotarray[$keyparent]->NCassignedkey = $keyaux;
                                    $chosencompl = true;
                                }
                            }
                        }
                    }
                }
            }
        } // Fi de per cada slot optatiu mirar si la paraula que el fit fa millor de NC
        
        // Si han quedat noms sense slot, mirar si poden fer de NC
        for ($i=0; $i<$numNouns; $i++) {
            $word = $arrayNouns[$i];
            
            if ($word->slotfinal == null) {
            
                $slotstemps = $word->slotstemps;
                
                foreach ($slotstemps as $num => $keyslot) {
                    if (strpos($keyslot, " NC")) {
                        
                        $str = explode(" NC", $keyslot);
                        
                        // si l'slot on podia fer de complement existeix, té un nucli i 
                        // no té ja un complement assignat
                        if (isset($this->slotarray[$str[0]]) && $this->slotarray[$str[0]]->full
                                && !$this->slotarray[$str[0]]->NCassigned) {
                            
                            // comprovem que el nucli estés a la distància de NC
                            if ((($langnouncorder == '1' && $this->slotarray[$str[0]]->paraulafinal->inputorder == $word->inputorder - 1))
                                    || (($langnouncorder == '0' && $this->slotarray[$str[0]]->paraulafinal->inputorder == $word->inputorder + 1))) {
                            
                                $word->used = true;
                                // indiquem que ara l'slot superior té un NC assignat
                                $this->slotarray[$str[0]]->NCassigned = true;
                                $this->slotarray[$str[0]]->NCassignedkey = $keyslot;

                                // un cop trobem el que compleix les condicions, ja no seguim amb el foreach
                                break;
                            }
                        }
                    }
                }
            }
        } // fi si han quedat noms sense slot
    }
    
    public function solveAdverbs($arrayadverbs)
    {
        $unusedAdverbs = array(); // adverbis no utilitzats a la primera ronda d'intents de fit
        $usedAdverbs = array();
        
        $numAdverbs = count($arrayadverbs);
                        
        for ($i=0; $i<$numAdverbs; $i++) {
            
            $word = $arrayadverbs[$i];
            
            // Si és un adverbi de temps, només pot anar a un slot de temps
            if ($word->isClass("temps")) {
                if ($this->allowstime) {
                    $this->timeexpr[] = $word;
                    $word->used = true;
                    $word->slotfinal = "Time Expr"; // està a l'array d'expressions de temps
                    $usedAdverbs[] = $word;
                }
                else $unusedAdverbs[] = $word;
            }
            else { // Pels altres adverbis
                foreach ($this->slotarray as $keyslot => $slot) {
                                
                    $fittype = 0; // 1, si fit slot, 0 si no

                    // passem el key de l'slot per si de cas hi ha subverb i els slots són de l'estil "Theme 1|2"
                    $fittype = $slot->adverbFitsSlot($word, $keyslot);

                    if ($fittype == 0) $unusedAdverbs[] = $word; // si no ha pogut anar a cap slot, no el fem servir
                    else $usedAdverbs[] = $word;
                }

                // MIREM SI LA PARAULA NOMÉS HA POGUT FER FILL D'UN ÚNIC SLOT PER FER EL BLOCK CHAINING
                $this->chainBlockingSlotsType1($word, "ADV");
            }
                        
        }
        
        // QUAN JA TENIM POSADES TOTES ELS ADVERBIS, RESOLEM ELS SLOTS ENCARA NO RESOLTS
        // HI HAURÂ SLOTS AMB JA NOMËS UNA PARAULA O ALTRES SLOTS AMB VÀRIES PARAULES
        
        $this->calculateVirtualOrder();
        $this->disambiguateSlotsNew("ADV");
                
        // un cop desambiguats tots els slots, veiem si alguna paraula aniria millor com a NC ADV
        // en comptes de com a opt.
        for ($i=0; $i<$numAdverbs; $i++) {
            
            $indexmillor = -1;
            $bestdistance = 1000;
            $bestpoints = 1000;
           
            $word = $arrayadverbs[$i];
            // si la paraula està en un slot (no de temps), mirem si aquest slot és opt i el comparem amb els
            // slots als que l'adv pot fer de complement, si no, compararem només els slots als 
            // que pot fer de complement entre ells
            if ($word->used && !$word->isClass("temps")) {
                $keyslotaux = $word->slotfinal;
                
                $slotaux = $this->slotarray[$keyslotaux];
                if ($slotaux->grade == "opt") {
                    $bestdistance = 0;
                    $bestpoints = $slotaux->puntsfinal;
                }
            }
            
            for ($j=0; $j<count($word->slotstemps); $j++) {
                $aux = explode(" ADV", $word->slotstemps[$j]);
                
                // comprovem que l'slot sigui del tipus ADV que complementa a nom, ja que
                // els altres opts als que pugui fer fit, com ara els de manner, també hi seran
                if (count($aux) > 1) {
                
                    $keyslotnoun = $aux[0];
                    $slotnoun = $this->slotarray[$keyslotnoun];

                    // si no té ja un adverbi assignat
                    if (!$slotnoun->CAdvassigned) {

                        $slotadv = $slotnoun->cmpAdvs[$word->slotstemps[$j]];
                        $distance = $slotnoun->paraulafinal->inputorder - $word->inputorder;
                        $points = $slotadv->puntsfinal + abs($distance);

                        if ($points < $bestpoints) {
                            $bestdistance = $distance;
                            $bestpoints = $points;
                            $indexmillor = $word->slotstemps[$j];
                        }
                        else if ($points == $bestpoints) {
                            // en cas d'empat ens quedem amb la que tenia distància positiva, 
                            // que vol dir un adverbi de lloc que vagi abans del nom al que complementa
                            if ($distance > $bestdistance) {
                                $bestdistance = $distance;
                                $bestpoints = $points;
                                $indexmillor = $word->slotstemps[$j];
                            }
                        }
                    }
                }
            }
            
            // si hem trobat un slot millor fem la substitució
            if ($indexmillor != -1) {
                
                // si l'adverbi que hem seleccionat feia fill a un altre slot (optatiu, com hem comprovat abans)
                // que no fos ja de complement el desassignem a l'slot que feia fill
                if ($word->slotfinal != null && !strpos($word->slotfinal, "ADV")) {

                    $keyslotfinal = $word->slotfinal;
                    $this->slotarray[$keyslotfinal]->full = false;
                    $this->slotarray[$keyslotfinal]->paraulafinal = null;
                    $this->slotarray[$keyslotfinal]->puntsfinal = 7;
                    $this->slotarray[$keyslotfinal]->indexclassfinalword = 0;
                }
                
                $word->slotfinal = $indexmillor;
                if (!$word->used) $usedAdverbs[] = $word;
                $word->used = true;
                $word->assignadaAComplement = true;
                
                $aux2 = explode(" ADV", $indexmillor);
                $keyslotnoun = $aux2[0];                
                
                $this->slotarray[$keyslotnoun]->CAdvassigned = true;
                $this->slotarray[$keyslotnoun]->CAdvassignedkey = $indexmillor;
                // treiem la preposició que anava davant de l'slot, si n'hi havia
                $this->slotarray[$keyslotnoun]->prep = null;

                // no cal que l'esborrem de les altres cues de complements xq només acceptàvem paraules que no estessin
                // ja assignades a un altre complement
                
            }
            
        } // fi for per cada adverbi
                
    }
    
    
    public function solveAdjs($arrayAdjs)
    {
        $unusedAdjs = array(); // noms no utilitzats a la primera ronda d'intents de fit
        $usedAdjs = array();
        
        $numAdjs = count($arrayAdjs);
                        
        // Per cada adjectiu
        for ($i=0; $i<$numAdjs; $i++) {
            
            $word = $arrayAdjs[$i];
                        
            foreach ($this->slotarray as $keyslot => $slot) {
                               
                $fittype = 0; // 1, si fit slot, 0 si no
                                
                // passem el key de l'slot per si de cas hi ha subverb i els slots són de l'estil "Theme 1|2"
                $fittype = $slot->adjFitsSlot($word, $keyslot);
                
                if ($fittype == 0) $unusedAdjs[] = $word; // si no ha pogut anar a cap slot, no el fem servir
                else $usedAdjs[] = $word;
                
            }
            
            // MIREM SI LA PARAULA NOMÉS HA POGUT FER FILL D'UN ÚNIC SLOT PER FER EL BLOCK CHAINING
            $this->chainBlockingSlotsType1($word, "ADJ");
        }
        
        // QUAN JA TENIM POSADES TOTES LES PARAULES, RESOLEM ELS SLOTS ENCARA NO RESOLTS
        // HI HAURÂ SLOTS AMB JA NOMËS UNA PARAULA O ALTRES SLOTS AMB VÀRIES PARAULES
        
        $mand2level = array();
        $mand1level = array();
        $subjectes = array();
        $opts = array();
        
        // Ordenem els slots en differents arrays Mandatory 2on nivell, Mandatory 1er nivell, subjecte, opts
        foreach ($this->slotarray as $keyslot => $slot) {
            
            if ($slot->level == 2) {
                if ($slot->category == 'Subject') $subjectes[$keyslot] = $slot;
                else if ($slot->grade == '1') $mand2level[$keyslot] = $slot;
                else $opts[] = $slot;
            }
            else {
                if ($slot->category == 'Subject') $subjectes[$keyslot] = $slot;
                else if ($slot->grade == '1') $mand1level[$keyslot] = $slot;
                else $opts[$keyslot] = $slot;
            }
        }
        
        $this->calculateVirtualOrder();
        $this->disambiguateSlotsNew("ADJ");
        
        // un cop desambiguats tots els slots, veiem a quin slot fan millor de complements
        // els adjs que no estan omplint ja un slot obligatori
        // si estan omplint un slot opt, mirem si és millor com a slotopt o com a complement
        // PER CADA ADJ
        foreach ($arrayAdjs as $wordadj) {
            
            $slotfinalobl = false;
            $slotfinalopt = false;
            $keyslotfinal = $wordadj->slotfinal;
            
            // mirem si l'slot final, que no sigui de tipus complement, és obligatori o optatiu
            if ($keyslotfinal != null) {
                if (!strpos($keyslotfinal, "ADJ")) {
                    if ($this->slotarray[$keyslotfinal]->grade == '1') {
                        $slotfinalobl = true;
                    }
                    else if ($this->slotarray[$keyslotfinal]->grade == 'opt') {
                        $slotfinalopt = true;
                    }
                }
            }
            
            // si no és obligatori, busquem el millor slot al que pot complementar l'adj
            if (!$slotfinalobl) {
                
                $puntsmillor = -1000;
                $keymillor = null;
                $keyparentmillor = null;
                                
                // per cada slot al llistat de temporals
                foreach ($wordadj->slotstempsext as $auxtupla) {
                    
                    $keyaux = $auxtupla[0];
                    $auxpunts = $auxtupla[1];
                        
                    // en cas d'empat volem els slot complements que entren més tard que els opts
                    if ($auxpunts > $puntsmillor) {

                        // extreiem també la key del parent slot al que complementa
                        $auxstring = explode(" ", $keyaux);
                        $keyparentaux;

                        // agafem el key de l'slot que podia tenir el NC
                        $numpartskeycompl = count($auxstring);
                        if ($numpartskeycompl > 0) {
                            // per si el compl era d'un slot de 2on nivell
                            if ($numpartskeycompl == 4) $keyparentaux = $auxstring[0]." ".$auxstring[1];
                            else $keyparentaux = $auxstring[0];
                        }
                        
                        // si l'slot no té ja assignat un altre adj el podem escollir provisionalment
                        if (!$this->slotarray[$keyparentaux]->CAdjassigned) {
                            $puntsmillor = $auxpunts;
                            $keymillor = $keyaux;
                            $keyparentmillor = $keyparentaux;
                        }                        
                    }
                }
                
                // si hem trobat el millor slot, el posem com a final de la paraula 
                // i a l'slot parent el posem al llistat de modificadors assignats
                if ($puntsmillor > -1000) {
                    
                    // si omplia un slot opt, mirem si el fit és millor de complement que opt
                    if ($slotfinalopt) {
                        
                        $slotopt = $this->slotarray[$keyslotfinal];
                        
                        // si el fit és millor, fem tot el procés de desassignar i assignar
                        if ($puntsmillor >= $slotopt->puntsguanyats) {
                            
                            // desassignem
                            $slotopt->full = false;
                            $slotopt->paraulafinal = null;
                            $slotopt->puntsfinal = 7;
                            $slotopt->indexclassfinalword = 0;
                            
                            // assignem
                            $wordadj->slotfinal = $keymillor;
                            $wordadj->used = true;
                            $wordadj->assignadaAComplement = true;

                            $slotparent = $this->slotarray[$keyparentmillor];
                            $slotparent->CAdjassigned = true;
                            $slotparent->CAdjassignedkey = $keymillor;
                        }
                    }
                    else {                        
                        $wordadj->slotfinal = $keymillor;
                        $wordadj->used = true;
                        $wordadj->assignadaAComplement = true;

                        $slotparent = $this->slotarray[$keyparentmillor];
                        $slotparent->CAdjassigned = true;
                        $slotparent->CAdjassignedkey = $keymillor;
                    }
                }
            }
        } // Fi de buscar per cada adj, a on fan millor de complement
     
    }
    
    public function solveModifs($arrayModifs)
    {
        $unusedModifs = array(); // noms no utilitzats a la primera ronda d'intents de fit
        $usedModifs = array();
        
        $numModifs = count($arrayModifs);
        
        $verbless = false;
                        
        // Per cada modificador
        for ($i=0; $i<$numModifs; $i++) {
            
            $word = $arrayModifs[$i];
            
            // si és de frase, i no és verbless, l'assignem al verb principal i ja està
            if ($word->tipus == "modifier" && $word->propietats->scope == "phrase" && $this->defaulttense != "verbless") {
                
                $keymainverb = "Main Verb";
                
                if (isset ($this->slotarray["Main Verb 1"])) $keymainverb = "Main Verb 1";
                
                $slotverb = $this->slotarray[$keymainverb];
                
                $nummodsslot = count($slotverb->cmpMod);

                $newslot = new Myslot();
                $word->slotfinal = $keymainverb." MOD ".$nummodsslot; // per dir que està de compl.
                $newslot->category = $slotverb->category." MOD";
                $newslot->grade = "opt";
                $newslot->full = true;
                $newslot->paraulafinal = $word;
                $newslot->level = $slotverb->level + 1;
                $newslot->parent = $keymainverb;
                $newslot->puntsfinal = 7; // Són els punts que resten un NC sobre 100
                $newslot->indexclassfinalword = 0;

                $slotverb->cmpMod[$keymainverb." MOD ".$nummodsslot] = $newslot;
                $slotverb->CModassigned = true;
                $slotverb->CModassignedkey[] = $keymainverb." MOD ".$nummodsslot;

                $word->used = true;
                $word->assignadaAComplement = true;
                
            }
            
            else {
                
                foreach ($this->slotarray as $keyslot => $slot) {
                                
                    $fittype = 0; // 1, si fit slot, 0 si no

                    // passem el key de l'slot per si de cas hi ha subverb i els slots són de l'estil "Theme 1|2"
                    $fittype = $slot->modifFitsSlot($word, $keyslot);

                    if ($fittype == 0) $unusedModifs[] = $word; // si no ha pogut anar a cap slot, no el fem servir
                    else $usedModifs[] = $word;
                }

                // MIREM SI LA PARAULA NOMÉS HA POGUT FER FILL D'UN ÚNIC SLOT PER FER EL BLOCK CHAINING
                $this->chainBlockingSlotsType1($word, "MOD");
                
            }
                        
        }
        
        // QUAN JA TENIM POSADES TOTES LES PARAULES, RESOLEM ELS SLOTS ENCARA NO RESOLTS
        // HI HAURÂ SLOTS AMB JA NOMËS UNA PARAULA O ALTRES SLOTS AMB VÀRIES PARAULES
        
        $this->calculateVirtualOrder();
        $this->disambiguateSlotsNew("MOD");
                        
        // un cop desambiguats tots els slots, veiem a quin slot fan millor de complements
        // els modifs que no estan omplint ja un slot obligatori
        // PER CADA MODIF
        
        foreach ($arrayModifs as $wordmodif) {
            
            $slotfinalobl = false;
            $slotfinalopt = false;
            $slotfinaloptpunts = -1000;
            $keyslotfinal = $wordmodif->slotfinal;
            
            // mirem si l'slot final, que no sigui de tipus complement, és obligatori o optatiu
            if ($keyslotfinal != null) {
                if (!strpos($keyslotfinal, "MOD")) {
                    if ($this->slotarray[$keyslotfinal]->grade == '1') {
                        $slotfinalobl = true;
                    }
                    else if ($this->slotarray[$keyslotfinal]->grade == 'opt') {
                        $slotfinalopt = true;
                        $slotfinaloptpunts = $this->slotarray[$keyslotfinal]->puntsguanyats-0; 
                        // -0 -> en cas d'empat ja agafa el complement vs l'slot optatiu 
                        // amb -1 ho penalitzaríem més perquè en cas d'altres empats 
                        // es quedés amb el complement i no amb l'slot optatiu
                    }
                }
            }
            
            // si no és obligatori, busquem el millor slot al que pot complementar el modif
            // i si aquest slot supera a l'slot opt que ja hi ha
            if (!$slotfinalobl) {
                
                $puntsmillor = $slotfinaloptpunts;
                $keymillor = null;
                $keyparentmillor = null;
                $slotmillorat = false;
                
                // per cada slot al llistat de temporals
                foreach ($wordmodif->slotstempsext as $auxtupla) {
                                        
                    $keyaux = $auxtupla[0];
                    $auxpunts = $auxtupla[1];
                    
                    // si és de tipus complement
                    if (strpos($keyaux, "MOD")) {
                        
                        if ($auxpunts > $puntsmillor) {
                            // extreiem també la key del parent slot al que complementa
                            $auxstring = explode(" ", $keyaux);
                            $keyparentprov;

                            // agafem el key de l'slot que podia tenir el NC
                            $numpartskeycompl = count($auxstring);
                            if ($numpartskeycompl > 0) {
                                // per si el compl era d'un slot de 2on nivell
                                if ($numpartskeycompl == 4) $keyparentprov = $auxstring[0]." ".$auxstring[1];
                                else $keyparentprov = $auxstring[0];
                            }
                            
                            // si no té un adverbi que ja el quantifiqui
                            if (isset($this->slotarray[$keyparentprov]) && !$this->slotarray[$keyparentprov]->CAdvassigned) {
                                $puntsmillor = $auxpunts;
                                $keymillor = $keyaux;
                                $keyparentmillor = $keyparentprov;
                                $slotmillorat = true;
                            }
                        }
                    }
                }
                
                // si hem trobat el millor slot, el posem com a final de la paraula 
                // i a l'slot parent el posem al llistat de modificadors assignats
                if ($slotmillorat) {
                    
                    // si omplia un slot opt, desassignem la paraula d'aquest slot
                    if ($slotfinalopt) {
                        
                        $slotopt = $this->slotarray[$keyslotfinal];
                        
                        $slotopt->full = false;
                        $slotopt->paraulafinal = null;
                        $slotopt->puntsfinal = 7;
                        $slotopt->indexclassfinalword = 0;
                    }
                    
                    $wordmodif->slotfinal = $keymillor;
                    $wordmodif->used = true;
                    $wordmodif->assignadaAComplement = true;
                    
                    $slotparent = $this->slotarray[$keyparentmillor];
                    $slotparent->CModassigned = true;
                    $slotparent->CModassignedkey[] = $keymillor;
                }
            }
        } // Fi de buscar per cada modif, a on fan millor de complement
                       
    }
    
    
    function calculateVirtualOrder()
    {
        $this->virtualslotsort = array();
        
        foreach ($this->slotarray as $keyslot => $slot) {
            
            $aux = array();
            $aux[0] = $keyslot;
            $aux[1] = $slot->slotCalcPuntsGuanyats();
            $aux[2] = $slot->grade;
            $aux[3] = $slot->level;
            
            if (!$slot->full && count($slot->paraulestemp) > 0) $this->virtualslotsort[] = $aux;
        }
                
        $this->mySort($this->virtualslotsort);
        
        // DEBUG:
        // print_r($this->virtualslotsort);
        // echo "<br /><br />";
        
    }
    
    function mySort($virtualslotorder)
    {
        $auxorder = array();
        
        for ($i=0; $i<count($virtualslotorder); $i++) {
            
            $infoaux = $virtualslotorder[$i]; 
            $j=0;
            $indexinsert = 0;
            $found = false;
            
            while(!$found) {
                
                $numauxorder = count($auxorder);
                
                // si hem arribat al final d'auxorder
                if ($j == $numauxorder) {
                    $indexinsert = $j;
                    $found = true;
                }
                else {
                    $infoactual = $auxorder[$j];
                    // si és més gran volem que l'inserti abans
                    if ($infoaux[1] > $infoactual[1]) {
                        $indexinsert = $j;
                        $found = true;
                    }
                    else if ($infoaux[1] == $infoactual[1]) {
                        
                        // DEBUG:
                        // echo "Infoaux: "; print_r($infoaux); echo "/ Infoactual: "; print_r($infoactual);
                        
                        
                        // en cas d'empat, si és són del mateix grade i del mateix nivell o el Receiver té més grade, 
                        // el receiver té prioritat sobre el qualsevol slot, excepte el Theme, si no és pseudoimpersonal
                        if (strpos($infoaux[0], "Receiver") === 0 && !($infoaux[3] == 2 && (strpos($infoactual[0], "Subject 1") === 0))
                                && !($infoaux[2] == "opt" && $infoactual[2] == '1') 
                                && !(strpos($infoactual[0], "Theme") === 0) && !$this->pseudoimpersonal) {
                            $indexinsert = $j;
                            $found = true;
                        }
                        // els slots secundaris tenen preferència sobre els de primer nivell
                        // menys si és el subjecte
                        else if (strpos($infoaux[0], "2") != 0 && !(strpos($infoactual[0], "Subject") === 0) && !$this->pseudoimpersonal) {
                            $indexinsert = $j;
                            $found = true;
                        }
                        // si és pseudoimpersonal el Receiver 1 té més importància que els slots de segon nivell
                        else if ($this->pseudoimpersonal && strpos($infoaux[0], "Receiver") === 0 && strpos($infoactual[0], "2") != 0) {
                            $indexinsert = $j;
                            $found = true;    
                        }
                    }
                }
                $j++;
            }
            
            array_splice($auxorder, $indexinsert, 0, array($infoaux)); // construim l'array ordenat
        }
        $this->virtualslotsort = $auxorder;
    }
    
    // Per quan una paraula només fa fit a 1 slot (no NC, ADJ, ADV, MOD)
    function chainBlockingSlotsType1($word, $stringCMP)
    {        
        
        if (count($word->slotstemps) == 1) {
                            
            $slotkey = $word->slotstemps[0];
            $word->slotfinal = $slotkey;
            $word->slotstemps = array(); // esborrem el llistat d'slots
            
            // DEBUG
            // echo $word->text;
            
            if (isset($this->slotarray[$slotkey])) { // si trobem l'slot dins el pattern. Els NC no es troben
                                                     // però com que se'n crea un per paraula, ja estan tractats
                
                $slotaux = $this->slotarray[$slotkey];
                
                if (!$slotaux->full) { // si no ha estat ja tractat
                    
                    // Passem la paraula com a final de l'slot (+ tot el que calgui) i bloquegem l'slot
                    $indexinslot = $slotaux->searchIndexWordInSlot($word);
                    
                    $penalty = $slotaux->paraulestemp[$indexinslot][1];
                    
                    $indexclassfinalword = $slotaux->paraulestemp[$indexinslot][2];
                    
                    $this->forceFillSlot($slotkey, $word, $penalty, $indexclassfinalword);
                    
                    // Esborrem la paraula del llistat temporal i reiniciem els index de l'array de paraulestemp
                    // unset($slotaux->paraulestemp[$indexinslot]);
                    
                    array_splice($slotaux->paraulestemp, $indexinslot, 1);
                    // $slotaux->paraulestemp = array_values($slotaux->paraulestemp);
                    
                    $wordaux;
                    
                    // per cada paraula que hi havia al llistat temporal
                    for ($i=0; $i<count($slotaux->paraulestemp); $i++) {
                        
                        $wordaux = $slotaux->paraulestemp[$i][0];
                        
                        // Buscar l'slot, esborrar l'slot i fer chainblocking de la paraula
                        $indexslot = $wordaux->searchSlotIndex($slotkey);
                        
                        if ($indexslot != -1) {
                            array_splice($wordaux->slotstemps, $indexslot, 1);
                            // $wordaux->slotstemps = array_values($wordaux->slotstemps);
                            
                            $this->chainBlockingSlotsType1($wordaux, $stringCMP);
                        }
                    }
                }
            }
            else if ($stringCMP == "NC") { // si era de NC
                
                $word->used = true;
                $keyparent = null;
                $auxstring = explode(" ", $slotkey);
                // agafem el key de l'slot que podia tenir el NC
                $numpartskeycompl = count($auxstring);
                if ($numpartskeycompl > 0) {
                    // per si el compl era d'un slot de 2on nivell
                    if ($numpartskeycompl == 4) $keyparent = $auxstring[0].$auxstring[1];
                    else $keyparent = $auxstring[0];
                }
                
                if ($keyparent != null) {

                    // indiquem que ara l'slot superior té un NC assignat
                    $this->slotarray[$keyparent]->NCassigned = true;
                    $this->slotarray[$keyparent]->NCassignedkey = $slotkey;
                }
            }
        }
    }
    
    
    // Per quan una paraula podia anar a varis slots i un és el seleccionat com a millor opció
    function chainBlockingSlotsType2($word)
    {
        
    }
    
    
    /*function disambiguateSlots($arraySlots, $stringCMP)
    {
        foreach ($arraySlots as $keyslot => $slot) {
            $indexselect = -1;
            $penalty = 1000;
            
            if (!$slot->full && count($slot->paraulestemp) > 0) {
                // per cada paraula que podia anar a l'slot busquem la que fa millor fit
                for ($i=0; $i<count($slot->paraulestemp); $i++) {
                    if ($slot->paraulestemp[$i][1] < $penalty) {
                        $penalty = $slot->paraulestemp[$i][1];
                        $indexselect = $i;
                    }
                    // si dues paraules estan empatades i omplen igual de bé l'slot
                    else if ($slot->paraulestemp[$i][1] == $penalty) {
                        if ($slot->category != "Subjecte") { // si l'slot no és de categoria subjecte
                            if (!$slot->paraulestemp[$i][0]->beforeverb && $slot->paraulestemp[$indexselect][0]->beforeverb) {
                                $penalty = $slot->paraulestemp[$i][1];
                                $indexselect = $i;
                            }
                            // potser no cal xq les paraules han estat introduïdes en ordre als slots
                            else if ($slot->paraulestemp[$i][0]->inputorder < $slot->paraulestemp[$indexselect][0]->inputorder) {
                                $penalty = $slot->paraulestemp[$i][1];
                                $indexselect = $i;
                            }
                        }
                        else { // si és de categoria subjecte
                            // només fem el canvi si la nova és before verb i l'altre no
                            if ($slot->paraulestemp[$i][0]->beforeverb && !$slot->paraulestemp[$indexselect][0]->beforeverb) {
                                $penalty = $slot->paraulestemp[$i][1];
                                $indexselect = $i;
                            }
                        }
                    }
                }

                // Ja tenim la paraula seleccionada
                $wordaux = $slot->paraulestemp[$indexselect][0];
                $this->forceFillSlot($keyslot, $wordaux, $slot->paraulestemp[$indexselect][1], $slot->paraulestemp[$indexselect][2]);
                
                // esborrem la paraula del llistat de paraulestemp de l'slot
                array_splice($slot->paraulestemp, $indexselect, 1);
                
                // esborrem l'slot del llistat d'slots temps de la paraula
                $indexslotaux = $wordaux->searchSlotIndex($keyslot);
                if ($indexslotaux != -1) array_splice($wordaux->slotstemps, $indexslotaux, 1);
                
                // per la resta d'slots que quedin esborrem la paraula del llistat de paraulestemp
                for ($i=0; $i<count($wordaux->slotstemps); $i++) {
                    $keyaux = $wordaux->slotstemps[$i];
                    // Si no és de tipus NC que tractarem més endavant
                    if (!strpos($keyaux, $stringCMP)) {
                        $slotaux = $this->slotarray[$keyaux];
                        $indexinslot = $slotaux->searchIndexWordInSlot($wordaux);

                        // esborrem la paraula seleccionada
                        if ($indexinslot != -1) array_splice($slotaux->paraulestemp, $indexinslot, 1);
                    }
                }
                
                // per la resta de paraules que podien anar a l'slot esborrem l'slot del llistat de slotstemps
                for ($i=0; $i<count($slot->paraulestemp); $i++) {
                    $wordaux2 = $slot->paraulestemp[$i][0];
                    $indexslotaux2 = $wordaux2->searchSlotIndex($keyslot);
                    
                    if ($indexslotaux2 != -1) array_splice($wordaux2->slotstemps, $indexslotaux2, 1);
                }
            }   // Fi si l'slot no estava ja ple i almenys tenia alguna paraula que podia fer fit         
        } // Fi de per cada slot
    }
    */
    
    function disambiguateSlotsNew($stringCMP)
    {
        $CI = &get_instance();
        
        // agafem el tipus d'idioma, ja que per desambiguar si l'idioma és svo
        // les paraules que vagin abans del verb tindran punts extra per fer de subjecte
        // i les que vagin darrere per fer dels altres slots
        $langtype = $CI->session->userdata('uinterfacelangtype');
        
        $svo = true;
        if ($langtype != 'svo') $svo = false;
        
        $numslots = count($this->virtualslotsort);
        
        $slotsleft = true;
        if ($numslots == 0) $slotsleft = false;
        
        while ($slotsleft) {
            
            // DEBUG
            // echo "Pattern id ".$this->id.": ";
            // print_r($this->virtualslotsort); echo '<br />';
                                            
            $infoslot = $this->virtualslotsort[0];
            $keyslot = $infoslot[0];
            $slot = $this->slotarray[$keyslot];
            
            $indexselect = -1;
            $penalty = 1000;
                        
            if (!$slot->full && count($slot->paraulestemp) > 0) {
                
                // per cada paraula que podia anar a l'slot busquem la que fa millor fit
                for ($i=0; $i<count($slot->paraulestemp); $i++) {
                    // si la paraula no ha estat ja utilitzada
                    if (!$slot->paraulestemp[$i][0]->used) {
                    
                        if ($slot->paraulestemp[$i][1] < $penalty) {
                            $penalty = $slot->paraulestemp[$i][1];
                            $indexselect = $i;
                        }
                        // si dues paraules estan empatades i omplen igual de bé l'slot
                        else if ($slot->paraulestemp[$i][1] == $penalty) {
                            // si l'idioma té estructura SVO
                            if ($svo) {
                                if ($slot->category != "Subjecte") { // si l'slot no és de categoria subjecte
                                    if (!$slot->paraulestemp[$i][0]->beforeverb && $slot->paraulestemp[$indexselect][0]->beforeverb) {
                                        $penalty = $slot->paraulestemp[$i][1];
                                        $indexselect = $i;
                                    }
                                    // potser no cal xq les paraules han estat introduïdes en ordre als slots
                                    else if ($slot->paraulestemp[$i][0]->inputorder < $slot->paraulestemp[$indexselect][0]->inputorder) {
                                        $penalty = $slot->paraulestemp[$i][1];
                                        $indexselect = $i;
                                    }
                                }
                                else { // si és de categoria subjecte
                                    // només fem el canvi si la nova és before verb i l'altre no
                                    if ($slot->paraulestemp[$i][0]->beforeverb && !$slot->paraulestemp[$indexselect][0]->beforeverb) {
                                        $penalty = $slot->paraulestemp[$i][1];
                                        $indexselect = $i;
                                    }
                                }
                            }
                            // si no té estructura SVO
                            else {
                                // es quedarà amb la primera paraula que podia fer fit a l'slot
                                if ($slot->paraulestemp[$i][0]->inputorder < $slot->paraulestemp[$indexselect][0]->inputorder) {
                                    $penalty = $slot->paraulestemp[$i][1];
                                    $indexselect = $i;
                                }
                            }
                        }
                    }
                }
                
                if ($indexselect == -1 ) $indexselect = 0;
                                
                // Ja tenim la paraula seleccionada
                $wordaux = $slot->paraulestemp[$indexselect][0];
                $this->forceFillSlot($keyslot, $wordaux, $slot->paraulestemp[$indexselect][1], $slot->paraulestemp[$indexselect][2]);
                                
                // esborrem la paraula del llistat de paraulestemp de l'slot
                array_splice($slot->paraulestemp, $indexselect, 1);
                                                
                // esborrem l'slot del llistat d'slots temps de la paraula
                $indexslotaux = $wordaux->searchSlotIndex($keyslot);
                if ($indexslotaux != -1) array_splice($wordaux->slotstemps, $indexslotaux, 1);
                                
                // per la resta d'slots que quedin esborrem la paraula del llistat de paraulestemp
                for ($i=0; $i<count($wordaux->slotstemps); $i++) {
                    $keyaux = $wordaux->slotstemps[$i];
                    // Si no és de tipus NC que tractarem més endavant
                    if (!strpos($keyaux, $stringCMP)) {
                        $slotaux = $this->slotarray[$keyaux];
                        $indexinslot = $slotaux->searchIndexWordInSlot($wordaux);
                        
                        // esborrem la paraula seleccionada
                        if ($indexinslot != -1) array_splice($slotaux->paraulestemp, $indexinslot, 1);
                    }
                }
                
                // per la resta de paraules que podien anar a l'slot esborrem l'slot del llistat de slotstemps
                for ($i=0; $i<count($slot->paraulestemp); $i++) {
                    $wordaux2 = $slot->paraulestemp[$i][0];
                    $indexslotaux2 = $wordaux2->searchSlotIndex($keyslot);
                    
                    if ($indexslotaux2 != -1) array_splice($wordaux2->slotstemps, $indexslotaux2, 1);
                }
                $slot->paraulestemp = array(); // eliminem totes les paraules que podien anar a l'slot
            }   // Fi si l'slot no estava ja ple i almenys tenia alguna paraula que podia fer fit    
            
            // recalculem la preferència dels slots
            $this->calculateVirtualOrder();
                        
            if (count($this->virtualslotsort) == 0) $slotsleft = false;
            
        } // Fi de per cada slot
    }
    
    public function calcPuntsFinalPattern()
    {
        // ens diu si una paraula introduïda no s'ha utilitzat al patró
        $paraulanoposada = false;
                
        foreach ($this->slotarray as $slot) {
                        
            if ($slot->full) {
                // compensem que si un slot obligatori és un perfect fill, que el triï sobre un slot opt d'un altre patró
                if ($slot->grade == '1' && $slot->puntsfinal == 0) $this->puntuaciofinal += 1;
                else $this->puntuaciofinal -= $slot->puntsfinal;
                
                // echo $slot->category.": ".$slot->puntsfinal." (".$slot->paraulafinal->text.')<br />';
                // punts de coordinacions
                $wordaux = $slot->paraulafinal;
                if (count($wordaux->paraulacoord) > 0) $this->puntuaciofinal += 7;
                
                if ($slot->NCassigned) {
                    $slotcomp = $slot->complements[$slot->NCassignedkey];
                    $this->puntuaciofinal += $slotcomp->puntsfinal;
                    
                    // echo $slot->NCassignedkey.": ".$slotcomp->puntsfinal.'<br />';
                    // punts de coordinacions
                    $wordaux = $slotcomp->paraulafinal;
                    if ($wordaux->paraulacoord != null) $this->puntuaciofinal += 7;
                }
                if ($slot->CAdvassigned) {
                    $slotcomp = $slot->cmpAdvs[$slot->CAdvassignedkey];
                    $this->puntuaciofinal += $slotcomp->puntsfinal;
                    
                    // echo $slot->CAdvassignedkey.": ".$slotcomp->puntsfinal.'<br />';
                    // punts de coordinacions
                    $wordaux = $slotcomp->paraulafinal;
                    if (count($wordaux->paraulacoord) > 0) $this->puntuaciofinal += 7;
                }
                if ($slot->CAdjassigned) {
                    $slotcomp = $slot->cmpAdjs[$slot->CAdjassignedkey];
                    $this->puntuaciofinal += $slotcomp->puntsfinal;
                    
                    // echo $slot->CAdjassignedkey.": ".$slotcomp->puntsfinal.'<br />';
                    // punts de coordinacions
                    $wordaux = $slotcomp->paraulafinal;
                    if (count($wordaux->paraulacoord) > 0) $this->puntuaciofinal += 7;
                }
                if ($slot->CModassigned) {
                    // un slot pot tenir varis modificadors assignats
                    foreach ($slot->CModassignedkey as $keymod) {
                        $slotcomp = $slot->cmpMod[$keymod];
                        $this->puntuaciofinal += $slotcomp->puntsfinal;
                        // punts de coordinacions
                        $wordaux = $slotcomp->paraulafinal;
                        if (count($wordaux->paraulacoord) > 0) $this->puntuaciofinal += 7;
                    }
                }
                
                // sumem les expressions de temps (advs)
                $numtemps = count($this->timeexpr);
                for ($i=0; $i<$numtemps; $i++) $this->puntuaciofinal += 7;
                
                $numexprs = count($this->exprsarray);
                
                // si necessitava una expressió i no hi és, li restem 25 punts
                if ($this->needexpr && $numexprs == 0) $this->puntuaciofinal -= 25;
                // si no, sumem 1 punt per expressió
                else {
                    for ($i=0; $i<$numexprs; $i++) $this->puntuaciofinal += 1;
                }
                
            }
            else {
                // restem els punts dels slots no plens
                // els optatius de time, manera, locat, locfrom que la majoria poden tenir no els restem
                // perquè si no els patterns amb pocs slots, que no els tenen tindrien avantatge
                if ($slot->grade == '1' || ($slot->category != "Manner" && $slot->category != "Time" 
                        && $slot->category != "LocAt" && $slot->category != "LocFrom" && $slot->category != "Company" && $slot->category != "Tool")) {
                    $this->puntuaciofinal -= $slot->puntsfinal;
                    // echo $slot->category.": -".$slot->puntsfinal.'<br />';
                }
                // que resti un punt pels slots anteriors no plens
                else if ($slot->category == "Company" || $slot->category == "Tool") {
                    $this->puntuaciofinal -= 1;
                }
            }
        } // Fi per cada slot
                
        // restem els punts de les paraules no fetes servir (les expressions no conten i
        // les preguntes tampoc (aquestes ja es tracten prèviament si no hi ha lloc per la
        // partícula de la pregunta)
        for ($i=0; $i<count($this->paraules); $i++) {
            if ($this->paraules[$i]->used == false && $this->paraules[$i]->tipus != "expression"
                    && $this->paraules[$i]->tipus != "questpart") {
                $this->puntuaciofinal -= 25;
                $paraulanoposada = true;
            }
        }
        
        // retorna un array on [0] = puntuaciofinal i [1] = si no ha pogut posar alguna paraula
        $aux = array();
        $aux[0] = $this->puntuaciofinal;
        $aux[1] = $paraulanoposada;
                
        return $aux;
    }


    // Imprimir el pattern
    public function printPattern()
    {
        $string = "";
        
        $string = "+++++++++++BEGIN PATTERN+++++++++++++<br /><br />";
        
        $string .= "Score: ".$this->puntuaciofinal.' --> Pattern ID: '.$this->id.'<br /><br />';
        
        foreach ($this->slotarray as $keyslot => $slot) {
            
            if ($slot->full) {
                $string .= "Slot: ".$keyslot." = ".$slot->paraulafinal->text;
                // tractem les coordinacions
                $wordaux = $slot->paraulafinal;
                if (count($wordaux->paraulacoord) > 0) {
                    for ($k=0; $k<count($wordaux->paraulacoord); $k++) {
                        $string .= " (i ".$wordaux->paraulacoord[$k]->text.")";
                    }
                }
                
                if ($slot->NCassigned) {
                    $slotcomp = $slot->complements[$slot->NCassignedkey];
                    $string .= " --> NC = ".$slotcomp->paraulafinal->text;
                    // tractem les coordinacions
                    $wordaux = $slotcomp->paraulafinal;
                    if (count($wordaux->paraulacoord) > 0) {
                        for ($k=0; $k<count($wordaux->paraulacoord); $k++) {
                            $string .= " (i ".$wordaux->paraulacoord[$k]->text.")";
                        }
                    }
                }
                if ($slot->CAdvassigned) {
                    $slotcomp = $slot->cmpAdvs[$slot->CAdvassignedkey];
                    $string .= " --> ADV = ".$slotcomp->paraulafinal->text;
                    // tractem les coordinacions
                    $wordaux = $slotcomp->paraulafinal;
                    if (count($wordaux->paraulacoord) > 0) {
                        for ($k=0; $k<count($wordaux->paraulacoord); $k++) {
                            $string .= " (i ".$wordaux->paraulacoord[$k]->text.")";
                        }
                    }                }
                if ($slot->CAdjassigned) {
                    $slotcomp = $slot->cmpAdjs[$slot->CAdjassignedkey];
                    $string .= " --> ADJ = ".$slotcomp->paraulafinal->text;
                    // tractem les coordinacions
                    $wordaux = $slotcomp->paraulafinal;
                    if (count($wordaux->paraulacoord) > 0) {
                        for ($k=0; $k<count($wordaux->paraulacoord); $k++) {
                            $string .= " (i ".$wordaux->paraulacoord[$k]->text.")";
                        }
                    }
                }
                if ($slot->CModassigned) {
                    // un slot pot tenir varis modificadors assignats
                    foreach ($slot->CModassignedkey as $keymod) {
                        $slotcomp = $slot->cmpMod[$keymod];
                        $string .= " --> MOD = ".$slotcomp->paraulafinal->text;
                        // tractem les coordinacions
                        $wordaux = $slotcomp->paraulafinal;
                        if (count($wordaux->paraulacoord) > 0) {
                            for ($k=0; $k<count($wordaux->paraulacoord); $k++) {
                                $string .= " (i ".$wordaux->paraulacoord[$k]->text.")";
                            }
                        }
                    }
                }
                $string .= "<br /><br />";
            }
        }
        
        // escrivim els adverbis de temps
        $numtemps = count($this->timeexpr);
        if ($numtemps > 0) {
            $string .= "Slot: Time Expr = ";
            for ($i=0; $i<$numtemps; $i++) {
                $string .= $this->timeexpr[$i]->text."; ";
            }
            $string .= '<br /><br />';
        }
        
        // escrivim les expressions
        $numexpr = count($this->exprsarray);
        if ($numexpr > 0) {
            $string .= "Slot: Expressions = ";
            for ($i=0; $i<$numexpr; $i++) {
                $string .= $this->exprsarray[$i][0]."; ";
            }
            $string .= '<br /><br />';
        }
        
        $string .= "+++++++++++END PATTERN++++++++++++++<br />";
        
        return $string;
    }
    
    public function printAllPattern()
    {
        // PER DEBUG
        echo "<br />++++++++++++++++++++BEGIN PATTERN+++++++++++++++++++++++<br /><br />";
        
        foreach ($this->slotarray as $keyslot => $slot) {
            
                echo "Slot: ".$keyslot." = ".$slot->puntsfinal.' / '.$slot->puntsguanyats;
                if ($slot->full) echo " FULL";
                if (count($slot->cmpAdjs) > 0) {
                    foreach ($slot->cmpAdjs as $slotcomp) {
                       echo " --> ADJ = ".$slotcomp->paraulafinal->text; 
                    }
                }
                echo "<br /><br />";
        }
        
        echo "++++++++++++++++++++END PATTERN++++++++++++++++++++++++<br /><br /><br />";
    }
    
    
    /*
     * FUNCIONS PEL GENERADOR
     */
    
    // Ordena els slots de la frase: ÉS EL PRIMER PAS DEL GENERADOR
    public function ordenarSlotsFrase($propietatsfrase)
    {
        $CI = &get_instance();
        $CI->load->library('Mymatching');
        $CI->load->library('Myslot');
        $CI->load->library('Myword');
        $matching = new Mymatching();
        
        //agafem si l'usuari parla en masculí o en femení pel generador
        if ($CI->session->userdata('isfem') == '1') $this->isfem = true;
        
        // agafem si la frase és negativa
        $this->frasenegativa = $propietatsfrase['negativa'];
                
        // ADVS TEMPS

        // mirar adverbis de temps, per si n'hi ha dels que van a davant de la frase
        $numadvstemps = count($this->timeexpr);
        
        for ($i=0; $i<$numadvstemps; $i++) {
            $wordaux = $this->timeexpr[$i];
            
            if ($matching->isFrontAdvTemps($wordaux->text)) {

                // el posem davant de la frase
                $this->frasefinal .= $wordaux->text." ";
            }
        }
        
        
        // SI SUBJ2 == JO -> SUBJ2 = SUBJ1
        
        // si hi ha subverb i el subjecte 2 no està definit, si el defvalue era "jo", que el subj2 s'ompli
        // amb el subj1 (defvalue o word que fa fill)
        if ($this->subverb) {
            if (isset($this->slotarray["Subject 2"])) {
                if (!$this->slotarray["Subject 2"]->full && $this->slotarray["Subject 2"]->defvalue == '1') {
                    
                    // si el subj1 tindrà un defvalue assignat passem aquest valor al subj2
                    if (isset($this->slotarray["Subject 1"]) && !$this->slotarray["Subject 1"]->full) {
                        $this->slotarray["Subject 2"]->defvalue = $this->slotarray["Subject 1"]->defvalue;
                    }
                    // si el subj 1 té una paraula que l'omple, passem la paraula al subj2
                    else if (isset($this->slotarray["Subject 1"])) {
                        $this->slotarray["Subject 2"]->full = true;
                        $this->slotarray["Subject 2"]->paraulafinal = $this->slotarray["Subject 1"]->paraulafinal;
                        $this->slotarray["Subject 2"]->indexclassfinalword = $this->slotarray["Subject 1"]->indexclassfinalword;
                    }
                    
                }
            }
        }
        
        
        // ORDRE NORMAL
        
        $counter = 0;
        $indexmainverb = 0;
        $indexsecondaryverb = null;
        $indexpartpregunta = null;
        
        // posem tots els slots en l'ordre normal
        foreach ($this->slotarray as $keyslot => $slot) {
            
            if ($slot->full || $slot->grade == '1') {
                // si no estava ple, vol dir que era un obligatori i l'omplim amb el valor per defecte
                if (!$slot->full) {
                    // si la frase és una ordre o una pregunta, el subj per defecte passa a ser "tu"
                    if (($propietatsfrase['tipusfrase'] == "ordre" || $propietatsfrase['tipusfrase'] == "pregunta") 
                            && $slot->category == "Subject" && $slot->level == 1) {
                        $slot->defvalue = '2';
                    }
                    $slot->full = true;
                    $slot->defvalueused = true;
                }
                // busquem si hi ha una partícula de pregunta, que només poden ser a slots plens
                else {
                    if ($slot->category == "PartPreguntaNoSlot" || $slot->paraulafinal->tipus == "questpart") {
                        $indexpartpregunta = $counter;
                    }
                }
                if ($slot->category == "Main Verb") $indexmainverb = $counter;
                if ($slot->category == "Secondary Verb") $indexsecondaryverb = $counter;
                
                $this->ordrefrase[] = $keyslot;
                $counter += 1;
            }
        }
        
        
        // MODIFICADORS INICI DE FRASE
        
        // tractem els modificadors de frase que estan enganxats al verb principal
        $keymainverb = "Main Verb";
        if (isset($this->slotarray["Secondary Verb 2"])) $keymainverb .= " 1";
        $slotmainverb = $this->slotarray[$keymainverb];
        $nummodifsfrase = count($slotmainverb->CModassignedkey);
        for ($i=0; $i<$nummodifsfrase; $i++) {
            $keymodifaux = $slotmainverb->CModassignedkey[$i];
            $slotmodifaux = $slotmainverb->cmpMod[$keymodifaux];
            
            // si no és del grup que va darrere el subjecte el posem al principi de la frase (ex: si, perquè, però)
            if (!$matching->isModAfterSubj($slotmodifaux->paraulafinal->text)) {
                
                $this->frasefinal = " ".$slotmodifaux->paraulafinal->text." ".$this->frasefinal;
            }
            // marquem si la frase és negativa pel modificador no
            else if ($slotmodifaux->paraulafinal->text == "no") $this->frasenegativa = true;
        }
        
        // THEME PRONOMINAL HO, JO, TU
        
        $indextheme1 = null;
        $indextheme2 = null;
                
        // busquem si té un slot de theme que sigui pronominal "ho", "jo", "tu"
        for($i=0; $i<count($this->ordrefrase); $i++) {
            
            $slotaux = $this->slotarray[$this->ordrefrase[$i]];
            if ($slotaux->category == "Theme") {
                // si està en forma de pronom
                if ($slotaux->defvalueused && ($slotaux->defvalue == "ho" || $slotaux->defvalue == "jo"
                        || $slotaux->defvalue == "tu")) {
                    if ($slotaux->level == 1) $indextheme1 = $i;
                    if ($slotaux->level == 2) $indextheme2 = $i;
                }
            }
        }
        
        if ($indextheme1 != null) {
            $temp = $this->ordrefrase[$indextheme1];
            // esborrem el theme 1 per moure'l de lloc
            array_splice($this->ordrefrase, $indextheme1, 1);
            
            // si la frase és un ordre i no és negativa
            if ($propietatsfrase['tipusfrase'] == "ordre" && !$this->frasenegativa) {
                // l'insertem just després del main verb
                array_splice($this->ordrefrase, $indexmainverb+1, 0, $temp);
            }
            else {
                // l'insertem just abans del main verb
                array_splice($this->ordrefrase, $indexmainverb, 0, $temp);
            }
        }
        // fem el mateix amb el theme 2, si hi és
        if ($indextheme2 != null) {
            $temp = $this->ordrefrase[$indextheme2];
            // esborrem el theme 1 per moure'l de lloc
            array_splice($this->ordrefrase, $indextheme2, 1);
            // l'insertem just abans del verb secundari
            array_splice($this->ordrefrase, $indexsecondaryverb, 0, $temp);
        }
        
        
        // RECEIVER PRONOMINAL
        // anirà abans que un theme pronominal, per això va després a l'algoritme
        
        $indexreceiver1 = null;
        $indexreceiver2 = null;
                
        // busquem si té un slot de receiver que sigui pronominal i el posem abans del verb principal
        for($i=0; $i<count($this->ordrefrase); $i++) {
            
            $slotaux = $this->slotarray[$this->ordrefrase[$i]];
            if ($slotaux->category == "Receiver") {
                $wordslotauxfinal = $slotaux->paraulafinal;
                // si està en forma de pronom (només de tu, jo)
                if ($slotaux->defvalueused || $wordslotauxfinal->isClass("pronoun")) {
                    if ($slotaux->level == 1) $indexreceiver1 = $i;
                    if ($slotaux->level == 2) $indexreceiver2 = $i;
                }
            }
        }
        
        if ($indexreceiver1 != null) {
            $temp = $this->ordrefrase[$indexreceiver1];
            // esborrem el receiver 1 per moure'l de lloc
            array_splice($this->ordrefrase, $indexreceiver1, 1);
            
            // si la frase és un ordre
            if ($propietatsfrase['tipusfrase'] == "ordre" && !$this->frasenegativa) {
                // l'insertem just després del main verb
                array_splice($this->ordrefrase, $indexmainverb+1, 0, $temp);
            }
            else {
                // l'insertem just abans del main verb
                array_splice($this->ordrefrase, $indexmainverb, 0, $temp);
            }
        }
        // fem el mateix amb el receiver 2, si hi és
        if ($indexreceiver2 != null) {
            $temp = $this->ordrefrase[$indexreceiver2];
            // esborrem el receiver 1 per moure'l de lloc
            array_splice($this->ordrefrase, $indexreceiver2, 1);
            // l'insertem just abans del verb secundari
            array_splice($this->ordrefrase, $indexsecondaryverb, 0, $temp);
        }
        
        // THEME PRONOMINAL -> JO / TU / ELL... si no té una preposició davant
                
        $indextheme1 = null;
        $indextheme2 = null;
                
        // busquem si té un slot de theme que sigui pronominal i el posem abans del verb principal
        for($i=0; $i<count($this->ordrefrase); $i++) {
            
            $slotaux = $this->slotarray[$this->ordrefrase[$i]];
                        
            if ($slotaux->category == "Theme" && $slotaux->prep == null) {
                                
                $wordslotauxfinal = $slotaux->paraulafinal;
                // si està en forma de pronom
                if (!$slotaux->defvalueused && $wordslotauxfinal->isClass("pronoun")) {
                    if ($slotaux->level == 1) $indextheme1 = $i;
                    if ($slotaux->level == 2) $indextheme2 = $i;
                }
            }
        }
        
        if ($indextheme1 != null) {
            $temp = $this->ordrefrase[$indextheme1];
            // esborrem el receiver 1 per moure'l de lloc
            array_splice($this->ordrefrase, $indextheme1, 1);
            
            // si la frase és un ordre
            if ($propietatsfrase['tipusfrase'] == "ordre" && !$this->frasenegativa) {
                // l'insertem just després del main verb
                array_splice($this->ordrefrase, $indexmainverb+1, 0, $temp);
            }
            else {
                // l'insertem just abans del main verb
                array_splice($this->ordrefrase, $indexmainverb, 0, $temp);
            }
        }
        // fem el mateix amb el receiver 2, si hi és
        if ($indextheme2 != null) {
            $temp = $this->ordrefrase[$indextheme2];
            // esborrem el receiver 1 per moure'l de lloc
            array_splice($this->ordrefrase, $indextheme2, 1);
            // l'insertem just abans del verb secundari
            array_splice($this->ordrefrase, $indexsecondaryverb, 0, $temp);
        }
        
                
        // DESIRE
        
        // si és un desig afegir el verb voler a un slot Desire
        if ($propietatsfrase['tipusfrase'] == "desig") {
                        
            $slotvoler = new Myslot();
            $slotvoler->category = "Desire";
            $slotvoler->type = "verb";
            $slotvoler->full = true;
            $slotvoler->level = 0;
            
            $auxtupla[0] = "vull";
            $auxtupla[1] = null;
            
            $slotvoler->slotstring[] = $auxtupla;
            
            $this->slotarray["Desire"] = $slotvoler;
            array_unshift($this->ordrefrase, "Desire");
            
            // afegir si us plau: de moment NO
            // $aux = array();
            // $aux[0] = "si us plau";
            // $aux[1] = '0';
            // $this->exprsarray[] = $aux;
        }
        
        
        // PERMISSION
        
        // si és un permís afegir el verb poder a un slot Permission
        if ($propietatsfrase['tipusfrase'] == "permis") {
                        
            $slotpoder = new Myslot();
            $slotpoder->category = "Permission";
            $slotpoder->type = "verb";
            $slotpoder->full = true;
            $slotpoder->level = 0;
            
            $auxtupla[0] = "puc";
            $auxtupla[1] = null;
            
            $slotpoder->slotstring[] = $auxtupla;
            
            $this->slotarray["Permission"] = $slotpoder;
            array_unshift($this->ordrefrase, "Permission");
            // afegir si us plau
            $aux = array();
            $aux[0] = "si us plau";
            $aux[1] = '0';
            $this->exprsarray[] = $aux;
        }
        
        
        // CONDITIONAL PHRASE
        
        // si és una frsse condicional, afegim el "si" a davant (però darrere els advs de temps
        // i modificadors de frase que vagin a l'inici de frase)
        if ($propietatsfrase['tipusfrase'] == "condicional") {
            
            $this->frasefinal .= "si ";
        }
        
        
        // QUESTION PARTICLE
        
        // si hi ha una partícula de pregunta, posem el seu slot a davant de tot
        if ($indexpartpregunta != null) {
            $temp = $this->ordrefrase[$indexpartpregunta];
            // esborrem l'slot de la part pregunta per moure'l de lloc
            array_splice($this->ordrefrase, $indexpartpregunta, 1);
            // l'insertem a l'inici de la frase
            array_splice($this->ordrefrase, 0, 0, $temp);
        }
        
        
        // SI HI HA UNA PREGUNTA DE "QUANT"
        if (isset($this->slotarray["PartPreguntaNoSlot"])) {
            
            if ($this->slotarray["PartPreguntaNoSlot"]->paraulafinal->text == "quant") {
                // si hi ha dos verbs a la frase, només passem el theme 2 davant del subjecte
                if (isset($this->slotarray["Secondary Verb 2"])) {
                    
                    $indexsubject1 = -1;
                    $indextheme2 = -1;
                    
                    for ($i=0; $i<count($this->ordrefrase); $i++) {
                        if ($this->ordrefrase[$i] == "Theme 2") $indextheme2 = $i;
                        if ($this->ordrefrase[$i] == "Subject 1") $indexsubject1 = $i;
                    }
                    
                    // pot ser que el verb no tingués theme 2, tot i que el "quant" aleshores no
                    // tindria sentit
                    if ($indexsubject1 != -1 && $indextheme2 != -1) {
                        
                        $temp = $this->ordrefrase[$indextheme2];
                        // esborrem l'slot del theme per moure'l de lloc
                        array_splice($this->ordrefrase, $indextheme2, 1);
                        // l'insertem a l'inici de la frase
                        array_splice($this->ordrefrase, $indexsubject1, 0, $temp); 
                        
                        $this->questiontypequant = true;
                    }
                }
                // si només hi ha un verb, fem swap de theme i subject
                else {
                    $indexsubject = -1;
                    $indextheme = -1;
                    
                    for ($i=0; $i<count($this->ordrefrase); $i++) {
                        if ($this->ordrefrase[$i] == "Theme") $indextheme = $i;
                        if ($this->ordrefrase[$i] == "Subject") $indexsubject = $i;
                    }
                    // si tenia subjecte i theme
                    if ($indexsubject != -1 && $indextheme != -1) {
                        $auxslotname = $this->ordrefrase[$indexsubject]; // guardem el subj
                        // posem el theme al subj
                        $this->ordrefrase[$indexsubject] = $this->ordrefrase[$indextheme];
                        // i el subj guardat al theme
                        $this->ordrefrase[$indextheme] = $auxslotname;
                        
                        $this->questiontypequant = true;
                    }
                }                
            }
        }
        // SI HI HA QUALSEVOL ALTRA PREGUNTA: passem el subjecte al final de la frase
        else if ($indexpartpregunta != null) {
            $indexsubject1 = -1;
            if (isset($this->slotarray["Secondary Verb 2"])) {
                    for ($i=0; $i<count($this->ordrefrase); $i++) {
                        if ($this->ordrefrase[$i] == "Subject 1") $indexsubject1 = $i;
                    }
            }
            else {
                for ($i=0; $i<count($this->ordrefrase); $i++) {
                        if ($this->ordrefrase[$i] == "Subject") $indexsubject1 = $i;
                }
            }
            if ($indexsubject1 != -1) {
               $temp = $this->ordrefrase[$indexsubject1];
                // esborrem l'slot del subjecte per moure'l de lloc
                array_splice($this->ordrefrase, $indexsubject1, 1);
                // l'insertem al final de la frase
                $this->ordrefrase[] = $temp;  
            }
        }
        
        // IMPERATIVE
        if ($propietatsfrase['tipusfrase'] == "ordre" && !$this->frasenegativa) {
            // afegir si us plau
            $aux = array();
            $aux[0] = "si us plau";
            $aux[1] = '0';
            $this->exprsarray[] = $aux;
        }
           
        // DEBUG
        // echo $this->printOrdreFrase()."<br /><br />";
    }
    
    // Ordena els slots de la frase: ÉS EL PRIMER PAS DEL GENERADOR
    public function ordenarSlotsFraseES($propietatsfrase)
    {
        $CI = &get_instance();
        $CI->load->library('Mymatching');
        $CI->load->library('Myslot');
        $CI->load->library('Myword');
        $matching = new Mymatching();
        
        // agafem si la frase és negativa
        $this->frasenegativa = $propietatsfrase['negativa'];
        
        //agafem si l'usuari parla en masculí o en femení pel generador
        if ($CI->session->userdata('isfem') == '1') $this->isfem = true;
                
        // ADVS TEMPS

        // mirar adverbis de temps, per si n'hi ha dels que van a davant de la frase
        $numadvstemps = count($this->timeexpr);
        
        for ($i=0; $i<$numadvstemps; $i++) {
            $wordaux = $this->timeexpr[$i];
            
            if ($matching->isFrontAdvTempsES($wordaux->text)) {

                // el posem davant de la frase
                $this->frasefinal .= $wordaux->text." ";
            }
        }
        
        
        // SI SUBJ2 == JO -> SUBJ2 = SUBJ1
        
        // si hi ha subverb i el subjecte 2 no està definit, si el defvalue era "jo", que el subj2 s'ompli
        // amb el subj1 (defvalue o word que fa fill)
        if ($this->subverb) {
            if (isset($this->slotarray["Subject 2"])) {
                if (!$this->slotarray["Subject 2"]->full && $this->slotarray["Subject 2"]->defvalue == '1') {
                    
                    // si el subj1 tindrà un defvalue assignat passem aquest valor al subj2
                    if (isset($this->slotarray["Subject 1"]) && !$this->slotarray["Subject 1"]->full) {
                        $this->slotarray["Subject 2"]->defvalue = $this->slotarray["Subject 1"]->defvalue;
                    }
                    // si el subj 1 té una paraula que l'omple, passem la paraula al subj2
                    else if (isset($this->slotarray["Subject 1"])) {
                        $this->slotarray["Subject 2"]->full = true;
                        $this->slotarray["Subject 2"]->paraulafinal = $this->slotarray["Subject 1"]->paraulafinal;
                        $this->slotarray["Subject 2"]->indexclassfinalword = $this->slotarray["Subject 1"]->indexclassfinalword;
                    }
                    
                }
            }
        }
        
        
        // ORDRE NORMAL
        
        $counter = 0;
        $indexmainverb = 0;
        $indexsecondaryverb = null;
        $indexpartpregunta = null;
        
        // posem tots els slots en l'ordre normal
        foreach ($this->slotarray as $keyslot => $slot) {
            
            if ($slot->full || $slot->grade == '1') {
                // si no estava ple, vol dir que era un obligatori i l'omplim amb el valor per defecte
                if (!$slot->full) {
                    // si la frase és una ordre o una pregunta, el subj per defecte passa a ser "tu"
                    if (($propietatsfrase['tipusfrase'] == "ordre" || $propietatsfrase['tipusfrase'] == "pregunta") 
                            && $slot->category == "Subject" && $slot->level == 1) {
                        $slot->defvalue = '2';
                    }
                    $slot->full = true;
                    $slot->defvalueused = true;
                }
                // busquem si hi ha una partícula de pregunta, que només poden ser a slots plens
                else {
                    if ($slot->category == "PartPreguntaNoSlot" || $slot->paraulafinal->tipus == "questpart") {
                        $indexpartpregunta = $counter;
                    }
                }
                if ($slot->category == "Main Verb") $indexmainverb = $counter;
                if ($slot->category == "Secondary Verb") $indexsecondaryverb = $counter;
                
                $this->ordrefrase[] = $keyslot;
                $counter += 1;
            }
        }
        
        
        // MODIFICADORS INICI DE FRASE
        
        // tractem els modificadors de frase que estan enganxats al verb principal
        $keymainverb = "Main Verb";
        if (isset($this->slotarray["Secondary Verb 2"])) $keymainverb .= " 1";
        $slotmainverb = $this->slotarray[$keymainverb];
        $nummodifsfrase = count($slotmainverb->CModassignedkey);
        for ($i=0; $i<$nummodifsfrase; $i++) {
            $keymodifaux = $slotmainverb->CModassignedkey[$i];
            $slotmodifaux = $slotmainverb->cmpMod[$keymodifaux];
            
            // si no és del grup que va darrere el subjecte el posem al principi de la frase (ex: si, perquè, pero)
            if (!$matching->isModAfterSubjES($slotmodifaux->paraulafinal->text)) {
                
                $this->frasefinal = " ".$slotmodifaux->paraulafinal->text." ".$this->frasefinal;
            }
            /**
             * INFO LANGUAGE DEPENDENT!!
             */
            // marquem si la frase és negativa pel modificador no
            else if ($slotmodifaux->paraulafinal->text == "no") $this->frasenegativa = true;
        }
        
        // THEME PRONOMINAL LO, TÚ, YO
        
        $indextheme1 = null;
        $indextheme2 = null;
                
        // busquem si té un slot de theme que sigui pronominal "lo", "tú", "yo"
        for($i=0; $i<count($this->ordrefrase); $i++) {
            
            $slotaux = $this->slotarray[$this->ordrefrase[$i]];
            if ($slotaux->category == "Theme") {
                // si està en forma de pronom
                if ($slotaux->defvalueused && ($slotaux->defvalue == "lo" || $slotaux->defvalue == "tú"
                        || $slotaux->defvalue == "yo")) {
                    if ($slotaux->level == 1) $indextheme1 = $i;
                    if ($slotaux->level == 2) $indextheme2 = $i;
                }
            }
        }
        
        if ($indextheme1 != null) {
            $temp = $this->ordrefrase[$indextheme1];
            // esborrem el theme 1 per moure'l de lloc
            array_splice($this->ordrefrase, $indextheme1, 1);
            
            // si la frase és una ordre i no és negativa
            if ($propietatsfrase['tipusfrase'] == "ordre" && !$this->frasenegativa) {
                // l'insertem just després del main verb
                array_splice($this->ordrefrase, $indexmainverb+1, 0, $temp);
            }
            else {
                // l'insertem just abans del main verb
                array_splice($this->ordrefrase, $indexmainverb, 0, $temp);
            }
        }
        // fem el mateix amb el theme 2, si hi és
        if ($indextheme2 != null) {
            $temp = $this->ordrefrase[$indextheme2];
            // esborrem el theme 2 per moure'l de lloc
            array_splice($this->ordrefrase, $indextheme2, 1);
            // l'insertem just abans del verb secundari
            array_splice($this->ordrefrase, $indexsecondaryverb, 0, $temp);
        }
        
        
        // RECEIVER PRONOMINAL
        // anirà abans que un theme pronominal, per això va després a l'algoritme
        
        $indexreceiver1 = null;
        $indexreceiver2 = null;
                
        // busquem si té un slot de receiver que sigui pronominal i el posem abans del verb principal
        for($i=0; $i<count($this->ordrefrase); $i++) {
            
            $slotaux = $this->slotarray[$this->ordrefrase[$i]];
            if ($slotaux->category == "Receiver") {
                $wordslotauxfinal = $slotaux->paraulafinal;
                // si està en forma de pronom (només de tu, jo)
                if ($slotaux->defvalueused || $wordslotauxfinal->isClass("pronoun")) {
                    if ($slotaux->level == 1) $indexreceiver1 = $i;
                    if ($slotaux->level == 2) $indexreceiver2 = $i;
                }
            }
        }
        
        if ($indexreceiver1 != null) {
            $temp = $this->ordrefrase[$indexreceiver1];
            // esborrem el receiver 1 per moure'l de lloc
            array_splice($this->ordrefrase, $indexreceiver1, 1);
            
            // si la frase és un ordre
            if ($propietatsfrase['tipusfrase'] == "ordre" && !$this->frasenegativa) {
                // l'insertem just després del main verb
                array_splice($this->ordrefrase, $indexmainverb+1, 0, $temp);
            }
            else {
                // l'insertem just abans del main verb
                array_splice($this->ordrefrase, $indexmainverb, 0, $temp);
            }
        }
        // fem el mateix amb el receiver 2, si hi és
        if ($indexreceiver2 != null) {
            $temp = $this->ordrefrase[$indexreceiver2];
            // esborrem el receiver 1 per moure'l de lloc
            array_splice($this->ordrefrase, $indexreceiver2, 1);
            // l'insertem just abans del verb secundari
            array_splice($this->ordrefrase, $indexsecondaryverb, 0, $temp);
        }
        
        
        // THEME PRONOMINAL / Yo, tu, él, etc. si no té preposició, excepte "a"
        // si té la preposició "a" davant, farà el canvi, però no posarà la preposició
        
        $indextheme1 = null;
        $indextheme2 = null;
                
        // busquem si té un slot de theme que sigui pronominal i el posem abans del verb principal
        for($i=0; $i<count($this->ordrefrase); $i++) {
            
            $slotaux = $this->slotarray[$this->ordrefrase[$i]];
            if ($slotaux->category == "Theme" && ($slotaux->prep == null || $slotaux->prep == "a")) {
                $wordslotauxfinal = $slotaux->paraulafinal;
                // si està en forma de pronom
                if (!$slotaux->defvalueused && $wordslotauxfinal->isClass("pronoun")) {
                    if ($slotaux->level == 1) $indextheme1 = $i;
                    if ($slotaux->level == 2) $indextheme2 = $i;
                }
            }
        }
        
        if ($indextheme1 != null) {
            $temp = $this->ordrefrase[$indextheme1];
            // esborrem el theme 1 per moure'l de lloc
            array_splice($this->ordrefrase, $indextheme1, 1);
            
            // si la frase és un ordre
            if ($propietatsfrase['tipusfrase'] == "ordre" && !$this->frasenegativa) {
                // l'insertem just després del main verb
                array_splice($this->ordrefrase, $indexmainverb+1, 0, $temp);
            }
            else {
                // l'insertem just abans del main verb
                array_splice($this->ordrefrase, $indexmainverb, 0, $temp);
            }
        }
        // fem el mateix amb el theme 2, si hi és
        if ($indextheme2 != null) {
            $temp = $this->ordrefrase[$indextheme2];
            // esborrem el theme 1 per moure'l de lloc
            array_splice($this->ordrefrase, $indextheme2, 1);
            // l'insertem just abans del verb secundari
            array_splice($this->ordrefrase, $indexsecondaryverb, 0, $temp);
        }
        
                
        // DESIRE
        
        // si és un desig afegir el verb voler a un slot Desire
        if ($propietatsfrase['tipusfrase'] == "desig") {
                        
            $slotvoler = new Myslot();
            $slotvoler->category = "Desire";
            $slotvoler->type = "verb";
            $slotvoler->full = true;
            $slotvoler->level = 0;
            
            $auxtupla[0] = "quiero";
            $auxtupla[1] = null;
            
            $slotvoler->slotstring[] = $auxtupla;
            
            $this->slotarray["Desire"] = $slotvoler;
            array_unshift($this->ordrefrase, "Desire");
            
            // afegir por favor: de moment NO
            // $aux = array();
            // $aux[0] = "por favor";
            // $aux[1] = '0';
            // $this->exprsarray[] = $aux;
        }
        
        
        // PERMISSION
        
        // si és un permís afegir el verb poder a un slot Permission
        if ($propietatsfrase['tipusfrase'] == "permis") {
                        
            $slotpoder = new Myslot();
            $slotpoder->category = "Permission";
            $slotpoder->type = "verb";
            $slotpoder->full = true;
            $slotpoder->level = 0;
            
            $auxtupla[0] = "puedo";
            $auxtupla[1] = null;
            
            $slotpoder->slotstring[] = $auxtupla;
            
            $this->slotarray["Permission"] = $slotpoder;
            array_unshift($this->ordrefrase, "Permission");
            // afegir por favor
            $aux = array();
            $aux[0] = "por favor";
            $aux[1] = '0';
            $this->exprsarray[] = $aux;
        }
        
        
        // CONDITIONAL PHRASE
        
        // si és una frsse condicional, afegim el "si" a davant (però darrere els advs de temps
        // i modificadors de frase que vagin a l'inici de frase)
        if ($propietatsfrase['tipusfrase'] == "condicional") {
            
            $this->frasefinal .= "si ";
        }
        
        
        // QUESTION PARTICLE
        
        // si hi ha una partícula de pregunta, posem el seu slot a davant de tot
        if ($indexpartpregunta != null) {
            $temp = $this->ordrefrase[$indexpartpregunta];
            // esborrem l'slot de la part pregunta per moure'l de lloc
            array_splice($this->ordrefrase, $indexpartpregunta, 1);
            // l'insertem a l'inici de la frase
            array_splice($this->ordrefrase, 0, 0, $temp);
        }
        
        
        // SI HI HA UNA PREGUNTA DE "QUANT"
        if (isset($this->slotarray["PartPreguntaNoSlot"])) {
            
            if ($this->slotarray["PartPreguntaNoSlot"]->paraulafinal->text == "cuánto") {
                // si hi ha dos verbs a la frase, només passem el theme 2 davant del subjecte
                if (isset($this->slotarray["Secondary Verb 2"])) {
                    
                    $indexsubject1 = -1;
                    $indextheme2 = -1;
                    
                    for ($i=0; $i<count($this->ordrefrase); $i++) {
                        if ($this->ordrefrase[$i] == "Theme 2") $indextheme2 = $i;
                        if ($this->ordrefrase[$i] == "Subject 1") $indexsubject1 = $i;
                    }
                    
                    // pot ser que el verb no tingués theme 2, tot i que el "quant" aleshores no
                    // tindria sentit
                    if ($indexsubject1 != -1 && $indextheme2 != -1) {
                        
                        $temp = $this->ordrefrase[$indextheme2];
                        // esborrem l'slot del theme per moure'l de lloc
                        array_splice($this->ordrefrase, $indextheme2, 1);
                        // l'insertem a l'inici de la frase
                        array_splice($this->ordrefrase, $indexsubject1, 0, $temp); 
                        
                        $this->questiontypequant = true;
                    }
                }
                // si només hi ha un verb, fem swap de theme i subject
                else {
                    $indexsubject = -1;
                    $indextheme = -1;
                    
                    for ($i=0; $i<count($this->ordrefrase); $i++) {
                        if ($this->ordrefrase[$i] == "Theme") $indextheme = $i;
                        if ($this->ordrefrase[$i] == "Subject") $indexsubject = $i;
                    }
                    // si tenia subjecte i theme
                    if ($indexsubject != -1 && $indextheme != -1) {
                        $auxslotname = $this->ordrefrase[$indexsubject]; // guardem el subj
                        // posem el theme al subj
                        $this->ordrefrase[$indexsubject] = $this->ordrefrase[$indextheme];
                        // i el subj guardat al theme
                        $this->ordrefrase[$indextheme] = $auxslotname;
                        
                        $this->questiontypequant = true;
                    }
                }                
            }
        }
        // SI HI HA QUALSEVOL ALTRA PREGUNTA: passem el subjecte al final de la frase
        else if ($indexpartpregunta != null) {
            $indexsubject1 = -1;
            if (isset($this->slotarray["Secondary Verb 2"])) {
                    for ($i=0; $i<count($this->ordrefrase); $i++) {
                        if ($this->ordrefrase[$i] == "Subject 1") $indexsubject1 = $i;
                    }
            }
            else {
                for ($i=0; $i<count($this->ordrefrase); $i++) {
                        if ($this->ordrefrase[$i] == "Subject") $indexsubject1 = $i;
                }
            }
            if ($indexsubject1 != -1) {
                $temp = $this->ordrefrase[$indexsubject1];
                // esborrem l'slot del subjecte per moure'l de lloc
                array_splice($this->ordrefrase, $indexsubject1, 1);
                // l'insertem al final de la frase
                $this->ordrefrase[] = $temp; 
            }
        }
        
        // IMPERATIVE
        if ($propietatsfrase['tipusfrase'] == "ordre"  && !$this->frasenegativa) {
            // afegir por favor
            $aux = array();
            $aux[0] = "por favor";
            $aux[1] = '0';
            $this->exprsarray[] = $aux;
        }
                   
        // DEBUG
        // echo $this->printOrdreFrase()."<br /><br />";
    }
    
    // Agafa la Persona, Gènere i número dels subjectes del pattern
    public function getPersGenNumSubjs()
    {
        $keysubj1 = "Subject";
        $keysubj2 = null;
        $slotsubj1 = null; // als pseudoimpersonals que com a subj hi ha un subverb, no hi ha subj1
        
        if (isset($this->slotarray["Secondary Verb 2"])) {
            $keysubj1 .= " 1";
            $keysubj2 = "Subject 2";
        }
        
        // si no hi ha subjecte i el verb és impersonal
        if(!isset($this->slotarray[$keysubj1]) && $this->impersonal) {
            $keysubj1 = "Theme";
        }
        
        // agafem les dades del primer subjecte
        if (isset($this->slotarray[$keysubj1]) && $this->slotarray[$keysubj1]->full) {
            $slotsubj1 = $this->slotarray[$keysubj1];
                                                
            // si hi ha el subjecte per defecte
            if ($slotsubj1->defvalueused) {
                $subj1 = $slotsubj1->defvalue;
                                
                if ($subj1 == '1') {
                    $this->perssubj1 = 1;
                    if ($this->isfem) $this->genmascsubj1 = false;
                }
                else if ($subj1 == '2') $this->perssubj1 = 2;
                else $this->perssubj1 = 3;
            }
            // si el subjecte és una paraula
            // si hi ha una partícula de pregunta al subjecte o no hi havia slot subjecte
            // perquè era impersonal, no ha d'agafar-ne les propietats
            else if($slotsubj1->paraulafinal->tipus != "questpart") {
                $subj1 = $slotsubj1->paraulafinal;
                                
                // si és un pronom personal
                if ($subj1->isClass("pronoun")) {
                    if ($subj1->text == "jo") {
                        if ($subj1->fem || $this->isfem) $this->genmascsubj1 = false;
                        else $this->genmascsubj1 = true;
                        $this->plsubj1 = false;
                        $this->perssubj1 = 1;
                    }
                    else if ($subj1->text == "tu") {
                        if ($subj1->fem) $this->genmascsubj1 = false;
                        else $this->genmascsubj1 = true;
                        $this->plsubj1 = false;
                        $this->perssubj1 = 2;
                    }
                    else if ($subj1->text == "ell") {
                        $this->genmascsubj1 = true;
                        if ($subj1->plural) $this->plsubj1 = true;
                        else $this->plsubj1 = false;
                        $this->perssubj1 = 3;
                    }
                    else if ($subj1->text == "ella") {
                        $this->genmascsubj1 = false;
                        if ($subj1->plural) $this->plsubj1 = true;
                        else $this->plsubj1 = false;
                        $this->perssubj1 = 3;
                    }
                    else if ($subj1->text == "nosaltres") {
                        if ($subj1->fem) $this->genmascsubj1 = false;
                        else $this->genmascsubj1 = true;
                        $this->plsubj1 = true;
                        $this->perssubj1 = 1;
                    }
                    else if ($subj1->text == "vosaltres") {
                        if ($subj1->fem) $this->genmascsubj1 = false;
                        else $this->genmascsubj1 = true;
                        $this->plsubj1 = true;
                        $this->perssubj1 = 2;
                    }
                    else if ($subj1->text == "ells") {
                        if ($subj1->fem) $this->genmascsubj1 = false;
                        else $this->genmascsubj1 = true;
                        $this->plsubj1 = true;
                        $this->perssubj1 = 3;
                    }
                    // per altra mena de pronoms, tipus això, allò, posem 3a persona
                    else {
                        $this->genmascsubj1 = true;
                        $this->plsubj1 = false;
                        $this->perssubj1 = 3;
                    }
                }
                // si no, agafem les propietats de la paraula
                else {     
                                        
                    if ($subj1->tipus == "name") {
                        if ($subj1->propietats->mf == "fem") $this->genmascsubj1 = false;
                        else $this->genmascsubj1 = true;
                        if ($subj1->propietats->singpl == "pl") $this->plsubj1 = true;
                        else $this->plsubj1 = false;

                        // hem de mirar si el subjecte té un quantificador o modificador que el fa ser plural
                        // la info estarà codificada a slotstring, al primer element que és nom
                        $i=0;
                        $found = false;
                        while ($i<count($slotsubj1->slotstring) && !$found) {
                            $aux = $slotsubj1->slotstring[$i];

                            // si trobem el nucli i efectivament és un nom
                            if (isset($aux[2]) && $aux[2]) {
                                $this->genmascsubj1 = $aux[3];
                                $this->plsubj1 = $aux[4];

                                $found = true;
                            }
                            $i++;
                        }
                    }
                    $this->perssubj1 = 3;
                }
                // si la paraula en té una altra de coordinada, passarà a plural
                if ($subj1->tipus == "name" && count($subj1->paraulacoord) > 0) $this->plsubj1 = true;
            }
            else if($slotsubj1->paraulafinal->tipus == "questpart") {
                $this->perssubj1 = 3;
            }
        }
        
        // si hi és també agafem les dades del segon subjecte
        if ($keysubj2 != null && isset($this->slotarray[$keysubj2])) {
            $slotsubj2 = $this->slotarray[$keysubj2];
            
            // si hi ha el subjecte per defecte
            if ($slotsubj2->defvalueused) {
                $subj2 = $slotsubj2->defvalue;
                
                if ($subj2 == '1')  {
                    $this->perssubj2 = 1;
                    if ($this->isfem) $this->genmascsubj2 = false;
                }
                else if ($subj2 == '2') $this->perssubj2 = 2;
                else $this->perssubj2 = 3;
            }
            // si el subjecte és una paraula
            else {
                $subj2 = $slotsubj2->paraulafinal;
                
                // si és un pronom personalq
                if ($subj2->isClass("pronoun")) {
                    if ($subj2->text == "jo") {
                        if ($subj2->fem || $this->isfem) $this->genmascsubj2 = false;
                        else $this->genmascsubj2 = true;
                        $this->plsubj2 = false;
                        $this->perssubj2 = 1;
                    }
                    else if ($subj2->text == "tu") {
                        if ($subj2->fem) $this->genmascsubj2 = false;
                        else $this->genmascsubj2 = true;
                        $this->plsubj2 = false;
                        $this->perssubj2 = 2;
                    }
                    else if ($subj2->text == "ell") {
                        $this->genmascsubj2 = true;
                        if ($subj2->plural) $this->plsubj2 = true;
                        else $this->plsubj2 = false;
                        $this->perssubj2 = 3;
                    }
                    else if ($subj2->text == "ella") {
                        $this->genmascsubj2 = false;
                        if ($subj2->plural) $this->plsubj2 = true;
                        else $this->plsubj2 = false;
                        $this->perssubj2 = 3;
                    }
                    else if ($subj2->text == "nosaltres") {
                        if ($subj2->fem) $this->genmascsubj2 = false;
                        else $this->genmascsubj2 = true;
                        $this->plsubj2 = true;
                        $this->perssubj2 = 1;
                    }
                    else if ($subj2->text == "vosaltres") {
                        if ($subj2->fem) $this->genmascsubj2 = false;
                        else $this->genmascsubj2 = true;
                        $this->plsubj2 = true;
                        $this->perssubj2 = 2;
                    }
                    else if ($subj2->text == "ells") {
                        if ($subj2->fem) $this->genmascsubj2 = false;
                        else $this->genmascsubj2 = true;
                        $this->plsubj2 = true;
                        $this->perssubj2 = 3;
                    }
                    // per altra mena de pronoms, tipus això, allò, posem 3a persona
                    else {
                        $this->genmascsubj2 = true;
                        $this->plsubj2 = false;
                        $this->perssubj2 = 3;
                    }
                }
                // si no, agafem les propietats de la paraula
                else {
                    if ($subj2->propietats->mf == "fem") $this->genmascsubj2 = false;
                    else $this->genmascsubj2 = true;
                    if ($subj2->propietats->singpl == "pl") $this->plsubj2 = true;
                    else $this->plsubj2 = false;
                    
                    // hem de mirar si el subjecte té un quantificador o modificador que el fa ser plural
                    // la info estarà codificada a slotstring, al primer element que és nom
                    $i=0;
                    $found = false;
                    while ($i<count($slotsubj2->slotstring) && !$found) {
                        $aux = $slotsubj2->slotstring[$i];
                        
                        // si trobem el nucli i efectivament és un nom
                        if (isset($aux[2]) && $aux[2]) {
                            $this->genmascsubj2 = $aux[3];
                            $this->plsubj2 = $aux[4];
                            
                            $found = true;
                        }
                        $i++;
                    }
                    $this->perssubj2 = 3;
                }
                // si la paraula en té una altra de coordinada, passarà a plural
                if (count($subj2->paraulacoord) > 0) $this->plsubj2 = true;
            }
            
            // sempre hi ha subj1 a no ser que a un pseudoimpersonal el subj sigui un subverb
            // No serveix per frases que no tenen subjecte, estil "És un pal" o "Fa sol"
            if ($slotsubj1 != null) {
                // Si hi havia dos subjectes MIREM SI ELS DOS SUBJECTES SÓN IGUALS
                if ($slotsubj1->paraulafinal != null && $slotsubj2->paraulafinal != null) {
                    if ($slotsubj1->paraulafinal->text == $slotsubj2->paraulafinal->text) $this->subjsiguals = true;
                }
                // si els dos estaven buits, seran iguals, per la funció ordenar slots
                else if ($slotsubj1->paraulafinal == null && $slotsubj2->paraulafinal == null) $this->subjsiguals = true;
                // si el primer és null i el segon no
                else if ($slotsubj1->paraulafinal == null && $slotsubj2->paraulafinal != null) {
                    if ($slotsubj1->defvalue == '1' && $slotsubj2->paraulafinal->text == "jo") $this->subjsiguals = true;
                    else if ($slotsubj1->defvalue == '2' && $slotsubj2->paraulafinal->text == "tu") $this->subjsiguals = true;
                }
                // si el primer no és null i el segon ho és
                else if ($slotsubj1->paraulafinal != null && $slotsubj2->paraulafinal == null) {
                    if ($slotsubj2->defvalue == '1' && $slotsubj1->paraulafinal->text == "jo") $this->subjsiguals = true;
                    else if ($slotsubj2->defvalue == '2' && $slotsubj1->paraulafinal->text == "tu") $this->subjsiguals = true;
                }
            }
            else {
                    $this->subjsiguals = true;
                    // i el subjecte és en 3a persona, ja que és una proposició
                    $this->perssubj1 = 3; 
            }
        }
        
        // si era pseudimpersonal amb subverb, si el receiver1 està definit i el subj2 no ho està, 
        // que subj2 = receiver1
        if ($this->pseudoimpersonal && isset($this->slotarray["Receiver 1"]) && $keysubj2 != null
            && !isset($this->slotarray[$keysubj2]) ) {
                    
            $this->subjsiguals = true;
        }
        
        
        // Si és verbless que posi per defecte la 1a persona, per si hi ha "Desig" activat
        // Ex: "Desig" Poma = Vull una poma
        if ($this->defaulttense == "verbless") $this->perssubj1 = 1;
        // Serveix per frases que no tenen subjecte, estil "És un pal" o "Fa sol"
        else if ($slotsubj1 == null) $this->perssubj1 = 3;
    }
    
    // Agafa la Persona, Gènere i número dels subjectes del pattern
    public function getPersGenNumSubjsES()
    {
        $keysubj1 = "Subject";
        $keysubj2 = null;
        $slotsubj1 = null; // als pseudoimpersonals que com a subj hi ha un subverb, no hi ha subj1
        
        if (isset($this->slotarray["Secondary Verb 2"])) {
            $keysubj1 .= " 1";
            $keysubj2 = "Subject 2";
        }
        
        // si no hi ha subjecte i el verb és impersonal
        if(!isset($this->slotarray[$keysubj1]) && $this->impersonal) {
            $keysubj1 = "Theme";
        }
        
        // agafem les dades del primer subjecte
        if (isset($this->slotarray[$keysubj1]) && $this->slotarray[$keysubj1]->full) {
            
            $slotsubj1 = $this->slotarray[$keysubj1];
            
            // si hi ha el subjecte per defecte
            if ($slotsubj1->defvalueused) {
                $subj1 = $slotsubj1->defvalue;
                
                if ($subj1 == '1') {
                    $this->perssubj1 = 1;
                    if ($this->isfem) $this->genmascsubj1 = false;
                }
                else if ($subj1 == '2') $this->perssubj1 = 2;
                else $this->perssubj1 = 3;
            }
            // si el subjecte és una paraula
            // si hi ha una partícula de pregunta al subjecte o no hi havia slot subjecte
            // perquè era impersonal, no ha d'agafar-ne les propietats
            else if($slotsubj1->paraulafinal->tipus != "questpart") {
                $subj1 = $slotsubj1->paraulafinal;
                
                // si és un pronom personal
                if ($subj1->isClass("pronoun")) {
                    if ($subj1->text == "yo") {
                        if ($subj1->fem || $this->isfem) $this->genmascsubj1 = false;
                        else $this->genmascsubj1 = true;
                        $this->plsubj1 = false;
                        $this->perssubj1 = 1;
                    }
                    else if ($subj1->text == "tú") {
                        if ($subj1->fem) $this->genmascsubj1 = false;
                        else $this->genmascsubj1 = true;
                        $this->plsubj1 = false;
                        $this->perssubj1 = 2;
                    }
                    else if ($subj1->text == "él") {
                        $this->genmascsubj1 = true;
                        if ($subj1->plural) $this->plsubj1 = true;
                        else $this->plsubj1 = false;
                        $this->perssubj1 = 3;
                    }
                    else if ($subj1->text == "ella") {
                        $this->genmascsubj1 = false;
                        if ($subj1->plural) $this->plsubj1 = true;
                        else $this->plsubj1 = false;
                        $this->perssubj1 = 3;
                    }
                    else if ($subj1->text == "nosotros") {
                        if ($subj1->fem) $this->genmascsubj1 = false;
                        else $this->genmascsubj1 = true;
                        $this->plsubj1 = true;
                        $this->perssubj1 = 1;
                    }
                    else if ($subj1->text == "vosotros") {
                        if ($subj1->fem) $this->genmascsubj1 = false;
                        else $this->genmascsubj1 = true;
                        $this->plsubj1 = true;
                        $this->perssubj1 = 2;
                    }
                    else if ($subj1->text == "ellos") {
                        if ($subj1->fem) $this->genmascsubj1 = false;
                        else $this->genmascsubj1 = true;
                        $this->plsubj1 = true;
                        $this->perssubj1 = 3;
                    }
                    // per altra mena de pronoms, tipus això, allò, posem 3a persona
                    else {
                        $this->genmascsubj1 = true;
                        $this->plsubj1 = false;
                        $this->perssubj1 = 3;
                    }
                }
                // si no, agafem les propietats de la paraula
                else {     
                    if ($subj1->tipus == "name") {
                        if ($subj1->propietats->mf == "fem") $this->genmascsubj1 = false;
                        else $this->genmascsubj1 = true;
                        if ($subj1->propietats->singpl == "pl") $this->plsubj1 = true;
                        else $this->plsubj1 = false;

                        // hem de mirar si el subjecte té un quantificador o modificador que el fa ser plural
                        // la info estarà codificada a slotstring, al primer element que és nom
                        $i=0;
                        $found = false;
                        while ($i<count($slotsubj1->slotstring) && !$found) {
                            $aux = $slotsubj1->slotstring[$i];

                            // si trobem el nucli i efectivament és un nom
                            if (isset($aux[2]) && $aux[2]) {
                                $this->genmascsubj1 = $aux[3];
                                $this->plsubj1 = $aux[4];

                                $found = true;
                            }
                            $i++;
                        }
                    }
                    $this->perssubj1 = 3;
                }
                // si la paraula en té una altra de coordinada, passarà a plural
                if ($subj1->tipus == "name" && count($subj1->paraulacoord) > 0) $this->plsubj1 = true;
            }
            else if($slotsubj1->paraulafinal->tipus == "questpart") {
                $this->perssubj1 = 3;
            }
        }
        
        // si hi és també agafem les dades del segon subjecte
        if ($keysubj2 != null && isset($this->slotarray[$keysubj2])) {
            $slotsubj2 = $this->slotarray[$keysubj2];
            
            // si hi ha el subjecte per defecte
            if ($slotsubj2->defvalueused) {
                $subj2 = $slotsubj2->defvalue;
                
                if ($subj2 == '1')  {
                    $this->perssubj2 = 1;
                    if ($this->isfem) $this->genmascsubj2 = false;
                }
                else if ($subj2 == '2') $this->perssubj2 = 2;
                else $this->perssubj2 = 3;
            }
            // si el subjecte és una paraula
            else {
                $subj2 = $slotsubj2->paraulafinal;
                
                // si és un pronom personal
                if ($subj2->isClass("pronoun")) {
                    if ($subj2->text == "yo") {
                        if ($subj2->fem || $this->isfem) $this->genmascsubj2 = false;
                        else $this->genmascsubj2 = true;
                        $this->plsubj2 = false;
                        $this->perssubj2 = 1;
                    }
                    else if ($subj2->text == "tú") {
                        if ($subj2->fem) $this->genmascsubj2 = false;
                        else $this->genmascsubj2 = true;
                        $this->plsubj2 = false;
                        $this->perssubj2 = 2;
                    }
                    else if ($subj2->text == "él") {
                        $this->genmascsubj2 = true;
                        if ($subj2->plural) $this->plsubj2 = true;
                        else $this->plsubj2 = false;
                        $this->perssubj2 = 3;
                    }
                    else if ($subj2->text == "ella") {
                        $this->genmascsubj2 = false;
                        if ($subj2->plural) $this->plsubj2 = true;
                        else $this->plsubj2 = false;
                        $this->perssubj2 = 3;
                    }
                    else if ($subj2->text == "nosotros") {
                        if ($subj2->fem) $this->genmascsubj2 = false;
                        else $this->genmascsubj2 = true;
                        $this->plsubj2 = true;
                        $this->perssubj2 = 1;
                    }
                    else if ($subj2->text == "vosotros") {
                        if ($subj2->fem) $this->genmascsubj2 = false;
                        else $this->genmascsubj2 = true;
                        $this->plsubj2 = true;
                        $this->perssubj2 = 2;
                    }
                    else if ($subj2->text == "ellos") {
                        if ($subj2->fem) $this->genmascsubj2 = false;
                        else $this->genmascsubj2 = true;
                        $this->plsubj2 = true;
                        $this->perssubj2 = 3;
                    }
                    // per altra mena de pronoms, tipus això, allò, posem 3a persona
                    else {
                        $this->genmascsubj2 = true;
                        $this->plsubj2 = false;
                        $this->perssubj2 = 3;
                    }
                }
                // si no, agafem les propietats de la paraula
                else {
                    if ($subj2->propietats->mf == "fem") $this->genmascsubj2 = false;
                    else $this->genmascsubj2 = true;
                    if ($subj2->propietats->singpl == "pl") $this->plsubj2 = true;
                    else $this->plsubj2 = false;
                    
                    // hem de mirar si el subjecte té un quantificador o modificador que el fa ser plural
                    // la info estarà codificada a slotstring, al primer element que és nom
                    $i=0;
                    $found = false;
                    while ($i<count($slotsubj2->slotstring) && !$found) {
                        $aux = $slotsubj2->slotstring[$i];
                        
                        // si trobem el nucli i efectivament és un nom
                        if (isset($aux[2]) && $aux[2]) {
                            $this->genmascsubj2 = $aux[3];
                            $this->plsubj2 = $aux[4];
                            
                            $found = true;
                        }
                        $i++;
                    }
                    $this->perssubj2 = 3;
                }
                // si la paraula en té una altra de coordinada, passarà a plural
                if (count($subj2->paraulacoord) > 0) $this->plsubj2 = true;
            }
            
            // sempre hi ha subj1 a no ser que a un pseudoimpersonal el subj sigui un subverb
            // No serveix per frases que no tenen subjecte, estil "És un pal" o "Fa sol"
            if ($slotsubj1 != null) {
                // Si hi havia dos subjectes MIREM SI ELS DOS SUBJECTES SÓN IGUALS
                if ($slotsubj1->paraulafinal != null && $slotsubj2->paraulafinal != null) {
                    if ($slotsubj1->paraulafinal->text == $slotsubj2->paraulafinal->text) $this->subjsiguals = true;
                }
                // si els dos estaven buits, seran iguals, per la funció ordenar slots
                else if ($slotsubj1->paraulafinal == null && $slotsubj2->paraulafinal == null) $this->subjsiguals = true;
                // si el primer és null i el segon no
                else if ($slotsubj1->paraulafinal == null && $slotsubj2->paraulafinal != null) {
                    if ($slotsubj1->defvalue == '1' && $slotsubj2->paraulafinal->text == "yo") $this->subjsiguals = true;
                    else if ($slotsubj1->defvalue == '2' && $slotsubj2->paraulafinal->text == "tú") $this->subjsiguals = true;
                }
                // si el primer no és null i el segon ho és
                else if ($slotsubj1->paraulafinal != null && $slotsubj2->paraulafinal == null) {
                    if ($slotsubj2->defvalue == '1' && $slotsubj1->paraulafinal->text == "yo") $this->subjsiguals = true;
                    else if ($slotsubj2->defvalue == '2' && $slotsubj1->paraulafinal->text == "tú") $this->subjsiguals = true;
                }
            }
            else {
                $this->subjsiguals = true;
                // i el subjecte és en 3a persona, ja que és una proposició
                $this->perssubj1 = 3; 
            }
        }
        
        // si era pseudimpersonal amb subverb, si el receiver1 està definit i el subj2 no ho està, 
        // que subj2 = receiver1
        if ($this->pseudoimpersonal && isset($this->slotarray["Receiver 1"]) && $keysubj2 != null
            && !isset($this->slotarray[$keysubj2]) ) {
                    
            $this->subjsiguals = true;
        }
        
        // Si és verbless que posi per defecte la 1a persona, per si hi ha "Desig" activat
        // Ex: "Desig" Poma = Vull una poma
        if ($this->defaulttense == "verbless") $this->perssubj1 = 1;
        // Serveix per frases que no tenen subjecte, estil "És un pal" o "Fa sol"
        else if ($slotsubj1 == null) $this->perssubj1 = 3;
    }

    // Ordena internament les paraules de cada slot i les fa concordar en gènere i nombre entre elles
    // PASSOS 2 I 3 DEL GENERADOR
    public function ordenarSlotsInternament()
    {
        $numslots = count($this->ordrefrase);
        $this->getPersGenNumSubjs();
        
        for ($i=0; $i<$numslots; $i++) {
            
            $slotaux = $this->slotarray[$this->ordrefrase[$i]];
            // si és un slot del subverb li passem les dades del subj2
            if ($slotaux->level == 2) $slotaux->ordenarSlot($this->genmascsubj2, $this->plsubj2, $this->copulatiu, $this->impersonal);
            else $slotaux->ordenarSlot($this->genmascsubj1, $this->plsubj1, $this->copulatiu, $this->impersonal);
            
            // si acabem de tractar un subjecte, refresquem els valors de gènere i número per si no eren
            // pronoms, i pels possibles adjectius que hagin de concordar amb ells, que apareixeran després
            // pels verbs impersonals sense subjecte també ho fem
            if ($slotaux->category == "Subject" || ($this->impersonal && $slotaux->category == "Theme")) {
                $this->getPersGenNumSubjs();
            }
        }
        
        if ($this->questiontypequant) {
        // fer que el quant concordi en gènere i nombre amb el nucli del theme
            $keytheme = "Theme";
            if (isset($this->slotarray["Secondary Verb 2"])) $keytheme .= " 2";
            
            $thememasc = true;
            $themepl = false;
            
            $slottheme = $this->slotarray[$keytheme];
            $slotstringtheme = $slottheme->slotstring;
            
            // canviem al nucli del theme que no necessiti article i agafem les dades de concordància
            $i=0;
            $found = false;
            while ($i<count($slotstringtheme) && !$found) {
                $aux = $slotstringtheme[$i];
                // si trobem el nucli
                if (isset($aux[2]) && $aux[2]) {
                    $thememasc = $aux[3];
                    $themepl = $aux[4];
                    // indiquem que el nucli del theme ja no necessitarà article
                    $slotstringtheme[$i][5] = true;
                }
                $i++;
            }
            
            // esborrem l'slotstring que hi hagués i fem concordar la partícula amb el theme
            $slotpartquant = $this->slotarray["PartPreguntaNoSlot"];
            $slotpartquant->slotstring = array();
            $partpregunta = $slotpartquant->paraulafinal->text;
            
            $elementaux = array();
            if ($thememasc && !$themepl) $partpregunta = $partpregunta; // masc sing
            else if ($thememasc && $themepl) $partpregunta = $partpregunta."s"; // masc pl
            else if (!$thememasc && !$themepl) $partpregunta = $partpregunta."a"; // fem sing
            else $partpregunta = $partpregunta."es"; // fem pl
            
            $elementaux[0] = $partpregunta;
            $elementaux[1] = $slotpartquant->paraulafinal;
            $elementaux[2] = false;
            
            $slotpartquant->slotstring[] = $elementaux;
        }
        
        // DEBUG
        // echo $this->printFraseFinalSlotString()."<br /><br />";
    }
    
    // Ordena internament les paraules de cada slot i les fa concordar en gènere i nombre entre elles
    // PASSOS 2 I 3 DEL GENERADOR
    public function ordenarSlotsInternamentES()
    {
        $numslots = count($this->ordrefrase);
        $this->getPersGenNumSubjsES();
        
        for ($i=0; $i<$numslots; $i++) {
            
            $slotaux = $this->slotarray[$this->ordrefrase[$i]];
            // si és un slot del subverb li passem les dades del subj2
            if ($slotaux->level == 2) $slotaux->ordenarSlotES($this->genmascsubj2, $this->plsubj2, $this->copulatiu, $this->impersonal);
            else $slotaux->ordenarSlotES($this->genmascsubj1, $this->plsubj1, $this->copulatiu, $this->impersonal);
            
            // si acabem de tractar un subjecte, refresquem els valors de gènere i número per si no eren
            // pronoms, i pels possibles adjectius que hagin de concordar amb ells, que apareixeran després
            // pels verbs impersonals sense subjecte també ho fem
            if ($slotaux->category == "Subject" || ($this->impersonal && $slotaux->category == "Theme")) {
                $this->getPersGenNumSubjsES();
            }
        }
        
        if ($this->questiontypequant) {
        // fer que el quant concordi en gènere i nombre amb el nucli del theme
            $keytheme = "Theme";
            if (isset($this->slotarray["Secondary Verb 2"])) $keytheme .= " 2";
            
            $thememasc = true;
            $themepl = false;
            
            $slottheme = $this->slotarray[$keytheme];
            $slotstringtheme = $slottheme->slotstring;
            
            // canviem al nucli del theme que no necessiti article i agafem les dades de concordància
            $i=0;
            $found = false;
            while ($i<count($slotstringtheme) && !$found) {
                $aux = $slotstringtheme[$i];
                // si trobem el nucli i el nucli és un nom
                if (isset($aux[2]) && $aux[2]) {
                    $thememasc = $aux[3];
                    $themepl = $aux[4];
                    // indiquem que el nucli del theme ja no necessitarà article
                    $slotstringtheme[$i][5] = true;
                }
                $i++;
            }
            
            // esborrem l'slotstring que hi hagués i fem concordar la partícula amb el theme
            $slotpartquant = $this->slotarray["PartPreguntaNoSlot"];
            $slotpartquant->slotstring = array();
            $partpregunta = $slotpartquant->paraulafinal->text;
            
            $elementaux = array();
            if ($thememasc && !$themepl) $partpregunta = $partpregunta; // masc sing
            else if ($thememasc && $themepl) $partpregunta = $partpregunta."s"; // masc pl
            else if (!$thememasc && !$themepl) $partpregunta = substr($partpregunta, 0, -1)."a"; // fem sing
            else $partpregunta = substr($partpregunta, 0, -1)."as"; // fem pl
            
            $elementaux[0] = $partpregunta;
            $elementaux[1] = $slotpartquant->paraulafinal;
            $elementaux[2] = false;
            
            $slotpartquant->slotstring[] = $elementaux;
        }
        
        // DEBUG
        // echo $this->printFraseFinalSlotString()."<br /><br />";
    }

    // Posa els articles als noms que hi ha a la frase, segons les propietats del nom
    // i el tipus d'slot on es trobi
    public function putArticlesToNouns($tipusfrase)
    { 
        // per cada slot
        for ($i=0; $i<count($this->ordrefrase); $i++) {
            $slotaux = $this->slotarray[$this->ordrefrase[$i]];
            $slotaux->putArticles($tipusfrase, $this->questiontypequant);
        }
        // DEBUG
        // echo $this->printFraseFinalSlotString()."<br /><br />";
    }
    
    // Posa els articles als noms que hi ha a la frase, segons les propietats del nom
    // i el tipus d'slot on es trobi
    public function putArticlesToNounsES($tipusfrase)
    { 
        // per cada slot
        for ($i=0; $i<count($this->ordrefrase); $i++) {
            $slotaux = $this->slotarray[$this->ordrefrase[$i]];
            $slotaux->putArticlesES($tipusfrase, $this->questiontypequant);
        }
        // DEBUG
        // echo $this->printFraseFinalSlotString()."<br /><br />";
    }
    
    // Conjuga els verbs principals, els secundaris i els de desig i permís, segons
    // el context (paraules de temps) i tipus de frase i els modificadors de temps
    public function conjugarVerbs($propietatsfrase)
    {
        $CI = &get_instance();
        $CI->load->model('Lexicon');
        $CI->load->library('Mymatching');
        $matching = new Mymatching();
                
        // agafem el default tense. Si no n'hi ha, perquè la frase és verbless, el posarem a present
        // per si ha seleccionat l'opció de desig
        if ($this->defaulttense == "verbless") $tense = "present";
        else $tense = $this->defaulttense;
                
        // per ORDRE d'importància, anem mirant si agafem un tense diferent del default
        if ($propietatsfrase['tipusfrase'] == "ordre") {
            if ($this->frasenegativa) $tense = "prsubj";
            else $tense = "imperatiu";
        }
        else {
            // si està definit el temps des de l'input, té preferència
            if ($propietatsfrase['tense'] != "defecte") $tense = $propietatsfrase['tense'];
            // si no, trajecte normal
            else {
                // si hi ha expressions de temps, la primera afegida que tingui associat
                // un temps verbal, és la que té preferència
                $found = false;
                $i=0;
                while ($i<count($this->timeexpr) && !$found) {
                    if (isset($matching->advsTempsTense[$this->timeexpr[$i]->text])) {
                        $tense = $matching->advsTempsTense[$this->timeexpr[$i]->text];
                        $found = true;
                        $tenseadvs = true;
                    }
                    $i++;
                }
            }
        }
        
        // Variables de persona i número dels verbs
        $persona = $this->perssubj1;
        $numero = "sing";
        if ($this->plsubj1) $numero = "pl";
        
        $persona2 = $this->perssubj2;
        $numero2 = "sing";
        if ($this->plsubj2) $numero2 = "pl";
        
        $desig = false;
        $permis = false;
        $subverb = false;
        
        // si el tense era imperatiu per primera persona, que no és possible, ho passem a present
        if ($tense == "imperatiu" && $persona == 1) $tense = "present";
        
        if ($propietatsfrase['tipusfrase'] == "desig") $desig = true;
        else if ($propietatsfrase['tipusfrase'] == "permis") $permis = true;
        
        if (isset($this->slotarray["Secondary Verb 2"])) $subverb = true;
        
        // conjuguem els verbs de desig o permís
        if ($desig || $permis) {
            
            // si el tense era imperatiu o present de subjuntiu, per una ordre positiva
            // o negativa, el canviem a present
            if ($tense == 'imperatiu' || $tense == "prsubj") $tense = "present";
            
            $slotaux;
            $verbid;
            
            if ($desig) {
                $slotaux = $this->slotarray["Desire"];
                $verbid = 99; // id del verb voler
            }
            if ($permis) {
                $slotaux = $this->slotarray["Permission"];
                $verbid = 104; // id del verb poder
            }
            
            // Els modificadors de tipuis de frase de permís o desig són per fer frases
            // en 1a persona "Vull que vagis a comprar" o "Puc menjar un gelat?".
            $verbconjugat = $CI->Lexicon->conjugar($verbid, $tense, 1, "sing", false);
            
            $auxtupla[0] = $verbconjugat."@VERBUM";
            $auxtupla[1] = null;
            
            $slotaux->slotstring = array();
            $slotaux->slotstring[] = $auxtupla;
            $slotaux->isInfinitive = false;
        }
        
        $mainverbslot;
        if (!$subverb) $mainverbslot = $this->slotarray["Main Verb"];
        else $mainverbslot = $this->slotarray["Main Verb 1"];
        
        // si era verbless, no el tractarem
        if ($mainverbslot->paraulafinal->text == "verbless") $mainverbslot = null;
        
        $secondaryverbslot = null;
        if ($subverb) $secondaryverbslot = $this->slotarray["Secondary Verb 2"];
        
        // si no era verbless
        if ($mainverbslot != null) {
            
            // si la frase era de permís (verb poder), tant el mainverb com el secondary verb van en infinitiu
            if ($permis) {

                $verbconjugat = $CI->Lexicon->conjugar($mainverbslot->paraulafinal->id, 'infinitiu', $persona, $numero, $this->pronominal);

                $auxtupla[0] = $verbconjugat."@VERBUM";
                $auxtupla[1] = $mainverbslot->paraulafinal;

                $mainverbslot->slotstring[] = $auxtupla; // omplim l'slotstring
                $mainverbslot->isInfinitive = true;
                
                if ($secondaryverbslot != null) {
                    $verbconjugat = $CI->Lexicon->conjugar($secondaryverbslot->paraulafinal->id, 'infinitiu', $persona2, $numero2, $this->pronominal2);

                    // posem la preposició que va davant del verb secundari, si n'hi havia
                    if ($secondaryverbslot->prep != null) {
                        $auxtupla[0] = $secondaryverbslot->prep;
                        $auxtupla[1] = null;

                        // la preposició del verb secundari va darrere del verb principal
                        
                        $mainverbslot->slotstring[] = $auxtupla;
                    }
                    
                    $auxtupla[0] = $verbconjugat."@VERBUM";
                    $auxtupla[1] = $secondaryverbslot->paraulafinal;

                    $secondaryverbslot->slotstring[] = $auxtupla;
                    $secondaryverbslot->isInfinitive = true;
                }
            } // Fi permís
            // si era un desig, el mainverb va en infinitiu (si el subjecte1 era en primera persona,
            // si no va en subjuntiu) i el secondary verb segons el subjecte2
            else if ($desig) {
                
                if ($persona == 1 && $numero == "sing") {
                    $verbconjugat = $CI->Lexicon->conjugarES($mainverbslot->paraulafinal->id, 'infinitiu', $persona, $numero, $this->pronominal);
                    $mainverbslot->isInfinitive = true;
                }
                else {
                    $verbconjugat = $CI->Lexicon->conjugarES($mainverbslot->paraulafinal->id, 'prsubj', $persona, $numero, $this->pronominal);
                    
                    // afegim la partícula QUE després del "Vull"
                    $auxtupla[0] = "que";
                    $auxtupla[1] = null;
                    
                    $slotaux = $this->slotarray["Desire"];
                    $slotaux->slotstring[] = $auxtupla;
                }

                $auxtupla[0] = $verbconjugat."@VERBUM";
                $auxtupla[1] = $mainverbslot->paraulafinal;

                $mainverbslot->slotstring[] = $auxtupla; // omplim l'slotstring
                
                if ($secondaryverbslot != null) {
                    
                    // si els subjectes eren iguals, també va en infinitiu
                    if ($this->subjsiguals) {
                        $verbconjugat = $CI->Lexicon->conjugar($secondaryverbslot->paraulafinal->id, 'infinitiu', $persona2, $numero2, $this->pronominal2);
                        
                        $secondaryverbslot->isInfinitive = true;
                        
                        // posem la preposició que va davant del verb secundari, si n'hi havia
                        if ($secondaryverbslot->prep != null) {
                            $auxtupla[0] = $secondaryverbslot->prep;
                            $auxtupla[1] = null;

                            $secondaryverbslot->slotstring[] = $auxtupla;
                        }
                    }
                    // si no, aleshores va en subjuntiu (si fos en passat hauria d'anar en passat
                    // de subjuntiu, però el sistema encara no ho té
                    else {
                        $verbconjugat = $CI->Lexicon->conjugar($secondaryverbslot->paraulafinal->id, 'prsubj', $persona2, $numero2, $this->pronominal2);
                        
                        // posem la preposició que va darrer del verb principal, si n'hi havia
                        if ($secondaryverbslot->prep != null) {
                            $auxtupla[0] = $secondaryverbslot->prep;
                            $auxtupla[1] = null;

                            $mainverbslot->slotstring[] = $auxtupla;
                        }
                        
                        // afegim la partícula QUE després del main verb
                        $auxtupla[0] = "que";
                        $auxtupla[1] = null;
                        $mainverbslot->slotstring[] = $auxtupla;
                    }
                    
                    $auxtupla[0] = $verbconjugat."@VERBUM";
                    $auxtupla[1] = $secondaryverbslot->paraulafinal;

                    $secondaryverbslot->slotstring[] = $auxtupla;
                }
            } // Fi desig
            
            // si no, CONJUGACIÓ NORMAL
            else {
                $verbconjugat = $CI->Lexicon->conjugar($mainverbslot->paraulafinal->id, $tense, $persona, $numero, $this->pronominal);

                $auxtupla[0] = $verbconjugat."@VERBUM";
                $auxtupla[1] = $mainverbslot->paraulafinal;
                $auxtupla[8] = true;
                $mainverbslot->isInfinitive = ($tense == "infinitiu"); // pel perifràstic no cal posar 
                                        // els pronoms febles a darrere, ja que a davant hi queden millor
                
                $mainverbslot->slotstring[] = $auxtupla; // omplim l'slotstring
                
                if ($secondaryverbslot != null) {
                    
                    // si els subjectes eren iguals, també va en infinitiu
                    if ($this->subjsiguals) {
                        $verbconjugat = $CI->Lexicon->conjugar($secondaryverbslot->paraulafinal->id, 'infinitiu', $persona2, $numero2, $this->pronominal2);
                        
                        $secondaryverbslot->isInfinitive = true;
                        
                        // posem la preposició que va davant del verb secundari, si n'hi havia
                        if ($secondaryverbslot->prep != null) {
                            $auxtupla[0] = $secondaryverbslot->prep;
                            $auxtupla[1] = null;

                            $secondaryverbslot->slotstring[] = $auxtupla;
                        }
                    }
                    // si no, aleshores va en subjuntiu (si fos en passat hauria d'anar en passat
                    // de subjuntiu, però el sistema encara no ho té
                    else {
                        $verbconjugat = $CI->Lexicon->conjugar($secondaryverbslot->paraulafinal->id, 'prsubj', $persona2, $numero2, $this->pronominal2);
                        
                        // posem la preposició que va darrer del verb principal, si n'hi havia
                        if ($secondaryverbslot->prep != null) {
                            $auxtupla[0] = $secondaryverbslot->prep;
                            $auxtupla[1] = null;

                            $mainverbslot->slotstring[] = $auxtupla;
                        }
                        
                        // afegim la partícula QUE després del main verb
                        $auxtupla[0] = "que";
                        $auxtupla[1] = null;
                        $mainverbslot->slotstring[] = $auxtupla;
                    }
                                        
                    $auxtupla[0] = $verbconjugat."@VERBUM";
                    $auxtupla[1] = $secondaryverbslot->paraulafinal;

                    $secondaryverbslot->slotstring[] = $auxtupla;
                }
            } // Fi conjugació normal
            
            
        } // Fi si no era verbless      
        
        // DEBUG
        // echo $this->printFraseFinalSlotString()."<br /><br />";
    }
    
    // Conjuga els verbs principals, els secundaris i els de desig i permís, segons
    // el context (paraules de temps) i tipus de frase i els modificadors de temps
    public function conjugarVerbsES($propietatsfrase)
    {
        $CI = &get_instance();
        $CI->load->model('Lexicon');
        $CI->load->library('Mymatching');
        $matching = new Mymatching();
                
        // agafem el default tense. Si no n'hi ha, perquè la frase és verbless, el posarem a present
        // per si ha seleccionat l'opció de desig
        if ($this->defaulttense == "verbless") $tense = "present";
        else $tense = $this->defaulttense;
                
        // per ORDRE d'importància, anem mirant si agafem un tense diferent del default
        if ($propietatsfrase['tipusfrase'] == "ordre") {
            if ($this->frasenegativa) $tense = "prsubj";
            else $tense = "imperatiu";
        }
        else {
            // si està definit el temps des de l'input, té preferència
            if ($propietatsfrase['tense'] != "defecte") $tense = $propietatsfrase['tense'];
            // si no, trajecte normal
            else {
                // si hi ha expressions de temps, la primera afegida que tingui associat
                // un temps verbal, és la que té preferència
                $found = false;
                $i=0;
                while ($i<count($this->timeexpr) && !$found) {
                    if (isset($matching->advsTempsTenseES[$this->timeexpr[$i]->text])) {
                        $tense = $matching->advsTempsTenseES[$this->timeexpr[$i]->text];
                        $found = true;
                        $tenseadvs = true;
                    }
                    $i++;
                }
            }
        }
        
        // Variables de persona i número dels verbs
        $persona = $this->perssubj1;
        $numero = "sing";
        if ($this->plsubj1) $numero = "pl";
        
        $persona2 = $this->perssubj2;
        $numero2 = "sing";
        if ($this->plsubj2) $numero2 = "pl";
        
        $desig = false;
        $permis = false;
        $subverb = false;
        
        // En castellà no hi ha perifràstic, és passat
        if ($tense == "perifrastic") $tense = "passat";
        
        // si el tense era imperatiu per primera persona, que no és possible, ho passem a present
        if ($tense == "imperatiu" && $persona == 1) $tense = "present";
        
        if ($propietatsfrase['tipusfrase'] == "desig") $desig = true;
        else if ($propietatsfrase['tipusfrase'] == "permis") $permis = true;
        
        if (isset($this->slotarray["Secondary Verb 2"])) $subverb = true;
        
        // conjuguem els verbs de desig o permís
        if ($desig || $permis) {
            
            // si el tense era imperatiu o present de subjuntiu, per una ordre positiva
            // o negativa, el canviem a present
            if ($tense == 'imperatiu' || $tense == "prsubj") $tense = "present";
            
            $slotaux;
            $verbid;
            
            if ($desig) {
                $slotaux = $this->slotarray["Desire"];
                $verbid = 99; // id del verb voler
            }
            if ($permis) {
                $slotaux = $this->slotarray["Permission"];
                $verbid = 104; // id del verb poder
            }
            
            // Els modificadors de tipuis de frase de permís o desig són per fer frases
            // en 1a persona "Vull que vagis a comprar" o "Puc menjar un gelat?".
            $verbconjugat = $CI->Lexicon->conjugarES($verbid, $tense, 1, "sing", false);
            
            $auxtupla[0] = $verbconjugat."@VERBUM";
            $auxtupla[1] = null;
            
            $slotaux->slotstring = array();
            $slotaux->slotstring[] = $auxtupla;
            $slotaux->isInfinitive = false;
        }
        
        $mainverbslot;
        if (!$subverb) $mainverbslot = $this->slotarray["Main Verb"];
        else $mainverbslot = $this->slotarray["Main Verb 1"];
        
        // si era verbless, no el tractarem
        if ($mainverbslot->paraulafinal->text == "verbless") $mainverbslot = null;
        
        $secondaryverbslot = null;
        if ($subverb) $secondaryverbslot = $this->slotarray["Secondary Verb 2"];
        
        // si no era verbless
        if ($mainverbslot != null) {
            
            // si la frase era de permís (verb poder), tant el mainverb com el secondary verb van en infinitiu
            if ($permis) {

                $verbconjugat = $CI->Lexicon->conjugarES($mainverbslot->paraulafinal->id, 'infinitiu', $persona, $numero, $this->pronominal);

                $auxtupla[0] = $verbconjugat."@VERBUM";
                $auxtupla[1] = $mainverbslot->paraulafinal;

                $mainverbslot->slotstring[] = $auxtupla; // omplim l'slotstring
                $mainverbslot->isInfinitive = true;
                
                if ($secondaryverbslot != null) {
                    $verbconjugat = $CI->Lexicon->conjugarES($secondaryverbslot->paraulafinal->id, 'infinitiu', $persona2, $numero2, $this->pronominal2);

                    // posem la preposició que va davant del verb secundari, si n'hi havia
                    if ($secondaryverbslot->prep != null) {
                        $auxtupla[0] = $secondaryverbslot->prep;
                        $auxtupla[1] = null;

                        // la preposició del verb secundari va darrere del verb principal
                        
                        $mainverbslot->slotstring[] = $auxtupla;
                    }
                    
                    $auxtupla[0] = $verbconjugat."@VERBUM";
                    $auxtupla[1] = $secondaryverbslot->paraulafinal;

                    $secondaryverbslot->slotstring[] = $auxtupla;
                    $secondaryverbslot->isInfinitive = true;
                }
            } // Fi permís
            // si era un desig, el mainverb va en infinitiu (si el subjecte1 era en primera persona,
            // si no va en subjuntiu) i el secondary verb segons el subjecte2
            else if ($desig) {
                
                if ($persona == 1 && $numero == "sing") {
                    $verbconjugat = $CI->Lexicon->conjugarES($mainverbslot->paraulafinal->id, 'infinitiu', $persona, $numero, $this->pronominal);
                    $mainverbslot->isInfinitive = true;
                }
                else {
                    $verbconjugat = $CI->Lexicon->conjugarES($mainverbslot->paraulafinal->id, 'prsubj', $persona, $numero, $this->pronominal);
                    
                    // afegim la partícula QUE després del "Quiero"
                    $auxtupla[0] = "que";
                    $auxtupla[1] = null;
                    
                    $slotaux = $this->slotarray["Desire"];
                    $slotaux->slotstring[] = $auxtupla;
                }
                
                $auxtupla[0] = $verbconjugat."@VERBUM";
                $auxtupla[1] = $mainverbslot->paraulafinal;

                $mainverbslot->slotstring[] = $auxtupla; // omplim l'slotstring
                
                if ($secondaryverbslot != null) {
                    
                    // si els subjectes eren iguals, va en infinitiu
                    if ($this->subjsiguals) {
                        $verbconjugat = $CI->Lexicon->conjugarES($secondaryverbslot->paraulafinal->id, 'infinitiu', $persona2, $numero2, $this->pronominal2);
                        
                        $secondaryverbslot->isInfinitive = true;
                        
                        // posem la preposició que va davant del verb secundari, si n'hi havia
                        if ($secondaryverbslot->prep != null) {
                            $auxtupla[0] = $secondaryverbslot->prep;
                            $auxtupla[1] = null;

                            $secondaryverbslot->slotstring[] = $auxtupla;
                        }
                    }
                    // si no, aleshores va en subjuntiu (si fos en passat hauria d'anar en passat
                    // de subjuntiu, però el sistema encara no ho té
                    else {
                        $verbconjugat = $CI->Lexicon->conjugarES($secondaryverbslot->paraulafinal->id, 'prsubj', $persona2, $numero2, $this->pronominal2);
                        
                        // posem la preposició que darrere del verb principal, si n'hi havia
                        if ($secondaryverbslot->prep != null) {
                            $auxtupla[0] = $secondaryverbslot->prep;
                            $auxtupla[1] = null;

                            $mainverbslot->slotstring[] = $auxtupla;
                        }
                        
                        // afegim la partícula QUE després del main verb
                        $auxtupla[0] = "que";
                        $auxtupla[1] = null;
                        $mainverbslot->slotstring[] = $auxtupla;
                    }
                    
                    $auxtupla[0] = $verbconjugat."@VERBUM";
                    $auxtupla[1] = $secondaryverbslot->paraulafinal;

                    $secondaryverbslot->slotstring[] = $auxtupla;
                }
            } // Fi desig
            
            // si no, CONJUGACIÓ NORMAL
            else {
                $verbconjugat = $CI->Lexicon->conjugarES($mainverbslot->paraulafinal->id, $tense, $persona, $numero, $this->pronominal);

                $auxtupla[0] = $verbconjugat."@VERBUM";
                $auxtupla[1] = $mainverbslot->paraulafinal;
                $auxtupla[8] = true;
                $mainverbslot->isInfinitive = ($tense == "infinitiu"); // pel perifràstic no cal posar 
                                        // els pronoms febles a darrere, ja que a davant hi queden millor
                
                $mainverbslot->slotstring[] = $auxtupla; // omplim l'slotstring
                
                if ($secondaryverbslot != null) {
                    
                    // si els subjectes eren iguals, també va en infinitiu
                    if ($this->subjsiguals) {
                        $verbconjugat = $CI->Lexicon->conjugarES($secondaryverbslot->paraulafinal->id, 'infinitiu', $persona2, $numero2, $this->pronominal2);
                        
                        $secondaryverbslot->isInfinitive = true;
                        
                        // posem la preposició que va davant del verb secundari, si n'hi havia
                        if ($secondaryverbslot->prep != null) {
                            $auxtupla[0] = $secondaryverbslot->prep;
                            $auxtupla[1] = null;

                            $secondaryverbslot->slotstring[] = $auxtupla;
                        }
                    }
                    // si no, aleshores va en subjuntiu (si fos en passat hauria d'anar en passat
                    // de subjuntiu, però el sistema encara no ho té
                    else {
                        $verbconjugat = $CI->Lexicon->conjugarES($secondaryverbslot->paraulafinal->id, 'prsubj', $persona2, $numero2, $this->pronominal2);
                        
                        // posem la preposició que darrere del verb principal, si n'hi havia
                        if ($secondaryverbslot->prep != null) {
                            $auxtupla[0] = $secondaryverbslot->prep;
                            $auxtupla[1] = null;

                            $mainverbslot->slotstring[] = $auxtupla;
                        }
                        
                        // afegim la partícula QUE després del main verb
                        $auxtupla[0] = "que";
                        $auxtupla[1] = null;
                        $mainverbslot->slotstring[] = $auxtupla;
                    }
                                        
                    $auxtupla[0] = $verbconjugat."@VERBUM";
                    $auxtupla[1] = $secondaryverbslot->paraulafinal;

                    $secondaryverbslot->slotstring[] = $auxtupla;
                }
            } // Fi conjugació normal
            
            
        } // Fi si no era verbless      
        
        // DEBUG
        // echo $this->printFraseFinalSlotString()."<br /><br />";
    }
    
    // Treure els "jo" i "tu" dels subjectes. Canviar receivers a pronoms febles i posar-los
    // a darrere el verb si cal. Posar modificadors de frase com el "no" o el "també".
    // Fusionar preposicions amb articles (de+el/s = del/s... a+el, per+el...). Posar apòstrofs 
    // de preps i pronoms febles (i guions?). Netejar espais abans o després dels apòstrofs.
    // Escriure la frase final, posant les expressions i altres advs de temps al final.
    public function launchCleaner($tipusfrase)
    {
        $CI = &get_instance();
        $CI->load->library('Mymatching');
        $CI->load->library('Myslot');
        
        $matching = new Mymatching();
        
        // TREURE ELS "JO" I "TU" i agafar info dels verbs per després
        $keymainverb = null;
        $indexmainverb = -1;
        $indexsecondaryverb = -1;
        
        $indextheme1 = -1;
        $indextheme2 = -1;
        
        $indexdesire = -1;
        $indexpermission = -1;
        
        $mainverbinf = false;
        $secondaryverbinf = false;
                
        $numslots = count($this->ordrefrase);
        
        for ($i=0; $i<$numslots; $i++) {
            $slotaux = $this->slotarray[$this->ordrefrase[$i]];
            
            if ($slotaux->category == "Main Verb") {
                $indexmainverb = $i;
                if ($slotaux->isInfinitive) $mainverbinf = true;
                $keymainverb = $this->ordrefrase[$i]; // agafem el key del main verb pel pas dels modificadors
            }
            else if ($slotaux->category == "Secondary Verb") {
                $indexsecondaryverb = $i;
                if ($slotaux->isInfinitive) $secondaryverbinf = true;
            }
            else if ($slotaux->category == "Theme") {
                if ($slotaux->level == 1 && $slotaux->defvalueused && $slotaux->defvalue == "ho") $indextheme1 = $i;
                else if ($slotaux->level == 2 && $slotaux->defvalueused && $slotaux->defvalue == "ho") $indextheme2 = $i;
            }
            else if ($slotaux->category == "Desire") $indexdesire = $i;
            else if ($slotaux->category == "Permission") $indexpermission = $i;
            else if ($slotaux->category == "Subject") {
                if ($slotaux->defvalueused) {
                    // esborrem el tu o jo o 3a persona impersonal o vostè quan hi són per defecte
                    if ($slotaux->defvalue == '1' || $slotaux->defvalue == '2' 
                            || $slotaux->defvalue == '3' || $slotaux->defvalue == '7') $slotaux->slotstring = array();
                }
                // si no s'ha fet servir el subjecte per defecte
                else {
                    // esborrem el tu o el jo (si no tenen cap element coordinat)
                    if (($slotaux->paraulafinal->text == "jo" || $slotaux->paraulafinal->text == "tu") 
                            && !$slotaux->paraulafinal->coord) {
                        $slotaux->slotstring = array();
                    }
                    // esborrem el subjecte del verb secundari, si és el mateix que el del principal
                    if ($slotaux->level == 2 && $this->subjsiguals) {
                        $slotaux->slotstring = array();
                    }
                }
            }
        }
        
        $indexreceiver1 = -1;
        $indexreceiver2 = -1;
        
        $receiver1pron = false;
        $receiver2pron = false;
        $theme1pron = false;
        $theme2pron = false;
        
        // si és una ordre els pronoms aniran darrere el verb i tindran una altra forma
        $ordre = ($tipusfrase == "ordre");
        $elementaux = array();
        
        
        // TRANSFORMAR ELS PRONOMS A FEBLES DEL RECEIVER O DEL THEME
        for ($i=0; $i<$numslots; $i++) {
            $slotaux = $this->slotarray[$this->ordrefrase[$i]];
            
            if ($slotaux->category == "Receiver") {
                // si hi ha valors per defecte
                if ($slotaux->defvalueused) {
                    if ($slotaux->defvalue == "mi") {
                        // posar "me", si és infinitiu o ordre positiva, els apòstrofs després
                        if (($slotaux->level == 1 && $mainverbinf) || 
                                ($slotaux->level == 1 && $ordre && !$this->frasenegativa)) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "me";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indexreceiver1 = $i;
                            $receiver1pron = true;
                        }
                        // posar "me"
                        else if ($slotaux->level == 2 && $secondaryverbinf) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "me";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indexreceiver2 = $i;
                            $receiver2pron = true;
                        }
                        // posar "em"
                        else {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "em";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            if ($slotaux->level == 1) {
                                $indexreceiver1 = $i;
                                $receiver1pron = true;
                            }
                            else if ($slotaux->level == 2) {
                                $indexreceiver2 = $i;
                                $receiver2pron = true;
                            }
                        }
                    }
                    else if ($slotaux->defvalue == "tu") {
                        // posar "te"
                        if ($slotaux->level == 1 && $mainverbinf || 
                                ($slotaux->level == 1 && $ordre && !$this->frasenegativa)) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "te";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indexreceiver1 = $i;
                            $receiver1pron = true;
                        }
                        // posar "te"
                        else if ($slotaux->level == 2 && $secondaryverbinf) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "te";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indexreceiver2 = $i;
                            $receiver2pron = true;
                        }
                        // posar "et"
                        else {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "et";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            if ($slotaux->level == 1) {
                                $indexreceiver1 = $i;
                                $receiver1pron = true;
                            }
                            else if ($slotaux->level == 2) {
                                $indexreceiver2 = $i;
                                $receiver2pron = true;
                            }
                        }
                    }
                }
                // si no hi ha valors per defecte -> transformar tots els pronoms personals
                else {
                    if (($slotaux->level == 1 && $mainverbinf) 
                            || ($slotaux->level == 1 && $ordre && !$this->frasenegativa)) {
                        // si són pronoms personals, posem la forma correcta pels receivers de darrere el verb
                        if ($matching->isPronomPers($slotaux->paraulafinal->text)) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = $matching->pronomsPersonalsAfterReceiver[$slotaux->paraulafinal->text];                          
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indexreceiver1 = $i;
                            $receiver1pron = true;
                        }                        
                    }
                    else if ($slotaux->level == 1) {
                        // si són pronoms personals, posem la forma correcta pels receivers d'abans del verb
                        if ($matching->isPronomPers($slotaux->paraulafinal->text)) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = $matching->pronomsPersonalsFrontReceiver[$slotaux->paraulafinal->text];                          
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indexreceiver1 = $i;
                            $receiver1pron = true;
                        }
                    }
                    else if ($slotaux->level == 2 && $secondaryverbinf) {
                        // si són pronoms personals, posem la forma correcta pels receivers de darrere el verb
                        if ($matching->isPronomPers($slotaux->paraulafinal->text)) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = $matching->pronomsPersonalsAfterReceiver[$slotaux->paraulafinal->text];                          
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indexreceiver2 = $i;
                            $receiver2pron = true;
                        }                        
                    }
                    else if ($slotaux->level == 2) {
                        // si són pronoms personals, posem la forma correcta pels receivers d'abans del verb
                        if ($matching->isPronomPers($slotaux->paraulafinal->text)) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = $matching->pronomsPersonalsFrontReceiver[$slotaux->paraulafinal->text];                          
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indexreceiver2 = $i;
                            $receiver2pron = true;
                        }
                    }                    
                }
            } // Fi if slotaux = receiver
            
            
            else if ($slotaux->category == "Theme") {
                // si hi ha valors per defecte
                if ($slotaux->defvalueused) {
                    if ($slotaux->defvalue == "jo") {
                        // posar "me", si és infinitiu o ordre positiva, els apòstrofs després
                        if (($slotaux->level == 1 && $mainverbinf) || 
                                ($slotaux->level == 1 && $ordre && !$this->frasenegativa)) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "me";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indextheme1 = $i;
                            $theme1pron = true;
                        }
                        // posar "me"
                        else if ($slotaux->level == 2 && $secondaryverbinf) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "me";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indextheme2 = $i;
                            $theme2pron = true;
                        }
                        // posar "em"
                        else {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "em";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            if ($slotaux->level == 1) {
                                $indextheme1 = $i;
                                $theme1pron = true;
                            }
                            else if ($slotaux->level == 2) {
                                $indextheme2 = $i;
                                $theme2pron = true;
                            }
                        }
                    }
                    else if ($slotaux->defvalue == "tu") {
                        // posar "te"
                        if ($slotaux->level == 1 && $mainverbinf || 
                                ($slotaux->level == 1 && $ordre && !$this->frasenegativa)) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "te";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indextheme1 = $i;
                            $theme1pron = true;
                        }
                        // posar "te"
                        else if ($slotaux->level == 2 && $secondaryverbinf) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "te";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indextheme2 = $i;
                            $theme2pron = true;
                        }
                        // posar "et"
                        else {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "et";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            if ($slotaux->level == 1) {
                                $indextheme1 = $i;
                                $theme1pron = true;
                            }
                            else if ($slotaux->level == 2) {
                                $indextheme2 = $i;
                                $theme2pron = true;
                            }
                        }
                    }
                }
                // si no hi ha valors per defecte -> transformar tots els pronoms personals
                else {
                    
                    $parauladerivada = $slotaux->slotstring[0];
                    $parauladerivada = $parauladerivada[0]; 
                    // si hi ha una preposicio davant del pronom, no hauria de fer el canvi
                    
                    if (($slotaux->level == 1 && $mainverbinf) 
                            || ($slotaux->level == 1 && $ordre && !$this->frasenegativa)) {
                        // si són pronoms personals, posem la forma correcta pels themes de darrere el verb
                        // menys si és un patró verbless, on la frase només tindrà el pronom
                        if ($matching->isPronomPers($parauladerivada) && !$this->verbless) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = $matching->pronomsPersonalsAfterTheme[$parauladerivada];                          
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indextheme1 = $i;
                            $theme1pron = true;
                        }                        
                    }
                    else if ($slotaux->level == 1) {
                        // si són pronoms personals, posem la forma correcta pels themes d'abans del verb
                        // menys si és un patró verbless, on la frase només tindrà el pronom
                        if ($matching->isPronomPers($parauladerivada) && !$this->verbless) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = $matching->pronomsPersonalsFrontTheme[$parauladerivada];                          
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indextheme1 = $i;
                            $theme1pron = true;
                        }
                    }
                    else if ($slotaux->level == 2 && $secondaryverbinf) {
                        // si són pronoms personals, posem la forma correcta pels themes de darrere el verb
                        if ($matching->isPronomPers($parauladerivada)) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = $matching->pronomsPersonalsAfterTheme[$parauladerivada];                          
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indextheme2 = $i;
                            $theme2pron = true;
                        }                        
                    }
                    else if ($slotaux->level == 2) {
                        // si són pronoms personals, posem la forma correcta pels themes d'abans del verb
                        if ($matching->isPronomPers($parauladerivada)) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = $matching->pronomsPersonalsFrontTheme[$parauladerivada];                          
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indextheme2 = $i;
                            $theme2pron = true;
                        }
                    }                    
                }
            } // Fi if slotaux = theme
            
            
        } // Fi for transformar pronoms
                
        // ORDRE DELS PRONOMS
        // amb tota la info recollida, movem els pronoms de lloc si cal
        // pel main verb si és infinitiu (els d'ordre ja són a darrere)
        if ($mainverbinf) {
            // el theme 1
            if ($indextheme1 != -1) {
                $temp = $this->ordrefrase[$indextheme1];
                // esborrem el theme 1 per moure'l de lloc
                array_splice($this->ordrefrase, $indextheme1, 1);
                // l'insertem just després del main verb
                array_splice($this->ordrefrase, $indexmainverb, 0, $temp);
                $indextheme1 = $indexmainverb;
                $indexmainverb -= 1;
            }
            // el receiver 1
            if ($indexreceiver1 != -1) {
                $temp = $this->ordrefrase[$indexreceiver1];
                // esborrem el theme 1 per moure'l de lloc
                array_splice($this->ordrefrase, $indexreceiver1, 1);
                // l'insertem just després del main verb
                array_splice($this->ordrefrase, $indexmainverb, 0, $temp);
                $indexreceiver1 = $indexmainverb;
                $indexmainverb -= 1;
            }
        }
        // pel verb secundari si és infinitiu
        if ($secondaryverbinf) {
            // el theme 2
            if ($indextheme2 != -1) {
                $temp = $this->ordrefrase[$indextheme2];
                // esborrem el theme 1 per moure'l de lloc
                array_splice($this->ordrefrase, $indextheme2, 1);
                // l'insertem just després del main verb
                array_splice($this->ordrefrase, $indexsecondaryverb, 0, $temp);
                $indextheme2 = $indexsecondaryverb;
                $indexsecondaryverb -= 1;
            }
            // el receiver 2
            if ($indexreceiver2 != -1) {
                $temp = $this->ordrefrase[$indexreceiver2];
                // esborrem el theme 1 per moure'l de lloc
                array_splice($this->ordrefrase, $indexreceiver2, 1);
                // l'insertem just després del main verb
                array_splice($this->ordrefrase, $indexsecondaryverb, 0, $temp);
                $indexreceiver2 = $indexsecondaryverb;
                $indexsecondaryverb -= 1;
            }
        }
        
        
        // ORDRE MODIFICADORS DE FRASE QUE NO VAN AL PRINCIPI: "NO", "TAMBÉ"
        // agafem els modificadors, si n'hi ha
        $slotmainverb = $this->slotarray[$keymainverb];
        $counter = 0;
        $indexsmodifs = array();
        $janegatiu = false;
        
        if ($slotmainverb->CModassigned) {
            for ($i=0; $i<count($slotmainverb->cmpMod); $i++) {
                
                $keymodifaux = $slotmainverb->CModassignedkey[$i];
                $slotmodifaux = $slotmainverb->cmpMod[$keymodifaux];

                // si és del grup que va darrere el subjecte (ex: no, també)
                if ($matching->isModAfterSubj($slotmodifaux->paraulafinal->text)) {
                    
                    // indiquem, que si la frase era negativa, ja no caldrà afegir el no
                    if ($slotmodifaux->paraulafinal->text == "no") $janegatiu = true;
                    
                    // Creem un slot, el posem a slotarray i de moment al final d'ordrefrase
                    $counter += 1;
                    $newslotmodif = new Myslot();
                    $newslotmodif->category = "Modifier ".$counter;
                    $newslotmodif->grade = "opt";
                    $newslotmodif->type = "modif";
                    $newslotmodif->full = true;
                    $newslotmodif->paraulafinal = $slotmodifaux->paraulafinal;
                    
                    $elementaux = array();
                    $elementaux[0] = $slotmodifaux->paraulafinal->text;
                    $elementaux[1] = $slotmodifaux->paraulafinal;
                    $newslotmodif->slotstring[] = $elementaux;
                    $newslotmodif->puntsfinal = 7;
                    
                    $this->slotarray["Modifier ".$counter] = $newslotmodif;
                    $this->ordrefrase[] = "Modifier ".$counter;
                    $indexsmodifs[] = $numslots;
                }
            }
        }
        
        // afegim un slot amb el no si la frase era negativa i no l'hem afegit ja
        if ($this->frasenegativa && !$janegatiu) {
            // Creem un slot, el posem a slotarray i de moment al final d'ordrefrase
            $counter += 1;
            $newslotmodif = new Myslot();
            $newslotmodif->category = "Modifier ".$counter;
            $newslotmodif->grade = "opt";
            $newslotmodif->type = "modif";
            $newslotmodif->full = true;

            $elementaux = array();
            $elementaux[0] = "no";
            $elementaux[1] = null;
            $newslotmodif->slotstring[] = $elementaux;
            $newslotmodif->puntsfinal = 7;

            $this->slotarray["Modifier ".$counter] = $newslotmodif;
            $this->ordrefrase[] = "Modifier ".$counter;
            $indexsmodifs[] = $numslots;
        }
            
        // si hem trobat algun d'aquests slots, els col·loquem al lloc on toqui
        if ($counter > 0) {
            for ($i=0; $i<count($indexsmodifs); $i++) {
                $indexmodifaux = $indexsmodifs[$i];
                
                // si hi ha un verb de desig, posar-los abans, amb els de permís van darrere del permís
                if ($indexdesire != -1) {
                    $indexaux = $indexdesire;
                    
                    $temp = $this->ordrefrase[$indexmodifaux];
                    // esborrem el modif per moure'l de lloc
                    array_splice($this->ordrefrase, $indexmodifaux, 1);
                    // l'insertem just abans del receiver
                    array_splice($this->ordrefrase, $indexaux, 0, $temp);
                }
                // si els possibles pronoms que hi hagi, van abans del verb principal
                else if (!$mainverbinf && (!$ordre || $this->frasenegativa)) {

                    // si hi ha receiver, que sempre va abans del theme en versió pronominal
                    // el posem abans del receiver
                    if ($receiver1pron) {
                        $temp = $this->ordrefrase[$indexmodifaux];
                        // esborrem el modif per moure'l de lloc
                        array_splice($this->ordrefrase, $indexmodifaux, 1);
                        // l'insertem just abans del receiver
                        array_splice($this->ordrefrase, $indexreceiver1, 0, $temp);
                    }
                    // si hi ha theme i no receiver, el posem abans del theme
                    else if ($indextheme1 != -1) {
                        $temp = $this->ordrefrase[$indexmodifaux];
                        // esborrem el modif per moure'l de lloc
                        array_splice($this->ordrefrase, $indexmodifaux, 1);
                        // l'insertem just abans del receiver
                        array_splice($this->ordrefrase, $indextheme1, 0, $temp);
                    }
                    // si no hi ha ni receiver, ni theme, el posem abans del mainverb
                    else {
                        $temp = $this->ordrefrase[$indexmodifaux];
                        // esborrem el modif per moure'l de lloc
                        array_splice($this->ordrefrase, $indexmodifaux, 1);
                        // l'insertem just abans del receiver
                        array_splice($this->ordrefrase, $indexmainverb, 0, $temp);
                    }
                }
                // si no, va just abans del verb
                else {
                    $temp = $this->ordrefrase[$indexmodifaux];
                    // esborrem el modif per moure'l de lloc
                    array_splice($this->ordrefrase, $indexmodifaux, 1);
                    // l'insertem just abans del receiver
                    array_splice($this->ordrefrase, $indexmainverb, 0, $temp);
                }
            }
        } // Fi si hi ha algun slot de modificador d'aquesta mena
        
        // AJUNTAR PREPS+ARTS / PRONOMS FEBLES I APÒSTROFS
        $frasebruta = $this->frasefinal.$this->printFraseFinalSlotString();
        
        // perps + arts
        $patterns[0] = '/[[:space:]][d][e][[:space:]][e][l][[:space:]]/u'; 
        $patterns[1] = '/[[:space:]][d][e][[:space:]][e][l][s][[:space:]]/u';
        $patterns[2] = '/[[:space:]][a][[:space:]][e][l][[:space:]]/u';
        $patterns[3] = '/[[:space:]][a][[:space:]][e][l][s][[:space:]]/u';
        $patterns[4] = '/[[:space:]][p][e][r][[:space:]][e][l][[:space:]]/u';
        $patterns[5] = '/[[:space:]][p][e][r][[:space:]][e][l][s][[:space:]]/u';
        
        // de => d'
        $patterns[6] = '/[[:space:][d][e][[:space:]](?=[(aeiouAEIOUhH)])/u'; 
        
        // em => m'; et => t'
        $patterns[7] = '/[[:space:]][e][m][[:space:]](?=[(aeiouAEIOUhH)])/u';
        $patterns[8] = '/[[:space:]][e][t][[:space:]](?=[(aeiouAEIOUhH)])/u';
        
        //verb acabat en vocal + pronoms de receiver/theme a darrere
        $patterns[9] = '/(?<=[aeio]@VERBUM)[[:space:]]me[[:space:]]/u';
        $patterns[10] = '/(?<=[aeio]@VERBUM)[[:space:]]te[[:space:]]/u';
        $patterns[11] = '/(?<=[aeiou]@VERBUM)[[:space:]]li[[:space:]]/u';
        $patterns[12] = '/(?<=[aeio]@VERBUM)[[:space:]]nos[[:space:]]/u';
        $patterns[13] = '/(?<=[aeiou]@VERBUM)[[:space:]]vos[[:space:]]/u';
        $patterns[14] = '/(?<=[aeio]@VERBUM)[[:space:]]los[[:space:]]/u';
        $patterns[38] = '/(?<=[aeio]@VERBUM)[[:space:]]lo[[:space:]]/u';
        $patterns[39] = '/(?<=[aeiou]@VERBUM)[[:space:]]@PRFEBLEla[[:space:]]/u';
        $patterns[42] = '/(?<=[aeiou]@VERBUM)[[:space:]]@PRFEBLEles[[:space:]]/u';
        $patterns[48] = '/(?<=[aeiou]@VERBUM)[[:space:]]ho[[:space:]]/u';
        
        // verb acabat en vocal+r + pronoms de receiver a darrere
        $patterns[15] = '/(?<=@VERBUM)[[:space:]]me[[:space:]]/u';
        $patterns[16] = '/(?<=@VERBUM)[[:space:]]te[[:space:]]/u';
        $patterns[17] = '/(?<=@VERBUM)[[:space:]]li[[:space:]]/u';
        $patterns[18] = '/(?<=@VERBUM)[[:space:]]nos[[:space:]]/u';
        $patterns[19] = '/(?<=@VERBUM)[[:space:]]vos[[:space:]]/u';
        $patterns[20] = '/(?<=@VERBUM)[[:space:]]los[[:space:]]/u';
        $patterns[40] = '/(?<=@VERBUM)[[:space:]]lo[[:space:]]/u';
        $patterns[41] = '/(?<=@VERBUM)[[:space:]]@PRFEBLEla[[:space:]]/u';
        $patterns[43] = '/(?<=@VERBUM)[[:space:]]@PRFEBLEles[[:space:]]/u';
        $patterns[49] = '/(?<=@VERBUM)[[:space:]]ho[[:space:]]/u';
        
        // verb+pronom feble de receiver ja enganxat, seguit de "ho"
        $patterns[21] = "/(?<=@VERBUM)'m[[:space:]]ho[[:space:]]/u";
        $patterns[22] = "/(?<=@VERBUM)'t[[:space:]]ho[[:space:]]/u";
        $patterns[23] = "/(?<=@VERBUM)-li[[:space:]]ho[[:space:]]/u";
        $patterns[24] = "/(?<=@VERBUM)'ns[[:space:]]ho[[:space:]]/u";
        $patterns[25] = "/(?<=@VERBUM)-vos[[:space:]]ho[[:space:]]/u";
        $patterns[26] = "/(?<=@VERBUM)'ls[[:space:]]ho[[:space:]]/u";
        
        $patterns[27] = "/(?<=@VERBUM)-me[[:space:]]ho[[:space:]]/u";
        $patterns[28] = "/(?<=@VERBUM)-te[[:space:]]ho[[:space:]]/u";
        $patterns[29] = "/(?<=@VERBUM)-nos[[:space:]]ho[[:space:]]/u";
        $patterns[30] = "/(?<=@VERBUM)-vos[[:space:]]ho[[:space:]]/u";
        $patterns[31] = "/(?<=@VERBUM)-los[[:space:]]ho[[:space:]]/u";
        
        // netejar espais abans i després dels apòstrofs, si n'hi ha
        $patterns[32] = "/[[:space:]]'/u";
        $patterns[33] = "/'[[:space:]]/u";
        
        // netejar @VERBUM
        $patterns[50] = "/@VERBUM/u";
        
        // canviar els pronoms febles el i la, per l', si cal
        $patterns[35] = "/(?<=@PRFEBLE)el[[:space:]](?=[(aeiouAEIOUhH)])/u";
        $patterns[36] = "/(?<=@PRFEBLE)la[[:space:]](?=[(aeiouAEIOUhH)])/u";
        
        // netejar @PRFEBLE
        $patterns[51] = "/@PRFEBLE/u";
        
        //preps + pronoms personals
        $patterns[44] = '/[[:space:]][p][e][r][[:space:]][j][o][[:space:]]/u';
        $patterns[45] = '/[[:space:]][a][m][b][[:space:]][j][o][[:space:]]/u';
        $patterns[46] = '/[[:space:]][a][[:space:]][j][o][[:space:]]/u';
        $patterns[47] = '/[[:space:]][d][e][[:space:]][j][o][[:space:]]/u';
        $patterns[34] = '/[[:space:]][e][n][[:space:]][j][o][[:space:]]/u';
        
        
        $replacements[0] = ' del ';
        $replacements[1] = ' dels ';
        $replacements[2] = ' al ';
        $replacements[3] = ' als ';
        $replacements[4] = ' pel ';
        $replacements[5] = ' pels ';
        
        $replacements[6] = " d'";
        
        $replacements[7] = " m'";
        $replacements[8] = " t'";
        
        $replacements[9] = "'m ";
        $replacements[10] = "'t ";
        $replacements[11] = "-li ";
        $replacements[12] = "'ns ";
        $replacements[13] = "-vos ";
        $replacements[14] = "'ls ";
        $replacements[38] = "'l ";
        $replacements[39] = "-la ";
        $replacements[42] = "-les ";
        $replacements[48] = "-ho ";
        
        $replacements[15] = "-me ";
        $replacements[16] = "-te ";
        $replacements[17] = "-li ";
        $replacements[18] = "-nos ";
        $replacements[19] = "-vos ";
        $replacements[20] = "-los ";
        $replacements[40] = "-lo ";
        $replacements[41] = "-la ";
        $replacements[43] = "-les ";
        $replacements[49] = "-ho ";
        
        $replacements[21] = "-m'ho ";
        $replacements[22] = "-t'ho ";
        $replacements[23] = "-li-ho ";
        $replacements[24] = "'ns-ho ";
        $replacements[25] = "-vos-ho ";
        $replacements[26] = "'ls-ho ";
        
        $replacements[27] = "-m'ho ";
        $replacements[28] = "-t'ho ";
        $replacements[29] = "-nos-ho ";
        $replacements[30] = "-vos-ho ";
        $replacements[31] = "-los-ho ";
        
        $replacements[32] = "'";
        $replacements[33] = "'";
        
        $replacements[50] = "";
        
        $replacements[35] = "l'";
        $replacements[36] = "l'";
        $replacements[51] = "";
        
        $replacements[44] = ' per mi ';
        $replacements[45] = ' amb mi ';
        $replacements[46] = ' a mi ';
        $replacements[47] = ' de mi ';
        $replacements[34] = ' en mi ';
        
        $frasebruta = preg_replace($patterns, $replacements, $frasebruta);
        
        // fem una assignació prèvia
        $this->frasefinal = $frasebruta;
        
        // afegim les expressions de temps que van a darrere, si n'hi havia
        for ($i=0; $i<count($this->timeexpr); $i++) {
            $wordaux = $this->timeexpr[$i];
            
            if (!$matching->isFrontAdvTemps($wordaux->text)) {

                // el posem darrere de la frase
                $frasebruta .= $wordaux->text." ";
            }
        }
        
        // afegim les expressions a davant o a darrere de la frase, si la frase no era buida,
        // afegim una coma abans -> CANVIAT, ja no afegim la coma
        
        $numexprs = count($this->exprsarray);
        $capitalafterexpr = false;
        
        /* if ($numexprs > 0 && $this->frasefinal != " ") {
            $frasebruta .= ", ";
            $frasebruta = preg_replace("/[[:space:]],/u", ",", $frasebruta);
        } */
        for ($i=0; $i<$numexprs; $i++) {
            // l'hola sempre va a davant i les que tenen 1 a la propietat front           
            if ($this->exprsarray[$i][1] == '1') {
                $fraseaux = $frasebruta;
                $frasebruta = " ".$this->exprsarray[$i][0];
                $llargexpr = strlen($this->exprsarray[$i][0]);
                $lastcharexpr = $this->exprsarray[$i][0][$llargexpr-1];
                // passem a majúscula la frase, si l'expressió de davant acaba en ".", "?", "!".
                if ($lastcharexpr != "?" || $lastcharexpr != "." || $lastcharexpr == "!") {
                    $fraseaux = preg_replace("/[[:space:]]$/u", "", $fraseaux);
                    if (isset($fraseaux[0])) {
                        if ($fraseaux[0] == " ") $fraseaux[1] = strtoupper($fraseaux[1]); 
                        else $fraseaux[0] = strtoupper($fraseaux[0]); 
                    }
                }
                
                // si la frase no era buida, afegim una coma
                if (($this->frasefinal != " " || $numexprs > 1) && $lastcharexpr != "?"
                        && $lastcharexpr != ".") $frasebruta = $frasebruta.", ";
                
                $frasebruta .= $fraseaux;
            }
            else $frasebruta .= $this->exprsarray[$i][0]." ";
        }
        
        // POSAR ELS PUNTS O EXCLAMACIONS O INTERROGANTS
        // esborrar si hi ha dos espais junts, l'últim espai i una coma al final si hi és. També l'espai del principi
        $frasebruta = preg_replace("/[[:space:]][[:space:]]/u", " ", $frasebruta);
        $frasebruta = preg_replace("/[[:space:]]$/u", "", $frasebruta);
        $frasebruta = preg_replace("/,$/u", "", $frasebruta);
        $frasebruta = substr($frasebruta, 1);
        
        $frasebruta[0] = strtoupper($frasebruta[0]);
        
        $llargfrase = strlen($frasebruta);
        $lastcharacter = $frasebruta[$llargfrase-1];
        
        if ($lastcharacter != "?") {
            if ($tipusfrase == "exclamacio") $frasebruta .= "!";
            else if ($tipusfrase == "pregunta" || $tipusfrase == "permis") $frasebruta .= "?";
            else $frasebruta .= ".";
        }
        
        $this->frasefinal = $frasebruta;
        
        // DEBUG
        // echo $frasebruta.'<br /><br />';
    }

    // Treure els "jo" i "tu" dels subjectes. Canviar receivers a pronoms febles i posar-los
    // a darrere el verb si cal. Posar accents a les noves formes verbals, si cal.
    // Posar modificadors de frase com el "no" o el "també".
    // Fusionar preposicions amb articles (de+el/s = del/s... a+el...).
    // Escriure la frase final, posant les expressions i altres advs de temps al final.
    public function launchCleanerES($tipusfrase)
    {
        $CI = &get_instance();
        $CI->load->library('Mymatching');
        $CI->load->library('Myslot');
        
        $matching = new Mymatching();
        
        // TREURE ELS "YO" I "TÚ" i agafar info dels verbs per després
        $keymainverb = null;
        $indexmainverb = -1;
        $indexsecondaryverb = -1;
        
        $indextheme1 = -1;
        $indextheme2 = -1;
        
        $indexdesire = -1;
        $indexpermission = -1;
        
        $mainverbinf = false;
        $secondaryverbinf = false;
                
        $numslots = count($this->ordrefrase);
        
        for ($i=0; $i<$numslots; $i++) {
            $slotaux = $this->slotarray[$this->ordrefrase[$i]];
            
            if ($slotaux->category == "Main Verb") {
                $indexmainverb = $i;
                if ($slotaux->isInfinitive) $mainverbinf = true;
                $keymainverb = $this->ordrefrase[$i]; // agafem el key del main verb pel pas dels modificadors
            }
            else if ($slotaux->category == "Secondary Verb") {
                $indexsecondaryverb = $i;
                if ($slotaux->isInfinitive) $secondaryverbinf = true;
            }
            else if ($slotaux->category == "Theme") {
                if ($slotaux->level == 1 && $slotaux->defvalueused && $slotaux->defvalue == "lo") $indextheme1 = $i;
                else if ($slotaux->level == 2 && $slotaux->defvalueused && $slotaux->defvalue == "lo") $indextheme2 = $i;
            }
            else if ($slotaux->category == "Desire") $indexdesire = $i;
            else if ($slotaux->category == "Permission") $indexpermission = $i;
            else if ($slotaux->category == "Subject") {
                
                // si l'idioma d'expansió no podia expandir i s'ha fet servir el castellà per defecte
                // que no elimini els subjectes perquè així per la posterior traducció hi haurà menys
                // ambigüitats (només pel primer nivell i si les frases no són ordres)
                if ($slotaux->defvalueused) {
                    if ($CI->session->userdata('explangcannotexpand') == '1' && $slotaux->level != 2
                            && $tipusfrase != "ordre") {
                        switch ($slotaux->defvalue) {
                            case '1':
                                $slotaux->slotstring = array();
                                $elementaux[0] = "yo";
                                $elementaux[1] = null;
                                $slotaux->slotstring[] = $elementaux;
                                break;
                            case '2':
                                $slotaux->slotstring = array();
                                $elementaux[0] = "tú";
                                $elementaux[1] = null;
                                $slotaux->slotstring[] = $elementaux;
                                break;
                            case '3':
                                $slotaux->slotstring = array();
                                break;
                            case '7':
                                $slotaux->slotstring = array();
                                $elementaux[0] = "usted";
                                $elementaux[1] = null;
                                $slotaux->slotstring[] = $elementaux;
                                break;

                            default:
                                break;
                        }
                    }
                    else {
                        // esborrem el tú o yo, 3a persona impersonal o usted quan hi són per defecte
                        if ($slotaux->defvalue == '1' || $slotaux->defvalue == '2' 
                                || $slotaux->defvalue == '3' || $slotaux->defvalue == '7') $slotaux->slotstring = array();
                    }
                }
                // si no s'ha fet servir el subjecte per defecte o era del segon nivell o era una ordre
                else {
                    if ($CI->session->userdata('explangcannotexpand') != '1' || $slotaux->level == 2
                            || $tipusfrase == "ordre") {
                        // esborrem el tú o el yo, si no tenen elements coordinats
                        if (($slotaux->paraulafinal->text == "yo" || $slotaux->paraulafinal->text == "tú")
                                && !$slotaux->paraulafinal->coord) {
                            $slotaux->slotstring = array();
                        }
                        // esborrem el subjecte del verb secundari, si és el mateix que el del principal
                        if ($slotaux->level == 2 && $this->subjsiguals) {
                            $slotaux->slotstring = array();
                        }
                    }
                }
                
            }
        }
        
        $indexreceiver1 = -1;
        $indexreceiver2 = -1;
        
        $receiver1pron = false;
        $receiver2pron = false;
        $theme1pron = false;
        $theme2pron = false;
        
        // si és una ordre els pronoms aniran darrere el verb i tindran una altra forma
        $ordre = ($tipusfrase == "ordre");
        $elementaux = array();
                
        
        // TRANSFORMAR ELS PRONOMS A FEBLES DEL RECEIVER O DEL THEME
        for ($i=0; $i<$numslots; $i++) {
            $slotaux = $this->slotarray[$this->ordrefrase[$i]];
            
            if ($slotaux->category == "Receiver") {
                // si hi ha valors per defecte
                if ($slotaux->defvalueused) {
                    if ($slotaux->defvalue == "mí") {
                        // posar "me" en tots els casos
                        if ($slotaux->level == 1) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "me";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indexreceiver1 = $i;
                            $receiver1pron = true;
                        }
                        // posar "me"
                        else if ($slotaux->level == 2) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "me";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indexreceiver2 = $i;
                            $receiver2pron = true;
                        }
                    }
                    else if ($slotaux->defvalue == "tú") {
                        // posar "te"
                        if ($slotaux->level == 1) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "te";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indexreceiver1 = $i;
                            $receiver1pron = true;
                        }
                        // posar "te"
                        else if ($slotaux->level == 2) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "te";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indexreceiver2 = $i;
                            $receiver2pron = true;
                        }
                    }
                }
                // si no hi ha valors per defecte -> transformar tots els pronoms personals
                else {
                    if (($slotaux->level == 1 && $mainverbinf) 
                            || ($slotaux->level == 1 && $ordre && !$this->frasenegativa)) {
                        // si són pronoms personals, posem la forma correcta pels receivers de darrere el verb
                        if ($matching->isPronomPersES($slotaux->paraulafinal->text)) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = $matching->pronomsPersonalsReceiverES[$slotaux->paraulafinal->text];                          
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indexreceiver1 = $i;
                            $receiver1pron = true;
                        }                        
                    }
                    else if ($slotaux->level == 1) {
                        // si són pronoms personals, posem la forma correcta pels receivers d'abans del verb
                        if ($matching->isPronomPersES($slotaux->paraulafinal->text)) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = $matching->pronomsPersonalsReceiverES[$slotaux->paraulafinal->text];                          
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indexreceiver1 = $i;
                            $receiver1pron = true;
                        }
                    }
                    else if ($slotaux->level == 2 && $secondaryverbinf) {
                        // si són pronoms personals, posem la forma correcta pels receivers de darrere el verb
                        if ($matching->isPronomPersES($slotaux->paraulafinal->text)) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = $matching->pronomsPersonalsReceiverES[$slotaux->paraulafinal->text];                          
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indexreceiver2 = $i;
                            $receiver2pron = true;
                        }                        
                    }
                    else if ($slotaux->level == 2) {
                        // si són pronoms personals, posem la forma correcta pels receivers d'abans del verb
                        if ($matching->isPronomPersES($slotaux->paraulafinal->text)) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = $matching->pronomsPersonalsReceiverES[$slotaux->paraulafinal->text];                          
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indexreceiver2 = $i;
                            $receiver2pron = true;
                        }
                    }                    
                }
            } // Fi if slotaux = receiver
            
            
            if ($slotaux->category == "Theme") {
                // si hi ha valors per defecte
                if ($slotaux->defvalueused) {
                    if ($slotaux->defvalue == "yo") {
                        // posar "me" en tots els casos
                        if ($slotaux->level == 1) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "me";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indextheme1 = $i;
                            $theme1pron = true;
                        }
                        // posar "me"
                        else if ($slotaux->level == 2) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "me";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indextheme2 = $i;
                            $theme2pron = true;
                        }
                    }
                    else if ($slotaux->defvalue == "tú") {
                        // posar "te"
                        if ($slotaux->level == 1) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "te";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indextheme1 = $i;
                            $theme1pron = true;
                        }
                        // posar "te"
                        else if ($slotaux->level == 2) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = "te";
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indextheme2 = $i;
                            $theme2pron = true;
                        }
                    }
                }
                // si no hi ha valors per defecte -> transformar tots els pronoms personals
                else {
                    
                    $parauladerivada = $slotaux->slotstring[0];
                    $parauladerivada = $parauladerivada[0];
                    
                    if (($slotaux->level == 1 && $mainverbinf) 
                            || ($slotaux->level == 1 && $ordre && !$this->frasenegativa)) {
                        // si són pronoms personals, posem la forma correcta pels receivers de darrere el verb
                        // menys si és un patró verbless, on la frase només tindrà el pronom
                        if ($matching->isPronomPersES($parauladerivada) && !$this->verbless) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = $matching->pronomsPersonalsThemeES[$parauladerivada];                          
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indextheme1 = $i;
                            $theme1pron = true;
                        }                        
                    }
                    else if ($slotaux->level == 1) {
                        // si són pronoms personals, posem la forma correcta pels receivers d'abans del verb
                        // menys si és un patró verbless, on la frase només tindrà el pronom
                        if ($matching->isPronomPersES($parauladerivada) && !$this->verbless) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = $matching->pronomsPersonalsThemeES[$parauladerivada];                          
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indextheme1 = $i;
                            $theme1pron = true;
                        }
                    }
                    else if ($slotaux->level == 2 && $secondaryverbinf) {
                        // si són pronoms personals, posem la forma correcta pels receivers de darrere el verb
                        if ($matching->isPronomPersES($parauladerivada)) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = $matching->pronomsPersonalsThemeES[$parauladerivada];                          
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indextheme2 = $i;
                            $theme2pron = true;
                        }                        
                    }
                    else if ($slotaux->level == 2) {
                        // si són pronoms personals, posem la forma correcta pels receivers d'abans del verb
                        if ($matching->isPronomPersES($parauladerivada)) {
                            $slotaux->slotstring = array();
                            $elementaux[0] = $matching->pronomsPersonalsThemeES[$parauladerivada];                          
                            $elementaux[1] = null;
                            $slotaux->slotstring[] = $elementaux;
                            $indextheme2 = $i;
                            $theme2pron = true;
                        }
                    }                    
                }
            }
            
        } // Fi for transformar pronoms
                
                        
        // ORDRE DELS PRONOMS
        // amb tota la info recollida, movem els pronoms de lloc si cal
        // pel main verb si és infinitiu (els d'ordre ja són a darrere)
        if ($mainverbinf) {
            // el theme 1
            if ($indextheme1 != -1) {
                $temp = $this->ordrefrase[$indextheme1];
                // esborrem el theme 1 per moure'l de lloc
                array_splice($this->ordrefrase, $indextheme1, 1);
                // l'insertem just després del main verb
                array_splice($this->ordrefrase, $indexmainverb, 0, $temp);
                $indextheme1 = $indexmainverb;
                $indexmainverb -= 1;
            }
            // el receiver 1
            if ($indexreceiver1 != -1) {
                $temp = $this->ordrefrase[$indexreceiver1];
                // esborrem el receiver 1 per moure'l de lloc
                array_splice($this->ordrefrase, $indexreceiver1, 1);
                // l'insertem just després del main verb
                array_splice($this->ordrefrase, $indexmainverb, 0, $temp);
                $indexreceiver1 = $indexmainverb;
                $indexmainverb -= 1;
            }
        }
        // pel verb secundari si és infinitiu
        if ($secondaryverbinf) {
            // el theme 2
            if ($indextheme2 != -1) {
                $temp = $this->ordrefrase[$indextheme2];
                // esborrem el theme 2 per moure'l de lloc
                array_splice($this->ordrefrase, $indextheme2, 1);
                // l'insertem just després del main verb
                array_splice($this->ordrefrase, $indexsecondaryverb, 0, $temp);
                $indextheme2 = $indexsecondaryverb;
                $indexsecondaryverb -= 1;
            }
            // el receiver 2
            if ($indexreceiver2 != -1) {
                $temp = $this->ordrefrase[$indexreceiver2];
                // esborrem el receiver 2 per moure'l de lloc
                array_splice($this->ordrefrase, $indexreceiver2, 1);
                // l'insertem just després del main verb
                array_splice($this->ordrefrase, $indexsecondaryverb, 0, $temp);
                $indexreceiver2 = $indexsecondaryverb;
                $indexsecondaryverb -= 1;
            }
        }
        
        
        // ORDRE MODIFICADORS DE FRASE QUE NO VAN AL PRINCIPI: "NO", "TAMBÉ"
        // agafem els modificadors, si n'hi ha
        $slotmainverb = $this->slotarray[$keymainverb];
        $counter = 0;
        $indexsmodifs = array();
        $janegatiu = false;
        
        if ($slotmainverb->CModassigned) {
            for ($i=0; $i<count($slotmainverb->cmpMod); $i++) {
                
                $keymodifaux = $slotmainverb->CModassignedkey[$i];
                $slotmodifaux = $slotmainverb->cmpMod[$keymodifaux];

                // si és del grup que va darrere el subjecte (ex: no, también)
                if ($matching->isModAfterSubjES($slotmodifaux->paraulafinal->text)) {
                    
                    // indiquem, que si la frase era negativa, ja no caldrà afegir el no
                    if ($slotmodifaux->paraulafinal->text == "no") $janegatiu = true;
                    
                    // Creem un slot, el posem a slotarray i de moment al final d'ordrefrase
                    $counter += 1;
                    $newslotmodif = new Myslot();
                    $newslotmodif->category = "Modifier ".$counter;
                    $newslotmodif->grade = "opt";
                    $newslotmodif->type = "modif";
                    $newslotmodif->full = true;
                    $newslotmodif->paraulafinal = $slotmodifaux->paraulafinal;
                    
                    $elementaux = array();
                    $elementaux[0] = $slotmodifaux->paraulafinal->text;
                    $elementaux[1] = $slotmodifaux->paraulafinal;
                    $newslotmodif->slotstring[] = $elementaux;
                    $newslotmodif->puntsfinal = 7;
                    
                    $this->slotarray["Modifier ".$counter] = $newslotmodif;
                    $this->ordrefrase[] = "Modifier ".$counter;
                    $indexsmodifs[] = $numslots;
                }
            }
        }
        
        // afegim un slot amb el no si la frase era negativa i no l'hem afegit ja
        if ($this->frasenegativa && !$janegatiu) {
            // Creem un slot, el posem a slotarray i de moment al final d'ordrefrase
            $counter += 1;
            $newslotmodif = new Myslot();
            $newslotmodif->category = "Modifier ".$counter;
            $newslotmodif->grade = "opt";
            $newslotmodif->type = "modif";
            $newslotmodif->full = true;

            $elementaux = array();
            $elementaux[0] = "no";
            $elementaux[1] = null;
            $newslotmodif->slotstring[] = $elementaux;
            $newslotmodif->puntsfinal = 7;

            $this->slotarray["Modifier ".$counter] = $newslotmodif;
            $this->ordrefrase[] = "Modifier ".$counter;
            $indexsmodifs[] = $numslots;
        }
            
        // si hem trobat algun d'aquests slots, els col·loquem al lloc on toqui
        if ($counter > 0) {
            for ($i=0; $i<count($indexsmodifs); $i++) {
                $indexmodifaux = $indexsmodifs[$i];
                
                // si hi ha un verb de desig, posar-los abans, amb els de permís van darrere del permís
                if ($indexdesire != -1) {
                    $indexaux = $indexdesire;
                    
                    $temp = $this->ordrefrase[$indexmodifaux];
                    // esborrem el modif per moure'l de lloc
                    array_splice($this->ordrefrase, $indexmodifaux, 1);
                    // l'insertem just abans del desig
                    array_splice($this->ordrefrase, $indexaux, 0, $temp);
                }
                // si els possibles pronoms que hi hagi, van abans del verb principal
                else if (!$mainverbinf && (!$ordre || $this->frasenegativa)) {

                    // si hi ha receiver, que sempre va abans del theme en versió pronominal
                    // el posem abans del receiver
                    if ($receiver1pron) {
                        $temp = $this->ordrefrase[$indexmodifaux];
                        // esborrem el modif per moure'l de lloc
                        array_splice($this->ordrefrase, $indexmodifaux, 1);
                        // l'insertem just abans del receiver
                        array_splice($this->ordrefrase, $indexreceiver1, 0, $temp);
                    }
                    // si hi ha theme i no receiver, el posem abans del theme
                    else if ($indextheme1 != -1) {
                        $temp = $this->ordrefrase[$indexmodifaux];
                        // esborrem el modif per moure'l de lloc
                        array_splice($this->ordrefrase, $indexmodifaux, 1);
                        // l'insertem just abans del theme
                        array_splice($this->ordrefrase, $indextheme1, 0, $temp);
                    }
                    // si no hi ha ni receiver, ni theme, el posem abans del mainverb
                    else {
                        $temp = $this->ordrefrase[$indexmodifaux];
                        // esborrem el modif per moure'l de lloc
                        array_splice($this->ordrefrase, $indexmodifaux, 1);
                        // l'insertem just abans del mainverb
                        array_splice($this->ordrefrase, $indexmainverb, 0, $temp);
                    }
                }
                // si no, va just abans del verb
                else {
                    $temp = $this->ordrefrase[$indexmodifaux];
                    // esborrem el modif per moure'l de lloc
                    array_splice($this->ordrefrase, $indexmodifaux, 1);
                    // l'insertem just abans del mainverb
                    array_splice($this->ordrefrase, $indexmainverb, 0, $temp);
                }
            }
        } // Fi si hi ha algun slot de modificador d'aquesta mena
        
        // AJUNTAR PREPS+ARTS / PRONOMS FEBLES I APÒSTROFS
        $frasebruta = $this->frasefinal.$this->printFraseFinalSlotString();
        
        // perps + arts
        $patterns[0] = '/[[:space:]][d][e][[:space:]][e][l][[:space:]]/u'; 
        $patterns[1] = '/[[:space:]][a][[:space:]][e][l][[:space:]]/u';

        // verb + pronoms de theme a darrere
        $patterns[2] = '/(?<=@VERBUM)[[:space:]]lo[[:space:]]/u';
        $patterns[14] = '/(?<=@VERBUM)[[:space:]]@PRFEBLEla[[:space:]]/u';
        $patterns[15] = '/(?<=@VERBUM)[[:space:]]@PRFEBLElos[[:space:]]/u';
        $patterns[16] = '/(?<=@VERBUM)[[:space:]]@PRFEBLElas[[:space:]]/u';
        
        // verb + pronoms de receiver a darrere
        $patterns[3] = '/(?<=@VERBUM)[[:space:]]me[[:space:]]/u';
        $patterns[4] = '/(?<=@VERBUM)[[:space:]]te[[:space:]]/u';
        $patterns[5] = '/(?<=@VERBUM)[[:space:]]se[[:space:]]/u';
        $patterns[6] = '/(?<=@VERBUM)[[:space:]]nos[[:space:]]/u';
        $patterns[7] = '/(?<=@VERBUM)[[:space:]]os[[:space:]]/u';
        $patterns[30] = '/(?<=@VERBUM)[[:space:]]le[[:space:]]/u';
        
        // verb+pronom feble de receiver ja enganxat, seguit de "lo"
        $patterns[8] = "/(?<=@VERBUM)me[[:space:]]lo[[:space:]]/u";
        $patterns[9] = "/(?<=@VERBUM)te[[:space:]]lo[[:space:]]/u";
        $patterns[10] = "/(?<=@VERBUM)se[[:space:]]lo[[:space:]]/u";
        $patterns[11] = "/(?<=@VERBUM)nos[[:space:]]lo[[:space:]]/u";
        $patterns[12] = "/(?<=@VERBUM)os[[:space:]]lo[[:space:]]/u";
        
        // netejar @VERBUM i @PRFEBLE
        $patterns[28] = "/@VERBUM/u";
        $patterns[29] = "/@PRFEBLE/u";
        
        // preps + pronoms personals
        $patterns[17] = '/[[:space:]][c][o][n][[:space:]][y][o][[:space:]]/u';
        $patterns[18] = '/[[:space:]][c][o][n][[:space:]][m][í][[:space:]]/u';
        $patterns[19] = '/[[:space:]][c][o][n][[:space:]][t][ú][[:space:]]/u';
        $patterns[20] = '/[[:space:]][c][o][n][[:space:]][t][i][[:space:]]/u';
        $patterns[21] = '/[[:space:]][p][a][r][a][[:space:]][y][o][[:space:]]/u';
        $patterns[22] = '/[[:space:]][p][a][r][a][[:space:]][t][ú][[:space:]]/u';
        $patterns[23] = '/[[:space:]][a][[:space:]][y][o][[:space:]]/u';
        $patterns[24] = '/[[:space:]][a][[:space:]][t][ú][[:space:]]/u';
        $patterns[25] = '/[[:space:]][d][e][[:space:]][y][o][[:space:]]/u';
        $patterns[26] = '/[[:space:]][d][e][[:space:]][t][ú][[:space:]]/u';
        $patterns[13] = '/[[:space:]][e][n][[:space:]][y][o][[:space:]]/u';
        $patterns[27] = '/[[:space:]][e][n][[:space:]][t][ú][[:space:]]/u';
        
        
        $replacements[0] = ' del ';
        $replacements[1] = ' al ';
        
        $replacements[2] = "lo";
        $replacements[14] = "la ";
        $replacements[15] = "los ";
        $replacements[16] = "las ";
        
        $replacements[3] = "me ";
        $replacements[4] = "te ";
        $replacements[5] = "se ";
        $replacements[6] = "nos ";
        $replacements[7] = "os ";
        $replacements[30] = "le ";
        
        $replacements[8] = "melo ";
        $replacements[9] = "telo ";
        $replacements[10] = "selo ";
        $replacements[11] = "noslo ";
        $replacements[12] = "roslo ";
        
        $replacements[28] = "";
        $replacements[29] = "";
        
        $replacements[17] = ' conmigo ';
        $replacements[18] = ' conmigo ';
        $replacements[19] = ' contigo ';
        $replacements[20] = ' contigo ';
        $replacements[21] = ' para mí ';
        $replacements[22] = ' para ti ';
        $replacements[23] = ' a mí ';
        $replacements[24] = ' a ti ';
        $replacements[25] = ' de mí ';
        $replacements[26] = ' de ti ';
        $replacements[13] = ' en mí ';
        $replacements[27] = ' en ti ';
        
        $frasebruta = preg_replace($patterns, $replacements, $frasebruta);
        
        // fem una assignació prèvia
        $this->frasefinal = $frasebruta;
        
        // afegim les expressions de temps que van a darrere, si n'hi havia
        for ($i=0; $i<count($this->timeexpr); $i++) {
            $wordaux = $this->timeexpr[$i];
            
            if (!$matching->isFrontAdvTempsES($wordaux->text)) {

                // el posem darrere de la frase
                $frasebruta .= $wordaux->text." ";
            }
        }
        
        // afegim les expressions a davant o a darrere de la frase, si la frase no era buida,
        // afegim una coma abans -> CANVIAT, ja no afegim la coma
        
        $numexprs = count($this->exprsarray);
        $capitalafterexpr = false;
        
        /* if ($numexprs > 0 && $this->frasefinal != " ") {
            $frasebruta .= ", ";
            $frasebruta = preg_replace("/[[:space:]],/u", ",", $frasebruta);
        } */
        for ($i=0; $i<$numexprs; $i++) {
            // l'hola sempre va a davant i les que tenen 1 a la propietat front           
            if ($this->exprsarray[$i][1] == '1') {
                $fraseaux = $frasebruta;
                $frasebruta = " ".$this->exprsarray[$i][0];
                $llargexpr = strlen($this->exprsarray[$i][0]);
                $lastcharexpr = $this->exprsarray[$i][0][$llargexpr-1];
                // passem a majúscula la frase, si l'expressió de davant acaba en ".", "?", "!".
                if ($lastcharexpr != "?" || $lastcharexpr != "." || $lastcharexpr == "!") {
                    $fraseaux = preg_replace("/[[:space:]]$/u", "", $fraseaux);
                    if (isset($fraseaux[0])) {
                        if ($fraseaux[0] == " ") $fraseaux[1] = strtoupper($fraseaux[1]); 
                        else $fraseaux[0] = strtoupper($fraseaux[0]); 
                    }
                }
                
                // si la frase no era buida, afegim una coma
                if (($this->frasefinal != " " || $numexprs > 1) && $lastcharexpr != "?"
                        && $lastcharexpr != ".") $frasebruta = $frasebruta.", ";
                
                $frasebruta .= $fraseaux;
            }
            else $frasebruta .= $this->exprsarray[$i][0]." ";
        }
        
        // POSAR ELS PUNTS O EXCLAMACIONS O INTERROGANTS
        // esborrar si hi ha dos espais junts, l'últim espai i una coma al final si hi és. També l'espai del principi
        $frasebruta = preg_replace("/[[:space:]][[:space:]]/u", " ", $frasebruta);
        $frasebruta = preg_replace("/[[:space:]]$/u", "", $frasebruta);
        $frasebruta = preg_replace("/,$/u", "", $frasebruta);
        $frasebruta = substr($frasebruta, 1);
        
        $frasebruta[0] = strtoupper($frasebruta[0]);
        
        $llargfrase = strlen($frasebruta);
        $lastcharacter = $frasebruta[$llargfrase-1];
        
        if ($lastcharacter != "?") {
            if ($tipusfrase == "exclamacio") {
                $frasebruta .= "!";
                $frasebruta = "¡".$frasebruta;
            }
            else if ($tipusfrase == "pregunta" || $tipusfrase == "permis") {
                $frasebruta .= "?";
                $frasebruta = "¿".$frasebruta;
            }
            else $frasebruta .= ".";
        }
        
        $this->frasefinal = $frasebruta;
        
        // DEBUG
        // echo $frasebruta.'<br /><br />';
    }
    
    public function printOrdreFrase()
    {
        $string = "";
        
        for ($i=0; $i<count($this->ordrefrase); $i++) {
            $string .= $this->ordrefrase[$i]." ";
        }
        
        return $string;
    }
    
    public function printFraseFinalSlotString()
    {
        $string = "";
        
        // provisional mentre no haguem omplert frasefinal
        // agafem la frase dels slots
        
        // DEBUG
        // print_r($this->ordrefrase);echo '<br /><br />';
        
        for ($i=0; $i<count($this->ordrefrase); $i++) {
            $slotaux = $this->slotarray[$this->ordrefrase[$i]];
            
            for ($j=0; $j<count($slotaux->slotstring); $j++) {
                $aux = $slotaux->slotstring[$j];
                $string .= $aux[0]." ";
            }
        }
        
        return $string;
    }
    
    public function printFraseFinal()
    {
        return $this->frasefinal;
    }
    
}

/* End of file Mypattern.php */