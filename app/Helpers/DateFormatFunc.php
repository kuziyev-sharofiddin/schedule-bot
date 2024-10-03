<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateFormatFunc
{
    public function validateDateText($text)
    {
        $parts = explode("\n", $text);

        if (count($parts) !== 2) {
            return false;
        }

        [$weekday, $date] = $parts;

        try {
            $parsedDate = Carbon::createFromFormat('d.m.Y', $date);

            if (!$parsedDate || $parsedDate->format('d.m.Y') !== $date) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function validateDateTime($input, $date)
    {
        if (preg_match('/^(?:[01]?\d|2[0-3]):[0-5]\d$/', $input)) {
            // Hozirgi sana va vaqt
            $currentDate = Carbon::now()->format("Y-m-d"); // Hozirgi sana
            $inputDate = Carbon::parse($date)->format("Y-m-d"); // Kiritilgan sana
            $currentTime = Carbon::now()->format('H:i'); // Kiritilgan vaqt
            $inputTime = Carbon::parse($input)->format('H:i'); // Kiritilgan vaqt

            $check_date = $currentDate == $inputDate;
            //agar bugungi sanaga teng bo'lsa
            if($check_date){
                $chack_time = $currentTime > $inputTime;
                if($chack_time){
                    return false;
                }else{
                    return true;
                }
            }else{
                return true;
            }
        }

        return "preg"; // Kiritilgan vaqt noto'g'ri formatda
    }
}
