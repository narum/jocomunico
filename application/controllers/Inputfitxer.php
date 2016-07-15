<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Inputfitxer extends CI_Controller {

	public function __construct()
        {
            parent::__construct();

            $this->load->model('Lexicon');
            $this->load->library('Myword');
            $this->load->library('Myslot');
            $this->load->library('Mypattern');
            $this->load->library('Myexpander');
        }

	public function index()
	{
            // CHECK COOKIES
            if (!$this->session->userdata('uname')) {
                redirect(base_url(), 'location');
            }
            else {
                
                $filename = "txt/joan.txt";
                $fitxertxt = fopen($filename,"r+b");
                $length = filesize($filename);
                
                if ($length > 0 && flock($fitxertxt, LOCK_EX)) {
                    $frase = fread($fitxertxt, $length);
                    ftruncate($fitxertxt, 0);
                    flock($fitxertxt, LOCK_UN);
                    fclose($fitxertxt);

                    $fraseconvertida = iconv("Windows-1252", "utf-8", $frase);
                    // GUARDAR LA FRASE DES DE L'ARXIU A LA BBDD
                    $this->Lexicon->insertarFraseDesDArxiu($fraseconvertida);

                    $expander = new Myexpander();
                    $expander->expand();
                    $info = $expander->info;
                    
                    $frasefinal = $info["frasefinal"];
                    $frasefinal = iconv("utf-8", "Windows-1252", $frasefinal);

                    $filenamewrite = "txt/jocomunico.txt";
                    $fitxertxtwrite = fopen($filenamewrite,"w+b");
                    if (flock($fitxertxtwrite, LOCK_EX)) {
                        fwrite($fitxertxtwrite, $frasefinal);
                        flock($fitxertxtwrite, LOCK_UN);
                        fclose($fitxertxtwrite);
                    }

                    $return['first'] = 1;
                    $return['second'] = $info["frasefinal"]." / ".$fraseconvertida;
                    $this->load->view('esperant-frase', $return);
                }
                else {
                    fclose($fitxertxt);
                    $return['first'] = 1;
                    $return['second'] = "";
                    $this->load->view('esperant-frase', $return);
                }
            }
	}
        
        function hiHaFrase()
        {
            $timer = $this->input->post('timer', true);
            $timer += 1;
            
            $filename = "txt/joan.txt";
            $fitxertxt = fopen($filename,"r+b");
            $length = filesize($filename);

            if ($length > 0 && flock($fitxertxt, LOCK_EX)) {
                $frase = fread($fitxertxt, $length);
                ftruncate($fitxertxt, 0);
                flock($fitxertxt, LOCK_UN);
                fclose($fitxertxt);

                $fraseconvertida = iconv("Windows-1252", "utf-8", $frase);
                // GUARDAR LA FRASE DES DE L'ARXIU A LA BBDD
                $this->Lexicon->insertarFraseDesDArxiu($fraseconvertida);

                $expander = new Myexpander();
                $expander->expand();
                $info = $expander->info;
                
                $frasefinal = $info["frasefinal"];
                $frasefinal = iconv("utf-8", "Windows-1252", $frasefinal);
                
                $filenamewrite = "txt/jocomunico.txt";
                $fitxertxtwrite = fopen($filenamewrite,"w+b");
                if (flock($fitxertxtwrite, LOCK_EX)) {
                    fwrite($fitxertxtwrite, $frasefinal);
                    flock($fitxertxtwrite, LOCK_UN);
                    fclose($fitxertxtwrite);
                }
                
                $return['first'] = 1;
                $return['second'] = $info["frasefinal"]." / ".$fraseconvertida;
                                
                echo json_encode($return);
            }
            else {
                fclose($fitxertxt);
                
                $return['first'] = $timer;
                $return['second'] = "";
                echo json_encode($return);
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
