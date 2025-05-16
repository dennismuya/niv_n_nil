<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DailySummary;
use App\Models\Debt\DebtHistory;
use App\Models\InvoicePayment;
use App\Models\NewStock;
use App\Models\Sale;
use App\Models\SaleDelivery;
use App\Models\Sales\NewSale;
use App\Models\Sales\NewSaleStock;
use App\Models\Slip;
use App\Models\Stock\StockHistory;
use App\Models\Stock\SupplierHistory;
use App\Models\Stock\TransferHistory;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPUnit\Event\Code\Throwable;
use stdClass;

class MyBaseController extends Controller
{

//    analytics

    // store opening balance

    public function get_opening_balance($store, $date)
    {
        return DB::table('daily_summaries')->where('store', $store)
            ->whereDate('date', '<', $date)
            ->orderBy('date', 'desc')
            ->first('closing_balance')->closing_balance;
    }


    public function get_last_day_analytics($store, $date)

    {
        return DB::table('daily_summaries')
            ->where('store', $store)
            ->whereDate('date', '!=', $date)
            ->orderBy('date', 'desc')
            ->first();
    }

    public function get_previous_day_analytics($store, $date)

    {
        $date_ = new DateTime($date);
        $date_->modify("-1 day");
        $date_->format("Y-m-d");

        return DB::table('daily_summaries')
            ->where('store', $store)
            ->whereDate('date', '=', $date_)
            ->first();
    }

    public function get_day_analytics_by_date($store, $date)
    {
        return DB::table('daily_summaries')
            ->where('store', $store)
            ->whereDate('date', '=', $date)
            ->first();
    }


//    get recent summary id

    public function get_recent_summary_id($store)
    {
        return DB::table('daily_summaries')->where('store', $store)
            ->orderBy('date', 'desc')
            ->first('id')->id;
    }


    public function update_analytics_debt_history(
        $store = null, $sales_debt = 0, $debt_recovered_mpesa = 0, $debt_recovered_cash = 0,
        $debt_recovered_total = 0
    )
    {
        DB::table('daily_summaries')
            ->where('store', $store)
            ->whereDate('date', Carbon::parse(Carbon::now())->toDateString())
            ->incrementEach([
                    'debt_recovered_cash' => $debt_recovered_cash,
                    'debt_recovered_mpesa' => $debt_recovered_mpesa,
                    'debt_recovered_total' => $debt_recovered_total,
                    'sales_debt' => $sales_debt,
                    'closing_balance' => $debt_recovered_cash,
                ]
            );

    }


//    get all stock
    public function get_stock($column)
    {
        return DB::table('new_stocks')
            ->select(DB::raw($column . '  as stock_quantity, id, stock_name,stock_properties,selling_price'))
            ->where('deleted', false)
            ->orderBy('stock_name')->get();
    }


//    add new stock

    public function add__new__stock($column, $stock)
    {
        return NewStock::create([
            'stock_name' => $stock->stock_name,
            'stock_properties' => $stock->properties,
            'selling_price' => $stock->price,
            $column => $stock->quantity
        ]);
    }


    //    get single stock

    public function get_single_stock($column = null, $stock_id = null)
    {
        return DB::table('new_stocks')->where('deleted', false)->where('id', $stock_id)
            ->select(DB::raw($column . '  as stock_quantity, id, stock_name,stock_properties,selling_price'))
            ->first();
    }

    /**
     * Generate Receipt Number
     * @returns integer
     * */


    public function receipt_number()
    {
        $letters = range('A', 'Z');
        $day = Carbon::now()->dayOfWeek;
        $month = Carbon::now()->month;
        $now = Carbon::now();
        $hour = Carbon::now()->minute;
        $end_of_year = Carbon::now()->endOfYear();
        $diff_days = $now->diffInDays($end_of_year, false);


        $record_id = Sale::latest()->get('id')->first();
        $num = 1;
        $rad = mt_rand(300, 2205);
        $rad_ = mt_rand(13, 14132);


        $receipt_num = '#' . $letters[$day] . $letters[$month] . $rad_ . $rad;

        return $receipt_num;

    }

