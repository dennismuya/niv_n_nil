<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cashflow\NewCashFlowController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerSupplyController;
use App\Http\Controllers\DailyAnalytics;
use App\Http\Controllers\DailySummaryController;
use App\Http\Controllers\Debt\DebtHistoryController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;

//use Illuminate\Http\Request;
use App\Http\Controllers\MyBaseController;
use App\Http\Controllers\NewInvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Sale\NewSaleController;
use App\Http\Controllers\Sale\NewSaleStockController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SlipController;
use App\Http\Controllers\Stock\NewStockController;
use App\Http\Controllers\Stock\SupplierHistoryController;
use App\Http\Controllers\Stock\TransferHistoryController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SupplyPaymentController;
use App\Models\SupplyPayment;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('auth/signup', [AuthController::class, 'signup']);
Broadcast::routes(['middleware' => ['auth:sanctum']]);


Route::middleware('auth:sanctum')->group(function () {


//    users & authentification
    Route::get('/users', [AuthController::class, 'get_users']);
    Route::get('/auth/{store}/user', [AuthController::class, 'get_user']);
    Route::post('/user/{user_}/change_password', [AuthController::class, 'change_user_password']);
    Route::post('/users/delete/{id}', [AuthController::class, 'delete_user']);
    Route::post('/store/add_user/{store_id}/{user_id}', [StoreController::class, 'add_user_store']);
    Route::post('/store/add', [StoreController::class, 'add_store']);

//    stocks
    Route::post('products/add_single', [ProductController::class, 'add_product']);
    Route::post('/stock/add', [StockController::class, "add_stock"]);
    Route::get('/store/{store}/stock', [StockController::class, "get_stock"]);
    Route::get('/store/{store}/transferred_stock', [StockController::class, 'get_transferred_stock']);
    Route::post('/store/{to_store}/stock_transfer', [StockController::class, "transfer_stock"]);

//    categories

    Route::get('/categories', [CategoryController::class, 'get_categories']);
    Route::post('/categories/add', [CategoryController::class, 'add_category']);

    Route::get('/suppliers', [CustomerSupplyController::class, 'customer_with_supply']);
    Route::get('/sales/{sale_id}/products', [SaleController::class, 'get_sale_products']);
    Route::post('/sales/{sale_id}/delete', [SaleController::class, 'delete_sale']);
    Route::post('sales/{sale_id}/update', [SaleController::class, 'update_sale']);

    Route::post('auth/store/{store}/register_user', [AuthController::class, 'register_user']);
//    analytics

    Route::get('/daily_data/{store}/today', [DailyAnalytics::class, 'daily_analytics']);

//
//    Add customer
    Route::post('/store/{store}/customers/add', [CustomerController::class, "add_customer"]);
    Route::post('/store/{store}/customers/{id}/edit', [CustomerController::class, "edit_customer"]);
    Route::get('/store/{store}/customers/all', [CustomerController::class, "get_customers"]);
    Route::get('/store/{store}/customers_with_invoices', [CustomerController::class, "get_customers_with_invoices"]);
    Route::get('/store/{store}/customer/invoice', [CustomerController::class, "customer_add_invoice"]);
    Route::get('/store/{store}/customers/invoices', [CustomerController::class, "get_customers_with_invoices"]);

    Route::post('/store/{store}/expenses/add', [ExpenseController::class, "add_expense"]);
    Route::get('/store/{store}/expenses', [ExpenseController::class, "get_expenses"]);
    Route::get('/store/{store}/sales', [SaleController::class, "get_sales"]);
    Route::get('/store/{store}/today_sales', [SaleController::class, "get_today_sales"]);
    Route::get('/store/{store}/invoices', [InvoiceController::class, "get_invoices"]);
    Route::get('/customers/{customer}/invoices', [InvoiceController::class, 'get_customer_invoice']);
    Route::post('/invoice/{customer}/pay', [InvoiceController::class, 'make_invoice_payment']);

    Route::get('/store/{store}/suppliers_with_stock', [CustomerSupplyController::class, 'supplier_with_stock']);
    Route::get('/store/{store}/suppliers', [CustomerSupplyController::class, 'suppliers']);
    Route::get('/supplier/{supplier}/stock', [CustomerSupplyController::class, 'supplier_stock']);
    Route::get('/store/{store}/receive_all', [StockController::class, 'receive_all_transferred']);
    Route::get('/stock/{stock}/soft_delete', [StockController::class, 'soft_delete_one']);
    Route::get('/stores/suppliers', [CustomerSupplyController::class, 'stores_suppliers']);


//    roles
//    get roles

    Route::get('/roles', [RoleController::class, 'get_roles']);


//    invoices

    Route::post('/invoice/{invoice}/return_stock', [InvoiceController::class, 'return_invoice_item']);


    Route::post('/store/{store}/make_sale', [SaleController::class, "make_sale"]);
    Route::post('/store/{store}/make_invoice', [InvoiceController::class, "make_invoice"]);
    Route::get('/store/{store}/users', [StoreController::class, 'get_store_users']);
    Route::get('/products', [ProductController::class, 'get_all']);
    Route::get('/products/{id}/delete', [ProductController::class, 'delete_product']);
    Route::post('/products/{id}/update', [ProductController::class, 'update_product']);


    Route::get('/stores', [StoreController::class, 'get_stores']);
    Route::get('/store/users', [AuthController::class, 'get_users']);

//  deposit
    Route::post('deposit/{store}/bank/{bank}', [DepositController::class, 'make_deposit']);

//    banks
    Route::get('/banks', [\App\Http\Controllers\BankController::class, 'get_banks']);


//    store

    Route::get('/store/{store}/cash_flow/{month}', [CashFlowController::class, 'get_shop_all_cashflow']);


//    transferred stock

    Route::get('/store/{store}/transfer_history', [StockController::class, 'transferred_stock']);

    Route::post('/edit/stock', [StockController::class, 'edit_stock']);

//    deposits

    Route::get('/{store}/deposits', [DepositController::class, 'get_deposits']);

//    month sales

    Route::get('/store/{store}/sales/{month}', [SaleController::class, 'get_month_sales']);

//    todays invoices

    Route::get('/store/{store}/today_invoices', [InvoiceController::class, 'get_todays_invoices']);


//    invoice summary

    Route::get('/store/{store}/invoice_summary', [InvoiceController::class, 'get_debt_summary']);

//    get monthly

    Route::get('store/{store}/cash_flow_summary', [CashFlowController::class, 'get_cash_summary']);

//    migrate  daily summary
//    Route::get('/store/{store}/migrate/{month}', [DailySummaryController::class, 'migrate_from_june']);

//    get summary

//    Route::get('/store/{store}/daily_summary', [DailySummaryController::class, 'get_all']);


//    move invoices
    Route::post('/store/{store}/move_invoices', [NewInvoiceController::class, 'move_invoices']);


//    get customer new invoices

    Route::get('/store/{store}/customers/{customer}/invoices', [NewInvoiceController::class, 'get_customer_invoice']);

//    get old invoices without stock

    Route::get('/invoices_without_stock', [NewInvoiceController::class, 'old_invoices_without_stock']);

//    make return

//    Route::post('customer_return/{customer}/return', [InvoiceController::class, 'return__stock']);
//    delete new invoice record

    Route::post('customer_return/{customer}/delete_record', [InvoiceController::class, 'delete_take_stock_record']);

//    clear daily_summary_table
    Route::get('/refresh_daily', [NewInvoiceController::class, 'clear_dairy_summary']);

//    make new invoice

    Route::post('store/{store}/make_new_invoice', [NewInvoiceController::class, 'make_new_invoice']);


//    delete stock

    Route::post('/store/{store}/delete_stock', [StockController::class, 'delete__stock']);


//    move stock

    Route::get('/chiniyamnazi/move/stock', [\App\Http\Controllers\NewStock\MoveStokController::class, 'move_stock']);


//    supplier supplies

    Route::get('/supplier/{supplier}/supplies', [CustomerSupplyController::class, 'supplier_stock']);

    //   create payment_dates
    Route::get('system/create_invoice_payment_dates',
        [\App\Http\Controllers\CustomerInvoiceController::class, 'create_invoice_pay_date']);


    //move debts
    Route::get('store/{store}/move_debts', [NewInvoiceController::class, 'move_invoices']);

//    get new customer debts

    Route::get('store/{store}/new_invoices/get_customers_with_invoices',
        [NewInvoiceController::class, 'get_customers_with_new_invoices']);


//    new invoices summary

    Route::get('/store/{store}/new_invoice_summary', [NewInvoiceController::class, 'get_new_invoice_summary']);

//get customers new invoices with archives
    Route::get('store/{store}/customers/{customer}/new_invoices/archives',
        [NewInvoiceController::class, 'get_customer_new_invoice_with_archives']);


//  get customers new invoices  without_archives
    Route::get('store/{store}/customers/{customer}/new_invoices',
        [NewInvoiceController::class, 'get_customer_new_invoice']);


//   make new invoice

    Route::post('/store/{store}/make_new_invoice', [NewInvoiceController::class, "make_new_invoice"]);


//    new invoice return
    Route::post('customer_return/{customer}/return_new_stock', [NewInvoiceController::class, 'return_stock']);

//    new invoice delete
    Route::post('customer_delete/{customer}/delete_record',
        [NewInvoiceController::class, 'delete_new_invoice_stock_record']);


//    daily summary

//    get_summarry

    Route::get('/store/{store}/get_daily_summary', [DailySummaryController::class, 'get_todays_summary']);


//add store to invoice payments
    Route::get('/all_stores/add_store_to_invoice_payments',
        [NewInvoiceController::class, 'add_store_to_invoice_payments']);


//update expenses time and date
    Route::get('/all_stores/update_expense_date_time', [ExpenseController::class, 'add_date_time_to_expenses']);

//update sale date_time

    Route::get('all_stores/update_sale_date_time', [SaleController::class, 'add_date_time_to_sales']);


//    add dates to deposits

    Route::get('all_stores/add_date_to_deposits', [DepositController::class, 'add_date_to_deposits']);


//    add old debts

    Route::post('store/{store}/old_debt/customer/{customer}', [NewInvoiceController::class, 'old_debt']);

//    add old debt payments
    Route::post('/store/{store}/old_payments/customer/{customer}', [NewInvoiceController::class, 'add_old_payments']);

//    make new invoice payment
    Route::post('/store/{store}/customer/{customer}/pay_invoice',
        [NewInvoiceController::class, 'make_new_invoice_payment']);

//    add multiple customers
    Route::post('/store/{store}/multiple_customers', [CustomerController::class, 'add_multiple_customer']);

//    old slips
    Route::post('/store/{store}/old_slips/customer/{customer}', [SlipController::class, 'move_old_slips']);

//    supply payments
    Route::post('/store/{store}/supplier/{supplier}/make_payment', [SupplyPaymentController::class, 'pay_supplier']);

//update buying price

    Route::post('/store/{store}/stocks/update', [CustomerSupplyController::class, 'update_buying_price']);

//    slip
    Route::post('/store/{store}/customer/{customer}/slip', [SlipController::class, 'slip']);

//    archive
    Route::post('/store/{store}/customer/{customer}/archive_debts', [NewInvoiceController::class, 'archive_invoices']);

//    create stock names
    Route::get('/make_stock_name/{store}', [StockController::class, 'add_stock_name']);

//    create quantity property

    Route::get('/add_stock_quantity/{store}/{letter}', [StockController::class, 'add_stock_quantity']);

//    update stock_

    Route::get('/update_stock/{store}/', [StockController::class, 'update_stock']);
});


Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    Route::get('/all_stores/move_stock_to_new_stocks/{lower_limit}/{upper_limit}',
        [NewStockController::class, 'transfer_to_new']);

});


