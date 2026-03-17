<?php
require_once 'config.php';

class HuiduService {
    
    // 100% Official API documentation Crypto logic
    private function encryptPayload($payload) {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return base64_encode(openssl_encrypt($json, 'AES-256-ECB', API_AES_KEY, OPENSSL_RAW_DATA));
    }

    private function decryptPayload($encrypted) {
        $decrypted = openssl_decrypt(base64_decode($encrypted), 'AES-256-ECB', API_AES_KEY, OPENSSL_RAW_DATA);
        return json_decode($decrypted, true);
    }

    private function sendRequest($endpoint, $data = [], $isPost = false) {
        $url = API_SERVER_URL . $endpoint;
        $ch = curl_init();

        if ($isPost) {
            $timestamp = (string)(time() * 1000);
            
            // 🔥 FIX: Huidu API রিকোয়ারমেন্ট অনুযায়ী এনক্রিপ্ট করার আগে পেলোডের ভেতরে agency_uid ঢোকাতে হবে
            $data['agency_uid'] = API_AGENCY_UID;
            $data['timestamp'] = $timestamp;

            $postFields = [
                'agency_uid' => API_AGENCY_UID,
                'timestamp' => $timestamp,
                'payload' => $this->encryptPayload($data)
            ];

            $jsonPayload = json_encode($postFields);

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonPayload)
            ]);
        } else {
            $data['agency_uid'] = API_AGENCY_UID;
            curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($data));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function fetchProviders() {
        return $this->sendRequest('/game/providers');
    }

    public function fetchGames($providerCode) {
        return $this->sendRequest('/game/list', ['code' => $providerCode]);
    }

    public function registerPlayer($username) {
        $username = str_starts_with($username, API_PLAYER_PREFIX) ? $username : API_PLAYER_PREFIX . $username;

        $payload = [
            'member_account' => $username,
            'member_name' => $username 
        ];

        $response = $this->sendRequest('/player/create', $payload, true);

        // Code 0 বা 8011 (আগে থেকেই আছে) দুটোই সাকসেস
        if (isset($response['code']) && ($response['code'] === 0 || $response['code'] === 8011)) {
            return true;
        }
        
        return false;
    }

    public function launchGame($username, $gameUid, $amount, $currency = 'USD') {
        $username = str_starts_with($username, API_PLAYER_PREFIX) ? $username : API_PLAYER_PREFIX . $username;

        $payload = [
            'member_account' => $username,
            'game_uid' => $gameUid,
            'credit_amount' => (string)$amount,
            'currency_code' => $currency,
            'language' => API_LANG,
            'home_url' => BASE_URL . '/index.php',
            'platform' => 2, 
            'callback_url' => BASE_URL . '/callback.php'
        ];

        $response = $this->sendRequest('/game/v1', $payload, true);

        if (isset($response['code']) && $response['code'] === 0) {
            $data = is_array($response['payload']) ? $response['payload'] : $this->decryptPayload($response['payload']);
            return ['status' => true, 'url' => $data['game_launch_url']];
        }
        
        return ['status' => false, 'message' => $response['msg'] ?? 'API Error', 'raw' => $response];
    }
}
?>