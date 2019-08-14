<?php
class Lister_CookieThemer_Model_Design_Package extends Mage_Core_Model_Design_Package {

	private static $_regexMatchCache      = array();
	private static $_customThemeTypeCache = array();
	private static $_regpathMatchCache    = array();

	public function getTheme($type) {

		if (empty($this->_theme[$type])) {
			$this->_theme[$type] = Mage::getStoreConfig('design/theme/'.$type, $this->getStore());
			if ($type !== 'default' && empty($this->_theme[$type])) {
				$this->_theme[$type] = $this->getTheme('default');
				if (empty($this->_theme[$type])) {
					$this->_theme[$type] = self::DEFAULT_THEME;
				}

				// "locale", "layout", "template"
			}
		}

		// + "default", "skin"
		// set exception value for theme based on cookie, if defined in config
		$instVar         = "design/theme/{$type}_cookie";
		$cookieThemeType = $this->_checkAgainstCookie($instVar);

		$validFolder = $this->validateFolder($cookieThemeType, $type, $instVar);
		if ($cookieThemeType && $validFolder) {
			//validate the existence of the  value /folder mentiond in config
			$this->_theme[$type] = $cookieThemeType;
		}

		// set exception value for theme, if defined in config
		$customThemeType = $this->_checkUserAgentAgainstRegexps("design/theme/{$type}_ua_regexp");
		if ($customThemeType) {
			$this->_theme[$type] = $customThemeType;
		}

		return $this->_theme[$type];
	}
	/**
	 * Return if a folder exists in the name given in the config
	 *
	 * @param $pathValue - design / skin name
	 * @param string $type
	 * @param string $instVar
	 * @return bool|string
	 */
	public function validateFolder($pathValue, $type, $instVar) {
		if (!$pathValue) {
			return;
		}
		if (!empty(self::$_regpathMatchCache[$instVar][$pathValue])) {
			return self::$_regpathMatchCache[$instVar][$pathValue];
		}

		return $this->designExists($pathValue, $instVar, $type);
	}
	/**
	 * Return if the  theme / skin  mentioned is a valid folder
	 *
	 * @param string $themeName
	 * @param string $instVar
	 * @param string $type
	 * @param string $area
	 * @return bool|string
	 */
	public function designExists($themeName, $instVar, $type, $area = self::DEFAULT_AREA) {
		$type                                           = ($type == "skin")?'skin':'design';
		self::$_regpathMatchCache[$instVar][$themeName] = is_dir(Mage::getBaseDir($type).DS.$area.DS.$this->getPackageName().DS.$themeName);
		return is_dir(Mage::getBaseDir($type).DS.$area.DS.$this->getPackageName().DS.$themeName);
	}

	

	/**
	 * Get regex rules from config and check cookie against them
	 *
	 * Rules must be stored in config as a serialized array(['cookienv']=>'...', ['value'] => '...')
	 * Will return false or found string.
	 *
	 * @param string $cookiePath
	 * @return mixed
	 */
	protected function _checkAgainstCookie($cookiePath) {

		if (empty($cookiePath)) {
			return false;
		}
		if (!empty(self::$_customThemeTypeCache[$cookiePath])) {
			return self::$_customThemeTypeCache[$cookiePath];
		}
		$configValueSerialized = Mage::getStoreConfig($cookiePath, $this->getStore());

		if (!$configValueSerialized) {
			return false;
		}

		$cookiedetails = @unserialize($configValueSerialized);
		if (empty($cookiedetails)) {
			return false;
		}

		return self::getPackageByCookie($cookiedetails, $cookiePath);
	}

	/**
	 * Return package name based on design exception rules
	 *
	 * @param array $rules - design exception rules
	 * @param string $cookiePath
	 * @return bool|string
	 */
	public static function getPackageByCookie(array $rules, $cookiePath) {
		foreach ($rules as $rule) {
			if (!empty($rule['cookienv'])) {
				/* Switching mobile theme based on cookie */
				if (strpos($rule['cookienv'], "|") > 0) {
					$cookieNameValue = str_replace("|", "_", trim($rule['cookienv']));
					if (!empty(self::$_regexMatchCache[$cookieNameValue][$cookiePath])) {
						/* Switching mobile theme based on cookie */
						self::$_customThemeTypeCache[$cookiePath] = $rule['value'];
						return $rule['value'];
					}

					$cookieDetailsArray = explode("|", $rule['cookienv']);
					if (is_array($cookieDetailsArray)) {
						$cookieName    = $cookieDetailsArray[0];
						$valFromConfig = $cookieDetailsArray[1];
						$cookieVal     = Lister_CookieThemer_Helper_Data::getCookieValue($cookieName);
						if ($cookieVal == $valFromConfig) {
							self::$_regexMatchCache[$cookieNameValue][$cookiePath] = true;
							self::$_customThemeTypeCache[$cookiePath]              = $rule['value'];
							return $rule['value'];
						}
					}
				}
			}

			$regexp = str_replace("|", "_", trim($rule['cookienv']));

		}
		return false;
	}

}
