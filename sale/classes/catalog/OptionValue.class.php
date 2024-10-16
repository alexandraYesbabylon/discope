<?php
/*
    This file is part of Symbiose Community Edition <https://github.com/yesbabylon/symbiose>
    Some Rights Reserved, Yesbabylon SRL, 2020-2024
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace sale\catalog;

use equal\orm\Model;

class OptionValue extends Model {

    public static function getDescription() {
        return "The possible values to which an option, for a given Product Attribute, can be set to.";
    }

    public static function getColumns() {
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
