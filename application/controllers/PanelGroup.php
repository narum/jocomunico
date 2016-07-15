<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class PanelGroup extends REST_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->library('session');
        $this->load->model('PanelInterface');
        $this->load->model('Lexicon');
        $this->load->model('BoardInterface');
        $this->load->model('AddWordInterface');
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

    public function getPanelGroupInfo_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $ID_GBoard = $request->idGroupBoard;

        $primaryBoard = $this->BoardInterface->getInfoGroupBoard($ID_GBoard);

        $response = [
            'ID_GB' => $primaryBoard[0]->ID_GB,
            'ID_GBUser' => $primaryBoard[0]->ID_GBUser,
            'GBname' => $primaryBoard[0]->GBname,
            'primaryGroupBoard' => $primaryBoard[0]->primaryGroupBoard,
            'defWidth' => $primaryBoard[0]->defWidth,
            'defHeight' => $primaryBoard[0]->defHeight,
            'imgGB' => $primaryBoard[0]->imgGB
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function getUserPanelGroups_post() {
        $idusu = $this->session->userdata('idusu');
        $panels = $this->PanelInterface->getUserPanels($idusu);

        $response = [
            'panels' => $panels
        ];

        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function getPanelToEdit_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $ID_GBoard = $request->ID_GB;

        $primaryBoard = $this->BoardInterface->getPrimaryBoard($ID_GBoard);
        $boards = $this->BoardInterface->getBoards($ID_GBoard);

        $response = [
            'id' => $primaryBoard[0]->ID_Board,
            'boards' => $boards
        ];

        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function setPrimaryGroupBoard_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $ID_GBoard = $request->ID_GB;
        $idusu = $this->session->userdata('idusu');

        $this->PanelInterface->setPrimaryGroupBoard($ID_GBoard, $idusu);

        $response = [
            'id' => $primaryBoard[0]->ID_Board
        ];

        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function newGroupPanel_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $GBName = $request->GBName;
        $defW = $request->defW;
        $defH = $request->defH;
        $imgGB = $request->imgGB;
        $idusu = $this->session->userdata('idusu');
        $this->BoardInterface->initTrans();
        $id = $this->PanelInterface->newGroupPanel($GBName, $idusu, $defW, $defH, $imgGB);

        $idBoard = $this->BoardInterface->createBoard($id, "default", $defW, $defH);
        $this->addColumns(0, 0, $idBoard, $defW);
        $this->addRows($defW, 0, $idBoard, $defH);
        $this->BoardInterface->setPrimaryBoard($idBoard);
        $this->BoardInterface->commitTrans();
        $response = [
            'idBoard' => $idBoard
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    //MODIF: Esta repetida, mirar que se puede hacer
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

    //MODIF: Esta repetida, mirar que se puede hacer
    public function addRows($columns, $rows, $idBoard, $rowsToAdd) {
        $currentPos = $columns * $rows + 1;
        for ($row = 0; $row < $rowsToAdd; $row++) {
            for ($column = 0; $column < $columns; $column++) {
                $this->BoardInterface->newCell($currentPos, $idBoard);
                $currentPos++;
            }
        }
    }

    public function modifyGroupBoardName_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $ID_GB = $request->ID;
        $name = $request->Name;
        $idusu = $this->session->userdata('idusu');

        $this->PanelInterface->changeGroupName($ID_GB, $name, $idusu);

        $response = [
            'id' => $primaryBoard[0]->ID_Board
        ];

        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function copyDefaultGroupBoard_post() {
        //MODIF: 2 es el panel default
        $this->BoardInterface->initTrans();
        $idusu = $this->session->userdata('idusu');
        $board = $this->BoardInterface->getPrimaryGroupBoard();
        if ($board == null) {

            $changedLinks = array();
            $srcGroupBoard = 2;
            $primaryBoard = $this->BoardInterface->getInfoGroupBoard($srcGroupBoard);

            $IDGboard = $this->PanelInterface->newGroupPanel($primaryBoard[0]->GBname, $idusu, $primaryBoard[0]->defWidth, $primaryBoard[0]->defHeight, $primaryBoard[0]->imgGB);
            $boards = $this->BoardInterface->getBoards($srcGroupBoard);
            //If we want to allow the user copy group boards this line have to be removed
            $this->PanelInterface->setPrimaryGroupBoard($IDGboard, $idusu);

            $sameGroupBoard = 1;
            for ($i = 0; $i < count($boards); $i++) {
                $idSrc = $boards[$i]->ID_Board;

                $name = $boards[$i]->Bname;
                $width = $boards[$i]->width;
                $height = $boards[$i]->height;
                $autoReturn = $boards[$i]->autoReturn;
                $autoReadSentence = $boards[$i]->autoReadSentence;

                $idDst = $this->BoardInterface->copyBoard($IDGboard, $name, $width, $height, $autoReturn, $autoReadSentence);
                if ($boards[$i]->primaryBoard) {
                    $this->BoardInterface->changePrimaryBoard($idDst, $IDGboard);
                    $idToShow = $idDst;
                }
                $boardtables = $this->BoardInterface->getBoardTables($idSrc);
                foreach ($boardtables as $row) {
                    $boardtables = $this->BoardInterface->copyBoardTables($idDst, $sameGroupBoard, $row);
                }
                array_push($changedLinks, $idSrc);
                array_push($changedLinks, $idDst);
            }
            for ($i = 0; $i < count($changedLinks); $i++) {
                $this->PanelInterface->updateBoardLinks($IDGboard, $changedLinks[$i], $changedLinks[$i + 1]);
                $i++;
            }
        } else {
            $primaryUserBoard = $this->BoardInterface->getPrimaryBoard($board[0]->ID_GB);
            $idToShow = $primaryUserBoard[0]->ID_Board;
        }
        $this->BoardInterface->commitTrans();
        $response = [
            'idBoard' => $idToShow
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function copyGroupBoard_post() {

        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $idusu = $request->user;
        $srcGroupBoard = $request->id;
        $changedLinks = array();
        
        $this->BoardInterface->initTrans();
        $primaryBoard = $this->BoardInterface->getInfoGroupBoard($srcGroupBoard);

        $IDGboard = $this->PanelInterface->newGroupPanel($primaryBoard[0]->GBname, $idusu, $primaryBoard[0]->defWidth, $primaryBoard[0]->defHeight, $primaryBoard[0]->imgGB);
        $boards = $this->BoardInterface->getBoards($srcGroupBoard);

        $sameGroupBoard = 1;
        for ($i = 0; $i < count($boards); $i++) {
            $idSrc = $boards[$i]->ID_Board;

            $name = $boards[$i]->Bname;
            $width = $boards[$i]->width;
            $height = $boards[$i]->height;
            $autoReturn = $boards[$i]->autoReturn;
            $autoReadSentence = $boards[$i]->autoReadSentence;

            $idDst = $this->BoardInterface->copyBoard($IDGboard, $name, $width, $height, $autoReturn, $autoReadSentence);
            if ($boards[$i]->primaryBoard) {
                $this->BoardInterface->changePrimaryBoard($idDst, $IDGboard);
            }
            $boardtables = $this->BoardInterface->getBoardTables($idSrc);
            foreach ($boardtables as $row) {
                $boardtables = $this->BoardInterface->copyBoardTables($idDst, $sameGroupBoard, $row);
                $idusuorigen = $this->session->userdata('idusu');
                if ($row->ID_CPicto != null) {
                    $this->Lexicon->addWordStatsX1($row->ID_CPicto, $idusu, true);
                    if ($row->imgCell != null) {
                        $this->Lexicon->addImgTempStatsX1($row->ID_CPicto, $idusu, $row->imgCell);
                    }
                }
            }

            array_push($changedLinks, $idSrc);
            array_push($changedLinks, $idDst);
        }

        for ($i = 0; $i < count($changedLinks); $i++) {
            $this->PanelInterface->updateBoardLinks($IDGboard, $changedLinks[$i], $changedLinks[$i + 1]);
            $i++;
        }
        $this->AddWordInterface->copyVocabulary($idusuorigen,$idusu);

        $this->BoardInterface->commitTrans();
        $response = [
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function loginToCopy_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $user = $request->user;
        $pass = $request->pass;

        $userObj = $this->PanelInterface->getUser($user, $pass);

        $response = [
            'userName' => $user,
            'userID' => $userObj[0]->ID_User
        ];
        $this->response($response, REST_Controller::HTTP_OK);
    }

}
