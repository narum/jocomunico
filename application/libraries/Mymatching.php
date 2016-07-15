<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Mymatching {
    
    /*
     * DATA STRUCTURE THAT REPRESENTS THE DEFINITE ARTICLES' ALGORITHM
     */
    
    var $conditions = array(
        "a" => "the word is a noun",
        "NOTa" => "the word is not a noun",
        "b" => "the word is in plural",
        "NOTb" => "the word is not in plural",
        "c" => "the word begins in consonant except for h",
        "NOTc" => "the word begins in vowel or h",
        "d" => "the word is masculine",
        "NOTd" => "the word is feminine",
        "e" => "the word begins in 'a', 'e', 'o' or in 'ha', 'he', 'ho'",
        "NOTe" => "the word does not begin neither in 'a', 'e', 'o' nor in 'ha', 'he', 'ho'",
        "f" => "the word belongs to the lists B, D or F",
        "NOTf" => "the word does not belong to the lists B, D or F",
        "g" => "the word begins in 'i', 'u' or in 'hi', 'hu'",
        "NOTg" => "the word does not begin neither in 'i', 'u' nor in 'hi', 'hu'",
        "h" => "the 'i' or 'u' is unstressed",
        "NOTh" => "the 'i' or 'u' is stressed",
        "i" => "the 'i' or 'u' is non-vocalic",
        "NOTi" => "the 'i' or 'u' is vocalic",
        "j" => "the word belongs to the lists A or E",
        "NOTj" => "the word does not belong to the lists A or E",
        "k" => "the word belongs to the list G",
        "NOTk" => "the word does not belong to the list G",
        "l" => "the word belongs to the list C",
        "NOTl" => "the word does not belong to the list C",
        "m" => "the word is not in the list H",
        "M" => "the word is in the list H",
        "i&m" => "the 'i' or 'u' is non-vocalic and the word is not in the list H",
        "NOTi&m" => "the 'i' or 'u' is vocalic or the word is in the list H",
    );
    
    var $answers = array(
        "O" => "el ",
        "P" => "la ",
        "Q" => "l'",
        "R" => "els ",
        "S" => "les ",
        "T" => "en ",
    );
    
    
    // LISTS
    
    var $listA = array(
        "una" => true,
        "ira" => true,
    );
    
    var $listB = array(
        "host" => true,
    );
    
    var $listC = array(
        "1" => true,
        "11" => true,
    );
    
    var $listD = array(
        "a" => true,
        "e" => true,
        "o" => true,
        "efa" => true,
        "ela" => true,
        "ema" => true,
        "ena" => true,
        "erra" => true,
        "essa" => true,
    );
    
    var $listE = array(
        "i" => true,
        "u" => true,
    );
    
    var $listF = array(
        "hac" => true,
    );
    
    var $listG = array(
        "hippy, Harry, Harriet" => true,
    );
    
    var $listH = array(
        "ió" => true,
    );
    
    var $resolutionTree = array();
    
    
    /*
     * FUNCTIONS FOR THE ARTICLES' MODULE
     */
    
    public function initialiseResolutionTree()
    {
        
        
        $RT = &$this->resolutionTree;
        $RT['1 '] = "b";
        $RT['0 '] = "exit";
        $RT['1 1 '] = "d";
        $RT['1 0 '] = "c";
        $RT['1 1 1 '] = "R";
        $RT['1 1 0 '] = "S";
        $RT['1 0 1 '] = "d";
        $RT['1 0 0 '] = "e";
        $RT['1 0 1 1 '] = "O";
        $RT['1 0 1 0 '] = "P";
        $RT['1 0 0 1 '] = "f";
        $RT['1 0 0 0 '] = "g";
        $RT['1 0 0 1 1 '] = "P";
        $RT['1 0 0 1 0 '] = "Q";
        $RT['1 0 0 0 1 '] = "h";
        $RT['1 0 0 0 0 '] = "l";
        $RT['1 0 0 0 1 1 '] = "d";
        $RT['1 0 0 0 1 0 '] = "j";
        $RT['1 0 0 0 1 1 1 '] = "i&m";
        $RT['1 0 0 0 1 1 0 '] = "P";
        $RT['1 0 0 0 1 1 1 1 '] = "O";
        $RT['1 0 0 0 1 1 1 0 '] = "Q";
        $RT['1 0 0 0 1 0 1 '] = "P";
        $RT['1 0 0 0 1 0 0 '] = "k";
        $RT['1 0 0 0 1 0 0 1 '] = "d";
        $RT['1 0 0 0 1 0 0 0 '] = "Q";
        $RT['1 0 0 0 1 0 0 1 1 '] = "O";
        $RT['1 0 0 0 1 0 0 1 0 '] = "P";
        $RT['1 0 0 0 0 1 '] = "Q";
        $RT['1 0 0 0 0 0 '] = "O";
    }
    
    /*
     * Returns an array of all the conditions followed by the variable path. Each conditions consists of the 
     * following elements: [0] text of the condition in positive (or the resulting article if a leave node has been reached) 
     * [1] text of the condition in negative [2] key (name) of the condition [3] path followed to reach this condition
     * [4] value of the condition for this path: 1 is the positive value and 0 the negative value
     * [5] if it is a leaf node
     */
    public function getConditions ($path)
    {
        $conditions = array();
        $auxmenu = array();
                
        $auxmenu[0] = $this->conditions["a"];
        $auxmenu[1] = $this->conditions["NOTa"];
        $auxmenu[2] = "a"; // key
        $auxmenu[3] = ""; // path followed
                
        // path has the form: "1 0 1 " (number followed by a space)
        $auxstep = explode(" ", $path);
        $numsteps = count($auxstep)-1; // explode always returns 1 more element at the end
        
        if ($numsteps == 0) $auxmenu[4] = null; // default value
        else $auxmenu[4] = $auxstep[0];
        
        $auxmenu[5] = false; // leaf node
        
        // the first menu to appear is always the list with a or ¬a, which is the first choice
        $conditions[] = $auxmenu;
        $indexpath = "";
        
        $defvalue = null;
        
        for ($i=0; $i<$numsteps; $i++) {
            
            $indexpath .= $auxstep[$i]." ";
            if ($i+1<$numsteps) $defvalue = $auxstep[$i+1];
            else $defvalue = null;

            $conditionkey = $this->resolutionTree[$indexpath];
            
            if ($conditionkey == "O" || $conditionkey == "P" || $conditionkey == "Q" || $conditionkey == "R" || $conditionkey == "S") {
                $auxmenu[0] = $this->answers[$conditionkey];
                $auxmenu[1] = null;
                $auxmenu[5] = true;
            }
            else if ($conditionkey == "exit") {
                $auxmenu[0] = "No article can be attached to this word.";
                $auxmenu[1] = null;
                $auxmenu[5] = true;
            }
            else {
                $auxmenu[0] = $this->conditions[$conditionkey];
                $auxmenu[1] = $this->conditions["NOT".$conditionkey];
                $auxmenu[5] = false;
            }
            $auxmenu[2] = $conditionkey;
            $auxmenu[3] = $indexpath;
            $auxmenu[4] = $defvalue;
            
            $conditions[] = $auxmenu;
        }
        return $conditions;
    }

    
    /*
     * DATA PEL PARSER 
     */
    
    var $nounsFitKeys = array(
        "noun" => 0,
        "animate" => 1,
        "human" => 2,
        "pronoun" => 3,
        "animal" => 4,
        "planta" => 5,
        "vehicle" => 6,
        "event" => 7,
        "inanimate" => 8,
        "objecte" => 9,
        "color" => 10,
        "forma" => 11,
        "joc" => 12,
        "cos" => 13,
        "abstracte" => 14, // el tractarem com un animat i com un inanimat
        "lloc" => 15, // si vull un lloc un event també val
        "menjar" => 16, // menjar un humà, animal o planta també està bé ho posarem a 1
        "beguda" => 17,
        "time" => 18,
        "hora" => 19,
        "month" => 20,
        "week" => 21,
        "tool" => 22,
        "profession" => 23,
        "material" => 24,
    );
        
    // sobre un slot d'un tipus 1 de nom, com de bé hi fa fit un nom de tipus 2
    var $nounsFit = array(
        //           0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4
        0 => array  (0,0,0,0,0,0,0,1,0,0,1,1,0,0,0,0,0,0,1,1,1,1,0,0,0), // perquè un match noun a qualsevol no és tan bon match
        1 => array  (5,0,0,0,0,0,1,5,5,5,5,5,5,5,1,5,2,5,5,5,5,5,5,1,5),
        2 => array  (5,1,0,0,1,2,5,2,5,5,5,5,5,5,2,5,5,5,5,5,5,5,5,1,5),
        3 => array  (5,2,1,0,2,2,2,2,5,5,5,5,5,5,2,5,5,5,5,5,5,5,5,5,5),
        4 => array  (5,1,1,1,0,1,1,1,5,5,5,5,5,5,1,5,5,5,5,5,5,5,5,5,5),
        5 => array  (5,1,1,1,1,0,1,1,5,5,5,5,5,5,1,5,5,5,5,5,5,5,5,5,5),
        6 => array  (5,1,1,1,1,1,0,1,5,5,5,5,5,5,1,5,5,5,5,5,5,5,5,5,5),
        7 => array  (5,1,1,1,1,1,1,0,5,5,5,5,5,5,1,5,5,5,5,5,5,5,5,5,5),
        8 => array  (5,5,5,5,5,1,2,5,0,0,0,0,0,0,0,1,0,0,1,1,1,1,0,5,1),
        9 => array  (5,5,5,5,5,5,0,5,1,0,0,0,0,0,1,1,1,1,1,1,1,1,1,5,1),
        10 => array (5,5,5,5,5,5,5,5,2,1,0,1,1,1,2,2,2,2,2,2,2,2,2,5,5),
        11 => array (5,5,5,5,5,5,5,5,2,1,1,0,1,1,2,2,2,2,2,2,2,2,2,5,5),
        12 => array (5,5,5,5,5,5,5,5,2,1,1,1,0,1,2,2,2,2,2,2,2,2,2,5,5),
        13 => array (5,5,5,5,5,5,5,5,3,3,3,3,3,0,3,3,3,3,5,5,5,5,5,5,3),
        14 => array (5,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,1,1,1,1,1,5,5),
        15 => array (5,5,3,5,5,5,0,0,2,2,2,1,1,1,5,0,1,1,5,5,5,5,1,0,5),
        16 => array (5,5,1,5,1,1,5,5,2,1,2,2,1,1,2,2,0,1,2,2,2,2,2,5,5),
        17 => array (5,5,5,5,5,5,5,5,2,2,2,2,2,2,2,2,1,0,2,2,2,2,2,5,5),
        18 => array (1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,0,0,0,0,1000,1000,1000),
        19 => array (5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,1,0,1,1,5,5,5),
        20 => array (5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,1,1,0,1,5,5,5),
        21 => array (5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,1,1,1,0,5,5,5),
        22 => array (5,5,5,5,5,5,0,5,1,1,5,2,5,3,5,2,2,5,5,5,5,5,0,5,2),
        23 => array (5,1,1,1,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,0,5),
        24 => array (5,5,5,5,5,5,5,5,2,2,5,5,5,5,5,5,1,5,5,5,5,5,2,5,0),
        //           0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4
    );
    
    var $advQuantFitKeys = array(
        "adv" => 0,
        "manera" => 1,
        "lloc" => 2,
        "temps" => 3,
        "quant" => 4
    );
    
    // la fila de lloc, és com de bé complementa a un nom dels tipus de dalt
    var $advQuantFit = array(
        0 => array(0,0,0,0,1),
        1 => array(5,0,5,5,1),
        2 => array(5,5,0,5,5),
        3 => array(5,5,5,0,5),
        4 => array(5,1,5,5,0),
    );
    
    var $advLocNC = array(
        //         0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4
        0 => array(2,2,2,2,2,2,0,5,2,2,5,0,2,2,4,0,2,2,5,5,5,5,2,2,2),
    );
            
    var $adjNounFitKeys = array(
        "all" => 0,
        "color" => 1,
        "human" => 2,
        "animate" => 3,
        "objecte" => 4,
        "menjar" => 5,
        "ordinal" => 6,
    );
            
    var $adjNounFit = array(
        //         0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4
        0 => array(0,0,0,5,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
        1 => array(0,0,0,5,0,0,0,5,0,0,0,0,0,0,5,0,0,0,5,5,5,5,0,5,1),
        2 => array(5,1,0,5,1,1,1,1,5,5,5,5,5,5,1,5,5,5,5,5,5,5,5,1,5),
        3 => array(5,0,0,5,0,0,0,0,5,5,5,5,5,5,0,5,5,5,5,5,5,5,5,1,5),
        4 => array(5,5,5,5,5,5,5,5,1,0,0,0,0,0,1,1,1,1,1,1,1,1,0,5,1),
        5 => array(5,5,1,5,1,1,5,5,2,1,2,2,1,1,2,2,0,1,2,2,2,2,1,2,5),
        6 => array(0,0,0,5,0,0,0,0,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,5),
    );
    
    // Tots els tipus d'adj
    var $adjFitKeys = array(
        "adj" => 0,
        "all" => 1,
        "color" => 2,
        "human" => 3,
        "animate" => 4,
        "objecte" => 5,
        "menjar" => 6,
        "ordinal" => 7,
        "manera" => 8,
    );
    
    var $adjFit = array(
        0 => array(0,0,0,0,0,0,0,5,0),
        1 => array(0,0,0,0,0,0,0,5,0),
        2 => array(0,0,0,0,0,0,0,5,0),
        3 => array(0,0,0,0,0,0,0,5,0),
        4 => array(0,0,0,0,0,0,0,5,0),
        5 => array(0,0,0,0,0,0,0,5,0),
        6 => array(0,0,0,0,0,0,0,5,0),
        7 => array(5,5,5,5,5,5,5,0,5),
        8 => array(2,2,2,2,2,2,2,5,0),
    );
    
    // Els tipus d'slot que accepten un adj
    var $adjFitKeysSmall = array(
        "adj" => 0,
        "ordinal" => 7,
        "manera" => 8,
    );
    
    // Els tipus d'slot que accepten un modificador
    var $modifFitKeys = array(
        "modif" => 0,
        "numero" => 1,
        "quant" => 2,
        "manera" => 3,
        "det" => 4,
        "similar" => 5,
    );
    
    // 1000 vol dir que no pot fer match
    var $modifFit = array(
        0 => array(0,0,0,1000,0,0),
        1 => array(1000,0,1000,1000,1000,1000),
        2 => array(1000,1000,0,1000,1000,1000),
        3 => array(1000,1000,1,0,1000,1000),
    );
    
    /** INFO LLENGUATGE DEPENDENT PEL GENERADOR */
    
    // CATALÀ
    
    // Llistat d'adverbis de temps que van al principi de la frase
    var $frontAdvTemps = array(
        "ahir" => true,
        "avui" => true,
        "demà" => true,
        "després" => true,
        "abans" => true,
        "mai" => true,
        "sempre" => true,
        "encara" => true,
    );
    
    // Modificadors de frase que van darrere els adverbis de temps i no a l'inici de la frase
    var $modAfterSubj = array(
        "no" => true,
        "també" => true,
    );
    
    // relaciona els adverbis de temps amb el temps verbal de la frase on apareixen
    var $advsTempsTense = array(
        "ahir" => "perifrastic",
        "ara" => "present",
        "abans" => "perfet",
        "demà" => "futur",
        "després" => "futur",
    );
    
    var $pronomsPersonalsFrontReceiver = array(
        "jo" => "em",
        "mi" => "em",
        "tu" => "et",
        "ell" => "li",
        "ella" => "li",
        "nosaltres" => "ens",
        "vosaltres" => "us",
        "ells" => "els",
        "elles" => "els",
    );
    
    var $pronomsPersonalsAfterReceiver = array(
        "jo" => "me",
        "mi" => "me",
        "tu" => "te",
        "ell" => "li",
        "ella" => "li",
        "nosaltres" => "nos",
        "vosaltres" => "vos",
        "ells" => "los",
        "elles" => "los",
    );
    
    var $pronomsPersonalsFrontTheme = array(
        "jo" => "em",
        "mi" => "em",
        "tu" => "et",
        "ell" => "@PRFEBLEel", // per diferenciar-lo de l'article "el"
        "ella" => "@PRFEBLEla", // per diferenciar-lo de l'article "la"
        "nosaltres" => "ens",
        "vosaltres" => "us",
        "ells" => "els",
        "elles" => "les",
    );
    
    var $pronomsPersonalsAfterTheme = array(
        "jo" => "me",
        "mi" => "me",
        "tu" => "te",
        "ell" => "lo",
        "ella" => "@PRFEBLEla",
        "nosaltres" => "nos",
        "vosaltres" => "vos",
        "ells" => "los",
        "elles" => "@PRFEBLEles",
    );
    
    var $tempsPrep = array(
        "tarda" => "a",
        "matí" => "a",
        "nit" => "a",
        "estiu" => "a",
        "tardor" => "a",
        "primavera" => "a",
        "hivern" => "a",
        "gener" => "a",
        "febrer" => "a",
        "març" => "a",
        "abril" => "a",
        "maig" => "a",
        "juny" => "a",
        "juliol" => "a",
        "agsot" => "a",
        "setembre" => "a",
        "octubre" => "a",
        "novembre" => "a",
        "desembre" => "a",
        "una" => "a",
        "dues" => "a",
        "tres" => "a",
        "quatre" => "a",
        "cinc" => "a",
        "sis" => "a",
        "set" => "a",
        "vuit" => "a",
        "nou" => "a",
        "deu" => "a",
        "onze" => "a",
        "dotze" => "a",
    );
    
    // CASTELLÀ
    
    // Llistat d'adverbis de temps que van al principi de la frase
    var $frontAdvTempsES = array(
        "ayer" => true,
        "hoy" => true,
        "mañana" => true,
        "después" => true,
        "antes" => true,
        "nunca" => true,
        "siempre" => true,
        "todavía" => true,
    );
    
    // Modificadors de frase que van darrere els adverbis de temps i no a l'inici de la frase
    var $modAfterSubjES = array(
        "no" => true,
        "también" => true,
    );
    
    // relaciona els adverbis de temps amb el temps verbal de la frase on apareixen
    var $advsTempsTenseES = array(
        "ayer" => "passat",
        "ahora" => "present",
        "antes" => "perfet",
        "mañana" => "futur",
        "después" => "futur",
    );
    
    var $pronomsPersonalsReceiverES = array(
        "yo" => "me",
        "mí" => "me",
        "tú" => "te",
        "él" => "le",
        "ella" => "le",
        "nosotros" => "nos",
        "vosotros" => "os",
        "ellos" => "les",
        "ellas" => "les",
    );
    
    var $pronomsPersonalsThemeES = array(
        "yo" => "me",
        "mí" => "me",
        "tú" => "te",
        "él" => "lo",
        "ella" => "@PRFEBLEla",
        "nosotros" => "nos",
        "vosotros" => "os",
        "ellos" => "@PRFEBLElos",
        "ellas" => "@PRFEBLElas",
    );
    
    var $tempsPrepES = array(
        "tarde" => "por",
        "mañana" => "por",
        "noche" => "por",
        "verano" => "en",
        "otoño" => "en",
        "primavera" => "en",
        "invierno" => "en",
        "enero" => "en",
        "febrero" => "en",
        "marzo" => "en",
        "abril" => "en",
        "mayo" => "en",
        "junio" => "en",
        "julio" => "en",
        "agsoto" => "en",
        "septiembre" => "en",
        "octubre" => "en",
        "noviembre" => "en",
        "diciembre" => "en",
        "una" => "a",
        "dos" => "a",
        "tres" => "a",
        "cuatro" => "a",
        "cinco" => "a",
        "seis" => "a",
        "siete" => "a",
        "ocho" => "a",
        "nueve" => "a",
        "diez" => "a",
        "once" => "a",
        "doce" => "a",
    );
    
    
    /*
     * FUNCIONS PEL PARSER
     */
        
    function __construct() {}
    
    public function isSetKeyNoun($key)
    {        
        return array_key_exists($key, $this->nounsFitKeys);
    }
    
    public function isSetKeyAdv($key)
    {        
        return array_key_exists($key, $this->advQuantFitKeys);
    }
    
    public function isSetKeyAdjSmall($key)
    {        
        return array_key_exists($key, $this->adjFitKeysSmall);
    }
    
    public function isSetKeyAdj($key)
    {        
        return array_key_exists($key, $this->adjFitKeys);
    }
    
    public function isSetKeyAdjNoun($key)
    {        
        return array_key_exists($key, $this->adjNounFitKeys);
    }
    
    public function isSetKeyModif($key)
    {        
        return array_key_exists($key, $this->modifFitKeys);
    }
    
    public function isFrontAdvTemps($key)
    {        
        return array_key_exists($key, $this->frontAdvTemps);
    }
        
    public function isModAfterSubj($key)
    {        
        return array_key_exists($key, $this->modAfterSubj);
    }
    
    public function isPronomPers($key)
    {        
        return array_key_exists($key, $this->pronomsPersonalsFrontReceiver);
    }
    
    public function isTimePrep($key)
    {        
        return array_key_exists($key, $this->tempsPrep);
    }
    
    /**
     * FUNCIONS PEL CASTELLÀ
     */
    
    public function isFrontAdvTempsES($key)
    {        
        return array_key_exists($key, $this->frontAdvTempsES);
    }
    
    public function isModAfterSubjES($key)
    {        
        return array_key_exists($key, $this->modAfterSubjES);
    }
    
    public function isPronomPersES($key)
    {        
        return array_key_exists($key, $this->pronomsPersonalsReceiverES);
    }
    
    public function isTimePrepES($key)
    {        
        return array_key_exists($key, $this->tempsPrepES);
    }
    
}

/* End of file Mypattern.php */
