<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
// use Stichoza\GoogleTranslate\TranslateClient;

class Myexpander {
    
    
    var $allpatterns = array(); // Array amb tots els patterns possibles per una entrada amb un o varis verbs
    /* Un pattern té un array d'slots (cada slot té les propietats més un array de paraules que l'estan omplint)
     * més un booleà que diu si ja està ple.
     */
    var $puntsallpatterns = array(); // array amb els punts finals de cada pattern
    var $patternescollit = 0; // id del pattern dins de l'array allpatterns
    
    var $errormessagetemp = null; 
    var $errormessage = array(); 
    var $errorcode = array();
    var $errorcodetemp = null;
    var $errortemp = null;
    var $error = array(); // true i false
    
    var $readwithoutexpansion = false; // per llegir les frases tal qual si el sistema d'expansió no les pot fer
    var $preguntaposada = array();
    var $paraulescopia = array();
    var $info = array();
        
    function __construct() {}
    
    public function expand()
    {
        // the character encoding of the words from the database is utf-8
        // so we set the php system to utf-8
        mb_internal_encoding( 'utf-8' );
        
        $CI = &get_instance();
        $CI->load->model('Lexicon');

        $this->info['errormessage'] = null; // Missatges d'error o warnings
        $this->info['error'] = false; // Si el missatge és d'error
        $this->info['printparsepattern'] = "No pattern found.";
        $this->info['errorcode'] = false;
        $frasefinalnotexpanded = "";
        
        // agafem si el sistema d'expansió està o no activat
        $expansionOff = false;
        if ($CI->session->userdata('cfgExpansionOnOff') == '0') $expansionOff = true;
        
        // GET SENTENCE
        $idusu = $CI->session->userdata('idusu');
        $this->paraulescopia = $CI->Lexicon->getLastSentence($idusu); // array amb les paraules
        $propietatsfrase = $CI->Lexicon->getLastSentenceProperties($idusu);
                
        // variables per mostrar els resultats per pantalla
        $this->info['identry'] = $propietatsfrase['identry'];
        $this->info['inputwords'] = $propietatsfrase['inputwords'];
        
        // si ha intentat fer una frase buida, li retornem la frase anterior
        // si no en tenia cap, seguirà l'execució
        if (count($this->paraulescopia) <= 0) {
                                    
            $this->info['frasefinal'] = $CI->Lexicon->getLastGeneratedText($idusu);
                        
            if ($this->info['frasefinal'] != null) {
                $this->info['errormessage'] = null;
                $this->info['error'] = false;
                $this->info['errorcode'] = false;
                $this->info['readwithoutexpansion'] = false;
                
                return;
            }
            else {
                $this->info['errormessage'] = "Error. No hi ha cap frase per aquest usuari.";
                $this->info['error'] = true;
                $this->info['errorcode'] = 1;
            }
        }

        // FIND VERBS
        $arrayVerbs = array();
        // si a la frase no hi ha verb i només hi ha adjectius (amb o sense modificadors) voldrem que 
        // agafi els patrons de ser i estar, si no només posarà els patrons verbless
        $thereisadj = false;
        $adjdefverb = 0;
        $adjdefsubj = false;
        $countnouns = 0;
        $noundefverb = 0;
        $othertypes = false;
                
        // fem una cerca de tots els pictogames i agafem configuracions necessàries pel sistema
        // com ara verbs per defecte
        for ($i=0; $i<count($this->paraulescopia); $i++) {
            $word = &$this->paraulescopia[$i];
            
            // si troba un pictograma que no es pot expandir, com ara el de "Falta pictograma"
            // que surti l'error i un cop acabi el bucle que surti del sistema d'expansió
            if ($word->supportsExpansion == '0') {
                $this->info['errormessage'] = "Error. S’ha trobat un pictograma que no es pot expandir.";
                $this->info['error'] = true;
                $this->info['errorcode'] = 6;
                $this->readwithoutexpansion = true;
            }
            
            if ($word->tipus == "verb") $arrayVerbs[] = &$word;
            else if ($word->tipus == "adj") { 
                if (!$thereisadj) {
                    $adjdefverb = $word->defaultverb;
                    $adjdefsubj = $word->subjdef;
                }
                $thereisadj = true;
            }
            else if ($word->tipus == "name") { 
                if ($countnouns < 1) $noundefverb = $word->defaultverb;
                if (!$word->isClass("pronoun")) {
                    $countnouns++;
                }
            }
            else if ($word->tipus != "modifier") $othertypes = true;
            
            // aprofitem el bucle que passa per totes les paraules per preparar la frase final per
            // si hi ha un error que digui la frase inicial sense expandir-la
            $frasefinalnotexpanded .= $word->text." ";
        }
        
        // si ha trobat un pictograma que no es pot expandir
        // o el sistema d'expansió estava desactivat que surti del sistema d'expansió
        if ($this->readwithoutexpansion || $expansionOff) {
            
            $this->info['frasefinal'] = $frasefinalnotexpanded;
            $CI->Lexicon->guardarParseIFraseResultat($propietatsfrase['identry'], null, $frasefinalnotexpanded);
            
            return;
        }

        // GET PATTERNS

        $this->initialiseVerbPatterns($arrayVerbs, $propietatsfrase, $thereisadj, $adjdefverb, $adjdefsubj, $countnouns, $noundefverb, $othertypes);
        // $verbPatterns = new Mypatterngroup();            
        // $verbPatterns.initialise($arrayVerbs);

        if ($this->errormessagetemp != null) {
            $this->info['errormessage'] = $this->errormessagetemp;
            $this->info['error'] = true;
            $this->info['errorcode'] = $this->errorcodetemp;
            $this->info['readwithoutexpansion'] = $this->readwithoutexpansion;
            
            $this->info['frasefinal'] = $frasefinalnotexpanded;
            $CI->Lexicon->guardarParseIFraseResultat($propietatsfrase['identry'], null, $frasefinalnotexpanded);
            
            return;
        }

        else {

            $partpreguntaposada = false;
            $preguntabona = false;
            // Per cada PATTERN
            for ($i=0; $i<count($this->allpatterns); $i++) {
                
                // fem una còpia de les paraules per treballar des de 0 a cada pattern
                $paraules = unserialize(serialize($this->paraulescopia));
                $partPregunta = array();
                $arrayNouns = array();
                $arrayAdjs = array();
                $arrayAdvs = array();
                $arrayModifs = array();
                $arrayExpressions = array();

                for ($j=0; $j<count($paraules); $j++) {
                    $word2 = &$paraules[$j];
                    if ($word2->tipus == "name") $arrayNouns[] = &$word2;
                    else if ($word2->tipus == "adj") {
                        if ($word2->isClass("numero")) $arrayModifs[] = &$word2;
                        else $arrayAdjs[] = &$word2; // els ordinals hi segueixen sent, vigilar amb els slots tipus ordinal
                    }
                    else if ($word2->tipus == "adv") $arrayAdvs[] = &$word2;
                    else if ($word2->tipus == "expression") $arrayExpressions[] = &$word2;
                    else if ($word2->tipus == "modifier") $arrayModifs[] = &$word2;
                    else if ($word2->tipus == "questpart") $partPregunta[] = &$word2;
                }

               $auxpattern = &$this->allpatterns[$i];
               $auxpattern->paraules = $paraules;

               // Posem les expressions a la tira d'expressions
               for ($j=0; $j<count($arrayExpressions); $j++) {
                   $aux = array();
                   $aux[0] = $arrayExpressions[$j]->text;
                   $aux[1] = $arrayExpressions[$j]->propietats->front;
                   $auxpattern->exprsarray[] = $aux;
               }

               // si la frase era de pregunta, ho guardem a les cookies
               if ($propietatsfrase['tipusfrase'] == "pregunta") $CI->session->set_userdata('preguntapattern', true);
               
               // Si hi ha una partícula de pregunta
               $numpreguntes = count($partPregunta);
               if ($numpreguntes > 1) {
                   
                   $this->errormessagetemp = "Error. La frase no pot contenir més d'una pregunta.";
                   $this->errorcodetemp = 2;
                   $this->readwithoutexpansion = true;
                   
                   $this->info['errormessage'] = $this->errormessagetemp;
                   $this->info['error'] = true;
                   $this->info['errorcode'] = $this->errorcodetemp;
                   $this->info['readwithoutexpansion'] = $this->readwithoutexpansion;

                   $this->info['frasefinal'] = $frasefinalnotexpanded;
                   $CI->Lexicon->guardarParseIFraseResultat($propietatsfrase['identry'], null, $frasefinalnotexpanded);

                   return;
               }
               else if ($numpreguntes == 1) {
                   $partpreguntaposada = $auxpattern->fillPartPregunta($partPregunta[0]);

                   if (!$partpreguntaposada && !$preguntabona) {
                       $this->errormessagetemp = "Warning. No s'ha trobat lloc per la partícula de la pregunta.";
                       $this->errorcodetemp = 4;
                       $this->errortemp = true;
                       $this->readwithoutexpansion = true;
                   }
                   else {
                       // només que hi hagi un patró on encaixi la partícula de pregunta, ja va bé
                       $preguntabona = true;
                       $CI->session->set_userdata('preguntapattern', true);
                       $this->errormessagetemp = null;
                       $this->errorcodetemp = null;
                       $this->errortemp = false;
                       $this->readwithoutexpansion = false;
                   }
               } // Fi tractament de pregunta

               // Si el verb és pseudoimpersonal o si hi ha una pregunta, invertim les preferències
               // d'aparèxier abans i després del verb, ja que ara el subjecte va darrere del verb
               // Les variables beforeverb només s'utilitzaran al codi si l'idioma té estructura SVO (Subject-Verb-Object)
               if ($auxpattern->pseudoimpersonal || $partpreguntaposada) {
                   for ($j=0; $j<count($paraules); $j++) {
                       $auxword = &$paraules[$j];
                       $auxword->beforeverb = !$auxword->beforeverb;
                   }
               }

               // echo "Noms: ".count($arrayNouns)."<br />";
               // Els noms
               $auxpattern->solveNouns($arrayNouns);
               // $auxpattern->printPattern();


               // echo "Adverbis: ".count($arrayAdvs)."<br />";
               // Els adverbis
               $auxpattern->solveAdverbs($arrayAdvs);
               // $auxpattern->printPattern();


               // echo "Adjectius: ".count($arrayAdjs)."<br />";
               // Els adjectius menys els numerals que els passarem amb la resta d'adjs i modificadors
               $auxpattern->solveAdjs($arrayAdjs);
               // $auxpattern->printPattern();


               // echo "Modificadors: ".count($arrayModifs)."<br />";
               // Els modificadors
               $auxpattern->solveModifs($arrayModifs);

               $auxreturn = $auxpattern->calcPuntsFinalPattern();
               $puntspattern = $auxreturn[0];
               $notusedpicto = $auxreturn[1];
               
               // si no ha pogut posar una paraula, activem un error temporal del patró
               if ($notusedpicto) {
                   $this->errormessagetemp = "Warning. No s'ha trobat lloc per una de les paraules.";
                   $this->errorcodetemp = 7;
                   $this->errortemp = true;
               }

               $this->puntsallpatterns[] = $puntspattern;

               $this->errormessage[] = $this->errormessagetemp;
               $this->errorcode[] = $this->errorcodetemp;
               $this->error[] = $this->errortemp;
               $this->preguntaposada[] = $partpreguntaposada;
               
               // reiniciem els errors pel següent patró
               $this->errormessagetemp = null;
               $this->errorcodetemp = null;
               $this->errortemp = false;
               
               $CI->session->set_userdata('preguntapattern', false);
               // DEBUG
               // echo $auxpattern->printPattern();

            } // fi per cada pattern

            // escollim el pattern amb més puntuació com a resultat
            $bestpatternindex = 0;
            $bestpatternpunts = -1000;

            for ($i=0; $i<count($this->puntsallpatterns); $i++) {
                
                // PER VEURE LES PUNTUACIONS DE TOTS ELS PATRONS QUE HA PROVAT 
                // echo "Patró ".$this->allpatterns[$i]->id.": ".$this->puntsallpatterns[$i]." </br ><br />";
                
                if ($this->puntsallpatterns[$i] > $bestpatternpunts) {
                    $bestpatternpunts = $this->puntsallpatterns[$i];
                    $bestpatternindex = $i;
                }
            }

            $bestpattern = new Mypattern();
            $bestpattern = unserialize(serialize($this->allpatterns[$bestpatternindex]));

            // agafem l'string amb el parse tree del pattern escollir
            $printparsepattern = $bestpattern->printPattern();

            $this->info['printparsepattern'] = $printparsepattern;

            // CRIDEM EL GENERADOR AMB EL MILLOR PATTERN, LES PROPIETATS DE LA FRASE SELECCIONADA
            // I SI HI HA UNA PARTÍCULA DE PREGUNTA

            $userlanguage = $CI->session->userdata('ulangabbr');
            $frasefinal = "";

            if ($userlanguage == "CA") {
                $frasefinal = $this->generateSentence($bestpattern, $propietatsfrase, $this->preguntaposada[$bestpatternindex]);
            } else if ($userlanguage == "ES") {
                $frasefinal = $this->generateSentenceES($bestpattern, $propietatsfrase, $this->preguntaposada[$bestpatternindex]);
            }

            // si el millor patró té un codi d'error 7 (paraula no utilitzada), aleshores llegim sense expansió
            if ($this->errorcode[$bestpatternindex] == 7) $this->readwithoutexpansion = true;
            
            // si hi ha hagut algun error o s'ha desactivat el sistema d'expansió, aleshores es llegeix sense expandir la frase
            if ($this->readwithoutexpansion) $this->info['frasefinal'] = $frasefinalnotexpanded;
            else $this->info['frasefinal'] = $frasefinal;
            
            // si l'idioma d'expansió no podia expandir i fa servir el castellà per defecte, traduïm la frase
            // amb el Google Translate
//            if ($CI->session->userdata('explangcannotexpand') == '1') {
//                
//                try {
//                    $tr = new TranslateClient($CI->session->userdata('ulangabbr'), $CI->session->userdata('ulangoriginalabbr'));
//                    $this->info['frasefinal'] = $tr->translate($this->info['frasefinal']);
//                } catch (Exception $exc) {
//                    $this->errormessage[$bestpatternindex] = "Error. Connection error. The sentence cannot be translated.";
//                    $this->error[$bestpatternindex] = true;
//                    $this->errorcode[$bestpatternindex] = 8;
//                }
//            }

            // Guardar parse tree i frase final a la base de dades
            if ($this->readwithoutexpansion) $CI->Lexicon->guardarParseIFraseResultat($propietatsfrase['identry'], null, $frasefinalnotexpanded);
            else $CI->Lexicon->guardarParseIFraseResultat($propietatsfrase['identry'], $printparsepattern, $frasefinal);

            $this->info['errormessage'] = $this->errormessage[$bestpatternindex];
            $this->info['error'] = $this->error[$bestpatternindex];
            $this->info['errorcode'] = $this->errorcode[$bestpatternindex];
            $this->info['readwithoutexpansion'] = $this->readwithoutexpansion;
            
            // MOSTREM LA INTERFÍCIE
            return;                
        }
    }



