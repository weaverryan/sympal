<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginsfSympalContent extends BasesfSympalContent
{
  protected
    $_route,
    $_routeObject,
    $_editableSlotsExistOnPage,
    $_contentRouteObject = null;
  
  /**
   * Initializes a new sfSympalContent for the given type
   * 
   * @param   mixed $type Specify either the name of the content type (e.g. sfSympalPage)
   *                      or pass in a sfSympalContentType object
   * 
   * @return  sfSympalContent
   */
  public static function createNew($type)
  {
    if (is_string($type))
    {
      $typeString = $type;

      $type = Doctrine_Core::getTable('sfSympalContentType')->findOneByString($type);

      if (!$type)
      {
        throw new InvalidArgumentException(sprintf('Could not find Sympal Content Type named "%s"', $typeString));
      }
    }

    if (!$type instanceof sfSympalContentType)
    {
      $type = is_object($type) ? get_class($type) : gettype($type);

      throw new InvalidArgumentException(sprintf('Invalid Content Type. Expected sfSympalContentType, got %s', $type));
    }

    $name = $type->name;

    $content = new sfSympalContent();
    $content->Type = $type;
    $content->$name = new $name();

    return $content;
  }

  /**
   * Returns this module with which this record should be rendered
   *
   * @return string
   */
  public function getModuleToRenderWith()
  {
    if ($module = $this->_get('module'))
    {
      return $module;
    }
    else
    {
      return $this->getType()->getModuleToRenderWith();
    }
  }

  /**
   * Returns the action with which this record should be rendered.
   *
   * @return string
   */
  public function getActionToRenderWith()
  {
    if ($actionName = $this->_get('action'))
    {
      return $actionName;
    }
    else
    {
      return $this->getType()->getActionToRenderWith();
    }
  }

  /**
   * Returns the slug with underscores instead of dashes
   *
   * Used when creating routes for content records with a custom_path
   *
   * @return string
   */
  public function getUnderscoredSlug()
  {
    return str_replace('-', '_', $this['slug']);
  }

  /**
   * @return string
   */
  public function getContentTypeClassName()
  {
    return $this->getType()->getName();
  }

  /**
   * @return string
   */
  public function __toString()
  {
    return $this->getHeaderTitle();
  }

  /**
   * @return string
   */
  public function getTitle()
  {
    return $this->getHeaderTitle();
  }

  /**
   * Returns the content type record related to this Content record, which
   * could be one of many classes (depending on the content type).
   *
   * If the class name of the content type related to this content were
   * "Product", then this method would be equivalent to the following:
   *
   * $content->Product
   *
   * @return Doctrine_Record|bool
   */
  public function getRecord()
  {
    if ($this['Type']['name'])
    {
      Doctrine_Core::initializeModels(array($this['Type']['name']));

      return $this[$this['Type']['name']];
    }
    else
    {
      return false;
    }
  }

  /**
   * Publishes this content with a published date of right now.
   *
   * @return void
   */
  public function publish()
  {
    $this->date_published = date('Y-m-d H:i:s');
    $this->save();
    $this->refresh();
  }

  /**
   * Upublishes this content
   *
   * @return void
   */
  public function unpublish()
  {
    $this->date_published = null;
    $this->save();
  }

  /**
   * Attempts to retrieve some sort of "title" for this record by seeing
   * if the content type record has a series of title-like columns
   *
   * @return string
   */
  public function getHeaderTitle()
  {
    if ($record = $this->getRecord())
    {
      $guesses = array('name',
                       'title',
                       'username',
                       'subject');

      // we try to guess a column which would give a good description of the object
      foreach ($guesses as $descriptionColumn)
      {
        try
        {
          return (string) $record->get($descriptionColumn);
        }
        catch (Doctrine_Record_UnknownPropertyException $e) {}
      }

      return (string) $this;
    }

    return sprintf('No description for object of class "%s"', $this->getTable()->getComponentName());
  }

  /**
   * @deprecated
   */
  public function getEditRoute()
  {
    throw new sfException('Method deprecated. Use @sympal_content_edit?id=ID.');
  }

  /**
   * Returns the content that will be output for xml, yml, and json formats
   *
   * This looks for a getXmlFormatData() named function on the content
   * type record, then on the sfSympalContent record. If neither is found,
   * getDefaultFormatData() is returned. In all cases, the method should
   * return an array.
   *
   * @param  string $format The format (e.g. xml, json, yml)
   * @return string
   */
  public function getFormatData($format)
  {
    $method = 'get'.ucfirst($format).'FormatData';
    
    if (method_exists($this->getContentTypeClassName(), $method))
    {
      $data = $this->getRecord()->$method();
    }
    else if (method_exists($this, $method))
    {
      $data = $this->$method();
    }
    else
    {
      $data = $this->getDefaultFormatData();
    }

    return Doctrine_Parser::dump($this->$method(), $format);
  }

  /**
   * Returns the default data to output for non-html formats that aren't
   * otherwise handled by getFormatData().
   *
   * @return array
   */
  public function getDefaultFormatData()
  {
    $data = $this->toArray(true);
    unset(
      $data['CreatedBy'],
      $data['Site']
    );

    return $data;
  }

  /**
   * Returns whether or not this content is published
   *
   * @return bool
   */
  public function getIsPublished()
  {
    return ($this->getDatePublished() && strtotime($this->getDatePublished()) <= time()) ? true : false;
  }

  /**
   * Returns whether or not this content is set with a published date in
   * the future.
   *
   * If this content is already published (date_published in the past), this
   * will return false.
   *
   * @return bool
   */
  public function getIsPublishedInTheFuture()
  {
    return ($this->getDatePublished() && strtotime($this->getDatePublished()) > time()) ? true : false;
  }

  /**
   * Getter for the month published
   *
   * @param string $format
   * @return string
   */
  public function getMonthPublished($format = 'm')
  {
    return date('m', strtotime($this->getDatePublished()));
  }

  /**
   * Getter for the day published
   *
   * @return string
   */
  public function getDayPublished()
  {
    return date('d', strtotime($this->getDatePublished()));
  }

  /**
   * Getter for the year published
   *
   * @return string
   */
  public function getYearPublished()
  {
    return date('Y', strtotime($this->getDatePublished()));
  }

  /**
   * Returns the name of the author of this record, if CreatedBy is set
   *
   * @return string
   */
  public function getAuthorName()
  {
    return $this->getCreatedById() ? $this->getCreatedBy()->getName() : null;
  }

  /**
   * Returns the author of the
   *
   * @return string
   */
  public function getAuthorEmail()
  {
    return $this->getCreatedById() ? $this->getCreatedBy()->getEmailAddress() : null;
  }

  /**
   * @return bool
   */
  public function hasCustomPath()
  {
    return $this->custom_path ? true : false;
  }

  /**
   * Returns the route object related to this content record
   *
   * @return sfSympalContentRouteObject
   */
  public function getContentRouteObject()
  {
    if (!$this->_contentRouteObject)
    {
      $this->_contentRouteObject = new sfSympalContentRouteObject($this);
    }

    return $this->_contentRouteObject;
  }

  /**
   * Returns the url to this content
   *
   * @param array $options The array of url options
   * @return string
   */
  public function getUrl($options = array())
  {
    return sfContext::getInstance()->getController()->genUrl($this->getRoute(), $options);
  }

  /**
   * Getter for the route name
   *
   * @return string
   */
  public function getRoute()
  {
    return $this->getContentRouteObject()->getRoute();
  }

  /**
   * Getter for the route path
   *
   * @return string
   */
  public function getRoutePath()
  {
    return $this->getContentRouteObject()->getRoutePath();
  }

  /**
   * Getter for the route name
   *
   * @return string
   */
  public function getRouteName()
  {
    return $this->getContentRouteObject()->getRouteName();
  }

  /**
   * Getter for the route object
   *
   * @return sfRoute
   */
  public function getRouteObject()
  {
    return $this->getContentRouteObject()->getRouteObject();
  }

  /**
   * Getter for the evaluated route path
   *
   * @return string
   */
  public function getEvaluatedRoutePath()
  {
    return $this->getContentRouteObject()->getEvaluatedRoutePath();
  }

  /**
   * Used by Sluggable to create the slug for this record.
   *
   * If a method called "slugBuilder" exists on the content type record,
   * that will be called to retrieve the slug.
   *
   * @static
   * @param  string $text The text to slug
   * @param  sfSympalContent $content
   * @return string
   */
  public static function slugBuilder($text, sfSympalContent $content)
  {
    if ($record = $content->getRecord())
    {
      try
      {
        return $record->slugBuilder($text);
      }
      catch (Doctrine_Record_UnknownPropertyException $e)
      {        
      }
    }

    return Doctrine_Inflector::urlize($text);
  }

  /**
   * This gets the correct template to render with
   * 
   * The process is this:
   *   1) Look first on the content record itself for a template "name"
   *   2) Look next on the type record for a template "name"
   * 
   * We then retrieve the actual template (module/template) by looking
   * under the "content_templates" key of the current content template's
   * configuration for the template "name".
   * 
   * If all else fails, the "default_view" template name of the current
   * content type config will be used
   */
  public function getTemplateToRenderWith()
  {
    if (!$templateName = $this->getTemplate())
    {
      $templateName = $this->getType()->getTemplate();
    }

    $templates = sfSympalConfig::getContentTemplates($this['Type']['name']);

    if (!isset($templates[$templateName]))
    {
      $templateName = 'default_view';
      if (!isset($templates[$templateName]))
      {
        throw new sfException(sprintf('No "default_view" template specified for "%s" content type', $this->getType()->getName()));
      }
    }

    if (!is_array($templates[$templateName]) || !isset($templates[$templateName]['template']))
    {
      throw new sfException(sprintf('Key "template" must be set under content_template "%s" in app.yml', $templateName));
    }

    return $templates[$templateName]['template'];
  }

  /**
   * @TODO think about where to put the theme logic
   *
   * Renders the theme name with which this Content should be rendered.
   * Priority is in this order
   * 
   *   * Content->theme
   *   * ContentType->theme
   *   * Site->theme
   * 
   * If none of the above are found, this Content record has no theme preference
   * 
   * @return string
   */
  public function getThemeToRenderWith()
  {
    if ($theme = $this->getTheme())
    {
      return $theme;
    }
    else if ($theme = $this->getType()->getTheme())
    {
      return $theme;
    }
    else if ($theme = $this->getSite()->getTheme())
    {
      return $theme;
    }
  }

/* @TODO put this in the search plugin
  public function disableSearchIndexUpdateForSave()
  {
    $this->_updateSearchIndex = false;
  }*/

  public function save(Doctrine_Connection $conn = null)
  {
    if (!$this->relatedExists('Site'))
    {
      $site = Doctrine_Core::getTable('sfSympalSite')->fetchCurrent(true);
      $this->Site = $site;
    }

    $result = parent::save($conn);

/*  @TODO put this in the search plugin
    if ($this->_updateSearchIndex)
    {
      sfSympalSearch::getInstance()->updateSearchIndex($this);
    }

    $this->_updateSearchIndex = true;*/

    return $result;
  }

  public function delete(Doctrine_Connection $conn = null)
  {
/*  @TODO put this back into the search plugin
    if ($this->_updateSearchIndex)
    {
      $index = sfSympalSearch::getInstance()->getIndex();
      foreach ($index->find('pk:'.$this->getId()) as $hit)
      {
        $index->delete($hit->id);
      }
    }*/

    // delete content from accociated content type table
    $this->getRecord()->delete();
    
    return parent::delete($conn);
  }

  /**
   * Returns the page title to be used for this content. This will retrieve
   * the page title from the Site relation if not set on this record.
   *
   * @return string
   */
  public function getPageTitleForRendering()
  {
    if ($pageTitle = $this->getPageTitle())
    {
      return $pageTitle;
    }
    else if ($pageTitle = $this->getSite()->getPageTitle())
    {
      return $pageTitle;
    }
    else if (sfSympalConfig::get('auto_seo', 'title'))
    {
      if (method_exists($this->getContentTypeClassName(), 'getAutoSeoTitle'))
      {
        return $this->Record->getAutoSeoTitle();
      }

      return $this->getAutoSeoTitle();
    }

    return null;
  }

  /**
   * Generates a page title for this content record
   *
   * @return string
   */
  public function getAutoSeoTitle()
  {
    $format = sfSympalConfig::get('auto_seo', 'title_format');
    $find = array(
      '%site_title%',
      '%content_title%',
      '%content_id%',
    );

    $replace = array(
      $this->getSite()->getTitle(),
      (string) $this,
      $this->getId(),
    );
    $title = str_replace($find, $replace, $format);

    return $title;
  }

  /**
   * Returns the meta keyowrds to be used for this content record
   *
   * @return string
   */
  public function getMetaKeywordsForRendering()
  {
    if ($metaKeywords = $this->getMetaKeywords())
    {
      return $metaKeywords;
    }
    else if ($metaKeywords = $this->getSite()->getMetaKeywords())
    {
      return $metaKeywords;
    }
  }

  /**
   * Returns the meta description to be used for this content record
   *
   * @return string
   */
  public function getMetaDescriptionForRendering()
  {
    if ($metaDescription = $this->getMetaDescription())
    {
      return $metaDescription;
    }
    else if ($metaDescription = $this->getSite()->getMetaDescription())
    {
      return $metaDescription;
    }
  }

/* @TODO put this back in the search plugin
  public function getSearchData()
  {
    $searchData = array();
    $clone = clone $this;
    $data = $clone->toArray(false);
    if ($data)
    {
      foreach ($data as $key => $value)
      {
        if (is_scalar($value))
        {
          $searchData[$key] = $value;
        }
      }
    }
    $data = $clone->getRecord()->toArray(false);
    if ($data)
    {
      foreach ($data as $key => $value)
      {
        if (is_scalar($value))
        {
          $searchData[$key] = $value;
        }
      }
    }
    foreach ($this->getSlots() as $slot)
    {
      $slot->setContentRenderedFor($this);
      $searchData[$slot->getName()] = $slot->getValue();
    }
    return $searchData;
  }*/
  
  /**
   * @TODO refactor this for the new system
   *
   * Used by sfSympalContentSlot to render the created_at_id slot value
   * 
   * @see sfSympalContentSlot::getValueForRendering()
   * @return string
   */
  public function getCreatedByIdSlotValue(sfSympalContentSlot $slot)
  {
    return $this->created_by_id ? $this->CreatedBy->username : 'nobody';
  }
  
  /**
   * @TODO refactor this for the new system
   * 
   * Used by sfSympalContentSlot to render the date_published slot value
   * 
   * @see sfSympalContentSlot::getValueForRendering()
   * @return string
   */
  public function getDatePublishedSlotValue(sfSympalContentSlot $slot)
  {
    if ($this->date_published)
    {
      sfApplicationConfiguration::loadHelpers('Date');

      return format_datetime($this->date_published, sfSympalConfig::get('date_published_format'));
    }
    else
    {
      return 'unpublished';
    }
  }

  /**
   * @TODO refactor
   */
  public function setEditableSlotsExistOnPage($bool)
  {
    $this->_editableSlotsExistOnPage = $bool;
  }

  /**
   * @TODO refactor
   */
  public function getEditableSlotsExistOnPage()
  {
    return $this->_editableSlotsExistOnPage;
  }
}