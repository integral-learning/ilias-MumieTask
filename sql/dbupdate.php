<#1>
<?php
if (!$ilDB->tableExists('xmum_sso_tokens')) {
    $fieldsToken = [
        'id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
        ],
        'token' => [
            'type' => 'text',
            'length' => 30,
            'notnull' => true,
        ],
        // hashed user id
        'user' => [
            'type' => 'text',
            'length' => 128,
            'notnull' => true,
        ],
        'timecreated' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
        ],
    ];

    $ilDB->createTable('xmum_sso_tokens', $fieldsToken);
    $ilDB->addPrimaryKey('xmum_sso_tokens', ['id']);
    $ilDB->createSequence('xmum_sso_tokens');
}
?>
<#2>
<?php
if (!$ilDB->tableExists('xmum_mumie_task')) {
    $fieldsMumie = [
        'id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
        ],
        'taskurl' => [
            'type' => 'text',
        ],
        'launchcontainer' => [
            'type' => 'integer',
            'length' => '4',
        ],
        'mumie_course' => [
            'type' => 'text',
            'length' => '255',
        ],
        'language' => [
            'type' => 'text',
            'length' => '255',
        ],
        'server' => [
            'type' => 'text',
            'length' => '255',
        ],
        'mumie_coursefile' => [
            'type' => 'text',
            'length' => '255',
        ],
        'passing_grade' => [
            'type' => 'integer',
            'length' => '4',
            'default' => 60,
        ],
        'lp_modus' => [
            'type' => 'integer',
            'length' => '2',
            'default' => '1',
        ],
        'online' => [
            'type' => 'integer',
            'length' => '2',
            'default' => '0',
        ],
    ];
    $ilDB->createTable('xmum_mumie_task', $fieldsMumie);
    $ilDB->addPrimaryKey('xmum_mumie_task', ['id']);
}
?>
<#3>
<?php
if (!$ilDB->tableExists('xmum_mumie_servers')) {
    $fieldsServer = [
        'server_id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
            'default' => 0,
        ],
        'name' => [
            'type' => 'text',
            'length' => 30,
            'notnull' => true,
        ],
        'url_prefix' => [
            'type' => 'text',
            'length' => 200,
            'notnull' => true,
        ],
    ];
    $ilDB->createTable('xmum_mumie_servers', $fieldsServer);
    $ilDB->addPrimaryKey('xmum_mumie_servers', ['server_id']);
    $ilDB->createSequence('xmum_mumie_servers');
}
?>
<#4>
<?php
if (!$ilDB->tableExists('xmum_admin_settings')) {
    $fieldsAminSettings = [
        'id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
        ],
        'share_first_name' => [
            'type' => 'integer',
            'default' => 'false',
            'length' => '1',
        ],
        'share_last_name' => [
            'type' => 'integer',
            'default' => 'false',
            'length' => '1',
        ],
        'share_email' => [
            'type' => 'integer',
            'default' => 'false',
            'length' => '1',
        ],
        'api_key' => [
            'type' => 'text',
            'length' => '255',
        ],
        'org' => [
            'type' => 'text',
            'length' => '7',
        ],
        'problem_selector_url' => [
            'type' => 'text',
            'length' => '255',
        ],
    ];
    $ilDB->createTable('xmum_admin_settings', $fieldsAminSettings);
    $ilDB->addPrimaryKey('xmum_admin_settings', ['id']);
}
?>
<#5>
<?php
$query = 'SELECT * FROM xmum_admin_settings';
$result = $ilDB->query($query);
if ($ilDB->numRows($result) < 1) {
    $ilDB->manipulate(
        'INSERT INTO xmum_admin_settings '
        . '(id, share_first_name, share_last_name, share_email, api_key, org, problem_selector_url) VALUES('
        . $ilDB->quote(1, 'integer') . ','
        . $ilDB->quote(0, 'integer') . ','
        . $ilDB->quote(0, 'integer') . ','
        . $ilDB->quote(0, 'integer') . ','
        . $ilDB->quote('', 'text') . ','
        . $ilDB->quote('', 'text') . ','
        . $ilDB->quote('https://pool.mumie.net', 'text')
        . ')',
    );
}
?>
<#6>
<?php
$set = $ilDB->query("SELECT obj_id FROM object_data WHERE type='typ' AND title = 'xmum'");
if ($rec = $ilDB->fetchAssoc($set)) {
    $typ_id = $rec['obj_id'];
} else {
    $typ_id = $ilDB->nextId('object_data');
    $ilDB->manipulate('INSERT INTO object_data ' .
        '(obj_id, type, title, description, owner, create_date, last_update) VALUES (' .
        $ilDB->quote($typ_id, 'integer') . ',' .
        $ilDB->quote('typ', 'text') . ',' .
        $ilDB->quote('xmum', 'text') . ',' .
        $ilDB->quote('Plugin MumieTask', 'text') . ',' .
        $ilDB->quote(-1, 'integer') . ',' .
        $ilDB->quote(date('Y-m-d H:i:s'), 'timestamp') . ',' .
        $ilDB->quote(date('Y-m-d H:i:s'), 'timestamp') .
        ')');
}

