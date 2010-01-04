<?php

/**
 * Get a Sympal Content instance property
 *
 * @param Content $content 
 * @param string $name 
 * @return mixed $value
 */
function get_sympal_content_property($content, $name)
{
  return $content->$name;
}

function render_content_author(sfSympalContent $content, $slot)
{
  return $content->CreatedBy->username;
}

function render_content_date_published(sfSympalContent $content, $slot)
{
  use_helper('Date');
  return format_datetime($content->date_published);
}

/**
 * Get Sympal content slot value
 *
 * @param Content $content  The Content instance
 * @param string $name The name of the slot
 * @param string $type The type of slot
 * @param string $renderFunction The function to use to render the value
 * @return void
 */
function get_sympal_content_slot($content, $name, $type = 'Text', $renderFunction = null)
{
  $isColumn = false;
  if ($content->hasField($name))
  {
    $isColumn = true;
  }

  if ($isColumn && is_null($renderFunction))
  {
    $renderFunction = 'get_sympal_content_property';
  }

  $slots = $content->getSlots();

  if ($name instanceof sfSympalContentSlot)
  {
    $slot = $name;
  } else {
    $slot = $content->getOrCreateSlot($name, $type, $isColumn, $renderFunction);
  }

  $user = sfContext::getInstance()->getUser();
  if ($user->isEditMode())
  {
    use_helper('SympalContentSlotEditor');

    return get_sympal_content_slot_editor($slot);
  } else {
    return $slot->render();
  }
}