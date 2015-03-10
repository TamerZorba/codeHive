<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Router Module
 *
 * control URL Parameters 
 *
 * @package		codeHive
 * @subpackage	Module
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class Router{

	/**
	 * List of arrays
	 *
	 * @access protected
	 */
	protected static $callback;
	protected static $router;

	public static function _goto($to=false){
		if(!$to)$to = Request::site();
		else $to = Request::site().$to;
		die("<meta http-equiv='refresh' content='0; url={$to}' />");
		return $to;
	}

	public static function get($name=0){
		$ret = false;
		$url = Request::parser();
		if(isset(self::$router[$url[0]])){
			$rarr = self::$router[$url[0]];
		}else{
			$rarr = self::$router['_otherwise'];
			if(!$rarr)$rarr = array();
		}
		if($name !== 0 && sizeof($rarr) > 0){
			if($name == 'pagename'){
				$ret = $url[0];
			}else{
				$arrSearch = array_search($name, $rarr);
				if($arrSearch !== false){
					if(isset($url[$arrSearch+1]))$ret = $url[$arrSearch+1];
				}
			}
		}
		return String::escape($ret);
	}
	
	public static function on($name,$val,$cb=false){
		$name = urlencode($name);

		if($cb != false)self::$router[$name] = $val;
		else $cb = $val;
		
		self::$callback[$name] = $cb;
		
		return self::$router;
	}

	public static function parse($arr){
		$mod = self::get("pagename");
		self::$router[$mod] = $arr;
		$cls = new stdClass();
		if(isset(self::$router[$mod]))foreach(self::$router[$mod] as $k)$cls->$k = self::get($k);
		return $cls;
	}
	
	public static function otherwise($val,$cb=false){
		
		if($cb != false)self::$router['_otherwise'] = array_merge($val,array("pagename"));
		else $cb = $val;

		self::$callback['_otherwise'] = $cb;
		
		return self::$router;
	}

	public static function callback(){
		//$mod = Request::get('pagename');
		$mod = Request::parser(0);

		$_exist = isset(self::$callback[$mod])?true:false;
		if(!$_exist){
			$mod = '_otherwise';
			$_exist = isset(self::$callback[$mod])?true:false;
		}

		if($_exist){
			$cls = new stdClass();
			if(isset(self::$router[$mod]))foreach(self::$router[$mod] as $k)$cls->$k = self::get($k);
			echo Controller::trigger(self::$callback[$mod],$cls);
		}else{
			// 404, 403... 
			//post parser
			//get page with that name if not then search for post
			if(Module::used("post"))post::parser();
		}
	}


	public static function __shutdown(){
		self::callback();
	}
}

/* End of file Router.php */
/* Location: ./system/module/Router.php */