<?php
namespace ISQNS\Base;
/**
 * Utility Class to log Notices. Standalone Class
 */
class Notice {
	/**
	 * Message to be displayed in a warning.
	 *
	 * @var string
	 */
	private string $message;
	private string $notice_type; // success,info,warning,error

	/**
	 * Initialize class.
	 *
	 * @param string $message Message to be displayed in a warning.
	 */
	public function __construct( string $message, string $notice_type = 'info' ) {
		$this->message = $message;
        $this->notice_type = $notice_type;

		add_action( 'admin_notices', array( $this, 'render' ) );
	}

	/**
	 * Displays warning on the admin screen.
	 *
	 * @return void
	 */
	public function render() {
		printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',$this->notice_type, esc_html( $this->message ) );
	}
}