<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginsfSympalContentType extends BasesfSympalContentType
{
  /**
   * Returns the sfSympalContentTypeObject that "owns" this content type
   * for the current configuration.
   *
   * This is really a bit of a cheat as it references the application via
   * the static context. Just realize that the type object is relative to
   * the current configuration.
   *
   * @return sfSympalContentTypeObject
   */
  public function getTypeObject()
  {
    return sfApplicationConfiguration::getActive()
      ->getPluginConfiguration('sfSympalPlugin')
      ->getContentTypeObject($this->type_key);
  }
}