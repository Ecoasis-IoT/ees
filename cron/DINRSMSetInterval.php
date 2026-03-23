<?php

//Set the Interval to 1H as from 03:59

//Jumbo Phoenix

//Controller 1

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba0011543/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjRmZTkwNGRmLTZlN2UtNDYzZi04Njg2LTEzM2RhZTc2OTg5NCIsInR5cCI6ImtleSJ9.n-SkdFoM-ubP41jsrFoueiQfivocVOqMz_50D6Ddkro',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"TjwA\",\n   \"fPort\": 1\n  }\n}");
$response = curl_exec($ch);

curl_close($ch);

$res = json_decode($response, true);

// print_r($res);

//Controller 2

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba00114df/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjRmZTkwNGRmLTZlN2UtNDYzZi04Njg2LTEzM2RhZTc2OTg5NCIsInR5cCI6ImtleSJ9.n-SkdFoM-ubP41jsrFoueiQfivocVOqMz_50D6Ddkro',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"TjwA\",\n   \"fPort\": 1\n  }\n}");
$response = curl_exec($ch);

curl_close($ch);

$res = json_decode($response, true);

// print_r($res);

//Home & Leisure - Controller 1

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba001154c/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjM5MTQ4MGQwLWJjMjgtNGZiOS1hZDMwLTI1OGIyODUwNWM5YiIsInR5cCI6ImtleSJ9.Mk83_REHFkCUbcYOKKeZjJCx2cAhteYLuid7_7vEaC8',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"TjwA\",\n   \"fPort\": 1\n  }\n}");
$response = curl_exec($ch);

curl_close($ch);

$res = json_decode($response, true);

// print_r($res);


//BoValon 

//PVDB1

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba0011541/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6Ijc4OTY3ZDFlLTEzMGEtNDdhMy1iNDY2LTc4NmM4ZDhhYmZmYSIsInR5cCI6ImtleSJ9.AkxIN3b9wOx6NGO3AHGzBXUxRRxUNkdB3KvIRRxagfI',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"TjwA\",\n   \"fPort\": 1\n  }\n}");
$response = curl_exec($ch);

curl_close($ch);

$res = json_decode($response, true);

// print_r($res);


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba001141d/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6Ijc4OTY3ZDFlLTEzMGEtNDdhMy1iNDY2LTc4NmM4ZDhhYmZmYSIsInR5cCI6ImtleSJ9.AkxIN3b9wOx6NGO3AHGzBXUxRRxUNkdB3KvIRRxagfI',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"TjwA\",\n   \"fPort\": 1\n  }\n}");
$response = curl_exec($ch);

curl_close($ch);

$res = json_decode($response, true);

// print_r($res);

//PVDB2

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba0011573/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6Ijc4OTY3ZDFlLTEzMGEtNDdhMy1iNDY2LTc4NmM4ZDhhYmZmYSIsInR5cCI6ImtleSJ9.AkxIN3b9wOx6NGO3AHGzBXUxRRxUNkdB3KvIRRxagfI',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"TjwA\",\n   \"fPort\": 1\n  }\n}");
$response = curl_exec($ch);

curl_close($ch);

$res = json_decode($response, true);

// print_r($res);


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba001141b/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6Ijc4OTY3ZDFlLTEzMGEtNDdhMy1iNDY2LTc4NmM4ZDhhYmZmYSIsInR5cCI6ImtleSJ9.AkxIN3b9wOx6NGO3AHGzBXUxRRxUNkdB3KvIRRxagfI',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"TjwA\",\n   \"fPort\": 1\n  }\n}");
$response = curl_exec($ch);

curl_close($ch);

$res = json_decode($response, true);

// print_r($res);


//Riche Terre Mall

//TX1

//Controller 1

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba0011428/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjRhOGM3MWY2LTZiM2ItNDM1Ny1iNjVkLTRiMDgxYWI1MzE0MiIsInR5cCI6ImtleSJ9.7nroIXftRx4ZhhNLGJ8oXnL5tXjw4cRa79AgCBuu0yk',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"TjwA\",\n   \"fPort\": 1\n  }\n}");
$response = curl_exec($ch);

curl_close($ch);



//Controller 2

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba00115b0/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjRhOGM3MWY2LTZiM2ItNDM1Ny1iNjVkLTRiMDgxYWI1MzE0MiIsInR5cCI6ImtleSJ9.7nroIXftRx4ZhhNLGJ8oXnL5tXjw4cRa79AgCBuu0yk',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"TjwA\",\n   \"fPort\": 1\n  }\n}");
$response = curl_exec($ch);

curl_close($ch);



//TX2

//Controller 1

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba0011542/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjRhOGM3MWY2LTZiM2ItNDM1Ny1iNjVkLTRiMDgxYWI1MzE0MiIsInR5cCI6ImtleSJ9.7nroIXftRx4ZhhNLGJ8oXnL5tXjw4cRa79AgCBuu0yk',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"TjwA\",\n   \"fPort\": 1\n  }\n}");
$response = curl_exec($ch);

curl_close($ch);

//TX3

//Controller 1

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba0011587/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjRhOGM3MWY2LTZiM2ItNDM1Ny1iNjVkLTRiMDgxYWI1MzE0MiIsInR5cCI6ImtleSJ9.7nroIXftRx4ZhhNLGJ8oXnL5tXjw4cRa79AgCBuu0yk',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"TjwA\",\n   \"fPort\": 1\n  }\n}");
$response = curl_exec($ch);

curl_close($ch);




date_default_timezone_set('Indian/Mauritius');
$myfile1 = fopen("DINRSM INTERVAL Log.txt", "a") or die("Unable to open file!");
fwrite($myfile1, date("Y-m-d H:i:s") . "\n");
fclose($myfile1);

?>