Route::middleware('auth:sanctum')->prefix('v1_stock')->group(function () {

//    suppliers
    Route::get('/store/{store}/suppliers',
        [SupplierHistoryController::class, 'get_suppliers']);
    Route::get('/supplier/{supplier}/supplies',
        [SupplierHistoryController::class, 'get_customer_supplies']);


//    create_stock
    Route::post('column/{column}/stock/create', [NewStockController::class, 'create_new_stock']);

    //delete_stock

    Route::post('column/{column}/soft_delete/stock/{stock_id}', [NewStockController::class, 'soft_delete']);
//    clear
    Route::get('new_stock/clear', [NewStockController::class, 'clear_new']);

    /**
     * get stock
     */
    Route::get('store/{column}/get_new_stock', [NewStockController::class, 'get_new_stock']);

    /**
     * add stock
     */

    Route::post('store/{store}/column/{column}/stock/{stock_id}/add', [NewStockController::class, 'add_new_stock']);


    /**
     * update stock
     */

    Route::post('store/{store}/column/{column}/stock/{stock_id}/update_stock',
        [NewStockController::class, 'update_new_stock']);


    /**
     * transfer stock
     */


    Route::post('store/{store}/column/{column}/stock_transfer',
        [TransferHistoryController::class, 'new_stock_transfer']);

    /**
     * received stock
     */

    Route::get('store/{store}/stock/received_stock', [TransferHistoryController::class, 'get_received_new_stocks']);

//    receive stocks

    Route::post('/store/{store}/column/{column}/receive_stock',
        [TransferHistoryController::class, 'receive_new_stock']);

//    edit stock name

    Route::post('/stock/{stock}/edit_name/column/{column}',[NewStockController::class,'update_stock_name']);



});


