<?php
/*
    This file is part of Symbiose Community Edition <https://github.com/yesbabylon/symbiose>
    Some Rights Reserved, Yesbabylon SRL, 2020-2024
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace sale\price;

use equal\orm\Model;

class Price extends Model {

    public static function getColumns() {
        return [

            'name' => [
                'type'              => 'computed',
                'function'          => 'calcName',
                'result_type'       => 'string',
                'store'             => true,
                'description'       => "The display name of the price."
            ],

            'price' => [
                'type'              => 'float',
                'usage'             => 'amount/money:4',
                'description'       => "Tax excluded price.",
                'required'          => true,
                'dependents'        => ['price_vat']
            ],

            'price_vat' => [
                'type'              => 'computed',
                'result_type'       => 'float',
                'function'          => 'calcPriceVat',
                'usage'             => 'amount/money:4',
                'description'       => "Tax included price. This field is used to allow encoding prices VAT incl.",
                'store'             => true,
                'onupdate'          => 'onupdatePriceVat'
            ],

            'type' => [
                'type'              => 'string',
                'selection'         => ['direct', 'computed'],
                'default'           => 'direct'
            ],

            'calculation_method_id' => [
                'type'              => 'string',
                'description'       => "Method to use for price computation.",
                'visible'           => ['type', '=', 'computed']
            ],

            'price_list_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'sale\price\PriceList',
                'description'       => "The Price List the price belongs to.",
                'required'          => true,
                'ondelete'          => 'cascade',
                'dependents'        => ['name']
            ],

            'is_active' => [
                'type'              => 'computed',
                'result_type'       => 'boolean',
                'function'          => 'calcIsActive',
                'store'             => true,
                'instant'           => true,
                'description'       => "Is the price currently applicable?"
            ],

            'accounting_rule_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'finance\accounting\AccountingRule',
                'description'       => "Selling accounting rule. If set, overrides the rule of the product this price is assigned to.",
                'dependents'        => ['vat_rate', 'price_vat']
            ],

            'product_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'sale\catalog\Product',
                'description'       => "The Product (sku) the price applies to.",
                'required'          => true,
                'dependents'        => ['name']
            ],

            'vat_rate' => [
                'type'              => 'computed',
                'result_type'       => 'float',
                'usage'             => 'amount/rate',
                'function'          => 'calcVatRate',
                'description'       => "VAT rate applied on the price (from accounting rule).",
                'store'             => true,
                'readonly'          => true
            ]

        ];
    }

    public static function calcName($self) {
        $result = [];
        $self->read(['product_id' => ['sku'], 'price_list_id' => ['name']]);
        foreach($self as $id => $price) {
            $result[$id] = "{$price['product_id']['sku']} - {$price['price_list_id']['name']}";
        }

        return $result;
    }

    public static function calcVatRate($self) {
        $result = [];
        $self->read(['accounting_rule_id' => ['vat_rule_id' => ['rate']]]);
        foreach($self as $id => $price) {
            $result[$id] = $price['accounting_rule_id']['vat_rule_id']['rate'];
        }

        return $result;
    }

    public static function calcPriceVat($self) {
        $result = [];
        $self->read(['price', 'vat_rate']);
        foreach($self as $id => $price) {
            $result[$id] = $price['price'] * (1.0 + $price['vat_rate']);
        }

        return $result;
    }

    public static function calcIsActive($self) {
        $result = [];
        $self->read(['price_list_id' => ['is_active']]);
        foreach($self as $id => $price) {
            $result[$id] = $price['price_list_id']['is_active'];
        }

        return $result;
    }

    /**
     * Update price, based on VAT incl. price and applied VAT rate
     */
    public static function onupdatePriceVat($self, $values) {
        $result = [];
        $self->read(['price_vat', 'vat_rate']);
        foreach($self as $id => $price) {
            Price::id($id)->update(['price' => $price['price_vat'] / (1.0 + $price['vat_rate'])]);
        }

        return $result;
    }

    public function getUnique() {
        return [
            ['product_id', 'price_list_id']
        ];
    }
}
