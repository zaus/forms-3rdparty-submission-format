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

	public function field_format($submission, $form, $service) {
		$settings = F3iFieldFormatOptions::settings();

		$fields = array();
		$pattern = array();
		$replace = array();

		foreach((array) $settings[F3iFieldFormatOptions::F_FIELDS] as $i => $input) {
			// url-style declaration for source+?destination
			parse_str($input, $f);
			$f = array_merge($fields, $f);

			//$fields = explode(F3iFieldFormatOptions::FIELD_DELIM, $settings[F3iFieldFormatOptions::F_FIELDS]);

			// regex - pattern, replace
			$pattern = array_merge($pattern, explode(F3iFieldFormatOptions::REGEX_DELIM, $settings[F3iFieldFormatOptions::F_PATTERNS][$i])); // '/(\d+)\/(\d+)\/(\d+)/';
			$replace = array_merge($replace, explode(F3iFieldFormatOptions::REGEX_DELIM, $settings[F3iFieldFormatOptions::F_REPLACEMENTS][$i])); //'$2-$1-$3';
		}

		### _log('bouwgenius-date', $fields, $submission); 

		foreach($fields as $dest => $src) {
			if(isset($submission[$src]) && !empty($submission[$src])) {
				$x = preg_replace($pattern, $replace, $submission[$src]);

				### _log($submission[$src], $x, $src);

				$submission[is_numeric($dest) ? $src : $dest] = $x;
			}
		}

		return $submission;
	}//--	fn	date_format
}//---	class	BouwgeniusDateFormat

// engage!
new F3iFieldFormat();