<?php

function paypal_directory() {
	if (!defined('WPLANG'))
		return 'en_US/';
	switch (true) {
		case preg_match("/^fr/i",WPLANG):
			return 'fr_FR/';
		case preg_match("/^de/i",WPLANG):
			return 'de_DE/';
		case preg_match("/^it/i",WPLANG):
			return 'it_IT/';
		case preg_match("/^ja/i",WPLANG):
			return 'ja_JP/';
		case preg_match("/^es/i",WPLANG):
			return 'es_XC/';
		case preg_match("/^nl/i",WPLANG):
			return 'nl_NL/';
		case preg_match("/^pl/i",WPLANG):
			return 'pl_PL/';
		case preg_match("/^zh_CN/i",WPLANG):
			return 'zh_XC/';
		case preg_match("/^zh_HK/i",WPLANG):
		case preg_match("/^zh_TW/i",WPLANG):
			return 'zh_HK/';
		default:
			return 'en_US/';
	}
}
