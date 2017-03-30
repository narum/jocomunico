<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Myaudio {
            
    function __construct() {}
    
    /*
     * RETURNS true if connected or false otherwise
     */
    public function isOnline() 
    {
        // pings example.com and google.com
        $is_conn = null;
        $connected1 = @fsockopen("www.example.com", 80); //website, port  (try 80 or 443)
        $connected2 = @fsockopen("www.google.com", 80); //website, port  (try 80 or 443)
        // if either is successful
        if ($connected1 || $connected2){
            $is_conn = true; //action when connected
            fclose($connected1);
            fclose($connected2);
        }else{
            $is_conn = false; //action in connection failure
        }
        return $is_conn;
    }
    
    /*
     * RETURNS 'local' if the App is running locally or 'server' if it's running from the server
     */
    public function AppLocalOrServer()
    {
        if (preg_match('/localhost/i', base_url())) return "local";
        else return "server";
    }
    
    /*
     * Gets the OS from which the app is running
     */
    public function getOS() { 
        
        $server_data = $_SERVER['HTTP_USER_AGENT'];

        $os_platform    =   "Unknown OS Platform";

        $os_array       =   array(
                                '/windows nt 10/i'      =>  'Windows',
                                '/windows nt 6.3/i'     =>  'Windows',
                                '/windows nt 6.2/i'     =>  'Windows',
                                '/windows nt 6.1/i'     =>  'Windows',
                                '/windows nt 6.0/i'     =>  'Windows',
                                '/windows nt 5.2/i'     =>  'Windows',
                                '/windows nt 5.1/i'     =>  'Windows',
                                '/windows xp/i'         =>  'Windows',
                                '/windows nt 5.0/i'     =>  'Windows 2000',
                                '/windows me/i'         =>  'Windows ME',
                                '/win98/i'              =>  'Windows 98',
                                '/win95/i'              =>  'Windows 95',
                                '/win16/i'              =>  'Windows 3.11',
                                '/macintosh|mac os x/i' =>  'Mac OS X',
                                '/mac_powerpc/i'        =>  'Mac OS 9',
                                '/linux/i'              =>  'Linux',
                                '/ubuntu/i'             =>  'Ubuntu',
                                '/iphone/i'             =>  'iPhone',
                                '/ipod/i'               =>  'iPod',
                                '/ipad/i'               =>  'iPad',
                                '/android/i'            =>  'Android',
                                '/blackberry/i'         =>  'BlackBerry',
                                '/webos/i'              =>  'Mobile'
                            );

        foreach ($os_array as $regex => $value) { 

            if (preg_match($regex, $server_data)) {
                $os_platform    =   $value;
            }
        }   

        return $os_platform;
    }
    
    /*
     * Returns an array with [0] name of voices found locally [1] true if there was an error
     * [2] error message [3] error code
     */
    public function getLocalVoices()
    {
        $user_agent = $this->getOS();
        
        $voices = array();
        $error = false;
        $errorcode = 0;
        $errormessage = null;
        
        switch ($user_agent) {
                
            case "Mac OS X":
                    
                try {
                    // Llistat de veus
                    $cmdresponse = shell_exec("say --voice=?");

                    // Partim pels espais d'abans de la definició de l'idioma de format xX_xX
                    // fins al salt de línia
                    $voices = preg_split( '/[\s]+..[_-][a-zA-Z]+[\s]+#[^\r\n]*(\r\n|\r|\n)/', $cmdresponse);
                    // eliminem l'últim element que és buit
                    array_pop($voices);
                    
                } catch (Exception $ex) {
                    $error = true;
                    $errormessage = "Error. Unable to access your Mac OS X voices. Try activating your system"
                            . "voices. Otherwise, your OS X may not be compatible with the 'say' command.";
                    $errorcode = 101;
                }
                
                if (!$error && count($voices) < 1) {
                    $error = true;
                    $errormessage = "Error. No installed voices found. Activate your system"
                            . "voices or install external voices for Mac OS X (i.e. Acapela voices).";
                    $errorcode = 102;
                }

                break;
                    
            case "Windows":

                // error de Microsoft Speech Platform
                $errorMSP = false;
                $errorMSPtmp = null;
                
                try {
                    // Recollim els objectes de les llibreries Speech de Microsoft que necessitem
                    $msVoice = new COM('Speech.SpVoice');

                    $numvoices = $msVoice->GetVoices()->Count;

                    // agafem les veus, la descripció la farem servir per buscar els idiomes
                    // de cada una d'elles, idealment són les que s'haurien de llistar
                    // a la interfície de l'usuari
                    for ($i=0; $i<$numvoices; $i++) {
                        $voices[] = $msVoice->GetVoices()->Item($i)->GetDescription;
                    }

                    // DEBUG
                    // print_r($voices);
                    
                } catch (Exception $ex) {
                    $errorMSP = true;
                    $errorMSPtmp = "Error. Unable to access Microsoft Speech Platform.";
                }
                
                // error de SAPI
                $errorSAPI = false;
                $errorSAPItmp = null;
                
                try {
                    // Recollim els objectes de les llibreries SAPI que necessitem

                    $msSAPIVoice = new COM('SAPI.SpVoice');

                    $numvoicesSAPI = $msSAPIVoice->GetVoices()->Count;

                    // agafem les veus, la descripció la farem servir per buscar els idiomes
                    // de cada una d'elles, idealment són les que s'haurien de llistar
                    // a la interfície de l'usuari

                    for ($i=0; $i<$numvoicesSAPI; $i++) {
                        $voices[] = $msSAPIVoice->GetVoices()->Item($i)->GetDescription;
                    }
                    // DEBUG
                    // print_r($voices);
                    
                } catch (Exception $ex) {
                    $errorSAPI = true;
                    $errorSAPItmp = "Error. Unable to access SAPI voices.";
                }
                
                if ($errorMSP && $errorSAPI) {
                    $error = true;
                    $errormessage = "Error. Unable to access your Windows voices. "
                            . "Install Microsoft Speech Platform (MSP) or SAPI voices. Otherwise, "
                            . "your Windows may not be compatible with MSP or SAPI.";
                    $errorcode = 103;
                }
                else if (count($voices) < 1) {
                    $error = true;
                    $errormessage = "Error. No installed voices found. "
                            . "Install Microsoft Speech Platform or SAPI voices.";
                    $errorcode = 104;
                }

                break;
                
            default:
                $error = true;
                $errormessage = "Error. Your OS is not compatible with the offline version of this app.";
                $errorcode = 105;
                break;
        }
        
        $output = array(
            0 => $voices,
            1 => $error,
            2 => $errormessage,
            3 => $errorcode
        );
        
        return $output;
    }

    /** 
     * @param bool $online parameter that says if online voices need to be added
     * (for the Interface voices, there will only be two default online voices for each language)
     * @return array $output Description: $output[0] an array of the available voices for the interface,
     * for each voice we have voiceName and voiceType
     * NOTE: calling function should check for returned errors in $output[1],
     * errormessage in $output[2] errorcode in $output[3]
     */
    public function listInterfaceVoices($online) 
    {
        $output = array();
        $output[1] = false;
        $output[2] = null;
        $output[3] = 0;
        $arrayVoices = array();
        
        if ($online) {
            $arrayVoices = array(
                0 => array(
                    'voiceName' => 'DEFAULT (fem)',
                    'voiceType' => 'online'
                ),
                1 => array(
                    'voiceName' => 'DEFAULT (masc)',
                    'voiceType' => 'online'
                )
            );
        }
                
        // we add the voices in the local CPU if the app is running locally
        if ($this->AppLocalOrServer() == 'local') {
            $auxresponse = $this->getLocalVoices();
            $localvoices = $auxresponse[0];
            $output[1] = $auxresponse[1];
            $output[2] = $auxresponse[2];
            $output[3] = $auxresponse[3];
            
            for ($i=0; $i<count($localvoices); $i++) {
                $aux = array();
                $aux['voiceName'] = $localvoices[$i];
                $aux['voiceType'] = "offline";
                $arrayVoices[] = $aux;
            }
        }
        
        $output[0] = $arrayVoices;
        
        return $output;
    }
    
    /** 
     * @param bool $online parameter that says if online voices need to be added
     * @return array $output Description: $output[0] an array of the available voices for the interface,
     * for each voice we have voiceName and voiceType
     * NOTE: calling function should check for returned errors in $output[1],
     * errormessage in $output[2] errorcode in $output[3]
     */
    public function listExpansionVoices($online)
    {
        $CI = &get_instance();
        $CI->load->model('Audio_model');
        
        $output = array();
        $output[1] = false;
        $output[2] = null;
        $output[3] = 0;
        $arrayVoices = array();
        
        if ($online) {
            $arrayVoices = $CI->Audio_model->getOnlineVoices(0);
        }
                
        // we add the voices in the local CPU if the app is running locally
        if ($this->AppLocalOrServer() == 'local') {
            $auxresponse = $this->getLocalVoices();
            $localvoices = $auxresponse[0];
            $output[1] = $auxresponse[1];
            $output[2] = $auxresponse[2];
            $output[3] = $auxresponse[3];
            
            for ($i=0; $i<count($localvoices); $i++) {
                $aux = array();
                $aux['voiceName'] = $localvoices[$i];
                $aux['voiceType'] = "offline";
                $arrayVoices[] = $aux;
            }
        }
        
        $output[0] = $arrayVoices;
        
        return $output;
    }
    
    /**
     * 
     * @param int $idusu Id of the current user
     * @param string $text string to generate audio
     * @param bool $interface TRUE if it's a string for the Interface,
     * FALSE if it's a string that comes from Expansion (MD5 and voices are 
     * treated differently for each of them)
     * @return array $output[0] Name of the generated audio file
     * NOTE: calling function should check for returned errors in $output[1],
     * errormessage in $output[2] errorcode in $output[3]
     */
    public function generateAudio($idusu, $text, $interface) 
    {
        $CI = &get_instance();
        $CI->load->model('Audio_model');
        
        $output = array();
        $output[1] = false; // error
        $output[2] = null; // error message
        $output[3] = 0; // error code
        
        // the name of the fetched (from the database) or the generated audio file
        // it will be in output[0]
        $filename = null;
        
        // all the info related to the voices and the languages from the user
        $applocal = $this->AppLocalOrServer();
        $userinfo = $CI->Audio_model->getUserInfo($idusu);
        
//        echo "Info USUARI: <br />";
//        print_r($userinfo);
//        echo "<br /><br />";
        
        
        $interfacelanguage = $userinfo->ID_ULanguage;
        $interfacegender = $userinfo->cfgInterfaceVoiceMascFem;
        $expansionlanguage = $userinfo->cfgExpansionLanguage;
        $rate = $userinfo->cfgVoiceOfflineRate;
        
        $md5 = "";
        
        if ($interface) {
            // Encoded file name: #INTERFACE@IdInterfaceLanguage#(masc|fem)$string
            $key = "#INTERFACE@".$interfacelanguage."#".$interfacegender."$".$text;
            $md5 = md5($key);
            
            $isindb = $CI->Audio_model->isAudioInDatabase($md5);
                        
            // if it's already in the database
            if ($isindb) $filename = $isindb;
            else {
                $voice = "";
                $type = "";
                
                // if the app is run locally
                if ($applocal == "local") {
                    // if it has internet connection
                    if ($this->isOnline()) {
                        $voice = $userinfo->cfgInterfaceVoiceOnline;
                        // if it uses the default online voices for the interface
                        if (preg_match("/DEFAULT \(/i", $voice)) {
                            $type = "online";
                        }
                        else $type = "offline";
                    }
                    // no internet connection
                    else {
                        $voice = $userinfo->cfgInterfaceVoiceOffline;
                        $type = "offline";                    
                    }
                }
                // if the app is run from the server
                else {
                    $voice = $userinfo->cfgInterfaceVoiceOnline;
                    $type = "online";
                }
                
                $auxresponse = $this->synthesizeAudio($md5, $text, $voice, $type, $interfacelanguage, $rate);
                $filename = $auxresponse[0];
                $output[1] = $auxresponse[1];
                $output[2] = $auxresponse[2];
                $output[3] = $auxresponse[3];
            }
        }
        // string from the expansion system
        else {
            // Encoded file name: #EXPANSION@(VoiceName|VoiceIdForOnlineVoices)#VoiceType$string
            $key = "#EXPANSION@";
            $voice = "";
            $type = "";
            
            // if the app is run locally
            if ($applocal == "local") {
                // if it has internet connection
                if ($this->isOnline()) {
                    $voice = $userinfo->cfgExpansionVoiceOnline;
                    $type = $userinfo->cfgExpansionVoiceOnlineType;
                }
                // no internet connection
                else {
                    $voice = $userinfo->cfgExpansionVoiceOffline;
                    $type = "offline";                    
                }
            }
            // if the app is run from the server
            else {
                $voice = $userinfo->cfgExpansionVoiceOnline;
                $type = "online";
            }
            
            $key .= $voice."#".$type."$".$text;
            $md5 = md5($key);

            $isindb = $CI->Audio_model->isAudioInDatabase($md5);

            // if it's already in the database
            if ($isindb) $filename = $isindb;
            else {
                $auxresponse = $this->synthesizeAudio($md5, $text, $voice, $type, $expansionlanguage, $rate);
                $filename = $auxresponse[0];
                $output[1] = $auxresponse[1];
                $output[2] = $auxresponse[2];
                $output[3] = $auxresponse[3];
            }
        }
        
        $output[0] = $filename;
        
        return $output;        
    }
    
    /**
     * 
     * It generates the audio and saves it to the database and into an audio file
     * @param string $md5 filename without the extension
     * @param string $text string to synthesize
     * @param string $voice voice name for offline voices or id for online voices (except 
     * for DEFAULT online interface voices)
     * @param string $type online or offline
     * @param int $language id of the language of the string to synthetize
     * @param type $rate rate of speech speed of offline voices
     * @return array $output[0] Name of the generated audio file with the extension
     * NOTE: calling function should check for returned errors in $output[1],
     * errormessage in $output[2] errorcode in $output[3]
     */
    function synthesizeAudio($md5, $text, $voice, $type, $language, $rate)
    {      
    	
        // DEBUG
    	// echo "T: ".$text."; V: ".$voice."; Ty: ".$type."; L: ".$language;
    	
        $CI = &get_instance();
        $CI->load->model('Audio_model');
        
        $output = array();
        $error = false;
        $errormessage = null;
        $errorcode = 0;
        $filename = null;
        $extension = "mp3";
        
        // if it's an online voice
        if ($type == "online") {
            
            // default voice ES masc (Jorge)
            $vocalwareLID = 2;
            $vocalwareVID = 6;
                        
            // if it's a default interface voice
            if (preg_match("/DEFAULT \(/i", $voice)) {
                $isfem = true;
                if (preg_match("/DEFAULT \(masc\)/i", $voice)) $isfem = false;
                
                // get default values for the interface voice in each language
                switch ($language) {
                    
                    // CA
                    case 1:
                        $vocalwareLID = 5;
                        if ($isfem) $vocalwareVID = 1;
                        else $vocalwareVID = 2;
                        break;
                    
                    // ES
                    case 2:
                        $vocalwareLID = 2;
                        if ($isfem) $vocalwareVID = 1;
                        else $vocalwareVID = 6;
                        break;
                    
                    // EN
                    case 3:
                        $vocalwareLID = 1;
                        if ($isfem) $vocalwareVID = 1;
                        else $vocalwareVID = 2;
                        break;
                    
                    default:
                        $error = true;
                        $errormessage = "Error. Default voice not found for this language.";
                        $errorcode = 106;
                        break;
                }                
            }
            // the voice is the id of the voice in the database
            else {
                // we get the info of the voice from the database
                $auxrow = $CI->Audio_model->getOnlineVoices((int) $voice);
                $voiceinfo = $auxrow[0];
                
                $vocalwareLID = $voiceinfo->vocalwareIdLang;
                $vocalwareVID = $voiceinfo->vocalwareVoiceId;
            }
            
            if (!$error) {
                $auxresponse = $this->synthesizeOnline($vocalwareLID, $vocalwareVID, $text, $md5);
                // if there was an error
                if ($auxresponse[0]) {
                    $error = true;
                    $errormessage = $auxresponse[1];
                    $errorcode = $auxresponse[2];
                }
            }
            
        }
        // si la veu és offline
        else {
            $user_agent = $this->getOS();
            
            switch ($user_agent) {
                case "Mac OS X":
                    $extension = "m4a";
                    
                    $auxresponse = $this->synthesizeMacOSX($voice, $text, $md5, $rate);
                    // if there was an error
                    if ($auxresponse[0]) {
                        $error = true;
                        $errormessage = $auxresponse[1];
                        $errorcode = $auxresponse[2];
                    }

                    break;
                
                case "Windows":
                    
                    $auxresponse = $this->synthesizeWindows($voice, $text, $md5);
                    // if there was an error
                    if ($auxresponse[0]) {
                        $error = true;
                        $errormessage = $auxresponse[1];
                        $errorcode = $auxresponse[2];
                    }
                    
                    break;

                default:
                    $error = true;
                    $errormessage = "Error. Your OS is not compatible with offline voices. "
                            . "Change your voices in your user configuration settings.";
                    $errorcode = 107;
                    break;
            }
        }
        
        if (!$error) {
            $filename = $md5.".".$extension;
            $CI->Audio_model->saveAudioFileToDatabase($text, $md5, $filename);
        }
        
        $output[0] = $filename;
        $output[1] = $error;
        $output[2] = $errormessage;
        $output[3] = $errorcode;
        
        return $output;
    }
    
    /**
     * THIS FUNCTION WILL NOT WORK WITHOUT A VALID VOCALWARE ACCOUNT
     * Requests and saves audio file from online voice service
     * @param type $vocalwareLID
     * @param type $vocalwareVID
     * @param type $text
     * @param type $filename (without extension)
     * @return array $output calling function should check for returned errors in $output[0],
     * errormessage in $output[1] errorcode in $output[2]
     */
    function synthesizeOnline($vocalwareLID, $vocalwareVID, $text, $filename)
    {
        $error = false;
        $errormessage = null;
        $errorcode = 0;
        $output = array();
        
        $curl = curl_init();


		// A Vocalware account is needed
        $url = "";
        $secret_phrase = "";

        // Vocalware API identification is required
        $data = array(
            'EID' => '2',
            'LID' => $vocalwareLID,
            'VID' => $vocalwareVID,
            'TXT' => $text,
            'EXT' => 'mp3',
            'ACC' => '', // required
            'API' => ''  // required            
        );

        $data['CS'] = md5($data['EID'].$data['LID'].$data['VID'].$data['TXT'].$data['EXT'].$data['ACC'].$data['API'].$secret_phrase);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);
                
        // if no error occurred (we assume there's an error if the mp3 data is less than 1000 characters)
        if ($result && !strpos($result, "Error: ") && (strlen($result) > 1000)) {

            try {
                $filenamewrite = "mp3/".$filename.".mp3";
                $fitxertxtwrite = fopen($filenamewrite,"w+b");

                if (flock($fitxertxtwrite, LOCK_EX)) {
                    fwrite($fitxertxtwrite, $result);
                    flock($fitxertxtwrite, LOCK_UN);
                    fclose($fitxertxtwrite);
                }
            } catch (Exception $ex) {
                $error = true;
                $errormessage = "Error. An error occurred while writing the audio file.";
                $errorcode = 108;
            }
        }
        // if there was an error
        else {
            $error = true;
            $errormessage = "Error. An error occurred while contacting the online voice service. Try again.";
            $errorcode = 109;
        }
        
        $output[0] = $error;
        $output[1] = $errormessage;
        $output[2] = $errorcode;
        return $output;
    }
    
    /**
     * Requests and saves audio file from online voice service
     * @param type $voice
     * @param type $text
     * @param type $filename (without extension)
     * @param type $rate rate of speech speed of offline Mac OS X voices
     * @return array $output calling function should check for returned errors in $output[0],
     * errormessage in $output[1] errorcode in $output[2]
     */
    function synthesizeMacOSX($voice, $text, $filename, $rate)
    {        
        $error = false;
        $errormessage = null;
        $errorcode = 0;
        $output = array();
        
        try {
            $concatveus = "";
        
            if ($rate > 0) $concatveus .= "-r ".$rate." ";

            $concatveus .= "-v '".$voice."' ";
            $concatveus .= "-o mp3/".$filename.".m4a --data-format=aach ";

            $cmd="say ".$concatveus.'"'.$text.'" > /dev/null 2>&1 &';
            shell_exec($cmd);

        } catch (Exception $ex) {
            $error = true;
            $errormessage = "Error. Unable to access your selected Mac OS X voice due to unkown circumstances. "
                    . "Try changing your voices in your user configuration settings. "
                    . "Otherwise, your OS X may not be compatible with the 'say' command.";
            $errorcode = 110;
        }
        
        $output[0] = $error;
        $output[1] = $errormessage;
        $output[2] = $errorcode;
        return $output;
    }
    
    /**
     * Requests and saves audio file from online voice service
     * @param type $voice
     * @param type $text
     * @param type $filename (without extension)
     * @return array $output calling function should check for returned errors in $output[0],
     * errormessage in $output[1] errorcode in $output[2]
     */
    function synthesizeWindows($voice, $text, $filename)
    {
        $error = false;
        $errormessage = null;
        $errorcode = 0;
        $output = array();
        
        $chosenVoice = null;
        $msVoice = null;
        $isSAPIVoice = false; 
        
        // error de Microsoft Speech Platform
        $errorMSP = false;
        $errorMSPtmp = null;

        try {
            // Recollim els objectes de les llibreries Speech de Microsoft que necessitem
            $msVoice = new COM('Speech.SpVoice');

            $numvoices = $msVoice->GetVoices()->Count;

            // per cada veu miram si la descripció coincideix amb el nom de la veu
            // seleccionada per l'usuari
            for ($i=0; $i<$numvoices; $i++) {
                if ($voice == $msVoice->GetVoices()->Item($i)->GetDescription) {
                    $chosenVoice = $msVoice->GetVoices()->Item($i);
                    $isSAPIVoice = false;
                }
            }
            
        } catch (Exception $ex) {
            $errorMSP = true;
            $errorMSPtmp = "Error. Unable to access Microsoft Speech Platform.";
        }

        // error de SAPI
        $errorSAPI = false;
        $errorSAPItmp = null;

        try {
            // Recollim els objectes de les llibreries SAPI que necessitem
            $msVoice = new COM('SAPI.SpVoice');

            $numvoicesSAPI = $msVoice->GetVoices()->Count;

            // per cada veu miram si la descripció coincideix amb el nom de la veu
            // seleccionada per l'usuari
            for ($i=0; $i<$numvoicesSAPI; $i++) {
                if ($voice == $msVoice->GetVoices()->Item($i)->GetDescription) {
                    $chosenVoice = $msVoice->GetVoices()->Item($i);
                    $isSAPIVoice = true;
                }
            }
            
        } catch (Exception $ex) {
            $errorSAPI = true;
            $errorSAPItmp = "Error. Unable to access SAPI voices.";
        }

        if ($errorMSP && $errorSAPI) {
            $error = true;
            $errormessage = "Error. Unable to access your selected Windows voice due to unkown circumstances. "
                    . "Try changing your voices in your user configuration settings. "
                    . "Otherwise, your Windows may not be compatible with MSP or SAPI.";
            $errorcode = 111;
        }
        // si no hi ha hagut cap error, procedim a generar l'audio
        else {

            try {

                $msFileStream = null;
                $msAudioFormat = null;
                
                if (!$isSAPIVoice) {
                    $msFileStream = new COM('Speech.SpFileStream');
                    $msAudioFormat = new COM('Speech.SpAudioFormat');
                }
                else {
                    $msFileStream = new COM('SAPI.SpFileStream');
                    $msAudioFormat = new COM('SAPI.SpAudioFormat');
                }

                // Path al fitxer on guardarem les veus
                $wavfile = "C:\\xampp\htdocs\mp3\\".$filename.".mp3";
                
                // hem de triar la veu que vol l'usuari (trobada anteriorment)
                $msVoice->Voice = $chosenVoice;

                // passem la frase d'utf-8 a format de Windows perquè llegeixi bé
                // tots els caràcters
                $fraseconvertida = iconv("utf-8", "Windows-1252", $text);

                // guardarem el fitxer amb la menor qualitat possible, format 4
                $msAudioFormat->Type = 4;
                $msFileStream->Format = $msAudioFormat;

                // obrim el fitxer on escriurem l'àudio en format CreateWrite
                $msFileStream->Open($wavfile, 3, 0);
                $msVoice->AudioOutputStream = $msFileStream;
                            
                // es diu la frase de manera asíncrona
                $msVoice->Speak($fraseconvertida, 1);
                // esperem a que acabi, ja que si no talla la frase
                $msVoice->WaitUntilDone(-1);

                // tanquem el fitxer i alliberem la memòria dels objectes
                $msFileStream->Close();
                $msAudioFormat = null;
                $msFileStream = null;
                $msVoice = null;

            } catch (Exception $e) {
                $error = true;
                $errormessage = "Error. An error occurred while writing your Windows audio file.";
                $errorcode = 112;
            }
        }
        
        $output[0] = $error;
        $output[1] = $errormessage;
        $output[2] = $errorcode;
        return $output;
    }
    
    /**
     * 
     * It generates the audio for the dropdown voices' menus in the user configuration of the app 
     * and saves it to the database and into an audio file
     * @param string $idusu user id
     * @param string $text string to synthesize
     * @param string $voice voice name for offline voices or id for online voices (except 
     * for DEFAULT online interface voices)
     * @param string $type online or offline
     * @param int $language id of the language of the string to synthetize
     * @param type $rate rate of speech speed of offline voices
     * @return array $output[0] Name of the generated audio file with the extension
     * NOTE: calling function should check for returned errors in $output[1],
     * errormessage in $output[2] errorcode in $output[3]
     */
    public function selectedVoiceAudio($idusu, $text, $voice, $type, $language, $rate) 
    {
        $CI = &get_instance();
        $CI->load->model('Audio_model');
        
        $output = array();
        $output[1] = false; // error
        $output[2] = null; // error message
        $output[3] = 0; // error code
        
        // the name of the fetched (from the database) or the generated audio file
        // it will be in output[0]
        $filename = null;
        $key = "";
        
        // Encoded file name: #INTERFACE@IdInterfaceLanguage#(masc|fem)$string
        if ($type == "online") $key = "#INTERFACEVOICE@".$language."#".$voice."(".$type.")"."$".$text;
        else $key = "#INTERFACEVOICE_USU".$idusu."@".$language."#".$voice."(".$type.")"."$".$text;
        
        $md5 = md5($key);

        $isindb = $CI->Audio_model->isAudioInDatabase($md5);

        // if it's already in the database
        if ($isindb) $filename = $isindb;
        else {
            $auxresponse = $this->synthesizeAudio($md5, $text, $voice, $type, $language, $rate);
            $filename = $auxresponse[0];
            $output[1] = $auxresponse[1];
            $output[2] = $auxresponse[2];
            $output[3] = $auxresponse[3];
        }
        
        $output[0] = $filename;
        
        return $output;
    }
    
    /**
     * If there is no error, waits for the file to be available and frees it 
     * @param type $file
     * @param type $error
     */
    public function waitForFile($file, $error)
    {
        if (!$error) {
            $handle = fopen("mp3/".$file, "r");
            if (is_resource($handle)) {
                fclose($handle);
            }
            else {
                $i = 0;
                while (!is_resource($handle) && $i<10) {
                    $i++;
                    $handle = fopen("mp3/".$file, "r");
                    usleep(100000);
                }
                fclose($handle);
            }
        }
    }
    
}

/* End of file Myaudio.php */