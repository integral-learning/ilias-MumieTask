<?php

function open_mumie_task(
                         string $loginurl,
                         string $userId,
                         string $token,
                         string $org,
                         string $problempath,
                         string $lang,
                         string $deadlineinmilliseconds,
                         string $signeddata,
                     ): string {
                         return "<form id='mumie_sso_form' name='mumie_sso_form' method='post' action='{$loginurl}'>
                                     <input type='hidden' name='userId' id='userId' type ='text' value='{$userId}'/>
                                     <input type='hidden' name='token' id='token' type ='text' value='{$token}'/>
                                     <input type='hidden' name='org' id='org' type ='text' value='{$org}'/>
                                     <input type='hidden' name='path' id='problempath' type ='text'
                                     value='{$problempath}'/>
                                     <input type='hidden' name='lang' id='lang' type ='text' value='{$gradingtype}'/>
                                     <input type='hidden' name='problemLang' id='problemLang' type ='text' value='{$lang}'/>
                                     <input type='hidden' name='origin' id='origin' type ='text' value='{$origin}'/>
                                     <input type='hidden' name='deadline' id='deadline' type='text' value='{$deadlineinmilliseconds}'>
                                     <input type='hidden' name='deadlineSignature' id='deadlineSignature' type='text' value='{$signeddata}'>
                                 </form>
                                 <script>
                                 document.forms['mumie_sso_form'].submit();
                                 </script>
                             ";
                     }
$queries = array();
parse_str($_SERVER['QUERY_STRING'], $queries);

$loginurl = $queries['loginurl'];
$userId = $queries['userId'];
$token = $queries['token'];
$org = $queries['org'];
$problempath = $queries['problempath'];
$lang = $queries['lang'];
$deadlineinmilliseconds = $queries['deadlineinmilliseconds'];
$signeddata = $queries['signeddata'];


echo open_mumie_task($loginurl, $userId, $token, $org, $problempath, $lang ,
$deadlineinmilliseconds, $signeddata );