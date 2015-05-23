<?php
defined('_JEXEC') or die;

class PlgSystemLanguageFilterEnhanced extends JPlugin
{
	protected $menuItemLanguageMapping = null;

	protected $app;

	/**
	 * After initialise.
	 *
	 * @return  void
	 */
	public function onAfterInitialise()
	{
		if ($this->app->isSite())
		{
			$router = $this->app->getRouter();

			// Attach build rules for language SEF.
			$router->attachBuildRule(array($this, 'preprocessBuildRule'), JRouter::PROCESS_BEFORE);
			
            if ($this->app->get('sef', 0))
            {
				$router->attachBuildRule(array($this, 'postprocessSEFBuildRule'), JRouter::PROCESS_AFTER);
            }

			$this->sefs = JLanguageHelper::getLanguages('sef');
		}
	}

	/**
	 * After dispatch
	 *
	 * @return  void
	 */
	public function onAfterDispatch()
	{
		$cookie_domain = $this->app->get('cookie_domain');
		$cookie_path = $this->app->get('cookie_path', '/');
		$this->app->input->cookie->set(JApplicationHelper::getHash('language'), '', 0, $cookie_path, $cookie_domain);
	}

	/**
	 * Add build preprocess rule to router.
	 *
	 * @param   JRouter &$router JRouter object.
	 * @param   JUri    &$uri    JUri object.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function preprocessBuildRule(&$router, &$uri)
	{
		$defaultLanguage = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');

		// Set language to real default language
		$lang = $uri->getVar('lang');

		if (empty($lang) || $lang != $defaultLanguage)
		{
			$Itemid = $uri->getVar('Itemid');

			if (!empty($Itemid))
			{
				$lang = $this->loadMenuItemLanguageMapping($Itemid);
			}
		}

		// Otherwise set the default language instead
		if (empty($lang))
		{
			$lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$uri->setVar('lang', $lang);

		if (isset($this->sefs[$lang]))
		{
			$lang = $this->sefs[$lang]->lang_code;
			$uri->setVar('lang', $lang);
		}
	}

	/**
	 * postprocess build rule for SEF URLs
	 *
	 * @param   JRouter  &$router  JRouter object.
	 * @param   JUri     &$uri     JUri object.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function postprocessSEFBuildRule(&$router, &$uri)
	{
		$languageCodes = JLanguageHelper::getLanguages('lang_code');
        $defaultLanguage = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
	    $sef = $languageCodes[$defaultLanguage]->sef;

        $uri->setPath(str_replace('/' . $sef . '/', '/', $uri->getPath()));
    }

	/**
	 * Helper method to fetch the mapped language of a certain Itemid, or fetch all mappings
	 *
	 * @param null $matchId
	 *
	 * @return array|null
	 */
	protected function loadMenuItemLanguageMapping($matchId = null)
	{
		if (!is_array($this->menuItemLanguageMapping))
		{
			$this->menuItemLanguageMapping = array();
			$defaultLanguage = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');

			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id', 'language')))
				->from($db->quoteName('#__menu'))
				->where($db->quoteName('published') . ' = 1');
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if (!empty($rows))
			{
				foreach($rows as $row)
				{
					if ($row->language == '*')
					{
						$row->language = $defaultLanguage;
					}

					$this->menuItemLanguageMapping[$row->id] = $row->language;
				}
			}
		}

		if (!empty($matchId))
		{
			if (isset($this->menuItemLanguageMapping[$matchId]))
			{
				return $this->menuItemLanguageMapping[$matchId];
			}
			else
			{
				return null;
			}
		}

		return $this->menuItemLanguageMapping;
	}
}
