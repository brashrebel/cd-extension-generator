<?php

/**
 * Class {plugin_class}_Menus
 *
 * This is an optional class for adding widgets to the Client Dash Settings -> Widgets
 * tab to be made available for use on the dashboard.
 */
class {plugin_class}_Menus extends ClientDash_Menus_API {

	/**
	 * All of the items to output in the group drop-down.
	 *
	 * This property will populate the "Group Drop-Down" (under "Available Items") area on the left side
	 * of the screen under Settings -> Menus.
	 *
	 * Currently, this boilerplate only adds one group drop-down area and uses this property to
	 * populate it.
	 *
	 * Pro Tip: If you only put one tab in the array, the tab selection (displayed over the available
	 * items) will not show.
	 */
	private static $group_items = array(
		'Tab 1' => array(
			'Item 1' => array(
				'url'  => '/options-general.php?page=cd_settings&tab={settings_tab}',
				'icon' => 'dashicons-smiley',
			),
			// You can add a separator like this!
			'Test' => 'separator',
		),
		'Tab 2' => array(
			'Item 1' => array(
				'url'  => '/options-general.php?page=cd_settings&tab={settings_tab}',
				'icon' => 'dashicons-smiley',
			),
		),
	);

	/**
	 * The main construct function.
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'add_new_menu_groups' ) );
	}

	/**
	 * This is where you add new menu groups.
	 *
	 * In the example provided, we are adding one new menu group with the title "{plugin_name} Group."
	 * This group will contain the menu items that are output inside of the "group_content" method
	 * inside of this class.
	 */
	public function add_new_menu_groups() {

		self::add_menu_group( '{plugin_name} Group', array( __CLASS__, 'group_content' ) );
	}

	/**
	 * This is the callback function for the example group we've created.
	 *
	 * Inside of this you can add any HTML that you want, but it is recommended that you stick with just
	 * using the supplied "group_output()" function. This function takes an array of tabs and items (currently
	 * using the $group_items property from above) and outputs the menu items accordingly.
	 */
	public static function group_content() {

		self::group_output( '{plugin_name} Group', self::$group_items );
	}
}

// Instantiates the class. Do NOT remove this line or nothing will work.
new {plugin_class}_Menus();