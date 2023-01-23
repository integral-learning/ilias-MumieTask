<?php
/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class ilMumieTaskGrade
{
    private $user_id;
    private $score;
    private $mumie_task;
    private $timestamp;

    public function __construct(string $user_id, float $score, ilObjMumieTask $mumie_task, int $timestamp)
    {
        $this->user_id = $user_id;
        $this->mumie_task = $mumie_task;
        $this->timestamp = $timestamp;
        $this->score = $score;
    }

    /**
     * @return mixed
     */
    public function getUserId(): string
    {
        return $this->user_id;
    }

    /**
     * @return mixed
     */
    public function getScore(): float
    {
        return $this->score;
    }

    /**
     * @return mixed
     */
    public function getMumieTask(): ilObjMumieTask
    {
        return $this->mumie_task;
    }

    public function getPercentileScore(): int
    {
        return round($this->getScore() * 100);
    }

    /**
     * @return mixed
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}