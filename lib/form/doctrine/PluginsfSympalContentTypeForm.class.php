<?php

/**
 * PluginContentType form.
 *
 * @package    form
 * @subpackage sfSympalContentType
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginsfSympalContentTypeForm extends BasesfSympalContentTypeForm
{
  public function setup()
  {
    parent::setup();
    
/*  @TODO replace this with something not invasive
    $field = sfApplicationConfiguration::getActive()
      ->getPluginConfiguration('sfThemePlugin')
      ->getThemeToolkit()
      ->getThemeWidgetAndValidator();
    $this->widgetSchema['theme'] = $field['widget'];
    $this->validatorSchema['theme'] = $field['validator'];*/

    $this->widgetSchema['name']->setLabel('Model name');

    // Sets up the template widget
    sfSympalFormToolkit::changeTemplateWidget($this);

    // Sets up the module widget
    sfSympalFormToolkit::changeModuleWidget($this);

    $models = array_keys(sfSympalConfig('content_templates'));
    foreach ($models as $model)
    {
      $table = Doctrine_Core::getTable($model);
      if (!$table->hasTemplate('sfSympalContentTypeTemplate'))
      {
        unset($models[$model]);
      }
    }

    $models = array_merge(array('' => ''), $models);
    $this->widgetSchema['name'] = new sfWidgetFormChoice(array('choices' => $models));
  }
}