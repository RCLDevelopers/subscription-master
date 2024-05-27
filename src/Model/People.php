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

class People extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id',
        'campaign',
        'uid',
        'status',
        'time_join',
        'time_update',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'newsletter',
    );
}