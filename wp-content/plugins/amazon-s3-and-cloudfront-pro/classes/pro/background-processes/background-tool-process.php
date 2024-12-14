<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Background_Processes;

use AS3CF_Background_Process;
use AS3CF_Error;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Pro\Tool;

abstract class Background_Tool_Process extends AS3CF_Background_Process {

	/**
	 * @var Tool
	 */
	protected $tool;

	/**
	 * Default batch limit.
	 *
	 * @var int
	 */
	protected $limit = 100;

	/**
	 * Default chunk size.
	 *
	 * @var int
	 */
	protected $chunk = 10;

	/**
	 * @var array
	 */
	protected $errors = array();

	/**
	 * @var int
	 */
	protected $reported_errors_limit = 100;

	/**
	 * Initiate new background tool process.
	 *
	 * @param object $as3cf Instance of calling class
	 * @param Tool   $tool
	 */
	public function __construct( $as3cf, $tool ) {
		parent::__construct( $as3cf );

		$this->tool = $tool;
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		if ( ! $item['blogs_processed'] ) {
			// Calculate how many items each blog has,
			// and return immediately to allow monitoring
			// processes see initial state.
			$item = $this->calculate_blog_items( $item );

			if ( $this->all_blog_items_processed( $item ) ) {
				// Nothing to do, remove from queue.
				return false;
			} else {
				return $item;
			}
		}

		if ( $this->all_blog_items_processed( $item ) ) {
			// Batch complete, remove from queue
			return false;
		}

		return $this->process_blogs( $item );
	}

	/**
	 * Calculate the number of items across all blogs.
	 *
	 * @param array $item
	 *
	 * @return array
	 */
	protected function calculate_blog_items( $item ) {
		foreach ( $item['blogs'] as $blog_id => $blog ) {
			if ( $this->time_exceeded() || $this->memory_exceeded() ) {
				// Batch limits reached
				return $item;
			}

			if ( ! is_null( $blog['total_items'] ) ) {
				// Blog already processed, move on
				continue;
			}

			$this->as3cf->switch_to_blog( $blog_id );

			foreach ( $blog['processed'] as $source_type => $processed ) {
				if ( isset( $blog['last_source_id'][ $source_type ] ) && is_numeric( $blog['last_source_id'][ $source_type ] ) ) {
					$last_source_id = $blog['last_source_id'][ $source_type ];
				} else {
					$last_source_id = null;
				}

				$total = $this->get_blog_items( $source_type, $last_source_id, null, true );

				if ( ! empty( $total ) ) {
					$item['blogs'][ $blog_id ]['total_items']                    += $total;
					$item['blogs'][ $blog_id ]['last_source_id'][ $source_type ] = $this->get_blog_last_source_id( $source_type ) + 1;
					$item['total_items']                                         += $total;
				} else {
					$item['blogs'][ $blog_id ]['processed'][ $source_type ] = true;
					$item['blogs'][ $blog_id ]['total_items']               = 0;
				}
			}

			$this->as3cf->restore_current_blog();
		}

		$item['blogs_processed'] = true;

		return $item;
	}

	/**
	 * Loop over each blog and process items.
	 *
	 * @param array $item
	 *
	 * @return array
	 */
	protected function process_blogs( $item ) {
		$this->errors = $this->tool->get_errors();

		foreach ( $item['blogs'] as $blog_id => $blog ) {
			if ( $this->time_exceeded() || $this->memory_exceeded() ) {
				// Batch limits reached
				break;
			}

			if ( $this->all_source_types_processed( $blog ) ) {
				// Blog processed, move onto the next
				continue;
			}

			$this->as3cf->switch_to_blog( $blog_id );
			$limit = apply_filters( "as3cf_tool_{$this->action}_batch_size", $this->limit );

			foreach ( $blog['last_source_id'] as $source_type => $last_source_id ) {
				$items = $this->get_blog_items( $source_type, $last_source_id, $limit );
				$item  = $this->process_blog_items( $item, $blog_id, $source_type, $items );
			}

			// If we've just finished processing a subsite, force update its totals.
			if ( is_multisite() && $this->all_source_types_processed( $item['blogs'][ $blog_id ] ) ) {
				$this->as3cf->media_counts( true, true, $blog_id );
			}

			$this->as3cf->restore_current_blog();
		}

		if ( count( $this->errors ) ) {
			$this->tool->update_errors( $this->errors );
			$this->tool->update_error_notice( $this->errors );
			$this->tool->undismiss_error_notice();
		}

		return $item;
	}

