<?php

namespace App\Helpers;

use App\Helpers\Telegram;
use Carbon\Carbon;
use App\Models\Admin;
use App\Helpers\Buttons;
use App\Models\AdminQueue;
use App\Models\MediaGroup;
use App\Models\ChatBotTopic;
use App\Models\ChatBotMessage;
use App\Models\ClientReserve;
use App\Models\ClientReserveDetail;

class CustomFunctions
{
    protected $telegram, $buttons;

    public function __construct(Buttons $buttons)
    {
        $this->telegram = new Telegram(env('TELEGRAM_GARANT_TOKEN'));
        $this->buttons = $buttons;
    }

    public function chatBotTopic($chat_id, $request, $message, $is_working_time = null)
    {
        $message_thread_id = ChatBotTopic::query()->where('user_chat_id', $chat_id)->where('bot_name','Garant')->orderBy('id', 'DESC')->first();
        if ($message_thread_id === null) { // client avval jurojat qilmagan bo'lsa yangi topic yaratish
            // Bo'sh adminni aniqlab ushbu admin guruxida mijoz murojatini yo'nalritish
            $this->isEmptyAdmin($chat_id, $request, $is_working_time);
        } else {
            $diff = Carbon::parse($message_thread_id->created_at)->diffInDays(Carbon::now());
            if ($diff < 7) { // Mijoz murojat qilganiga 7 kundan kam bo'lsa, gaplashayotgan adminga sms borsin
                if ($is_working_time) {
                    $group_id = Admin::findOrFail($message_thread_id->admin_id)->user_group_id;
                    if ($message === "/start") { // /start messageni gruppaga yubormaslik (/startdan boshqa barcha msglar guruxga yuborilaveradi)
                        // $this->telegram->sendMessage($group_id, "☝️ Ushbu mijoz BOT orqali sizga murojat qilmoqda.", $message_thread_id->message_thread_id);
                    } else {
                        if (isset($request->message['photo'])) {
                            $message_thread_id = ChatBotTopic::query()->where('user_chat_id', $chat_id)->orderBy('id', 'DESC')->first();
                            $group_id = Admin::findOrFail($message_thread_id->admin_id)->user_group_id;

                            $is_message_thread_id = $this->sendAnyPhoto($chat_id, $request, null, $group_id, $message_thread_id);
                        } elseif (isset($request->message['sticker'])) {
                            $message_thread_id = ChatBotTopic::query()->where('user_chat_id', $chat_id)->orderBy('id', 'DESC')->first();
                            $group_id = Admin::findOrFail($message_thread_id->admin_id)->user_group_id;

                            $is_message_thread_id = $this->telegram->sendSticker($group_id, $request->message['sticker']['file_id'], $message_thread_id->message_thread_id);
                        } elseif (isset($request->message['voice'])) {
                            $message_thread_id = ChatBotTopic::query()->where('user_chat_id', $chat_id)->orderBy('id', 'DESC')->first();
                            $group_id = Admin::findOrFail($message_thread_id->admin_id)->user_group_id;

                            $is_message_thread_id = $this->telegram->sendVoice($group_id, $request->message['voice']['file_id'], $message_thread_id->message_thread_id);
                        } elseif (isset($request->message['location'])) {
                            $message_thread_id = ChatBotTopic::query()->where('user_chat_id', $chat_id)->orderBy('id', 'DESC')->first();
                            $group_id = Admin::findOrFail($message_thread_id->admin_id)->user_group_id;

                            $is_message_thread_id = $this->telegram->sendLocation($group_id, $request->message['location']['latitude'], $request->message['location']['longitude'], $message_thread_id->message_thread_id);
                        } elseif (isset($request->message['video'])) {
                            $message_thread_id = ChatBotTopic::query()->where('user_chat_id', $chat_id)->orderBy('id', 'DESC')->first();
                            $group_id = Admin::findOrFail($message_thread_id->admin_id)->user_group_id;

                            $is_message_thread_id = $this->telegram->sendVideo($group_id, $request->message['video']['file_id'], $message_thread_id->message_thread_id);
                        } elseif (isset($request->message['document'])) {
                            $caption = isset($request->message['caption']) ? $request->message['caption'] : null;
                            $message_thread_id = ChatBotTopic::query()->where('user_chat_id', $chat_id)->orderBy('id', 'DESC')->first();
                            $group_id = Admin::findOrFail($message_thread_id->admin_id)->user_group_id;

                            $is_message_thread_id = $this->telegram->sendDocument($group_id, $request->message['document']['file_id'], $caption, $message_thread_id->message_thread_id);
                        } elseif (isset($request->message['audio'])) {
                            $caption = isset($request->message['caption']) ? $request->message['caption'] : null;
                            $message_thread_id = ChatBotTopic::query()->where('user_chat_id', $chat_id)->orderBy('id', 'DESC')->first();
                            $group_id = Admin::findOrFail($message_thread_id->admin_id)->user_group_id;

                            $is_message_thread_id = $this->telegram->sendAudio($group_id, $request->message['audio']['file_id'], $caption, $message_thread_id->message_thread_id);
                        } elseif (isset($request->message['contact'])) {
                            $message_thread_id = ChatBotTopic::query()->where('user_chat_id', $chat_id)->orderBy('id', 'DESC')->first();
                            $group_id = Admin::findOrFail($message_thread_id->admin_id)->user_group_id;

                            $is_message_thread_id = $this->telegram->sendContact($group_id, $request->message['contact']['phone_number'], $request->message['contact']['first_name'], $message_thread_id->message_thread_id);
                        } elseif (isset($request->message['text'])) {
                            if (isset($request->message['reply_to_message'])) {
                                $reply_message_id = ChatBotMessage::query()
                                    ->where('user_message_id', $request->message['reply_to_message']['message_id'])
                                    ->where('group_chat_id', $group_id)
                                    ->where('user_chat_id', $chat_id)
                                    ->first();
                                if ($reply_message_id !== null) {
                                    $is_message_thread_id = $this->telegram->sendMessageReply($group_id, $message, $message_thread_id->message_thread_id, ['message_id' => $reply_message_id->group_message_id]);
                                } else {
                                    $is_message_thread_id = $this->telegram->sendMessage($group_id, $message, $message_thread_id->message_thread_id);
                                }
                            } else {
                                $is_message_thread_id = $this->telegram->sendMessage($group_id, $message, $message_thread_id->message_thread_id);
                            }
                        }

                        // yuborilgan messageni bazaga saqlash
                        if ($is_message_thread_id->object()->ok) {
                            $this->storeMessage($group_id, $is_message_thread_id->object()->result->message_id, $chat_id, $request->message['message_id']);
                        }

                        if ($is_message_thread_id->object()->ok === false) { // Guruhdan client uchun ochilgan topic o'chirib yuborilgan xolat uchun
                            // Mavjud topicni tekshirish
                            $old_topic = ChatBotTopic::query()->where('user_first_name', $request->message['from']['first_name'])
                                ->where('bot_name', 'Garant')
                                ->first();

                            // Yangi topic yaratish
                            if (!$old_topic) {
                                $topic = $this->telegram->createForumTopic($group_id, $request->message['from']['first_name'] . '-Garant');
                                $message_thread_id = $topic->object()->result->message_thread_id;
                            } else {
                                $message_thread_id = $old_topic->message_thread_id; // Eski topic mavjud bo'lsa, message_thread_idni olamiz
                            }

                            // ChatBotTopic yangilash yoki yaratish
                            $chatBotTopic = ChatBotTopic::query()->where('user_chat_id', $chat_id)->first();
                            if (!$chatBotTopic) {
                                // Agar avvaldan topic yaratilmagan bo'lsa, yangi topic qo'shish
                                ChatBotTopic::create([
                                    'user_chat_id' => $chat_id,
                                    'user_first_name' => $request->message['from']['first_name'],
                                    'bot_name' => 'Garant',
                                    'message_thread_id' => $message_thread_id,
                                ]);
                            } else {
                                // Mavjud topicni yangilash
                                $chatBotTopic->update([
                                    'message_thread_id' => $message_thread_id
                                ]);
                            }

                            // Yangi xabarni topicga joylashtirish
                            $this->createChatBotTopicMessage($chat_id, $request, $message_thread_id, $group_id);
                        }
                    }
                } else {
                    $this->telegram->sendMessage($chat_id, "Xurmatli mijoz, xozirda ish vaqtimiz yakunlangan!\n<b>Ish vaqtimiz:</b> <b>🕣 8:30</b> dan <b>🕚 23:00</b> gacha.");
                }
            } else { // Mijoz murojat qilganiga 7 kundan ko'p bo'lsa, boshqa bo'sh adminga yo'naltirilsin
                // Bo'sh adminni aniqlab ushbu admin guruxida mijoz murojatini yo'nalritish
                $this->isEmptyAdmin($chat_id, $request, $is_working_time);
            }
            // }
        }
    }

