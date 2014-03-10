<?php
class Localization {
	const LOCALE_TABLE			= "locale";
	const LOCALE_MESSAGE_TABLE	= "locale_message";
	const LOCALE_NAMESPACE_TABLE= "locale_namespace";
	
	private static $_db = NULL;
	private static $_locale = 'en';
	private static $_cache = array();
	
	public static function connect($db) {
		if ($db !== self::$_db) {
			self::$_cache = array(); // clear the cache due to the new datasource
			self::$_db = $db;
		}
	}
	
	public static function isConnected() {
		return self::$_db !== NULL;
	}
	
	public static function setLocale($locale) {
		if ($locale !== self::$_locale && is_string($locale)) {
			self::$_locale = $locale;
			self::$_cache = array(); // clear the cached language files
		}
	}
	
	public static function getLocale() {
		return self::$_locale;
	}
	
	public static function get($message,$formats=array()) {
		if (!self::isConnected()) {
			throw new Exception("Database is not connected in the Localization component");
		}
		
		if (($dot = strpos($message, '.')) > 0 && $dot < (strlen($message) - 1)) {
			$setName = substr($message, 0, $dot);
			$messageName = strtolower(substr($message, $dot + 1));
			
			$langSet = self::getLanguageSet($setName);
			if (isset($langSet[$messageName])) {
				return self::format($langSet[$messageName], $formats);
			}
			
			trigger_error("The message '{$messageName}' was not found in the namespace '{$setName}' under the '" . self::$_locale . "' locale.", E_USER_WARNING);	
		}
		
		return "";
	}
	
	public static function set($message, $value) {
		if (!self::isConnected()) {
			throw new Exception("Database is not connected in the Localization component");
		}
		
		if (($dot = strpos($message, '.')) > 0 && $dot < (strlen($message) - 1)) {
			$setName = strtolower(substr($message, 0, $dot));
			$messageName = strtolower(substr($message, $dot + 1));

			$namespaceRow = self::$_db->queryFirst("SELECT id FROM " . self::LOCALE_NAMESPACE_TABLE . " WHERE LOWER(namespace) = ?", array($setName));
			if ($namespaceRow === NULL) {
				// namespace doesn't exist in the database, insert it.
				self::$_db->excecute("INSERT INTO " . self::LOCALE_NAMESPACE_TABLE . "(namespace) VALUES(?)", array($setName));
				$namespaceRow = self::$_db->queryFirst("SELECT id FROM " . self::LOCALE_NAMESPACE_TABLE . " WHERE LOWER(namespace) = ?", array($setName));
			}
			
			if ($namespaceRow !== NULL) {
				self::$_db->execute("
					INSERT INTO " . self::LOCALE_MESSAGE_TABLE . "(locale_id,namespace_id,name,message) 
					VALUES (:locale,:namespace,:name,:message)
					ON DUPLICATE KEY UPDATE
						message = :message,
						modified_date = CURRENT_TIMESTAMP",
					array(
						"locale" => self::$_locale,
						"namespace" => $namespaceRow['id'],
						"name" => $messageName,
						"message" => $value,
					)
				);
			}
		}
	}
	
	private static function getLanguageSet($namespace) {
		$namespace = strtolower($namespace);
		if (!isset(self::$_cache[$namespace])) {
			$results = self::$_db->queryAll(
				"SELECT	LOWER(msg.name) AS name, msg.message AS message
				 FROM	" . self::LOCALE_MESSAGE_TABLE . " msg
				 LEFT JOIN " .self::LOCALE_NAMESPACE_TABLE. " ns ON ns.id = msg.namespace_id 
				 WHERE	msg.locale_id = :locale
				 AND	LOWER(ns.namespace) = :namespace
				 AND	msg.active = 'Y'",
				array(
					"locale" => self::$_locale, 
					"namespace" => $namespace
				)
			);
			
			self::$_cache[$namespace] = array();
			foreach($results as $row) {
				self::$_cache[$namespace][$row['name']] = $row['message'];
			}
		}
		
		return self::$_cache[$namespace];
	}
	
	private static function format($message, $formats) {
		if (!empty($formats)) {
			$formatter = new Formatter($formats);
			return preg_replace_callback('/:([a-z]+)/i', array($formatter, 'format'), $message);
		}
		else {
			return $message;
		}
	}
}

class Formatter {
	private $_formats;
	
	public function __construct($formats) {
		$this->_formats = $formats;
	}
	
	public function format($matches) {
		return isset($this->_formats[$matches[1]])
			? $this->_formats[$matches[1]]
			: $matches[0];
	}
}
?>