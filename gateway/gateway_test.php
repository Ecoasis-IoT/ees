<?php

// Placeholder values - replace with your actual ChirpStack details
$chirpstackHost = 'http://195.35.48.27:8090'; // e.g., "https://chirpstack.example.com"
$tenantId = 'e801c4e7-dd19-4128-a4c3-543bbca0088c';           // e.g., "b827ebfffe1ca5b0"
$apiToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6ImM4OTYzMGVkLWEwN2UtNGIzYi04ZWVkLWFmMTMzNTlkOTliNyIsInR5cCI6ImtleSJ9.rRzCp9GjGhvmSJ3sH6ZqNCd2Ce9DCk9XNcDfMbR0DDc';// ChirpStack API token


// ===== API Endpoint to list gateways =====
$url = "$chirpstackHost/api/gateways?limit=10&offset=0&tenant_id=$tenantId";

// ===== HTTP Headers =====
$headers = [
    "Accept: application/json",
    "Grpc-Metadata-Authorization: Bearer $apiToken"
];

function formatIsoDatetime($isoDatetime) {
    // Create a DateTime object from the ISO 8601 string
    $date = new DateTime($isoDatetime);

    // Set timezone to Mauritius
    $date->setTimezone(new DateTimeZone('Indian/Mauritius'));

    // Return formatted date string
    return $date->format('Y-m-d H:i:s');
}



// ===== Init and Execute cURL Request =====
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Optional for self-signed certs (dev only)
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

// ===== Handle Response =====
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
} else {
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (!empty($data['result'])) {
            echo "Gateways and Last Seen Times:\n\n";
            foreach ($data['result'] as $gateway) {
                $id = $gateway['gatewayId'] ?? 'N/A';
                $name = $gateway['name'] ?? 'N/A';
                $lastSeen = $gateway['lastSeenAt'] ?? "N/A";
                $state = $gateway['state'] ?? "N/A";
                
                if ($lastSeen) {
                    $lastSeenFormatted = formatIsoDatetime($lastSeen);
                } else {
                    $lastSeenFormatted = 'Never seen';
                }
                
                echo "Gateway ID: $id\n";
                echo "Name: $name\n";
                echo "Last Seen: $lastSeenFormatted\n";
                echo "State: $state\n";
                echo "--------------------------\n";
            }
        } else {
            echo "No gateways found for tenant.\n";
        }
    } else {
        echo "Failed to fetch gateways. HTTP Status: $httpCode\n";
        echo "Response: $response\n";
    }
}

curl_close($ch);
?>