    /**
     * BOTga mijoz tomonidan /start berilganda adminga mijoz ma'lumotlarini to'plab yuborish
     */
    public function createChatBotTopicMessage($chat_id, $request, $topic, $group_id, $is_working_time = null)
    {
        $user_info = "";
        $user_info .= "🆔 $chat_id\n";
        $user_info .= isset($request->message['from']['username']) ? "🤑 @" . $request->message['from']['username'] . "\n" : "🤑 ." . "\n";
        $user_info .= "👤 " . $request->message['from']['first_name'];

        $this->telegram->sendMessage($group_id, $user_info, $topic->object()->result->message_thread_id);

        if ($is_working_time === null || $is_working_time) {
            $this->telegram->sendMessage($group_id, "<b>Mijozga yuborilgan birinchi xabar:</b>\n\nAssalomu alaykum, xurmatli mijoz!\nSizga qanday yordam bera olaman?", $topic->object()->result->message_thread_id);
        } else {
            $this->telegram->sendMessage($group_id, "☝️ Ushbu mijoz, 🔴 ish vaqti tugagan payt BOT orqali sizga murojat qildi.", $topic->object()->result->message_thread_id);
        }
    }

    /**
     * Bo'sh adminni aniqlab ushbu admin guruxida mijoz murojatini yo'nalritish
     */
    public function isEmptyAdmin($chat_id, $request, $is_working_time)
    {
        if ($is_working_time) {
            $adminQueue = AdminQueue::whereHas('admin', function ($query) {
                $query->where('status', 'start');
            })->orderBy('count', 'asc')
                ->first();

            $admin = $adminQueue->admin ?? null;

            if ($admin !== null) {
                if ($adminQueue->count == -1) {
                    $count = $adminQueue->count + 2;
                } else {
                    $count = $adminQueue->count + 1;
                }
                $adminQueue->update([
                    'count' => $count
                ]);
            }

            // $admin = Admin::query()
            //     ->withCount([
            //         'chatBotTopic' => function ($query) {
            //             $query->where('status', 0)->whereDate('created_at', Carbon::now()->format('Y-m-d'));
            //         },
            //     ])
            //     ->whereIn('status', ['start'])
            //     ->orderBy('chat_bot_topic_count', 'asc')
            //     ->first();

            if ($admin !== null) {
                // Bo'sh adminni aniqlab ushbu admin guruxida mijoz murojatini yo'nalritish
                $group_id = $admin->user_group_id;
                $old_topic = ChatBotTopic::query()->where('user_first_name', $request->message['from']['first_name'])->where('bot_name','Garant')->first();
                if (!$old_topic) {
                    $topic = $this->telegram->createForumTopic($group_id, $request->message['from']['first_name'] . '-Garant');
                }
                ChatBotTopic::create([
                    'user_first_name' => $request->message['from']['first_name'],
                    'user_chat_id' => $chat_id,
                    'message_thread_id' => $topic->object()->result->message_thread_id,
                    'status' => 0, // mijoz murojatiga javob berilmagan xolat
                    'admin_id' => $admin->id, // client murojati qaysi adminga yo'naltirilgan bo'lsa
                    'bot_name' => 'Garant',
                ]);

                $this->createChatBotTopicMessage($chat_id, $request, $topic, $group_id, $is_working_time);

                // mijoz birinchi smsini yozganda admin guruhida yangi topic yaratiladi
                if (isset($request->message['text'])) {
                    $m = $this->telegram->sendMessage($group_id, $request->message['text'], $topic->object()->result->message_thread_id);
                } elseif (isset($request->message['photo'])) {
                    $m = $this->sendAnyPhoto($chat_id, $request, null, $group_id, $topic->object()->result->message_thread_id);
                    if ($m !== null) {
                        if ($m->object()->ok) {
                            // yuborilgan messageni bazaga saqlash
                            if (collect($m->object()->result)->count() == 2) {
                                $this->storeMessage($group_id, collect($m->object()->result)->first()->message_id, $chat_id, $request->message['message_id']);
                                $this->storeMessage($group_id, collect($m->object()->result)->last()->message_id, $chat_id, $request->message['message_id']);
                            } else {
                                $this->storeMessage($group_id, $m->object()->result->message_id, $chat_id, $request->message['message_id']);
                            }
                        }
                    }
                }
                // yuborilgan messageni bazaga saqlash
                if ($m !== null) {
                    $this->storeMessage($group_id, $m->object()->result->message_id, $chat_id, $request->message['message_id']);
                }
            } else {
                $this->telegram->sendMessage($chat_id, "Xurmatli mijoz, murojatingiz qabul qilindi, tez orada adminlarimiz siz bilan bog'lanadi!");
                $this->clientReserveQuery($chat_id, $request);
            }
        } else {
            $this->clientReserveQuery($chat_id, $request);
        }
    }

