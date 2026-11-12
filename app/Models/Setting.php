<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Simple key/value store for admin-editable system settings. */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** Defaults used until an administrator saves a value. 0 means "no limit". */
    public const DEFAULTS = [
        'max_borrow_days'       => 14,
        'max_quantity_per_item' => 0,
    ];

    /**
     * Reads a setting. Creates the settings table first if it does not exist yet,
     * and falls back to the built-in default if anything goes wrong.
     */
    public static function get(string $key, $default = null)
    {
        try {
            static::ensureTable();
            $value = static::where('key', $key)->value('value');
        } catch (\Throwable $e) {
            report($e); // logged, but the borrower still sees a working page
            $value = null;
        }

        return $value ?? $default ?? (self::DEFAULTS[$key] ?? null);
    }

    public static function put(string $key, $value): void
    {
        static::ensureTable();
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }

    /**
     * Creates the `settings` table (and its default rows) only if it is missing.
     * Existing tables and records are never touched. Checked once per request.
     */
    protected static function ensureTable(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        if (Schema::hasTable('settings')) {
            return;
        }

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $now = now();
        foreach (self::DEFAULTS as $key => $value) {
            DB::table('settings')->insert(['key' => $key, 'value' => (string) $value, 'created_at' => $now, 'updated_at' => $now]);
        }
    }
}
