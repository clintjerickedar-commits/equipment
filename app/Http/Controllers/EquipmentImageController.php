<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Support\Facades\Storage;

class EquipmentImageController extends Controller
{
    public function show(Equipment $equipment)
    {
        if ($equipment->image_path) {
            $disk = Storage::disk('public');

            if ($disk->exists($equipment->image_path)) {
                return $disk->response($equipment->image_path);
            }
        }

        return response()
            ->view('equipment.image', ['item' => $equipment])
            ->header('Content-Type', 'image/svg+xml');
    }
}
