<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PluginsfSympalContentTable extends Doctrine_Table
{
  
  /**
   * Adds the necessary where clause to only return published content
   * 
   * @param string          $alias  The alias to use to refer to sfSympalContent
   * @param Doctrine_Query  $q      An optional query to add to
   */
  public function addPublishedQuery($alias = 'c', Doctrine_Query $q = null)
  {
    if ($q === null)
    {
      $q = $this->createQuery($alias);
    }
    
    $expr = new Doctrine_Expression('NOW()');
    $q->andWhere($alias.'.date_published <= '.$expr);
    
    return $q;
  }

  /**
   * Called by sfInlineObjectDoctrineResource to create a query that
   * will return a collection of objects given the array of keys and
   * key column
   *
   * @see sfInlineObjectDoctrineResource
   */
  public function getQueryForInlineObjects($keys, $keyColumn)
  {
    $q = $this->createQuery('c')
      ->whereIn('c.'.$keyColumn, $keys)
      ->orderBy('c.'.$keyColumn.' ASC')
      ->select('c.*, t.*')
      ->from('sfSympalContent c')
      ->innerJoin('c.Type t')
      ->innerJoin('c.Site s')
      ->andWhere('s.slug = ?', sfSympalConfig::getCurrentSiteName());

    if (sfSympalConfig::isI18nEnabled('sfSympalContent'))
    {
      $q->leftJoin('c.Translation ct');
    }

    return $q;
  }

  /**
   * Used by the admin generator.
   *
   * @TODO move this into the admin plugin (somehow) and test
   *
   * @param  $q
   * @return Doctrine_Query
   */
  public function getAdminGenQuery($q)
  {
    $q = Doctrine_Core::getTable('sfSympalContent')
      ->getFullTypeQuery(sfContext::getInstance()->getUser()->getAttribute('content_type_id'), 'r');

    return $q;
  }
}
