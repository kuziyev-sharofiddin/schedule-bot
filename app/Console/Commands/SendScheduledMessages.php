<?php

namespace App\Console\Commands;

use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;

class SendScheduledMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:scheduled-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled messages to Telegram group';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $day = $now->format('l'); // Hafta kunini olish
        $time = $now->format('H:i'); // Vaqtni olish

        $messages = Message::where('day', $day)->where('time', $time)->get();

        foreach ($messages as $message) {
            Telegram::forwardMessage([
                'chat_id' => env('TELEGRAM_GROUP_ID'),
                'from_chat_id' => $message->chat_id,
                'message_id' => $message->message_id
            ]);

            // Xabar yuborilgandan keyin uni o'chirish (ixtiyoriy)
            $message->delete();
        }
    }
}
