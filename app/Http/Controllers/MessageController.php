<?php

namespace App\Http\Controllers;

use App\Helpers\Buttons;
use App\Helpers\CustomFunctions;
use App\Helpers\Telegram;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{

    protected $telegram, $buttons, $customFunctions;

    public function __construct(Buttons $buttons, Telegram $telegram, CustomFunctions $customFunctions)
    {
        $this->telegram = $telegram;
        $this->buttons = $buttons;
        $this->customFunctions = $customFunctions;
    }

    public function store(Request $request)
    {
        if (isset($request->message)) {
            $chat_id = $request->message['chat']['id'];
            $message_id = $request->message['message_id'];

            if (isset($request->message['text']) && $request->message['text'] == '/start') {
                $this->telegram->sendButtons($chat_id, "Assalomu Alaykum yaxshimisiz. Men yo'naltirilgan xabarlarni kunning qaysidir vaqtlarida jadval asosida guruhga jo'natadigan botman. Quyidagilardan birini tanlang:", $this->buttons->report_detail_buttons);
            }
            if (isset($request->message['text']) && $request->message['text'] == 'Yangi post joylash') {
                $this->telegram->sendButtons($chat_id, "Iltimos postni guruhga yo'naltiring:", $this->buttons->add_post);
            }
            if (isset($request->message['text']) && $request->message['text'] == 'Rejalashtirilgan postlar hisoboti') {
                $this->telegram->sendButtons($chat_id, "Rejalashtirilgan postlar hisoboti:");
            }
            if (isset($request->message['text']) && $request->message['text'] == 'Yuborilgan postlar hisoboti') {
                $this->telegram->sendButtons($chat_id, "Yuborilgan postlar hisoboti:");
            }
            if (isset($request->message['forward_from']) || isset($request->message['forward_from_chat'])) {
                $this->telegram->sendButtons($chat_id, "Iltimos, hafta kunini belgilang:", $this->buttons->report_buttons);

            }
            elseif (isset($request->message['text']) && (in_array($request->message['text'], ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']))) {
                // $chat_id asosida cache kalitini o'rnating
                cache()->put("selected_day_$chat_id", $request->message['text']);
                $this->telegram->sendButtons($chat_id, "Iltimos, vaqtni tanlang yoki qo'lda kiriting (24-soat formatida, masalan: 10:00):", $this->buttons->number_buttons);
            }
            elseif (isset($request->message['text']) && (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $request->message['text']))) {
                cache()->put("selected_time_$chat_id", $request->message['text']);
                $selected_day = cache()->get("selected_day_$chat_id");
                $selected_time = cache()->get("selected_time_$chat_id");

                if ($selected_day && $selected_time) {
                    Message::query()->create([
                        'chat_id' => $chat_id,
                        'message_id' => $message_id,
                        'day' => $selected_day,
                        'time' => $selected_time
                    ]);
                    $this->telegram->sendMessage($chat_id, "Xabar jadvalga qo'shildi! Xabar  guruhga $selected_day kuni  soat $selected_time da jo'natiladi.");
                    $this->telegram->sendMessage($chat_id, "Kun va vaqtni belgilab yana xabar jo'natishingiz mumkin.");
                } else {
                    $this->telegram->sendMessage($chat_id, "Kun va vaqt tanlanmagan, iltimos qaytadan jarayonni boshlang.", $this->buttons->report_buttons);
                }
                $this->telegram->sendMessage($chat_id, "Endi xabarni yo'naltiring.");
            }
        }
    }
}