    public function create_invoice_lot_number()
    {
        $letters = range('A', 'Z');
        $day = Carbon::now()->dayOfWeek;
        $month = Carbon::now()->month;
        $now = Carbon::now();
        $hour = Carbon::now()->minute;
        $end_of_year = Carbon::now()->endOfYear();
        $diff_days = $now->diffInDays($end_of_year, false);


        $rad = mt_rand(0, 12205);
        $rad_ = mt_rand(1300, 14132);


        $receipt_num = '#inv' . $letters[$day] . $letters[$month] . $rad_ . $rad;

        return $receipt_num;

    }


    /**
     * Generate Deliverly Number
     * @returns integer
     * */

    public function delivery_number()
    {
        $letters = range('A', 'Z');
        $day = Carbon::now()->dayOfWeek;
        $now = Carbon::now();
        $hour = Carbon::now()->hour;
        $end_of_year = Carbon::now()->endOfYear();
        $diff_days = $now->diffInDays($end_of_year, false);


        $deliv = mt_rand(1, 10132);


        $delivery_number = '#' . 'd' . $diff_days . $letters[$hour] . $deliv + 1;

        return $delivery_number;


    }


//    add supply

    public function add_supply(
        $stock,
        $quantity,
        $supplier,
        $buying_price,
        $supply_date,
        $serial_number,

    )
    {
        $done = SupplierHistory::create([
            'stock'=>$stock,
            'quantity'=>$quantity,
            'supplier'=>$supplier,
            'buying_price'=>$buying_price,
            'total_price'=>$quantity*$buying_price,
            'supply_date'=>$supply_date,
            'received_by'=>Auth::id(),
            'serial_number'=>$serial_number,
        ]);


      return $done;
    }


    /**
     *   * add stock
     * @returns  Collection
     */

    public function add_stock(
        $store = null, $stock_id = null, $column = null, $quantity = null, $supplier = null,
        $buying_price = null, $serial_number = null
    )
    {
        $history = new stdClass();

        $history->stock = null;
        $history->stock_action = null;
        $history->buying_price = null;
        $history->selling_price = null;
        $history->quantity = null;
        $history->supplier = null;
        $history->action_date = null;
        $history->user = null;
        $history->store = null;
        $history->previous_stock = null;
        $history->stock_after = null;
        $history->reason = null;
        $history->serial_number = null;
        $history->serial_array = null;
        $history->returned_from = null;
        $history->replaced = null;
        $history->sale_replaced = null;


        $stock_ = $this->get_single_stock($column, $stock_id);
        $history->stock = $stock_id;
        $history->action = 1;
        $history->quantity = $quantity;
        $history->selling_price = null;
        $history->supplier = $supplier;
        $history->action_date = Carbon::now();
        $history->user = Auth::id();
        $history->store = $store;
        $history->buying_price = $buying_price;
        $history->previous_stock = $stock_->stock_quantity;
        $history->serial_number = $serial_number;
        $history->stock_after = $stock_->stock_quantity + $quantity;

        DB::table('new_stocks')->where('id', $stock_id)
            ->increment($column, $quantity);

        $this->make_stock_history($history);

        return NewStock::find($stock_id);

    }


    /**
     *   * update stock
     * @returns  Collection
     */

    public function update_stock($stock_id = null, $column = null, $quantity = null)
    {

        return DB::table('new_stocks')
            ->where('id', $stock_id)
            ->update([$column => $quantity]);
    }

    /**
     *   * deduct stock
     * @returns  Collection
     */

    public function deduct_stock($stock_id, $column = null, $quantity = null)
    {
        return DB::table('new_stocks')->where('id', $stock_id)
            ->decrement($column, $quantity);
    }

    /**
     *   * increase stock
     * @returns  Collection
     */

    public function increase_stock($stock_id, $column = null, $quantity = null)
    {
        return DB::table('new_stocks')->where('id', $stock_id)
            ->increment($column, $quantity);
    }

    /**
     * Delete stock
     * */

    public function delete_stock($stock_id)
    {
        $stock = \App\Models\Stock\NewStock::find($stock_id);
        $stock->deleted = true;
        $stock->save();

    }

    /**
     * Make stock History
     * */

