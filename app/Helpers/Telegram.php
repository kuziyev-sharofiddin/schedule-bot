<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Telegram
{
//    protected $http;
    const url = 'https://api.telegram.org/bot';
    // const key = '7025283045:AAGWwL1ON4l0431zAUY_9FFRZGwW3-vzSFw';
    // const key = '6825049665:AAHDZ7Y-2rarjWfpASLx617SpoBESitcXZQ';

    public function __construct()
    {
        $this->http = new Http();
    }

    public function sendMessage($chat_id, $message, $message_thread_id = null, $media_group_id = null)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/sendMessage', [
            'chat_id' => $chat_id,
            'message_thread_id' => $message_thread_id,
            'text' => $message,
            'media_group_id' => $media_group_id,
            'parse_mode' => 'html'
        ]);
    }

    public function sendMessageReply($chat_id, $message, $message_thread_id = null, $reply_parameters)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/sendMessage', [
            'chat_id' => $chat_id,
            'text' => $message,
            'message_thread_id' => $message_thread_id,
            'reply_parameters' => $reply_parameters
        ]);
    }

    public function editMessageText($chat_id, $message, $message_id)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $message,
            'parse_mode' => 'html'
        ]);
    }

    public function deleteMessage($chat_id, $message_id)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/deleteMessage', [
            'chat_id' => $chat_id,
            'parse_mode' => 'html',
            'message_id' => $message_id
        ]);
    }

    public function sendButtons($chat_id, $message, $button)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/sendMessage', [
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'html',
            'reply_markup' => $button
        ]);
    }

    public function removeButtons($chat_id, $button)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/sendMessage', [
            'chat_id' => $chat_id,
            'text' => '',
            'parse_mode' => 'html',
            'reply_markup' => $button
        ]);
    }

    public function editButtons($chat_id, $message, $button, $message_id)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/editMessageText', [
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'html',
            'reply_markup' => $button,
            'message_id' => $message_id
        ]);
    }

    public function sendPoll($chat_id, $question, $options, $correct_option_id, $open_period, $button)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/sendPoll', [
            'chat_id' => $chat_id,
            'question' => $question,
            'options' => json_encode($options),
            'protect_content' => true,
            'is_anonymous' => false,
            'type' => 'quiz',
            'correct_option_id' => $correct_option_id,
            'open_period' => $open_period,
            'reply_markup' => $button
        ]);
    }

    public function getFile($file_id)
    {
        return $this->http::get(self::url . env('TELEGRAM_BOT_TOKEN') . '/getFile', [
            'file_id' => $file_id
        ]);
    }

    public function downloadFile($file_path)
    {
        return $this->http::get(self::url . env('TELEGRAM_BOT_TOKEN') . '/' . $file_path);
    }


    public function createForumTopic($chat_id, $name)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/createForumTopic', [
            'chat_id' => $chat_id,
            'name' => $name,
            // 'icon_custom_emoji_id' => (string)$user_chat_id
        ]);
    }

    public function editForumTopic($chat_id, $name, $message_thread_id)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/editForumTopic', [
            'chat_id' => $chat_id,
            'name' => $name,
            'message_thread_id' => $message_thread_id,
        ]);
    }

    public function getUserProfilePhotos($user_id)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/getUserProfilePhotos', [
            'user_id' => $user_id
        ]);
    }

    public function sendPhoto($chat_id, $photo, $caption = null, $message_thread_id = null)
    {
        if ($caption !== null) {
            $data = [
                'chat_id' => $chat_id,
                'photo' => $photo,
                'caption' => $caption,
                'message_thread_id' => $message_thread_id
            ];
        } else {
            $data = [
                'chat_id' => $chat_id,
                'photo' => $photo,
                'message_thread_id' => $message_thread_id
            ];
        }

        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/sendPhoto', $data);
    }

    public function sendMediaGroup($chat_id, $media, $message_thread_id = null)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/sendMediaGroup', [
            'chat_id' => $chat_id,
            'media' => $media,
            'message_thread_id' => $message_thread_id
        ]);
    }

    public function sendChatAction($chat_id, $action)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/sendChatAction', [
            'chat_id' => $chat_id,
            'action' => $action
        ]);
    }

    public function sendSticker($chat_id, $sticker, $message_thread_id = null)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/sendSticker', [
            'chat_id' => $chat_id,
            'sticker' => $sticker,
            'message_thread_id' => $message_thread_id
        ]);
    }

    public function sendVoice($chat_id, $voice, $message_thread_id = null)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/sendVoice', [
            'chat_id' => $chat_id,
            'voice' => $voice,
            'message_thread_id' => $message_thread_id
        ]);
    }

    public function sendLocation($chat_id, $latitude, $longitude, $message_thread_id = null)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/sendLocation', [
            'chat_id' => $chat_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'message_thread_id' => $message_thread_id
        ]);
    }

    public function sendVideo($chat_id, $video, $message_thread_id = null)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/sendVideo', [
            'chat_id' => $chat_id,
            'video' => $video,
            'message_thread_id' => $message_thread_id
        ]);
    }

    public function sendAudio($chat_id, $audio, $caption, $message_thread_id = null)
    {
        if ($caption !== null) {
            $data = [
                'chat_id' => $chat_id,
                'audio' => $audio,
                'caption' => $caption,
                'message_thread_id' => $message_thread_id
            ];
        } else {
            $data = [
                'chat_id' => $chat_id,
                'audio' => $audio,
                'message_thread_id' => $message_thread_id
            ];
        }

        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/sendAudio', $data);
    }

    public function sendDocument($chat_id, $document, $caption, $message_thread_id = null)
    {
        if ($caption !== null) {
            $data = [
                'chat_id' => $chat_id,
                'document' => $document,
                'caption' => $caption,
                'message_thread_id' => $message_thread_id
            ];
        } else {
            $data = [
                'chat_id' => $chat_id,
                'document' => $document,
                'message_thread_id' => $message_thread_id
            ];
        }

        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/sendDocument', $data);
    }

//    public function forwardMessage($chat_id, $from_chat_id, $message_id)
//    {
//        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/forwardMessage', [
//            'chat_id' => $chat_id,
//            'from_chat_id' => $from_chat_id,
//            'message_id' => $message_id
//        ]);
//    }
    public function forwardMessage($chat_id, $from_chat_id, $message_id)
    {
        $response = $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/forwardMessage', [
            'chat_id' => $chat_id,
            'from_chat_id' => $from_chat_id,
            'message_id' => $message_id
        ]);

        // API javobini tekshirish
        if ($response->failed()) {
            // Xatolik haqida xabar berish
            Log::error('Forward message failed', [
                'response' => $response->json(),
                'chat_id' => $chat_id,
                'from_chat_id' => $from_chat_id,
                'message_id' => $message_id,
            ]);
        }

        return $response;
    }

    public function sendContact($chat_id, $phone_number, $first_name, $message_thread_id = null)
    {
        return $this->http::post(self::url . env('TELEGRAM_BOT_TOKEN') . '/sendContact', [
            'chat_id' => $chat_id,
            'phone_number' => $phone_number,
            'first_name' => $first_name,
            'message_thread_id' => $message_thread_id
        ]);
    }

    public function getChatMember($chat_id, $user_id)
    {
        return $this->http::post(self::url . env('TELEGRAM_TOKEN_GET_MEMBER') . '/getChatMember', [
            'chat_id' => $chat_id,
            'user_id' => $user_id
        ]);
    }
}
