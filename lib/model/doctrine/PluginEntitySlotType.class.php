<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginEntitySlotType extends BaseEntitySlotType
{
  public function setName($name)
  {
    if ($name)
    {
      $result = Doctrine_Query::create()
        ->from('EntitySlotType t')
        ->where('t.name = ?', $name)
        ->fetchArray();

      if ($result)
      {
        $this->assignIdentifier($result[0]['id']);
        $this->hydrate($result[0]);
      } else {
        $this->_set('name', $name);
      }
    }
  }
}