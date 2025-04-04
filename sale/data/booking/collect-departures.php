<?php
/*
    This file is part of the Discope property management software <https://github.com/discope-pms/discope>
    Some Rights Reserved, Discope PMS, 2020-2024
    Original author(s): Yesbabylon SRL
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\orm\Domain;
use identity\User;

list($params, $providers) = eQual::announce([
    'description'   => 'Advanced search for Bookings: returns a collection of Booking according to extra parameters.',
    'extends'       => 'core_model_collect',
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to look into (e.g. \'core\\User\').',
            'type'          => 'string',
            'default'       => 'sale\booking\Booking'
        ],
        'date_from' => [
            'type'          => 'date',
            'description'   => "First date of the time interval.",
            'default'       => strtotime('today midnight')
        ],
        'date_to' => [
            'type'          => 'date',
            'description'   => "Last date of the time interval.",
            'default'       => strtotime('today midnight')
        ],
        'center_id' => [
            'type'              => 'many2one',
            'foreign_object'    => 'identity\Center',
            'description'       => "The center to which the booking relates to."
        ]

    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => [ 'context', 'orm', 'auth' ]
]);

/**
 * @var \equal\php\Context $context
 * @var \equal\orm\ObjectManager $orm
 * @var \equal\auth\AuthenticationManager $auth
 */
list($context, $orm, $auth) = [ $providers['context'], $providers['orm'], $providers['auth'] ];


/*
    Add conditions to the domain to consider advanced parameters
*/

$domain = $params['domain'];
$bookings_ids = [];

$domain = Domain::conditionAdd($domain, ['date_to', '>=', $params['date_from']]);
$domain = Domain::conditionAdd($domain, ['date_to', '<=', $params['date_to']]);

if(isset($params['center_id']) && $params['center_id'] > 0) {
    // add constraint on center_id
    $domain = Domain::conditionAdd($domain, ['center_id', '=', $params['center_id']]);
}
else {
    // if no center is provided, fallback to current users'
    $user = User::id($auth->userId())->read(['centers_ids'])->first(true);
    if(count($user['centers_ids']) == 1) {
        $domain = Domain::conditionAdd($domain, ['center_id', '=', reset($user['centers_ids'])]);
    }
    else {
        $domain = Domain::conditionAdd($domain, ['center_id', '=', 0]);
    }
}

$params['domain'] = $domain;

$result = eQual::run('get', 'model_collect', $params, true);

$context->httpResponse()
        ->body($result)
        ->send();