    /**
     * Zaxiradagi mijozni olish uchun
     */
    public function getReserveClient($_is_admin,$bot_id)
    {
        $client_reserve = ClientReserve::query()
            ->whereNull('admin_id')
            ->orderBy('id', 'DESC')
            ->first();

        if ($_is_admin->status == "stop") {
            $this->telegram->sendMessage($_is_admin->user_group_id, "⚠️ Zaxiradagi mijozlarni olish uchun ish jarayonini boshlagan bo'lishingiz kerak!");
            return;
        }

        if ($client_reserve !== null) {
            // Bo'sh adminni aniqlab ushbu admin guruxida mijoz murojatini yo'nalritish
            $group_id = $_is_admin->user_group_id;

            $old_topic = ChatBotTopic::query()->where('user_first_name', $client_reserve->user_first_name)->where('bot_name','Garant')->first();
            if (!$old_topic) {
                $topic = $this->telegram->createForumTopic($group_id, $client_reserve->user_first_name . '-Garant');
            }
                ChatBotTopic::create([
                    'user_first_name' => $client_reserve->user_first_name,
                    'user_chat_id' => $client_reserve->user_chat_id,
                    'message_thread_id' => $topic->object()->result->message_thread_id,
                    'status' => 0, // mijoz murojatiga javob berilmagan xolat
                    'admin_id' => $_is_admin->id, // client murojati qaysi adminga yo'naltirilgan bo'lsa\
                    'bot_name' => 'Garant',
                ]);

            $user_info = "";
            $user_info .= "🆔 $client_reserve->user_chat_id\n";
            $user_info .= "🤑 @" . ($client_reserve->user_name !== null) ? "🤑 @" . $client_reserve->user_name . "\n" : '.' . "\n";
            $user_info .= "👤 " . $client_reserve->user_first_name;

            $m = $this->telegram->sendMessage($_is_admin->user_group_id, $user_info, $topic->object()->result->message_thread_id);

            foreach ($client_reserve->clientReserveDetail as $key => $value) {
                if ($value->message_text != "/start") {
                    switch ($value->message_type) {
                        case 'text':
                            $message_text = "";
                            $message_text .= "<b>Mijoz:</b>\n";
                            $message_text .= "<b>" . Carbon::parse($value->message_dete_time)->format('d.m.Y H:i:s') . "</b>\n\n";
                            $message_text .= $value->message_text;

                            $this->telegram->sendMessage($_is_admin->user_group_id, $message_text, $topic->object()->result->message_thread_id);
                            break;
                        case 'photo':
                            $this->telegram->sendPhoto($m->object()->result->chat->id, $value->file_id, $value->caption, $topic->object()->result->message_thread_id);
                            break;

                        case 'voice':
                            $this->telegram->sendVoice($m->object()->result->chat->id, $value->file_id, $topic->object()->result->message_thread_id);
                            break;

                        case 'video':
                            $this->telegram->sendVideo($m->object()->result->chat->id, $value->file_id, $topic->object()->result->message_thread_id);
                            break;

                        case 'sticker':
                            $this->telegram->sendSticker($m->object()->result->chat->id, $value->file_id, $topic->object()->result->message_thread_id);
                            break;

                        case 'document':
                            $this->telegram->sendDocument($m->object()->result->chat->id, $value->file_id, $value->caption, $topic->object()->result->message_thread_id);
                            break;

                        default:
                            # code...
                            break;
                    }
                }
            }

            $client_reserve->update([
                'admin_id' => $_is_admin->id
            ]);

            $this->telegram->sendMessage($_is_admin->user_group_id, "✅ Zaxirada mijozlardan biri sizning mijozlar ro'yxatingizga qo'shildi!");
        } else {
            $this->telegram->sendMessage($_is_admin->user_group_id, "🚫 Zaxirada mijozlar mavjud emas!");
        }

        return;
    }

