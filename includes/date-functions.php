<?php
/**
 * Date Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       1.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Gets date in specific format
 *
 * @since WPAS 4.0
 * @param array $myfield_opt field info
 * @param string $meta_value value of attribute
 * @param bool $reverse where to display
 * @return string $meta_value attr with new format
 */
if (!function_exists('emd_translate_date_format')) {
	function emd_translate_date_format($myfield_opt, $meta_value, $reverse = 0) {
		$emd_date_format_translation = array(
			'd' => 'j',
			'dd' => 'd',
			'oo' => 'z',
			'D' => 'D',
			'DD' => 'l',
			'm' => 'n',
			'mm' => 'm',
			'M' => 'M',
			'MM' => 'F',
			'y' => 'y',
			'yy' => 'Y'
		);
		$emd_time_format_translation = array(
			'H' => 'H',
			'HH' => 'H',
			'h' => 'H',
			'hh' => 'H',
			'mm' => 'i',
			'ss' => 's',
			'l' => 'u',
			'tt' => 'a',
			'TT' => 'A'
		);
		if (isset($myfield_opt['type']) && $meta_value != '' && !empty($myfield_opt['dformat'])) {
			$type = $myfield_opt['type'];
			$format = $myfield_opt['dformat'];
			$dformat = isset($format['dateFormat']) ? $format['dateFormat'] : '';
			$tformat = isset($format['timeFormat']) ? $format['timeFormat'] : '';
			switch ($type) {
				case 'date':
					$new_format = strtr($dformat, $emd_date_format_translation);
					$data_format = 'Y-m-d';
				break;
				case 'datetime':
					$new_format = strtr($dformat, $emd_date_format_translation) . " " . strtr($tformat, $emd_time_format_translation);
					if ($tformat == 'hh:mm:ss') {
						$data_format = 'Y-m-d H:i:s';
					} else {
						$data_format = 'Y-m-d H:i';
					}
				break;
				case 'time':
					$new_format = strtr($tformat, $emd_time_format_translation);
					if ($tformat == 'hh:mm:ss') {
						$data_format = 'H:i:s';
					} else {
						$data_format = 'H:i';
					}
				break;
				default:
					return $meta_value;
			}
			if ($reverse == 1) {
				if(DateTime::createFromFormat($data_format, $meta_value)){
					return DateTime::createFromFormat($data_format, $meta_value)->format($new_format);
				}
			} else {
				if(DateTime::createFromFormat($new_format, $meta_value)){
					return DateTime::createFromFormat($new_format, $meta_value)->format($data_format);
				}
			}
		}
		return $meta_value;
	}
}
if (!function_exists('emd_human_readable_date_range')) {
	function emd_human_readable_date_range($start_date,$end_date){
		$mysdate_arr = explode(" ",$start_date);
		$start_year = date("Y",strtotime($mysdate_arr[0]));
		$start_month = date("m",strtotime($mysdate_arr[0]));
		$start_day = date("d",strtotime($mysdate_arr[0]));

		$myedate_arr = explode(" ",$end_date);
		$end_year = date("Y",strtotime($myedate_arr[0]));
		$end_month = date("m",strtotime($myedate_arr[0]));
		$end_day = date("d",strtotime($myedate_arr[0]));

		$format = 'l, F j, Y';
		if($start_year == date('Y') && $end_year == date('Y')){
			$format = 'l, F j';
		}

		$mysdate_i18n = date_i18n($format,strtotime($mysdate_arr[0]));
		$myedate_i18n = date_i18n($format,strtotime($myedate_arr[0]));

		$mystime_i18n = "";     
		if(!empty($mysdate_arr[1]) && $mysdate_arr[1] != "00:00" && $mysdate_arr[1] != "00:00:00"){
			$mystime_i18n = date_i18n(get_option('time_format'),strtotime($mysdate_arr[1]));
		}
		$myetime_i18n = "";
		if(!empty($myedate_arr[1]) && $myedate_arr[1] != "00:00" && $myedate_arr[1] != "00:00:00"){
			$myetime_i18n = date_i18n(get_option('time_format'),strtotime($myedate_arr[1]));
		}
		if($end_year == $start_year){
			if($start_month == $end_month && $start_day == $end_day){
				$view = $mysdate_i18n; 
				if($mystime_i18n != ''){ 
					$view .= ' @ '  . $mystime_i18n; 
				}
				if($myetime_i18n != ''){ 
					$view .= ' - ' . $myetime_i18n;
				}
			}
			elseif($start_month == $end_month && $mystime_i18n == '' && $myetime_i18n == ''){
				$view = date_i18n('F',strtotime($mysdate_arr[0])) . ' ' . date_i18n('j',strtotime($mysdate_arr[0])) .  ' - ' . date_i18n('j',strtotime($myedate_arr[0]));
			}
			else{
				$view = $mysdate_i18n . " @ " . $mystime_i18n . " - " . $myedate_i18n . " @ " . $myetime_i18n;
			}
		}
		else {
			$view = $mysdate_i18n . " - " . $myedate_i18n;
		}
		return $view;
	}
}
