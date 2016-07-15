<?php

class PanelInterface extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();

        $this->load->library('Myword');
    }

    /*
     * Get all group panels owned by a user (idusu)
     */

    function getUserPanels($idusu) {
        $output = array();
        $this->db->order_by('primaryGroupBoard DESC, ID_GB DESC');
        $this->db->where('ID_GBUser', $idusu);
        $query = $this->db->get('GroupBoards');

        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;

        return $output;
    }

    /*
     * Set the group board ($ID_GB) primary in the group ($idusu, the user)
     */

    function setPrimaryGroupBoard($ID_GB, $idusu) {
        $this->db->where('ID_GBUser', $idusu);
        $this->db->update('GroupBoards', array(
            'primaryGroupBoard' => '0',
        ));

        $this->db->where('ID_GB', $ID_GB);
        $this->db->update('GroupBoards', array(
            'primaryGroupBoard' => '1',
        ));
    }

    /*
     * Set the group board ($ID_GB) primary in the group ($idusu, the user)
     */

    function newGroupPanel($GBName, $idusu, $defW, $defH, $imgGB) {
        $data = array(
            'ID_GBUser' => $idusu,
            'GBName' => $GBName,
            'primaryGroupBoard' => '0',
            'defWidth' => $defW,
            'defHeight' => $defH,
            'imgGB' => $imgGB
        );

        $this->db->insert('GroupBoards', $data);

        $id = $this->db->insert_id();

        return $id;
    }

    /*
     * Change the group board Name
     */

    function changeGroupName($ID_GB, $name, $idusu) {
        $this->db->where('ID_GBUser', $idusu);
        $this->db->where('ID_GB', $ID_GB);
        $this->db->update('GroupBoards', array(
            'GBname' => $name,
        ));
    }

    /*
     * Update all the board links from oldBL to newBL in a groupboard(useful after copygroupboard) 
     */

    function updateBoardLinks($IDGB, $oldBoardLink, $newBoardLink) {
        $data = array(
            'boardLink' => $newBoardLink
        );
        $this->db->query("update Cell,R_BoardCell,Boards "
                . "SET Cell.boardLink = " . $newBoardLink . " "
                . "WHERE Boards.ID_GBBoard = " . $IDGB . " AND Cell.boardLink = " . $oldBoardLink . " AND R_BoardCell.ID_RBoard = Boards.ID_Board AND Cell.ID_Cell = R_BoardCell.ID_RCell");
        return $data;
    }

    function getUser($user, $pass) {
        $languageExp = $this->session->userdata('ulanguage');
        $this->db->join('User', 'SuperUser.ID_SU = User.ID_USU', 'left');
        $this->db->where('cfgExpansionLanguage', $languageExp);
        $this->db->where('SUname', $user);
        $this->db->where('pswd', md5($pass));
        $query = $this->db->get('SuperUser');

        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;

        return $output;
    }

}
