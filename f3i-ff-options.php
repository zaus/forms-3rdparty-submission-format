<?php

if(!class_exists('Wp_Options_Base')) include('wp-options-base.php');
class F3iFieldFormatOptions extends Wp_Options_Base {
	// generated via http://wpsettingsapi.jeroensormani.com/

	const N = 'f3i_ff_settings';
	const T = 'Forms 3rdparty Submission Formatter';
	const Ts = 'Forms 3rdparty Formatter';
	const X = 'wordpress';

	const F_FIELDS = 'fields';
	const F_PATTERNS = 'patterns';
	const F_REPLACEMENTS = 'replacements';

	const CAPABILITY = 'manage_options';

	function settings_init(  ) {

		register_setting( self::N, self::N, array(&$this, 'prep_settings') );

		$section = 'f3i_ff_pluginPage_section';
		add_settings_section(
			$section,
			__( 'Field Formatting Replacement', self::X ), 
			array(&$this, 'section'), 
			self::N
		);

		add_settings_field( 
			self::F_FIELDS, 
			__( 'Submission Field Name(s)', self::X ), 
			array(&$this, 'render_names'), 
			self::N, 
			$section
		);

		add_settings_field( 
			self::F_PATTERNS, 
			__( 'Given Format (regex pattern)', self::X ), 
			array(&$this, 'render_pattern'), 
			self::N, 
			$section
		);

		add_settings_field(
			self::F_REPLACEMENTS, 
			__( 'Expected Format (regex replacement or function)', self::X ),
			array(&$this, 'render_replace'), 
			self::N, 
			$section
		);
	}//--	settings_init

	function prep_settings($settings) {
		### _log(__FUNCTION__, $settings);

		$this->_prepare_multiple_setting($settings, self::F_FIELDS);
		$this->_prepare_multiple_setting($settings, self::F_PATTERNS);
		$this->_prepare_multiple_setting($settings, self::F_REPLACEMENTS);

		### _log(__FUNCTION__ . '-after', $settings);


		// make sure there are corresponding fields for each; if not duplicate until there are
		$n = count($settings[self::F_FIELDS]);
		foreach(array(self::F_PATTERNS, self::F_REPLACEMENTS) as $f) {
			$settings[$f] = (array)$settings[$f];
			for ($i = count($settings[$f]); $i < $n; $i++) {
				$settings[$f][$i] = end($settings[$f]);
			}
		}

		// make sure patterns are properly surrounded
		foreach($settings[self::F_PATTERNS] as $i => &$v) {
			// assume if it didn't start with slash, also needs it at the end
			if(substr($v, 0, 1) != '/') $v = '/' . $v . '/';
		}

		### _log(__FUNCTION__ . '-afterafter', $settings);

		return $settings;
	}//--	prep_settings

	function render_names(  ) { 
		$this->_render(self::F_FIELDS, true);
	}

	function render_pattern(  ) { 
		$this->_render(self::F_PATTERNS, true);
		?>
		<p><?php $this->p('em', 'Example: split sequence of three numbers separated by slashes (i.e. a date)') ?> = <code>/(\d+)\/(\d+)\/(\d+)/</code><p>
		<?php

	}

	function render_replace(  ) { 
		$this->_render(self::F_REPLACEMENTS, true);
		?>
		<p><?php $this->p('em', 'Example: reorder the three date segments from above') ?> = <code>$2-$1-$3</code><p>
		<?php

	}


	function section(  ) { 
		$this->p('p', 'Enter the field name(s), optionally as %s to be parsed and rearranged according to the given (regex) patterns and replacements.'
			, '<code>source=destination</code>');
		$this->p('p', 'Separate multiple fields, patterns, and replacements with %s to create new lines on saving.  Leave a line empty to remove it.'
			, '<code>' . self::MULTI_DELIM . '</code>');
		$this->p('p', 'If multiple fields provided, there should be corresponding entries for each field; it will automatically copy the last pattern/replacement to fill the list.');
		$this->p('p', 'Special functions are available as replacements: %s'
			, '<code>' . implode('</code>, <code>', F3iFieldFormat::get_special_fns()  ) . '</code>');
	}

}//---	F3iFieldFormatOptions


