<?php
/**
 * Jalali Date Functions (jdf)
 * Version 3.2
 * Source: https://github.com/sallar/jdf
 */

if (!function_exists('jdate')) {
    function jdate($format, $timestamp = '', $none = '', $time_zone = 'Asia/Tehran', $tr_num = 'fa')
    {
        $T_sec = 0;
        if ($time_zone != 'local') {
            date_default_timezone_set(($time_zone == '' || $time_zone == 'Asia/Tehran') ? 'Asia/Tehran' : $time_zone);
        }
        if ($timestamp == '') {
            $timestamp = time();
        } elseif ((string)(int)$timestamp !== (string)$timestamp) {
            $timestamp = strtotime($timestamp);
        }
        $ts = $timestamp + $T_sec;
        $date = explode('_', date('H_i_j_n_w_Y', $ts));
        list($j_y, $j_m, $j_d) = gregorian_to_jalali($date[5], $date[3], $date[2]);
        $out = '';
        $sub = array('ss' => date('s', $ts), 'mm' => date('i', $ts), 'hh' => date('H', $ts), 'AA' => date('a', $ts), 'rr' => date('r', $ts), 'tt' => date('U', $ts));
        for ($i = 0; $i < strlen($format); $i++) {
            $chr = $format[$i];
            if ($chr == '\\') {
                $out .= $format[++$i];
            } else {
                $out .= jdate_chars($chr, $date, $j_y, $j_m, $j_d, $tr_num, $sub);
            }
        }
        return $out;
    }
}

if (!function_exists('jstrftime')) {
    function jstrftime($format, $timestamp = '', $none = '', $time_zone = 'Asia/Tehran', $tr_num = 'fa')
    {
        // جلالی استر‌اف‌تایم
        if ($time_zone != 'local') {
            date_default_timezone_set(($time_zone == '' || $time_zone == 'Asia/Tehran') ? 'Asia/Tehran' : $time_zone);
        }
        if ($timestamp == '') {
            $timestamp = time();
        } elseif ((string)(int)$timestamp !== (string)$timestamp) {
            $timestamp = strtotime($timestamp);
        }
        $ts = $timestamp;
        $date = explode('_', date('H_i_j_n_w_Y', $ts));
        list($j_y, $j_m, $j_d) = gregorian_to_jalali($date[5], $date[3], $date[2]);
        $out = '';
        for ($i = 0; $i < strlen($format); $i++) {
            $chr = $format[$i];
            if ($chr == '%') {
                $chr2 = $format[++$i];
                $out .= jdate_chars($chr2, $date, $j_y, $j_m, $j_d, $tr_num);
            } else {
                $out .= $chr;
            }
        }
        return $out;
    }
}
if (!function_exists('gregorian_to_jalali')) {
    function gregorian_to_jalali($g_y, $g_m, $g_d)
    {
        $g_days_in_month = array(31,28,31,30,31,30,31,31,30,31,30,31);
        $j_days_in_month = array(31,31,31,31,31,31,30,30,30,30,30,29);

        $gy = $g_y-1600;
        $gm = $g_m-1;
        $gd = $g_d-1;

        $g_day_no = 365*$gy+intval(($gy+3)/4)-intval(($gy+99)/100)+intval(($gy+399)/400);
        for ($i=0;$i<$gm;++$i)
            $g_day_no += $g_days_in_month[$i];
        if ($gm>1 && (($gy%4==0 && $gy%100!=0)||($gy%400==0)))
            $g_day_no++;
        $g_day_no += $gd;

        $j_day_no = $g_day_no-79;
        $j_np = intval($j_day_no/12053);
        $j_day_no %= 12053;

        $jy = 979+33*$j_np+4*intval($j_day_no/1461);
        $j_day_no %= 1461;

        if ($j_day_no >= 366) {
            $jy += intval(($j_day_no-1)/365);
            $j_day_no = ($j_day_no-1)%365;
        }

        for ($i=0; $i<11 && $j_day_no>=$j_days_in_month[$i]; ++$i)
            $j_day_no -= $j_days_in_month[$i];
        $jm = $i+1;
        $jd = $j_day_no+1;

        return array($jy, $jm, $jd);
    }
}

