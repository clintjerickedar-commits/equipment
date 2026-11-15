<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Support\Facades\Storage;

class EquipmentImageController extends Controller
{
    public function show(Equipment $equipment)
    {
        abort_unless($equipment->image_path, 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($equipment->image_path), 404);

        return $disk->response($equipment->image_path);
    }
}