    public function make_stock_history($history)
    {

        $done = StockHistory::create([
            'stock' => $history->stock,
            'stock_action' => $history->action,
            'buying_price' => $history->buying_price,
            'selling_price' => $history->selling_price ? $history->selling_price : null,
            'quantity' => $history->quantity,
            'supplier' => $history->supplier ?: null,
            'action_date' => Carbon::now(),
            'user' => Auth::id(),
            'store' => $history->store,
            'previous_stock' => $history->previous_stock,
            'stock_after' => $history->stock_after,
//            'reason' => $history->reason ?: null,
            'serial_number' => $history->serial_number ?: null,
            'serial_array' => $history->serial_array ?: null,
            'returned_from' => $history->returned_from ?: null,
            'replaced' => $history->replaced ?: null,
            'sale_replaced' => $history->sale_replaced ?: null,

        ]);

        return $done;


    }


    /**
     * Daily Summaries
     * */


//    update bank deposits


//    get sales

//by hrs
    public function get_new_sales_by_hours($hrs_from_now, $store)
    {
        return Sale::with('new_sale_stocks')->where('store', $store)->whereDate('date', Carbon::today())
            ->where
            ('time', '>', Carbon::now()
                ->subHours
                ($hrs_from_now)->toTimeString())->orderBy('id', 'desc')->get();

    }

//    today

    public function get_new_sales_today($store)
    {
        return Sale::with('new_sale_stocks')->where('store', $store)->whereDate('date', Carbon::today())
            ->orderBy('date')->orderBy('id', 'desc')->get();

    }

//    this_week

    public function get_new_sales_this_week($store)
    {
        return Sale::with('new_sale_stocks')
            ->where('store', $store)
            ->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->orderBy('date')->orderBy('date')->get();

    }

//     month
    public function get_new_sales_month($year, $month, $store)
    {
        return Sale::with('new_sale_stocks')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('store', $store)
            ->orderBy('date')->orderBy('date')->get();
    }

//    specific date

    public function get_sales_specific_date($year, $month, $day, $store)
    {
        return Sale::with('new_sale_stocks')
            ->whereYear('date', $year)
            ->where('store', $store)
            ->whereMonth('date', $month)
            ->whereDay('date', $day)
            ->orderBy('date')->orderBy('date')->get();
    }



    /**
     * DEBT HISTORY
     *
     * */

//    customers with invoices

    public function get_customers_with_invoices($store)
    {
        $customers = Customer::where('store', $store)
            ->whereHas('debt_history')
            ->get();

        foreach ($customers as $customer)
        {


            $payments = InvoicePayment::where('customer', $customer->id)->sum('total_payment');
            $slip = Slip::where('customer', $customer->id)->sum('slip_total');
            $debt_total = DebtHistory::where('customer', $customer->id)->sum('total_amount');


        

            $NUM =  (int)$debt_total - ((int)$payments + $slip);
            $customer->debt_balance = number_format($NUM);
            
        }



    
        
        return $customers;

    }

//    customer debt summary

    public function customer_debt_summary($customer)
    {
        return Customer::where('id', $customer)->withSum('payments', 'total_payment')
            ->withSum('debt', 'total_amount')->first();
    }


//    get debt summary

    public function get_debt_history_total_debt($store = null)
    {
        return DB::table('debt_histories')->where('store', $store)->sum('total_amount');
    }

//    invoice payments

    public function get_store_debt_payments($store = null)
    {
        return DB::table('invoice_payments')->where('store', $store)->sum('total_payment');
    }

//    customer debt payments
    public function get_customer_all_debt_payments($customer = null)
    {
        return DB::table('invoice_payments')->where('customer', $customer)->get();
    }


//    customer invoices all

    public function get_customer_all_debts($customer, $date)
    {
        return DebtHistory::with(['stock'])->with(['old_stock'])->where('customer', $customer)->whereDate('date', $date)
            ->get();
    }


//customer debt history this week

    public function get_customer_all_invoices_this_week($customer)
    {
        return DebtHistory::with('debt_history_stocks')
            ->where('customer', $customer)
            ->with('old_stock')
            ->with('customer')
            ->get();
    }

//    customer debt history this month

    public function get_customer_all_invoices_this_month($customer)
    {
        return DebtHistory::with('debt_history_stocks')
            ->where('customer', $customer)
            ->with('old_stock')
            ->get();

    }

    //    customer debt history by month


    /**
     * CASHFLOW METHODS
     **/

//  date sold items

