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
	 * @return array list of whitelisted special replacement functions
	 */
	public static function get_special_fns() {
		return array(self::FN_UPPER, self::FN_LOWER);
	}

	public function field_format($submission, $form, $service) {
		$settings = F3iFieldFormatOptions::settings();

		### _log(__FUNCTION__ . '-before', $submission, $settings);

		foreach((array) $settings[F3iFieldFormatOptions::F_FIELDS] as $i => $input) {
			// maybe also url-style declaration for source+?destination
			parse_str($input, $f);

			foreach($f as $dest => $src) {
				if(empty($src)) $src = $dest;

				if(!isset($submission[$src]) || empty($submission[$src])) continue;

				// corresponding settings
				$pattern = $settings[F3iFieldFormatOptions::F_PATTERNS][$i];
				$replace = $settings[F3iFieldFormatOptions::F_REPLACEMENTS][$i];

				### _log(sprintf('replacing "%s" with "%s" in "%s"', $pattern, $replace, $submission[$src]));

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

				$submission[$dest] = $x;
			}
		}

		### _log(__FUNCTION__ . '-after', $submission);

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