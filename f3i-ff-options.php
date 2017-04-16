<?php

if(!class_exists('Wp_Options_Base')) include('wp-options-base.php');
class F3iFieldFormatOptions extends Wp_Options_Base {
	// generated via http://wpsettingsapi.jeroensormani.com/

	const N = 'f3i_ff_settings';
	const T = 'Forms 3rdparty Submission Formatter';
	const Ts = 'Forms 3rdparty Formatter';
	const X = 'wordpress';

	const FIELD_DELIM = ',';
	const REGEX_DELIM = '|||';

	const F_FIELDS = 'fields';
	const F_PATTERNS = 'patterns';
	const F_REPLACEMENTS = 'replacements';

	const CAPABILITY = 'manage_options';


	function settings_init(  ) { 

		register_setting( self::N, self::N );

		add_settings_section(
			'f3i_ff_pluginPage_section', 
			__( 'Field Formatting Replacement', self::X ), 
			array(&$this, 'section'), 
			self::N
		);

		add_settings_field( 
			self::F_FIELDS, 
			__( 'Submission Field Name(s)', self::X ), 
			array(&$this, 'render_names'), 
			self::N, 
			'f3i_ff_pluginPage_section' 
		);

		add_settings_field( 
			self::F_PATTERNS, 
			__( 'Given Format (regex pattern)', self::X ), 
			array(&$this, 'render_pattern'), 
			self::N, 
			'f3i_ff_pluginPage_section' 
		);

		add_settings_field( 
			self::F_REPLACEMENTS, 
			__( 'Expected Format (regex replacement)', self::X ), 
			array(&$this, 'render_replace'), 
			self::N, 
			'f3i_ff_pluginPage_section' 
		);
	}//--	settings_init

	function render_names(  ) { 
		$this->_render(self::F_FIELDS, true);
	}

	function render_pattern(  ) { 
		$this->_render(self::F_PATTERNS, true);
		?>
		<p><em>Example:</em> <code>/(\d+)\/(\d+)\/(\d+)/</code><p>
		<?php

	}

	function render_replace(  ) { 
		$this->_render(self::F_REPLACEMENTS, true);
		?>
		<p><em>Example:</em> <code>$2-$1-$3</code><p>
		<?php

	}


	function section(  ) { 
		$this->p('p', 'Enter the field name(s) in url-format (ex. %s) to be parsed and rearranged according to the given patterns and replacements.'
			, '<code>field1&field2&split_dest1=field3&split_dest2=field3</code>');
		$this->p('p', 'Separate multiple patterns and replacements with %s'
			, '<code>' . self::REGEX_DELIM . '</code>');
		$this->p('p', 'If multiple fields provided, there should be corresponding entries for each field.');
	}

}//---	F3iFieldFormatOptions