/**
 * Add new RBAC operations.
 */
$operations = ['read_learning_progress'];
foreach ($operations as $operation) {
    $query = 'SELECT ops_id FROM rbac_operations WHERE operation = ' . $ilDB->quote($operation, 'text');
    $res = $ilDB->query($query);
    $row = $ilDB->fetchObject($res);
    $ops_id = $row->ops_id;

    $query = 'INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('
        . $ilDB->quote($typ_id, 'integer') . ','
        . $ilDB->quote($ops_id, 'integer') . ')';
    $ilDB->manipulate($query);
}

?>
<#7>
<?php
if (!$ilDB->tableExists('xmum_id_hashes')) {
    $fieldsHashes = [
        'usr_id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
        ],
        'hash' => [
            'type' => 'text',
            'length' => '128',
            'notnull' => true,
        ],
    ];
    $ilDB->createTable('xmum_id_hashes', $fieldsHashes);
    $ilDB->addPrimaryKey('xmum_id_hashes', ['usr_id']);
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
            $ilDB->quote($row['rol_id'], 'integer') . ',' .
            $ilDB->quote('xmum', 'text') . ',' .
            $ilDB->quote($row['ops_id'], 'integer') . ',' .
            $ilDB->quote($row['parent'], 'integer') . ')';
        $ilDB->manipulate($query);
    }
}
?>
<#9>
<?php
if (!$ilDB->tableColumnExists('xmum_mumie_task', 'privategradepool')) {
    $ilDB->addTableColumn(
        'xmum_mumie_task',
        'privategradepool',
        [
            'type' => 'integer',
            'length' => '2',
            'notnull' => true,
            'default' => '-1',
        ],
    );
}
$ilDB->manipulate('UPDATE xmum_mumie_task SET privategradepool = 0');
?>
<#10>
<?php
if ($ilDB->tableExists('xmum_id_hashes')) {
    $query = 'SELECT usr_id, hash FROM xmum_id_hashes';
    $result = $ilDB->query($query);
    $ilDB->dropTable('xmum_id_hashes');
}
$fieldsHashes = [
    'id' => [
        'type' => 'integer',
        'length' => 8,
        'notnull' => true,
    ],
    'usr_id' => [
        'type' => 'integer',
        'length' => 8,
        'notnull' => true,
    ],
    'hash' => [
        'type' => 'text',
        'length' => '143',
        'notnull' => true,
    ],
];
$ilDB->createTable('xmum_id_hashes', $fieldsHashes);
$ilDB->addPrimaryKey('xmum_id_hashes', ['id']);
$ilDB->createSequence('xmum_id_hashes');
if (!is_null($result)) {
    while ($row = $ilDB->fetchAssoc($result)) {
        $query = 'INSERT INTO xmum_id_hashes (id, usr_id, hash) VALUES (' .
            $ilDB->nextID('xmum_id_hashes') . ',' .
            $ilDB->quote($row['usr_id'], 'integer') . ',' .
            $ilDB->quote($row['hash'], 'text') . ')';
        $ilDB->manipulate($query);
    }
}
?>
<#11>
<?php
$ilDB->modifyTableColumn(
    'xmum_sso_tokens',
    'user',
    [
        'type' => 'text',
        'length' => 143,
        'notnull' => true,
    ],
);
?>
<#12>
<?php
if (!$ilDB->tableExists('xmum_grade_override')) {
    $gradeOverrideSettings = [
        'task_id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
        ],
        'usr_id' => [
            'type' => 'text',
            'length' => '143',
            'notnull' => true,
        ],
        'new_grade' => [
            'type' => 'integer',
            'length' => '3',
            'notnull' => true,
            'default' => '0',
        ],
    ];
    $ilDB->createTable('xmum_grade_override', $gradeOverrideSettings);
}
?>
<#13>
<?php
if (!$ilDB->tableExists('xmum_deadline_ext')) {
    $tableSettings = [
        'task_id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
        ],
        'usr_id' => [
            'type' => 'text',
            'length' => '143',
            'notnull' => true,
        ],
        'date' => [
            'type' => 'integer',
            'length' => '4',
            'notnull' => true,
        ],
    ];
    $ilDB->createTable('xmum_deadline_ext', $tableSettings);
}
?>
<#14>
<?php
if (!$ilDB->tableColumnExists('xmum_mumie_task', 'deadline')) {
    $ilDB->addTableColumn(
        'xmum_mumie_task',
        'deadline',
        [
            'type' => 'integer',
            'length' => '4',
            'notnull' => false,
        ],
    );

    $query = 'SELECT xmum_mumie_task.id, crs_items.timing_end FROM xmum_mumie_task JOIN object_reference ON xmum_mumie_task.id = object_reference.obj_id JOIN crs_items ON object_reference.ref_id = crs_items.obj_id WHERE crs_items.timing_type = 0';
    $result = $ilDB->query($query);
    while ($row = $ilDB->fetchAssoc($result)) {
        $ilDB->manipulate(
            'UPDATE xmum_mumie_task SET deadline = ' .
            $ilDB->quote($row['timing_end'], 'integer') .
            ' WHERE id = ' .
            $ilDB->quote($row['id'], 'integer'),
        );
    }
}
?>
<#15>
<?php
if (!$ilDB->tableColumnExists('xmum_mumie_task', 'worksheet')) {
    $ilDB->addTableColumn(
        'xmum_mumie_task',
        'worksheet',
        [
            'type' => 'text',
            'notnull' => false,
        ],
    );
}
?>
<#16>
<?php
if (!$ilDB->tableColumnExists('xmum_admin_settings', 'problem_selector_url')) {
    $ilDB->addTableColumn(
        'xmum_admin_settings',
        'problem_selector_url',
        [
            'type' => 'text',
            'length' => '255',
        ],
    );
    $ilDB->manipulate("UPDATE xmum_admin_settings SET problem_selector_url = 'https://pool.mumie.net'");
}
?>
<#17>
<?php
if (!$ilDB->tableExists('xmum_crypto_key')) {
    $ilDB->createTable('xmum_crypto_key', [
        'id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
        'private_key_pem' => ['type' => 'clob', 'notnull' => true],
        'created_at' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
    ]);
    $ilDB->addPrimaryKey('xmum_crypto_key', ['id']);
}
?>
<#18>
<?php
// The 'org' column was originally created with a length of 7 characters,
// which is too short for real-world org identifiers (e.g. "il112767").
$ilDB->modifyTableColumn('xmum_admin_settings', 'org', [
    'type' => 'text',
    'length' => 255,
]);
?>
<#19>
<?php
if (!$ilDB->tableColumnExists('xmum_mumie_task', 'timelimit')) {
    $ilDB->addTableColumn(
        'xmum_mumie_task',
        'timelimit',
        [
            'type' => 'integer',
            'length' => '4',
            'notnull' => false,
        ],
    );
}
?>
<#20>
<?php
foreach (['share_first_name', 'share_last_name', 'share_email'] as $column) {
    if ($ilDB->tableColumnExists('xmum_admin_settings', $column)) {
        $ilDB->dropTableColumn('xmum_admin_settings', $column);
    }
}
?>
