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

/**
 * Opens the Problem Selector with SSO
 *
 * @copyright  2017-2023 integral-learning GmbH (https://www.integral-learning.de/)
 * @author Yannic Lapawczyk (yannic.lapawczyk@integral-learning.de)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Generates a hidden input field to send the selection when submitting the form
 *
 * @param string|null $selection The potential selection
 *
 * @return string The HTML representation of the hidden input field for the selection value
 */
function selection_input(?string $selection): string
{
    if ($selection === null) {
        return '';
    }
    return "<input type='hidden' name='selection' id='selection' type ='text' value='{$selection}'/>";
}

/**
 * Generates a form to open the problem selector and submit the form automatically
 *
 * @param string $user_id
 * @param string $serverurl The server URL
 * @param string $gradingtype The grading type
 * @param string $problemlang The problem language
 * @param string $origin The origin of the request
 * @param string|null $selection The potential selection
 *
 * @return string The HTML representation of the problem selector form
 * @throws \dml_exception
 */
function open_problem_selector(
    string $user_id,
    string $user_token,
    string $user_lang,
    string $serverurl,
    string $gradingtype,
    string $problemlang,
    string $origin,
    string $selection = null,
    string $org,
    string $lmsSelectorUrl
): string {
    $selectioninput = selection_input($selection);

    return "
            <form id='mumie_problem_selector_form' name='mumie_problem_selector_form'
            method='post' action='{$lmsSelectorUrl}/api/sso/problem-selector'>
                <input type='hidden' name='userId' id='userId' type ='text' value='{$user_id}'/>
                <input type='hidden' name='token' id='token' type ='text' value='{$user_token}'/>
                <input type='hidden' name='org' id='org' type ='text' value='{$org}'/>
                <input type='hidden' name='uiLang' id='uiLang' type ='text' value='{$user_lang}'/>
                <input type='hidden' name='serverUrl' id='serverUrl' type ='text' value='{$serverurl}'/>
                <input type='hidden' name='gradingType' id='gradingType' type ='text' value='{$gradingtype}'/>
                <input type='hidden' name='problemLang' id='problemLang' type ='text' value='{$problemlang}'/>
                <input type='hidden' name='origin' id='origin' type ='text' value='{$origin}'/>
                {$selectioninput}
            </form>
            <script>
            document.forms['mumie_problem_selector_form'].submit();
            </script>
        ";
}

$queries = array();
parse_str($_SERVER['QUERY_STRING'], $queries);

$serverurl = $queries['serverurl'];
$gradingtype = $queries['gradingtype'];
$problemlang = $queries['problemlang'];
$origin = $queries['origin'];
$contextid = $queries['contextid'];
$selection = $queries['selection'];
$lmsSelectorUrl = $queries['lmsSelectorUrl'];
$user_id = $queries['user_id'];
$user_token = $queries['user_token'];
$user_lang = $queries['user_lang'];
$org = $queries['org'];



// TODO
// $context = \context::instance_by_id($contextid);
// require_capability('auth/mumie:ssotoproblemselector', $context);
echo open_problem_selector(
    $user_id,
    $user_token,
    $user_lang,
    $serverurl,
    $gradingtype,
    $problemlang,
    $origin,
    $selection,
    $org,
    $lmsSelectorUrl
);
