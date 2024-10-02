<?php

namespace App\Http\Controllers;

use App\Helpers\Buttons;
use App\Helpers\CustomFunctions;
use App\Helpers\Telegram;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

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

            if (isset($request->message['text']) && $request->message['text'] == '/start') {
                $this->telegram->sendButtons($chat_id, "Assalomu Alaykum yaxshimisiz. Men yo'naltirilgan xabarlarni kunning qaysidir vaqtlarida jadval asosida guruhga jo'natadigan botman. Quyidagilardan birini tanlang:", $this->buttons->report_detail_buttons);
            }

            if (isset($request->message['text']) && $request->message['text'] == 'Yangi post joylash') {
                $this->telegram->sendMessage($chat_id, "Iltimos postni guruhga yo'naltiring:");
            }

            if (isset($request->message['text']) && $request->message['text'] == 'Rejalashtirilgan postlar hisoboti') {
                $forwardedMessages = Message::query()->get();
                if ($forwardedMessages->isEmpty()) {
                    $this->telegram->sendMessage($request->message['chat']['id'], 'Hech qanday forward qilingan xabar topilmadi.');
                }
                $this->telegram->sendButtons($request->message['chat']['id'], "Rejalashtirilgan postlar hisoboti:", $this->buttons->report_detail_buttons);

                foreach ($forwardedMessages as $forwardedMessage) {
                    $message = "Xabar " . $forwardedMessage['day'] ." kuni " . $forwardedMessage['time'] . " da yoboriladi.";
                    $this->telegram->sendMessageReply($forwardedMessage['chat_id'], $message, null, ['message_id' => $forwardedMessage['from_chat_message_id']]);
                }
            }

            if (isset($request->message['text']) && $request->message['text'] == 'Yuborilgan postlar hisoboti') {
                $forwardedMessages = Message::query()->get();
                if ($forwardedMessages->isEmpty()) {
                    $this->telegram->sendMessage($request->message['chat']['id'], 'Hech qanday forward qilingan xabar topilmadi.');
                }
                $this->telegram->sendButtons($request->message['chat']['id'], "Yuborilgan postlar hisoboti:", $this->buttons->report_detail_buttons);

                foreach ($forwardedMessages as $forwardedMessage) {
                    $message = "Xabar " . $forwardedMessage['day'] ." kuni " . $forwardedMessage['time'] . " da yoboriladi.";
                    $this->telegram->sendMessageReply($forwardedMessage['chat_id'], $message, null, ['message_id' => $forwardedMessage['from_chat_message_id']]);
                }
            }

            if (isset($request->message['forward_from']) || isset($request->message['forward_from_chat'])) {
                $report_buttons = $this->buttons->getReportButtons();
                cache()->put("selected_forward_message_$chat_id", $request->message['message_id']);
                $this->telegram->sendButtons($chat_id, "Iltimos, hafta kunini belgilang:", $report_buttons);
            }

            if (isset($request->message['text']) && (in_array($request->message['text'], ['Dushanba', 'Seshanba', 'Chorshanba', 'Payshanba', 'Juma', 'Shanba', 'Yakshanba']))) {
                cache()->put("selected_day_$chat_id", $request->message['text']);
                $this->telegram->sendButtons($chat_id, "Iltimos, vaqtni tanlang yoki qo'lda kiriting (24-soat formatida, masalan: 10:00):", $this->buttons->number_buttons);
            }

            if (isset($request->message['text']) && (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $request->message['text']))) {
                cache()->put("selected_time_$chat_id", $request->message['text']);
                $this->telegram->sendButtons($chat_id, "Xabar qo'shildi.", $this->buttons->completed_button);
            }
            if(isset($request->message['text']) && $request->message['text'] == 'Yakunlash') {
                $selected_day = cache()->get("selected_day_$chat_id");
                $selected_time = cache()->get("selected_time_$chat_id");
                $selected_forward_from_chat_id = cache()->get("selected_forward_message_$chat_id");
                $message = "Xabar " . $selected_day ." kuni " . $selected_time . " da yoboriladi.";
                $this->telegram->sendMessageReply($chat_id, $message, null, ['message_id' => $selected_forward_from_chat_id]);
                $this->telegram->sendButtons($chat_id, "Xabar qabul qilindi.Hammasi to'g'ri bo'lsa tasdiqlash tugmasini bosing:",$this->buttons->confirm_button);
            }

            if(isset($request->message['text']) && $request->message['text'] == 'Tasdiqlash') {
                $selected_day = cache()->get("selected_day_$chat_id");
                $selected_time = cache()->get("selected_time_$chat_id");
                $selected_forward_from_chat_id = cache()->get("selected_forward_message_$chat_id");
                if ($selected_day && $selected_time && $selected_forward_from_chat_id) {
                    Message::query()->create([
                        'chat_id' => $chat_id,
                        'from_chat_message_id' => $selected_forward_from_chat_id,
                        'day' => $selected_day,
                        'time' => $selected_time
                    ]);
                } else {
                    $this->telegram->sendMessage($chat_id, "Kun va vaqt tanlanmagan, iltimos ortga qaytib qaytadan jarayonni boshlang.", $this->buttons->report_buttons);
                }

                $this->telegram->sendMessage($chat_id, "Xabar jadvalga qo'shildi! Xabar  guruhga $selected_day kuni  soat $selected_time da jo'natiladi.");
                $this->telegram->sendButtons($chat_id, "Kun va vaqtni belgilab yana xabar jo'natishingiz mumkin.",$this->buttons->report_detail_buttons);
            }
        }
    }
}