Route::middleware('auth:sanctum')->prefix('v1_new_sales')->group(function () {


////    modify old sale products
//    Route::get('/add_name_to_stock_products',
//        [NewSaleController::class, 'add_stock_name_to_sale_products']);
////    move sale stocks
//    Route::get('/move_sale_products/{store}/{month}',
//        [NewSaleController::class, 'add_new_sale_stocks']);
//    make new sale
    Route::post('store/{store}/column/{column}/make_new_sale',
        [NewSaleController::class, 'make_new_sale']);

    Route::post('store/{store}/column/{column}/sale/{sale}/edit',
        [NewSaleController::class, 'edit_new_sale']);

    //    delete_sales
    Route::get('/column/{column}/sale/{sale_id}/delete', [NewSaleController::class, 'delete_store_sale']);


//    sales by hrs
    Route::get('store/{store}/get_new_sales/by_hrs/{hours}',
        [NewSaleController::class, 'get_recent_sales_by_hrs']);

//     sales by month
    Route::get('store/{store}/year/{year}/month/{month}/get_sales',
        [NewSaleController::class, 'get_month_sales']);

//    sale items date and store

//    Route::get('stores/add_date_store_to_new_sale_stocks',
//        [NewSaleStockController::class, 'add_store_and_date_to_sale_stock']);


});

