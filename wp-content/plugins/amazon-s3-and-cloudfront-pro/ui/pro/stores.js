import {derived, get, writable} from "svelte/store";
import {api, config, state} from "../js/stores";
import {objectsDiffer} from "../js/objectsDiffer";

// Convenience readable store of licence, derived from config.
// We currently have one licence applied to a plugin install.
export const licence = derived( config, $config => $config.hasOwnProperty( "licences" ) ? $config.licences.at( 0 ) : [] );

// Convenience readable store of offload remaining with count message, derived from config.
export const offloadRemainingWithCount = derived( config, $config => $config.offload_remaining_with_count );

// Convenience readable store of documentation, derived from config.
export const documentation = derived( config, $config => $config.documentation );

/*
 * Tools.
 */

// Whether tools are locked due to background activity such as upgrade.
export const toolsLocked = writable( false );

// Keeps track of the currently running background tool.
export const running = writable( "" );

const toolIcons = {
	"add-metadata": "offload",
	"reverse-add-metadata": "analyzerepair",
	"verify-add-metadata": "analyzerepair",
	"copy-buckets": "move",
	"download-and-remover": "remove",
	"downloader": "download",
	"elementor-analyze-and-repair": "analyzerepair",
	"move-objects": "move",
	"move-private-objects": "move",
	"move-public-objects": "move",
	"remove-local-files": "clean",
	"update-acls": "analyzerepair",
	"uploader": "offload",
	"woocommerce-product-urls": "analyzerepair",
};

/**
 * Creates store of tools info and API access methods.
 *
 * @return {Object}
 */
function createTools() {
	const { subscribe, set, update } = writable( {} );

	return {
		subscribe,
		set,
		async action( tool, action ) {
			state.pausePeriodicFetch();

			// Set the status text to the default busy description
			// until the API returns a calculated status description.
			tool.status_description = tool.busy_description;
			tool.short_status_description = tool.busy_description;

			// Ensure all subscribers know the tool status is changing.
			update( _tools => {
				_tools[ tool.id ] = tool;

				return _tools;
			} );

			let result = {};
			const json = await api.put( "tools", {
				id: tool.id,
				action: action
			} );

			if ( json.hasOwnProperty( "ok" ) ) {
				result = json;
			}

			await state.resumePeriodicFetch();
			return result;
		},
		async start( tool ) {
			// Ensure all subscribers know that a tool is running.
			running.update( _running => tool.id );
			tool.is_queued = true;

			return await this.action( tool, "start" );
		},
		async cancel( tool ) {
			tool.is_cancelled = true;

			return await this.action( tool, "cancel" );
		},
		async pauseResume( tool ) {
			tool.is_paused = !tool.is_paused;

			return await this.action( tool, "pause_resume" );
		},
		updateTools( json ) {
			if ( json.hasOwnProperty( "tools" ) ) {
				// Update our understanding of what the server's tools status is.
				update( _tools => {
					return { ...json.tools };
				} );

				// Update our understanding of the currently running tool.
				const runningTool = Object.values( json.tools ).find( ( tool ) => tool.is_processing || tool.is_queued || tool.is_paused || tool.is_cancelled );

				if ( runningTool ) {
					running.update( _running => runningTool.id );
				} else {
					running.update( _running => "" );
				}
			}
		},
		icon( tool, isRunning, animated ) {
			let icon = "tool-generic";
			let type = "default";

			if ( isRunning ) {
				if ( tool.is_paused ) {
					type = "paused";
				} else if ( animated ) {
					type = "running-animated";
				} else {
					type = "active";
				}
			}

			if ( tool && tool.hasOwnProperty( "slug" ) && toolIcons.hasOwnProperty( tool.slug ) ) {
				icon = "tool-" + toolIcons[ tool.slug ];
			}

			if ( ["active", "default", "paused", "running-animated"].includes( type ) ) {
				icon = icon + "-" + type + ".svg";
			} else {
				icon = icon + "-default.svg";
			}

			return icon;
		}
	};
}

export const tools = createTools();

/*
 * Assets.
 */

// Does the app need a page refresh to resolve conflicts?
export const assetsNeedsRefresh = writable( false );

// Whether assets settings are locked due to background activity such as upgrade.
export const assetsSettingsLocked = writable( false );

// Convenience readable store of server's assets settings, derived from config.
export const currentAssetsSettings = derived( config, $config => $config.assets_settings );

// Convenience readable store of defined assets settings keys, derived from config.
export const assetsDefinedSettings = derived( config, $config => $config.assets_defined_settings );

// Convenience readable store of assets domain check info, derived from config.
export const assetsDomainCheck = derived( config, $config => $config.assets_domain_check );

// Convenience readable store indicating whether Assets functionality may be used.
export const enableAssets = derived( [licence, config], ( [$licence, $config] ) => {
	if (
		$licence.hasOwnProperty( "is_set" ) &&
		$licence.is_set &&
		$licence.hasOwnProperty( "is_valid" ) &&
		$licence.is_valid &&
		$config.hasOwnProperty( "assets_settings" )
	) {
		return true;
	}

	return false;
} );

/**
 * Creates store of assets settings.
 *
 * @return {Object}
 */
function createAssetsSettings() {
	const { subscribe, set, update } = writable( [] );

	return {
		subscribe,
		set,
		async save() {
			const json = await api.put( "assets-settings", get( this ) );

			if ( json.hasOwnProperty( "saved" ) && true === json.saved ) {
				// Sync settings with what the server has.
				this.updateSettings( json );

				return json;
			}

			return {};
		},
		reset() {
			set( { ...get( currentAssetsSettings ) } );
		},
		async fetch() {
			const json = await api.get( "assets-settings", {} );
			this.updateSettings( json );
		},
		updateSettings( json ) {
			if (
				json.hasOwnProperty( "assets_defined_settings" ) &&
				json.hasOwnProperty( "assets_settings" )
			) {
				const dirty = get( assetsSettingsChanged );
				const previousSettings = { ...get( currentAssetsSettings ) }; // cloned

				// Update our understanding of what the server's settings are.
				config.update( _config => {
					return {
						..._config,
						assets_defined_settings: json.assets_defined_settings,
						assets_settings: json.assets_settings,
					};
				} );

				// No need to check for changes from state if we've just saved these settings.
				if ( json.hasOwnProperty( "saved" ) && true === json.saved ) {
					return;
				}

				// If the settings weren't changed before, they shouldn't be now.
				if ( !dirty && get( assetsSettingsChanged ) ) {
					assetsSettings.reset();
				}

				// If settings are in middle of being changed when changes come in
				// from server, reset to server version.
				if ( dirty && objectsDiffer( [previousSettings, get( currentAssetsSettings )] ) ) {
					assetsNeedsRefresh.update( _needsRefresh => true );
					assetsSettings.reset();
				}
			}
		}
	};
}

export const assetsSettings = createAssetsSettings();

// Have the assets settings been changed from current server side settings?
export const assetsSettingsChanged = derived( [assetsSettings, currentAssetsSettings], objectsDiffer );
