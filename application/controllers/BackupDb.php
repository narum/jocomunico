<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BackupDb extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->dbutil();
    }

    private function prefs() {
        return array(                
            'format'    => 'zip',
            'filename'  => date("Y-m-d-H_i").'-jocomunicoapp.sql'
        );
    }   

    public function saveBackup()
    {
        $backup = $this->dbutil->backup($this->prefs());

        $this->load->helper('file');
        write_file('ddbb/'.date("Y-m-d-H_i").'.zip', $backup);            
    }

    private function downloadBackup()
    {
        $backup = $this->dbutil->backup($this->prefs());

        $this->load->helper('download');
        force_download(date("Y-m-d-H_i").'.zip', $backup);
    }   
    
}
