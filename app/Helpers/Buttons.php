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

    public $add_post = [
        'resize_keyboard' => true,
        'keyboard' => [
            [
                [
                    'text' => "Postni joylash"
                ]
            ],
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
}
