<?php

/**
 * Base actions for the sfTestPlugin sympal_dashboard module.
 * 
 * @package     sfTestPlugin
 * @subpackage  sympal_dashboard
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_dashboardActions extends sfActions
{
  public function preExecute()
  {
    parent::preExecute();

    $this->useAdminLayout();
  }

  public function executeIndex()
  {
    $this->upgrade = new sfSympalUpgradeFromWeb(
      $this->getContext()->getConfiguration(),
      $this->getContext()->getEventDispatcher(),
      new sfFormatter()
    );

    $this->hasNewVersion = $this->upgrade->hasNewVersion();

    $this->boxes = new sfSympalMenu('Dashboard Boxes');

    $this->boxes['Sites']
      ->setRoute('@sympal_sites')
      ->setCredentials(array('ManageSites'));

    $this->boxes['Manage Content']
      ->setRoute('@sympal_content')
      ->setCredentials(array('ManageContent'));

    $contentTypes = Doctrine_Core::getTable('ContentType')->findAll();

    foreach ($contentTypes as $contentType)
    {
      $this->boxes['Create '.$contentType['label']]
        ->setRoute('@sympal_content_create_type?type='.$contentType['slug'])
        ->setCredentials(array('ManageContent'))
        ->setLiClass('create_content');
    }

    $this->boxes['Menu Manager']
      ->setRoute('@sympal_menu_items')
      ->setCredentials(array('ManageMenus'));

    $this->boxes['Plugin Manager']
      ->setRoute('@sympal_plugin_manager')
      ->setCredentials(array('ManagePlugins'));

    $this->boxes['Content Types']
      ->setRoute('@sympal_content_types')
      ->setCredentials(array('ManageContentSetup'));

    $this->boxes['Content Templates']
      ->setRoute('@sympal_content_templates')
      ->setCredentials(array('ManageContentSetup'));

    $this->boxes['Slot Types']
      ->setRoute('@sympal_content_slot_types')
      ->setCredentials(array('ManageContentSetup'));

    $this->boxes['Users']
      ->setRoute('@sympal_users')
      ->setCredentials(array('ManageUsers'));

    $this->boxes['Groups']
      ->setRoute('@sympal_groups')
      ->setCredentials(array('ManageGroups'));

    $this->boxes['Permissions']
      ->setRoute('@sympal_permissions')
      ->setCredentials(array('ManagePermissions'));

    $this->boxes['Configuration']
      ->setRoute('@sympal_config')
      ->setCredentials(array('ManageConfiguration'));

    $this->boxes['Sitemap']
      ->setRoute('@sympal_sitemap')
      ->setCredentials(array('ManageMenus'));

    $this->getContext()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.load_dashboard_boxes', array('menu' => $this->boxes)));
  }
}