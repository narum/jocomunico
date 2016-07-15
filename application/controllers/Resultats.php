<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Resultats extends CI_Controller {
    
    var $allpatterns = array(); // Array amb tots els patterns possibles per una entrada amb un o varis verbs
    /* Un pattern té un array d'slots (cada slot té les propietats més un array de paraules que l'estan omplint)
     * més un booleà que diu si ja està ple.
     */
    var $puntsallpatterns = array(); // array amb els punts finals de cada pattern
    var $patternescollit = 0; // id del pattern dins de l'array allpatterns
    
    var $errormessagetemp = null; 
    var $errormessage = array(); 
    var $preguntaposada = array();
    var $paraulescopia = array();

	public function __construct()
        {
            parent::__construct();

            $this->load->model('Lexicon');
            $this->load->library('Myword');
            $this->load->library('Myslot');
            $this->load->library('Mypattern');
            $this->load->library('Myexpander');
            // $this->load->library('Mymatching');
            // $this->load->library('Mypatterngroup');
        }

	public function index()
	{
            $expander = new Myexpander();
            $expander->expand();
            $info = $expander->info;
            $this->load->view('resultats', $info); 
            
	}
        
        
        
        // INICIALITZA TOTS ELS PATTERNS POSSIBLES I ELS POSA A L'ARRAY ALLPATTERNS
        function initialiseVerbPatterns($arrayVerbs, $propietatsfrase)
        {            
            $numverbs = count($arrayVerbs);
                    
            $auxword = new Myword();
            $auxpattern = new Mypattern();

            if ($numverbs > 2) {
                $this->allpatterns = null;
                $this->errormessagetemp = "Error. Hi ha més de dos verbs a la frase. <br />
                                    El sistema actual no pot generar frases d'aquesta mena.";
                return; // En aquest cas ja hauríem acabat
            }

            else if ($numverbs == 0) {
                // Agafem els verbless patterns
                $arrayVerbs[] = $this->Lexicon->getPatternsVerb(0); // Verbless
                
                // si no és una resposta afegir també els patterns de ser i estar
                if ($propietatsfrase['tipusfrase'] != "resposta") {
                    $arrayVerbs[] = $this->Lexicon->getPatternsVerb(100); // Estar
                    $arrayVerbs[] = $this->Lexicon->getPatternsVerb(86); // Ser
                }

                // Per cada paraula
                for ($i=0; $i<count($arrayVerbs); $i++) {

                    $auxword = &$arrayVerbs[$i]; // paraules passades per referència
                    
                    // Treiem els patterns de la paraula
                    foreach ($auxword->patterns as $pattern) {

                        $auxpattern = new Mypattern();
                        $auxpattern->initialise($pattern); // inicialitzem el pattern

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
                        $auxpattern->initialise($pattern);

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
                        $auxpattern->initialise($pattern);

                        // Posar a dins els patterns del segon verb que no accepten subverb
                        foreach ($auxword2->patterns as $pattern2) {

                            if ($pattern2->subverb == '0') {

                                $subverbfound = true;

                                $auxpattern2 = new Mypattern();
                                $auxpattern2->initialise($pattern2);

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
                            $auxpattern2->initialise($pattern2);

                            // Posar a dins els patterns del segon verb que no accepten subverb
                            foreach ($auxword->patterns as $pattern) {

                                if ($pattern->subverb == '0') {

                                    $subverbfound = true;

                                    $auxpattern = new Mypattern();
                                    $auxpattern->initialise($pattern);

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
                if (!$subverbfound) $this->errormessagetemp = "Error. No s'ha trobat cap patró
                                                            possible amb aquests verbs.";
            } // Fi if ($numverbs == 2)
        }
        
        
        function generateSentence($patternfinal, $propietatsfrase, $partpreguntaposada)
        {
            $pattern = new Mypattern();
            $pattern = $patternfinal;
            
            // Indiquem que si el temps per defecte és l'imperatiu, que la frase és una ordre
            // a no ser que estigui activat el modificador de desig o permís que tenen preferència o
            // que hi hagi una partícula de pregunta.
            if ($propietatsfrase['tense'] == "defecte" && $pattern->defaulttense == "imperatiu"
                    && (!$propietatsfrase['tipusfrase'] == "desig" || !$propietatsfrase['tipusfrase'] == "permis"
                    || !$partpreguntaposada)) {
                $propietatsfrase['tipusfrase'] = "ordre";
            }
            else if ($partpreguntaposada) $propietatsfrase['tipusfrase'] = "pregunta";
                        
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
            
            // Indiquem que si el temps per defecte és l'imperatiu, que la frase és una ordre
            // a no ser que estigui activat el modificador de desig o permís que tenen preferència o
            // que hi hagi una partícula de pregunta.
            if ($propietatsfrase['tense'] == "defecte" && $pattern->defaulttense == "imperatiu"
                    && (!$propietatsfrase['tipusfrase'] == "desig" || !$propietatsfrase['tipusfrase'] == "permis"
                    || !$partpreguntaposada)) {
                $propietatsfrase['tipusfrase'] = "ordre";
            }
            else if ($partpreguntaposada) $propietatsfrase['tipusfrase'] = "pregunta";
                        
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
        
        public function gracies()
        {
            $identry = $this->input->post('identry', true);
            $scoreparser = $this->input->post('scoreparser', true);
            $scoregen = $this->input->post('scoregen', true);
            $comments = $this->input->post('comments', true);

            $this->Lexicon->addEntryScores($identry, $scoreparser, $scoregen, $comments);

            $this->load->view('gracies');
        }
}
