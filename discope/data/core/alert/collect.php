<?php
/*
    This file is part of the Discope property management software.
    Author: Yesbabylon SRL, 2020-2022
    License: GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\orm\Domain;

list($params, $providers) = eQual::announce([
    'description'   => 'Advanced search for Messages: returns a collection of Message according to extra parameters.',
    'extends'       => 'core_model_collect',
    'params'        => [
        'entity' =>  [
            'description'       => 'Full name (including namespace) of the class to look into (e.g. \'core\\User\').',
            'type'              => 'string',
            'default'           => 'discope\core\alert\Message'
        ],
        'center_office_id' => [
            'type'              => 'many2one',
            'foreign_object'    => 'identity\CenterOffice',
            'description'       => 'Office the message relates to (for targeting the users).'
        ],
        'booking_id' => [
            'type'              => 'many2one',
            'foreign_object'    => 'sale\booking\Booking',
            'description'       => 'Booking the invoice relates to.'
        ],
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => [ 'context', 'orm' ]
]);

/**
 * @var \equal\php\Context $context
 * @var \equal\orm\ObjectManager $orm
 */
list($context, $orm) = [ $providers['context'], $providers['orm'] ];

$domain = $params['domain'];

if(isset($params['center_office_id']) && $params['center_office_id'] > 0) {
    // #memo - center_office_id does not exist on Message and group_id is used instead
    $domain = Domain::conditionAdd($domain, ['group_id', '=', $params['center_office_id']]);
}

if(isset($params['booking_id']) && $params['booking_id'] > 0) {
    $domain = Domain::conditionAdd($domain, ['object_id', '=', $params['booking_id']]);
}

$params['domain'] = $domain;

$result = eQual::run('get', 'model_collect', $params, true);

$context->httpResponse()
        ->body($result)
        ->send();