    // INICIALITZA TOTS ELS PATTERNS POSSIBLES I ELS POSA A L'ARRAY ALLPATTERNS
    function initialiseVerbPatterns($arrayVerbs, $propietatsfrase, $thereisadj, $adjdefverb, $adjdefsubj, $countnouns, $noundefverb, $othertypes)
    {   
        $CI = &get_instance();
        $CI->load->model('Lexicon');
        
        $numverbs = count($arrayVerbs);

        $auxword = new Myword();
        $auxpattern = new Mypattern();

        if ($numverbs > 2) {
            $this->allpatterns = null;
            $this->errormessagetemp = "Error. Hi ha més de dos verbs a la frase. <br />
                                El sistema actual no pot generar frases d'aquesta mena.";
            $this->readwithoutexpansion = true;
            $this->errorcodetemp = 3;
            return; // En aquest cas ja hauríem acabat
        }

        else if ($numverbs == 0) {

            // si no és una resposta afegir també els patterns per defecte dels noms
            // o dels adjectius (principalment ser o estar)
            if ($propietatsfrase['tipusfrase'] != "resposta") {
                
                // si hi ha un adjectiu i no hi ha noms, ni altres paraules excepte modificadors
                // agafem el verb per defecte del primer adjectiu introduït
                if ($thereisadj && $countnouns == 0 && !$othertypes) {
                    $arrayVerbs[] = $CI->Lexicon->getPatternsVerb($adjdefverb, ($adjdefverb != 0)); 
                }
                // si hi ha algun nom i no hi ha altres paraules excepte modificadors i/o adjectius
                // agafem el verb per defecte del primer nom introduït
                // si el nom no tenia verb per defecte i hi havia un adjectiu, agafem el verb per
                // defecte de l'adjectiu
                else if ($countnouns > 0 && !$othertypes) {
                    if ($noundefverb != 0) $arrayVerbs[] = $CI->Lexicon->getPatternsVerb($noundefverb, false);
                    else if ($thereisadj) $arrayVerbs[] = $CI->Lexicon->getPatternsVerb($adjdefverb, ($adjdefverb != 0));
                    else $arrayVerbs[] = $CI->Lexicon->getPatternsVerb(0, false); // Verbless
                }
                else {
                    // Agafem els verbless patterns
                    $arrayVerbs[] = $CI->Lexicon->getPatternsVerb(0, false); // Verbless
                }
                
            }
            else {
                // Agafem els verbless patterns
                $arrayVerbs[] = $CI->Lexicon->getPatternsVerb(0, false); // Verbless
            }

            // Per cada paraula
            for ($i=0; $i<count($arrayVerbs); $i++) {

                $auxword = &$arrayVerbs[$i]; // paraules passades per referència

                // Treiem els patterns de la paraula
                foreach ($auxword->patterns as $pattern) {

                    $auxpattern = new Mypattern();
                    // inicialitzem el pattern, li diem que és verbless i li passem el subjecte per defecte
                    // de l'adjectiu (si era un nom el valor serà false)
                    $auxpattern->initialise($pattern, true, $adjdefsubj); 

                    // Omplim el main verb
                    $auxpattern->forceFillSlot("Main Verb", $auxword, 0, 0);

                    $this->allpatterns[] = $auxpattern; // Posem el pattern al llistat de possibles patterns
                }
            }
            return; // En aquest cas ja hauríem acabat
        }

        else if ($numverbs == 1) {

            $auxword = &$arrayVerbs[0];

            foreach ($auxword->patterns as $pattern) {

                // menys els que eren de subverb
                if ($pattern->subverb == '0') {
                    $auxpattern = new Mypattern();
                    $auxpattern->initialise($pattern, false, false);

                    $auxpattern->forceFillSlot("Main Verb", $auxword, 0, 0);

                    $this->allpatterns[] = $auxpattern;
                }
            }
            return; // En aquest cas ja hauríem acabat
        }

        else if ($numverbs == 2) {

            $auxword = &$arrayVerbs[0];
            $auxword2 = new Myword();
            $auxword2 = &$arrayVerbs[1];

            $subverbfound = false;

            // Per cada pattern del 1er verb
            foreach ($auxword->patterns as $pattern) {

                if ($pattern->subverb == '1') { // Si el pattern accepta subverb

                    $auxpattern = new Mypattern();
                    $auxpattern->initialise($pattern, false, false);

                    // Posar a dins els patterns del segon verb que no accepten subverb
                    foreach ($auxword2->patterns as $pattern2) {

                        if ($pattern2->subverb == '0') {

                            $subverbfound = true;

                            $auxpattern2 = new Mypattern();
                            $auxpattern2->initialise($pattern2, false, false);

                            $auxpatternfusion = new Mypattern();
                            $auxpatternfusion = unserialize(serialize($auxpattern));

                            $auxpatternfusion->fusePatterns($auxpattern2);

                            // FER ELS FILLS DELS SLOTS DELS VERBS
                            $auxpatternfusion->forceFillSlot("Main Verb 1", $auxword, 0, 0);
                            $auxpatternfusion->forceFillSlot("Secondary Verb 2", $auxword2, 0, 0);

                            $this->allpatterns[] = $auxpatternfusion;
                        }
                    }

                }
            }

            if (!$subverbfound) { // si el primer verb no podia ser el principal

                // Per cada pattern del 2on verb
                foreach ($auxword2->patterns as $pattern2) {

                    if ($pattern2->subverb == '1') { // Si el pattern accepta subverb

                        $auxpattern2 = new Mypattern();
                        $auxpattern2->initialise($pattern2, false, false);

                        // Posar a dins els patterns del segon verb que no accepten subverb
                        foreach ($auxword->patterns as $pattern) {

                            if ($pattern->subverb == '0') {

                                $subverbfound = true;

                                $auxpattern = new Mypattern();
                                $auxpattern->initialise($pattern, false, false);

                                $auxpatternfusion = new Mypattern();
                                $auxpatternfusion = unserialize(serialize($auxpattern2));

                                $auxpatternfusion->fusePatterns($auxpattern);

                                // FER ELS FILLS DELS SLOTS DELS VERBS
                                $auxpatternfusion->forceFillSlot("Main Verb 1", $auxword2, 0, 0);
                                $auxpatternfusion->forceFillSlot("Secondary Verb 2", $auxword, 0, 0);

                                $this->allpatterns[] = $auxpatternfusion;
                            }
                        }
                    }
                }
            }
            if (!$subverbfound) {
                $this->errormessagetemp = "Error. No s'ha trobat cap patró possible amb aquests verbs.";
                $this->errorcodetemp = 5;
            }
        } // Fi if ($numverbs == 2)
    }


