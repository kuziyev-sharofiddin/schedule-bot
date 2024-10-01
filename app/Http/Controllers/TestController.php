<?php

namespace App\Http\Controllers;

use App\Helpers\Buttons;
use App\Helpers\CustomFunctions;
use App\Models\Message;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TestController extends Controller
{

    protected $telegram, $buttons, $customFunctions;

    public function __construct(Buttons $buttons, \App\Helpers\Telegram $telegram, CustomFunctions $customFunctions)
    {
        $this->telegram = $telegram;
        $this->buttons = $buttons;
        $this->customFunctions = $customFunctions;
    }
    public function store(Request $request)
    {
        $update = Telegram::getWebhookUpdate();
        $message = $update->getMessage();

        // Start bosilganda kunlarni ko'rsatish
        if ($message->getText() == '/start') {
            Telegram::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => "Hafta kunini tanlang:",
                'reply_markup' => $this->buttons->report_buttons,
            ]);
        }

        // Kun tanlanganda va vaqtni kiritishni so'rash
        elseif (in_array($message->getText(), ['Dushanba', 'Seshanba', 'Chorshanba', 'Payshanba', 'Juma', 'Shanba', 'Yakshanba'])) {
            $request->session()->put('selected_day', $message->getText());

            Telegram::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => "Iltimos, vaqtni tanlang yoki qo'lda kiriting (24-soat formatida, masalan: 10:00):",
                'reply_markup' => $this->buttons->number_buttons,
            ]);
        }

        // Vaqt tanlanganda
        elseif (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $message->getText())) {
            $request->session()->put('selected_time', $message->getText());

            Telegram::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => "Endi xabarni yo'naltiring."
            ]);
        }

        // Forward qilingan xabarni olish
        elseif ($message->getForwardFromChat()) {
            $selected_day = $request->session()->get('selected_day');
            $selected_time = $request->session()->get('selected_time');

            // Xabarni saqlash
            Message::create([
                'chat_id' => $message->getChat()->getId(),
                'message_id' => $message->getMessageId(),
                'day' => $selected_day,
                'time' => $selected_time
            ]);

            Telegram::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => "Xabar jadvalga qo'shildi!"
            ]);
        }
    }
}
