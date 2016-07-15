<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Myword {
    
    /*
     * THE FUNCTIONS USED IN THE ARTICLES' MODULE CAN BE FOUND AT THE END OF THE FILE (BEGIN AT LINE 176)
     */
    
    var $id; // Id de la paraula dins la seva taula
    var $identry; // Id de l'entrada (FRASE): serveix per eliminar paraules dels elements seleccionats
    var $tipus; // Tipus de la paraula que coincideix amb la PoS i la taula de la BBDD
    var $used = false; // si la paraula ja està adjudicada a un slot del pattern o no
    var $classes = array(); // Per si la paraula és de diferents classes (dins del mateix tipus)
                            // Ex: Per noms (lloc, event)...
    var $text; // Paraula en text
    var $img; // Enllaç al pictograma
    var $imgtemp; // Enllaç per si han canviat la imatge del pictograma a la interfície
    var $supportsExpansion; // Si la paraula pot ser utilitzada dins el sistema d'expansió
    var $defaultverb = 0; // Pels noms i pels adjectius, el verb per defecte si no hi ha més paraules a la frase
    var $subjdef = false; // Pels adjectius, el subjecte per defecte si no hi ha més paraules a la frase
    
    var $propietats = array(); // Varia segons el tipus de paraula. És la fila de la BBDD
    var $patterns = array(); // Només pels verbs
    
    var $inputorder; // Número a l'entrada, de 0 a n-1
    // només s'utilitzaran si l'idioma té estructura SVO (Subject-Verb-Object)
    var $beforeverb = false; // Si apareix abans o després del 1er verb
    var $beforeverb2 = false; // Si apareix entre dos verbs: només quan hi ha dos verbs, quan n'hi ha un sempre està a true
    
    var $slotstemps = array(); // Array amb els slots temporals on és la paraula. Hi ha les KEYS dels slots
    var $slotstempsext = array(); // Array per adjs i modificadors, igual que l'anterior, però té les keys
                                  // a l'element [0] i els punts de l'slot on pot fer de complement a [1]
    var $slotfinal = null; // nom de l'slot on es queda finalment la paraula
    
    var $assignadaAComplement = false;
    
    
    // Modificadors
    var $plural;
    var $fem;
    var $coord;
    
    var $paraulacoord = array(); // si n'hi ha, paraules coordinades amb aquesta
    
    function __construct() {}
    
    public function initialise($paraula, $infobbdd, $order, $beforeverb, $beforeverb2, $prov) 
    {
        $this->inputorder = $order;
        $this->beforeverb = $beforeverb;
        $this->beforeverb2 = $beforeverb2;
        
        $this->id = $paraula->pictoid;
        if ($prov) $this->identry = $paraula->ID_RSTPSentencePicto;
        else $this->identry = $paraula->ID_RSHPSentence;
        $this->tipus = $paraula->pictoType;
        $this->img = $paraula->imgPicto;
        $this->imgtemp = $paraula->imgtemp;
        $this->supportsExpansion = $paraula->supportsExpansion;
        
        if ($paraula->isplural == '1') $this->plural = true;
        else $this->plural = false;
        
        if ($paraula->isfem == '1') $this->fem = true;
        else $this->fem = false;
        
        if ($paraula->coordinated == '1') $this->coord = true;
        else $this->coord = false;
        
        switch ($this->tipus)
        {
            case 'name':
                $this->text = $infobbdd[0]->nomtext;
                $this->defaultverb = $infobbdd[0]->defaultverb;
                $this->propietats = $infobbdd[0];
                foreach ($infobbdd as $row) {
                    array_push($this->classes, $row->class);
                }
                break;
            
            case 'verb':
                $this->text = $infobbdd[0]->verbtext;
                foreach ($infobbdd as $row) {
                    array_push($this->patterns, $row);
                }
                break;
            
            case 'adj':
                $this->text = $infobbdd[0]->masc;
                $this->defaultverb = $infobbdd[0]->defaultverb;
                $this->subjdef = $infobbdd[0]->subjdef;
                $this->propietats = $infobbdd[0];
                foreach ($infobbdd as $row) {
                    array_push($this->classes, $row->class);
                }
                break;
            
            case 'adv':
                $this->text = $infobbdd[0]->advtext;
                $this->propietats = $infobbdd[0];
                foreach ($infobbdd as $row) {
                    array_push($this->classes, $row->type);
                }
                break;
            
            case 'expression':
                $this->text = $infobbdd[0]->exprtext;
                $this->propietats = $infobbdd[0];
                foreach ($infobbdd as $row) {
                    array_push($this->classes, $row->type);
                }
                break;
            
            case 'modifier':
                $this->text = $infobbdd[0]->masc;
                $this->propietats = $infobbdd[0];
                array_push($this->classes, $infobbdd[0]->type);
                break;
            
            case 'questpart':
                $this->text = $infobbdd[0]->parttext;
                $this->propietats = $infobbdd[0];
                array_push($this->classes, $infobbdd[0]->complement1);
                array_push($this->classes, $infobbdd[0]->complement2);
                break;
            
            default:
                break;
        }
                
    }
    
    public function initSingleVerbWord($verbid, $infobbdd)
    {
        $this->id = $verbid;
        $this->tipus = "verb";
        
        $this->plural = false;
        $this->fem = false;
        $this->coord = false;
        
        $this->text = $infobbdd[0]->verbtext;
        $this->img = $infobbdd[0]->imgPicto;
        foreach ($infobbdd as $row) {
            array_push($this->patterns, $row);
        }
    }

    public function isType($tipus)
    {
        return ($this->tipus == $tipus);
    }
    
    public function isClass($classe)
    {
        for ($i=0; $i<count($this->classes); $i++) {
            if ($this->classes[$i] == $classe) return true;
        }
        return false;
    }
    
    public function searchSlotIndex($slotkey)
    {
        $index = -1;
        
        $found = false;
        
        $i=0;
        
        while ($i<count($this->slotstemps) && !$found) {
            
            if ($this->slotstemps[$i] == $slotkey) {
                $index = $i;
                $found = true;
            }
            $i++;
        }
        
        return $index;
    }
    
    
    /*
     * FUNCTIONS FOR THE ARTICLES' MODULE
     */
    
    /*
     * This function returns the correct form of the definite article
     * for the word in this class
     */
    public function giveDefiniteArticle()
    {        
        $CI = &get_instance();
        $CI->load->library('Mymatching');
        
        // create an object of the class Mymatching, which has encoded the Lists
        // and the Answers (operators)
        $matching = new Mymatching();
        
        // the character encoding of the words from the database is utf-8
        // so we set the php system to utf-8
        mb_internal_encoding( 'utf-8' );
        
        // letters that we want to replace
        $patterns[0] = '/[à]/u'; 
        $patterns[1] = '/[è|é]/u';
        $patterns[2] = '/[í|ï]/u';
        $patterns[3] = '/[ò|ó]/u';
        $patterns[4] = '/[ú|ü]/u';
        
        $replacements[0] = 'a';
        $replacements[1] = 'e';
        $replacements[2] = 'i';
        $replacements[3] = 'o';
        $replacements[4] = 'u';
        
        $wordoriginal = $this->text;
        $wordlowered = mb_strtolower($wordoriginal); // word in lowercase
                        
        // remove the accents from lowercase word
        $wordlowerednoaccents = preg_replace($patterns, $replacements, $wordlowered);
                
        // a
        if ($this->tipus == "name") {
            // b
            if ($this->plural || $this->propietats->singpl == "pl") {
                    // d
                    if ($this->propietats->mf == "masc" && !$this->fem) return $matching->answers["R"];
                    else return $matching->answers["S"];
            }
            else {
                // c -> We use regular expressions to verify the condition        
                if (preg_match('/^[bcdfgjklmnpqrstvwxyz]/', $wordlowered) == '1') {

                    // d
                    if ($this->propietats->mf == "masc" && !$this->fem)  return $matching->answers["O"];
                    else return $matching->answers["P"];
                }
                // e -> We use regular expressions to verify the condition
                else if (preg_match('/^([aeo]|[h][aeo])/', $wordlowerednoaccents) == '1') {
                    
                    //f
                    if (isset($matching->listB[$wordlowered]) || isset($matching->listD[$wordlowered]) || isset($matching->listF[$wordlowered])) {
                        return $matching->answers["P"];
                    }
                    else return $matching->answers["Q"];
                }
                // g -> We use regular expressions to verify the condition
                else if (preg_match('/^([iu]|[h][iu])/', $wordlowerednoaccents) == '1') {
                    // see if the i or u are unstressed   
                    
                    $aux = $this->StressedOrNonVocalic($wordlowered);
                    $stressed = $aux[0];
                    $nonvocalic = $aux[1];
                    
                    // h
                    if (!$stressed) {
                        // d
                        if ($this->propietats->mf == "masc" && !$this->fem) {
                            // i&m
                            if ($nonvocalic && !isset($matching->listH[$wordlowered])) return $matching->answers["O"];
                            else return $matching->answers["Q"];
                        }
                        else return $matching->answers["P"];
                    }
                    else {
                        
                        // j
                        if (isset($matching->listA[$wordlowered]) || isset($matching->listE[$wordlowered])) return $matching->answers["P"];
                        
                        // k
                        else if (isset($matching->listG[$wordlowered])) {
                            // d
                            if ($this->propietats->mf == "masc" && !$this->fem) return $matching->answers["O"];
                            else return $matching->answers["P"];
                        }
                        else return $matching->answers["Q"];
                    }
                }
                
                else {
                    // l
                    if (isset($matching->listC[$wordlowered])) return $matching->answers["Q"];
                    else return $matching->answers["O"];
                }
            }
        }
        else return null;
        
    }  // End function giveDefiniteArticle
    
    
    // it returns a pair: [0] if it is stressed [1] if it is non-vocalic
    function StressedOrNonVocalic($wordlowered)
    {
        $output = array();
        
        $accentsCatalan = array("à", "è", "é", "í", "ò", "ó", "ú");
        $diphthongs = array("ai", "ei", "ii", "oi", "ui", "au", "eu", "iu", "ou", "uu", "üe", "üi");
        // at the beginning of a word this combinations are added to the previous ones as possible diphthongs
        $diphthongsBeginning = array("ia", "ie", "io", "iu", "ua", "ue", "ui", "uo");
        $triphthongs = array("uau", "uai");
        $nonvocalic = false;
        $stressed = false;
        
        $numsyllables = 0;

        // if the "i" or "u" have an accent, then they are stressed
        if (in_array($wordlowered, $accentsCatalan)) $stressed = true;
        else {

            $numletters = strlen($wordlowered);

            // we loop the word letter by letter from the end to the beginning
            for ($i=$numletters-1;$i>=0; $i--) {
                $currentLetter = $wordlowered[$i];

                // if the current letter is a vowel
                if (preg_match('/^[aàeèéiíïoòóuü]/u', $currentLetter) == '1') {
                    // if we have reached the beginning of the word
                    if ($i==0) $numsyllables++;
                    else {
                        $nextletter = $wordlowered[$i-1];

                        // if the next letter is not a vowel
                        if (preg_match('/^[aàeèéiíïoòóuü]/u', $nextletter) == '0') {
                            $numsyllables++;
                            $i--; // we skip the next letter as it is already treated
                        }
                        else {
                            $aux2letters = $nextletter.$currentLetter;

                            // we are at the beginning of the word
                            if ($i-1 == 0) {
                                // if it is a diphthong
                                if (in_array($aux2letters, $diphthongs) || in_array($aux2letters, $diphthongsBeginning)) {
                                    $numsyllables++;
                                    $i--;
                                    $nonvocalic = true;
                                }
                                // if it is not a diphthong
                                else {
                                    $numsyllables += 2;
                                    $i--;
                                }
                            }
                            // if we are not at the beginning
                            else {
                                $afternextletter = $wordlowered[$i-2];

                                // if it is not a vowel
                                if (preg_match('/^[aàeèéiíïoòóuü]/u', $afternextletter) == '0') {

                                    // if we reached the beginning and the first letter is an "h"
                                    if ($i-2==0 && $afternextletter == "h") {
                                        // if it is a diphthong
                                        if (in_array($aux2letters, $diphthongs) || in_array($aux2letters, $diphthongsBeginning)) {
                                            $numsyllables++;
                                            $i -= 2;
                                            $nonvocalic = true;
                                        }
                                        else {
                                            $numsyllables += 2;
                                            $i -= 2;
                                        }
                                    }
                                    // see if it is a diphthong
                                    else if (in_array($aux2letters, $diphthongs)) {
                                        $numsyllables++;
                                        $i -= 2; // we skip the next two letters
                                    }
                                    // if it is not a diphthong
                                    else {
                                        $numsyllables += 2;
                                        $i -=2;
                                    }
                                }
                                // if it is a vowel
                                else {
                                    $aux3letters = $afternextletter.$aux2letters;

                                    // see if it is a triphthong
                                    if (in_array($aux3letters, $triphthongs)) {
                                        $numsyllables++;
                                        $i -= 3; // we skip an extra letter as for sure it will be a vowel
                                                    // there is not any word in catalan with 4 vowels together
                                    }
                                    // if it was not
                                    else {
                                        $numsyllables += 2;
                                        $i -= 3;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // if we already found that it is stressed
        if ($stressed) {
            $output[0] = $stressed;
            $output[1] = $nonvocalic;
        }
        
        // RULES
        else {
            // if the word has 1 syllable
            if ($numsyllables == 1) {
                // it is only unstressed when it is also nonvocalic
                if ($nonvocalic) $stressed = false;
                else $stressed = true;
            }
            else if ($numsyllables == 2) {
                // we already checked that it is not accentuated
                // then if the word ends in vowel, "s", "en" or "in" it is unstressed
                if (preg_match('/([aàeèéiíïoòóuü]|[s]|[ei][n])$/u', $currentLetter) == '1') {
                    $stressed = false;
                }
                else $stressed = true;
            }
            // if it has three or more syllables it is unstressed 
            // because we already checked that it is not accentuated
            else {
                $stressed = false;
            }
            
            $output[0] = $stressed;
            $output[1] = $nonvocalic;
        }
        
        return $output;
    }
    
    /*
     * This function returns the correct form of the definite article
     * for the word in this class, taking into account the context
     */
    public function giveDefiniteArticleContext($masc, $plural)
    {        
        $CI = &get_instance();
        $CI->load->library('Mymatching');
        
        // create an object of the class Mymatching, which has encoded the Lists
        // and the Answers (operators)
        $matching = new Mymatching();
        
        // the character encoding of the words from the database is utf-8
        // so we set the php system to utf-8
        setlocale(LC_ALL, 'es_CA');
        mb_internal_encoding( 'utf-8' );
        
        // letters that we want to replace
        $patterns[0] = '/[à|À]/u'; 
        $patterns[1] = '/[è|é|É|È]/u';
        $patterns[2] = '/[í|ï|Í|Ï]/u';
        $patterns[3] = '/[ò|ó|Ò|Ó]/u';
        $patterns[4] = '/[ú|ü|Ú|Ü]/u';
        
        $replacements[0] = 'a';
        $replacements[1] = 'e';
        $replacements[2] = 'i';
        $replacements[3] = 'o';
        $replacements[4] = 'u';
        
        $wordoriginal = $this->text;
        $wordlowered = strtolower($wordoriginal); // word in lowercase
                
        // remove the accents from lowercase word
        $wordlowerednoaccents = preg_replace($patterns, $replacements, $wordlowered);
                
        // a
        if ($this->tipus == "name") {
            // b
            if ($plural) {
                    // d
                    if ($masc) return $matching->answers["R"];
                    else return $matching->answers["S"];
            }
            else {
                // m -> New rule: if it's a proper noun, the article is "en"
                if ($this->propietats->ispropernoun == '1' && $masc) {
                    return $matching->answers["T"];
                }
                else { 
                    // c -> We use regular expressions to verify the condition        
                    if (preg_match('/^[bcdfgjklmnpqrstvwxyz]/', $wordlowerednoaccents) == '1') {

                        // d
                        if ($masc)  return $matching->answers["O"];
                        else return $matching->answers["P"];
                    }
                    // e -> We use regular expressions to verify the condition
                    else if (preg_match('/^([aeo]|[h][aeo])/', $wordlowerednoaccents) == '1') {
                        //f
                        if (isset($matching->listB[$wordlowered]) || isset($matching->listD[$wordlowered]) || isset($matching->listF[$wordlowered])) {
                            return $matching->answers["P"];
                        }
                        else return $matching->answers["Q"];
                    }
                    // g -> We use regular expressions to verify the condition
                    else if (preg_match('/^([iu]|[h][iu])/', $wordlowerednoaccents) == '1') {
                        // see if the i or u are unstressed   

                        $aux = $this->StressedOrNonVocalic($wordlowered);
                        $stressed = $aux[0];
                        $nonvocalic = $aux[1];

                        // h
                        if (!$stressed) {
                            // d
                            if ($masc) {
                                // i&m
                                if ($nonvocalic && !isset($matching->listH[$wordlowered])) return $matching->answers["O"];
                                else return $matching->answers["Q"];
                            }
                            else return $matching->answers["P"];
                        }
                        else {

                            // j
                            if (isset($matching->listA[$wordlowered]) || isset($matching->listE[$wordlowered])) return $matching->answers["P"];

                            // k
                            else if (isset($matching->listG[$wordlowered])) {
                                // d
                                if ($masc) return $matching->answers["O"];
                                else return $matching->answers["P"];
                            }
                            else return $matching->answers["Q"];
                        }
                    }

                    else {
                        // l
                        if (isset($matching->listC[$wordlowered])) return $matching->answers["Q"];
                        else return $matching->answers["O"];
                    }
                }
            }
        }
        else return null;
        
    }  // End function giveDefiniteArticleContext
    
    public function giveIndefiniteArticleContext ($masc, $plural)
    {
        if ($masc && !$plural) return "un ";
        else if ($masc && $plural) return "uns ";
        else if (!$masc && !$plural) return "una ";
        else return "unes ";
    }
    
}

/* End of file Myword.php */