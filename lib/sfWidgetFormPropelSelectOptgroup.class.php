<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormPropelSelectOptgroup represents a select HTML tag for a model.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Marc Busqué <marc@lamarciana.com>
 */
class sfWidgetFormPropelSelectOptgroup extends sfWidgetFormPropelSelect
{
  /**
   * @see sfWidget
   */
  public function __construct($options = array(), $attributes = array())
  {
    $options['choices'] = new sfCallable(array($this, 'getChoices'));

    parent::__construct($options, $attributes);
  }

  /**
   * Constructor.
   *
   * Available options:
   *
   *  * optgroup_column:    The column to group the results within optgroup HTML elements, in the PhpName format (required)
   *
   * @see sfWidgetFormPropelSelect
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('optgroup_column');
    $this->addOption('optgroup_method', null);

    parent::configure($options, $attributes);
  }

  /**
   * Returns the choices associated to the model grouped by optcolumn column values
   *
   * @return array An array of choices
   */
  public function getChoices()
  {
    $choices = array();
    if (false !== $this->getOption('add_empty'))
    {
      $choices[''] = true === $this->getOption('add_empty') ? '' : $this->getOption('add_empty');
    }

    $class = constant($this->getOption('model').'::PEER');

    $criteria = is_null($this->getOption('criteria')) ? new Criteria() : clone $this->getOption('criteria');

    if ($order = $this->getOption('order_by'))
    {
      $method = sprintf('add%sOrderByColumn', 0 === strpos(strtoupper($order[1]), 'ASC') ? 'Ascending' : 'Descending');
      $criteria->$method(call_user_func(array($class, 'translateFieldName'), $order[0], BasePeer::TYPE_PHPNAME, BasePeer::TYPE_COLNAME));
    }
    $objects = call_user_func(array($class, $this->getOption('peer_method')), $criteria, $this->getOption('connection'));

    $methodKey = $this->getOption('key_method');
    if (!method_exists($this->getOption('model'), $methodKey))
    {
      throw new RuntimeException(sprintf('Class "%s" must implement a "%s" method to be rendered in a "%s" widget', $this->getOption('model'), $methodKey, __CLASS__));
    }

    $methodValue = $this->getOption('method');
    if (!method_exists($this->getOption('model'), $methodValue))
    {
      throw new RuntimeException(sprintf('Class "%s" must implement a "%s" method to be rendered in a "%s" widget', $this->getOption('model'), $methodValue, __CLASS__));
    }

    if (!$this->getOption('optgroup_method')) {
       $methodOptgroup = 'get'.$this->getOption('optgroup_column');
    } else {
       $methodOptgroup = $this->getOption('optgroup_method');
    }
    //$methodOptgroup = 'get'.$this->getOption('optgroup_column');
    if (!method_exists($this->getOption('model'), $methodOptgroup))
    {
      throw new RuntimeException(sprintf('Class "%s" must implement a "%s" method to be rendered in a "%s" widget', $this->getOption('model'), $methodOptgroup, __CLASS__));
    }

    foreach ($objects as $object)
    {
       $choices[$object->$methodOptgroup()][$object->$methodKey()] = $object->$methodValue();
    }

    return $choices;
  }
}
