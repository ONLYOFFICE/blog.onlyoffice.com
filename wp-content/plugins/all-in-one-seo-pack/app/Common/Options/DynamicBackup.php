<?php
namespace AIOSEO\Plugin\Common\Options;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the dynamic backup.
 *
 * @since 4.1.3
 */
class DynamicBackup {
	/**
	 * A the name of the option to save dynamic backups to.
	 *
	 * @since 4.1.3
	 *
	 * @var string
	 */
	protected $optionsName = 'aioseo_dynamic_settings_backup';

	/**
	 * The dynamic backup.
	 *
	 * @since 4.1.3
	 *
	 * @var array
	 */
	protected $backup = [];

	/**
	 * Whether the backup should be updated.
	 *
	 * @since 4.1.3
	 *
	 * @var boolean
	 */
	protected $shouldBackup = false;

	/**
	 * The options from the DB.
	 *
	 * @since 4.1.3
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.3
	 */
	public function __construct() {
		add_action( 'wp_loaded', [ $this, 'init' ], 5000 );
		add_action( 'shutdown', [ $this, 'updateBackup' ] );
	}

	/**
	 * Updates the backup after restoring options.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	public function updateBackup() {
		if ( $this->shouldBackup ) {
			$this->shouldBackup = false;
			$backup = aioseo()->dynamicOptions->convertOptionsToValues( $this->backup, 'value' );
			update_option( $this->optionsName, wp_json_encode( $backup ) );
		}
	}

	/**
	 * Checks whether data from the backup has to be restored.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	public function init() {
		$backup = json_decode( get_option( $this->optionsName ), true );
		if ( empty( $backup ) ) {
			update_option( $this->optionsName, '{}' );
			return;
		}

		$this->backup  = $backup;
		$this->options = aioseo()->dynamicOptions->getDefaults();

		$this->restorePostTypes();
		$this->restoreTaxonomies();
		$this->restoreArchives();
	}

	/**
	 * Restores the dynamic Post Types options.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	private function restorePostTypes() {
		$postTypes = aioseo()->helpers->getPublicPostTypes();
		foreach ( $postTypes as $postType ) {
			$name = $postType['name'];
			if ( 'type' === $name ) {
				$name = '_aioseo_type';
			}

			if ( ! empty( $this->backup['postTypes'][ $name ]['searchAppearance'] ) ) {
				$this->restoreOptions( $this->backup['postTypes'][ $name ]['searchAppearance'], [ 'searchAppearance', 'postTypes', $name ] );
				unset( $this->backup['postTypes'][ $name ]['searchAppearance'] );
				$this->shouldBackup = true;
			}

			if ( ! empty( $this->backup['postTypes'][ $name ]['social']['facebook'] ) ) {
				$this->restoreOptions( $this->backup['postTypes'][ $name ]['social']['facebook'], [ 'social', 'facebook', 'general', 'postTypes', $name ] );
				unset( $this->backup['postTypes'][ $name ]['social']['facebook'] );
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Restores the dynamic Taxonomies options.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	private function restoreTaxonomies() {
		$taxonomies = aioseo()->helpers->getPublicTaxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			$name = $taxonomy['name'];
			if ( 'type' === $name ) {
				$name = '_aioseo_type';
			}

			if ( ! empty( $this->backup['taxonomies'][ $name ]['searchAppearance'] ) ) {
				$this->restoreOptions( $this->backup['taxonomies'][ $name ]['searchAppearance'], [ 'searchAppearance', 'taxonomies', $name ] );
				unset( $this->backup['taxonomies'][ $name ]['searchAppearance'] );
				$this->shouldBackup = true;
			}

			if ( ! empty( $this->backup['taxonomies'][ $name ]['social']['facebook'] ) ) {
				$this->restoreOptions( $this->backup['taxonomies'][ $name ]['social']['facebook'], [ 'social', 'facebook', 'general', 'taxonomies', $name ] );
				unset( $this->backup['taxonomies'][ $name ]['social']['facebook'] );
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Restores the dynamic Archives options.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	private function restoreArchives() {
		$postTypes = aioseo()->helpers->getPublicPostTypes();
		foreach ( $postTypes as $postType ) {
			$name = $postType['name'];
			if ( 'type' === $name ) {
				$name = '_aioseo_type';
			}

			if ( ! empty( $this->backup['archives'][ $name ]['searchAppearance'] ) ) {
				$this->restoreOptions( $this->backup['archives'][ $name ]['searchAppearance'], [ 'searchAppearance', 'archives', $name ] );
				unset( $this->backup['archives'][ $name ]['searchAppearance'] );
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Restores the backuped options.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 * @param  array $backupOptions The options to be restored.
	 * @param  array $groups        The group that the option should be restored.
	 */
	protected function restoreOptions( $backupOptions, $groups ) {
		$groupPath = $this->options;
		foreach ( $groups as $group ) {
			if ( ! isset( $groupPath[ $group ] ) ) {
				return false;
			}
			$groupPath = $groupPath[ $group ];
		}

		$options = aioseo()->dynamicOptions->noConflict();
		foreach ( $backupOptions as $setting => $value ) {
			// Check if the option exists by checking if the type is defined.
			$type = ! empty( $groupPath[ $setting ]['type'] ) ? $groupPath[ $setting ]['type'] : '';
			if ( ! $type ) {
				continue;
			}

			foreach ( $groups as $group ) {
				$options = $options->$group;
			}

			$options->$setting = $value;
		}
	}

