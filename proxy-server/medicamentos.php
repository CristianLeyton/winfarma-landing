<?php
// proxy.php

// 1. Configurar CORS (para permitir que tu frontend acceda a este proxy)
header("Access-Control-Allow-Origin: *"); // Permite cualquier origen. Para producción, cámbialo a tu dominio específico: "https://chat.chatbotapp.ai"
header("Access-Control-Allow-Headers: Content-Type"); // Permite el encabezado Content-Type

// Manejar la solicitud OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. Obtener los datos de la solicitud POST del frontend
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Verificar si se recibió searchdata
if (!isset($data['searchdata'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'searchdata is required in the request body']);
    exit();
}

$searchData = $data['searchdata'];

// 3. URL de la API externa
$targetUrl = 'https://cnpm.msal.gov.ar/api/vademecum';

// 4. Configurar la solicitud cURL para la API externa
$ch = curl_init($targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Devuelve la respuesta como string
curl_setopt($ch, CURLOPT_POST, true);           // Es una solicitud POST
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['searchdata' => $searchData]));

// Desactivar la verificación SSL para depuración (NO RECOMENDADO EN PRODUCCIÓN)
// Si tienes problemas de certificado, esto podría ser una solución temporal,
// pero la mejor práctica es configurar cURL para verificar certificados válidos.
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// 5. Ejecutar la solicitud cURL
$response = curl_exec($ch);

// 6. Manejar errores de cURL
if (curl_errno($ch)) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'cURL Error: ' . curl_error($ch)]);
    curl_close($ch);
    exit();
}

// 7. Obtener el código de respuesta HTTP de la API externa
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

// 8. Devolver la respuesta de la API externa al frontend
http_response_code($httpCode); // Establecer el código de respuesta de la API externa
header('Content-Type: application/json'); // Asegurar que la respuesta sea JSON
echo $response;

?>