<?php 

(function(){
    
    if(_is_installed()) return;
    
    include "../api/helpers/sqli.php";
    
    if(!_exec("DELETE FROM user WHERE admin = 1")){
        echo "ERRO - Não foi possível deletar antigos administradores";
        exit;
    }
    
    $arquivo = '../admin.txt';
    if(!file_exists($arquivo)){
        echo "ERRO - O arquivo 'admin.txt' não foi criado na raiz da aplicação";
        exit;
    }

    $content = file_get_contents($arquivo);
    $parts = explode("/", $content);
    $email = $parts[0];
    $senha = password_hash($parts[1], PASSWORD_DEFAULT);
  
    $insert = "INSERT INTO user (nome, slug, email, senha, cargo, admin, ativo)
                VALUES ('admin', '$email', '$email', '$senha', 'Admin', 1, 1)";
    
    if(!($id = _exec($insert, true)) || !_exec("INSERT INTO user_admin (user_id) VALUES ($id)")){
        echo "ERRO - Não foi possível criar um novo administrador";
        exit;
    }

    // altera arquivo de configuração
    $confgFile = '../conf.yaml';
    $contentAr = explode("\r\n", file_get_contents($confgFile));
    $contentFi = "";
    foreach ($contentAr as $linha) {
        if(strpos($linha, 'installed') !== false){
            $contentFi .= "installed: true\r\n";
            continue;
        }
        $contentFi .= $linha."\r\n";
    }
    
    file_put_contents($confgFile, $contentFi);
    echo "Admin criado com sucesso!";

})();
