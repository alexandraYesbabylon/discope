<?php
/*
    This file is part of the Discope property management software <https://github.com/discope-pms/discope>
    Some Rights Reserved, Discope PMS, 2020-2024
    Original author(s): Yesbabylon SRL
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace identity;

class Contact extends \identity\Partner {

    public static function getName() {
        return "Contact";
    }

    public static function getColumns() {

        return [

            'relationship' => [
                'type'              => 'string',
                'default'           => 'contact',
                'description'       => "The partnership should remain 'contact'."
            ],

            'type' => [
                'type'              => 'string',
                'selection'         => [
                    'booking',          // person that is in charge of handling the booking details
                    'invoice',          // person to who the invoice of the booking must be sent
                    'contract',         // person to who the contract(s) must be sent
                    'sojourn'           // person that will be present during the sojourn (beneficiary)
                ],
                'description'       => 'The kind of contact, based on its responsibilities.',
                'default'           => 'booking'
            ]

        ];
    }

}