if (!function_exists('jalali_to_gregorian')) {
    function jalali_to_gregorian($j_y, $j_m, $j_d)
    {
        $g_days_in_month = array(31,28,31,30,31,30,31,31,30,31,30,31);
        $j_days_in_month = array(31,31,31,31,31,31,30,30,30,30,30,29);

        $jy = $j_y-979;
        $jm = $j_m-1;
        $jd = $j_d-1;

        $j_day_no = 365*$jy + intval($jy/33)*8 + intval(($jy%33+3)/4);
        for ($i=0; $i<$jm; ++$i)
            $j_day_no += $j_days_in_month[$i];

        $j_day_no += $jd;
        $g_day_no = $j_day_no+79;

        $gy = 1600+400*intval($g_day_no/146097);
        $g_day_no %= 146097;

        $leap = true;
        if ($g_day_no >= 36525) {
            $g_day_no--;
            $gy += 100*intval($g_day_no/36524);
            $g_day_no %= 36524;

            if ($g_day_no >= 365) $g_day_no++;
            else $leap = false;
        }

        $gy += 4*intval($g_day_no/1461);
        $g_day_no %= 1461;

        if ($g_day_no >= 366) {
            $leap = false;
            $g_day_no--;
            $gy += intval($g_day_no/365);
            $g_day_no %= 365;
        }

        for ($i=0; $g_day_no>=$g_days_in_month[$i]+($i==1 && $leap); $i++)
            $g_day_no -= $g_days_in_month[$i]+($i==1 && $leap);

        $gm = $i+1;
        $gd = $g_day_no+1;

        return array($gy, $gm, $gd);
    }
}
if (!function_exists('jdate_chars')) {
    function jdate_chars($chr, $date, $j_y, $j_m, $j_d, $tr_num, $sub = [])
    {
        switch ($chr) {
            case 'Y': return tr_num($j_y, $tr_num);
            case 'y': return tr_num(substr($j_y, 2, 2), $tr_num);
            case 'm': return tr_num(str_pad($j_m, 2, '0', STR_PAD_LEFT), $tr_num);
            case 'n': return tr_num($j_m, $tr_num);
            case 'd': return tr_num(str_pad($j_d, 2, '0', STR_PAD_LEFT), $tr_num);
            case 'j': return tr_num($j_d, $tr_num);
            case 'H': return tr_num($date[0], $tr_num);
            case 'i': return tr_num($date[1], $tr_num);
            case 's': return tr_num($sub['ss'] ?? date('s'), $tr_num);
            case 'a': return (intval($date[0]) < 12 ? 'ق.ظ' : 'ب.ظ');
            case 'A': return (intval($date[0]) < 12 ? 'قبل‌ازظهر' : 'بعدازظهر');
            case 'w': return tr_num($date[4], $tr_num);
            case 'W': return jdate_days_name($date[4]);
            case 'F': return jdate_month_name($j_m);
            case 'M': return jdate_month_name($j_m, true);
            case 't': return jlastday($j_y, $j_m);
            case 'L': return (int)is_kabise($j_y);
            // سایر کاراکترها را همینطور برگردان
            default: return $chr;
        }
    }
}

// تبدیل اعداد به فارسی یا انگلیسی
if (!function_exists('tr_num')) {
    function tr_num($str, $mod = 'fa', $mf = '٫')
    {
        $num_a = ['0','1','2','3','4','5','6','7','8','9','.'];
        $key_a = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹',$mf];
        return ($mod != 'fa') ? str_replace($key_a, $num_a, $str) : str_replace($num_a, $key_a, $str);
    }
}
if (!function_exists('jdate_month_name')) {
    function jdate_month_name($m, $short = false)
    {
        $months = [
            1 => ['فروردین','فرو'],
            2 => ['اردیبهشت','ارد'],
            3 => ['خرداد','خرد'],
            4 => ['تیر','تیر'],
            5 => ['مرداد','مر'],
            6 => ['شهریور','شهر'],
            7 => ['مهر','مهر'],
            8 => ['آبان','آبا'],
            9 => ['آذر','آذر'],
            10 => ['دی','دی'],
            11 => ['بهمن','بهم'],
            12 => ['اسفند','اسف']
        ];
        return $short ? $months[$m][1] : $months[$m][0];
    }
}

if (!function_exists('jdate_days_name')) {
    function jdate_days_name($w)
    {
        $days = [
            0 => 'یک‌شنبه',
            1 => 'دوشنبه',
            2 => 'سه‌شنبه',
            3 => 'چهارشنبه',
            4 => 'پنج‌شنبه',
            5 => 'جمعه',
            6 => 'شنبه'
        ];
        return $days[$w];
    }
}
if (!function_exists('is_kabise')) {
    function is_kabise($year)
    {
        return (((($year-474)%2820)+474+38)*682)%2816<682;
    }
}

if (!function_exists('jlastday')) {
    function jlastday($y, $m)
    {
        if ($m < 7)
            return 31;
        if ($m < 12)
            return 30;
        return is_kabise($y) ? 30 : 29;
    }
}