    public function daily_sold_items_summary($date, $store)
    {
        return DB::table('new_sale_stocks')
            ->whereDate('date', $date)
            ->where('store', $store)
            ->join('new_stocks', 'new_sale_stocks.stock', '=', 'new_stocks.id')
            ->select('new_sale_stocks.id', 'new_sale_stocks.quantity', 'new_sale_stocks.each',
                'new_sale_stocks.total', 'new_stocks.stock_name')
            ->get();

    }


//    get days summlied items


    public function daily_debt_items($store, $date)
    {
        return DB::table('debt_histories')
            ->whereDate('date', $date)
            ->join('new_stocks', 'debt_histories.stock', '=', 'new_stocks.id')
            ->join('customers', 'debt_histories.customer', '=', 'customers.id')
            ->select('debt_histories.quantity', 'debt_histories.price', 'debt_histories.total_amount', 'customers.name',
                'new_stocks.stock_name')
            ->where('debt_histories.store', '=', $store)
            ->get();

    }

    public function daily_debt_total($store, $date)
    {
        return DB::table('debt_histories')
            ->whereDate('date', $date)
            ->where('store', '=', $store)
            ->sum('total_amount');

    }

    public function daily_debt_payments($store, $date)
    {
        return DB::table('invoice_payments')
            ->whereDate('date', $date)
            ->join('customers', 'invoice_payments.customer', '=', 'customers.id')
            ->where('invoice_payments.store', $store)
            ->select('invoice_payments.total_payment', 'customers.name')
            ->get();
    }

    public function daily_debt_payments_total($store, $date)
    {
        return DB::table('invoice_payments')
            ->where('store', $store)
            ->whereDate('date', $date)
            ->sum('total_payment');
    }


    public function daily_debt_payments_mpesa($store, $date)
    {
        return DB::table('invoice_payments')
            ->where('store', $store)
            ->whereDate('date', $date)
            ->sum('mpesa');
    }

    public function daily_debt_payments_cash($store, $date)
    {
        return DB::table('invoice_payments')
            ->where('store', $store)
            ->whereDate('date', $date)
            ->sum('cash');
    }


    public function daily_expenses($store, $date)
    {
        return DB::table('expenses')
            ->whereDate('date', $date)
            ->where('store', $store)
            ->select('amount', 'reason')
            ->get();
    }

    public function daily_expenses_total($store, $date)
    {
        return DB::table('expenses')
            ->whereDate('date', $date)
            ->where('store', $store)
            ->sum('amount');
    }

    public function daily_deposits_total($store, $date)
    {
        return DB::table('deposits')
            ->whereDate('date', $date)
            ->where('store', $store)
            ->sum('amount');
    }


    public function check_daily_summary($store)
    {
        $date = Carbon::parse(Carbon::now())->toDateString();

        $daily = $this->get_day_analytics_by_date($store, $date);

        if (!$daily)
        {
            $opening_balance = $this->get_opening_balance($store, $date);
            return DB::table('daily_summaries')
                ->updateOrInsert([
                    'date' => Carbon::today(),
                    'store' => $store,
                ],
                    [
                        'opening_balance' => $opening_balance,
                        'opening_time' => Carbon::parse(Carbon::now())->toDateTime(),
                        'closing_balance' => $opening_balance,
                    ]
                );
        }

    }


    public function day_sale_cash($store, $date)
    {


        $sales_without_broker = Sale::where('store', $store)
            ->whereDate('date', $date)
            ->where('broker_total', 0)
            ->where('mpesa', '=', 0)
            ->sum('cash');
        $sale_with_broker_without_mpesa = DB::table('sales')
            ->where('store', $store)
            ->whereDate('date', $date)
            ->where('broker_total', '!=', 0)
            ->where('mpesa', '=', 0)
            ->sum('sale_total');

        $cash_total__ = DB::table('sales')
            ->where('store', $store)
            ->whereDate('date', $date)
            ->where('broker_total', '=', 0)
            ->where('cash', '!=', 0)
            ->where('mpesa', '!=', 0)
            ->sum('cash');

        $cash_total_broker_with_mpesa = DB::table('sales')
            ->where('store', $store)
            ->whereDate('date', $date)
            ->where('broker_total', '!=', 0)
            ->where('cash', '!=', 0)
            ->where('mpesa', '!=', 0)
            ->sum('cash');

//        $net = $cash_total_ - $broker_total;
        $sub = (int)$sales_without_broker + $sale_with_broker_without_mpesa + $cash_total__ + $cash_total_broker_with_mpesa;
        return $sub;

    }

