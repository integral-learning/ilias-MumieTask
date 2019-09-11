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
        'name' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        ),
        'taskurl' => array(
            'type' => 'text',
            'notnull' => true,
        ),
        'launchcontainer' => array(
            'type' => 'integer',
            'length' => '4',
            'notnull' => true,
        ),
        'mumie_course' => array(
            'type' => 'text',
            'length' => '255',
            'notnull' => true,
        ),
        'language' => array(
            'type' => 'text',
            'length' => '255',
            'notnull' => true,
        ),
        'server' => array(
            'type' => 'text',
            'length' => '255',
            'notnull' => true,
        ),
        'mumie_coursefile' => array(
            'type' => 'text',
            'length' => '255',
            'notnull' => true,
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