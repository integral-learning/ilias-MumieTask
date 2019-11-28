<#1>
<?php
if (!$ilDB->tableExists("xmum_sso_tokens")) {
    $fieldsToken = array(
        'id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
        ),
        'token' => array(
            'type' => 'text',
            'length' => 30,
            'notnull' => true,
        ),
        // hashed user id
        'user' => array(
            'type' => 'text',
            'length' => 128,
            'notnull' => true,
        ),
        'timecreated' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
        ),
    );

    $ilDB->createTable("xmum_sso_tokens", $fieldsToken);
    $ilDB->addPrimaryKey("xmum_sso_tokens", array("id"));
    $ilDB->createSequence("xmum_sso_tokens");
}
?>
<#2>
<?php
if (!$ilDB->tableExists("xmum_mumie_task")) {
    $fieldsMumie = array(
        'id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
        ),
        'taskurl' => array(
            'type' => 'text',
        ),
        'launchcontainer' => array(
            'type' => 'integer',
            'length' => '4',
        ),
        'mumie_course' => array(
            'type' => 'text',
            'length' => '255',
        ),
        'language' => array(
            'type' => 'text',
            'length' => '255',
        ),
        'server' => array(
            'type' => 'text',
            'length' => '255',
        ),
        'mumie_coursefile' => array(
            'type' => 'text',
            'length' => '255',
        ),
        'passing_grade' => array(
            'type' => 'integer',
            'length' => '4',
            'default' => 60,
        ),
        'lp_modus' => array(
            'type' => 'integer',
            'length' => '2',
            'default' => '1',
        ),
        'online' => array(
            'type' => 'integer',
            'length' => '2',
            'default' => '0',
        ),
    );
    $ilDB->createTable("xmum_mumie_task", $fieldsMumie);
    $ilDB->addPrimaryKey("xmum_mumie_task", array("id"));
}
?>
<#3>
<?php
if (!$ilDB->tableExists("xmum_mumie_servers")) {
    $fieldsServer = array(
        'server_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
            'default' => 0,
        ),
        'name' => array(
            'type' => 'text',
            'length' => 30,
            'notnull' => true,
        ),
        'url_prefix' => array(
            'type' => 'text',
            'length' => 200,
            'notnull' => true,
        ),
    );
    $ilDB->createTable("xmum_mumie_servers", $fieldsServer);
    $ilDB->addPrimaryKey("xmum_mumie_servers", array("server_id"));
    $ilDB->createSequence("xmum_mumie_servers");
}
?>
<#4>
<?php
if (!$ilDB->tableExists('xmum_admin_settings')) {
    $fieldsAminSettings = array(
        'id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
        ),
        'share_first_name' => array(
            'type' => 'integer',
            'default' => 'false',
            'length' => '1',
        ),
        'share_last_name' => array(
            'type' => 'integer',
            'default' => 'false',
            'length' => '1',
        ),
        'share_email' => array(
            'type' => 'integer',
            'default' => 'false',
            'length' => '1',
        ),
        'api_key' => array(
            'type' => 'text',
            'length' => '255',
        ),
        'org' => array(
            'type' => 'text',
            'length' => '7',
        ),
    );
    $ilDB->createTable("xmum_admin_settings", $fieldsAminSettings);
    $ilDB->addPrimaryKey("xmum_admin_settings", array("id"));
}
?>
<#5>
<?php
$query = 'SELECT * FROM ' . 'xmum_admin_settings';
$result = $ilDB->query($query);
if ($ilDB->numRows($result) < 1) {
    $ilDB->manipulate(
        "INSERT INTO xmum_admin_settings "
        . '(id, share_first_name, share_last_name, share_email, api_key, org) VALUES('
        . $ilDB->quote(1, 'integer') . ','
        . $ilDB->quote(0, 'integer') . ','
        . $ilDB->quote(0, 'integer') . ','
        . $ilDB->quote(0, 'integer') . ','
        . $ilDB->quote('', 'text') . ','
        . $ilDB->quote('', 'text')
        . ')'
    );
}
?>
<#6>
<?php

$set = $ilDB->query("SELECT obj_id FROM object_data WHERE type='typ' AND title = 'xmum'");
if ($rec = $ilDB->fetchAssoc($set)) {
    $typ_id = $rec["obj_id"];
} else {
    $typ_id = $ilDB->nextId("object_data");
    $ilDB->manipulate("INSERT INTO object_data " .
        "(obj_id, type, title, description, owner, create_date, last_update) VALUES (" .
        $ilDB->quote($typ_id, "integer") . "," .
        $ilDB->quote("typ", "text") . "," .
        $ilDB->quote("xmum", "text") . "," .
        $ilDB->quote("Plugin MumieTask", "text") . "," .
        $ilDB->quote(-1, "integer") . "," .
        $ilDB->quote(ilUtil::now(), "timestamp") . "," .
        $ilDB->quote(ilUtil::now(), "timestamp") .
        ")");
}

/**
 * Add new RBAC operations
 */
$operations = array('read_learning_progress');
foreach ($operations as $operation) {
    $query = "SELECT ops_id FROM rbac_operations WHERE operation = " . $ilDB->quote($operation, 'text');
    $res = $ilDB->query($query);
    $row = $ilDB->fetchObject($res);
    $ops_id = $row->ops_id;

    $query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ("
    . $ilDB->quote($typ_id, 'integer') . ","
    . $ilDB->quote($ops_id, 'integer') . ")";
    $ilDB->manipulate($query);
}

?>
<#7>
<?php
if (!$ilDB->tableExists('xmum_id_hashes')) {
    $fieldsHashes = array(
        'usr_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
        ),
        'hash' => array(
            'type' => 'text',
            'length' => '128',
            'notnull' => true,
        ),
    );
    $ilDB->createTable("xmum_id_hashes", $fieldsHashes);
    $ilDB->addPrimaryKey("xmum_id_hashes", array("usr_id"));
}

?>
<#8>
<?php
/**
 * We want to have permissions set to reasonable values by default for all newly create MumieTasks. We are using repobj Test as template and just copy theirs.
 */
$query = "SELECT * FROM rbac_templates WHERE type='xmum' AND parent= " . $ilDB->quote(ROLE_FOLDER_ID, 'integer');
if (!$ilDB->fetchAssoc($ilDB->query($query))) {
    $query = 'SELECT * FROM rbac_templates WHERE type = "tst" and parent =' . $ilDB->quote(ROLE_FOLDER_ID, 'integer');
    $result = $ilDB->query($query);
    while ($row = $ilDB->fetchAssoc($result)) {
        $query = 'INSERT INTO rbac_templates (rol_id,type,ops_id,parent) ' .
        'VALUES (' .
        $ilDB->quote($row['rol_id'], 'integer') . "," .
        $ilDB->quote("xmum", 'text') . "," .
        $ilDB->quote($row['ops_id'], 'integer') . "," .
        $ilDB->quote($row['parent'], 'integer') . ")";
        $ilDB->manipulate($query);
    }
}
?>