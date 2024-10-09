<?php

namespace App\Console\Commands;

use App\Models\Message;
use Carbon\Carbon;
use App\Helpers\Telegram;
use Illuminate\Console\Command;

class SendScheduledMessages extends Command
{
    protected $telegram;

    public function __construct(Telegram $telegram)
    {
        parent::__construct();
        $this->telegram = $telegram;
    }

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

        $currentDate = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');

        $messages = Message::query()
            ->where('date', $currentDate)
            ->where('time', $currentTime)
            ->get();

        foreach ($messages as $message) {
            $this->telegram->forwardMessage(
                env('TELEGRAM_GROUP_ID'),
                $message->chat_id,
                $message->message_id
            );
            $message->delete();
        }

        $this->info("Scheduled messages have been sent successfully.");
    }
}
