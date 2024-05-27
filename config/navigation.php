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
return array(
    'front' => array(),
    'admin' => array(
        'people' => array(
            'label' => _a('People'),
            'permission' => array(
                'resource' => 'people',
            ),
            'route' => 'admin',
            'module' => 'subscription',
            'controller' => 'people',
            'action' => 'index',

            'pages' => array(
                'people' => array(
                    'label' => _a('People'),
                    'permission' => array(
                        'resource' => 'people',
                    ),
                    'route' => 'admin',
                    'module' => 'subscription',
                    'controller' => 'people',
                    'action' => 'index',
                ),
                'export' => array(
                    'label' => _a('Export csv'),
                    'permission' => array(
                        'resource' => 'people',
                    ),
                    'route' => 'admin',
                    'module' => 'subscription',
                    'controller' => 'people',
                    'action' => 'export',
                ),
            ),
        ),

        'campaign' => array(
            'label' => _a('Campaign'),
            'permission' => array(
                'resource' => 'campaign',
            ),
            'route' => 'admin',
            'module' => 'subscription',
            'controller' => 'campaign',
            'action' => 'index',

            'pages' => array(
                'campaign' => array(
                    'label' => _a('Campaign'),
                    'permission' => array(
                        'resource' => 'campaign',
                    ),
                    'route' => 'admin',
                    'module' => 'subscription',
                    'controller' => 'campaign',
                    'action' => 'index',
                ),
                'update' => array(
                    'label' => _a('New campaign'),
                    'permission' => array(
                        'resource' => 'campaign',
                    ),
                    'route' => 'admin',
                    'module' => 'subscription',
                    'controller' => 'campaign',
                    'action' => 'update',
                ),
            ),
        ),
    ),
);