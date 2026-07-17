<?php

class Swastikaa_Logger {
   
   /**
     * A file pointer resource of the log file.
     * @access  private
     * @since   1.0.0
     */
    private $fileHandle;

    /**
     * A Path for the log file.
     * @access  private
     * @since   1.0.0
     */
    private $logger_path;
   
    /**
	* Swastikaa Logger The single instance of Swastikaa_logger.
    * @var     object
    * @access  private
    * @since   1.0.0
    */
   private static $_instance = null;

   /**
	 * Main Swastikaa_logger Instance
	 *
	 * Ensures only one instance of Swastikaa_logger is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Swastikaa_logger()
	 * @return Main Swastikaa_logger instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    /**
	 * Constructor function.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
    public function __construct() {
        $this->logger_path = wp_normalize_path( untrailingslashit( dirname( get_stylesheet_directory() ) ) );
        $this->fileHandle = fopen( $this->logger_path . '/error_logs.log', 'a+' );
    }

    /**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Something went to wrong' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Something went wrong' ), '1.0.0' );
	}

    /**
     * Write a log entry to the opened file.
     * 
     * @since 1.0.0
     */
    public function writeLog( $message ) {
        if( !is_string( $message ) ){
            $message = print_r( $message, true );
        }
        $date = gmdate( 'Y-m-d H:i:s' );
        fwrite( $this->fileHandle, "$date - $message\n" );
    }

    /**
     * Just a handy shortcut to reduce the amount of code needed to log messages
     * from the client code.
     * 
     * @since 1.0.0
     */
    public static function log( $message ) {
        $logger = static::instance();
        $logger->writeLog( $message );
    }
}
?>