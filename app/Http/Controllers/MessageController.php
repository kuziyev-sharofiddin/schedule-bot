<?php

namespace App\Http\Controllers;

use App\Helpers\Buttons;
use App\Helpers\CustomFunctions;
use App\Helpers\DateFormatFunc;
use App\Helpers\Telegram;
use App\Models\Message;
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
        $posts = Message::query()->get();
        if (isset($request->message)) {
            $chat_id = $request->message['chat']['id'];
            if (isset($request->message['text']) && $request->message['text'] == '/start') {
                $this->telegram->sendButtons($chat_id, "Assalomu Alaykum yaxshimisiz. Men yo'naltirilgan xabarlarni kunning qaysidir vaqtlarida jadval asosida guruhga jo'natadigan botman. Quyidagilardan birini tanlang:", $this->buttons->report_detail_buttons);
            }

            if (isset($request->message['text']) && $request->message['text'] == 'Yangi post joylash') {
                $this->telegram->sendButtons($chat_id, "⤵️ Iltimos postni guruhga yo'naltiring:", $this->buttons->come_back);
            }

            if (isset($request->message['text']) && $request->message['text'] == 'Rejalashtirilgan postlar hisoboti') {
                $forwardedMessages = Message::query()->get();
                if ($forwardedMessages->isEmpty()) {
                    $this->telegram->sendMessage($request->message['chat']['id'], 'Hech qanday forward qilingan xabar topilmadi.');
                }
                $this->telegram->sendButtons($request->message['chat']['id'], "Rejalashtirilgan postlar hisoboti:", $this->buttons->delete_post);

                foreach ($forwardedMessages as $forwardedMessage) {
                    $message = "Xabar " . $forwardedMessage['weekDay'] . " kuni " . $forwardedMessage['time'] . "🕦 da yoboriladi.";
                    $this->telegram->sendMessageReply($forwardedMessage['chat_id'], $message, null, ['message_id' => $forwardedMessage['message_id']]);
                }
            }
            if (isset($request->message['text']) && $request->message['text'] == "❌ Postni olib tashlash") {
                $post_buttons = $this->buttons->posts();
                $this->telegram->sendButtons($request->message['chat']['id'], "Quyidagi postlardan birini o'chiring:", $post_buttons);
            }
            if (isset($request->message['text'])) {
                foreach ($posts as $post) {
                    if ($request->message['text'] == $post->weekDay . ' ' . $post->time) {
                        $post->delete();
                        $this->telegram->deleteMessage($request->message['chat']['id'], $post->message_id);
                        $this->telegram->sendMessage($request->message['chat']['id'], "Qolgan postlar ro'yxati:");
                        $reports = Message::query()->get();
                        foreach ($reports as $report) {
                            $message = "Xabar " . $report['weekDay'] . " kuni " . $report['time'] . " da yoboriladi.";
                            $this->telegram->sendMessageReply($request->message['chat']['id'], $message, null, ['message_id' => $report->message_id]);
                        }
                        $this->telegram->sendButtons($request->message['chat']['id'], "Post bazadan muvaffaqiyatli o'chirildi. Yana boshqa post qo'shishingiz yoki o'chirishingiz mumkin.", $this->buttons->report_detail_buttons);
                        break;
                    }
                }
            }

            if (isset($request->message['text']) && $request->message['text'] == 'Yuborilgan postlar hisoboti') {
                $forwardedMessages = Message::query()->get();
                if ($forwardedMessages->isEmpty()) {
                    $this->telegram->sendMessage($request->message['chat']['id'], 'Hech qanday forward qilingan xabar topilmadi.');
                }
                $this->telegram->sendButtons($request->message['chat']['id'], "Yuborilgan postlar hisoboti:", $this->buttons->report_detail_buttons);

                foreach ($forwardedMessages as $forwardedMessage) {
                    $message = "Xabar " . $forwardedMessage['weekDay'] . " kuni " . $forwardedMessage['time'] . "🕦 da yoboriladi.";
                    $this->telegram->sendMessageReply($forwardedMessage['chat_id'], $message, null, ['message_id' => $forwardedMessage['message_id']]);
                }
            }

            if (isset($request->message['forward_from']) || isset($request->message['forward_from_chat'])) {
                $report_buttons = $this->buttons->getReportButtons();
                cache()->put("selected_forward_message_$chat_id", $request->message['message_id']);
                $this->telegram->sendButtons($chat_id, "Iltimos, hafta kunini belgilang:", $report_buttons);
            }

            if (isset($request->message['text']) && $this->dateFormatFunction->validateDateText($request->message['text'])) {
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
            }
            if (isset($request->message['text']) && (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $request->message['text']))){
                $selected_date = cache()->get("selected_date_$chat_id");
                if ($selected_date) {
                    $checkerTime = $this->dateFormatFunction->validateDateTime($request->message['text'], $selected_date);
                    if ($checkerTime === true) {
                        cache()->put("selected_time_$chat_id", $request->message['text']);
                        $this->telegram->sendButtons($chat_id, "Vaqt to'g'ri kiritildi!", $this->buttons->completed_button); // Vaqt to'g'ri kiritilganda tasdiqlash
                    } elseif ($checkerTime === false) {
                        $this->telegram->sendMessage($chat_id, "Iltimos, vaqtni to'g'ri kiriting. Vaqt Hozirgi vaqtdan keyin bo'lishi kerak!");
                    }
                }
            }

            if (isset($request->message['text']) && $request->message['text'] == 'Yakunlash') {
                $selected_day = cache()->get("selected_day_$chat_id");
                $selected_date = cache()->get("selected_date_$chat_id");
                $selected_time = cache()->get("selected_time_$chat_id");


                $selected_forward_from_chat_id = cache()->get("selected_forward_message_$chat_id");
                $message = "Xabar " . $selected_day . " kuni " . $selected_date . ' ' . $selected_time . " da yoboriladi.";
                $this->telegram->sendMessageReply($chat_id, $message, null, ['message_id' => $selected_forward_from_chat_id]);
                $this->telegram->sendButtons($chat_id, "Xabar qabul qilindi.Hammasi to'g'ri bo'lsa tasdiqlash tugmasini bosing:", $this->buttons->confirm_button);
            }
            if (isset($request->message['text']) && $request->message['text'] == "❌ Postni o'chirish") {
                $selected_forward_from_chat_id = cache()->get("selected_forward_message_$chat_id");
                $this->telegram->deleteMessage($request->message['chat']['id'], $selected_forward_from_chat_id);
                $this->telegram->sendButtons($chat_id, "Post muvaffaqiyatli o'chirildi.", $this->buttons->report_detail_buttons);
            }

            if (isset($request->message['text']) && $request->message['text'] == 'Tasdiqlash') {
                $selected_day = cache()->get("selected_day_$chat_id");
                $selected_date = cache()->get("selected_date_$chat_id");
                $selected_time = cache()->get("selected_time_$chat_id");
                $selected_forward_from_chat_id = cache()->get("selected_forward_message_$chat_id");
                if ($selected_day && $selected_date && $selected_time && $selected_forward_from_chat_id) {
                    $selected_date = Carbon::parse($selected_date)->format("Y-m-d");
                    Message::query()->create([
                        'chat_id' => $chat_id,
                        'message_id' => $selected_forward_from_chat_id,
                        'weekDay' => $selected_day,
                        'date' => $selected_date,
                        'time' => $selected_time
                    ]);

                    cache()->clear();
                    $this->telegram->sendMessage($chat_id, "Xabar jadvalga qo'shildi! Xabar  guruhga $selected_day kuni  soat $selected_time da jo'natiladi.");
                    $this->telegram->sendButtons($chat_id, "Kun va vaqtni belgilab yana xabar jo'natishingiz mumkin.", $this->buttons->report_detail_buttons);
                }
            }
        }
    }
}
