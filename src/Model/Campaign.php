<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @linkhttp://code.piengine.org for the Pi Engine source repository
 * @copyright Copyright (c) Pi Engine http://piengine.org
 * @licensehttp://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Subscription\Model;

use Pi\Application\Model\Model;

class Campaign extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id',
        'title',
        'slug',
        'text_description',
        'text_subscription',
        'text_email',
        'text_sms',
        'seo_title',
        'seo_keywords',
        'seo_description',
        'status',
        'time_create',
        'time_start',
        'time_end',
        'hits',
        'join',
        'image',
        'path',
        'subscription_type',
    );
}