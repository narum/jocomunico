<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Frase extends CI_Controller {

	public function __construct()
        {
            parent::__construct();

            $this->load->model('Lexicon');
        }

	public function index()
	{
            
            // CHECK COOKIES
            if (!$this->session->userdata('uname')) {
                redirect(base_url(), 'location');
            }
            else {

                $this->load->library('form_validation');
                $this->form_validation->set_rules('tipusfrase', 'Tipus de frase', 'required');

                if ($this->form_validation->run() == false) {

                    // BUSCAR TOTS ELS LLISTATS A LA BBDD
                    $info['nomsTemps'] = $this->Lexicon->getNoms(array('time'));
                    $info['nomsWeek'] = $this->Lexicon->getNoms(array('week'));
                    $info['nomsMonth'] = $this->Lexicon->getNoms(array('month'));
                    $info['nomsHora'] = $this->Lexicon->getNoms(array('hora'));

                    $info['nomsAnimal'] = $this->Lexicon->getNoms(array('animal'));
                    $info['nomsAnimat'] = $this->Lexicon->getNoms(array('animate'));
                    $info['nomsPlanta'] = $this->Lexicon->getNoms(array('planta'));

                    $info['nomsPronoun'] = $this->Lexicon->getNoms(array('pronoun'));
                    $info['nomsHuman'] = $this->Lexicon->getNoms(array('human'));

                    $info['nomsAbstracte'] = $this->Lexicon->getNoms(array('abstracte'));
                    $info['nomsJoc'] = $this->Lexicon->getNoms(array('joc'));
                    $info['nomsObjecte'] = $this->Lexicon->getNoms(array('objecte'));
                    $info['nomsCos'] = $this->Lexicon->getNoms(array('cos'));
                    $info['nomsForma'] = $this->Lexicon->getNoms(array('forma', 'color'));
                    $info['nomsMenjar'] = $this->Lexicon->getNoms(array('menjar'));
                    $info['nomsBeguda'] = $this->Lexicon->getNoms(array('beguda'));

                    $info['nomsLloc'] = $this->Lexicon->getNoms(array('lloc'));

                    $info['verbs'] = $this->Lexicon->getVerbs();

                    $info['adjsAnimat'] = $this->Lexicon->getAdjs(array('human', 'animate'));
                    $info['adjsObjecte'] = $this->Lexicon->getAdjs(array('objecte', 'menjar'));
                    $info['adjsAll'] = $this->Lexicon->getAdjs(array('all'));
                    $info['adjsColor'] = $this->Lexicon->getAdjs(array('color'));

                    $info['adjsNumero'] = $this->Lexicon->getAdjs(array('numero'));
                    $info['adjsOrdinal'] = $this->Lexicon->getAdjs(array('ordinal'));

                    $info['advsLloc'] = $this->Lexicon->getAdvs(array('lloc'));
                    $info['advsTemps'] = $this->Lexicon->getAdvs(array('temps'));
                    $info['advsManera'] = $this->Lexicon->getAdvs(array('manera'));

                    $info['modifsWord'] = $this->Lexicon->getModifs(array('word'));
                    $info['modifsPhrase'] = $this->Lexicon->getModifs(array('phrase'));

                    $info['expressions'] = $this->Lexicon->getExprs(array('complet'));

                    $info['partspregunta'] = $this->Lexicon->getPartPregunta();
                    
                    // MIRAR SI L'USUARI TÉ UNA FRASE A MITGES
                    $info['paraulesFrase'] = $this->Lexicon->recuperarFrase($this->session->userdata('idusu'));
                    $this->load->view('interficie'.$this->session->userdata('ulangabbr'), $info);
                }
                else {
                    // GUARDAR LA FRASE SENCERA A LA BBDD
                    $this->Lexicon->insertarFrase($this->session->userdata('idusu'));
                    redirect(base_url().'resultats', 'location');
                }
            }
	}

        function afegirParaula()
        {
            $idparaula = $this->input->post('idparaula', true);
            $taula = $this->input->post('taula', true);

            $this->Lexicon->afegirParaula($this->session->userdata('idusu'), $idparaula, $taula);

            $data['paraulesFrase'] = $this->Lexicon->recuperarFrase($this->session->userdata('idusu'));

            $this->load->view('frase-building', $data);
        }

        function afegirModifNom()
        {
            $modif = $this->input->post('modif', true);

            $this->Lexicon->afegirModifNom($this->session->userdata('idusu'), $modif);

            $data['paraulesFrase'] = $this->Lexicon->recuperarFrase($this->session->userdata('idusu'));

            $this->load->view('frase-building', $data);
        }

        function eliminarParaula()
        {
            $identry = $this->input->post('identry', true);

            $this->Lexicon->eliminarParaula($identry);

            $data['paraulesFrase'] = $this->Lexicon->recuperarFrase($this->session->userdata('idusu'));

            $this->load->view('frase-building', $data);
        }

}
