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
namespace Module\Subscription\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/*
 * Pi::api('campaign', 'subscription')->getCampaign($parameter, $type);
 * Pi::api('campaign', 'subscription')->canonizeCampaign($campaign);
 */

class Campaign extends AbstractApi
{
    public function getCampaign($parameter, $type = 'id')
    {
        // Get campaign
        $campaign = Pi::model('campaign', $this->getModule())->find($parameter, $type);
        $campaign = $this->canonizeCampaign($campaign);
        return $campaign;
    }

    public function canonizeCampaign($campaign)
    {
        // Check
        if (empty($campaign)) {
            return '';
        }
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // boject to array
        $campaign = $campaign->toArray();
        // Set text_description
        $campaign['text_description'] = Pi::service('markup')->render($campaign['text_description'], 'html', 'html');
        // Set text_subscription
        $campaign['text_subscription'] = Pi::service('markup')->render($campaign['text_subscription'], 'html', 'html');
        // Set text_email
        $campaign['text_email'] = Pi::service('markup')->render($campaign['text_email'], 'html', 'html');
        // Set times
        $campaign['time_start_view'] = _date($campaign['time_start']);
        $campaign['time_end_view'] = _date($campaign['time_end']);
        // Set campaign url
        $campaign['campaignUrl'] = Pi::url(Pi::service('url')->assemble('default', array(
            'module' => $this->getModule(),
            'controller' => 'index',
            'slug' => $campaign['slug'],
        )));
        // Set image url
        if ($campaign['image']) {
            // Set image original url
            $campaign['originalUrl'] = Pi::url(
                sprintf('upload/%s/original/%s/%s',
                    $config['image_path'],
                    $campaign['path'],
                    $campaign['image']
                ));
            // Set image large url
            $campaign['largeUrl'] = Pi::url(
                sprintf('upload/%s/large/%s/%s',
                    $config['image_path'],
                    $campaign['path'],
                    $campaign['image']
                ));
            // Set image medium url
            $campaign['mediumUrl'] = Pi::url(
                sprintf('upload/%s/medium/%s/%s',
                    $config['image_path'],
                    $campaign['path'],
                    $campaign['image']
                ));
            // Set image thumb url
            $campaign['thumbUrl'] = Pi::url(
                sprintf('upload/%s/thumb/%s/%s',
                    $config['image_path'],
                    $campaign['path'],
                    $campaign['image']
                ));
        }
        // return campaign
        return $campaign;
    }
}