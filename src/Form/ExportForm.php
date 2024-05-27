<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Subscription\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class ExportForm extends BaseForm
{
    public function __construct($name = null, $option = array())
    {
        $this->option = $option;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new ExportFilter($this->option);
        }
        return $this->filter;
    }

    public function init()
    {
        // include_user
        $this->add(array(
            'name' => 'include_user',
            'type' => 'checkbox',
            'options' => array(
                'label' => __('Include website user'),
            ),
            'attributes' => array(
                'description' => __('You can make CSV export from just people on subscription list on include website users end of the list'),
            )
        ));
        // Save
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Export'),
            )
        ));
    }
}