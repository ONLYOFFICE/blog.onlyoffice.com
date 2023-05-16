export const toolSettingsNotifications = {
	/**
	 * Process local and server settings to return a new Map of inline notifications.
	 *
	 * @param {Map} notifications
	 * @param {Object} settings
	 * @param {Object} current_settings
	 * @param {Object} strings
	 * @param {Object} counts
	 * @param {Object} licence
	 *
	 * @return {Map<string, Map<string, Object>>} keyed by setting name, containing map of notification objects keyed by id.
	 */
	process: ( notifications, settings, current_settings, strings, counts, licence ) => {
		// use-yearmonth-folders
		let entries = notifications.has( "use-yearmonth-folders" ) ? notifications.get( "use-yearmonth-folders" ) : new Map();
		if (
			current_settings.hasOwnProperty( "use-yearmonth-folders" ) &&
			current_settings[ "use-yearmonth-folders" ] &&
			settings.hasOwnProperty( "use-yearmonth-folders" ) &&
			!settings[ "use-yearmonth-folders" ] &&
			counts.hasOwnProperty( "offloaded" ) &&
			counts.offloaded > 0 &&
			licence.hasOwnProperty( "is_valid" ) &&
			licence.is_valid
		) {
			if ( !entries.has( "no-move-objects-year-month-notice" ) ) {
				entries.set( "no-move-objects-year-month-notice", {
					inline: true,
					type: "warning",
					message: strings.no_move_objects_year_month_notice
				} );
			}
		} else if ( entries.has( "no-move-objects-year-month-notice" ) ) {
			entries.delete( "no-move-objects-year-month-notice" );
		}

		notifications.set( "use-yearmonth-folders", entries );

		// object-versioning
		entries = notifications.has( "object-versioning" ) ? notifications.get( "object-versioning" ) : new Map();
		if (
			current_settings.hasOwnProperty( "object-versioning" ) &&
			current_settings[ "object-versioning" ] &&
			settings.hasOwnProperty( "object-versioning" ) &&
			!settings[ "object-versioning" ] &&
			counts.hasOwnProperty( "offloaded" ) &&
			counts.offloaded > 0 &&
			licence.hasOwnProperty( "is_valid" ) &&
			licence.is_valid
		) {
			if ( !entries.has( "no-move-objects-object-versioning-notice" ) ) {
				entries.set( "no-move-objects-object-versioning-notice", {
					inline: true,
					type: "warning",
					message: strings.no_move_objects_object_versioning_notice
				} );
			}
		} else if ( entries.has( "no-move-objects-object-versioning-notice" ) ) {
			entries.delete( "no-move-objects-object-versioning-notice" );
		}

		notifications.set( "object-versioning", entries );

		return notifications;
	}
};