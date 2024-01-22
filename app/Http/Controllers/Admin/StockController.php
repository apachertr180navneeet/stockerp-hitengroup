<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\{
    User,
    Branch,
    Unit,
    Item,
    Stock,
    StockItem,
    StockReport,
    ItemStock
};


use Exception;
use DB;
use Illuminate\Http\Request;





use Illuminate\Support\Facades\{
    Auth,
    Hash,
    Session,
    Storage
};

class StockController extends Controller
{
    public function index(){
        if(Auth::check()){
            $user_data = auth()->user();
            if($user_data->type == '2') {
                $stock_list = Stock::where('stock.branch_id', $user_data->branch_id)
                ->select('stock.id','stock.stock_date','stock.total_amount','stock.qty','stock.status','stock.branch_id','users.name','branch.name as branch_name')
                ->join('branch', 'stock.branch_id', '=', 'branch.id')
                ->join('users', 'stock.vendor_id', '=', 'users.id')
                ->paginate(10);
            }else{
                $stock_list = Stock::select('stock.id','stock.stock_date','stock.total_amount','stock.qty','stock.status','stock.branch_id','users.name','branch.name as branch_name')
                ->join('branch', 'stock.branch_id', '=', 'branch.id')
                ->join('users', 'stock.vendor_id', '=', 'users.id')
                ->paginate(10);
            }
            $startDate = "";
            $endDate = "";

            return view('admin.stock.stock_list',compact('user_data','stock_list','startDate','endDate'))->with('i', (request()->input('page', 1) - 1) * 1);
        }

        return redirect("admin/login")->withSuccess('You are not allowed to access');
    }
    public function create(){
        if(Auth::check()){
            $user_data = auth()->user();
            $unit_list = Unit::get();
            if($user_data->type == '2') {
                $branch_list = Branch::where('id', $user_data->branch_id)->get();
            }else{
                $branch_list = Branch::get();
            }
            $user_list = User::where('type', '1')->get();
            $item_list = Item::join('unit', 'item.unit_id', '=', 'unit.id')->get();

            return view('admin.stock.stock_add',compact('user_data','unit_list','user_list','item_list','branch_list'));
        }

        return redirect("admin/login")->withSuccess('You are not allowed to access');
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'stock_date' => 'required',
        ]);
        $itemid = $request->service_id;
        $branch_id = $request->branch_id;
        if(empty($itemid)){
            return redirect()->back()->with(['danger' => 'At least one item to select']);
        }
        $qty = $request->qty;
        $amount = $request->rate;
        $unit = $request->unit;
        $totalamount = $request->totalamount;
        $date = $request->stock_date;
        $newDateFormat = date("m/d/Y", strtotime($date));
        $mainqty =  0;
        foreach ($qty as $qtymainkey => $qtymainvalue) {
            $mainqty += $qtymainvalue;
        }
        $datastock = [
            'stock_date' => $request->stock_date,
            'vendor_id' => $request->vendor_id,
            'total_amount' => $request->finaltotal_amount,
            'qty' => $mainqty
        ];
        $id = Stock::insertGetId($datastock);
        foreach ($itemid as $key => $itemidvalue) {
            $datastockitem[] = [
                'stock_id' => $id,
                'item_id' => $itemidvalue,
                'stock_quantity' => $qty[$key],
                'stock_amount' => $amount[$key],
                'itemtotalamount' => $totalamount[$key],
                'unit' => $unit[$key]
            ];
            $dataStockReport[] = [
                'item_id' => $itemidvalue,
                'quantity' => $qty[$key],
                'unit' => $unit[$key]
            ];
        }
        foreach ($datastockitem as $stockitemvalue) {
            StockItem::insertGetId($stockitemvalue);
        }
        foreach ($dataStockReport as $StockReportValue) {
            $itemid = $StockReportValue['item_id'];
            $quantity = $StockReportValue['quantity'];
            $unit = $StockReportValue['unit'];
            $stockreporthcheck = StockReport::where('item_id', '=', $itemid)->where('branch_id', '=', $branch_id)->first();
            if(empty($stockreporthcheck)){
                $dataReport = [
                    'item_id' => $itemid,
                    'branch_id' => $branch_id,
                    'quantity' => $quantity,
                    'unit' => $unit
                ];
                StockReport::insertGetId($dataReport);
            }else{
                $oldqty = $stockreporthcheck->quantity;
                $testqty = $oldqty + $quantity;
                StockReport::where('item_id', $itemid)->where('branch_id', $branch_id)->update(['quantity' => $testqty]);
            }
        }
        return redirect()->route('admin.stock.in.list')->with('success','stock created successfully.');

    }
    public function edit($id){
        if(Auth::check()){
            $user_data = auth()->user();
            $stock = Stock::where('id', $id)->first();
            $stockItem = StockItem::where('stock_id', $id)->join('item', 'stock_item.item_id', '=', 'item.id')->get();
            $unit_list = Unit::get();
            $item_list = Item::get();
            $user_list = User::where('type', '1')->get();
            return view('admin.stock.stock_edit',compact('user_data','stock','unit_list','user_list','item_list','stockItem'));
        }
        return redirect("admin/login")->withSuccess('You are not allowed to access');
    }
    public function update(Request $request){
        $validatedData = $request->validate([
            'stock_date' => 'required',
        ]);
        $stockitemid = $request->stockitemid;
        $stock_quantity = $request->stock_quantity;
        $stock_amount = $request->stock_amount;
        $itemtotalamount = $request->itemtotalamount;
        $id = $request->id;
        $datastock = [
            'stock_date' => $request->stock_date,
            'vendor_id' => $request->vendor_id,
            'total_amount' => $request->finaltotal_amount
        ];
        Stock::where('id', $id)->update($datastock);
        $deleted = StockItem::where('stock_id', $id)->delete();
        foreach ($request->stockitemid as $key => $itemidvalue) {
            $datastockitem[] = [
                'stock_id' => $id,
                'item_id' => $itemidvalue,
                'stock_quantity' => $stock_quantity[$key],
                'stock_amount' => $stock_amount[$key],
                'itemtotalamount' => $itemtotalamount[$key],
            ];

        }

        foreach ($datastockitem as $stockitemvalue) {
            StockItem::insertGetId($stockitemvalue);
        }
        return redirect()->route('admin.stock.in.list')->with('success','stock Update successfully.');
    }
    public function delete($id)
    {
        $deleted = StockItem::where('stock_id', $id)->delete();
        $deleted = Stock::where('id', $id)->delete();
        return response()->json(['success'=>'stock Deleted Successfully!']);
    }
    public function status(Request $request){
        $id = $request->id;
        $datauser = [
             'status' => $request->status,
        ];
        Stock::where('id', $id)->update($datauser);
        return response()->json(['success'=>'stock Status Changes Successfully!']);
    }
    public function serach(){
        if(Auth::check()){

            $user_data = auth()->user();
            if(!empty($_GET['startDate'])){
                $startDate = date($_GET['startDate']);
                $startDateFormat = date("Y-m-d", strtotime($startDate));
                $endDate = date($_GET['endDate']);
                $endDateFormat = date("Y-m-d", strtotime($endDate));
                $stock_list = Stock::select('stock.id','stock.stock_date','stock.status','users.name')->where('stock_date', '>=', $startDateFormat)->where('stock_date', '<=', $endDateFormat)->join('users', 'stock.vendor_id', '=', 'users.id')->paginate(10);
            }

            return view('admin.stock.stock_list',compact('user_data','stock_list','startDate','endDate'))->with('i', (request()->input('page', 1) - 1) * 1);
        }

        return redirect("admin/login")->withSuccess('You are not allowed to access');
    }
}
