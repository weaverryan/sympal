<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginsfSympalContentType extends BasesfSympalContentType
{
  protected $_routeObject;

  public function __toString()
  {
    return (string) $this->getLabel();
  }

  public function getRouteName()
  {
    return '@'.str_replace('-', '_', $this->getSlug());
  }

  public function getRoutePath()
  {
    $path = $this->default_path;
    if ($path != '/')
    {
      $path .= '.:sf_format';
    }
    return $path;
  }

  public function getRouteObject()
  {
    if (!$this->_routeObject)
    {
      $this->_routeObject = new sfRoute($this->getRoutePath(), array(
        'sf_format' => 'html',
        'sf_culture' => sfConfig::get('default_culture')
      ));
    }
    return $this->_routeObject;
  }

  public function getModuleToRenderWith()
  {
    if ($moduleName = $this->_get('module'))
    {
      return $moduleName;
    }
    else
    {
      return sfSympalConfig::get($this->getName(), 'default_rendering_module', sfSympalConfig::get('default_rendering_module', null, 'sympal_content_renderer'));
    }
  }

  public function getActionToRenderWith()
  {
    if ($actionName = $this->_get('action'))
    {
      return $actionName;
    }
    else
    {
      return sfSympalConfig::get($this->getName(), 'default_rendering_action', sfSympalConfig::get('default_rendering_action', null, 'index'));
    }
  }
}