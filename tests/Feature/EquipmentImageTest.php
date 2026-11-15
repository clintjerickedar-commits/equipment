<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EquipmentImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_equipment_image_is_served_from_public_storage_without_a_storage_link(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('equipment/projector.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/l3sAAAAASUVORK5CYII='
        ));

        $category = Category::create(['name' => 'Audio-Visual']);
        $equipment = Equipment::create([
            'equipment_code' => 'EQ-0001',
            'category_id' => $category->id,
            'name' => 'LCD Projector',
            'quantity' => 1,
            'available_quantity' => 1,
            'image_path' => 'equipment/projector.png',
            'condition' => 'good',
            'status' => 'active',
        ]);

        $this->get($equipment->image_url)
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
    }

    public function test_equipment_image_shows_a_product_illustration_when_photo_is_missing(): void
    {
        Storage::fake('public');

        $category = Category::create(['name' => 'Audio-Visual']);
        $equipment = Equipment::create([
            'equipment_code' => 'EQ-0001',
            'category_id' => $category->id,
            'name' => 'LCD Projector',
            'quantity' => 1,
            'available_quantity' => 1,
            'image_path' => 'equipment/missing.png',
            'condition' => 'good',
            'status' => 'active',
        ]);

        $this->get($equipment->image_url)
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml')
            ->assertSee('LCD Projector');
    }

    public function test_equipment_without_an_assigned_photo_still_has_an_image(): void
    {
        Storage::fake('public');

        $category = Category::create(['name' => 'Computing']);
        $equipment = Equipment::create([
            'equipment_code' => 'EQ-0002',
            'category_id' => $category->id,
            'name' => 'Laptop',
            'quantity' => 1,
            'available_quantity' => 1,
            'condition' => 'good',
            'status' => 'active',
        ]);

        $this->get($equipment->image_url)
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml')
            ->assertSee('Laptop');
    }
}