    function generateSentence($patternfinal, $propietatsfrase, $partpreguntaposada)
    {
        $pattern = new Mypattern();
        $pattern = $patternfinal;
        
        if ($partpreguntaposada) $propietatsfrase['tipusfrase'] = "pregunta";
        // si el temps per defecte és l'imperatiu, però hi ha modificadors de frase o de tense
        // sobreescrivim els valors
        if ($pattern->defaulttense == "imperatiu") {
            if ($propietatsfrase['tense'] != "defecte") {
                $pattern->defaulttense = $propietatsfrase['tense'];
                $pattern->tipusfrase = $propietatsfrase['tipusfrase'];
            }
            else if ($propietatsfrase['tipusfrase'] != "defecte" && 
                    !($propietatsfrase['tipusfrase'] == "exclamacio" || $propietatsfrase['tipusfrase'] == "resposta")) {
                $pattern->defaulttense = "present";
                $pattern->tipusfrase = $propietatsfrase['tipusfrase'];
            }
        }
        // Si el tipus de frase triat era per defecte, ara el tipus de frase és el per defecte del patró
        if ($propietatsfrase['tipusfrase'] == "defecte") $propietatsfrase['tipusfrase'] = $pattern->tipusfrase;
        
        // Indiquem que si el temps per defecte és l'imperatiu, que la frase és una ordre
        // a no ser que estigui activat el modificador de desig o permís que tenen preferència o
        // que hi hagi una partícula de pregunta.
//        if ($propietatsfrase['tense'] == "defecte" && $pattern->defaulttense == "imperatiu"
//                && (!$propietatsfrase['tipusfrase'] == "desig" || !$propietatsfrase['tipusfrase'] == "permis"
//                || !$partpreguntaposada)) {
//            $propietatsfrase['tipusfrase'] = "ordre";
//        }
//        else if ($partpreguntaposada) $propietatsfrase['tipusfrase'] = "pregunta";
//        
//        // Si el tipus de frase triat era per defecte, ara el tipus de frase és el per defecte del patró
//        if ($propietatsfrase['tipusfrase'] == "defecte") $propietatsfrase['tipusfrase'] = $pattern->tipusfrase;

        // 1. Ordenem els slots segons el tipus de frase
        $pattern->ordenarSlotsFrase($propietatsfrase);

        // 2 i 3. 2: Ordenar paraules de dins dels slots, ja posant les preposicions.
        // 3: Controlar que les paraules concordin en gènere i número (els adjs amb els noms 
        // i la PartPregunta "quant" amb el theme, si hi és). Afegir també les coordinacions
        // només de NOMS, ADJECTIUS i ADVERBIS DE MANERA
        $pattern->ordenarSlotsInternament();

        // 4. Posar articles als noms

        $pattern->putArticlesToNouns($propietatsfrase["tipusfrase"]);

        // 5. Conjugar els verbs

        $pattern->conjugarVerbs($propietatsfrase);

        // 6. Treure els "jo" i "tu" dels subjectes. Canviar receivers a pronoms febles i posar-los
        // a darrere el verb si cal. Posar modificadors de frase com el "no" o el "també".
        // Fusionar preposicions amb articles (de+el/s = del/s... a+el, per+el...). Posar apòstrofs 
        // de preps i pronoms febles (i guions?). Netejar espais abans o després dels apòstrofs.
        // Escriure la frase final, posant les expressions i altres advs de temps al final.

        $pattern->launchCleaner($propietatsfrase["tipusfrase"]);

        return $pattern->printFraseFinal();
    }

