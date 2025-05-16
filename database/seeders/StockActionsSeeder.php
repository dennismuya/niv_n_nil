<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockActionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        DB::table('stock_actions')->insert([

            ['action' => 'add_stock'],
            ['action' => 'delete'],
            ['action' => 'return_supplied'],
            ['action' => 'returned_from_debt'],
            ['action' => 'transfer'],
            ['action' => 'sale'],
            ['action' => 'sale_replacement'],
            ['action' => 'update_stock'],
            ['action' => 'transfer_stock'],
            ['action' => 'receive_stock'],
            ['action' => 'give_debtor']

            ,
        ]);
    }
}