    public function day_sale_mpesa($store, $date)
    {


        $sales_without_broker = Sale::where('store', $store)
            ->whereDate('date', $date)
            ->sum('mpesa');
        return (int)$sales_without_broker;

    }

    public function day_sales_total($store, $date)
    {


        $sales = Sale::where('store', $store)
            ->whereDate('date', $date)
            ->sum('sale_total');

        return (int)$sales;

    }


    public function refresh_dairy_summary($store, $date)
    {

        $opening_balance = $this->get_opening_balance($store, $date);

        $money_out = $this->daily_expenses_total($store, $date) + $this->daily_deposits_total($store, $date);
        $money_in = $this->day_sale_cash($store, $date) + $this->daily_debt_payments_cash($store,
                $date) + $opening_balance;

        $closing = $money_in - (int)$money_out;
        return DB::table('daily_summaries')
            ->updateOrInsert([
                'date' => $date,
                'store' => $store,

            ],
                [
                    'opening_time' => Carbon::parse(Carbon::now())->toDateTime(),
                    'opening_balance' => $opening_balance,
                    'sales_cash' => $this->day_sale_cash($store, $date),
                    'sales_mpesa' => $this->day_sale_mpesa($store, $date),
                    'sales_total' => $this->day_sales_total($store, $date),
                    'sales_debt' => $this->daily_debt_total($store, $date),
                    'expenses' => $this->daily_expenses_total($store, $date),
                    'closing_balance' => $closing,
                    'debt_recovered_cash' => $this->daily_debt_payments_cash($store, $date),
                    'debt_recovered_mpesa' => $this->daily_debt_payments_mpesa($store, $date),
                    'debt_recovered_total' => $this->daily_debt_payments_total($store, $date),
                    'bank_deposits' => $this->daily_deposits_total($store, $date)
                ]
            );

    }


    public function return_sale_stock($sale_id, $column)
    {

        $sale_stocks = NewSaleStock::where('sale', $sale_id)->get();

        foreach ($sale_stocks as $sale_stock)
        {
            $this->increase_stock($sale_stock->stock, $column, $sale_stock->quantity);
            NewSaleStock::destroy($sale_stock->id);
        }

    }

    public function delete_sale($sale_id, $column)
    {
        $this->return_sale_stock($sale_id, $column);
        return Sale::destroy($sale_id);
    }


