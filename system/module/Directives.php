<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Directives Module
 *
 * Append Special Shortcodes to the core 
 *
 * @package		codeHive
 * @subpackage	Module
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 *
 * 
 * For Nasted directives
 * Spacial Thanx to http://stackoverflow.com/questions/5438133/how-to-remove-improper-nesting-bbcode-tags-using-php
 * and http://www.amazon.com/Mastering-Regular-Expressions-Jeffrey-Friedl/dp/0596528124
 * $re_url = '%# Match outermost [URL=...]...[/URL] (may have nested URL tags
 *   (\[URL\b[^[\]]*+\])       # $1: opening URL tag.
 *   (                         # $2: Contents of URL tag.
 *     (?:                     # Group of contents alternatives.
 *       (?:(?!\[/?URL\b).)++  # One or more non-"[URL", non-"[/URL"
 *     | (?R)                  # Or recursively match nested [URL]..[/URL].
 *     )*+                     # Zero or more contents alternatives.
 *   )                         # End $2: Contents of URL tag.
 *   (\[/URL\s*+\])            # $3: Outermost closing [/URL]
 *   %six';
 * 
 */

class Directives{

	/**
	 * Directives bootstrap
	 *
	 * @access	public
	 * @return	string
	 */
	public static function __bootstrap(){


		/**
		* directive Each
		* Description : Looping array
		*/
		Shortcode::register(array(
			'code' 		=> 'each',
			'pattern' 	=> '%\<EACH (\b[^<\>]*+)\>((?:(?:(?!\</?EACH\b).)++| (?R))*+)(\</EACH\s*+\>)%six',
			'callback' 	=> function($match){
				$str = "";
				$ex = explode(' in ',trim($match[1]));
				foreach(Controller::$scope->$ex[1] as $k => $file){
					Controller::$scope->$ex[0] = $file;
					$index = "{$ex[0]}Index";
					echo $index;
					Controller::$scope->$index = $k;
					Controller::$scope->__index = $k;
					$str .= Shortcode::trigger($match[2]);
				}
				return $str;
			}
		));

		/**
		* directive Scope
		* Description : Scoping variables
		*/
		Shortcode::register(array(
			'code' 		=> 'scope',
			'pattern' 	=> '#\{-(.+)\-}#Usi',
			'callback' 	=> function($match){
				$filter = explode("|", $match[1]);//check ex for plugins like lower
				$match[1] = $filter[0];

				$val = Directives::scope($match[1]);

				if(isset($filter[1])){
					$f = trim($filter[1]);
					if($f == 'upper'){
						$val = strtoupper($val);
					}else if($f == 'escape'){
						$val = htmlspecialchars($val,ENT_QUOTES,'UTF-8');
					}
				}
				return $val;
			}
		));

		/**
		* directive Each
		* Description : Looping array
		*/
		Shortcode::register(array(
			'code' 		=> 'if',
			'pattern' 	=> '%\<IF (\b[^<\>]*+)\>((?:(?:(?!\</?IF\b).)++| (?R))*+)(\</IF\s*+\>)%six',
			'callback' 	=> function($match){

				$parse = function($v){
					if(strpos(trim($v),'"') === 0){
						$v = rtrim($v,'"');
						$v = ltrim($v,'"');
						return $v;
					}else if(strpos(trim($v),"'") === 0){
						$v = rtrim($v,"'");
						$v = ltrim($v,"'");
						return $v;
					}else if(is_numeric(trim($v))){
						return $v;
					}else if(trim($v) == "true"){
						return true;
					}else if(trim($v) == "false"){
						return false;
					}else if(trim($v) == "null"){
						return '';
					}else{
						return Directives::scope($v);
					}
				};
				$ok = true;

				if(strpos($match[1], "=") !== false){
					$ex = explode("=",trim($match[1]));

					if($parse($ex[0]) == $parse($ex[1]))return Shortcode::trigger($match[2]);

				}else if(strpos($match[1], "!=") !== false){
					$ex = explode("!=",trim($match[1]));
					if($parse($ex[0]) != $parse($ex[1]))return Shortcode::trigger($match[2]);

				}

				return ;
				/*
				$str = "";
				$ex = explode(' in ',trim($match[1]));
				foreach(Controller::$scope->$ex[1] as $file){
					Controller::$scope->$ex[0] = $file;
					$str .= Shortcode::trigger($match[2]);
				}
				return $str;
				*/
			}
		));

		/*
		* directive Scope
		* Description : Scoping variables
		*
		Shortcode::register(array(
			'code' 		=> 'scope',
			'pattern' 	=> '#\<!(.+)\>#Usi',
			'callback' 	=> function($match){
				$val = Controller::$scope;
				$ex = explode(".", $match[1]);
				$val = isset($val->$ex[0])?$val->$ex[0]:null;
				if(sizeof($ex) > 1){
					foreach($ex as $k => $v){
						if($k == 0)continue;
						$val = isset($val[$v])?$val[$v]:null;
						if($val == null)break;
					}
				}
				return $val;
			}
		));
		*/

		/**
		* directive Import
		* Description : include view
		*/
		Shortcode::register(array(
			'code' 		=> 'import',
			'pattern' 	=> '#\@import\((.+)\)#Usi',
			'callback' 	=> function($match){
				return View::load($match[1]);// set additional import if
			}
		));

		/**
		* directive Event
		* Description : trigger event
		*/
		Shortcode::register(array(
			'code' 		=> 'event',
			'pattern' 	=> '#\@event\((.+)\)#Usi',
			'callback' 	=> function($match){
				return Event::trigger($match[1]);
			}
		));

		/**
		* directive Internationalization
		* Description : trigger event
		* Usage : __(You have :count messages and :days days :> {count:1,days:2} @ module.name)
		*/
		Shortcode::register(array(
			'code' 		=> 'internationalization',
			'pattern' 	=> '#\__\((.+)\)#Usi',
			'callback' 	=> function($match){
				$ar = array();
				$at = false;

				$ex = explode(":>", $match[1]);

				if(isset($ex[1])){
					$ex2 = explode("@", $ex[1]);
					//
					$a = explode(",", rtrim(ltrim(trim($ex2[0]),"{"),"}"));
					foreach($a as $v){
						$vv = explode(":", $v);
						$ar[$vv[0]] = $vv[1];
					}
					//
					if(isset($ex2[1]))$at = $ex2[1];
				}
				
				return Internationalization::translate(trim($ex[0]),$ar,$at);
			}
		));

/*
		Shortcode::register(array(
			'code' 		=> 'input',
			'pattern' 	=> '#\[input(.+)]#Usi',
			'callback' 	=> function($match){
				return "input is here";
			}
		));

		Shortcode::register(array(
			'code' 		=> 'extends',
			'pattern' 	=> '#\@extends\((.+)\)#Usi',
			'callback' 	=> function($match){
				return "you are extending {$match[1]}";
			}
		));
*/

	}

	private static function scope($v){
		$val = Controller::$scope;
		$ex = explode(".", $v);
		$val = isset($val->$ex[0])?$val->$ex[0]:null;
		if(sizeof($ex) > 1){
			foreach($ex as $k => $v){
				if($k == 0)continue;
				$val = (array)$val;
				$val = isset($val[$v])?$val[$v]:null;
				if($val == null)break;
			}
		}
		return stripcslashes($val);
	}
}

/* End of file Query.php */
/* Location: ./system/module/Query.php */