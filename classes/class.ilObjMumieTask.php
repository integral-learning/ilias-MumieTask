<?php

include_once ("./Services/Repository/classes/class.ilObjectPlugin.php");
require_once ("./Services/Tracking/interfaces/interface.ilLPStatusPlugin.php");
require_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTaskGUI.php");
require_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskSSOService.php");

/**
 */
class ilObjMumieTask extends ilObjectPlugin implements ilLPStatusPluginInterface {

    private static $MUMIE_TASK_TABLE_NAME = "xmum_mumie_task";
    private $name, $server, $mumie_course, $taskurl, $launchcontainer, $language, $mumie_coursefile; /**
     * Constructor
     *
     * @access        public
     * @param int $a_ref_id
     */
    function __construct($a_ref_id = 0) {
        parent::__construct($a_ref_id);
    }

    /**
     * Get type.
     */
    final function initType() {
        $this->setType(ilMumieTaskPlugin::ID);
    }

    /**
     * Create object
     */
    function doCreate() {
        global $ilDB;
        $ilDB->insert(ilObjMumieTask::$MUMIE_TASK_TABLE_NAME, array(
            "id" => array('integer', $this->getId()),
        ));
    }

    /**
     * Read data from db
     */
    function doRead() {
        global $ilDB;

        $result = $ilDB->query("SELECT * FROM " . ilObjMumieTask::$MUMIE_TASK_TABLE_NAME .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
        if (!is_null($result)) {
            $rec = $ilDB->fetchAssoc($result);
            $this->setName($rec['name']);
            $this->setTaskurl($rec['taskurl']);
            $this->setLaunchcontainer($rec['launchcontainer']);
            $this->setMumie_course($rec['mumie_course']);
            $this->setMumie_coursefile($rec['mumie_coursefile']);
            $this->setLanguage($rec['language']);
            $this->setServer($rec['server']);
        }
    }

    /**
     * Update data
     */
    function doUpdate() {
        global $DIC;

        $DIC->database()->update(ilObjMumieTask::$MUMIE_TASK_TABLE_NAME,
            array(
                'name' => array('text', $this->getName()),
                'taskurl' => array('text', $this->getTaskurl()),
                'launchcontainer' => array('integer', $this->getLaunchcontainer()),
                'mumie_course' => array('text', $this->getMumie_course()),
                'language' => array('text', $this->getLanguage()),
                'server' => array('text', $this->getServer()),
                'mumie_coursefile' => array('text', $this->getMumie_coursefile()),
            ),
            array(
                'id' => array("int", $this->getId()),
            ));
        /*
    $ilDB->manipulate($up = "UPDATE rep_robj_xtst_data SET " .
    " is_online = " . $ilDB->quote($this->isOnline(), "integer") . "" .
    " WHERE id = " . $ilDB->quote($this->getId(), "integer")
    );
     */
    }

    /**
     * Delete data from db
     */
    function doDelete() {
        global $ilDB;
/*
$ilDB->manipulate("DELETE FROM rep_robj_xtst_data WHERE " .
" id = " . $ilDB->quote($this->getId(), "integer")
);
 */
    }

    /**
     * Do Cloning
     */
    function doClone($a_target_id, $a_copy_id, $new_obj) {
        global $ilDB;

        $new_obj->setOnline($this->isOnline());
        $new_obj->setOptionOne($this->getOptionOne());
        $new_obj->setOptionTwo($this->getOptionTwo());
        $new_obj->update();
    }

    /**
     * Set online
     *
     * @param        boolean                online
     */
    function setOnline($a_val) {
        $this->online = $a_val;
    }

    /**
     * Get online
     *
     * @return        boolean                online
     */
    function isOnline() {
        return $this->online;
    }

    /**
     * Get all user ids with LP status completed
     *
     * @return array
     */
    public function getLPCompleted() {
        return array();
    }

    /**
     * Get all user ids with LP status not attempted
     *
     * @return array
     */
    public function getLPNotAttempted() {
        return array();
    }

    /**
     * Get all user ids with LP status failed
     *
     * @return array
     */
    public function getLPFailed() {
        return array(6);
    }

    /**
     * Get all user ids with LP status in progress
     *
     * @return array
     */
    public function getLPInProgress() {
        return array();
    }

    /**
     * Get current status for given user
     *
     * @param int $a_user_id
     * @return int
     */
    public function getLPStatusForUser($a_user_id) {
        global $ilUser;
        if ($ilUser->getId() == $a_user_id) {
            return $_SESSION[ilObjMumieTaskGUI::LP_SESSION_ID];
        } else {
            return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
        }
    }

    /**
     * Get the value of name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of server
     */
    public function getServer() {
        return $this->server;
    }

    /**
     * Set the value of server
     *
     * @return  self
     */
    public function setServer($server) {
        $this->server = $server;

        return $this;
    }

    /**
     * Get the value of mumie_course
     */
    public function getMumie_course() {
        return $this->mumie_course;
    }

    /**
     * Set the value of mumie_course
     *
     * @return  self
     */
    public function setMumie_course($mumie_course) {
        $this->mumie_course = $mumie_course;

        return $this;
    }

    /**
     * Get the value of taskurl
     */
    public function getTaskurl() {
        return $this->taskurl;
    }

    /**
     * Set the value of taskurl
     *
     * @return  self
     */
    public function setTaskurl($taskurl) {
        $this->taskurl = $taskurl;

        return $this;
    }

    /**
     * Get the value of launchcontainer
     */
    public function getLaunchcontainer() {
        return $this->launchcontainer;
    }

    /**
     * Set the value of launchcontainer
     *
     * @return  self
     */
    public function setLaunchcontainer($launchcontainer) {
        $this->launchcontainer = $launchcontainer;

        return $this;
    }

    /**
     * Get the value of language
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * Set the value of language
     *
     * @return  self
     */
    public function setLanguage($language) {
        $this->language = $language;

        return $this;
    }

    /**
     * Get the value of mumie_coursefile
     */
    public function getMumie_coursefile() {
        return $this->mumie_coursefile;
    }

    /**
     * Set the value of mumie_coursefile
     *
     * @return  self
     */
    public function setMumie_coursefile($mumie_coursefile) {
        $this->mumie_coursefile = $mumie_coursefile;

        return $this;
    }

    /**
     * Generates the html code for launching the mumietask 
     */

    public function getContent(){
        $ssoService = new ilMumieTaskSSOService;
        return $ssoService->setUpTokenAndLaunchForm($this->getLoginUrl(), $this->launchcontainer, $this->getProblemUrl());
    }

    /**
     * Get complete url for single sign in to MUMIE server
     *
     * @return string login url
     */
    public function getLoginUrl() {
        return $this->server . 'public/xapi/auth/sso/login' ;
    }

    /**
     * Get complete url for single sign out from MUMIE server
     *
     * @return string logout url
     */
    public function getLogoutUrl() {
        return $this->server . 'public/xapi/auth/sso/logout' ;
    }

    /**
     * Get complete url to the problem on MUMIE server
     *
     * @return string login url
     */
    public function getProblemUrl() {
        return $this->server . $this->taskurl . '?lang=' . $this->language;
    }

}
?>