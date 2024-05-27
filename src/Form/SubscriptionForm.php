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

class SubscriptionForm extends BaseForm
{
    public function __construct($name = null, $option = array())
    {
        $this->option = $option;
        parent::__construct($name);
    }

    public function init()
    {
        // first_name
        if ($this->option['config']['subscription_name']) {
            $this->add(array(
                'name' => 'first_name',
                'options' => array(
                    'label' => __('First name'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                    'placeholder' => __('First name'),
                )
            ));
            // last_name
            $this->add(array(
                'name' => 'last_name',
                'options' => array(
                    'label' => __('Last name'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                    'placeholder' => __('Last name'),
                )
            ));
        } else {
            $this->add(array(
                'name' => 'first_name',
                'attributes' => array(
                    'type' => 'hidden',
                ),
            ));
            $this->add(array(
                'name' => 'last_name',
                'attributes' => array(
                    'type' => 'hidden',
                ),
            ));
        }
        // email
        $this->add(array(
            'name' => 'email',
            'options' => array(
                'label' => __('Email'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',
                'required' => (in_array($this->option['type'], array('both', 'email'))) ? true : false,
                'placeholder' => __('Email'),
            )
        ));
        // mobile
        if ($this->option['config']['subscription_mobile']) {
            $this->add(array(
                'name' => 'mobile',
                'options' => array(
                    'label' => __('Mobile'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => (in_array($this->option['type'], array('both', 'mobile'))) ? true : false,
                    'placeholder' => __('Mobile'),
                )
            ));
        }
        
        if ($captchaElement = Pi::service('form')->getReCaptcha(2)) {
            $this->add($captchaElement);
        }
        $this->add([
            'name' => 'security',
            'type' => 'csrf',
        ]);
        
        // Save
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Subscription'),
            )
        ));
    }
}
