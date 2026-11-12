<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

/** Borrowing rules. Both values are enforced in StoreBorrowRequest. 0 = no limit. */
class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings', [
            'maxDays' => (int) Setting::get('max_borrow_days'),
            'maxQty'  => (int) Setting::get('max_quantity_per_item'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'max_borrow_days'       => ['required', 'integer', 'min:0', 'max:365'],
            'max_quantity_per_item' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        try {
            foreach ($data as $key => $value) {
                Setting::put($key, $value);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);
            return back()->withInput()->with('error', 'Settings could not be saved because the "settings" table is missing in the database. Create it first (see the SQL in the instructions), then try again.');
        }

        return back()->with('success', 'Settings saved. New limits apply to the next requests.');
    }
}
