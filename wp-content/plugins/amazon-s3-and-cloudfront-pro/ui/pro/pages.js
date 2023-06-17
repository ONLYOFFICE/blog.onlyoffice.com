import {get} from "svelte/store";
import {location} from "svelte-spa-router";
import {
	bapa,
	ooe,
	counts,
	current_settings,
	storage_provider,
	strings
} from "../js/stores";
import {pages} from "../js/routes";
import {licence} from "./stores";
import AssetsPage from "./AssetsPage.svelte";
import ToolsPage from "./ToolsPage.svelte";
import LicencePage from "./LicencePage.svelte";
import SupportPage from "./SupportPage.svelte";
import UpdateObjectACLsPromptSubPage
	from "./UpdateObjectACLsPromptSubPage.svelte";
import CopyBucketsPromptSubPage from "./CopyBucketsPromptSubPage.svelte";
import MoveObjectsPromptPage from "./MoveObjectsPromptPage.svelte";
import MovePublicObjectsPromptPage from "./MovePublicObjectsPromptPage.svelte";
import MovePrivateObjectsPromptPage
	from "./MovePrivateObjectsPromptPage.svelte";
import RemoveLocalFilesPromptPage from "./RemoveLocalFilesPromptPage.svelte";
import UploaderPromptPage from "./UploaderPromptPage.svelte";
import DownloaderPromptPage from "./DownloaderPromptPage.svelte";

