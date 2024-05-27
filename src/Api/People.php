<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Mickaël STAMM <contact@sta2m.com>
 */
namespace Module\Subscription\Api;

use Pi;
use Pi\Application\Api\AbstractApi;


class People extends AbstractApi
{
    public function createPeople($values = array() )
    {
        $peopleModel = Pi::model('people', 'subscription');
        $people      = $peopleModel->createRow();

        $values['time_join']  = time();
        $values['time_update']  = time();

        $people->assign($values);
        $people->save();
        
        return $people;
    }

    public function update($data, $uid)
    {
        $data['time_update']  = time();
        Pi::model("people", 'subscription')->update($data, array('uid' => $uid));
    }

    public function getCurrentPeople()
    {
        $select      = Pi::model('people', 'subscription')->select();
        $select->where(
            [
                'uid'      => Pi::user()->getId(),
                'campaign' => 0,
            ]
        );

        $people = Pi::model('people', 'subscription')->selectWith($select)->current();

        return $people;
    }
}