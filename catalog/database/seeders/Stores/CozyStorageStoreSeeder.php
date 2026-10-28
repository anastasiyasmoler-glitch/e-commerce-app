<?php

namespace Database\Seeders\Stores;

use App\Models\Store;
use Database\Seeders\Concerns\SeedsCatalogDemo;
use Illuminate\Database\Seeder;

class CozyStorageStoreSeeder extends Seeder
{
    use SeedsCatalogDemo;

    public function run(): void
    {
        $store = Store::query()->create([
            'name' => 'Cozy Storage',
            'slug' => 'cozy-storage',
        ]);

        $this->category($store, 'Wardrobes', 'wardrobes');
        $this->category($store, 'Shelving', 'shelving');
        $this->category($store, 'Decor', 'decor');

        $this->seedStoreProducts($store, [
            ['cat' => 'wardrobes', 'name' => 'Alba sliding wardrobe 180 cm', 'slug' => 'alba-sliding-wardrobe', 'sku' => 'HR-SHK-001', 'desc' => 'Two-door sliding wardrobe with mirror, white carcass.', 'qty' => 5, 'res' => 1],
            ['cat' => 'wardrobes', 'name' => 'Scandi wardrobe 120 cm', 'slug' => 'scandi-wardrobe', 'sku' => 'HR-SHK-002', 'desc' => 'Wardrobe in light wood with two doors.', 'qty' => 7, 'res' => 0],
            ['cat' => 'wardrobes', 'name' => 'Nord chest of drawers 90 cm', 'slug' => 'nord-chest-of-drawers', 'sku' => 'HR-SHK-003', 'desc' => 'Four-drawer chest with handleless push-to-open fronts.', 'qty' => 12, 'res' => 2],
            ['cat' => 'shelving', 'name' => 'Modul shelving unit, 5 shelves', 'slug' => 'modul-shelving-unit', 'sku' => 'HR-STE-001', 'desc' => 'Open shelving unit in oak veneer chipboard, 180 cm tall.', 'qty' => 15, 'res' => 1],
            ['cat' => 'shelving', 'name' => 'Loft metal shelving', 'slug' => 'loft-metal-shelving', 'sku' => 'HR-STE-002', 'desc' => 'Shelving on a metal frame with adjustable shelves.', 'qty' => 9, 'res' => 0],
            ['cat' => 'shelving', 'name' => 'Veranda etagere', 'slug' => 'veranda-etagere', 'sku' => 'HR-STE-003', 'desc' => 'Etagere for plants and books, four tiers.', 'qty' => 0, 'res' => 0],
            ['cat' => 'decor', 'name' => 'Oval mirror 60×90', 'slug' => 'oval-mirror', 'sku' => 'HR-DEK-001', 'desc' => 'Wall mirror in a slim oak frame.', 'qty' => 20, 'res' => 2],
            ['cat' => 'decor', 'name' => 'Flora vase set', 'slug' => 'flora-vase-set', 'sku' => 'HR-DEK-002', 'desc' => 'Three ceramic vases in different heights, matte glaze.', 'qty' => 17, 'res' => 1],
            ['cat' => 'decor', 'name' => 'Forest scented candles', 'slug' => 'forest-scented-candles', 'sku' => 'HR-DEK-003', 'desc' => 'Set of two soy wax candles, pine and cedar scent.', 'qty' => 34, 'res' => 4],
            ['cat' => 'decor', 'name' => 'Woven storage basket', 'slug' => 'woven-storage-basket', 'sku' => 'HR-DEK-004', 'desc' => 'Rattan woven basket, 35 L, with handles.', 'qty' => 22, 'res' => 3],
            ['cat' => 'decor', 'name' => 'Ceramic trinket tray', 'slug' => 'ceramic-trinket-tray', 'sku' => 'HR-DEK-005', 'desc' => 'Small glazed tray for keys and jewellery.', 'qty' => 40, 'res' => 2, 'price' => 1999],
            ['cat' => 'wardrobes', 'name' => 'Walk-in wardrobe system', 'slug' => 'walk-in-wardrobe-system', 'sku' => 'HR-SHK-004', 'desc' => 'Modular walk-in wardrobe kit, white and oak.', 'qty' => 1, 'res' => 0, 'price' => 3299900],
            ['cat' => 'shelving', 'name' => 'Corner shelf unit', 'slug' => 'corner-shelf-unit', 'sku' => 'HR-STE-004', 'desc' => 'Five-tier corner shelving, 200 cm height.', 'qty' => 8, 'res' => 0, 'price' => 34900],
            ['cat' => 'wardrobes', 'name' => 'Berg wardrobe 160 cm', 'slug' => 'berg-wardrobe-160', 'sku' => 'HR-SHK-005', 'desc' => 'Two-door wardrobe with soft-close doors, oak foil.', 'qty' => 6, 'res' => 1],
            ['cat' => 'wardrobes', 'name' => 'Norr chest of drawers 120 cm', 'slug' => 'norr-chest-of-drawers', 'sku' => 'HR-SHK-006', 'desc' => 'Wide six-drawer chest, walnut veneer, brass handles.', 'qty' => 9, 'res' => 0],
            ['cat' => 'shelving', 'name' => 'Arctic ladder shelf', 'slug' => 'arctic-ladder-shelf', 'sku' => 'HR-STE-005', 'desc' => 'Leaning ladder shelf in white lacquered MDF, 5 tiers.', 'qty' => 17, 'res' => 2],
            ['cat' => 'shelving', 'name' => 'Hanging shelf oak 90 cm', 'slug' => 'hanging-shelf-oak', 'sku' => 'HR-STE-006', 'desc' => 'Wall-mounted oak shelf with leather straps.', 'qty' => 24, 'res' => 3],
            ['cat' => 'decor', 'name' => 'Nordic candle trio', 'slug' => 'nordic-candle-trio', 'sku' => 'HR-DEK-006', 'desc' => 'Three scented candles: sea salt, birch, amber.', 'qty' => 31, 'res' => 2],
            ['cat' => 'decor', 'name' => 'Stone table clock', 'slug' => 'stone-table-clock', 'sku' => 'HR-DEK-007', 'desc' => 'Silent sweep clock in a travertine-look case.', 'qty' => 15, 'res' => 0],
        ]);
    }
}
