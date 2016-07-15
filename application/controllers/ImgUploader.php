<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class ImgUploader extends REST_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('ImgUploader_model');
    }

    //MODIF: mirar que hacer aqui...
    public function index_get() {
        // CHECK COOKIES
        if (!$this->session->userdata('uname')) {
            redirect(base_url(), 'location');
        } else {
            if (!$this->session->userdata('cfguser')) {
                $this->BoardInterface->loadCFG($this->session->userdata('uname'));
                $this->load->view('MainBoard', true);
            } else {
                $this->load->view('MainBoard', true);
            }
        }
    }

    public function upload_post() {
        //"vocabulary" is a string.....
        if (filter_input(INPUT_POST, 'vocabulary') == "true") {
            $target_dir = "img/pictos/";
        } else {
            $target_dir = "img/users/";
        }
        $errorText = array();
        $error = false;
        for ($i = 0; $i < count($_FILES); $i++) {
            $md5Name = $this->Rename_Img(basename($_FILES['file' . $i]['name']));
            if (!($_FILES['file' . $i]['type'] == "image/gif" || $_FILES['file' . $i]['type'] == "image/jpeg" || $_FILES['file' . $i]['type'] == "image/png")) {
                $errorProv = ["errorImg1", $_FILES['file' . $i]['name']];
                array_push($errorText, $errorProv);
                $error = true;
                continue;
            }
            $handle = fopen($target_dir . $md5Name, "r");
            if (is_resource($handle)) {
                fclose($handle);
                //MODIF: lanzar error 
                $errorProv = ["errorImg2", $_FILES['file' . $i]['name']];
                array_push($errorText, $errorProv);
                $error = true;
                continue;
            }
            //MODIF: poner tamaño a 100 kb y tamaño 150 minimo
            if ($_FILES['file' . $i]['size'] > 100000) {
                $success = $this->Img_Resize($_FILES['file' . $i]['tmp_name'], $target_dir, $md5Name);
            } else {
                $success = move_uploaded_file($_FILES['file' . $i]['tmp_name'], $target_dir . $md5Name);
            }
            if ($success) {
                $idusu = $this->session->userdata('idsu');
                $this->ImgUploader_model->insertImg($idusu, basename($_FILES['file' . $i]['name']), $md5Name);
            } else {
                $errorProv = ["errorImg2", $_FILES['file' . $i]['name']];
                array_push($errorText, $errorProv);
                $error = true;
                continue;
            }
        }

        $response = [
            'url' => $target_dir . $md5Name,
            'errorText' => $errorText,
            'error' => $error
        ];

        $this->response($response, REST_Controller::HTTP_OK);
    }

    function Rename_Img($string) {

        $idusu = $this->session->userdata('idsu');
        $fecha = microtime();
        //MODIF: Pasar superuser no user
        $stringlen = strlen($string);
        $pointpos = strrpos($string, '.');

        $ext = substr($string, $pointpos, $stringlen);
        $name = "idu" . $idusu . "-" . $string . "-" . $fecha;
        $name = md5($name . $idusu);
        $md5Name = $name . $ext;
        return $md5Name;
    }

    function Img_Resize($src_path, $target_dir, $dst_path) {
        $success = true;

        $x = getimagesize($src_path);

        $width = $x['0'];
        $height = $x['1'];
        $type = $x['mime'];

        $rs_width = $width / 2; //resize to half of the original width.
        $rs_height = $height / 2; //resize to half of the original height.
        // The grater value between height and width have to be, at least, 150
        if ($rs_height < 150 || $rs_width < 150) {
            if ($rs_height > $rs_width && $rs_height < 150) {
                $ratio = 150 / $rs_height;
            } else if ($rs_height < $rs_width && $rs_width < 150) {
                $ratio = 150 / $rs_width;
            } else {
                $ratio = 1;
            }
            $rs_height = $rs_height * $ratio;
            $rs_width = $rs_width * $ratio;
        }

        switch ($type) {
            case "image/gif":
                $img = imagecreatefromgif($src_path);
                break;
            case "image/jpeg": // jpeg and jpg
                $img = imagecreatefromjpeg($src_path);
                break;
            case "image/png":
                $img = imagecreatefrompng($src_path);
                break;
        }
        // Create an empty img
        $img_base = imagecreatetruecolor($rs_width, $rs_height);
        // Set the alpha transparency if needed
        switch ($type) {
            case "image/png":
                // integer representation of the color black (rgb: 0,0,0)
                $background = imagecolorallocate($img_base, 0, 0, 0);
                // removing the black from the placeholder
                imagecolortransparent($img_base, $background);

                // turning off alpha blending (to ensure alpha channel information 
                // is preserved, rather than removed (blending with the rest of the 
                // image in the form of black))
                imagealphablending($img_base, false);

                // turning on alpha channel information saving (to ensure the full range 
                // of transparency is preserved)
                imagesavealpha($img_base, true);

                break;
            case "image/gif":
                // integer representation of the color black (rgb: 0,0,0)
                $background = imagecolorallocate($img_base, 0, 0, 0);
                // removing the black from the placeholder
                imagecolortransparent($img_base, $background);

                break;
        }
        //Copy the img
        $success = $success && imagecopyresampled($img_base, $img, 0, 0, 0, 0, $rs_width, $rs_height, $width, $height);
        // Create the image with the correct extension
        switch ($type) {
            case "image/gif":
                $success = $success && imagegif($img_base, $target_dir . $dst_path);
                break;
            case "image/jpeg":
                $success = $success && imagejpeg($img_base, $target_dir . $dst_path);
                break;
            case "image/png":
                $success = $success && imagepng($img_base, $target_dir . $dst_path);
                break;
        }
        // If we have to resize the img again
        // MODIF: Se puede quedar en bucle?? yo diria que no pero puede ser mirar que se puede hacer.
        if (filesize($target_dir . $dst_path) > 100000) {
            // The new source img will be the last output img
            $newsrc_path = $target_dir . $dst_path;
            // And the new output will be r(esized) + name
            $newdst_path = "r" . $dst_path;
            $success = $success && $this->Img_Resize($newsrc_path, $target_dir, $newdst_path);
            // Remove the last output
            unlink($newsrc_path);
            // Rename de new output
            rename($target_dir . $newdst_path, $newsrc_path);
        }
        return $success;
    }

    function getImagesUploads_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $name = $request->name;

        $idusu = $this->session->userdata('idsu');
        $data = $this->ImgUploader_model->getImages($idusu, $name);

        $response = [
            'data' => $data
        ];

        $this->response($response, REST_Controller::HTTP_OK);
    }

    function getImagesArasaac_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $name = $request->name;

        $idusu = $this->session->userdata('idusu');
        $languageInt = $this->session->userdata('uinterfacelangauge');
        $data = $this->ImgUploader_model->getImagesArasaac($idusu, $name, $languageInt);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["imgPath"] = "img/pictos/" . $data[$i]["imgPath"];
        }

        $response = [
            'data' => $data
        ];

        $this->response($response, REST_Controller::HTTP_OK);
    }

}
