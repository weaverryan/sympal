<?php

/**
 * Doctrine template for all models in sympal to act as. By implementing
 * this template, you're allowing your model to be more flexible.
 * 
 * This template accomplishes several things:
 * 
 *   1) Adds I18N to tables "internationalized_models" config. The i18n
 *      filter class is configurable in app.yml (i18n_filter_class)
 * 
 *   2) Adds sfSympalRecordEventFilter, which throws symfony an event
 *      (sympal.model_name.method_not_found) on each set and get that
 *      is not recognized.
 *
 *   3) Throw a sympal.table_name.set_table_definition event so that
 *      table definition can be modified at run-time.
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalRecordTemplate extends Doctrine_Template
{
  /**
   * @var string $eventName The name of the event generated from the invoker class name
   */
  protected $_eventName;

  /**
   * @var string $modelName The fixed name of the model for this template instances invoker
   */
  protected $_modelName;

  /**
   * Override the setInvoker() method to allow us to generate the event name
   *
   * @param Doctrine_Record_Abstract $invoker 
   * @return void
   */
  public function setInvoker(Doctrine_Record_Abstract $invoker)
  {
    parent::setInvoker($invoker);
    $this->_eventName = sfInflector::tableize(get_class($invoker));
  }

  /**
   * Hook into the models setTableDefinition() process and handle some additional
   * setup with our models
   *
   * @return void
   */
  public function setTableDefinition()
  {
    // Add global Doctrine record filter which implements Symfony filter events for unknown property calls
    $this->_table->unshiftFilter(new sfSympalRecordEventFilter());

    // Attach i18n behavior if is i18ned
    if ($this->isI18ned())
    {
      $this->sympalActAsI18n(array('fields' => $this->getI18nedFields()), 'Doctrine_Template_I18n');
    }

    // Invoke Symfony event to hook into every models setTableDefinition() process
    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this->getInvoker(), 'sympal.'.$this->_eventName.'.set_table_definition', array('object' => $this)));
  }

  /**
   * Attach a Sympal template to this models
   *
   * @param mixed $tpl The template to act as
   * @param string $options The array of options for the template
   * @param string $name The name of the template
   * @return void
   */
  public function sympalActAs($tpl, $options = array(), $name = null)
  {
    if (is_string($tpl))
    {
      $tpl = new $tpl($options);
    }

    if (is_null($name))
    {
      $name = get_class($tpl);
    }

    $this->_table->addTemplate($name, $tpl);

    $tpl->setInvoker($this->getInvoker());
    $tpl->setTable($this->_table);
    $tpl->setUp();
    $tpl->setTableDefinition();
  }

  /**
   * Make this model act as i18n.
   *
   * Basically acts lke the normal i18n functionality except that the
   * filter class is controlled via app.yml
   *
   * @param array $options Array of options for the i18n behavior
   * @param string $name The name of the i18n behavior
   * @return void
   */
  public function sympalActAsI18n($options = array(), $name = null)
  {
    $this->sympalActAs('Doctrine_Template_I18n', $options, $name);

    if (!$this->_table->getOption('has_symfony_i18n_filter'))
    {
      $filterClass = sfSympalConfig::get('i18n_filter_class', null, 'sfSympalDoctrineRecordI18nFilter');

      $this->_table
        ->unshiftFilter(new $filterClass())
        ->setOption('has_symfony_i18n_filter', true)
      ;
    }
  }

  /**
   * Notify a Symfony event to allow us to hook into any models setUp() process
   *
   * @return void
   */
  public function setUp()
  {
    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(
      new sfEvent(
        $this->getInvoker(),
        'sympal.'.$this->_eventName.'.setup',
        array('object' => $this)
      )
    );
  }

  /**
   * Check if this model has i18n enabled
   *
   * @return boolean $result Whether or not i18n is enabled for this model
   */
  public function isI18ned()
  {
    return sfSympalConfig::isI18nEnabled($this->getModelName());
  }

  /**
   * Get the array of fields that are i18ned for this model
   *
   * @return array $fields
   */
  public function getI18nedFields()
  {
    if ($this->isI18ned())
    {
      return sfSympalConfig::get('internationalized_models', $this->getModelName(), array());
    }
    else
    {
      return array();
    }
  }

  /**
   * Hack for working around the ToPrfx and FromPrfx prefix used by migrations
   *
   * @return string $modelName
   */
  public function getModelName()
  {
    if (!$this->_modelName)
    {
      $this->_modelName = str_replace('ToPrfx', '', $this->_table->getOption('name'));
      $this->_modelName = str_replace('FromPrfx' ,'', $this->_modelName);
    }

    return $this->_modelName;
  }
}