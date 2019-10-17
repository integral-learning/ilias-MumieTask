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
        // user id
        'user' => array(
            'type' => 'integer',
            'length' => 8,
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
            'default' => '60',
        ),
        'lp_modus' => array(
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
    $ilDB->manipulate("INSERT INTO xmum_admin_settings "
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
$rec = $ilDB->fetchAssoc($set);
$typ_id = $rec["obj_id"];

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