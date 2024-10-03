<?php

namespace App\Http\Controllers;

use App\Helpers\Buttons;
use App\Helpers\CustomFunctions;
use App\Helpers\DateFormatFunc;
use App\Helpers\Telegram;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MessageController extends Controller
{

    protected $telegram, $buttons, $customFunctions, $dateFormatFunction;

    public function __construct(Buttons $buttons, Telegram $telegram, CustomFunctions $customFunctions, DateFormatFunc $dateFormatFunction)
    {
        $this->telegram = $telegram;
        $this->buttons = $buttons;
        $this->customFunctions = $customFunctions;
        $this->dateFormatFunction = $dateFormatFunction;
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
                $this->telegram->sendMessage($chat_id, "Iltimos postni guruhga yo'naltiring:");
            }
            if (isset($request->message['text']) && $request->message['text'] == 'Rejalashtirilgan postlar hisoboti') {
                $this->telegram->sendButtons($chat_id, "Rejalashtirilgan postlar hisoboti:");
            }
            if (isset($request->message['text']) && $request->message['text'] == 'Yuborilgan postlar hisoboti') {
                $this->telegram->sendButtons($chat_id, "Yuborilgan postlar hisoboti:");
            }
            if (isset($request->message['forward_from']) || isset($request->message['forward_from_chat'])) {
                $report_buttons = $this->buttons->getReportButtons();
                $this->telegram->sendButtons($chat_id, "Iltimos, hafta kunini belgilang:", $report_buttons);
            }
            elseif (isset($request->message['text']) && $this->dateFormatFunction->validateDateText($request->message['text'])) {
                $today = Carbon::today();
                $threeDaysLater = Carbon::today()->addDays(3);
                $parts = explode("\n", $request->message['text']);
                [$weekday, $date] = $parts;
                $date = Carbon::createFromFormat('d.m.Y', $date);
                if ($date->greaterThanOrEqualTo($today) && $date->lessThanOrEqualTo($threeDaysLater)) {
                    cache()->put("selected_day_$chat_id", $weekday);
                    cache()->put("selected_date_$chat_id", $date->format('d.m.Y'));
                    $number_buttons = $this->buttons->getNumberButtons($weekday);
                    $this->telegram->sendButtons($chat_id, "Iltimos, vaqtni tanlang yoki qo'lda kiriting (24-soat formatida, masalan: 10:00):", $number_buttons);
                } else {
                    $report_buttons = $this->buttons->getReportButtons();
                    $this->telegram->sendButtons($chat_id, "Kiritilgan sana noto'g'ri. Iltimos, bugungi kundan uch kun ichida biror sanani tanlang:", $report_buttons);
                }
            } elseif (isset($request->message['text'])) {
                $selected_date = cache()->get("selected_date_" . $chat_id); // Cache'dan tanlangan sanani olish
                if ($selected_date) {
                    $checkerTime = $this->dateFormatFunction->validateDateTime($request->message['text'], $selected_date);
                    if ($checkerTime === true) {
                        $this->telegram->sendMessage($chat_id, "Vaqt to'g'ri kiritildi!"); // Vaqt to'g'ri kiritilganda tasdiqlash
                    } elseif ($checkerTime === false) {
                        $this->telegram->sendMessage($chat_id, "Iltimos, vaqtni to'g'ri kiriting. Vaqt Hozirgi vaqtdan keyin bo'lishi kerak!");
                    } else {
                        $this->telegram->sendMessage($chat_id, "Iltimos, vaqtni to'g'ri formatda kiriting (masalan: 14:30).");
                    }
                }
            }

//            elseif (isset($request->message['text']) && (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $request->message['text']))) {
//                cache()->put("selected_time_$chat_id", $request->message['text']);
//                $selected_day = cache()->get("selected_day_$chat_id");
//                $selected_time = cache()->get("selected_time_$chat_id");
//                if ($selected_day && $selected_time) {
//                    Message::query()->create([
//                        'chat_id' => $chat_id,
//                        'message_id' => $message_id,
//                        'day' => $selected_day,
//                        'time' => $selected_time
//                    ]);
//                    $this->telegram->sendMessage($chat_id, "Xabar jadvalga qo'shildi! Xabar  guruhga $selected_day kuni  soat $selected_time da jo'natiladi.");
//                    $this->telegram->sendButtons($chat_id, "Kun va vaqtni belgilab yana xabar jo'natishingiz mumkin.", $this->buttons->report_detail_buttons);
//                } else {
//                    $this->telegram->sendMessage($chat_id, "Kun va vaqt tanlanmagan, iltimos qaytadan jarayonni boshlang.", $this->buttons->report_buttons);
//                }
//            }
        }
    }
}
