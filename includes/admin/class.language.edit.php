<?php
/**
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPGlobus_language_edit
 */
class WPGlobus_language_edit {

	/**
	 * All flag files
	 * @var array
	 */
	var $all_flags = array();

	/**
	 * Current action
	 * @var string
	 */
	var $action = '';

	/**
	 * Language code
	 * @var string
	 */
	var $language_code = '';

	/**
	 * Language name
	 * @var string
	 */
	var $language_name = '';

	/**
	 * Language name in English
	 * @var string
	 */
	var $en_language_name = '';

	/**
	 * Locale
	 * @var string
	 */
	var $locale = '';

	/**
	 * Flag of the current language
	 * @var string
	 */
	var $flag = '';

	/**
	 * Set up to true at submit form action
	 * @var bool
	 */
	var $submit = false;

	/**
	 * Messages for form submit
	 * @var array
	 */
	var $submit_messages = array();

	/**
	 * Constructor
	 */
	function __construct() {

		if ( isset( $_GET['action'] ) && 'delete' == $_GET['action'] ) {
			$this->action = 'delete';
		}
		elseif ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] ) {
			$this->action = 'edit';
		}
		else {
			$this->action = 'add';
		}

		if ( ! empty( $_GET['lang'] ) ) {
			$this->language_code = $_GET['lang'];
		}

		if ( isset( $_POST['submit'] ) ) {
			$this->submit = true;
			$this->process_submit();
		}
		elseif ( isset( $_POST['delete'] ) ) {
			$this->process_delete();
		}
		else {
			$this->get_data();
		}

		$this->display_table();

	}

	/**
	 * Process delete language action
	 * @return void
	 */
	function process_delete() {

		global $WPGlobus_Config;

		$opts = get_option( $WPGlobus_Config->option );

		if ( isset( $opts['enabled_languages'][$this->language_code] ) ) {

			unset( $opts['enabled_languages'][$this->language_code] );

			/** FIX: reset $opts['more_languages'] */
			if ( array_key_exists( 'more_languages', $opts ) ) {
				$opts['more_languages'] = '';
			}
			update_option( $WPGlobus_Config->option, $opts );

		}

		unset( $WPGlobus_Config->language_name[$this->language_code] );
		update_option( $WPGlobus_Config->option_language_names, $WPGlobus_Config->language_name );

		unset( $WPGlobus_Config->flag[$this->language_code] );
		update_option( $WPGlobus_Config->option_flags, $WPGlobus_Config->flag );

		unset( $WPGlobus_Config->en_language_name[$this->language_code] );
		update_option( $WPGlobus_Config->option_en_language_names, $WPGlobus_Config->en_language_name );

		unset( $WPGlobus_Config->locale[$this->language_code] );
		update_option( $WPGlobus_Config->option_locale, $WPGlobus_Config->locale );

		/** @todo make "$location" available from WPGlobus_Config */
		$location = admin_url( '/admin.php?page=' . WPGlobus::OPTIONS_PAGE_SLUG );
		wp_redirect( $location );
		exit;

	}

	/**
	 * Process submit action
	 * @return void
	 */
	function process_submit() {

		$code = isset( $_POST['language_code'] ) ? $_POST['language_code'] : '';
		if ( $this->language_code == $code ) {
			if ( $this->check_fields( $code, false ) ) {
				$this->save();
				$this->submit_messages['success'][] = __( 'Options updated', 'wpglobus' );
			}
		}
		else {
			if ( $this->check_fields( $code ) ) {
				$this->save( true );
				$this->submit_messages['success'][] = __( 'Options updated', 'wpglobus' );
			}
		}
		$this->_get_flags();

	}

	/**
	 * Save data language to DB
	 * @param bool $update_code If need to change language code
	 * @return void
	 */
	function save( $update_code = false ) {

		global $WPGlobus_Config;
		$old_code = '';
		if ( $update_code && 'edit' == $this->action ) {
			$old_code = isset( $_GET['lang'] ) ? $_GET['lang'] : $old_code;
			if ( isset( $WPGlobus_Config->language_name[$old_code] ) ) {
				unset( $WPGlobus_Config->language_name[$old_code] );
			}

			$opts = get_option( $WPGlobus_Config->option );
			if ( isset( $opts['enabled_languages'][$old_code] ) ) {
				unset( $opts['enabled_languages'][$old_code] );
				update_option( $WPGlobus_Config->option, $opts );
			}
			if ( isset( $opts['more_languages'] ) && $old_code == $opts['more_languages'] ) {
				unset( $opts['more_languages'] );
				update_option( $WPGlobus_Config->option, $opts );
			}
		}
		$WPGlobus_Config->language_name[$this->language_code] = $this->language_name;
		update_option( $WPGlobus_Config->option_language_names, $WPGlobus_Config->language_name );

		if ( $update_code && isset( $WPGlobus_Config->flag[$old_code] ) ) {
			unset( $WPGlobus_Config->flag[$old_code] );
		}
		$WPGlobus_Config->flag[$this->language_code] = $this->flag;
		update_option( $WPGlobus_Config->option_flags, $WPGlobus_Config->flag );

		if ( $update_code && isset( $WPGlobus_Config->en_language_name[$old_code] ) ) {
			unset( $WPGlobus_Config->en_language_name[$old_code] );
		}
		$WPGlobus_Config->en_language_name[$this->language_code] = $this->en_language_name;
		update_option( $WPGlobus_Config->option_en_language_names, $WPGlobus_Config->en_language_name );

		if ( $update_code && isset( $WPGlobus_Config->locale[$old_code] ) ) {
			unset( $WPGlobus_Config->locale[$old_code] );
		}
		$WPGlobus_Config->locale[$this->language_code] = $this->locale;
		update_option( $WPGlobus_Config->option_locale, $WPGlobus_Config->locale );

		if ( $update_code ) {
			/** @todo make "$location" available from WPGlobus_Config */
			$location = admin_url( '/admin.php?page=' . WPGlobus::OPTIONS_PAGE_SLUG );
			wp_redirect( $location );
			exit;
		}
	}

	/**
	 * Check form fields
	 * @param string $lang_code
	 * @param bool   $check_code Use for existence check language code
	 * @return bool True if no errors, false otherwise.
	 */
	function check_fields( $lang_code, $check_code = true ) {
		$this->submit_messages['errors'] = array();
		if ( $check_code && empty( $lang_code ) ) {
			$this->submit_messages['errors'][] = __( 'Please enter a language code!', 'wpglobus' );
		}

		if ( $check_code && $this->language_exists( $lang_code ) ) {
			$this->submit_messages['errors'][] = __( 'Language code already exists!', 'wpglobus' );
		}

		if ( empty( $_POST['flags'] ) ) {
			$this->submit_messages['errors'][] = __( 'Please specify the language flag!', 'wpglobus' );
		}

		if ( empty( $_POST['language_name'] ) ) {
			$this->submit_messages['errors'][] = __( 'Please enter the language name!', 'wpglobus' );
		}

		if ( empty( $_POST['en_language_name'] ) ) {
			$this->submit_messages['errors'][] = __( 'Please enter the language name in English!', 'wpglobus' );
		}

		if ( empty( $_POST['locale'] ) ) {
			$this->submit_messages['errors'][] = __( 'Please enter the locale!', 'wpglobus' );
		}

		$this->language_code    = $lang_code;
		$this->flag             = isset( $_POST['flags'] ) ? $_POST['flags'] : '';
		$this->language_name    = isset( $_POST['language_name'] ) ? $_POST['language_name'] : '';
		$this->en_language_name = isset( $_POST['en_language_name'] ) ? $_POST['en_language_name'] : '';
		$this->locale           = isset( $_POST['locale'] ) ? $_POST['locale'] : '';

		if ( empty( $this->submit_messages['errors'] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Check existing language code in global $WPGlobus_Config
	 * @param string $code
	 * @return bool true if language code exists
	 */
	function language_exists( $code ) {
		global $WPGlobus_Config;
		if ( array_key_exists( $code, $WPGlobus_Config->language_name ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Get data for form fields
	 * @return void
	 */
	function get_data() {

		if ( 'edit' == $this->action || 'delete' == $this->action ) {
			global $WPGlobus_Config;
			$this->language_name    = $WPGlobus_Config->language_name[$this->language_code];
			$this->en_language_name = $WPGlobus_Config->en_language_name[$this->language_code];
			$this->locale           = $WPGlobus_Config->locale[$this->language_code];
			$this->flag             = $WPGlobus_Config->flag[$this->language_code];
		}
		$this->_get_flags();
	}

	/**
	 * Display language form
	 * @return void
	 */
	function display_table() {
		$disabled = '';
		if ( 'edit' == $this->action ) {
			$header = __( 'Edit Language', 'wpglobus' );
		}
		elseif ( 'delete' == $this->action ) {
			$header   = __( 'Are you sure you want to delete?', 'wpglobus' );
			$disabled = 'disabled';
		}
		else {
			$header = __( 'Add Language', 'wpglobus' );
		}
		?>
		<div class="wrap">
			<h2><?php echo $header; ?></h2>
			<?php if ( $this->submit ) {
				if ( ! empty( $this->submit_messages['errors'] ) ) {
					$mess = '';
					foreach ( $this->submit_messages['errors'] as $message ) {
						$mess .= $message . '<br />';
					}    ?>
					<div class="error"><?php echo $mess; ?></div> <?php
				}
				elseif ( ! empty( $this->submit_messages['success'] ) ) {
					$mess = '';
					foreach ( $this->submit_messages['success'] as $message ) {
						$mess .= $message . '<br />';
					}    ?>
					<div class="updated"><?php echo $mess; ?></div> <?php
				}
			}                ?>
			<form method="post" action="">
				<table class="form-table">
					<tr>
						<th scope="row"><label for="language_code"><?php _e( 'Language Code', 'wpglobus' ); ?></label></th>
						<td>
							<input name="language_code" <?php echo $disabled; ?> type="text" id="language_code"
								   value="<?php echo $this->language_code; ?>" class="regular-text"/>

							<p class="description"><?php _e( '2-Letter ISO Language Code for the Language you want to insert. (Example: en)', 'wpglobus' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="flags"><?php _e( 'Language flag', 'wpglobus' ); ?></label></th>
						<td>
							<select id="flags" name="flags" style="width:300px;" class="populate">    <?php
								foreach ( $this->all_flags as $file_name ) :
									if ( $this->flag == $file_name ) {
										$selected = 'selected';
									}
									else {
										$selected = '';
									}
									?>
									<option <?php echo $selected; ?>
										value="<?php echo $file_name; ?>"><?php echo $file_name; ?></option>    <?php
								endforeach;    ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="language_name"><?php _e( 'Name', 'wpglobus' ); ?></label></th>
						<td><input name="language_name" type="text" id="language_name"
								   value="<?php echo $this->language_name; ?>" class="regular-text"/>

							<p class="description"><?php _e( 'The name of the language in its native alphabet.
								(Examples: English, Русский)', 'wpglobus' ); ?></p></td>
					</tr>
					<tr>
						<th scope="row"><label for="en_language_name"><?php _e( 'Name in English', 'wpglobus' ); ?></label></th>
						<td><input name="en_language_name" type="text" id="en_language_name"
								   value="<?php echo $this->en_language_name; ?>" class="regular-text"/>

							<p class="description"><?php _e( 'The name of the language in English', 'wpglobus' ); ?></p></td>
					</tr>
					<tr>
						<th scope="row"><label for="locale"><?php _e( 'Locale', 'wpglobus' ); ?></label></th>
						<td><input name="locale" type="text" id="locale" value="<?php echo $this->locale; ?>"
								   class="regular-text"/>

							<p class="description"><?php _e( 'PHP/WordPress Locale of the language. (Examples: en_US, ru_RU)', 'wpglobus' ); ?></p></td>
					</tr>
				</table>    <?php

				if ( 'edit' == $this->action || 'add' == $this->action ) {
					?>
					<p class="submit"><input class="button button-primary" type="submit" name="submit"
											 value="<?php esc_attr_e( 'Save Changes', 'wpglobus' ); ?>"></p>    <?php
				}
				elseif ( 'delete' == $this->action ) {
					?>
					<p class="submit"><input class="button button-primary" type="submit" name="delete"
											 value="<?php esc_attr_e( 'Delete Language', 'wpglobus' ); ?>"></p>    <?php
				}    ?>

			</form>
		</div>
	<?php
	}

	/**
	 * Get flag files from directory
	 * @return void
	 */
	function _get_flags() {

		$path = WP_PLUGIN_DIR . '/' . WPGlobus_Config::GLOBUS_PLUGIN_NAME . '/flags/';

		$dir = new DirectoryIterator( $path );

		foreach ( $dir as $file ) {
			/** @var DirectoryIterator $file */
			if ( $file->isFile() ) {
				$this->all_flags[] = $file->getFilename();
			}
		}

	}

}

# --- EOF