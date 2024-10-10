<?php
/*
    This file is part of the Discope property management software.
    Author: Yesbabylon SRL, 2020-2024
    License: GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace finance\accounting;
use equal\orm\Model;

class AccountingRuleLine extends Model {

    public static function getName() {
        return "Accounting Rule Line";
    }

    public static function getDescription() {
        return "Accounting rules have one or more lines associating them with an account and a VAT rule.";
    }

    public static function getColumns() {

        return [
            'name' => [
                'type'              => 'string',
                'description'       => "Short string to serve as memo.",
                'required'          => true
            ],

            'account' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => "Code of the related account.",
                'function'          => 'calcAccount',
                'store'             => true
            ],

            'account_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'finance\accounting\AccountChartLine',
                'description'       => "Code of the related account.",
            ],

            'share' => [
                'type'              => 'float',
                'usage'             => 'amount/percent',
                'description'       => "Share of the line, in percent (lines sum must be 100%).",
                'default'           => 1.0
            ],

            'accounting_rule_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'finance\accounting\AccountingRule',
                'description'       => "Parent accounting rule this line is related to."
            ]

        ];
    }


    public static function calcAccount($self) {
        $result = [];
        $self->read(['account_id' => ['code']]);
        foreach($self as $id => $line) {
            $result[$id] = $line['code'];
        }
        return $result;
    }


}