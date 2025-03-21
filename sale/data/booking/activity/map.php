<?php
/*
    This file is part of the Discope property management software <https://github.com/discope-pms/discope>
    Some Rights Reserved, Discope PMS, 2020-2024
    Original author(s): Yesbabylon SRL
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\orm\Domain;
use hr\employee\Employee;
use sale\booking\Booking;
use sale\booking\BookingActivity;
use sale\catalog\ProductModel;

[$params, $providers] = eQual::announce([
    'description'   => "Retrieve the consumptions assigned to specified employees and return an associative array mapping employees and ate indexes with related activities (this controller is used for the planning).",
    'params'        => [
        'employees_ids' =>  [
            'description'   => 'Identifiers of the employees for which the activities are requested.',
            'type'          => 'array',
            'required'      => true
        ],
        'date_from' => [
            'description'   => 'Start of time-range for the lookup.',
            'type'          => 'date',
            'required'      => true
        ],
        'date_to' => [
            'description'   => 'End of time-range for the lookup.',
            'type'          => 'date',
            'required'      => true
        ]
    ],
    'access' => [
        'groups'            => ['booking.default.user', 'booking.infra.user']
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm', 'auth', 'adapt']
]);

/**
 * @var \equal\php\Context                   $context
 * @var \equal\orm\ObjectManager             $orm
 * @var \equal\auth\AuthenticationManager    $auth
 */
['context' => $context, 'orm' => $orm, 'auth' => $auth, 'adapt' => $dap] = $providers;

// #memo - processing of this controller might be heavy, so we make sure AC does not check permissions for each single consumption
$auth->su();


$domain = [
    [
        ['employee_id', 'in', $params['employees_ids']],
        ['activity_date', '>=', $params['date_from']],
        ['activity_date', '<=', $params['date_to']]
    ],
    [
        ['employee_id', 'is', null],
        ['activity_date', '>=', $params['date_from']],
        ['activity_date', '<=', $params['date_to']]
    ]
];

$activities_ids = $orm->search(BookingActivity::getType(), $domain);

// #memo - we use the ORM to prevent recursion and bypass permission check
$activities = $orm->read(BookingActivity::getType(), $activities_ids, [
        'id',
        'name',
        'employee_id',
        'activity_date',
        'time_slot_id',
        'booking_id',
        'product_model_id',
        'activity_booking_line_id'
    ]);

// read additional fields for the view
$map_bookings = [];
$map_employees = [];
$map_product_models = [];

// retrieve all foreign objects identifiers
foreach($activities as $id => $activity) {
    $map_bookings[$activity['booking_id']] = true;
    $map_employees[$activity['employee_id']] = true;
    $map_product_models[$activity['product_model_id']] = true;
}

// load all foreign objects at once
$product_models = $orm->read(ProductModel::getType(), array_keys($map_product_models), ['id', 'name', 'description']);
$bookings = $orm->read(Booking::getType(), array_keys($map_bookings), ['id', 'name', 'description', 'status', 'payment_status']);
$employees = $orm->read(Employee::getType(), array_keys($map_employees), ['id', 'name']);

$result = [];
// build result: enrich and adapt consumptions
foreach($activities as $id => $activity) {

    // #memo - we use employee_id 0 for unassigned activities
    $employee_id = intval($activity['employee_id']);
    $date_index = date('Y-m-d', $activity['activity_date']);
    $time_slot = [1 => 'AM', 3 => 'PM', 6 => 'EV'][$activity['time_slot_id']];

    if(!isset($result[$employee_id])) {
        $result[$employee_id] = [];
    }

    if(!isset($result[$employee_id][$date_index])) {
        $result[$employee_id][$date_index] = [];
    }

    if(!isset($result[$employee_id][$date_index][$time_slot])) {
        $result[$employee_id][$date_index][$time_slot] = [];
    }

    $result[$employee_id][$date_index][$time_slot][] = array_merge($activity->toArray(), [
            'booking_id'        => ($activity['booking_id']) ? $bookings[$activity['booking_id']]->toArray() : null,
            'employee_id'       => ($activity['employee_id']) ? $employees[$activity['employee_id']]->toArray() : 0,
            'product_model_id'  => ($activity['product_model_id']) ? $product_models[$activity['product_model_id']]->toArray() : null,
            'activity_date'     => date('c', $activity['activity_date']),
            'time_slot'         => $time_slot
        ]);
}

$context->httpResponse()
        ->body($result)
        ->send();
