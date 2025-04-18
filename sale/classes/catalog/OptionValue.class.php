<?php
/*
    This file is part of the Discope property management software <https://github.com/discope-pms/discope>
    Some Rights Reserved, Discope PMS, 2020-2024
    Original author(s): Yesbabylon SRL
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace sale\catalog;
use equal\orm\Model;

class OptionValue extends Model {
    public static function getColumns() {
        /**
         * OptionValue objects are the possible values to which an option, for a given Product Attriubute, can be set to.
         */
        return [
            'name' => [
                'type'              => 'alias',
                'alias'             => 'description'
            ],

            'value' => [
                'type'              => 'string',
                'description'       => "The choice (possible value) for the related option."
            ],

            'description' => [
                'type'              => 'string',
                'description'       => "Short description of the value.",
                'multilang'         => true
            ],

            'option_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'sale\catalog\Option',
                'description'       => "Product Option this value relates to.",
                'required'          => true
            ]
        ];
    }
}