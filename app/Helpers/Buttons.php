<?php

namespace App\Helpers;

use App\Models\ClientReserve;
use Carbon\Carbon;

class Buttons
{
    public $report_detail_buttons = [
        'resize_keyboard' => true,
        'keyboard' => [
            [
                [
                    'text' => "Oxirgi 1 soat ichida"
                ]
            ],
            [
                [
                    'text' => "Bugungi kun bo'yicha"
                ]
            ],
            [
                [
                    'text' => "Kechagi kun bo'yicha"
                ]
            ],
            [
                [
                    'text' => "⏪ Ortga"
                ]
            ]
        ]
    ];

    public $report_buttons = [
        'resize_keyboard' => true,
        'keyboard' => [
            [
                [
                    'text' => "Monday"
                ],
                [
                    'text' => "Tuesday"
                ],

                [
                    'text' => "Wednesday"
                ]
            ],
            [
                [
                    'text' => "Thursday"
                ],
                [
                    'text' => "Friday"
                ],

                [
                    'text' => "Saturday"
                ]
            ],
            [
                [
                    'text' => "Sunday"
                ]
            ],
        ]
    ];

    public $number_buttons = [
        'resize_keyboard' => true,
        'keyboard' => [
                [
                    [
                        'text' => "01:00"
                    ],
                    [
                        'text' => "02:00"
                    ],
                    [
                        'text' => "03:00"
                    ],
                    [
                        'text' => "04:00"
                    ],
                ],
            [
                [
                    'text' => "05:00"
                ],
                [
                    'text' => "06:00"
                ],
                [
                    'text' => "07:00"
                ],
                [
                    'text' => "08:00"
                ],
            ],
            [
                [
                    'text' => "09:00"
                ],
                [
                    'text' => "10:00"
                ],
                [
                    'text' => "11:00"
                ],
                [
                    'text' => "12:00"
                ],
            ],
            [
                [
                    'text' => "13:00"
                ],
                [
                    'text' => "14:00"
                ],
                [
                    'text' => "15:00"
                ],
                [
                    'text' => "16:00"
                ],
            ],
            [
                [
                    'text' => "17:00"
                ],
                [
                    'text' => "18:00"
                ],
                [
                    'text' => "19:00"
                ],
                [
                    'text' => "20:00"
                ],
            ],
            [
                [
                    'text' => "21:00"
                ],
                [
                    'text' => "22:00"
                ],
                [
                    'text' => "23:00"
                ],
                [
                    'text' => "00:00"
                ],
            ],
        ]
    ];

    public $report_detail_buttons_for_admin = [
        'inline_keyboard' => [
            [
                [
                    'text' => "Oxirgi 1 soat ichida",
                    'callback_data' => 'oxirgi_1_soat_ichida'
                ]
            ],
            [
                [
                    'text' => "Bugungi kun bo'yicha",
                    'callback_data' => 'bugungi_kun_bo\'yicha'
                ]
            ],
            [
                [
                    'text' => "Kechagi kun bo'yicha",
                    'callback_data' => 'kechagi_kun_bo\'yicha'
                ]
            ],
            [
                [
                    'text' => "⏪ Ortga",
                    'callback_data' => 'ortga'
                ]
            ]
        ]
    ];

    public function status_start_button_for_admin()
    {
        return $status_start_button_for_admin = [
            'inline_keyboard' => [
                [
                    ['text' => "🟢 Ish jarayonini boshlash", 'callback_data' => 'ish_jarayonini_boshlash']
                ],
                [
                    ['text' => "👥 Ish jarayonidagi adminlar", 'callback_data' => 'ish_jarayonidagi_adminlar']
                ],
                [
                    ['text' => "✍️ Murojaat qilgan mijozlar", 'callback_data' => 'murojaat_qilgan_mijozlar']
                ],
                [
                    ['text' => "👥 Kechikib javob berilgan mijozlar", 'callback_data' => 'kechikib_javob_berilgan_mijozlar']
                ],
                [
                    ['text' => "📧 Zaxiradagi mijozlar (" . $this->clientReserveCount() . ")", 'callback_data' => 'zaxiradagi_mijozlar']
                ]
            ]
        ];
    }

    public function status_stop_button_for_admin()
    {
        return $status_stop_button_for_admin = [
            'inline_keyboard' => [
                [
                    ['text' => "🔴 Ish jarayonini yakunlash", 'callback_data' => 'ish_jarayonini_yakunlash']
                ],
                [
                    ['text' => "👥 Ish jarayonidagi adminlar", 'callback_data' => 'ish_jarayonidagi_adminlar']
                ],
                [
                    ['text' => "✍️ Murojaat qilgan mijozlar", 'callback_data' => 'murojaat_qilgan_mijozlar']
                ],
                [
                    ['text' => "👥 Kechikib javob berilgan mijozlar", 'callback_data' => 'kechikib_javob_berilgan_mijozlar']
                ],
                [
                    ['text' => "📧 Zaxiradagi mijozlar (" . $this->clientReserveCount() . ")", 'callback_data' => 'zaxiradagi_mijozlar']
                ]
            ]
        ];
    }

    public $code_for_client = [
        'resize_keyboard' => true,
        'keyboard' => [
            [
                [
                    'text' => "🆔 Chegirma Kodini olish"
                ]
            ]
        ]
    ];

    public $invite_group_link = [
        'inline_keyboard' => [
            [
                [
                    'text' => "GARANT SAVDO guruhiga obuna bo'lish",
                    'url' => 'https://t.me/garantsavdo_chat'
                ]
            ],
            [
                [
                    'text' => "GARANT GOLD guruhiga obuna bo'lish",
                    'url' => 'https://t.me/garant_gold_markazi'
                ]
            ]
        ]
    ];

    /**
     * Zaxiradagi mijozlar sonini bilish
     */
    private function clientReserveCount()
    {
        $client_reserve_count = ClientReserve::query()
            ->whereNull('admin_id')
            ->count();

        return $client_reserve_count;
    }
}
