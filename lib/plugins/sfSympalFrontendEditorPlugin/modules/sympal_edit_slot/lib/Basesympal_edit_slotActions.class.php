<?php

/**
 * Base actions for the sfSympalPlugin sympal_edit_slot module.
 * 
 * @package     sfSympalPlugin
 * @subpackage  sympal_edit_slot
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_edit_slotActions extends sfActions
{
  public function executeChange_content_slot_type(sfWebRequest $request)
  {
    $this->contentSlot = $this->_getContentSlot($request);
    $this->contentSlot->content_slot_type_id = $request->getParameter('type');
    $this->contentSlot->save();

    $this->form = $this->_getContentSlotForm($request);

    $this->setLayout(false);
    $this->setTemplate('edit_slot');
  }

  protected function _getContentSlot(sfWebRequest $request)
  {
    $this->contentSlot = $this->getRoute()->getObject();
    $this->content = Doctrine_Core::getTable('sfSympalContent')->find($request->getParameter('content_id'));
    $this->contentSlot->setContentRenderedFor($this->content);

    return $this->contentSlot;
  }

  protected function _getContentSlotForm(sfWebRequest $request)
  {
    $this->contentSlot = $this->_getContentSlot($request);

    $this->form = $this->contentSlot->getEditForm();

    return $this->form;
  }

  public function executeEdit_slot(sfWebRequest $request)
  {
    $this->setLayout(false);

    $this->contentSlot = $this->_getContentSlot($request);
    $this->form = $this->_getContentSlotForm($request);
  }

  public function executeSave_slot(sfWebrequest $request)
  {
    $this->form = $this->_getContentSlotForm($request);

    $this->form->bind($request->getParameter($this->form->getName()));
    if ($this->form->isValid())
    {
      $this->form->save();
    }

    $this->setLayout(false);
    $this->setTemplate('preview_slot');
  }

  public function executePreview_slot(sfWebRequest $request)
  {
    $this->setLayout(false);

    $this->form = $this->_getContentSlotForm($request);
    $this->contentSlot->resetRenderCache();

    $this->form->bind($request->getParameter($this->form->getName()));
    if ($this->form->isValid())
    {
      $this->form->updateObject();

      $this->setTemplate('preview_slot');
    } else {
      exit((string) $this->form);
      $this->setTemplate('edit_slot');
    }
  }
}
