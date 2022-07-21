<?php 

$url = 'https://fcm.googleapis.com/fcm/send';

$fields = [
    'registration_ids' => [
        "eksrd5EMsjaZQxRVcFLE4Q:APA91bHORrgQWJPwIikqlVperQJ8sXnv13WEfoHll3BsuH0ksJDx42Tv2dCJdU1WjcPENJ-CTSvAUylUPt4unylpM3xkrbpHRC29qjBcADxu7w0gufLVA1_kUqyzpuFcDoEZP7BTmR4K",
        "czCMTWzYqsCIZY8_jS8BIa:APA91bECtwGUuE5oCUr0FYlxEl6C53TLJWvBjq5rnDnXZz31zhs9EVgGBp57Lk_F3yzEN-WSd5kYingYH_78u-YQ1He0glJ7nW0sYERG1usklcsZBPUe6JqLTwKFjgH1fQuLX2DMVx59"
    ],
    "notification" => [
        "title" => "title test 1",
        "body"  => "This is an FCM notification message!"
    ]
];

$headers = [
    'Authorization: key=' . _fmc_key(),
    'Content-Type: application/json'
];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

$result = curl_exec($ch);
if ($result === FALSE) {
    die('Curl failed: ' . curl_error($ch));
}

curl_close($ch);