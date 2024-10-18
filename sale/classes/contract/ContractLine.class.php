<?php
/*
    This file is part of Symbiose Community Edition <https://github.com/yesbabylon/symbiose>
    Some Rights Reserved, Yesbabylon SRL, 2020-2024
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace sale\contract;

use equal\orm\Model;

class ContractLine extends Model {

    public static function getName() {
        return "Contract line";
    }

    public static function getColumns() {

        return [
            'name' => [
                'type'              => 'computed',
                'function'          => 'calcName',
                'result_type'       => 'string',
                'store'             => true,
                'description'       => "The display name of the line."
            ],

            'description' => [
                'type'              => 'string',
                'description'       => "Complementary description of the line. If set, replaces the product name.",
                'default'           => ''
            ],

            'contract_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'sale\contract\Contract',
                'description'       => "The contract the line relates to.",
            ],

            'contract_line_group_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'sale\contract\ContractLineGroup',
                'description'       => "The contract the line relates to.",
            ],

            'product_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'sale\catalog\Product',
                'description'       => "The product (SKU) the line relates to.",
                'required'          => true,
                'dependents'         => ['total', 'price']
            ],

            'price_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'sale\price\Price',
                'description'       => "The price the line relates to, if any.",
                'dependents'         => ['total', 'price']
            ],

            'unit_price' => [
                'type'              => 'float',
                'usage'             => 'amount/money:4',
                'description'       => "Tax-excluded price of the product related to the line.",
                'required'          => true
            ],

            'vat_rate' => [
                'type'              => 'float',
                'description'       => "VAT rate to be applied.",
                'required'          => true
            ],

            'qty' => [
                'type'              => 'float',
                'description'       => "Quantity of product.",
                'required'          => true
            ],

            'free_qty' => [
                'type'              => 'integer',
                'description'       => "Free quantity.",
                'default'           => 0
            ],

            'discount' => [
                'type'              => 'float',
                'usage'             => 'amount/rate',
                'description'       => "Total amount of discount to apply, if any.",
                'default'           => 0.0
            ],

            'total' => [
                'type'              => 'computed',
                'result_type'       => 'float',
                'usage'             => 'amount/money:4',
                'description'       => "Total tax-excluded price of the line.",
                'function'          => 'calcTotal',
                'store'             => true
            ],

            'price' => [
                'type'              => 'computed',
                'result_type'       => 'float',
                'usage'             => 'amount/money:2',
                'description'       => "Final tax-included price of the line.",
                'function'          => 'calcPrice',
                'store'             => true
            ]

        ];
    }

    public static function calcName($self) {
        $result = [];
        $self->read(['product_id' => ['label']]);
        foreach($self as $id => $line) {
            $result[$id] = $line['product_id']['label'];
        }
        return $result;
    }

    public static function calcTotal($self) {
        $result = [];
        $self->read(['unit_price', 'qty', 'free_qty', 'discount']);
        foreach($self as $id => $line) {
            $result[$id] = $line['unit_price'] * (1 - $line['discount']) * ($line['qty'] - $line['free_qty']);
        }

        return $result;
    }

    public static function calcPrice($self) {
        $result = [];
        $self->read(['total', 'vat_rate']);
        foreach($self as $id => $line) {
            $result[$id] = round($line['total'] * (1 + $line['vat_rate']), 2);
        }

        return $result;
    }

    public static function canupdate($self, $values) {
        $allowed_fields = ['total', 'price'];
        foreach($values as $field => $value) {
            if(!in_array($field, $allowed_fields)) {
                return ['contract_id' => ['not_allowed' => 'Contract cannot be manually updated.']];
            }
        }
    }

}