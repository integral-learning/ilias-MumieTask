<?php
/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class ilMumieTaskTemplateEngine
{
    public const EMPTY_CELL = '-';
    public static function getTemplate(string $path): ilTemplate
    {
        global $tpl;
        $tpl->addCss("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/mumie.css");
        $tpl->addCss("Services/FileUpload/templates/default/fileupload.css");
        return new ilTemplate($path, true, true, true, "DEFAULT", true);
    }

    public static function getDropzoneTemplate(): ilTemplate
    {
        return self::getTemplate("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/MumieTasks/tpl.file-drop-zone.html");
    }

    public static function getStudentGradingInfoboxTemplate(ilObjMumieTask $mumie_task, string $user_id, string $description = ''): ilTemplate
    {
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/i18n/class.ilMumieTaskI18N.php');
        $i18N = new ilMumieTaskI18N();
        $template = ilMumieTaskTemplateEngine::getTemplate("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/GradeList/tpl.grade-list-info-box.html");
        $template->setVariable('STUDENT_NAME', $i18N->txt('student_name'));
        $template->setVariable('STUDENT_NAME_VALUE', ilMumieTaskUserService::getFullName($user_id));
        $template->setVariable('GENERAL_DEADLINE', $i18N->txt('frm_user_overview_list_general_deadline'));
        $template->setVariable('GENERAL_DEADLINE_VALUE', self::getDeadlineInformation($mumie_task));
        $template->setVariable('DEADLINE_EXTENSION', $i18N->txt('frm_user_overview_list_extended_deadline'));
        $template->setVariable('DEADLINE_EXTENSION_VALUE', self::getDeadlineExtensionInformation($mumie_task, $user_id));
        $template->setVariable('CURRENT_GRADE', $i18N->txt('frm_grade_overview_list_used_grade'));
        $template->setVariable('CURRENT_GRADE_VALUE', self::getCurrentGradeInformation($mumie_task, $user_id));
        $template->setVariable('DESCRIPTION', $description);
        return $template;
    }

    private static function getDeadlineInformation(ilObjMumieTask $mumie_task): string
    {
        if ($mumie_task->hasDeadline()) {
            return $mumie_task->getDeadlineDateTime();
        }
        return ilMumieTaskTemplateEngine::EMPTY_CELL;
    }

    private static function getDeadlineExtensionInformation(ilObjMumieTask $mumie_task, $user_id): string
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/deadlines/extension/class.ilMumieTaskDeadlineExtensionService.php');
        if (ilMumieTaskDeadlineExtensionService::hasDeadlineExtension($user_id, $mumie_task) && $mumie_task->hasDeadline()) {
            return ilMumieTaskDeadlineExtensionService::getDeadlineExtensionDate($user_id, $mumie_task);
        }
        return ilMumieTaskTemplateEngine::EMPTY_CELL;
    }

    private static function getCurrentGradeInformation(ilObjMumieTask $mumie_task, $user_id): string
    {
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskLPStatus.php');
        $grade = ilMumieTaskLPStatus::getCurrentGradeForUser($user_id, $mumie_task);
        if (is_null($grade)) {
            return ilMumieTaskTemplateEngine::EMPTY_CELL;
        }
        if (ilMumieTaskGradeOverrideService::wasGradeOverridden($user_id, $mumie_task)) {
            $template = ilMumieTaskTemplateEngine::getTemplate("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/GradeOverview/tpl.overridden-grade-cell-html.html");
            $template->setVariable("VAL_GRADE", $grade->getPercentileScore());
            return $template->get();
        }
        return $grade->getPercentileScore();
    }
}
