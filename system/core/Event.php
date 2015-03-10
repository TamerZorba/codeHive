<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Event Module
 *
 * This class Control Event Requests
 *
 * @package		codeHive
 * @subpackage	Module
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class Event{

	/**
	 * Variables
	 *
	 * @var mixen
	 * @access protected
	 */
	private static $events;

	// --------------------------------------------------------------------

	/**
	 * event addListener
	 *
	 * @param	string
	 * @param	mixen
	 * @return	void
	 */
	public static function addListener($event,$callback){
		if(!isset(self::$events[$event]))self::$events[$event] = [];
		array_push(self::$events[$event], $callback);
		return $callback;
	}


	// --------------------------------------------------------------------

	/**
	 * event trigger
	 *
	 * @return	function
	 */
	public static function trigger($event){
		$r = "";
		if(isset(self::$events[$event]))foreach(self::$events[$event] as $c)$r .= call_user_func($c);
		return $r;
	}
}