<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/locallib.php");
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/sso/cryptographic/mumie_cryptography_service.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/sso/token/token_service.php');

class ilMumieTaskLaunchFormGUI
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

public function getContent(): string {

        $loginurl = $this->mumieTask->auth_mumie_get_login_url();
        $org = ilMumieTaskAdminSettings::getInstance()->getOrg();
        $problempath = $this->mumieTask->auth_mumie_get_problem_path();
        $userId = $this->sso_token->get_user();
        $token = $this->sso_token->get_token();
        $lang = $this->sso_token->get_token();

    return "
        <form >
            <input type='hidden' id='xmum_loginurl' value='$loginurl'>
            <input type='hidden' id='xmum_userId' value='$userId'>
            <input type='hidden' id='xmum_token' value='$token'>
            <input type='hidden' id='xmum_org' value='$org'>
            <input type='hidden' id='xmum_problempath' value='$problempath'>
            <input type='hidden' id='xmum_lang' value='$lang'>
            <input type='hidden' id='xmum_deadlineinmilliseconds' value='$this->deadlineinmilliseconds'>
            <input type='hidden' id='xmum_signeddata' value='$this->signeddata'>
            <a class='xmum-pseudo-btn' id='xmum_launch'>
                Launch
            </a>
        </form>
    ";

}
}
