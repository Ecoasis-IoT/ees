<?php

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba00114df/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjRmZTkwNGRmLTZlN2UtNDYzZi04Njg2LTEzM2RhZTc2OTg5NCIsInR5cCI6ImtleSJ9.n-SkdFoM-ubP41jsrFoueiQfivocVOqMz_50D6Ddkro',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"zwcAAxHzCxAC0AcAgxELDBAC0QcAgxErsBAC0gcAAxLzCxEC0wcAgxILDBEClACDEis=\",\n   \"fPort\": 1\n  }\n}");
    
$response = curl_exec($ch);

curl_close($ch);

$res = json_decode($response, true);

print_r($res);


sleep(20);


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba00114df/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjRmZTkwNGRmLTZlN2UtNDYzZi04Njg2LTEzM2RhZTc2OTg5NCIsInR5cCI6ImtleSJ9.n-SkdFoM-ubP41jsrFoueiQfivocVOqMz_50D6Ddkro',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"lAOwEQLVBwADE/MLEgLWBwCDEwsMEgLXBwCDEyuwEgLYBwADFPMLEwLZBwCDFAsMEwI=\",\n   \"fPort\": 1\n  }\n}");
    
$response = curl_exec($ch);

curl_close($ch);

$res = json_decode($response, true);

print_r($res);


sleep(20);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba00114df/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjRmZTkwNGRmLTZlN2UtNDYzZi04Njg2LTEzM2RhZTc2OTg5NCIsInR5cCI6ImtleSJ9.n-SkdFoM-ubP41jsrFoueiQfivocVOqMz_50D6Ddkro',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"2gcAgxQrsBMC2wcAAxXzCxQC3AcAgxULDBQC3QcAgxUrsBQC3gcAAxbzCxUCnwCDFgs=\",\n   \"fPort\": 1\n  }\n}");
    
$response = curl_exec($ch);

curl_close($ch);

$res = json_decode($response, true);

print_r($res);


sleep(20);


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba00114df/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjRmZTkwNGRmLTZlN2UtNDYzZi04Njg2LTEzM2RhZTc2OTg5NCIsInR5cCI6ImtleSJ9.n-SkdFoM-ubP41jsrFoueiQfivocVOqMz_50D6Ddkro',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"nwMMFQLgBwCDFiuwFQLhBwADF/MLFgLiBwCDFwsMFgLjBwCDFyuwFgLkBwADGPMLFwI=\",\n   \"fPort\": 1\n  }\n}");
    
$response = curl_exec($ch);

curl_close($ch);

$res = json_decode($response, true);

print_r($res);


sleep(20);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba00114df/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjRmZTkwNGRmLTZlN2UtNDYzZi04Njg2LTEzM2RhZTc2OTg5NCIsInR5cCI6ImtleSJ9.n-SkdFoM-ubP41jsrFoueiQfivocVOqMz_50D6Ddkro',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"5QcAgxgLDBcC5gcAgxgrsBcC5wcAAxnzCxgC6AcAgxkLDBgC6QcAgxkrsBgCqgADGgs=\",\n   \"fPort\": 1\n  }\n}");

$response = curl_exec($ch);

curl_close($ch);

$res = json_decode($response, true);

print_r($res);


sleep(20);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba00114df/queue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjRmZTkwNGRmLTZlN2UtNDYzZi04Njg2LTEzM2RhZTc2OTg5NCIsInR5cCI6ImtleSJ9.n-SkdFoM-ubP41jsrFoueiQfivocVOqMz_50D6Ddkro',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"qgMMGQLrBwCDGoMMGQTsBwCDGvMLGQLtBwCDGtsLGQI=\",\n   \"fPort\": 1\n  }\n}");

$response = curl_exec($ch);

curl_close($ch);

$res = json_decode($response, true);

print_r($res);

?>