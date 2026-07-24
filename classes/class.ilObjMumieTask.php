<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ilObjMumieTask extends ilObjectPlugin implements ilLPStatusPluginInterface
{
    public const DUMMY_TITLE = '-- Empty MumieTask --';
    public const WORKSHEET_PREFIX = 'worksheet_';
    private static $MUMIE_TASK_TABLE_NAME = 'xmum_mumie_task';
    private $server;
    private $mumie_course;
    private $taskurl;
    private $launchcontainer;
    private $language;
    private $mumie_coursefile;
    private $worksheet;
    private $lp_modus = 1;
    private $passing_grade = 60;
    private $private_gradepool;
    private $online;
    private $activation_limited;
    private $activation_starting_time;
    private $activation_ending_time;
    private $activation_visibility;
    private $deadline;
    private $timelimit;

    /**
     * Constructor.
     *
     * @param int $a_ref_id
     */
    public function __construct($a_ref_id = 0)
    {
        parent::__construct($a_ref_id);
    }

    public static function constructDummy(): ilObjMumieTask
    {
        $task = new ilObjMumieTask();
        $task->setTitle(self::DUMMY_TITLE);

        return $task;
    }

    /**
     * Get type.
     */
    final public function initType(): void
    {
        $this->setType(ilMumieTaskPlugin::ID);
    }

    /**
     * Create object.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function doCreate(bool $clone_mode = false): void
    {
        global $DIC;
        $DIC->database()->insert(ilObjMumieTask::$MUMIE_TASK_TABLE_NAME, [
            'id' => ['integer', $this->getId()],
        ]);
    }

    /**
     * Read data from db.
     */
    public function doRead(): void
    {
        global $DIC;
        $db = $DIC->database();

        $result = $db->query(
            'SELECT * FROM ' . ilObjMumieTask::$MUMIE_TASK_TABLE_NAME .
            ' WHERE id = ' . $db->quote($this->getId(), 'integer'),
        );
        if (!is_null($result)) {
            $rec = $db->fetchAssoc($result);
            $this->setTaskurl($rec['taskurl']);
            $this->setLaunchcontainer($rec['launchcontainer']);
            $this->setMumieCourse($rec['mumie_course']);
            $this->setMumieCoursefile($rec['mumie_coursefile']);
            $this->setLanguage($rec['language']);
            $this->setServer($rec['server']);
            $this->setLpModus($rec['lp_modus']);
            $this->setPassingGrade($rec['passing_grade']);
            $this->setOnline($rec['online']);
            $this->setPrivateGradepool($rec['privategradepool']);
            $this->setDeadline($rec['deadline']);
            $this->setTimelimit($rec['timelimit']);
            $this->setWorksheet($rec['worksheet']);
        }

        /*
         * Snippet taken from ilObjTask->loadFromDb
         */
        if ($this->ref_id) {
            $activation = ilObjectActivation::getItem($this->ref_id);
            switch ($activation['timing_type']) {
                case ilObjectActivation::TIMINGS_ACTIVATION:
                    $this->setActivationLimited(true);
                    $this->setActivationStartingTime($activation['timing_start']);
                    $this->setActivationEndingTime($activation['timing_end']);
                    $this->setActivationVisibility($activation['visible']);
                    break;

                default:
                    $this->setActivationLimited(false);
                    break;
            }
        }
    }

    /**
     * Update data.
     */
    public function doUpdate(): void
    {
        global $DIC;

        $DIC->database()->update(
            ilObjMumieTask::$MUMIE_TASK_TABLE_NAME,
            [
                'taskurl' => ['text', $this->getTaskurl()],
                'launchcontainer' => ['integer', $this->getLaunchcontainer()],
                'mumie_course' => ['text', $this->getMumieCourse()],
                'language' => ['text', $this->getLanguage()],
                'server' => ['text', $this->getServer()],
                'mumie_coursefile' => ['text', $this->getMumieCoursefile()],
                'passing_grade' => ['integer', $this->getPassingGrade()],
                'lp_modus' => ['integer', $this->getLpModus()],
                'privategradepool' => ['integer', $this->getPrivateGradepool()],
                'online' => ['integer', $this->getOnline()],
                'deadline' => ['integer', $this->getDeadline()],
                'timelimit' => ['integer', $this->getTimelimit()],
                'worksheet' => ['text', $this->getWorksheet()],
            ],
            [
                'id' => ['int', $this->getId()],
            ],
        );

        /*
         * Snippet taken from ilObjTest->saveToDb()
         */
        if ($this->ref_id) {
            ilObjectActivation::getItem($this->ref_id);

            $item = new ilObjectActivation();
            if (!$this->getActivationLimited()) {
                $item->setTimingType(ilObjectActivation::TIMINGS_DEACTIVATED);
            } else {
                $item->setTimingType(ilObjectActivation::TIMINGS_ACTIVATION);
                $item->setTimingStart($this->getActivationStartingTime());
                $item->setTimingEnd($this->getActivationEndingTime());
                $item->toggleVisible((bool) $this->getActivationVisibility());
            }

            $item->update($this->ref_id);
        }
    }

    /**
     * Delete data from db.
     */
    public function doDelete(): void
    {
        global $DIC;
        $db = $DIC->database();
        ilMumieTaskDeadlineExtensionService::deleteDeadlineExtensions($this);
        $db->manipulate(
            'DELETE FROM ' . ilObjMumieTask::$MUMIE_TASK_TABLE_NAME . ' WHERE ' .
            ' id = ' . $db->quote($this->getId(), 'integer'),
        );
    }

    /**
     * Do Cloning.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function doClone($a_target_id, $a_copy_id, $new_obj)
    {
        $new_obj->setOnline($this->getOnline());
        $new_obj->setOptionOne($this->getOptionOne());
        $new_obj->setOptionTwo($this->getOptionTwo());
        $new_obj->update();
    }

    /**
     * Set online.
     *
     * @param bool                online
     */
    public function setOnline($a_val)
    {
        $this->online = $a_val;
    }

    /**
     * Get online.
     *
     * @return bool online
     */
    public function getOnline()
    {
        return $this->online;
    }

    /**
     * Get all user ids with LP status completed.
     */
    public function getLPCompleted(): array
    {
        return ilMumieTaskLPStatus::getLPCompletedForMumieTask($this->getId());
    }

    /**
     * Get all user ids with LP status not attempted.
     */
    public function getLPNotAttempted(): array
    {
        return ilMumieTaskLPStatus::getLPNotAttemptedForMumieTask($this->getId());
    }

    /**
     * Get all user ids with LP status failed.
     */
    public function getLPFailed(): array
    {
        return ilMumieTaskLPStatus::getLPFailedForMumieTask($this->getId());
    }

    /**
     * Get all user ids with LP status in progress.
     */
    public function getLPInProgress(): array
    {
        return ilMumieTaskLPStatus::getLPInProgressForMumieTask($this->getId());
    }

    /**
     * Get current status for given user.
     *
     * @param int $a_user_id
     */
    public function getLPStatusForUser($a_user_id): int
    {
        return ilMumieTaskLPStatus::getLPStatusForUser($this, $a_user_id);
    }

    public function updateAccess()
    {
        global $DIC;
        $user_id = $DIC->user()->getId();
        if (ANONYMOUS_USER_ID != $user_id) {
            ilMumieTaskLPStatus::updateAccess($user_id, $this, $this->getRefId(), $this->getLPStatusForUser($user_id));
        }
    }

    /**
     * A dummy is a MumieTask without any meaningful properties.
     *
     * All MumieTasks are created as dummy for technical reasons
     */
    public function isDummy()
    {
        return self::DUMMY_TITLE == $this->title;
    }

    /**
     * Get the value of server.
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Set the value of server.
     *
     * @return self
     */
    public function setServer($server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * Get the value of mumie_course.
     */
    public function getMumieCourse()
    {
        return $this->mumie_course;
    }

    /**
     * Set the value of mumie_course.
     *
     * @return self
     */
    public function setMumieCourse($mumie_course)
    {
        $this->mumie_course = $mumie_course;

        return $this;
    }

    /**
     * Get the value of taskurl.
     */
    public function getTaskurl()
    {
        return $this->taskurl;
    }

    /**
     * Set the value of taskurl.
     *
     * @return self
     */
    public function setTaskurl($taskurl)
    {
        $this->taskurl = $taskurl;

        return $this;
    }

    public function isWorksheet(): bool
    {
        return str_starts_with((string) $this->getTaskurl(), self::WORKSHEET_PREFIX);
    }

    public function getWorksheetId(): ?string
    {
        if (!$this->isWorksheet()) {
            return null;
        }

        return substr($this->getTaskurl(), strlen(self::WORKSHEET_PREFIX));
    }

    /**
     * Get the value of launchcontainer.
     */
    public function getLaunchcontainer()
    {
        return $this->launchcontainer;
    }

    /**
     * Set the value of launchcontainer.
     *
     * @return self
     */
    public function setLaunchcontainer($launchcontainer)
    {
        $this->launchcontainer = $launchcontainer;

        return $this;
    }

    /**
     * Get the value of language.
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set the value of language.
     *
     * @return self
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get the value of mumie_coursefile.
     */
    public function getMumieCoursefile()
    {
        return $this->mumie_coursefile;
    }

    /**
     * Set the value of mumie_coursefile.
     *
     * @return self
     */
    public function setMumieCoursefile($mumie_coursefile)
    {
        $this->mumie_coursefile = $mumie_coursefile;

        return $this;
    }

    /**
     * Generates the html code for launching the MumieTask.
     */
    public function getContent()
    {
        $ssoService = new ilMumieTaskSSOService();

        return $ssoService->setUpTokenAndLaunchForm($this);
    }

    /**
     * Get complete url for single sign in to MUMIE server.
     *
     * @return string login url
     */
    public function getLoginUrl()
    {
        return ilMumieTaskServer::fromUrl($this->server)->getLoginUrl();
    }

    /**
     * Get complete url for single sign out from MUMIE server.
     *
     * @return string logout url
     */
    public function getLogoutUrl()
    {
        return ilMumieTaskServer::fromUrl($this->server)->getLogoutUrl();
    }

    /**
     * Get complete url to the problem on MUMIE server.
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
     * Get the value of lp_modus.
     */
    public function getLpModus()
    {
        return $this->lp_modus;
    }

    /**
     * Set the value of lp_modus.
     *
     * @return self
     */
    public function setLpModus($lp_modus)
    {
        $this->lp_modus = $lp_modus;

        return $this;
    }

    /**
     * Get the value of passing_grade.
     */
    public function getPassingGrade()
    {
        return $this->passing_grade;
    }

    /**
     * Set the value of passing_grade.
     *
     * @return self
     */
    public function setPassingGrade($passing_grade)
    {
        $this->passing_grade = $passing_grade;

        return $this;
    }

    public function getActivationLimited()
    {
        return $this->activation_limited;
    }

    public function setActivationLimited($activation_limited)
    {
        $this->activation_limited = $activation_limited;

        return $this;
    }

    public function getActivationStartingTime()
    {
        return $this->activation_starting_time;
    }

    public function setActivationStartingTime($activation_starting_time)
    {
        $this->activation_starting_time = $activation_starting_time;

        return $this;
    }

    public function getActivationEndingTime()
    {
        return $this->activation_ending_time;
    }

    public function setActivationEndingTime($activation_ending_time)
    {
        $this->activation_ending_time = $activation_ending_time;

        return $this;
    }

    public function getActivationVisibility()
    {
        return $this->activation_visibility;
    }

    public function setActivationVisibility($activation_visibility)
    {
        $this->activation_visibility = $activation_visibility;

        return $this;
    }

    public function getPrivateGradepool()
    {
        return $this->private_gradepool;
    }

    public function setPrivateGradepool($private_gradepool)
    {
        $this->private_gradepool = $private_gradepool;
    }

    public function isGradepoolSet()
    {
        return !(-1 == $this->private_gradepool);
    }

    public function getParentRef()
    {
        global $DIC;
        $tree = $DIC['tree'];

        return $tree->getParentId($this->getRefId());
    }

    public function hasDeadline()
    {
        return !empty($this->deadline) && $this->deadline > 0;
    }

    public function getDeadline()
    {
        return $this->deadline;
    }

    public function setDeadline($deadline): void
    {
        $this->deadline = $deadline;
    }

    public function getDeadlineDateTime(): ilMumieTaskDateTime
    {
        return new ilMumieTaskDateTime($this->deadline);
    }

    public function hasTimelimit(): bool
    {
        return !empty($this->timelimit) && $this->timelimit > 0;
    }

    public function getTimelimit()
    {
        return $this->timelimit;
    }

    public function setTimelimit($timelimit): void
    {
        $this->timelimit = $timelimit;
    }

    public function hasAnyDeadline(): bool
    {
        return $this->hasDeadline() || $this->hasTimelimit();
    }

    public function requiresDeadlineSignature(): bool
    {
        return $this->isWorksheet() && $this->hasAnyDeadline();
    }

    public function getWorksheet()
    {
        return $this->worksheet;
    }

    /**
     * @param string $worksheet
     */
    public function setWorksheet($worksheet): void
    {
        $this->worksheet = $worksheet;
    }
}
