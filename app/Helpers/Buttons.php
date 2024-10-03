<?php

namespace App\Helpers;

use App\Models\ClientReserve;
use App\Models\Message;
use Carbon\Carbon;

class Buttons
{
    protected $daysOfWeek = [
        'Monday' => 'Dushanba',
        'Tuesday' => 'Seshanba',
        'Wednesday' => 'Chorshanba',
        'Thursday' => 'Payshanba',
        'Friday' => 'Juma',
        'Saturday' => 'Shanba',
        'Sunday' => 'Yakshanba'
    ];
    public $number_buttons = [
        'resize_keyboard' => true,
        'keyboard' => [
            [
                ['text' => "01:00"], ['text' => "02:00"], ['text' => "03:00"], ['text' => "04:00"],
            ],
            [
                ['text' => "05:00"], ['text' => "06:00"], ['text' => "07:00"], ['text' => "08:00"],
            ],
            [
                ['text' => "09:00"], ['text' => "10:00"], ['text' => "11:00"], ['text' => "12:00"],
            ],
            [
                ['text' => "13:00"], ['text' => "14:00"], ['text' => "15:00"], ['text' => "16:00"],
            ],
            [
                ['text' => "17:00"], ['text' => "18:00"], ['text' => "19:00"], ['text' => "20:00"],
            ],
            [
                ['text' => "21:00"], ['text' => "22:00"], ['text' => "23:00"], ['text' => "00:00"]
            ],
            [
                ['text' => "⏪ Ortga"]
            ],
        ]
    ];
    public $report_detail_buttons = [
        'resize_keyboard' => true,
        'keyboard' => [
            [
                [
                    'text' => "Yangi post joylash"
                ]
            ],
            [
                [
                    'text' => "Rejalashtirilgan postlar hisoboti"
                ]
            ],
            [
                [
                    'text' => "Yuborilgan postlar hisoboti"
                ]
            ],
        ]
    ];

    public $come_back = [
        'resize_keyboard' => true,
        'keyboard' => [
            [
                [
                    'text' => "⏪ Ortga"
                ]
            ],
        ]
    ];
    public $completed_button = [
        'resize_keyboard' => true,
        'keyboard' => [
            [
                [
                    'text' => "Yakunlash"
                ]
            ],
            [
                [
                    'text' => "❌ Postni o'chirish"
                ]
            ],

            [
                [
                    'text' => "⏪ Ortga"
                ]
            ],
        ]
    ];

    public $confirm_button = [
        'resize_keyboard' => true,
        'keyboard' => [
            [
                [
                    'text' => "Tasdiqlash"
                ]
            ],
            [
                [
                    'text' => "⏪ Ortga"
                ]
            ],
        ]
    ];
    public $delete_post = [
        'resize_keyboard' => true,
        'keyboard' => [
            [
                [
                    'text' => "❌ Postni olib tashlash"
                ]
            ],
            [
                [
                    'text' => "⏪ Ortga"
                ]
            ],
        ]
    ];

    public function posts() {
        $posts = Message::query()->get();

        $keyboard = [];

        foreach ($posts as $post) {
            $keyboard[] = [
                [
                    'text' => $post->weekDay . ' ' . $post->time
                ]
            ];
        }

        // Ortga tugmasini qo'shamiz
        $keyboard[] = [
            [
                'text' => '⏪ Ortga'
            ],
        ];

        return [
            'resize_keyboard' => true,
            'keyboard' => $keyboard
        ];
    }
    public function getReportButtons()
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        $day_after_tomorrow = Carbon::today()->addDays(2);
        $tomorrw_ay_after_tomorrow = Carbon::today()->addDays(3);

        $today_weekday = $this->daysOfWeek[$today->format('l')];
        $tomorrow_weekday = $this->daysOfWeek[$tomorrow->format('l')];
        $day_after_tomorrow_weekday = $this->daysOfWeek[$day_after_tomorrow->format('l')];
        $tomorrw_ay_after_tomorrow_weekday = $this->daysOfWeek[$tomorrw_ay_after_tomorrow->format('l')];

        return [
            'resize_keyboard' => true,
            'keyboard' => [
                [
                    [
                        'text' => $today_weekday . "\n" . $today->format('d.m.Y')
                    ],
                    [
                        'text' => $tomorrow_weekday . "\n" . $tomorrow->format('d.m.Y')
                    ],
                ],
                [
                    [
                        'text' => $day_after_tomorrow_weekday . "\n" . $day_after_tomorrow->format('d.m.Y')
                    ],
                    [
                        'text' => $tomorrw_ay_after_tomorrow_weekday . "\n" . $tomorrw_ay_after_tomorrow->format('d.m.Y')
                    ]
                ],
                [
                    [
                        'text' => "⏪ Ortga"
                    ]
                ],
            ]
        ];
    }

    public function getNumberButtons($text)
    {
        $today_weekday = $this->daysOfWeek[Carbon::today()->format('l')];
        if ($text == $today_weekday) {
            $currentHour = Carbon::now()->hour;
            \Log::info('Current hour: ' . $currentHour);
            $buttons = [
                'resize_keyboard' => true,
                'keyboard' => []
            ];
            $row = [];
            for ($hour = $currentHour + 1; $hour <= 23; $hour++) {
                $formattedHour = str_pad($hour, 2, '0', STR_PAD_LEFT) . ":00";
                $row[] = ['text' => $formattedHour];

                if (count($row) == 4) {
                    $buttons['keyboard'] [] = $row;
                    $row = [];
                }
            }
            if (!empty($row)) {
                $buttons['keyboard'][] = $row;
            }
            $buttons['keyboard'][] = [['text' => '⏪ Ortga']];
            return $buttons;
        } else {
            return $this->number_buttons;
        }
    }

}
