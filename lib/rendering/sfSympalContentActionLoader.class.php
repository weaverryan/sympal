<?php

/**
 * Used the retrieve the sfSympalContent record for an action and initialize
 * it into the system, performing actions like:
 *
 *  * Setting the current content and site on the site manager
 *  * Setting the page title, metadata
 *  * Handling unpublished content
 * 
 * @package     sfSympalPlugin
 * @subpackage  rendering
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-31
 * @version     svn:$Id$ $Author$
 */
class sfSympalContentActionLoader
{
  protected
    $_actions,
    $_applicationConfiguration,
    $_user,
    $_response,
    $_request,
    $_content,
    $_dispatcher;

  /**
   * Class constructor
   *
   * @param sfActions $actions
   * @return void
   */
  public function __construct(sfActions $actions)
  {
    $this->_actions = $actions;
    $this->_applicationConfiguration = $actions->getContext()->getApplicationConfiguration();
    $this->_user = $actions->getUser();
    $this->_response = $actions->getResponse();
    $this->_request = $actions->getRequest();
    $this->_dispatcher = $this->_actions->getContext()->getEventDispatcher();
  }

  /**
   * Returns the current sfSympalContent record.
   *
   * Also sets the current content and site on the site manager service.
   *
   * @return sfSympalContent
   */
  public function getContent()
  {
    if (!$this->_content)
    {
      $this->_content = $this->_actions->getRoute()->getObject();
      if ($this->_content)
      {
        $siteManager = $this->_getSympalConfiguration()->getSiteManager();
        $siteManager->setSite($this->_content->getSite());
        $siteManager->setCurrentContent($this->_content);
      }
    }

    return $this->_content;
  }

  /**
   * Loads and processes the sfSympalContent record associated with this action:
   *
   *  * Fetches the sfSympalContent record
   *  * Handles the 404 if necessary
   *  * Handles unpublished content
   *  * Sets up the metadata (page title, etc)
   *  * Throws a sympal.load_content event
   *
   * @todo Replace the security check
   *
   * @return sfSympalContent
   */
  public function loadContent()
  {
    $content = $this->getContent();
    $this->_handleForward404($content);
    $this->_handleIsPublished($content);
    //$this->_user->checkContentSecurity($content);

    $this->_loadMetaData($this->_response);

    // throw the sympal.load_content event
    $this->_dispatcher->notify(new sfEvent($this, 'sympal.load_content', array('content' => $content)));

    return $content;    
  }

  /**
   * @param bool $fakeHtmlRequest Whether to fake an html request. Should usually be left as false.
   * @return sfSympalContentRenderer
   */
  public function loadContentRenderer($fakeHtmlRequest = false)
  {
    // load and initialize the content
    $content = $this->loadContent();

    // get the renderer
    $renderer = $this->_getSympalConfiguration()
      ->getContentRenderer($content, $this->_request->getRequestFormat());

    if ($fakeHtmlRequest)
    {
      $this->fakeHtmlRequest();
    }

    return $renderer;
  }

  /**
   * Allows the request to think it has an html format while allowing
   * the respones to still return with the correct mime-type.
   *
   * The advantage is that the normal templates (with a format-specific
   * filename suffix) will be used. This is useful in sympal_content_renderer
   * when we simply want to get ourselves to the template so we can
   * echo the content renderer.
   *
   * @return void
   */
  public function fakeHtmlRequest()
  {
    if ($renderer->getFormat() != 'html')
    {
      sfConfig::set('sf_web_debug', false);

      $format = $this->_request->getRequestFormat();
      $this->_request->setRequestFormat('html');
      $this->_actions->setLayout(false);

      if ($mimeType = $this->_request->getMimeType($format))
      {
        $this->_response->setContentType($mimeType);
      }
    }
  }

  /**
   * Loads the metadata from the content object
   *
   * @return void
   */
  protected function _loadMetaData()
  {
    // page title
    if ($pageTitle = $this->_content->getPageTitle())
    {
      $this->_response->setTitle($pageTitle);
    }
    else if ($pageTitle = $this->_content->getSite()->getPageTitle())
    {
      $this->_response->setTitle($pageTitle);
    }
    else if (sfSympalConfig::get('auto_seo', 'title'))
    {
      $this->_response->setTitle($this->_getAutoSeoTitle());
    }

    // meta keywords
    if ($metaKeywords = $this->_content->getMetaKeywords())
    {
      $this->_response->addMeta('keywords', $metaKeywords);
    }
    else if ($metaKeywords = $this->_content->getSite()->getMetaKeywords())
    {
      $this->_response->addMeta('keywords', $metaKeywords);
    }

    // meta description
    if ($metaDescription = $this->_content->getMetaDescription())
    {
      $this->_response->addMeta('description', $metaDescription);
    }
    else if ($metaDescription = $this->_content->getSite()->getMetaDescription())
    {
      $this->_response->addMeta('description', $metaDescription);
    }
  }

  /**
   * Attempts to generate a rich page title
   *
   * @TODO re-implement the hook this had with menu items
   *
   * @return string
   */
  protected function _getAutoSeoTitle()
  {
    $title = (string) $this->_content;

    $format = sfSympalConfig::get('auto_seo', 'title_format');
    $find = array(
      '%site_title%',
      '%content_title%',
      '%content_id%',
    );

    $replace = array(
      $this->_content->getSite()->getTitle(),
      (string) $this->_content,
      $this->_content->getId(),
    );
    $title = str_replace($find, $replace, $format);

    return $title;
  }

  /**
   * Handles the situation where a content record is unpublished
   *
   * @TODO re-implement the isEditMode() situation
   * @param  sfSympalContent $record The unpublished content record
   * @return void
   */
  protected function _handleIsPublished(sfSympalContent $record)
  {
    //if (!$record->getIsPublished() && !$this->_user->isEditMode())
    if (!$record->getIsPublished())
    {
      if (sfSympalConfig::get('unpublished_content', 'forward_404'))
      {
        $this->_actions->forward404('Content has not been published yet!');
      }
      else if ($forwardTo = sfSympalConfig::get('unpublished_content', 'forward_to'))
      {
        $this->_actions->forward($forwardTo[0], $forwardTo[1]);
      }
    }
  }

  /**
   * @return sfSympalPluginConfiguration
   */
  protected function _getSympalConfiguration()
  {
    return $this->_applicationConfiguration
      ->getPluginConfiguration('sfSympalPlugin');
  }

  /**
   * Handle the 404 for a content record
   *
   * @param  sfSympalContent|null $record
   * @return void
   */
  protected function _handleForward404(sfSympalContent $record = null)
  {
    if ($record)
    {
      return;
    }

    $siteManager = $this->_getSympalConfiguration()->getSiteManager();
    $site = $siteManager->getSite();

    // No site record exception
    if (!$site)
    {
      // create the site and then refresh
      Doctrine_Core::getTable('sfSympalSite')->fetchCurrent(true);
      
      $this->_actions->refresh();
      $this->_actions->redirect($this->_request->getUri());
    }
    else
    {
      // Check for no content and redirect to default new site page
      $q = Doctrine_Query::create()
        ->from('sfSympalContent c')
        ->andWhere('c.site_id = ?', $site->getId());
      $count = $q->count();
      if (!$count)
      {
        $this->_actions->forward('sympal_default', 'new_site');
      }

      $parameters = $this->_actions->getRoute()->getParameters();
      $msg = sprintf(
        'No %s record found that relates to sfSympalContent record id "%s"',
        $parameters['sympal_content_type'],
        $parameters['sympal_content_type_id']
      );
      $this->_actions->forward404($msg);
    }
  }
}