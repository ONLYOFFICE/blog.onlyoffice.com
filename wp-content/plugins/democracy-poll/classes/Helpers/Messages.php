<?php

namespace DemocracyPoll\Helpers;

class Messages {

	/** @var array */
	private $msg = [
		'error'   => [],
		'notice'  => [],
		'updated' => [],
		'warning' => [],
	];

	public function __construct(){
	}

	public function add_ok( $msg ) {
		$this->msg['updated'][] = $msg;
	}

	public function add_notice( $msg ) {
		$this->msg['notice'][] = $msg;
	}

	public function add_warn( $msg ) {
		$this->msg['warning'][] = $msg;
	}

	public function add_error( $msg ) {
		$this->msg['error'][] = $msg;
	}

	/**
	 * Gets the HTML code of all messages in the current data.
	 */
	public function messages_html(): string {
		if( ! $this->msg ){
			return '';
		}

		$out = '';

		if( isset( $this->msg['error'] ) ){
			foreach( $this->msg['error'] as $msg ){
				$out .= $this->msg_html( $msg, 'error' );
			}
		}

		if( isset( $this->msg['notice'] ) ){
			foreach( $this->msg['notice'] as $msg ){
				$out .= $this->msg_html( $msg, 'notice' );
			}
		}

		if( isset( $this->msg['updated'] ) ){
			foreach( $this->msg['updated'] as $msg ){
				$out .= $this->msg_html( $msg, 'updated' );
			}
		}

		if( isset( $this->msg['warning'] ) ){
			foreach( $this->msg['warning'] as $msg ){
				$out .= $this->msg_html( $msg, 'warning' );
			}
		}

		return $out;
	}

	public function msg_html( $msg, $type = 'updated' ): string {

		$type === 'updated' && $type = 'success'; // alias

		return sprintf( '<div class="notice-%s notice is-dismissible"><p>%s</p></div>', $type, $msg );
	}

	public function admin_notices( $msg, $type = '' ) {

		add_action( 'admin_notices', function() use ( $msg, $type ) {
			echo $this->msg_html( $msg, $type );
		} );
	}

}
