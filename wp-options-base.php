<?php
abstract class Wp_Options_Base {
	// generated via http://wpsettingsapi.jeroensormani.com/

//	const N = 'your_plugin';
//	const T = 'Your plugin long title';
//	const Ts = 'Short Title';
	const X = 'wordpress';

	const CAPABILITY = 'manage_options';

	/**
	 * How to separate multiple fields for automatic splitting
	 */
	const MULTI_DELIM = ';';


	#region ------------------ setup ------------------

	public static function settings() {
		return get_option( static::N );
	}

	/**
	 * @var string the plugin basename root, used to set the action link
	 */
	var $root;

	public function __construct($root) {
		$this->root = $root;
		add_action( 'admin_menu', array(&$this, 'add_admin_menu') );
		add_action( 'admin_init', array(&$this, 'settings_init') );
	}


	function add_admin_menu(  ) {
		add_options_page(
			static::T,
			static::Ts,
			static::CAPABILITY,
			static::N,
			array(&$this, 'options_page')
		);

		//add plugin entry settings link
		add_filter( 'plugin_action_links', array(&$this, 'plugin_action_links'), 10, 2 );

		// admin scripts

	}//--	add_admin_menu

	/**
	 * HOOK - Add the "Settings" link to the plugin list entry
	 * @param $links
	 * @param $file
	 */
	function plugin_action_links( $links, $file ) {
		if ( $file != plugin_basename($this->root ) )
			return $links;

		$url = esc_url_raw(admin_url('options-general.php?page=' . static::N));

		$settings_link = '<a title="Capability ' . static::CAPABILITY . ' required" href="' . esc_attr( $url ) . '">'
			. esc_html( __( 'Settings', static::X ) ) . '</a>';

		array_unshift( $links, $settings_link );

		return $links;
	}

	#endregion ------------------ setup ------------------


	#region ------------------ helpers ------------------

	function _render($field, $isMultiple = false) {
		$options = static::settings();
		## _log(__FUNCTION__, $options);

		if(!isset($options[$field])) $options[$field] = $isMultiple ? array('') : '';
		if(is_array($options[$field])) {
			$isMultiple = true;
			// make sure we have at least one value to loop
			if(empty($options[$field])) $options[$field] = array('');
		}

		foreach( (array) $options[$field] as $k => $v) {
			?>
			<div>
				<input type="text" name="<?php echo static::N, '[', $field;
				if ($isMultiple) echo ']['; ?>]" value="<?php echo $v;
				if ($isMultiple) echo '" class="multiple' ?>">
			</div>
			<?php
		}
	}

	/**
	 * Call within a sanitize callback to prepare an `$isMultiple` setting
	 * @param array $settings all settings for plugin
	 * @param string $key indexed field within $settings corresponding to @see _render with `$isMultiple=true`
	 * @param bool $remove_empties whether or not to remove empty values
	 * @param string $delim optionally specify a delimiter other than @see self::MULTI_DELIM
	 * @return array
	 */
	function _prepare_multiple_setting(&$settings, $key, $remove_empties = true, $delim = self::MULTI_DELIM) {
		### _log(__FUNCTION__, $settings, $key, $delim);

		$list = [];
		// closure scope http://stackoverflow.com/a/8403958/1037948
		array_walk( $settings[$key], function($val, $k) use (&$list, $delim) {
			$val = array_map('trim', explode($delim, $val));
			$list = array_merge($list, $val);
		});

		if($remove_empties) $list = array_filter($list);

		$settings[$key] = $list;
		return $list;
	}

	/**
	 * Like c# string.format
	 * @param $message string the message to translate
	 * @param $tokens array/mixed optionally provide extra tokens
	 * @return string
	 */
	function x($message, $tokens = array()) {
		if(!is_array($tokens)) $tokens = array_slice(func_get_args(), 1);
		return vsprintf( __( $message, static::X ), $tokens);
	}

	/**
	 * Echo a message with optional tokens
	 * @param $message string the message to translate
	 * @param $tokens array/mixed optionally provide extra tokens
	 */
	function e($message, $tokens = array()) {
		if(!is_array($tokens)) $tokens = array_slice(func_get_args(), 1);
		echo $this->x($message, $tokens);
	}
	/**
	 * Echo a message with optional tokens and tag
	 * @param $tag string the html tag to wrap
	 * @param $message string the message to translate
	 * @param $tokens array/mixed optionally provide extra tokens
	 */
	function p($tag, $message, $tokens = array()) {
		if(!is_array($tokens)) $tokens = array_slice(func_get_args(), 2);
		echo "<$tag>", $this->x($message, $tokens), "</$tag>";
	}

	function options_page(  ) { 

		?>
		<form action='options.php' method='post'>
			
			<h2><?php _e(static::T, static::X) ?></h2>
			
			<?php

			echo '<!-- ', print_r(static::settings(), true), ' -->';

			settings_fields( static::N );
			do_settings_sections( static::N );
			submit_button();
			?>
			
		</form>
		<?php

	}

	#endregion ------------------ helpers ------------------

}//---	Wp_Options_Base


