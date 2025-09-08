<?php

namespace App\Traits;

use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Http;

trait NotificationTrait
{
    public static function sendPushNotificationToTopic($data, $topic, $type,$web_push_link = null): bool|string
    {
        if(isset($data['module_id'])){
            $module_id = $data['module_id'];
        }else{
            $module_id = '';
        }
        if(isset($data['order_type'])){
            $order_type = $data['order_type'];
        }else{
            $order_type = '';
        }
        if(isset($data['zone_id'])){
            $zone_id = $data['zone_id'];
        }else{
            $zone_id = '';
        }

//        $click_action = "";
//        if($web_push_link){
//            $click_action = ',
//            "click_action": "'.$web_push_link.'"';
//        }

        if (isset($data['order_id'])) {
            $postData = [
                'message' => [
                    "topic" => $topic,
                    "data" => [
                        "title" => (string)$data['title'],
                        "body" => (string)$data['description'],
                        "order_id" => (string)$data['order_id'],
                        "order_type" => (string)$order_type,
                        "type" => (string)$type,
                        "image" => (string)$data['image'],
                        "module_id" => (string)$module_id,
                        "zone_id" => (string)$zone_id,
                        "title_loc_key" => (string)$data['order_id'],
                        "body_loc_key" => (string)$type,
                        "click_action" => $web_push_link?(string)$web_push_link:'',
                        "sound" => "notification.wav",
                    ],
                    "notification" => [
                        "title" => (string)$data['title'],
                        "body" => (string)$data['description'],
                        "image" => (string)$data['image'],
                    ],
                    "android" => [
                        "notification" => [
                            "channelId" => '6ammart',
                        ]
                    ],
                    "apns" => [
                        "payload" => [
                            "aps" => [
                                "sound" => "notification.wav"
                            ]
                        ]
                    ],
                ]
            ];
        } else {
            $postData = [
                'message' => [
                    "topic" => $topic,
                    "data" => [
                        "title" => (string)$data['title'],
                        "body" => (string)$data['description'],
                        "type" => (string)$type,
                        "image" => (string)$data['image'],
                        "body_loc_key" => (string)$type,
                        "click_action" => $web_push_link?(string)$web_push_link:'',
                        "sound" => "notification.wav",
                    ],
                    "notification" => [
                        "title" => (string)$data['title'],
                        "body" => (string)$data['description'],
                        "image" => (string)$data['image'],
                    ],
                    "android" => [
                        "notification" => [
                            "channelId" => '6ammart',
                        ]
                    ],
                    "apns" => [
                        "payload" => [
                            "aps" => [
                                "sound" => "notification.wav"
                            ]
                        ]
                    ],
                ]
            ];
        }
        return self::sendNotificationToHttp($postData);
    }

    public static function sendPushNotificationToDevice($fcm_token, $data, $web_push_link = null): bool|string
    {
        //        if(isset($data['message'])){
//            $message = $data['message'];
//        }else{
//            $message = '';
//        }
        if(isset($data['conversation_id'])){
            $conversation_id = $data['conversation_id'];
        }else{
            $conversation_id = '';
        }
        if(isset($data['sender_type'])){
            $sender_type = $data['sender_type'];
        }else{
            $sender_type = '';
        }
        if(isset($data['module_id'])){
            $module_id = $data['module_id'];
        }else{
            $module_id = '';
        }
        if(isset($data['order_type'])){
            $order_type = $data['order_type'];
        }else{
            $order_type = '';
        }

//        $click_action = "";
//        if($web_push_link){
//            $click_action = ',
//            "click_action": "'.$web_push_link.'"';
//        }
        $postData = [
            'message' => [
                "token" => $fcm_token,
                "data" => [
                    "title" => (string)$data['title'],
                    "body" => (string)$data['description'],
                    "image" => (string)$data['image'],
                    "order_id" => (string)$data['order_id'],
                    "type" => (string)$data['type'],
                    "conversation_id" => (string)$conversation_id,
                    "module_id" => (string)$module_id,
                    "sender_type" => (string)$sender_type,
                    "order_type" => (string)$order_type,
                    "click_action" => $web_push_link?(string)$web_push_link:'',
                    "sound" => "notification.wav",
                ],
                "notification" => [
                    'title' => (string)$data['title'],
                    'body' => (string)$data['description'],
                    "image" => (string)$data['image'],
                ],
                "android" => [
                    "notification" => [
                        "channelId" => '6ammart',
                    ]
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "sound" => "notification.wav"
                        ]
                    ]
                ]
            ]
        ];

        return self::sendNotificationToHttp($postData);
    }

    public static function sendNotificationToHttp(array|null $data)
    {
        $config = self::get_business_settings('push_notification_service_file_content');
        $key = (array)$config;
        
        if(!$key || !isset($key['project_id']) || empty($key['project_id'])){
            \Log::error('FCM: Missing project_id in push_notification_service_file_content');
            return false;
        }
        
        $url = 'https://fcm.googleapis.com/v1/projects/'.$key['project_id'].'/messages:send';
        $accessToken = self::getAccessToken($key);
        
        if(!$accessToken){
            \Log::error('FCM: Failed to get access token');
            return false;
        }
        
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ];
        
        try {
            $response = Http::withHeaders($headers)->post($url, $data);
            
            if($response->successful()) {
                \Log::info('FCM: Notification sent successfully', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return true;
            } else {
                \Log::error('FCM: Failed to send notification', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'data' => $data
                ]);
                return false;
            }
        } catch (\Exception $exception) {
            \Log::error('FCM: Exception occurred', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'data' => $data
            ]);
            return false;
        }
    }

    public static function getAccessToken($key)
    {
        try {
            if(!isset($key['client_email']) || !isset($key['private_key'])) {
                \Log::error('FCM: Missing client_email or private_key in service account');
                return null;
            }
            
            $jwtToken = [
                'iss' => $key['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => time() + 3600,
                'iat' => time(),
            ];
            $jwtHeader = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $jwtPayload = base64_encode(json_encode($jwtToken));
            $unsignedJwt = $jwtHeader . '.' . $jwtPayload;
            
            if(!openssl_sign($unsignedJwt, $signature, $key['private_key'], OPENSSL_ALGO_SHA256)) {
                \Log::error('FCM: Failed to sign JWT token');
                return null;
            }
            
            $jwt = $unsignedJwt . '.' . base64_encode($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);
            
            if($response->successful()) {
                $accessToken = $response->json('access_token');
                if($accessToken) {
                    return $accessToken;
                } else {
                    \Log::error('FCM: No access token in response', ['response' => $response->json()]);
                    return null;
                }
            } else {
                \Log::error('FCM: Failed to get access token', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return null;
            }
        } catch (\Exception $exception) {
            \Log::error('FCM: Exception in getAccessToken', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            return null;
        }
    }

    public static function get_business_settings($name)
    {
        $config = null;

        $paymentmethod = BusinessSetting::where('key', $name)->first();

        if ($paymentmethod) {
            $config = json_decode($paymentmethod->value, true);
        }

        return $config;
    }
}
