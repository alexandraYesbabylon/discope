<?php
/*
    This file is part of the Discope property management software <https://github.com/discope-pms/discope>
    Some Rights Reserved, Discope PMS, 2020-2024
    Original author(s): Yesbabylon SRL
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'	=>	"Runs a checks update on a selection of Booking.",
    'params' 		=>	[
        'ids' => [
            'description'       => 'List of Booking identifiers the check against emptiness.',
            'type'              => 'array'
        ]
    ],
    'access' => [
        'visibility'        => 'protected',
        'groups'            => ['booking.default.user'],
    ],
    'response' => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers' => ['context', 'dispatch']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\dispatch\Dispatcher          $dispatch
 */
list($context, $dispatch) = [ $providers['context'], $providers['dispatch']];

foreach($params['ids'] as $id) {
    eQual::run('do', 'sale_booking_do-update-check', ['id' => $id]);
}

$context->httpResponse()
        ->status(204)
        ->send();
