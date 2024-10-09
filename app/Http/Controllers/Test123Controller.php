<?php

namespace App\Http\Controllers;

use App\Helpers\Telegram;
use App\Models\Message;
use Carbon\Carbon;

class Test123Controller extends Controller
{

    protected $telegram, $buttons, $customFunctions, $dateFormatFunction;

    public function __construct(Telegram $telegram, )
    {
        $this->telegram = $telegram;
    }
    public function index()
    {
        $now = Carbon::now();

        $currentDate = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');

        $messages = Message::query()
            ->where('date', $currentDate)
            ->where('time', $currentTime)
            ->get();

        foreach ($messages as $message) {
            $data = $this->telegram->forwardMessage(
                env('TELEGRAM_GROUP_ID'),
                $message->chat_id,
                $message->message_id
            );

            $message->delete();
        }

    }

}