    public function end_year_result()
    {


        $result = new stdClass();

        $chini_ya_mnazi = new stdClass();
        $nakuru = new stdClass();
        $old_nation = new stdClass();


        $result->best_sellers_by_quantity_all_shops = DB::table('new_sale_stocks')
            ->join('new_stocks', 'new_sale_stocks.stock', '=', 'new_stocks.id')
            ->select('new_stocks.stock_name', DB::raw('COUNT(stock) as sold_units'))
            ->groupBy('new_stocks.stock_name')
            ->orderBy('sold_units', 'desc')
            ->limit(10)
            ->get();
        $result->best_sellers_by_amount = DB::table('new_sale_stocks')
            ->join('new_stocks', 'new_sale_stocks.stock', '=', 'new_stocks.id')
            ->select('new_stocks.stock_name', DB::raw('COUNT(stock) as sold_units'),
                DB::raw('SUM(total) as total_amount'))
            ->groupBy('new_stocks.stock_name')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();


        $result->worst_sellers_by_amount = DB::table('new_sale_stocks')
            ->join('new_stocks', 'new_sale_stocks.stock', '=', 'new_stocks.id')
            ->select('new_stocks.stock_name', DB::raw('COUNT(stock) as sold_units'),
                DB::raw('SUM(total) as total_amount'))
            ->groupBy('new_stocks.stock_name')
            ->orderBy('total_amount')
            ->limit(5)
            ->get();

        $result->worst_sellers_by_quantity_all_shops = DB::table('new_sale_stocks')
            ->join('new_stocks', 'new_sale_stocks.stock', '=', 'new_stocks.id')
            ->select('new_stocks.stock_name', DB::raw('COUNT(stock) as sold_units'))
            ->groupBy('new_stocks.stock_name')
            ->orderBy('sold_units')
            ->limit(5)
            ->get();

        $result->shop_performance = DB::table('sales')
            ->join('stores', 'sales.store', 'stores.id')
            ->select('stores.name', DB::raw('SUM(sales.sale_total) as total_sales'))
            ->groupBy('stores.name')
            ->orderBy('total_sales', 'desc')
            ->get();


        $chini_ya_mnazi->total_sales = $this->number_of_sales(1);
        $old_nation->total_sales = $this->number_of_sales(2);
        $nakuru->total_sales = $this->number_of_sales(3);


        $chini_ya_mnazi->best_seller_by_quantity = $this->best_seller(1);
        $old_nation->best_seller_by_quantity = $this->best_seller(2);
        $nakuru->best_seller_by_quantity = $this->best_seller(3);

        $chini_ya_mnazi->worst_seller_by_quantity = $this->worst_seller(1);
        $old_nation->worst_seller_by_quantity = $this->worst_seller(2);
        $nakuru->worst_seller_by_quantity = $this->worst_seller(3);


        $chini_ya_mnazi->best_seller_by_amount = $this->big_seller(1);
        $old_nation->best_seller_by_amount = $this->big_seller(2);
        $nakuru->best_seller_by_amount = $this->big_seller(3);

        $chini_ya_mnazi->worst_seller_by_amount = $this->small_seller(1);
        $old_nation->worst_seller_by_amount = $this->small_seller(2);
        $nakuru->worstt_seller_by_amount = $this->small_seller(3);


        $chini_ya_mnazi->sales_stotal = $this->total_sales_all(1);
        $old_nation->sales_stotal = $this->total_sales_all(2);
        $nakuru->sales_stotal = $this->total_sales_all(3);


        return response()->json([
            'status' => true,
            'old_nation' => $old_nation,
            'chini_ya_mnazi' => $chini_ya_mnazi,
            'nakuru' => $nakuru,
            'data' => $result,
        ]);
    }


    public function number_of_sales($store)
    {

        return DB::table('sales')->where('store', $store)->count();


    }

    public function best_seller($store)
    {
        return DB::table('new_sale_stocks')
            ->where('store', $store)
            ->join('new_stocks', 'new_sale_stocks.stock', '=', 'new_stocks.id')
            ->select('new_stocks.stock_name', DB::raw('COUNT(stock) as sold_units'))
            ->groupBy('new_stocks.stock_name')
            ->orderBy('sold_units', 'desc')
            ->limit(10)
            ->get();

    }

    public function big_seller($store)
    {
        return DB::table('new_sale_stocks')
            ->where('store', $store)
            ->join('new_stocks', 'new_sale_stocks.stock', '=', 'new_stocks.id')
            ->select('new_stocks.stock_name', DB::raw('COUNT(stock) as sold_units'),
                DB::raw('SUM(total) as total_amount'))
            ->groupBy('new_stocks.stock_name')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();

    }

    public function worst_seller($store)
    {
        return DB::table('new_sale_stocks')
            ->where('store', $store)
            ->join('new_stocks', 'new_sale_stocks.stock', '=', 'new_stocks.id')
            ->select('new_stocks.stock_name', DB::raw('COUNT(stock) as sold_units'))
            ->groupBy('new_stocks.stock_name')
            ->orderBy('sold_units')
            ->limit(10)
            ->get();

    }

    public function small_seller($store)
    {
        return DB::table('new_sale_stocks')
            ->where('store', $store)
            ->join('new_stocks', 'new_sale_stocks.stock', '=', 'new_stocks.id')
            ->select('new_stocks.stock_name', DB::raw('COUNT(stock) as sold_units'),
                DB::raw('SUM(total) as total_amount'))
            ->groupBy('new_stocks.stock_name')
            ->orderBy('total_amount')
            ->limit(10)
            ->get();

    }


    public function total_sales_all($store)
    {

        return (integer)DB::table('sales')
            ->where('store', $store)
            ->sum('sale_total');
    }



//    public function transferred_stock($store){
//
//        TransferHistory::with('stock')->with('destination_store')->where('store')-
//
//    }


}