Route::middleware('auth:sanctum')->prefix('v1_debt_history')->group(function () {

//    add stock name to new invoices
    Route::get('/store/{store}/add_stock_name_to_new_invoices',
        [DebtHistoryController::class, 'create_new_invoice_stock_names']);

//    move new_invoices

    Route::get('store/{store}/move_new_invoices', [DebtHistoryController::class, 'move_new_invoices']);

//    make new debt_history

    Route::post('store/{store}/customer/{customer}/column/{column}/make_invoice',
        [DebtHistoryController::class, 'make_new_invoice']);


    //get debt summary
    Route::get('store/{store}/debt_history_summary', [DebtHistoryController::class, 'get_debt_summary']);


//    get customer all debt
    Route::get('customer/{customer}/all_debt_history', [DebtHistoryController::class, 'get_customer_all_debts_']);
    //    get customer today debt
    Route::get('customer/{customer}/today_debt_history', [DebtHistoryController::class, 'get_customer_today_debts_']);
    //    get customer yesterday debt
    Route::get('customer/{customer}/yesterday_debt_history',
        [DebtHistoryController::class, 'get_customer_yesterday_debts_']);
    //    get customer this week debt
    Route::get('customer/{customer}/week_debt_history',
        [DebtHistoryController::class, 'get_customer_this_week_debts_']);
    //    get customer this month debt
    Route::get('customer/{customer}/month_debt_history',
        [DebtHistoryController::class, 'get_customer_this_month_debts_']);


//    return and restock

    Route::post('store/{store}/column/{column}/customer/{customer}/return/',
        [DebtHistoryController::class, 'return_and_restock_new_invoice']);
//    move old debts

//    Route::get('/move_all_old_debts', [DebtHistoryController::class, 'move_old_debts']);
});


Route::middleware('auth:sanctum')->prefix('v1_stores')->group(function () {

//    update store_column
    Route::get('stores/update_stock_column', [StoreController::class, 'add_store_stock_column']);
});

Route::middleware('auth:sanctum')->prefix('v1_analytics')->group(function () {
//    update store_column
    Route::get('store/{store}/get_new_daily_summary', [DailyAnalytics::class, 'get_daily_analytics']);

    Route::get('/store/{store}/all_summaries/year/{year?}/month/{month?}',
        [NewCashFlowController::class, 'get_all_daily_cashflow']);

    Route::post('store/{store}/daily_summary/refresh', [NewCashFlowController::class, 'refresh__daily_by_date']);


});

Route::middleware('auth:sanctum')->prefix('v1_cashflow')->group(function () {

//    get new analytics
    Route::get('store/{store}/get_cashflow/year/{year}/month/{month}',
        [NewCashFlowController::class, 'get_month_cashflow']);

});

Route::middleware('auth:sanctum')->prefix('v1_end_year_report')->group(function () {

//    get new analytics
    Route::get('end_year_summary', [MyBaseController::class, 'end_year_result']);

});















