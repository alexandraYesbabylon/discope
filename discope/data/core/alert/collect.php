<?php
/*
    This file is part of the Discope property management software.
    Author: Yesbabylon SRL, 2020-2024
    License: GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\orm\Domain;

[$params, $providers] = eQual::announce([
    'description'   => "Advanced search for Messages: returns a collection of Message according to extra parameters.",
    'extends'       => 'core_model_collect',
    'params'        => [
        'entity' =>  [
            'type'              => 'string',
            'description'       => "Full name (including namespace) of the class to look into (e.g. \'core\\User\').",
            'default'           => 'discope\core\alert\Message'
        ],
        'center_office_id' => [
            'type'              => 'many2one',
            'foreign_object'    => 'identity\CenterOffice',
            'description'       => "Office the message relates to (for targeting the users)."
        ],
        'booking_id' => [
            'type'              => 'many2one',
            'foreign_object'    => 'lodging\sale\booking\Booking',
            'description'       => "Booking the invoice relates to."
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$domain = $params['domain'];

if(isset($params['center_office_id']) && $params['center_office_id'] > 0) {
    $domain = Domain::conditionAdd($domain, ['center_office_id', '=', $params['center_office_id']]);
}

if(isset($params['booking_id']) && $params['booking_id'] > 0) {
    $domain = Domain::conditionAdd($domain, ['object_id', '=', $params['booking_id']]);
}

$params['domain'] = $domain;

$result = eQual::run('get', 'model_collect', $params, true);

$context->httpResponse()
        ->body($result)
        ->send();
