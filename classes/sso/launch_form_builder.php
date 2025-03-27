<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/sso/cryptographic/mumie_cryptography_service.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/sso/token/sso_token.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/users/class.ilMumieTaskUser.php');

/**
 * This class is used to create an HTML form that's used to launch the SSO POST request
 *
 * @package auth_mumie
 * @copyright  2017-2023 integral-learning GmbH (https://www.integral-learning.de/)
 * @author Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class launch_form_builder
{
    private sso_token $ssotoken;
    private ilObjMumieTask $mumietask;
    private ilMumieTaskUser$user;
    private string $deadlinefragment;

    /**
     * Create a new instance
     * @param sso_token  $ssotoken
     * @param ilObjMumieTask  $mumieTask
     * @param ilMumieTaskUser$user
     */
    public function __construct(sso_token $ssotoken, ilObjMumieTask $mumieTask, ilMumieTaskUser
    $user)
    {
        $this->ssotoken = $ssotoken;
        $this->mumietask = $mumieTask;
        $this->user = $user;
        $this->deadlinefragment = '';
    }

    /**
     * Add a deadline parameter to the launch form.
     * @param int $deadline
     * @return $this
     */
    public function with_deadline(int $deadline): launch_form_builder
    {
        $this->deadlinefragment = $this->get_deadline_signature_inputs($deadline);
        return $this;
    }

    /**
     * Get the html string input for deadline parameter
     * @param int $deadline
     * @return string
     */
    private function get_deadline_signature_inputs(int $deadline): string
    {
        $deadlineinmilliseconds = self::auth_mumie_get_deadline_in_ms($deadline);
        $syncidlowercase = strtolower($this->user->get_sync_id());
        $signeddata = \mumie_cryptography_service::sign_data(
            $deadlineinmilliseconds,
            $syncidlowercase,
            $this->get_worksheet_id()
        );
        return "<input type='hidden' name='deadline' id='deadline' type='text' value='{$deadlineinmilliseconds}'>
        <input type='hidden' name='deadlineSignature' id='deadlineSignature' type='text' value='{$signeddata}'>";
    }

    /**
     * Transforms the deadline(Unix Timestamp) from seconds to milliseconds.
     * @param int $deadline timestamp in s
     * @return int timestamp in ms
     */
    public function auth_mumie_get_deadline_in_ms($deadline)
    {
        return $deadline * 1000;
    }

    /**
     * Get worksheet id from problem path
     * @return string
     */
    private function get_worksheet_id(): string
    {
        $problempath = $this->mumietask->auth_mumie_get_problem_path();
        return str_replace(ilMumieTaskSSOService::WORKSHEET_PREFIX, "", $problempath);
    }

    /**
     * Get the launch form html code as string
     * @return string
     * @throws \dml_exception
     */
    public function build(): string
    {
        $loginurl = $this->mumietask->auth_mumie_get_login_url();
        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php");
        $org = ilMumieTaskAdminSettings::getInstance()->getOrg();
        $problemurl = $this->mumietask->auth_mumie_get_problem_url();
        $problempath = $this->mumietask->auth_mumie_get_problem_path();
        $target = "";
        $iframe = "";
        $iframe_settings = "";
        if ($this->mumietask->getLaunchcontainer() == 1) {
            $target = 'MumieTaskLaunchFrame';
            $iframe = "<iframe name='MumieTaskLaunchFrame'  id='basicMumieTaskLaunchFrame' src='{$loginurl}'
                       width='100%' height='600' scrolling='auto' frameborder='0' transparency>
                       </iframe>";
            $iframe_settings = "const iframe = document.getElementById('basicMumieTaskLaunchFrame');
                                          let width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;

                                          let height = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
                                          height = height * 0.8;
                                          width = width * 0.6;

                                          //iframe.width = width;
                                          iframe.height = height;";
        } else {
            $target = '_blank';
        }

        return"
            <form id='mumie_sso_form' name='mumie_sso_form' method='post' action='{$loginurl}'
            target='{$target}'>
                <input type='hidden' name='userId' id='userId' type ='text' value='{$this->ssotoken->get_user()}'/>
                <input type='hidden' name='token' id='token' type ='text' value='{$this->ssotoken->get_token()}'/>
                <input type='hidden' name='org' id='org' type ='text' value='{$org}'/>
                <input type='hidden' name='resource' id='resource' type ='text' value='{$problemurl}'/>
                <input type='hidden' name='path' id='path' type ='text' value='{$problempath}'/>
                <input type='hidden' name='lang' id='lang' type ='text' value='{$this->mumietask->getLanguage()}'/>
                {$this->deadlinefragment}
            </form>
            {$iframe}
            <script>
                {$iframe_settings}
                document.forms['mumie_sso_form'].submit();
            </script>
        ";
    }
}
