<?php

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");
require_once("./Services/Tracking/interfaces/interface.ilLPStatusPlugin.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTaskGUI.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskSSOService.php");
include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');

/**
 */
class ilObjMumieTask extends ilObjectPlugin implements ilLPStatusPluginInterface
{
    const DUMMY_TITLE = "-- Empty MumieTask --";
    private static $MUMIE_TASK_TABLE_NAME = "xmum_mumie_task";
    private $server;
    private $mumie_course;
    private $taskurl;
    private $launchcontainer;
    private $language;
    private $mumie_coursefile;
    private $lp_modus = 1;
    private $passing_grade = 60;
    private $online;
    private $activationLimited;
    private $activationStartingTime;
    private $activationEndingTime;
    private $activationVisibility;

    /**
     * Constructor
     *
     * @access        public
     * @param int $a_ref_id
     */
    public function __construct($a_ref_id = 0)
    {
        parent::__construct($a_ref_id);
    }

    public static function constructDummy()
    {
        $task = new ilObjMumieTask();
        $task->setTitle(self::DUMMY_TITLE);
        return $task;
    }

    /**
     * Get type.
     */
    final public function initType()
    {
        $this->setType(ilMumieTaskPlugin::ID);
    }

    /**
     * Create object
     */
    public function doCreate()
    {
        global $ilDB;
        $ilDB->insert(ilObjMumieTask::$MUMIE_TASK_TABLE_NAME, array(
            "id" => array('integer', $this->getId()),
        ));
    }