    /**
     * send any photo method
     */
    public function sendAnyPhoto($chat_id, $request, $user_chat_id, $group_id, $message_thread_id)
    {
        $caption = isset($request->message['caption']) ? $request->message['caption'] : null;
        if (isset($request->message['media_group_id'])) { // group media rasmlar yuborilganda
            MediaGroup::create([
                'chat_id' => $chat_id,
                'media_group_id' => $request->message['media_group_id'],
                'file_id' => $request->message['photo'][0]['file_id'],
                'caption' => $caption
            ]);

            $mediaGroup = MediaGroup::where('media_group_id', $request->message['media_group_id'])->get();

            if ($mediaGroup->count() == 2) {
                $media = [];
                foreach ($mediaGroup as $val) {
                    if ($val->caption !== null) {
                        $media[] = [
                            'type' => 'photo',
                            'media' => $val->file_id,
                            'caption' => $val->caption
                        ];
                    } else {
                        $media[] = [
                            'type' => 'photo',
                            'media' => $val->file_id
                        ];
                    }
                }

                if ($user_chat_id !== null) { // admin mijozga sms yozganda
                    return $this->telegram->sendMediaGroup($user_chat_id->user_chat_id, $media);
                } else { // mijoz adminga sms yozganda
                    return $this->telegram->sendMediaGroup($group_id, $media, $message_thread_id);
                }
            }
        } else { // oddiy bittalik rasm yuborilganda
            if ($user_chat_id !== null) { // admin mijozga sms yozganda
                return $this->telegram->sendPhoto($user_chat_id->user_chat_id, $request->message['photo'][0]['file_id'], $caption);
            } else { // mijoz adminga sms yozganda
                return $this->telegram->sendPhoto($group_id, $request->message['photo'][0]['file_id'], $caption, $message_thread_id);
            }
        }
    }

