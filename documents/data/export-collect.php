<?php
/*
    This file is part of the Discope property management software.
    Author: Yesbabylon SRL, 2020-2024
    License: GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\Domain;

[$params, $providers] = eQual::announce([
    'description'   => "Advanced search for Reports: returns a collection of Reports according to extra parameters.",
    'extends'       => 'core_model_collect',
    'params'        => [
        'entity' =>  [
            'type'              => 'string',
            'description'       => "Full name (including namespace) of the class to look into (e.g. \'core\\User\').",
            'default'           => 'documents\Export'
        ],
        'name' => [
            'type'              => 'string',
            'description'       => "Name of the export."
        ],
        'date_from' => [
            'type'              => 'date',
            'description'       => "First date of the time interval.",
            'default'           => null
        ],
        'date_to' => [
            'type'              => 'date',
            'description'       => "Last date of the time interval.",
            'default'           => time()
        ],
        'center_office_id' => [
            'type'              => 'many2one',
            'foreign_object'    => 'lodging\identity\CenterOffice',
            'description'       => "Office the invoice relates to (for center management)."
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

if(isset($params['name']) && strlen($params['name']) > 0 ) {
    $domain = Domain::conditionAdd($domain, ['name', 'like','%'.$params['name'].'%']);
}

if(isset($params['center_office_id']) && $params['center_office_id'] > 0) {
    $domain = Domain::conditionAdd($domain, ['center_office_id', '=', $params['center_office_id']]);
}

if(isset($params['date_from']) && $params['date_from'] > 0) {
    $domain = Domain::conditionAdd($domain, ['created', '>=', $params['date_from']]);
}

if(isset($params['date_to']) && $params['date_to'] > 0) {
    $domain = Domain::conditionAdd($domain, ['created', '<=', $params['date_to']]);
}


$params['domain'] = $domain;

$result = eQual::run('get', 'model_collect', $params, true);

$context->httpResponse()
        ->body($result)
        ->send();