	/**
	 * Maybe backup the options if it has disappeared.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $newOptions An array of options to check.
	 * @return void
	 */
	public function maybeBackup( $newOptions ) {
		$this->maybeBackupPostType( $newOptions['searchAppearance']['postTypes'], $newOptions['social']['facebook']['general']['postTypes'] );
		$this->maybeBackupTaxonomy( $newOptions['searchAppearance']['taxonomies'] );
		$this->maybeBackupArchives( $newOptions['searchAppearance']['archives'] );
	}

	/**
	 * Maybe backup the Post Types.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $dynamicPostTypes  An array of dynamic post types from Search Appearance to check.
	 * @param  array $dynamicPostTypeOG An array of dynamic post types from Social Facebook to check.
	 * @return void
	 */
	private function maybeBackupPostType( $dynamicPostTypes, $dynamicPostTypesOG ) {
		$postTypes = aioseo()->helpers->getPublicPostTypes();
		$postTypes = $this->normalizeObjectName( $postTypes );

		foreach ( $dynamicPostTypes as $dynamicPostTypeName => $dynamicPostTypeSettings ) {
			$found = wp_list_filter( $postTypes, [ 'name' => $dynamicPostTypeName ] );
			if ( count( $found ) === 0 ) {
				$this->backup['postTypes'][ $dynamicPostTypeName ]['searchAppearance'] = $dynamicPostTypeSettings;
				$this->shouldBackup = true;
			}
		}

		foreach ( $dynamicPostTypesOG as $dynamicPostTypeNameOG => $dynamicPostTypeSettingsOG ) {
			$found = wp_list_filter( $postTypes, [ 'name' => $dynamicPostTypeNameOG ] );
			if ( count( $found ) === 0 ) {
				$this->backup['postTypes'][ $dynamicPostTypeNameOG ]['social']['facebook'] = $dynamicPostTypeSettingsOG;
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Maybe backup the Taxonomies.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $dynamicTaxonomies   An array of dynamic taxonomy from Search Appearance to check.
	 * @param  array $dynamicTaxonomiesOG An array of dynamic taxonomy from Social Facebook to check.
	 * @return void
	 */
	protected function maybeBackupTaxonomy( $dynamicTaxonomies, $dynamicTaxonomiesOG = [] ) {
		$taxonomies = aioseo()->helpers->getPublicTaxonomies();
		$taxonomies = $this->normalizeObjectName( $taxonomies );

		foreach ( $dynamicTaxonomies as $dynamicTaxonomyName => $dynamicTaxonomySettings ) {
			$found = wp_list_filter( $taxonomies, [ 'name' => $dynamicTaxonomyName ] );
			if ( count( $found ) === 0 ) {
				$this->backup['taxonomies'][ $dynamicTaxonomyName ]['searchAppearance'] = $dynamicTaxonomySettings;
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Maybe backup the Archives.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $dynamicArchives An array of dynamic archives to check.
	 * @return void
	 */
	private function maybeBackupArchives( $dynamicArchives ) {
		$postTypes = aioseo()->helpers->getPublicPostTypes( false, true );
		$postTypes = $this->normalizeObjectName( $postTypes );

		foreach ( $dynamicArchives as $archiveName => $archiveSettings ) {
			$found = wp_list_filter( $postTypes, [ 'name' => $archiveName ] );
			if ( count( $found ) === 0 ) {
				$this->backup['archives'][ $archiveName ]['searchAppearance'] = $archiveSettings;
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Normalize object name to work properly with AIOSEO.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $items The items.
	 * @return array        The normalized items.
	 */
	public function normalizeObjectName( $items ) {
		foreach ( $items as &$item ) {
			if ( 'type' === $item['name'] ) {
				$item['name'] = '_aioseo_type';
			}
		}

		return $items;
	}
}