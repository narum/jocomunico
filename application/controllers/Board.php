<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Board extends REST_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('BoardInterface');
        $this->load->model('Lexicon');
        $this->load->library('Myword');
        $this->load->library('Myslot');
        $this->load->library('Mypattern');
        $this->load->library('Myexpander');
        $this->load->library('Myprediction');
        $this->load->library('Myaudio');
        $this->load->library('session');
    }

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

    public function loadCFG_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $luid = $request->lusuid;
        //MODIF: mirar que id de lenguage es 
        $data = array(
            'uinterfacelangauge' => $luid // Id language
        );
    }

    /*
     * Get the cell's info
     */

    public function getCell_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $pos = $request->pos;
        $idboard = $request->idboard;

        $info = $this->BoardInterface->getCell($pos, $idboard);


        $response = [
            'info' => $info[0]
        ];

        $this->response($response, REST_Controller::HTTP_OK);
    }

    /*
     * Get the cells of the boards that will be displayed and the 
     * number of rows and columns in order to set the proportion
     */

    public function getCellboard_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $idboard = $request->idboard;

        $output = $this->BoardInterface->getBoardStruct($idboard);
        $columns = $output[0]->width;
        $rows = $output[0]->height;
        $name = $output[0]->Bname;
        $primaryBoard = $output[0]->primaryBoard;
        $autoReturn = $output[0]->autoReturn;
        $autoRead = $output[0]->autoReadSentence;


        $response = [
            'col' => $columns,
            'row' => $rows,
            'name' => $name,
            'primaryBoard' => $primaryBoard,
            'autoReturn' => $autoReturn,
            'autoRead' => $autoRead
        ];

        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function showCellboard_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $idboard = $request->idboard;

        $array = array();

        $output = $this->BoardInterface->getBoardStruct($idboard);
        if ($output != null) {
            $columns = $output[0]->width;
            $rows = $output[0]->height;
            $autoRead = $output[0]->autoRead;

            $array = $this->BoardInterface->getCellsBoard($idboard);


            $response = [
                'col' => $columns,
                'row' => $rows,
                'data' => $array,
                'autoRead' => $autoRead
            ];

            $this->response($response, REST_Controller::HTTP_OK);
        }else{
            $response = [
                'data' => null
            ];

            $this->response($response, REST_Controller::HTTP_OK);
        }
    }

    public function modifyNameboard_post() {
        $this->BoardInterface->initTrans();

        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $Name = $request->Name;
        $IDnumboard = $request->ID;

        $this->BoardInterface->updateName($Name, $IDnumboard);
        $this->BoardInterface->commitTrans();
    }

    /*
     * Estos van en otro controlador que seria el de edicion, pero aun no estan hechos
     */
    /*
     * Returns de cells of the boards that will be displayed and the 
     * number of rows and columns in order to set the proportion
     * Modify the number of rows and columns and add or remove cells.
     */

    public function modifyCellboard_post() {
        $this->BoardInterface->initTrans();

        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $c = $request->c;
        $r = $request->r;
        $idboard = $request->idboard;

        $output = $this->BoardInterface->getBoardStruct($idboard);
        $this->BoardInterface->updateNumCR($c, $r, $idboard);
        $columnsDiff = $c - $output[0]->width;
        $rowsDiff = $r - $output[0]->height;




        if ($columnsDiff > 0) {
            $this->addColumns($output[0]->width, $output[0]->height, $idboard, $columnsDiff);
        } elseif ($columnsDiff < 0) {
            $this->removeColumns($output[0]->width, $output[0]->height, $idboard, -$columnsDiff);
        } elseif ($rowsDiff > 0) {
            $this->addRows($output[0]->width, $output[0]->height, $idboard, $rowsDiff);
        } elseif ($rowsDiff < 0) {
            $this->removeRows($output[0]->width, $output[0]->height, $idboard, -$rowsDiff);
        }

        $this->BoardInterface->commitTrans();
    }

    /*
     * Add one or more columns to the board. Each cell keeps his physical position
     * currentPos: Cell position in the new "array"
     * oldCurrentPos: Cell position in the old "array"
     * For each row: We create one cell for each column to add
     *             : We move up the other cells in that row
     * We go backwards through the array
     */

    public function addColumns($columns, $rows, $idBoard, $columnsToAdd) {
        $currentPos = ($columns + $columnsToAdd) * $rows;
        $oldCurrentPos = $columns * $rows;
        for ($row = 0; $row < $rows; $row++) {
            for ($i = $columns; $i < $columns + $columnsToAdd; $i++) {
                $this->BoardInterface->newCell($currentPos, $idBoard);
                $currentPos--;
            }
            for ($column = 0; $column < $columns; $column++) {
                $this->BoardInterface->updatePosCell($oldCurrentPos, $currentPos, $idBoard);
                $currentPos--;
                $oldCurrentPos--;
            }
        }
    }

    /*
     * Remove one or more columns in the board. Each cell keeps his physical position
     * The same than adding columns. We move down and remove instead.
     */

    public function removeColumns($columns, $rows, $idBoard, $columnsToSub) {
        $currentPos = 1;
        $oldCurrentPos = 1;
        //We can add a start trans and commit at the end?
        for ($row = 0; $row < $rows; $row++) {
            for ($column = 0; $column < $columns - $columnsToSub; $column++) {
                $this->BoardInterface->updatePosCell($oldCurrentPos, $currentPos, $idBoard);
                $oldCurrentPos++;
                $currentPos++;
            }
            for ($i = $columns - $columnsToSub; $i < $columns; $i++) {
                $cell = $this->BoardInterface->getIDCell($oldCurrentPos, $idBoard);
                $this->BoardInterface->removeCell($cell[0]->ID_RCell, $idBoard);
                $oldCurrentPos++;
            }
        }
    }

    /*
     * Add one or more rows to the board. Each cell keeps his physical position
     * currentPos: The last position + 1 (the position where the cell will be added)
     * For each row we add one cell for each column the board has
     */

    public function addRows($columns, $rows, $idBoard, $rowsToAdd) {
        $currentPos = $columns * $rows + 1;
        for ($row = 0; $row < $rowsToAdd; $row++) {
            for ($column = 0; $column < $columns; $column++) {
                $this->BoardInterface->newCell($currentPos, $idBoard);
                $currentPos++;
            }
        }
    }

    /*
     * Remove one or more rows in the board. Each cell keeps his physical position
     * The same than adding rows. We remove instead.
     */

    public function removeRows($columns, $rows, $idBoard, $rowsToSub) {
        $currentPos = $columns * $rows;
        for ($row = 0; $row < $rowsToSub; $row++) {
            for ($column = 0; $column < $columns; $column++) {
                $cell = $this->BoardInterface->getIDCell($currentPos, $idBoard);
                $this->BoardInterface->removeCell($cell[0]->ID_RCell, $idBoard);
                $currentPos--;
            }
        }
    }

    /*
     * Add the clicked word (pictogram) in the S_Temp database table.
     * Then, get the entire sentence from this table.
     */

    public function addWord_post() {
        //To get the parameters
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;
        $imgtemp = $request->imgtemp;

        $idusu = $this->session->userdata('idusu');
        $this->Lexicon->afegirParaula($idusu, $id, $imgtemp);

        $data = $this->Lexicon->recuperarFrase($idusu);
        $newdata = $this->inserty($data);

        $response = [
            'data' => $newdata
        ];

        $this->response($response, REST_Controller::HTTP_OK);
    }

    /*
     * Get the sentence
     */

    public function getTempSentence_post() {
        $idusu = $this->session->userdata('idusu');

        $data = $this->Lexicon->recuperarFrase($idusu);

        $newdata = $this->inserty($data);

        $response = [
            'data' => $newdata
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    /*
     * Insert a especial picto (concatenation picto) in the array.
     */

    public function inserty($data) {
        $newdata = array();
        $j = 0;
        for ($i = 0; $i < count($data); $i++) {
            $newdata[$j] = $data[$i];
            if ($data[$i]->coord) {
                $j++;
                $newdata[$j] = (object) array('imgtemp' => "/img/pictosespeciales/y.png");
            }
            $j++;
        }
        return $newdata;
    }

    /*
     * Remove the last word (pictogram) added in the S_Temp database table.
     * Then, get the entire sentence from this table.
     */

    public function deleteLastWord_post() {

        $idusu = $this->session->userdata('idusu');
        $id = $this->BoardInterface->getLastWord($idusu);

        $this->Lexicon->eliminarParaula($id->ID_RSTPSentencePicto);

        $data = $this->Lexicon->recuperarFrase($idusu);

        $newdata = $this->inserty($data);

        $response = [
            'data' => $newdata
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    /*
     * Remove the entire phrase (pictograms) in the S_Temp database table.
     */

    public function deleteAllWords_post() {

        $idusu = $this->session->userdata('idusu');
        $this->BoardInterface->removeSentence($idusu);

        $data = $this->Lexicon->recuperarFrase($idusu);

        $newdata = $this->inserty($data);

        $response = [
            'data' => $newdata
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    /*
     * Copy the S_Temp table to the S_Historic table and all this dependecies. 
     * Also remove the entire phrase (pictograms) in the S_Temp database table.
     */

    public function generate_post() {

        $this->BoardInterface->initTrans();

        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $tense = $request->tense;
        $tipusfrase = $request->tipusfrase;
        $negativa = $request->negativa;
        $idusu = $this->session->userdata('idusu');
        $this->Lexicon->insertarFrase($idusu, $tipusfrase, $tense, $negativa);


        $this->BoardInterface->commitTrans();

        if ($this->BoardInterface->statusTrans() === FALSE) {
            $response = [
                'error' => "errorText"
            ];
            $this->response($response, 500);
        } else {
            $expander = new Myexpander();
            $expander->expand();
            
            $info = $expander->info;
            $idErrorcode = $info[errorcode];
            $errorText = $this->BoardInterface->get_errorText($idErrorcode);
            
            $response = [
                'info' => $info,
                'errorText' => $errorText[0][content]
            ];

            $this->response($response, REST_Controller::HTTP_OK);
        }
    }

    /*
     * Get the functions in a list to create the dropdown menu
     */

    public function getFunctions_post() {

        $functions = $this->BoardInterface->getFunctions();

        $response = [
            'functions' => $functions
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    /*
     * Get the primary user board (the primary board in her/his primary group board)
     */

    public function getPrimaryUserBoard_post() {

        $board = $this->BoardInterface->getPrimaryGroupBoard();
        $primaryBoard = $this->BoardInterface->getPrimaryBoard($board[0]->ID_GB);

        $response = [
            'idboard' => $primaryBoard[0]->ID_Board
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    /*
     * Get the user boards in a list to create the dropdown menu
     */

    public function getBoards_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $idboard = $request->idboard;

        $board = $this->BoardInterface->getIDGroupBoards($idboard);
        $boards = $this->BoardInterface->getBoards($board[0]->ID_GBBoard);
        $primaryBoard = $this->BoardInterface->getPrimaryBoard($board[0]->ID_GBBoard);
        $nameGBoard = $this->BoardInterface->getInfoGroupBoard($board[0]->ID_GBBoard);

        $response = [
            'boards' => $boards,
            'primaryBoard' => $primaryBoard[0],
            'name' => $nameGBoard[0]->GBname
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    /*
     * Get the function
     */

    public function getFunction_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;
        $tense = $request->tense;
        $tipusfrase = $request->tipusfrase;
        $negativa = $request->negativa;

        $control = "";
        $function = $this->BoardInterface->getFunction($id);
        $value = $function[0]->functValue;
        $type = $function[0]->functType;

        switch ($type) {
            case "modif":
                $idusu = $this->session->userdata('idusu');
                $this->Lexicon->afegirModifNom($idusu, $value);
                break;
            case "tense":
                $tense = $value;
                break;
            case "tipusfrase":
                $tipusfrase = $value;
                break;
            case "negativa":
                $negativa = $value;
                break;
            case "control":
                $control = $value;
                break;
        }
        $idusu = $this->session->userdata('idusu');
        $data = $this->Lexicon->recuperarFrase($idusu);

        $newdata = $this->inserty($data);

        $response = [
            'tense' => $tense,
            'tipusfrase' => $tipusfrase,
            'negativa' => $negativa,
            'control' => $control,
            'data' => $newdata
        ];

        $this->response($response, REST_Controller::HTTP_OK);
    }

    /*
     * Add the selected pictogram to the board 
     */

    public function addPicto_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;
        $pos = $request->pos;
        $idboard = $request->idboard;

        $cell = $this->BoardInterface->getIDCell($pos, $idboard);
        $this->BoardInterface->updateDataCell($id, $cell[0]->ID_RCell);

        $data = $this->BoardInterface->getCellsBoard($idboard);
        $idusu = $this->session->userdata('idusu');
        $this->Lexicon->addWordStatsX1($id, $idusu, true);
        $response = [
            'data' => $data
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    /*
     * Swap the two selected pictograms in the board
     */

    public function swapPicto_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $pos1 = $request->pos1;
        $pos2 = $request->pos2;
        $idboard = $request->idboard;

        $this->BoardInterface->updatePosCell($pos1, -1, $idboard);
        $this->BoardInterface->updatePosCell($pos2, $pos1, $idboard);
        $this->BoardInterface->updatePosCell(-1, $pos2, $idboard);

        $data = $this->BoardInterface->getCellsBoard($idboard);

        $response = [
            'data' => $data
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    /*
     * Remove the selected pictogram to the board
     */

    public function removePicto_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $pos = $request->pos;
        $idboard = $request->idboard;
        //$boardid = $request->boardid;
        $cell = $this->BoardInterface->getIDCell($pos, $idboard);
        $this->BoardInterface->removeDataCell($cell[0]->ID_RCell);

        $data = $this->BoardInterface->getCellsBoard($idboard);

        $response = [
            'data' => $data
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    /*
     * Get all prerecorded user sentences 
     */

    public function searchSentence_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $search = $request->search;

        $idusu = $this->session->userdata('idusu');
        $sentence = $this->BoardInterface->getSentences($idusu, $search);

        $response = [
            'sentence' => $sentence
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function getSentence_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;

        $sentence = $this->BoardInterface->getSentence($id);

        $response = [
            'sentence' => $sentence
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    /*
     * 
     */

    public function searchSFolder_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $search = $request->search;

        $idusu = $this->session->userdata('idusu');
        $sFolder = $this->BoardInterface->getSFolders($idusu, $search);

        $response = [
            'sfolder' => $sFolder
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function getSFolder_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;

        $sFolder = $this->BoardInterface->getSFolder($id);

        $response = [
            'sFolder' => $sFolder
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function editCell_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;
        $boardLink = $request->boardLink;
        $idFunct = $request->idFunct;
        $textInCell = $request->textInCell;
        $visible = $request->visible;
        $isFixed = $request->isFixed;
        $idPicto = $request->idPicto;
        $idSentence = $request->idSentence;
        $idSFolder = $request->idSFolder;
        $numScanBlockText1 = $request->numScanBlockText1;
        $textInScanBlockText1 = $request->textInScanBlockText1;
        $numScanBlockText2 = $request->numScanBlockText2;
        $textInScanBlockText2 = $request->textInScanBlockText2;
        $cellType = $request->cellType;
        $color = $request->color;
        $imgCell = $request->imgCell;

        $this->BoardInterface->updateMetaCell($id, $visible, $textInCell, $isFixed, $idFunct, $boardLink, $idPicto, $idSentence, $idSFolder, $cellType, $color, $imgCell);
        $this->BoardInterface->updateScanCell($id, $numScanBlockText1, $textInScanBlockText1, $numScanBlockText2, $textInScanBlockText2);
    }

    public function changeImgCell_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->idboard;
        $posInBoard = $request->pos;
        $imgCell = $request->imgCell;
        $idusu = $this->session->userdata('idusu');

        $cell = $this->BoardInterface->getIDCell($posInBoard, $id);
        $idPicto = $this->BoardInterface->updateImgCell($cell[0]->ID_RCell, $imgCell);
        $data = $this->BoardInterface->getCellsBoard($id);
        $this->Lexicon->addImgTempStatsX1($idPicto, $idusu, $imgCell);
        $response = [
            'data' => $data
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function changePrimaryBoard_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;
        $idBoard = $request->idBoard;

        $this->BoardInterface->changePrimaryBoard($id, $idBoard);
    }

    public function changeAutoReturn_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $value = ($request->value == true ? '1' : '0');
        $id = $request->id;


        $this->BoardInterface->changeAutoReturn($id, $value);
    }

    public function changeAutoRead_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $value = ($request->value == true ? '1' : '0');
        $id = $request->id;


        $this->BoardInterface->changeAutoReadSentence($id, $value);
    }

    public function autoReturn_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;


        $board = $this->BoardInterface->getBoardStruct($id);

        $idPrimaryBoard = null;

        if ($board[0]->autoReturn === "1") {
            $primaryBoard = $this->BoardInterface->getPrimaryBoard($board[0]->ID_GBBoard);
            $idPrimaryBoard = $primaryBoard[0]->ID_Board;
        }

        $response = [
            'idPrimaryBoard' => $idPrimaryBoard
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function autoReadSentence_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;


        $board = $this->BoardInterface->getBoardStruct($id);

        $response = [
            'read' => $board[0]->autoReadSentence
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function newBoard_post() {
        $this->BoardInterface->initTrans();
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $IDGboard = $request->idGroupBoard;
        $name = $request->CreateBoardName;
        $width = $request->width;
        $height = $request->height;

        $idBoard = $this->BoardInterface->createBoard($IDGboard, $name, $width, $height);
        $this->addColumns(0, 0, $idBoard, $width);
        $this->addRows($width, 0, $idBoard, $height);
        $this->BoardInterface->commitTrans();
        $response = [
            'idBoard' => $idBoard
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function removeBoard_post() {
        $this->BoardInterface->initTrans();
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;

        $board = $this->BoardInterface->getIDGroupBoards($id);
        $primaryboard = $this->BoardInterface->getPrimaryBoard($board[0]->ID_GBBoard);
        $boards = $this->BoardInterface->getBoards($board[0]->ID_GBBoard);
        $primaryboardID = $primaryboard[0]->ID_Board;
        if ($id == $primaryboardID) {
            for ($x = 0; $boards[$x] != NULL; $x++) {
                $cell = $this->BoardInterface->getCellsBoard($boards[$x]->ID_Board);
                for ($i = 0; $i < count($cell); $i++) {
                    $this->BoardInterface->removeCell($cell[$i]->ID_RCell, $boards[$x]->ID_Board);
                }
                $this->BoardInterface->removeBoardLinks($boards[$x]->ID_Board);

                $this->BoardInterface->removeBoard($boards[$x]->ID_Board);
            }
            $this->BoardInterface->removeGoupBoard($board[0]->ID_GBBoard);
            $this->BoardInterface->commitTrans();
            $response = [
                'idboard' => null
            ];
            $this->response($response, REST_Controller::HTTP_OK);
        } else {

            $cell = $this->BoardInterface->getCellsBoard($id);
            for ($i = 0; $i < count($cell); $i++) {
                $this->BoardInterface->removeCell($cell[$i]->ID_RCell, $id);
            }
            $this->BoardInterface->removeBoardLinks($id);

            $this->BoardInterface->removeBoard($id);
            $this->BoardInterface->commitTrans();

            $response = [
                'idboard' => $primaryboard[0]->ID_Board
            ];
            $this->response($response, REST_Controller::HTTP_OK);
        }
    }

    public function copyBoard_post() {
        $this->BoardInterface->initTrans();
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $idSrc = $request->id;
        $srcGroupBoard = $request->srcGroupBoard;
        $IDGboard = $request->idGroupBoard;
        $sameGroupBoard = 0;
        if ($srcGroupBoard == $IDGboard) {
            $sameGroupBoard = 1;
        }
        $name = $request->CreateBoardName;
        $width = $request->width;
        $height = $request->height;
        $autoReturn = $request->autoreturn ? '1' : '0';
        $autoReadSentence = $request->autoread ? '1' : '0';

        $idDst = $this->BoardInterface->copyBoard($IDGboard, $name, $width, $height, $autoReturn, $autoReadSentence);
        $boardtables = $this->BoardInterface->getBoardTables($idSrc);
        foreach ($boardtables as $row) {
            $boardtables = $this->BoardInterface->copyBoardTables($idDst, $sameGroupBoard, $row);
        }
        /*
         * This commented part can update the size of the board if it is implemented.
         * 
          $this->addColumns(0, 0, $idBoard, $NEW_width);
          $this->addRows($width, 0, $idBoard, $NEW_height);
         */

        $this->BoardInterface->commitTrans();
        $response = [
            'idBoard' => $idDst
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function getIDGroupBoards_post() {
        $this->BoardInterface->initTrans();
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;

        $idBoard = $this->BoardInterface->getIDGroupBoards($id);
        $response = [
            'idGroupBoard' => $idBoard[0]->ID_GBBoard
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function getMaxScanBlock1_post() {
        $this->BoardInterface->initTrans();
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->idboard;
        $type = $request->type;
        switch ($type) {
            case 0:
                $max = $this->BoardInterface->getMaxScanBlock1($id);
                break;
            case 1:
                $max = $this->BoardInterface->getRows($id);
                break;
            case 2:
                $max = $this->BoardInterface->getColumns($id);
                break;
        }
        $response = [
            'max' => $max
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function getMaxScanBlock2_post() {
        $this->BoardInterface->initTrans();
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->idboard;
        $type = $request->type;
        $scanGroup = $request->scanGroup;

        switch ($type) {
            case 0:
                $max = $this->BoardInterface->getMaxScanBlock2($id, $scanGroup);
                break;
            case 1:
                $max = $this->BoardInterface->getColumns($id);
                break;
            case 2:
                $max = $this->BoardInterface->getRows($id);
                break;
        }

        if ($max != null) {
            $response = [
                'max' => $max
            ];
        } else {
            $response = [
                'max' => "No group found"
            ];
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function getScannedCells_post() {
        $this->BoardInterface->initTrans();
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->idboard;
        $csb1 = $request->numCustomScanBlock1;
        $csb2 = $request->numCustomScanBlock2;

        $array = $this->BoardInterface->getScannedCells($id, $csb1, $csb2);
        $response = [
            'array' => $array
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function getAudioSentence_post() {

        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $sentence = $request->sentence;
        $voice = $request->voice;


        $md5 = MD5(strval($voice) . $sentence);
        $array = $this->BoardInterface->getAudioSentence($md5);
        if ($array != null) {
            $response = [
                'data' => $array[0]->mp3Path
            ];
        } else {
            $response = [
                //MODIF: NO ESTA EL MP3, DESCARREGAR MP3 VOCALWARE
                'data' => MD5(strval($voice) . $sentence)
            ];
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function getPrediction_post() {
        // CARGA recommenderArray                 
        $prediction = new Myprediction();
        $recommenderArray = $prediction->getPrediction();

        for ($i = 0; $i < count($recommenderArray); $i++) {
            $img = $this->BoardInterface->getImgCell($recommenderArray[$i]->pictoid);
            $recommenderArray[$i]->imgtemp = $img;
        }
        $response = [ 'recommenderArray' => $recommenderArray];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function score_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $score = $request->score;

        $idusu = $this->session->userdata('idusu');
        $id = $this->BoardInterface->getIdLastSentence($idusu);
        if ($id === null) {
            $this->response(300);
        }
        $this->BoardInterface->score($id, $score);
        $this->response(REST_Controller::HTTP_OK);
    }

    public function modifyColorCell_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;
        $color = $request->color;

        $this->BoardInterface->modifyColorCell($id, $color);
    }

    /*
     * Generate audio
     */

    public function readText_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $text = $request->text;
        $interface = $request->interface;
        $idusu = $this->session->userdata('idusu');

        // GENERAR AUDIO
        $audio = new Myaudio();
        $aux = $audio->generateAudio($idusu, $text, $interface);

        $audio->waitForFile($aux[0], $aux[1]);
        
        // We save the audio error code in the database
        if ($aux[1]) {
            $this->BoardInterface->ErrorAudioToDB($aux[3]);
        }

        $response = [
            'audio' => $aux
        ];

        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function getColors_post() {
        $data = $this->BoardInterface->getColors();

        $response = [
            'data' => $data
        ];

        $this->response($response, REST_Controller::HTTP_OK);
    }

}