    /**
     * Barcha yozilgan messagelarni bazaga saqlab ketish. Keyinchalik delete yo edit qilish uchun
     */
    public function storeMessage($group_chat_id, $group_message_id, $user_chat_id, $user_message_id)
    {
        ChatBotMessage::create([
            'group_chat_id' => $group_chat_id,
            'group_message_id' => $group_message_id,
            'user_chat_id' => $user_chat_id,
            'user_message_id' => $user_message_id
        ]);
    }

    /**
     * Ishni boshlash uchun (admin, Inline button)
     */
    public function reportButtonsForAdminsStart($chat_id, $status_button)
    {
        $text = "<b>🔴 Mijozlarni qabul qilish jarayoni to'xtatildi.</b>\n\nIsh jarayonini boshlash tugmasini bosish orqali murojatlarni qabul qilishni boshlashingiz mumkin. <b>(🟢 Ish jarayonini boshlash)</b>👇";

        return $this->telegram->sendButtons($chat_id, $text, $status_button);
    }

    /**
     * Ishni yakunlash uchun (admin, Inline button)
     */
    public function reportButtonsForAdminsStop($chat_id, $status_button)
    {
        $text = "<b>🟢 Mijozlarni qabul qilish jarayoni boshlandi.</b>\n\nIsh jarayonini yakunlash tugmasini bosish orqali murojatlarni qabul qilishni to'xtatishingiz mumkin. <b>(🔴 Ish jarayonini yakunlash)</b>👇";

        return $this->telegram->sendButtons($chat_id, $text, $status_button);
    }

