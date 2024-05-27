<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Subscription\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/*
 * Pi::api('notification', 'subscription')->joinUser($people, $campaign);
 */

class Notification extends AbstractApi
{
    public function joinUser($people, $campaign)
    {
        // Check notification module
        if (!Pi::service('module')->isActive('notification')) {
            return false;
        }

        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        // Get admin main
        $siteName = Pi::config('sitename');
        $adminMail = Pi::config('adminmail');
        $adminName = Pi::config('adminname');
        
        // Set module name
        $module = Pi::service('module')->current();
        
        // Set uid
        $uid = ($people['uid'] > 0) ? $people['uid'] : '';

        // Set mail information
        $information = array(
            'first_name' => $people['first_name'],
            'last_name' => $people['last_name'],
            'title' => $campaign['title'],
            'extra' => $campaign['text_main'],
            'time' => _date($people['time_join']),
            'site_name' => $siteName,
        );

        // Send mail to admin
        if ($config['notification_admin_email']) {
            $toAdmin = array(
                $adminMail => $adminName,
            );
            Pi::api('mail', 'notification')->send(
                $toAdmin,
                'subscription_admin',
                $information,
                $module
            );
        }

        // Send sms to admin
        if ($config['notification_admin_sms']) {
            $content = sprintf(
                $config['sms_subscription_admin'],
                $siteName,
                $people['first_name'],
                $people['last_name'],
                $campaign['title']
            );
            Pi::api('sms', 'notification')->sendToAdmin($content);
        }

        // Send mail to user
        if (isset($people['email']) && !empty($people['email'])) {
            $toUser = array(
                $people['email'] => sprintf('%s %s', $people['first_name'], $people['last_name']),
            );
            Pi::api('mail', 'notification')->send(
                $toUser,
                'subscription_user',
                $information,
                $module,
                $uid
            );
        }

        // Send sms to user
        if (isset($people['mobile']) && !empty($people['mobile'])) {
            $content = sprintf(
                $config['sms_subscription_user'],
                $people['first_name'],
                $people['last_name'],
                $campaign['text_sms']
            );
            Pi::api('sms', 'notification')->send($content, $people['mobile']);
        }
    }
}