<?php
/**
 * TerraFusion Chat API - Gemini Middleman
 * Features: Secure API key handling, Rate limiting, Persona enforcement.
 */

session_start();
header('Content-Type: application/json');

// --- 1. Rate Limiting (10 requests per minute) ---
$now = time();
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// Remove timestamps older than 60 seconds
$_SESSION['chat_history'] = array_filter($_SESSION['chat_history'], function($ts) use ($now) {
    return ($now - $ts) < 60;
});

if (count($_SESSION['chat_history']) >= 10) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please wait a minute before sending more messages.']);
    exit;
}
$_SESSION['chat_history'][] = $now;


// --- 2. Get Input ---
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';
$menuContext = $input['menu'] ?? ''; // Menu data from frontend

if (empty($userMessage)) {
    echo json_encode(['error' => 'No message provided']);
    exit;
}

// --- 3. Configuration ---
// ⚠️ USER: Replace 'YOUR_ACTUAL_API_KEY_HERE' with your key from https://aistudio.google.com/
$apiKey = 'AIzaSyCPAoceSfPZPuKLvEIoX6erW74a1isoRus'; 
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$apiKey";

// --- 4. System Instruction / Persona ---
$systemInstruction = "You are Chef Mahmoud, the friendly and professional head chef of TerraFusion restaurant. 
Your goal is to suggest meals, answer menu questions, and help with reservations.
Rules:
1. Use a warm, culinary persona. Use phrases like 'Bon appétit', 'Freshly prepared', etc.
2. Be bilingual: Respond in the language the user speaks (English or Arabic).
3. Strictly only discuss TerraFusion and its menu. If asked about other topics, politely steer back to food.
4. If a user wants to order or add an item to their cart, use the provided tool call 'add_to_cart'.
5. Here is the current menu knowledge: " . json_encode($menuContext);

// --- 5. Prepare Payload ---
$data = [
    "system_instruction" => [
        "parts" => [
            ["text" => $systemInstruction]
        ]
    ],
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => $userMessage]
            ]
        ]
    ],
    "tools" => [
        [
            "function_declarations" => [
                [
                    "name" => "add_to_cart",
                    "description" => "Adds a specific meal to the user's shopping cart based on its meal ID.",
                    "parameters" => [
                        "type" => "object",
                        "properties" => [
                            "meal_id" => [
                                "type" => "integer",
                                "description" => "The unique ID of the meal to add."
                            ],
                            "meal_name" => [
                                "type" => "string",
                                "description" => "The name of the meal being added."
                            ]
                        ],
                        "required" => ["meal_id", "meal_name"]
                    ]
                ],
                [
                    "name" => "create_reservation",
                    "description" => "Books a table for a customer with specific date, time, and party size.",
                    "parameters" => [
                        "type" => "object",
                        "properties" => [
                            "reservation_date" => [
                                "type" => "string",
                                "description" => "The date of the reservation (YYYY-MM-DD)."
                            ],
                            "reservation_time" => [
                                "type" => "string",
                                "description" => "The time of the reservation (HH:MM)."
                            ],
                            "party_size" => [
                                "type" => "integer",
                                "description" => "The number of people for the reservation."
                            ],
                            "notes" => [
                                "type" => "string",
                                "description" => "Any special requests or notes."
                            ]
                        ],
                        "required" => ["reservation_date", "reservation_time", "party_size"]
                    ]
                ]
            ]
        ]
    ]
];

// --- 6. Send Request ---
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Often needed on local XAMPP/Windows

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// --- 7. Handle Result ---
if ($httpCode === 200 && $response) {
    $decoded = json_decode($response, true);
    $candidate = $decoded['candidates'][0] ?? null;
    
    if (!$candidate) {
        echo json_encode(['reply' => "I apologize, I'm having trouble thinking right now."]);
        exit;
    }

    $parts = $candidate['content']['parts'] ?? [];
    $reply = "";
    $toolCalls = [];

    foreach ($parts as $part) {
        if (isset($part['text'])) {
            $reply .= $part['text'];
        }
        if (isset($part['functionCall'])) {
            $toolCalls[] = $part['functionCall'];
        }
    }

    echo json_encode([
        'reply' => $reply ?: "I've processed your request.",
        'tool_calls' => $toolCalls
    ]);

} else {
    echo json_encode([
        'error' => 'Mahmoud is currently busy in the kitchen (API Error).', 
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'details' => json_decode($response, true) ?: $response
    ]);
}
?>
