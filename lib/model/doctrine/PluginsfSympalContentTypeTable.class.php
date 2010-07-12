<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PluginsfSympalContentTypeTable extends Doctrine_Table
{

  /**
   * Creates a new content type with some sensible defaults.
   *
   * @param  string $key   The key identifier to give the type
   * @param  array  $data  Any other dat that should be merged in
   * @return sfSympalContentType
   */
  public function createType($key, $data = array())
  {
    $class = $this->getOption('name');
    $type = new $class();
    $type['key'] = $key;

    // guess at a default_path. This will be overwritten immediately if
    // a default_path key were passed in the $data argument
    $typeModel = sfSympalConfig::getContentTypeModelFromKey($key);
    if (Doctrine_Core::getTable($typeModel)->hasField('slug'))
    {
      $type['default_path'] = '/'.$type['key'].'/:slug';
    }
    else
    {
      $type['default_path'] = '/'.$type['key'].'/:id';
    }

    $type->fromArray($data);

    return $type;
  }

  /**
   * Retrieves the content type or creates it if it doesn't exist.
   *
   * @param  string $key The key identifier to give the type
   * @param  array $data  Any other dat that should be merged in
   * @return void
   */
  public function getOrCreateType($key, $data = array())
  {
    $type = $this->createQuery('ct')
      ->where('ct.key = ?', $key)
      ->limit(1)
      ->fetchOne();

    if (!$type)
    {
      $type = $this->createType($key, $data);
    }

    return $type;
  }
}