export function addPages( enabledTools ) {
	pages.add(
		{
			position: 10,
			name: "assets",
			title: () => get( strings ).assets_tab_title,
			nav: true,
			route: "/assets",
			component: AssetsPage
		}
	);
	pages.add(
		{
			position: 20,
			name: "tools",
			title: () => get( strings ).tools_tab_title,
			nav: true,
			route: "/tools",
			component: ToolsPage
		}
	);
	pages.add(
		{
			position: 90,
			name: "licence",
			title: () => get( strings ).licence_tab_title,
			nav: true,
			route: "/license",
			component: LicencePage
		}
	);
	pages.add(
		{
			position: 100,
			name: "support",
			title: () => get( strings ).support_tab_title,
			nav: true,
			route: "/support",
			component: SupportPage
		}
	);

	// Update ACLs tool prompt.
	if ( enabledTools.hasOwnProperty( "update_acls" ) ) {
		const updateACLs = {
			position: 240,
			name: "update-acls",
			title: () => enabledTools.update_acls.name,
			subNav: true,
			route: "/storage/update-acls",
			component: UpdateObjectACLsPromptSubPage,
			enabled: () => {
				// Nothing to update?
				if (
					!get( counts ).hasOwnProperty( "offloaded" ) ||
					get( counts ).offloaded < 1 ||
					!get( current_settings ).hasOwnProperty( "bucket" ) ) {
					return false;
				}

				// Current Storage Provider never allows ACLs to be disabled.
				if ( get( storage_provider ).requires_acls ) {
					return false;
				}

				// If either Block All Public Access or Object Ownership turned on,
				// we should not update ACLs.
				if ( get( bapa ) || get( ooe ) ) {
					return false;
				}

				// Update ACLs if BAPA just turned off.
				if (
					get( storage_provider ).block_public_access_supported &&
					get( current_settings ).hasOwnProperty( "block-public-access" ) &&
					updateACLs.blockPublicAccess !== get( current_settings )[ "block-public-access" ]
				) {
					return true;
				}

				// Update ACLs if OOE just turned off.
				if (
					get( storage_provider ).object_ownership_supported &&
					get( current_settings ).hasOwnProperty( "object-ownership-enforced" ) &&
					updateACLs.objectOwnershipEnforced !== get( current_settings )[ "object-ownership-enforced" ]
				) {
					return true;
				}

				return false;
			},
			isNextRoute: ( data ) => {
				if (
					!get( licence ).hasOwnProperty( "is_valid" ) ||
					!get( licence ).is_valid ||
					!updateACLs.enabled()
				) {
					return false;
				}

				// If currently in /storage/security route, then update-acls is next.
				return get( location ) === "/storage/security";
			},
			blockPublicAccess: get( current_settings ).hasOwnProperty( "bucket" ) && get( current_settings ).hasOwnProperty( "block-public-access" ) ? get( current_settings )[ "block-public-access" ] : false,
			objectOwnershipEnforced: get( current_settings ).hasOwnProperty( "bucket" ) && get( current_settings ).hasOwnProperty( "object-ownership-enforced" ) ? get( current_settings )[ "object-ownership-enforced" ] : false,
			setInitialProperties: ( data ) => {
				if ( data.hasOwnProperty( "settings" ) && data.settings.hasOwnProperty( "bucket" ) ) {
					if ( data.settings.hasOwnProperty( "block-public-access" ) ) {
						updateACLs.blockPublicAccess = data.settings[ "block-public-access" ];
					} else {
						updateACLs.blockPublicAccess = false;
					}

					if ( data.settings.hasOwnProperty( "object-ownership-enforced" ) ) {
						updateACLs.objectOwnershipEnforced = data.settings[ "object-ownership-enforced" ];
					} else {
						updateACLs.objectOwnershipEnforced = false;
					}
				}

				return false;
			},
			events: {
				"next": ( data ) => updateACLs.isNextRoute( data ),
				"bucket-security": ( data ) => updateACLs.isNextRoute( data ),
				"settings.save": ( data ) => updateACLs.setInitialProperties( data ),
				"page.initial.settings": ( data ) => updateACLs.setInitialProperties( data )
			}
		};
		pages.add( updateACLs );
	}

	// Copy Files tool prompt.
	if ( enabledTools.hasOwnProperty( "copy_buckets" ) ) {
		const copyBuckets = {
			position: 250,
			name: "copy-buckets",
			title: () => enabledTools.copy_buckets.name,
			subNav: true,
			route: "/storage/copy-buckets",
			component: CopyBucketsPromptSubPage,
			enabled: () => {
				return get( counts ).offloaded > 0 && get( current_settings ).hasOwnProperty( "bucket" ) && copyBuckets.bucket !== get( current_settings ).bucket;
			},
			isNextRoute: ( data ) => {
				if (
					!get( licence ).hasOwnProperty( "is_valid" ) ||
					!get( licence ).is_valid ||
					!copyBuckets.enabled()
				) {
					return false;
				}

				// If currently in any of the below routes, then copy-buckets is next if not gazumped.
				return get( location ) === "/storage/bucket" || get( location ) === "/storage/security" || get( location ) === "/storage/update-acls";
			},
			bucket: get( current_settings ).hasOwnProperty( "bucket" ) ? get( current_settings ).bucket : "",
			setInitialBucket: ( data ) => {
				if ( data.hasOwnProperty( "settings" ) && data.settings.hasOwnProperty( "bucket" ) ) {
					copyBuckets.bucket = data.settings.bucket;
				}

				return false;
			},
			events: {
				"next": ( data ) => copyBuckets.isNextRoute( data ),
				"settings.save": ( data ) => copyBuckets.isNextRoute( data ),
				"bucket-security": ( data ) => copyBuckets.isNextRoute( data ),
				"page.initial.settings": ( data ) => copyBuckets.setInitialBucket( data )
			}
		};
		pages.add( copyBuckets );
	}

	// Move Public/Private Objects tool prompt.
	if (
		enabledTools.hasOwnProperty( "move_objects" ) &&
		enabledTools.hasOwnProperty( "move_public_objects" ) &&
		enabledTools.hasOwnProperty( "move_private_objects" )
	) {
		const moveObjects = {
			position: 400,
			name: "move-objects",
			title: () => enabledTools.move_objects.name,
			route: "/prompt/move-objects",
			component: MoveObjectsPromptPage,
			publicPathChanged: ( data ) => {
				if ( data.hasOwnProperty( "changed_settings" ) ) {
					// Year/Month disabled - never show prompt.
					if (
						data.changed_settings.includes( "use-yearmonth-folders" ) &&
						get( current_settings ).hasOwnProperty( "use-yearmonth-folders" ) &&
						!get( current_settings )[ "use-yearmonth-folders" ]
					) {
						return false;
					}

					// Object Versioning disabled - never show prompt.
					if (
						data.changed_settings.includes( "object-versioning" ) &&
						get( current_settings ).hasOwnProperty( "object-versioning" ) &&
						!get( current_settings )[ "object-versioning" ]
					) {
						return false;
					}

					// Path enabled/disabled.
					if (
						data.changed_settings.includes( "enable-object-prefix" ) &&
						get( current_settings ).hasOwnProperty( "enable-object-prefix" )
					) {
						return true;
					}

					// Path changed while enabled.
					if (
						data.changed_settings.includes( "object-prefix" ) &&
						get( current_settings ).hasOwnProperty( "enable-object-prefix" ) &&
						get( current_settings )[ "enable-object-prefix" ]
					) {
						return true;
					}

					// Year/Month enabled.
					if (
						data.changed_settings.includes( "use-yearmonth-folders" ) &&
						get( current_settings ).hasOwnProperty( "use-yearmonth-folders" ) &&
						get( current_settings )[ "use-yearmonth-folders" ]
					) {
						return true;
					}

					// Object Versioning enabled.
					if (
						data.changed_settings.includes( "object-versioning" ) &&
						get( current_settings ).hasOwnProperty( "object-versioning" ) &&
						get( current_settings )[ "object-versioning" ]
					) {
						return true;
					}
				}

				return false;
			},
			privatePathChanged: ( data ) => {
				if ( data.hasOwnProperty( "changed_settings" ) ) {
					// Signed URLs enabled/disabled.
					if (
						data.changed_settings.includes( "enable-signed-urls" ) &&
						get( current_settings ).hasOwnProperty( "enable-signed-urls" )
					) {
						return true;
					}

					// Signed URLs prefix changed while enabled.
					if (
						data.changed_settings.includes( "signed-urls-object-prefix" ) &&
						get( current_settings ).hasOwnProperty( "enable-signed-urls" ) &&
						get( current_settings )[ "enable-signed-urls" ]
					) {
						return true;
					}
				}

				return false;
			},
			isNextRoute: ( data ) => {
				// Anything to work with?
				if (
					!get( licence ).hasOwnProperty( "is_valid" ) ||
					!get( licence ).is_valid ||
					!get( current_settings ).hasOwnProperty( "bucket" ) ||
					!get( counts ).hasOwnProperty( "offloaded" ) ||
					get( counts ).offloaded < 1
				) {
					return false;
				}

				return moveObjects.publicPathChanged( data ) && moveObjects.privatePathChanged( data );
			},
			events: {
				"settings.save": ( data ) => moveObjects.isNextRoute( data )
			}
		};
		pages.add( moveObjects );

		const movePublicObjects = {
			position: 410,
			name: "move-public-objects",
			title: () => enabledTools.move_public_objects.name,
			route: "/prompt/move-public-objects",
			component: MovePublicObjectsPromptPage,
			isNextRoute: ( data ) => {
				// Anything to work with?
				if (
					!get( licence ).hasOwnProperty( "is_valid" ) ||
					!get( licence ).is_valid ||
					!get( current_settings ).hasOwnProperty( "bucket" ) ||
					!get( counts ).hasOwnProperty( "offloaded" ) ||
					get( counts ).offloaded < 1
				) {
					return false;
				}

				return moveObjects.publicPathChanged( data );
			},
			events: {
				"settings.save": ( data ) => movePublicObjects.isNextRoute( data )
			}
		};
		pages.add( movePublicObjects );

		const movePrivateObjects = {
			position: 420,
			name: "move-private-objects",
			title: () => enabledTools.move_private_objects.name,
			route: "/prompt/move-private-objects",
			component: MovePrivateObjectsPromptPage,
			isNextRoute: ( data ) => {
				// Anything to work with?
				if (
					!get( licence ).hasOwnProperty( "is_valid" ) ||
					!get( licence ).is_valid ||
					!get( current_settings ).hasOwnProperty( "bucket" ) ||
					!get( counts ).hasOwnProperty( "offloaded" ) ||
					get( counts ).offloaded < 1
				) {
					return false;
				}

				return moveObjects.privatePathChanged( data );
			},
			events: {
				"settings.save": ( data ) => movePrivateObjects.isNextRoute( data )
			}
		};
		pages.add( movePrivateObjects );
	}

	// Remove Local Files tool prompt.
	if ( enabledTools.hasOwnProperty( "remove_local_files" ) ) {
		const removeLocalFiles = {
			position: 430,
			name: "remove-local-files",
			title: () => enabledTools.remove_local_files.name,
			route: "/prompt/remove-local-files",
			component: RemoveLocalFilesPromptPage,
			onPreviousPage: () => {
				const previousPages = pages.withPrefix( "/prompt/" ).filter( ( page ) => page.position < removeLocalFiles.position );

				for ( const previousPage of previousPages ) {
					if ( get( location ) === previousPage.route ) {
						return true;
					}
				}

				return false;
			},
			removeLocalFile: get( current_settings ).hasOwnProperty( "remove-local-file" ) ? get( current_settings )[ "remove-local-file" ] : false,
			setInitialRemoveLocalFile: ( data ) => {
				if (
					get( location ) !== removeLocalFiles.route &&
					!removeLocalFiles.onPreviousPage() &&
					data.hasOwnProperty( "settings" ) &&
					data.settings.hasOwnProperty( "remove-local-file" )
				) {
					removeLocalFiles.removeLocalFile = data.settings[ "remove-local-file" ];
				}

				return false;
			},
			isNextRoute: ( data ) => {
				// Anything to work with?
				if (
					!get( licence ).hasOwnProperty( "is_valid" ) ||
					!get( licence ).is_valid ||
					!get( current_settings ).hasOwnProperty( "bucket" ) ||
					!get( counts ).hasOwnProperty( "offloaded" ) ||
					get( counts ).offloaded < 1
				) {
					return false;
				}

				if ( data.hasOwnProperty( "changed_settings" ) ) {
					// Remove Local Files turned on.
					if (
						data.changed_settings.includes( "remove-local-file" ) &&
						get( current_settings ).hasOwnProperty( "remove-local-file" ) &&
						removeLocalFiles.removeLocalFile !== get( current_settings )[ "remove-local-file" ] &&
						get( current_settings )[ "remove-local-file" ]
					) {
						return true;
					}
				}

				// Setting changed and event from previous prompt page.
				if (
					removeLocalFiles.onPreviousPage() &&
					get( current_settings ).hasOwnProperty( "remove-local-file" ) &&
					removeLocalFiles.removeLocalFile !== get( current_settings )[ "remove-local-file" ] &&
					get( current_settings )[ "remove-local-file" ]
				) {
					return true;
				}

				// We're not interested in showing prompt, just ensure local state is up to date.
				// NOTE: This handles syncing the local state when moving on from this prompt too.
				if ( get( current_settings ).hasOwnProperty( "remove-local-file" ) ) {
					removeLocalFiles.removeLocalFile = get( current_settings )[ "remove-local-file" ];
				}

				return false;
			},
			events: {
				"next": ( data ) => removeLocalFiles.isNextRoute( data ),
				"settings.save": ( data ) => removeLocalFiles.isNextRoute( data ),
				"page.initial.settings": ( data ) => removeLocalFiles.setInitialRemoveLocalFile( data )
			}
		};
		pages.add( removeLocalFiles );
	}

	// Uploader tool prompt.
	if ( enabledTools.hasOwnProperty( "uploader" ) ) {
		const uploader = {
			position: 440,
			name: "uploader",
			title: () => enabledTools.uploader.name,
			route: "/prompt/uploader",
			component: UploaderPromptPage,
			onPreviousPage: () => {
				const previousPages = pages.withPrefix( "/prompt/" ).filter( ( page ) => page.position < uploader.position );

				for ( const previousPage of previousPages ) {
					if ( get( location ) === previousPage.route ) {
						return true;
					}
				}

				return false;
			},
			copyToProvider: get( current_settings ).hasOwnProperty( "copy-to-s3" ) ? get( current_settings )[ "copy-to-s3" ] : false,
			setInitialCopyToProvider: ( data ) => {
				if (
					get( location ) !== uploader.route &&
					!uploader.onPreviousPage() &&
					data.hasOwnProperty( "settings" ) &&
					data.settings.hasOwnProperty( "copy-to-s3" )
				) {
					uploader.copyToProvider = data.settings[ "copy-to-s3" ];
				}

				return false;
			},
			isNextRoute: ( data ) => {
				// Anything to work with?
				if (
					!get( licence ).hasOwnProperty( "is_valid" ) ||
					!get( licence ).is_valid ||
					!get( current_settings ).hasOwnProperty( "bucket" ) ||
					!get( counts ).hasOwnProperty( "not_offloaded" ) ||
					get( counts ).not_offloaded < 1
				) {
					return false;
				}

				if ( data.hasOwnProperty( "changed_settings" ) ) {
					// Copy to Provider turned on.
					if (
						data.changed_settings.includes( "copy-to-s3" ) &&
						get( current_settings ).hasOwnProperty( "copy-to-s3" ) &&
						uploader.copyToProvider !== get( current_settings )[ "copy-to-s3" ] &&
						get( current_settings )[ "copy-to-s3" ]
					) {
						return true;
					}
				}

				// Setting changed and event from previous prompt page.
				if (
					uploader.onPreviousPage() &&
					get( current_settings ).hasOwnProperty( "copy-to-s3" ) &&
					uploader.copyToProvider !== get( current_settings )[ "copy-to-s3" ] &&
					get( current_settings )[ "copy-to-s3" ]
				) {
					return true;
				}

				// We're not interested in showing prompt, just ensure local state is up to date.
				// NOTE: This handles syncing the local state when moving on from this prompt too.
				if ( get( current_settings ).hasOwnProperty( "copy-to-s3" ) ) {
					uploader.copyToProvider = get( current_settings )[ "copy-to-s3" ];
				}

				return false;
			},
			events: {
				"next": ( data ) => uploader.isNextRoute( data ),
				"settings.save": ( data ) => uploader.isNextRoute( data ),
				"page.initial.settings": ( data ) => uploader.setInitialCopyToProvider( data )
			}
		};
		pages.add( uploader );
	}

	// Downloader tool prompt when Remove Local Files turned off.
	if ( enabledTools.hasOwnProperty( "downloader" ) ) {
		const downloader = {
			position: 450,
			name: "downloader",
			title: () => enabledTools.downloader.name,
			route: "/prompt/downloader",
			component: DownloaderPromptPage,
			onPreviousPage: () => {
				const previousPages = pages.withPrefix( "/prompt/" ).filter( ( page ) => page.position < downloader.position );

				for ( const previousPage of previousPages ) {
					if ( get( location ) === previousPage.route ) {
						return true;
					}
				}

				return false;
			},
			removeLocalFile: get( current_settings ).hasOwnProperty( "remove-local-file" ) ? get( current_settings )[ "remove-local-file" ] : false,
			setInitialRemoveLocalFile: ( data ) => {
				if (
					get( location ) !== downloader.route &&
					!downloader.onPreviousPage() &&
					data.hasOwnProperty( "settings" ) &&
					data.settings.hasOwnProperty( "remove-local-file" )
				) {
					downloader.removeLocalFile = data.settings[ "remove-local-file" ];
				}

				return false;
			},
			isNextRoute: ( data ) => {
				// Anything to work with?
				if (
					!get( licence ).hasOwnProperty( "is_valid" ) ||
					!get( licence ).is_valid ||
					!get( current_settings ).hasOwnProperty( "bucket" ) ||
					!get( counts ).hasOwnProperty( "offloaded" ) ||
					get( counts ).offloaded < 1
				) {
					return false;
				}

				if ( data.hasOwnProperty( "changed_settings" ) ) {
					// Remove Local Files turned off.
					if (
						data.changed_settings.includes( "remove-local-file" ) &&
						get( current_settings ).hasOwnProperty( "remove-local-file" ) &&
						downloader.removeLocalFile !== get( current_settings )[ "remove-local-file" ] &&
						!get( current_settings )[ "remove-local-file" ]
					) {
						return true;
					}
				}

				// Setting changed and event from previous prompt page.
				if (
					downloader.onPreviousPage() &&
					get( current_settings ).hasOwnProperty( "remove-local-file" ) &&
					downloader.removeLocalFile !== get( current_settings )[ "remove-local-file" ] &&
					!get( current_settings )[ "remove-local-file" ]
				) {
					return true;
				}

				// We're not interested in showing prompt, just ensure local state is up to date.
				// NOTE: This handles syncing the local state when moving on from this prompt too.
				if ( get( current_settings ).hasOwnProperty( "remove-local-file" ) ) {
					downloader.removeLocalFile = get( current_settings )[ "remove-local-file" ];
				}

				return false;
			},
			events: {
				"next": ( data ) => downloader.isNextRoute( data ),
				"settings.save": ( data ) => downloader.isNextRoute( data ),
				"page.initial.settings": ( data ) => downloader.setInitialRemoveLocalFile( data )
			}
		};
		pages.add( downloader );
	}
}
