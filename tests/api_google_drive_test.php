<?php 

ini_set('memory_limit', '4096M');

include "../api/external_libs/google-client/vendor/autoload.php";

$serviceDrive = new Google_Service_Drive(getClient());

//upload_file($serviceDrive, '../upload_for_test/', 'projeto.pdf');
list_files($serviceDrive);
download_file($serviceDrive);
# 1kHg88VxHq0rf7wdb_rVDGQxDDAQAnlCJ
// download_file($serviceDrive);

function download_file($serviceDrive, $id = ""){
    
    echo "<pre>";
    var_dump($serviceDrive->files);
    echo "</pre>";
}

function list_files($serviceDrive){
    $optParams = array(
        'pageSize' => 10,
        'fields' => 'files(id, name, webViewLink)',
        #'driveId' => '1Lm5EnESfy26OMcHEbxJ5YeU7PueNeBH6',
        'includeItemsFromAllDrives' => true,
        'pageSize' => 10,
        'supportsAllDrives' => true,
        'q'=> '"1Lm5EnESfy26OMcHEbxJ5YeU7PueNeBH6" in parents',
        'fields' => 'nextPageToken, files(id, name)'
    );
    $results = $serviceDrive->files->listFiles($optParams);
    echo "<pre>";
    var_dump($results);
    echo "</pre>";
}

function upload_file($serviceDrive, $pasta, $arquivo){    

    //Essa é a pasta destino do Google Drive
    $parentId   = '1Lm5EnESfy26OMcHEbxJ5YeU7PueNeBH6';

    $file_path = $pasta.'/'.$arquivo;
    //Conecta no Drive da sua conta
    $file = new Google_Service_Drive_DriveFile([
        'name' => $arquivo,
        'driveId' => '1Lm5EnESfy26OMcHEbxJ5YeU7PueNeBH6',
        'includeItemsFromAllDrives' => true,
        'parents' => [$parentId]
    ]);
    //Cria o arquivo no GDrive
    $fileGen = $serviceDrive->files->create(
        $file,
        [
            'data'          => file_get_contents($file_path),
            'mimeType'      => 'application/octet-stream',
            'supportsAllDrives' => true,
            'fields' => 'id',
            'uploadType'    => 'resumable'
        ]
    );

    echo "File Id: ". $fileGen -> id;
    /*
    echo "<pre>";
    var_dump($fileGen);
    echo "</pre>";
    */

}


function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Projeto NIC test');
    $client->setScopes(Google_Service_Drive::DRIVE);
    $client->setAuthConfig('../credentials.json');
    $client->setSubject('main-account-service-drive@projeto-nic-test.iam.gserviceaccount.com');
    $client->setAccessType('offline');
    return $client;
    
}

