<?php

const CONSOLE_LOG_VERBOSE = 1;
const CONSOLE_LOG_RAW = 2;

const UTF8_SYMBOL_OK = '✔︎';
const UTF8_SYMBOL_ERROR = '✖︎';
const UTF8_SYMBOL_WARNING = '⚠︎';

if (!defined('DEBUG')) {
	define('DEBUG', false);
}

function console(string $message, int $options = 0) {
	static $sequence = "\033[%sm";

	static $styles = [
		// 'reset'  => '0',
		'bold'      => '1', 'b' => '1', 'strong' => '1',
		'dark'      => '2',
		'italic'    => '3', 'i' => '3', 'em' => '3',
		'underline' => '4', 'u' => '4',
		'blink'     => '5',
		'reverse'   => '7', 'invert' => '7',
		'hide'      => '8', 'invisible' => '8',

		'black'         => '30',
		'red'           => '31', 'error' => '31',
		'green'         => '32', 'success' => '32',
		'yellow'        => '33', 'warning' => '33',
		'blue'          => '34', 'info' => '34',
		'magenta'       => '35', 'pink' => '35',
		'cyan'          => '36', 'link' => '36',
		// 'light-gray'    => '37', // Not using due similarity to white
		'default'       => '39', 'normal' => '39',
		'dim'           => '90', 'gray' => '90', 'grey' => '90',
		'light-red'     => '91', 'error-light' => '91',
		'light-green'   => '92', 'success-light' => '92',
		'light-yellow'  => '93', 'warning-light' => '93',
		'light-blue'    => '94', 'info-light' => '94',
		'light-magenta' => '95', 'light-pink' => '95',
		'light-cyan'    => '96', 'link-light' => '96',
		'white'         => '97', 'bright' => '97',

		'bg:black'            => '40',
		'bg:red'              => '41', 'bg:error' => '41',
		'bg:green'            => '42', 'bg:success' => '42',
		'bg:yellow'           => '43', 'bg:warning' => '43',
		'bg:blue'             => '44', 'bg:info' => '44',
		'bg:magenta'          => '45', 'bg:pink' => '45',
		'bg:cyan'             => '46', 'bg:link' => '46',
		// 'bg:light-gray'       => '47', // Not using due similarity to white
		'bg:default'          => '49', 'bg:normal' => '49',
		'bg:dim'              => '100', 'bg:gray' => '100', 'bg:grey' => '100',
		'bg:light-red'        => '101', 'bg:error-light' => '101',
		'bg:light-green'      => '102', 'bg:success-light' => '102',
		'bg:light-yellow'     => '103', 'bg:warning-light' => '103',
		'bg:light-blue'       => '104', 'bg:info-light' => '104',
		'bg:light-magenta'    => '105', 'bg:light-pink' => '105',
		'bg:light-cyan'       => '106', 'bg:link-light' => '106',
		'bg:white'            => '107',
	];

	static $REG_EXP_OPENING = '/<('.implode('|', array_keys($styles)).')>/';
	static $REG_EXP_CLOSING = '/<\/('.implode('|', array_keys($styles)).')>/';

	$verbose = $options & CONSOLE_LOG_VERBOSE;

	if ($verbose && !DEBUG) {
		return;
	}

	$raw = $options & CONSOLE_LOG_RAW;

	if ($raw) {
		echo "{$message}";
	} else {
		$opening = preg_replace_callback($REG_EXP_OPENING, function ($matches) use ($styles, $sequence) {
			return sprintf($sequence, $styles[$matches[1]]);
		}, $message);

		$closing = preg_replace_callback($REG_EXP_CLOSING, function ($matches) use ($styles, $sequence) {
			return sprintf($sequence, '0');
		}, $opening);

		echo "{$closing}";
	}
}

function debug(string $message, int $options = 0) {
	console("<dim>{$message}</dim>", $options | CONSOLE_LOG_VERBOSE);
}

function success(string $message, int $options = 0) {
	console("<success>{$message}</success>", $options);
}

function error(string $message, int $options = 0) {
	console("<error>{$message}</error>", $options);
}

function warning(string $message, int $options = 0) {
	console("<warning>{$message}</warning>", $options);
}
