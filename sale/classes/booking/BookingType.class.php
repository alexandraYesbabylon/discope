<?php
/*
    This file is part of the Discope property management software <https://github.com/discope-pms/discope>
    Some Rights Reserved, Discope PMS, 2020-2024
    Original author(s): Yesbabylon SRL
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace sale\booking;
use equal\orm\Model;

class BookingType extends Model {

    public static function getName() {
        return "Booking type";
    }

    public static function getDescription() {
        return "Booking types are used to associate a reason to a given booking (ex. 'individual', 'group', ...) for statistics purpose.";
    }


    public static function getColumns() {
        /**
         */

        return [
            'name' => [
                'type'              => 'string',
                'description'       => 'Short name of the booking type.',
                'required'          => true
            ],

            'code' => [
                'type'              => 'string',
                'description'       => 'Mnemo for the type.'
            ],

            'description' => [
                'type'              => 'string',
                'description'       => 'Description of the intended use of the type.'
            ],

/*
            'stat_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'identity\Partner',
                'domain'            => ['type', '=', 'customer'],
                'description'       => "The customer to whom the booking relates to.",
                'required'          => true
            ],
*/

            'product_categories_ids' => [
                'type'              => 'many2many',
                'foreign_object'    => 'sale\catalog\Category',
                'foreign_field'     => 'booking_types_ids',
                'rel_table'         => 'sale_rel_productcategory_bookingtype',
                'rel_foreign_key'   => 'productcategory_id',
                'rel_local_key'     => 'bookingtype_id',
                'description'       => "Categories of products that the type relates to."
            ],

            'days_expiry_option' =>  [
                'description'   => 'The number of days for the option to expire.',
                'type'          => 'integer'
            ],

            'booking_schedule_layout' => [
                'type'              => 'string',
                'selection'         => [
                    'simple',
                    'detailed'
                ],
                'description'       => 'Set the configuration for consumption printing.',
                'default'           => 'simple'
            ]

        ];
    }

}
