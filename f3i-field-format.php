<?php
/*

Plugin Name: Forms-3rdparty Submission Reformat
Plugin URI: https://github.com/zaus/forms-3rdparty-submission-format
Description: Reformat specific field submission
Author: zaus
Version: 0.2
Author URI: http://drzaus.com
Changelog:
	0.1	initial
	0.2 options-based
*/

class F3iFieldFormat {

	const N = 'F3iFieldFormat';
	const B = 'Forms3rdPartyIntegration';

	public function __construct() {
		// hook early to clean stuff out before other plugins
		add_filter(self::B.'_get_submission', array(&$this, 'field_format'), 22, 3);
		
		// include options settings
		require('f3i-ff-options.php');
		new F3iFieldFormatOptions(__FILE__);
	}

	/**
	 * Uppercase function
	 */
	const FN_UPPER = 'upper';
	/**
	 * Lowercase function
	 */
	const FN_LOWER = 'lower';

	/**
	 * Placeholder value to check if not set
	 */
	const V_UNSET = -1;

	/**
	 * @return array list of special replacement functions
	 */
	public static function get_special_fns() {
		return array(self::FN_UPPER, self::FN_LOWER);
	}

	public function field_format($submission, $form, $service) {
		$settings = F3iFieldFormatOptions::settings();

		$fields = array();
		$pattern = array();
		$replace = array();

		foreach((array) $settings[F3iFieldFormatOptions::F_FIELDS] as $i => $input) {
			_log(__FUNCTION__, $i, $input);
			// maybe also url-style declaration for source+?destination
			parse_str($input, $f);
			$f = array_merge($fields, $f);

			//$fields = explode(F3iFieldFormatOptions::FIELD_DELIM, $settings[F3iFieldFormatOptions::F_FIELDS]);

			// regex - pattern, replace
			$pattern = array_merge($pattern, explode(F3iFieldFormatOptions::MULTI_DELIM, $settings[F3iFieldFormatOptions::F_PATTERNS][$i])); // '/(\d+)\/(\d+)\/(\d+)/';
			$replace = array_merge($replace, explode(F3iFieldFormatOptions::MULTI_DELIM, $settings[F3iFieldFormatOptions::F_REPLACEMENTS][$i])); //'$2-$1-$3';
		}

		### _log(__FUNCTION__, $fields, $submission);

		foreach($fields as $dest => $src) {
			if(isset($submission[$src]) && !empty($submission[$src])) {
				// untouched value; if it's still this after checking special functions just do regular replacement
				$x = self::V_UNSET;
				// are we using a special function?
				foreach(self::get_special_fns() as $f) {
					if(substr($replace, 0, strlen($f)) === $f) {
						$x = preg_replace_callback($pattern, array(&$this, $f), $submission[$src]);
						break;
					}
				}
				if($x === self::V_UNSET) $x = preg_replace($pattern, $replace, $submission[$src]);

				### _log($submission[$src], $x, $src);

				$submission[is_numeric($dest) ? $src : $dest] = $x;
			}
		}

		return $submission;
	}//--	fn	field_format

	public function upper($matches) {
		return strtoupper($matches[0]);
	}
	public function lower($matches) {
		return strtolower($matches[0]);
	}

}//---	class	F3iFieldFormat

// engage!
new F3iFieldFormat();