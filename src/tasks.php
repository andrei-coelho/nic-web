<?php 

// secret for sistem 
# 45b63efdcd5722c2ab064ccb71235429d7123f3a7d569d7575012dbcf5a5d824313ac09ea4602127b857f7bdddbd8d4eb435e3f7dd307144ebfe16c2a143b254

(function(){

    include "../api/helpers/sqli.php";

    $request = _request(['req', 'task', 'secret']);

    if(!$request->vars['task'] || 
        (_is_in_production() 
        && (!$request->vars['secret'] 
        ||  $request->vars['secret'] != '45b63efdcd5722c2ab064ccb71235429d7123f3a7d569d7575012dbcf5a5d824313ac09ea4602127b857f7bdddbd8d4eb435e3f7dd307144ebfe16c2a143b254')))
    die('Permission Denied');

    $file_task = "../tasks/".$request->vars['task'].".php";
    if(!file_exists($file_task)) die('Permission Denied');

    include $file_task;

})();