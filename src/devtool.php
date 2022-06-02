<?php 

(function(){

    $request = _request(['req', 'lib', 'file']);

    include "../api/helpers/sqli.php";

    $lib  = $request->vars['lib'];
    $file = "../dev_tools/".$lib."/".$request->vars['file'].".php";

    include (file_exists($file) ? $file : "../tests/error.php");

})();

exit;