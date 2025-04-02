<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/locallib.php");
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/sso/cryptographic/mumie_cryptography_service.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/sso/token/token_service.php');

class ilMumieTaskLaunchFormGUI extends ilPropertyFormGUI
{

    private ilMumieTaskI18N $i18N;
    private ilObjMumieTask $mumieTask;
    private sso_token $sso_token;
    private $deadlineinmilliseconds;
    private $signeddata;
    private ilMumieTaskUser $mumieUser;

    private $loginurl_item;
    private $userId_item;
    private $token_item;
    private $org_item;
    private $problemurl_item;
    private $problempath_item;
    private $lang_item;
    private $deadlineinmilliseconds_item;
    private $signeddata_item;

    public function __construct(ilObjMumieTask $task, ilMumieTaskUser $mumieUser)
    {
        parent::__construct();

        ilLoggerFactory::getLogger('xmum')->error('new ilMumieTaskLaunchFormGUI' . $mumieUser->get_mumie_id());
        ilLoggerFactory::getLogger('xmum')->error('new ilMumieTaskLaunchFormGUI 2' .
        $mumieUser->get_sync_id());

        $this->i18N = new ilMumieTaskI18N();
        $this->mumieTask = $task;
        $this->sso_token = token_service::generate_sso_token($mumieUser);
        $this->mumieUser = $mumieUser;

        global $ilUser;
        $deadline = locallib::mumie_get_effective_duedate($ilUser->getId(), $task);
        $this->deadlineinmilliseconds = locallib::auth_mumie_get_deadline_in_ms($deadline);
        $syncidlowercase = strtolower($this->mumieUser->get_sync_id());
        // TODO douplicate code
        $this->signeddata = mumie_cryptography_service::sign_data(
            $this->deadlineinmilliseconds,
            $syncidlowercase,
            locallib::get_worksheet_id($this->mumieTask)
        );
    }

//     /**
//      * ********************************** TODO is copy of launch_form_builder.php ****************
//      * Get the html string input for deadline parameter
//      * @param int $deadline
//      * @return string
//      */
//     private function get_deadline_signature_inputs(int $deadline): string
//     {
//         $deadlineinmilliseconds = locallib::auth_mumie_get_deadline_in_ms($deadline);
//         $syncidlowercase = strtolower($this->mumieUser->get_sync_id());
//         $signeddata = mumie_cryptography_service::sign_data(
//             $deadlineinmilliseconds,
//             $syncidlowercase,
//             locallib::get_worksheet_id($this->mumieTask)
//         );
//         return "<input type='hidden' name='deadline' id='deadline' type='text' value='{$deadlineinmilliseconds}'>
//         <input type='hidden' name='deadlineSignature' id='deadlineSignature' type='text' value='{$signeddata}'>";
//     }


    public function setFields() {
        $loginurl = $this->mumieTask->auth_mumie_get_login_url();
        $org = ilMumieTaskAdminSettings::getInstance()->getOrg();
        $problempath = $this->mumieTask->auth_mumie_get_problem_path();

        $this->loginurl_item = new ilHiddenInputGUI('xmum_loginurl');
        $this->loginurl_item->setValue($loginurl);
        $this->loginurl_item->setRequired(false);
        $this->addItem($this->loginurl_item);

        $this->userId_item = new ilHiddenInputGUI('xmum_userId');
        $this->userId_item->setValue($this->sso_token->get_user());
        $this->userId_item->setRequired(false);
        $this->addItem($this->userId_item);

        $this->token_item = new ilHiddenInputGUI('xmum_token');
        $this->token_item->setValue($this->sso_token->get_token());
        $this->token_item->setRequired(false);
        $this->addItem($this->token_item);

        $this->org_item = new ilHiddenInputGUI('xmum_org');
        $this->org_item->setValue($org);
        $this->org_item->setRequired(false);
        $this->addItem($this->org_item);

        $this->problempath_item = new ilHiddenInputGUI('xmum_problempath');
        $this->problempath_item->setValue($problempath);
        $this->problempath_item->setRequired(false);
        $this->addItem($this->problempath_item);

        $this->lang_item = new ilHiddenInputGUI('xmum_lang');
        $this->lang_item->setValue($this->mumieTask->getLanguage());
        $this->lang_item->setRequired(false);
        $this->addItem($this->lang_item);

        $this->deadlineinmilliseconds_item = new ilHiddenInputGUI('xmum_deadlineinmilliseconds');
        $this->deadlineinmilliseconds_item->setValue($this->deadlineinmilliseconds);
        $this->deadlineinmilliseconds_item->setRequired(false);
        $this->addItem($this->deadlineinmilliseconds_item);

        $this->signeddata_item = new ilHiddenInputGUI('xmum_signeddata');
        $this->signeddata_item->setValue($this->signeddata);
        $this->signeddata_item->setRequired(false);
        $this->addItem($this->signeddata_item);


        $button = new ilMumieTaskFormButtonGUI("", "xmum_launch");
        $button->setButtonLabel('Launch'); // TODO use i18n
        $button->setRequired(false);
        $this->addItem($button);
    }

}