    /**
     * Read data from db
     */
    public function doRead()
    {
        global $ilDB;

        $result = $ilDB->query(
            "SELECT * FROM " . ilObjMumieTask::$MUMIE_TASK_TABLE_NAME .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
        if (!is_null($result)) {
            $rec = $ilDB->fetchAssoc($result);
            $this->setTaskurl($rec['taskurl']);
            $this->setLaunchcontainer($rec['launchcontainer']);
            $this->setMumie_course($rec['mumie_course']);
            $this->setMumie_coursefile($rec['mumie_coursefile']);
            $this->setLanguage($rec['language']);
            $this->setServer($rec['server']);
            $this->setLp_modus($rec['lp_modus']);
            $this->setPassing_grade($rec['passing_grade']);
            $this->setOnline($rec['online']);
        }

        /**
         * Snippet taken from ilObjTask->loadFromDb
         */
        if ($this->ref_id) {
            include_once "./Services/Object/classes/class.ilObjectActivation.php";
            $activation = ilObjectActivation::getItem($this->ref_id);
            switch ($activation["timing_type"]) {
                case ilObjectActivation::TIMINGS_ACTIVATION:
                    $this->setActivationLimited(true);
                    $this->setActivationStartingTime($activation["timing_start"]);
                    $this->setActivationEndingTime($activation["timing_end"]);
                    $this->setActivationVisibility($activation["visible"]);
                    break;

                default:
                    $this->setActivationLimited(false);
                    break;
            }
        }
    }

    /**
     * Update data
     */
    public function doUpdate()
    {
        global $DIC;

        $DIC->database()->update(
            ilObjMumieTask::$MUMIE_TASK_TABLE_NAME,
            array(
                'taskurl' => array('text', $this->getTaskurl()),
                'launchcontainer' => array('integer', $this->getLaunchcontainer()),
                'mumie_course' => array('text', $this->getMumie_course()),
                'language' => array('text', $this->getLanguage()),
                'server' => array('text', $this->getServer()),
                'mumie_coursefile' => array('text', $this->getMumie_coursefile()),
                'passing_grade' => array('integer', $this->getPassing_grade()),
                'lp_modus' => array('integer', $this->getLp_modus()),
                'online' => array('integer', $this->getOnline()),
            ),
            array(
                'id' => array("int", $this->getId()),
            )
        );

        /**
         * Sinppet taken from ilObjTest->saveToDb()
         */
        if ($this->ref_id) {
            include_once "./Services/Object/classes/class.ilObjectActivation.php";
            ilObjectActivation::getItem($this->ref_id);

            $item = new ilObjectActivation;
            if (!$this->getActivationLimited()) {
                $item->setTimingType(ilObjectActivation::TIMINGS_DEACTIVATED);
            } else {
                $item->setTimingType(ilObjectActivation::TIMINGS_ACTIVATION);
                $item->setTimingStart($this->getActivationStartingTime());
                $item->setTimingEnd($this->getActivationEndingTime());
                $item->toggleVisible($this->getActivationVisibility());
            }

            $item->update($this->ref_id);
        }
    }

    /**
     * Delete data from db
     */
    public function doDelete()
    {
        global $ilDB;

        $ilDB->manipulate(
            "DELETE FROM " . ilObjMumieTask::$MUMIE_TASK_TABLE_NAME . " WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    /**
     * Do Cloning
     */
    public function doClone($a_target_id, $a_copy_id, $new_obj)
    {
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
    public function setOnline($a_val)
    {
        $this->online = $a_val;
    }

    /**
     * Get online
     *
     * @return        boolean                online
     */
    public function getOnline()
    {
        return $this->online;
    }

    /**
     * Get all user ids with LP status completed
     *
     * @return array
     */
    public function getLPCompleted()
    {
        $this->plugin->includeClass('class.ilMumieTaskLPStatus.php');
        return ilMumieTaskLPStatus::getLPCompletedForMumieTask($this->getId());
    }

    /**
     * Get all user ids with LP status not attempted
     *
     * @return array
     */
    public function getLPNotAttempted()
    {
        $this->plugin->includeClass('class.ilMumieTaskLPStatus.php');
        return ilMumieTaskLPStatus::getLPNotAttemptedForMumieTask($this->getId());
    }

    /**
     * Get all user ids with LP status failed
     *
     * @return array
     */
    public function getLPFailed()
    {
        $this->plugin->includeClass('class.ilMumieTaskLPStatus.php');
        return ilMumieTaskLPStatus::getLPFailedForMumieTask($this->getId());
    }

    /**
     * Get all user ids with LP status in progress
     *
     * @return array
     */
    public function getLPInProgress()
    {
        $this->plugin->includeClass('class.ilMumieTaskLPStatus.php');
        return ilMumieTaskLPStatus::getLPInProgressForMumieTask($this->getId());
    }

    /**
     * Get current status for given user
     *
     * @param int $a_user_id
     * @return int
     */
    public function getLPStatusForUser($a_user_id)
    {
        global $ilUser;
        if ($ilUser->getId() == $a_user_id) {
            return $_SESSION[ilObjMumieTaskGUI::LP_SESSION_ID];
        } else {
            return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
        }
    }

    public function updateAccess()
    {
        global $ilUser;
        if ($ilUser->getId() != ANONYMOUS_USER_ID) {
            $this->plugin->includeClass('class.ilMumieTaskLPStatus.php');
            ilMumieTaskLPStatus::updateAccess($ilUser->getId(), $this->getId(), $this->getRefId());
        }
    }

    public function isDummy()
    {
        return $this->title == self::DUMMY_TITLE;
    }

    /**
     * Get the value of server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Set the value of server
     *
     * @return  self
     */
    public function setServer($server)
    {
        $this->server = $server;
        return $this;
    }

    /**
     * Get the value of mumie_course
     */
    public function getMumie_course()
    {
        return $this->mumie_course;
    }

    /**
     * Set the value of mumie_course
     *
     * @return  self
     */
    public function setMumie_course($mumie_course)
    {
        $this->mumie_course = $mumie_course;

        return $this;
    }

    /**
     * Get the value of taskurl
     */
    public function getTaskurl()
    {
        return $this->taskurl;
    }

    /**
     * Set the value of taskurl
     *
     * @return  self
     */
    public function setTaskurl($taskurl)
    {
        $this->taskurl = $taskurl;

        return $this;
    }

    /**
     * Get the value of launchcontainer
     */
    public function getLaunchcontainer()
    {
        return $this->launchcontainer;
    }

    /**
     * Set the value of launchcontainer
     *
     * @return  self
     */
    public function setLaunchcontainer($launchcontainer)
    {
        $this->launchcontainer = $launchcontainer;

        return $this;
    }

    /**
     * Get the value of language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set the value of language
     *
     * @return  self
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get the value of mumie_coursefile
     */
    public function getMumie_coursefile()
    {
        return $this->mumie_coursefile;
    }

    /**
     * Set the value of mumie_coursefile
     *
     * @return  self
     */
    public function setMumie_coursefile($mumie_coursefile)
    {
        $this->mumie_coursefile = $mumie_coursefile;

        return $this;
    }

    /**
     * Generates the html code for launching the mumietask
     */

    public function getContent()
    {
        $ssoService = new ilMumieTaskSSOService;
        return $ssoService->setUpTokenAndLaunchForm($this->getLoginUrl(), $this->launchcontainer, $this->getProblemUrl());
    }

    /**
     * Get complete url for single sign in to MUMIE server
     *
     * @return string login url
     */
    public function getLoginUrl()
    {
        return ilMumieTaskServer::fromUrl($this->server)->getLoginUrl();
        //return $this->server . 'public/xapi/auth/sso/login';
    }

    /**
     * Get complete url for single sign out from MUMIE server
     *
     * @return string logout url
     */
    public function getLogoutUrl()
    {
        //return $this->server . 'public/xapi/auth/sso/logout';
        ilMumieTaskServer::fromUrl($this->server)->getLogoutUrl();
    }

    /**
     * Get complete url to the problem on MUMIE server
     *
     * @return string login url
     */
    public function getProblemUrl()
    {
        return $this->server . $this->taskurl . '?lang=' . $this->language;
    }

    public function getGradeSyncURL()
    {
        return ilMumieTaskServer::fromUrl($this->server)->getGradeSyncURL();
    }

    /**
     * Get the value of lp_modus
     */
    public function getLp_modus()
    {
        return $this->lp_modus;
    }

    /**
     * Set the value of lp_modus
     *
     * @return  self
     */
    public function setLp_modus($lp_modus)
    {
        $this->lp_modus = $lp_modus;

        return $this;
    }

    /**
     * Get the value of passing_grade
     */
    public function getPassing_grade()
    {
        return $this->passing_grade;
    }

    /**
     * Set the value of passing_grade
     *
     * @return  self
     */
    public function setPassing_grade($passing_grade)
    {
        $this->passing_grade = $passing_grade;

        return $this;
    }

    public function getActivationLimited()
    {
        return $this->activationLimited;
    }

    public function setActivationLimited($activationLimited)
    {
        $this->activationLimited = $activationLimited;

        return $this;
    }

    public function getActivationStartingTime()
    {
        return $this->activationStartingTime;
    }

    public function setActivationStartingTime($activationStartingTime)
    {
        $this->activationStartingTime = $activationStartingTime;

        return $this;
    }

    public function getActivationEndingTime()
    {
        return $this->activationEndingTime;
    }

    public function setActivationEndingTime($activationEndingTime)
    {
        $this->activationEndingTime = $activationEndingTime;

        return $this;
    }

    public function getActivationVisibility()
    {
        return $this->activationVisibility;
    }

    public function setActivationVisibility($activationVisibility)
    {
        $this->activationVisibility = $activationVisibility;

        return $this;
    }
}