    /**
     * mijozni zaxiraga olish query
     */
    public function clientReserveQuery($chat_id, $request)
    {
        $client_reserve = ClientReserve::updateOrCreate([
            'admin_id' => null,
            'user_chat_id' => $chat_id
        ], [
            'user_first_name' => $request->message['from']['first_name'],
            'user_name' => isset($request->message['from']['username']) ? $request->message['from']['username'] : null,
            'user_chat_id' => $chat_id
        ]);

        $caption = isset($request->message['caption']) ? $request->message['caption'] : null;
        if (isset($request->message['photo'])) {
            ClientReserveDetail::create([
                'file_id' => $request->message['photo'][0]['file_id'],
                'caption' => $caption,
                'message_id' => $request->message['message_id'],
                'message_dete_time' => Carbon::now(),
                'client_reserve_id' => $client_reserve->id,
                'message_type' => "photo"
            ]);
        } elseif (isset($request->message['voice'])) {
            ClientReserveDetail::create([
                'file_id' => $request->message['voice']['file_id'],
                'message_id' => $request->message['message_id'],
                'message_dete_time' => Carbon::now(),
                'client_reserve_id' => $client_reserve->id,
                'message_type' => "voice"
            ]);
        } elseif (isset($request->message['video'])) {
            ClientReserveDetail::create([
                'file_id' => $request->message['video']['file_id'],
                'message_id' => $request->message['message_id'],
                'message_dete_time' => Carbon::now(),
                'client_reserve_id' => $client_reserve->id,
                'message_type' => "video"
            ]);
        } elseif (isset($request->message['document'])) {
            ClientReserveDetail::create([
                'file_id' => $request->message['document']['file_id'],
                'caption' => $caption,
                'message_id' => $request->message['message_id'],
                'message_dete_time' => Carbon::now(),
                'client_reserve_id' => $client_reserve->id,
                'message_type' => "document"
            ]);
        } elseif (isset($request->message['text'])) {
            ClientReserveDetail::create([
                'message_text' => $request->message['text'],
                'message_id' => $request->message['message_id'],
                'message_dete_time' => Carbon::now(),
                'client_reserve_id' => $client_reserve->id,
                'message_type' => "text"
            ]);
        }
    }

    /**
     * Ish vaqti tugagan yoki tugamaganligini aniqlash candition
     */
    public function workingTimeCondition(): bool
    {
        $today =  Carbon::now();
        $hourMinut = (string)$today->format('H') . (string)$today->format('i');

        return ($hourMinut > 830 && $hourMinut < 2300);
        // return (0);
    }
}
