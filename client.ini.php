; <?php exit; ?>
[server]
start = ./login.php
[client]
name = default
description =
access = 1
[db]
type = innodb
host = mysql
user = root
pass = example
name = ilias
port = 3306
[auth]
table = usr_data
usercol = login
passcol = passwd
[language]
default = en
path = ./lang
[layout]
skin = default
style = delos
[session]
expire = 1800
[system]
ROOT_FOLDER_ID = 1
SYSTEM_FOLDER_ID = 9
ROLE_FOLDER_ID = 8
MAIL_SETTINGS_ID = 12
MAXLENGTH_OBJ_TITLE = 65
MAXLENGTH_OBJ_DESC = 123
DEBUG = 0
DEVMODE = 1
[cache]
activate_global_cache = 0
global_cache_service_type = 0
