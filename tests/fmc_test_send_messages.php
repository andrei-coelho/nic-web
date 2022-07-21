<?php

// echo "oi"; exit;

include "../api/external_libs/google-client/vendor/autoload.php";

$url = 'https://fcm.googleapis.com/v1/projects/quickstart-1554606735377/messages:send';

$devices = [
    "eksrd5EMsjaZQxRVcFLE4Q:APA91bHORrgQWJPwIikqlVperQJ8sXnv13WEfoHll3BsuH0ksJDx42Tv2dCJdU1WjcPENJ-CTSvAUylUPt4unylpM3xkrbpHRC29qjBcADxu7w0gufLVA1_kUqyzpuFcDoEZP7BTmR4K",
    "czCMTWzYqsCIZY8_jS8BIa:APA91bECtwGUuE5oCUr0FYlxEl6C53TLJWvBjq5rnDnXZz31zhs9EVgGBp57Lk_F3yzEN-WSd5kYingYH_78u-YQ1He0glJ7nW0sYERG1usklcsZBPUe6JqLTwKFjgH1fQuLX2DMVx59"
];

$message = [
    "title" => "title test 1",
    "body"  => "This is an FCM notification message!"
];

$headers = [
	'Authorization: Bearer '._fmc_key(), 
	'Content-Type: application/json',
    'accept: application/json'
];

$multiCurl = [];
$result = [];
$mh = curl_multi_init();
$i = 0;

foreach ($devices as $device) {
    
    $multiCurl[$i] = curl_init();
    
    $fields = [
        "message" => [
            "token" => $device,
            "notification" => $message
        ]
    ];

    curl_setopt( $multiCurl[$i],CURLOPT_URL,$url);
    curl_setopt( $multiCurl[$i],CURLOPT_POST,true);
    curl_setopt( $multiCurl[$i],CURLOPT_HTTPHEADER,$headers);
    curl_setopt( $multiCurl[$i],CURLOPT_RETURNTRANSFER,true);
    curl_setopt( $multiCurl[$i],CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt( $multiCurl[$i],CURLOPT_POSTFIELDS,json_encode($fields));
    
    curl_multi_add_handle($mh, $multiCurl[$i]);
    $i++;

}

$index=null;
do {
    curl_multi_exec($mh,$index);
} while($index > 0);
// get content and remove handles
foreach($multiCurl as $k => $ch) {
  $result[$k] = curl_multi_getcontent($ch);
  curl_multi_remove_handle($mh, $ch);
}
// close
curl_multi_close($mh);

print_r($result);