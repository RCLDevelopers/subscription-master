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
namespace Module\Subscription\Block;

use Pi;
use Module\Subscription\Form\SubscriptionForm;

class Block
{
    public static function subscription($options = array(), $module = null)
    {
        // Set options
        $block = array();
        $block = array_merge($block, $options);

        // Load language
        Pi::service('i18n')->load(array('module/subscription', 'default'));

        // Get config
        $block['config'] = Pi::service('registry')->config->read($module);

        $uid = Pi::user()->getId();
        if ($uid > 0) {
            $user = Pi::api('user', 'subscription')->getUserInformation($uid);
        }

        // Set subscription
        $subscription = array();
        if (isset($user['first_name']) && !empty($user['first_name'])) {
            $subscription['first_name'] = $user['first_name'];
        }
        if (isset($user['last_name']) && !empty($user['last_name'])) {
            $subscription['last_name'] = $user['last_name'];
        }
        if (isset($user['email']) && !empty($user['email'])) {
            $subscription['email'] = $user['email'];
        }
        if (isset($user['mobile']) && !empty($user['mobile'])) {
            $subscription['mobile'] = $user['mobile'];
        }

        // Set option
        $option = array(
            'type' => 'email',
            'uid' => $uid,
            'config' => array(
                'subscription_name' => $block['subscription_name'],
                'subscription_mobile' => $block['subscription_mobile'],
            ),
        );

        // Set form
        $form = new SubscriptionForm('subscription', $option);
        $form->setAttribute('enctype', 'multipart/form-data');
        $form->setAttribute('action', Pi::url(Pi::service('url')->assemble('default', array(
            'module' => $module,
        ))));
        $form->setData($subscription);
        $block['form'] = $form;

        return $block;
    }
}