<?php 
use \Firebase\JWT\JWT;

class Login_model extends CI_Model {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    public function Login($user, $pass)
    {
        $output = array();
        $this->db->join('User', 'SuperUser.ID_SU = User.ID_USU', 'left');
        $this->db->where('SUname', $user);
        $this->db->where('pswd', md5($pass));
        $query = $this->db->get('SuperUser');
        
        if ($query->num_rows() == 0) {
            return false;
        }
            
        $output = $query->result();
        $tokenId    = base64_encode(mcrypt_create_iv(32));
        $issuedAt   = time();
        $notBefore  = $issuedAt;                            // Is valid right away
        $expire     = $notBefore + (60 * 60  * 24 * 365 * 50);     // Token expires in 5 years
        $serverName = 'myserver'; // Retrieve the server name from config file
        
        /*
         * Create the token as an array
         */
        $data = [
            'iat'  => $issuedAt,         // Issued at: time when the token was generated
            'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
            'iss'  => $serverName,       // Issuer
            'nbf'  => $notBefore,        // Not before
            'exp'  => $expire,           // Expire
            'data' => [                  // Data related to the signer user
                'userId'   => $output[0]->ID_SU, // userid from the users table
                'userName' => $output[0]->SUname // User name
            ]
        ];

        $secretKey = base64_decode('lamevaclausupersecreta');
        $jwt = JWT::encode(
            $data,      //Data to be encoded in the JWT
            $secretKey, // The signing key
            'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
        );

        // Get user data and user config data
        $this->db->from('SuperUser');
        $this->db->join('User', 'SuperUser.cfgDefUser = User.ID_User');
        $this->db->join('Languages', 'SuperUser.cfgDefUser = User.ID_User AND User.ID_ULanguage = Languages.ID_Language', 'right');
        $this->db->where('SUname', $user);
        $query2 = $this->db->get()->result_array();
        $userConfig = $query2[0];

        // Save user config data in the COOKIES
        $this->session->set_userdata('idsu', $userConfig["ID_SU"]);
        $this->session->set_userdata('idusu', $userConfig["ID_User"]);
        $this->session->set_userdata('uname', $userConfig["SUname"]);
        $this->session->set_userdata('ulanguage', $userConfig["cfgExpansionLanguage"]);
        //MODIF: Cuando lo juntemos con jose dará fallo. Jose tiene que cambiar "uinterfacelangauge" por este
        $this->session->set_userdata('uinterfacelangauge', $userConfig["ID_ULanguage"]);
        $this->session->set_userdata('uinterfacelangtype', $userConfig["type"]);
        $this->session->set_userdata('uinterfacelangnadjorder', $userConfig["nounAdjOrder"]);
        $this->session->set_userdata('uinterfacelangncorder', $userConfig["nounComplementOrder"]);
        $this->session->set_userdata('uinterfacelangabbr', $userConfig["languageabbr"]);
        $this->session->set_userdata('cfgAutoEraseSentenceBar', $userConfig["cfgAutoEraseSentenceBar"]);
        $this->session->set_userdata('isfem', $userConfig["cfgIsFem"]);
        $this->session->set_userdata('cfgExpansionOnOff', $userConfig["cfgExpansionOnOff"]);
        $this->session->set_userdata('cfgPredBarNumPred', $userConfig["cfgPredBarNumPred"]);

        // Save Expansion language in the COOKIES
        $this->db->select('canExpand');
        $this->db->where('ID_Language', $userConfig["cfgExpansionLanguage"]);
        $query3 = $this->db->get('Languages');

        if ($query3->num_rows() > 0) {
            $aux = $query3->result();
            $canExpand = $aux[0]->canExpand;

            if ($canExpand == '1'){
                $this->session->set_userdata('ulangabbr', $userConfig["languageabbr"]);
                $this->session->set_userdata('explangcannotexpand', '0');
            }else{
                $this->session->set_userdata('ulangabbr', 'ES');
                $this->session->set_userdata('explangcannotexpand', '1');
            }
        }

        // Guardamos los datos como objeto
        $unencodedArray = [
            'token' => $jwt,
            'userConfig' => $userConfig
        ];

        return $unencodedArray;
    }

}