    function generateSentenceES($patternfinal, $propietatsfrase, $partpreguntaposada)
    {
        $pattern = new Mypattern();
        $pattern = $patternfinal;

        if ($partpreguntaposada) $propietatsfrase['tipusfrase'] = "pregunta";
        // si el temps per defecte és l'imperatiu, però hi ha modificadors de frase o de tense
        // sobreescrivim els valors
        if ($pattern->defaulttense == "imperatiu") {
            if ($propietatsfrase['tense'] != "defecte") {
                $pattern->defaulttense = $propietatsfrase['tense'];
                $pattern->tipusfrase = $propietatsfrase['tipusfrase'];
            }
            else if ($propietatsfrase['tipusfrase'] != "defecte" && 
                    !($propietatsfrase['tipusfrase'] == "exclamacio" || $propietatsfrase['tipusfrase'] == "resposta")) {
                $pattern->defaulttense = "present";
                $pattern->tipusfrase = $propietatsfrase['tipusfrase'];
            }
        }
        // Si el tipus de frase triat era per defecte, ara el tipus de frase és el per defecte del patró
        if ($propietatsfrase['tipusfrase'] == "defecte") $propietatsfrase['tipusfrase'] = $pattern->tipusfrase;
        
//        // Indiquem que si el temps per defecte és l'imperatiu, que la frase és una ordre
//        // a no ser que estigui activat el modificador de desig o permís que tenen preferència o
//        // que hi hagi una partícula de pregunta.
//        if ($propietatsfrase['tense'] == "defecte" && $pattern->defaulttense == "imperatiu"
//                && (!$propietatsfrase['tipusfrase'] == "desig" || !$propietatsfrase['tipusfrase'] == "permis"
//                || !$partpreguntaposada)) {
//            $propietatsfrase['tipusfrase'] = "ordre";
//        }
//        else if ($partpreguntaposada) $propietatsfrase['tipusfrase'] = "pregunta";
//        
//        // Si el tipus de frase triat era per defecte, ara el tipus de frase és el per defecte del patró
//        if ($propietatsfrase['tipusfrase'] == "defecte") $propietatsfrase['tipusfrase'] = $pattern->tipusfrase;

        // 1. Ordenem els slots segons el tipus de frase
        $pattern->ordenarSlotsFraseES($propietatsfrase);

        // 2 i 3. 2: Ordenar paraules de dins dels slots, ja posant les preposicions.
        // 3: Controlar que les paraules concordin en gènere i número (els adjs amb els noms 
        // i la PartPregunta "quant" amb el theme, si hi és). Afegir també les coordinacions
        // només de NOMS, ADJECTIUS i ADVERBIS DE MANERA
        $pattern->ordenarSlotsInternamentES();

        // 4. Posar articles als noms

        $pattern->putArticlesToNounsES($propietatsfrase["tipusfrase"]);

        // 5. Conjugar els verbs

        $pattern->conjugarVerbsES($propietatsfrase);

        // 6. Treure els "jo" i "tu" dels subjectes. Canviar receivers a pronoms febles i posar-los
        // a darrere el verb si cal. Posar accents a les noves formes verbals, si cal.
        // Posar modificadors de frase com el "no" o el "també".
        // Fusionar preposicions amb articles (de+el/s = del/s... a+el...).
        // Escriure la frase final, posant les expressions i altres advs de temps al final.

        $pattern->launchCleanerES($propietatsfrase["tipusfrase"]);

        return $pattern->printFraseFinal();
    }    
    
}

/* End of file Mypatterngroup.php */