	/**
	 * Process blog items.
	 *
	 * @param array  $item
	 * @param int    $blog_id
	 * @param string $source_type
	 * @param array  $items
	 *
	 * @return array
	 */
	protected function process_blog_items( $item, $blog_id, $source_type, $items ) {
		$chunks = array_chunk( $items, $this->chunk );

		foreach ( $chunks as $chunk ) {
			$processed = $this->process_items_chunk( $source_type, $chunk, $blog_id );

			if ( ! empty( $processed ) ) {
				$item['processed_items']                                     += count( $processed );
				$item['blogs'][ $blog_id ]['last_source_id'][ $source_type ] = end( $processed );
			}

			if ( $this->time_exceeded() || $this->memory_exceeded() || count( $chunk ) > count( $processed ) ) {
				break;
			}
		}

		if ( empty( $items ) ) {
			$item['blogs'][ $blog_id ]['processed'][ $source_type ] = true;
		}

		return $item;
	}

	/**
	 * Get blog items to process.
	 *
	 * @param string $source_type    Item source type
	 * @param int    $last_source_id The ID of the last item previously processed
	 * @param int    $limit          Maximum number of item IDs to return
	 * @param bool   $count          Just return the count, negates $limit, default false
	 *
	 * @return array|int
	 */
	protected function get_blog_items( $source_type, $last_source_id, $limit, $count = false ) {
		/** @var Item $class */
		$class = $this->as3cf->get_source_type_class( $source_type );

		return $class::get_source_ids( $last_source_id, $limit, $count );
	}

	/**
	 * Get blog last item ID.
	 *
	 * @param string $source_type Item source type
	 *
	 * @return int
	 */
	protected function get_blog_last_source_id( $source_type ) {
		$items = $this->get_blog_items( $source_type, null, 1 );

		return empty( $items ) ? 0 : reset( $items );
	}

	/**
	 * Have all blog items been processed?
	 *
	 * @param array $item
	 *
	 * @return bool
	 */
	protected function all_blog_items_processed( $item ) {
		foreach ( $item['blogs'] as $blog ) {
			if ( ! $this->all_source_types_processed( $blog ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Have all item types in blog been processed?
	 *
	 * @param array $blog
	 *
	 * @return bool
	 */
	protected function all_source_types_processed( $blog ) {
		foreach ( $blog['processed'] as $processed ) {
			if ( ! $processed ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Record error.
	 *
	 * @param int    $blog_id
	 * @param string $source_type
	 * @param int    $source_id
	 * @param string $message
	 */
	protected function record_error( $blog_id, $source_type, $source_id, $message ) {
		AS3CF_Error::log( $message );

		// Existing entry for item to append message to?
		foreach ( $this->errors as $error ) {
			if ( $error->blog_id === $blog_id && $error->source_type === $source_type && $error->source_id === $source_id ) {
				$error->messages[] = $message;

				return;
			}
		}

		// Restrict to $reported_errors_limit entries in the UI.
		if ( $this->count_errors() >= $this->reported_errors_limit ) {
			return;
		}

		$this->errors[] = (object) array(
			'blog_id'     => $blog_id,
			'source_type' => $source_type,
			'source_id'   => $source_id,
			'messages'    => array( $message ),
		);
	}

	/**
	 * How many items have had errors recorded by this process?
	 *
	 * @return int
	 */
	protected function count_errors() {
		return is_array( $this->errors ) ? count( $this->errors ) : 0;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		parent::complete();

		$notice_id = $this->tool->get_tool_key() . '_completed';

		$this->as3cf->notices->undismiss_notice_for_all( $notice_id );
		$this->as3cf->notices->remove_notice_by_id( $notice_id );

		if ( $this->tool->get_errors() ) {
			$message = $this->get_complete_with_errors_message();
			$type    = 'notice-warning';
		} else {
			$message = $this->get_complete_message();
			$type    = 'updated';
		}

		$args = array(
			'custom_id'         => $notice_id,
			'type'              => $type,
			'flash'             => false,
			'only_show_to_user' => false,
		);

		$this->as3cf->notices->add_notice( $message, $args );
	}

	/**
	 * Adds a note about errors to completion message.
	 *
	 * @return string
	 */
	protected function get_complete_with_errors_message() {
		$msg = $this->get_complete_message() . ' ';
		$msg .= sprintf(
			'<a href="%1$s">',
			$this->as3cf->get_plugin_page_url( array( 'hash' => '/tools/' ) )
		);
		$msg .= __( 'Some errors were recorded.', 'amazon-s3-and-cloudfront' );
		$msg .= '</a>';

		return $msg;
	}

	/**
	 * Process items chunk.
	 *
	 * @param string $source_type
	 * @param array  $source_ids
	 * @param int    $blog_id
	 *
	 * @return array
	 */
	abstract protected function process_items_chunk( $source_type, $source_ids, $blog_id );

	/**
	 * Get complete notice message.
	 *
	 * @return string
	 */
	abstract protected function get_complete_message();
}
