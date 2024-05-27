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
use Laminas\InputFilter\InputFilter;

class ExportFilter extends InputFilter
{
    public function __construct($option)
    {
        // include_user
        $this->add(array(
            'name' => 'include_user',
            'required' => false,
        ));
    }
}