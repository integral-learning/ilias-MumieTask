<?php

class locallib
{
    /**
      * Get the unique identifier for a MUMIE task
      *
      * @param stdClass $mumietask
      * @return string id for MUMIE task on MUMIE/LEMON server
      */
    public static function getMumieId($mumietask): string
    {
        return self::transformMumieId($mumietask->getTaskurl());
    }

    public static function transformMumieId($mumietask_taskurl): string
    {
        $id = $mumietask_taskurl;
        $prefix = "link/";
        if (strpos($id, $prefix) > 0) {
            $id = substr($mumietask_taskurl, strlen($prefix));
            ilLoggerFactory::getLogger('xmum')->info("test ");
        }
        ilLoggerFactory::getLogger('xmum')->info("mumietask_taskurl " . json_encode($id));
        return $id;

    }

}
