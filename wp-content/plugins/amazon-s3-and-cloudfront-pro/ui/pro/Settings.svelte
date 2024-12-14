<script>
	import {onMount} from "svelte";
	import {
		strings,
		config,
		defaultStorageProvider,
		settingsLocked,
		notifications,
		current_settings,
		needs_access_keys,
		needs_refresh,
		counts,
		settings_notifications,
		settings,
		settings_changed,
		preStateUpdateCallbacks,
		postStateUpdateCallbacks
	} from "../js/stores";
	import {
		licence,
		running,
		tools,
		toolsLocked,
		assetsNeedsRefresh,
		assetsSettingsLocked,
		assetsSettings,
		assetsSettingsChanged
	} from "./stores";
	import {pages} from "../js/routes";
	import {defaultPages} from "../js/defaultPages";
	import {addPages} from "./pages";
	import {settingsNotifications} from "../js/settingsNotifications";
	import {toolSettingsNotifications} from "./toolSettingsNotifications";
	import Settings from "../components/Settings.svelte";
	import Header from "./Header.svelte";
	import Nav from "./Nav.svelte";
	import Pages from "../components/Pages.svelte";

	export let init = {};

	// During initialization set config store to passed in values to avoid undefined values in components during mount.
	// This saves having to do a lot of checking of values before use.
	config.set( init );
	pages.set( defaultPages );

	// We need a disassociated copy of the initial tools info to start with.
	tools.updateTools( { tools: { ...$config.tools } } );

	// We need a disassociated copy of the initial assets settings to work with.
	assetsSettings.set( { ...$config.assets_settings } );

	// Add Pro specific pages.
	addPages( $tools );

	/**
	 * Handles state update event's changes to config.
	 *
	 * @param {Object} config
	 *
	 * @return {Promise<void>}
	 */
	async function handleStateUpdate( config ) {
		let _settingsLocked = false;
		let _toolsLocked = false;
		let _assetsSettingsLocked = false;

		// All settings need to be locked?
		if ( config.upgrades.is_upgrading ) {
			_settingsLocked = true;
			_toolsLocked = true;
			_assetsSettingsLocked = true;

			const notification = {
				id: "as3cf-all-settings-locked",
				type: "warning",
				dismissible: false,
				heading: config.upgrades.locked_notifications[ config.upgrades.running_upgrade ],
				icon: "notification-locked.svg",
				plainHeading: true
			};
			notifications.add( notification );

			if ( $settings_changed ) {
				settings.reset();
			}

			if ( $assetsSettingsChanged ) {
				assetsSettings.reset();
			}
		} else {
			notifications.delete( "as3cf-all-settings-locked" );
		}

		// Media settings need to be locked?
		if ( $needs_refresh ) {
			_settingsLocked = true;
			_toolsLocked = true;

			const notification = {
				id: "as3cf-media-settings-locked",
				type: "warning",
				dismissible: false,
				only_show_on_tab: "media",
				heading: $strings.needs_refresh,
				icon: "notification-locked.svg",
				plainHeading: true
			};
			notifications.add( notification );
		} else if ( $running ) {
			_settingsLocked = true;

			const tool = $tools[ $running ];
			const notification = {
				id: "as3cf-media-settings-locked",
				type: "warning",
				dismissible: false,
				only_show_on_tab: "media",
				heading: tool.locked_notification,
				icon: "notification-locked.svg",
				plainHeading: true
			};
			notifications.add( notification );

			if ( $settings_changed ) {
				settings.reset();
			}
		} else {
			notifications.delete( "as3cf-media-settings-locked" );
		}

		// Assets settings need to be locked?
		if ( $assetsNeedsRefresh ) {
			_assetsSettingsLocked = true;

			const notification = {
				id: "as3cf-assets-settings-locked",
				type: "warning",
				dismissible: false,
				only_show_on_tab: "assets",
				heading: $strings.needs_refresh,
				icon: "notification-locked.svg",
				plainHeading: true
			};
			notifications.add( notification );
		} else {
			notifications.delete( "as3cf-assets-settings-locked" );
		}

		$settingsLocked = _settingsLocked;
		$toolsLocked = _toolsLocked;
		$assetsSettingsLocked = _assetsSettingsLocked;

		// Show a persistent error notice if bucket can't be accessed.
		if ( $needs_access_keys && ($settings.provider !== $defaultStorageProvider || $settings.bucket.length !== 0) ) {
			const notification = {
				id: "as3cf-needs-access-keys",
				type: "error",
				dismissible: false,
				only_show_on_tab: "media",
				hide_on_parent: true,
				heading: $strings.needs_access_keys,
				plainHeading: true
			};
			notifications.add( notification );
		} else {
			notifications.delete( "as3cf-needs-access-keys" );
		}
	}

	// Catch changes to running tool as soon as possible.
	$: if ( $running ) {
		handleStateUpdate( $config );
	}

	// Catch changes to needing access credentials as soon as possible.
	$: if ( $needs_access_keys ) {
		handleStateUpdate( $config );
	}

	onMount( () => {
		// Make sure state dependent data is up-to-date.
		handleStateUpdate( $config );

		// When state info is fetched we need some extra processing of the data.
		preStateUpdateCallbacks.update( _callables => {
			return [..._callables, assetsSettings.updateSettings];
		} );

		postStateUpdateCallbacks.update( _callables => {
			return [..._callables, tools.updateTools, handleStateUpdate];
		} );
	} );

	// Make sure all inline notifications are in place.
	$: settings_notifications.update( ( notices ) => settingsNotifications.process( notices, $settings, $current_settings, $strings ) );
	$: settings_notifications.update( ( notices ) => toolSettingsNotifications.process( notices, $settings, $current_settings, $strings, $counts, $licence ) );
</script>

<Settings header={Header}>
	<Pages nav={Nav}/>
</Settings>
