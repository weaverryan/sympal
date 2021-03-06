<?php

/**
 * Doctrine template for Sympal content type models to act as.
 * Automatically adds a content_id column and creates a one-to-one relationship
 * with sfSympalContent.
 *
 * Example: If you had a sfSympalBlogPost content type you would have sfSympalContent
 * hasOne sfSympalBlogPost and sfSympalBlogPost hasOne sfSympalContent
 *
 * @package sfSympalCMFPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalContentTypeTemplate extends sfSympalRecordTemplate
{
  protected $_options = array(
  );

  /**
   * Hook into the content type models setTableDefinition() process and add 
   * a content_id column
   *
   * @return void
   */
  public function setTableDefinition()
  {
    parent::setTableDefinition();

    $this->hasColumn('content_id', 'integer');

    $this->addListener(new sfSympalContentTypeListener($this->_options));
  }

  /**
   * Hook into the content type models setTableDefinition() process and add the
   * relationships between sfSympalContent and the sfSympalContentTypeNameModel
   *
   * @return void
   */
  public function setUp()
  {
    parent::setUp();

    $this->hasOne('sfSympalContent as Content', array(
      'local' => 'content_id',
      'foreign' => 'id',
      'onDelete' => 'CASCADE'
    ));

    $contentTable = Doctrine_Core::getTable('sfSympalContent');
    $class = $this->getInvoker()->getTable()->getOption('name');
    $contentTable->bind(array($class, array('local' => 'id', 'foreign' => 'content_id')), Doctrine_Relation::ONE);
  }

 /**
   * Returns the base query for retrieving sfSympalContent records.
   *
   * @TODO reimplement many of the joines in plugins
   *
   * @param string $alias The alias used for the sfSympalContent model
   * @return Doctrine_Query
   */
  public function getBaseContentQueryTableProxy($alias = 'cr')
  {
    $tbl = $this->getInvoker()->getTable();
    
    $q = $tbl->createQuery($alias)
      ->innerJoin($alias.'.Content c')
      ->leftJoin('c.CreatedBy u')
      ->innerJoin('c.Type t')
      // Don't use param to work around Doctrine pgsql bug
      // with limit subquery and number of params
      ->innerJoin(sprintf("c.Site si WITH si.slug = '%s'", sfSympalConfig::getCurrentSiteName()));

    if (sfSympalConfig::isI18nEnabled($tbl->getOption('name')))
    {
      $q->leftJoin($alias.'.Translation crt');
    }

    if (sfSympalConfig::isI18nEnabled('sfSympalContent'))
    {
      $q->leftJoin('c.Translation ct');
    }

    // throw an event that plugins can hook into the modify the query
    if (sfApplicationConfiguration::hasActive())
    {
      sfApplicationConfiguration::getActive()
        ->getEventDispatcher()
        ->notify(new sfEvent($this, 'sympal.content.get_base_content_query', array(
          'query' => $q,
      )));
    }

    // allow for a hook on the invoker table to add to the query
    if (method_exists($tbl, 'appendBaseContentQuery'))
    {
      $q = $tbl->appendBaseContentQuery($q);
    }

    return $q;
  }

  /**
   * Method called by the routing to retrieve a the invoker record
   * 
   * @param array $params The params passed in from the route
   * @return Doctrine_Record
   */
  public function fetchFromRoutingTableProxy(array $params)
  {
    $tbl = $this->getInvoker()->getTable();
    $contentTbl = $tbl->getRelation('Content')->getTable();

    // special parameter used for exact routes
    // we have to get the request because of how symfony filters out params not part of url variables (e.g. :slug)
    $request = sfContext::getInstance()->getRequest();
    $contentId = $request->getParameter('content_id');


    $q = $this->getInvoker()->getTable()->getBaseContentQuery();

    // If we have an explicit content id
    if ($contentId)
    {
      $q->andWhere('c.id = ?', $contentId);
    }
    else
    {
      // Loop over all other request parameters and see if they can be used to add a where condition
      // to find the content record
      $paramFound = false;
      foreach ($params as $key => $value)
      {
        if ($contentTbl->hasField($key))
        {
          // found on the Content record
          $paramFound = true;
          $q->andWhere('c.'.$key.' = ?', $value);
        }
        else if ($contentTbl->hasRelation('Translation') && $contentTbl->getRelation('Translation')->getTable()->hasField($key))
        {
          // found on the Content->Translation record
          $paramFound = true;
          $q->andWhere('ct.'.$key, $value);
        }
        if ($tbl->hasField($key))
        {
          // found on the invoker record
          $paramFound = true;
          $q->andWhere('cr.'.$key.' = ?', $value);
        }
        else if ($tbl->hasRelation('Translation') && $tbl->getRelation('Translation')->getTable()->hasField($key))
        {
          // found on the invoker translation record
          $paramFound = true;
          $q->andWhere('crt.'.$key, $value);
        }
      }

      // prevents any record being matched if no good params were found.
      if (!$paramFound)
      {
        return null;
      }
    }

    /*
     * If this sfSympalContent record has more than one content type (e.g. sfSympalPage)
     * record and I18N is enabled, a very deep hydration error will be
     * thrown. This is placed here to help the developer track down the cause.
     */
    try
    {
      return $q->fetchOne();
    }
    catch (Doctrine_Hydrator_Exception $e)
    {
      throw new sfException(sprintf(
        'Hydration Error. Check that there is only one %s record related to this sfSympalContent record. Raw error: "%s"',
        get_class($this->getInvoker()),
        $e->getMessage())
      );
    }
  }
}
