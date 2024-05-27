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
namespace Module\Subscription\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Subscription\Form\SubscriptionForm;
use Module\Subscription\Form\SubscriptionFilter;

class IndexController extends ActionController
{
    public function indexAction()
    {
        // Get info from url
        $module = $this->params('module');
        $slug = $this->params('slug');
        // Get config
        $config = Pi::service('registry')->config->read($module);
        if (!$config['subscription_enabled']) {
            $this->getResponse()->setStatusCode(404);
            $this->view()->setLayout('layout-simple');
        }
        // Get campaign
        if (isset($slug) && !empty($slug)) {
            $campaign = Pi::api('campaign', 'subscription')->getCampaign($slug, 'slug');
            if (!empty($campaign)) {
                if ($campaign['status'] != 1 || $campaign['time_start'] > time() || $campaign['time_end'] < time()) {
                    $this->getResponse()->setStatusCode(404);
                    $this->terminate(__('The campaign not found.'), '', 'error-404');
                    $this->view()->setLayout('layout-simple');
                    return;
                }
            } else {
                $this->getResponse()->setStatusCode(404);
                $this->terminate(__('The campaign not found.'), '', 'error-404');
                $this->view()->setLayout('layout-simple');
                return;
            }
        } else {
            $campaign = array();
            $campaign['id'] = 0;
            $campaign['title'] = $config['default_title'];
            $campaign['text_description'] = $config['default_description'];
            $campaign['text_subscription'] = $config['default_subscription'];
            $campaign['text_email'] = $config['default_email'];
            $campaign['text_sms'] = $config['default_sms'];
            $campaign['subscription_type'] = $config['default_subscription_type'];
        }
        // Get user information
        $uid = Pi::user()->getId();
        if ($uid > 0) {
            $user = Pi::api('user', 'subscription')->getUserInformation($uid);
            // Set view
            $this->view()->assign('user', $user);
        }
        // Set option
        $option = array(
            'type' => $campaign['subscription_type'],
            'uid' => $uid,
            'config' => $config,
        );
        // Set form
        $form = new SubscriptionForm('subscription', $option);
        $form->setAttribute('enctype', 'multipart/form-data');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new SubscriptionFilter($option));
            $form->setData($data);
            if ($form->isValid()) {
                // Set values
                $values = $form->getData();
                $values['campaign'] = $campaign['id'];
                $values['uid'] = $uid;
                $values['status'] = 1;
                $values['newsletter'] = 1;
                $people = Pi::api('people', 'subscription')->createPeople($values);   
                
                $people = $people->toArray();
                // Send notification
                Pi::api('notification', 'subscription')->joinUser($people, $campaign);
                // Set ID
                $subscriptionId = uniqid('subscription');
                // Set session
                $_SESSION[$subscriptionId] = array(
                    'campaign' => $campaign['id'],
                    'people' => $people['id'],
                );
                // Jump
                $this->jump(array(
                    'action' => 'finish',
                    'id' => $subscriptionId,
                ), __('Your subscription data saved successfully.'));
            }
        } else {
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
            $form->setData($subscription);
        }
        // Set view
        if (isset($campaign['seo_title'])) {
            $this->view()->headTitle($campaign['seo_title']);
        }
        if (isset($campaign['seo_description'])) {
            $this->view()->headDescription($campaign['seo_description'], 'set');
        }
        if (isset($campaign['seo_keywords'])) {
            $this->view()->headKeywords($campaign['seo_keywords'], 'set');
        }
        $this->view()->setTemplate('subscription');
        $this->view()->assign('config', $config);
        $this->view()->assign('campaign', $campaign);
        $this->view()->assign('form', $form);
    }

    public function finishAction()
    {
        // Get info from url
        $module = $this->params('module');
        $id = $this->params('id');
        // Get config
        $config = Pi::service('registry')->config->read($module);
        // Check id
        if (isset($_SESSION[$id]) && !empty($_SESSION[$id])) {
            $information = $_SESSION[$id];
            unset($_SESSION[$id]);
        } else {
            $this->getResponse()->setStatusCode(403);
            $this->terminate(__('Nothing Set'), '', 'error');
            $this->view()->setLayout('layout-simple');
            return;
        }
        // Find campin and people
        $people = $this->getModel('people')->find($information['people']);
        if ($information['campaign'] > 0) {
            $campaign = Pi::api('campaign', 'subscription')->getCampaign($information['campaign']);
        } else {
            $campaign = array();
            $campaign['id'] = 0;
            $campaign['title'] = $config['default_title'];
            $campaign['text_description'] = $config['default_description'];
            $campaign['text_subscription'] = $config['default_subscription'];
            $campaign['text_email'] = $config['default_email'];
            $campaign['text_sms'] = $config['default_sms'];
            $campaign['subscription_type'] = $config['default_subscription_type'];
        }
        // Set view
        if (isset($campaign['seo_title'])) {
            $this->view()->headTitle($campaign['seo_title']);
        }
        if (isset($campaign['seo_description'])) {
            $this->view()->headDescription($campaign['seo_description'], 'set');
        }
        if (isset($campaign['seo_keywords'])) {
            $this->view()->headKeywords($campaign['seo_keywords'], 'set');
        }
        $this->view()->setTemplate('finish');
        $this->view()->assign('config', $config);
        $this->view()->assign('people', $people);
        $this->view()->assign('campaign', $campaign);
    }
}