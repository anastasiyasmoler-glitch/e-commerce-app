<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Seed demo catalog data (stores, categories, products, stocks).
     *
     * The seeder is idempotent: all existing catalog rows are removed
     * in FK-safe order before the demo data is created.
     */
    public function run(): void
    {
        Stock::query()->delete();
        Product::query()->delete();
        Category::query()->delete();
        Store::query()->delete();

        $this->seedNordicHome();
        $this->seedWarmLight();
        $this->seedSoftLinen();
        $this->seedCozyStorage();
    }

    private function seedNordicHome(): void
    {
        $store = Store::query()->create([
            'name' => 'Nordic Home',
            'slug' => 'nordic-home',
        ]);

        $sofas = $this->category($store, 'Sofas', 'sofas');
        $this->category($store, 'Straight sofas', 'straight-sofas', $sofas);
        $this->category($store, 'Corner sofas', 'corner-sofas', $sofas);
        $beds = $this->category($store, 'Beds', 'beds');
        $tables = $this->category($store, 'Tables', 'tables');
        $chairs = $this->category($store, 'Chairs', 'chairs');

        $products = [
            ['cat' => 'sofas', 'name' => 'Oslo straight sofa', 'slug' => 'oslo-straight-sofa', 'sku' => 'NH-DIV-001', 'desc' => 'Straight sofa in Scandinavian style, light oak legs, woven fabric upholstery.', 'qty' => 8, 'res' => 1],
            ['cat' => 'straight-sofas', 'name' => 'Copenhagen sofa', 'slug' => 'copenhagen-sofa', 'sku' => 'NH-DIV-002', 'desc' => 'Three-seat straight sofa, beech legs, removable grey covers.', 'qty' => 6, 'res' => 2],
            ['cat' => 'straight-sofas', 'name' => 'Malmo sofa', 'slug' => 'malmo-sofa', 'sku' => 'NH-DIV-003', 'desc' => 'Compact straight sofa for a small living room, light fabric.', 'qty' => 10, 'res' => 0],
            ['cat' => 'corner-sofas', 'name' => 'Helsinki corner sofa', 'slug' => 'helsinki-corner-sofa', 'sku' => 'NH-DIV-004', 'desc' => 'Corner sofa with ottoman, solid pine frame, minimalist design.', 'qty' => 4, 'res' => 1],
            ['cat' => 'sofas', 'name' => 'Stockholm straight sofa', 'slug' => 'stockholm-straight-sofa', 'sku' => 'NH-DIV-005', 'desc' => 'Straight sofa with tall legs, sand-toned velvet upholstery.', 'qty' => 0, 'res' => 0],
            ['cat' => 'beds', 'name' => 'Bergen bed 160×200', 'slug' => 'bergen-bed-160', 'sku' => 'NH-KRO-001', 'desc' => 'Double bed in light wood with an upholstered headboard.', 'qty' => 7, 'res' => 1],
            ['cat' => 'beds', 'name' => 'Arvika bed 140×200', 'slug' => 'arvika-bed-140', 'sku' => 'NH-KRO-002', 'desc' => 'Small double bed with slatted base, Scandinavian style.', 'qty' => 9, 'res' => 0],
            ['cat' => 'beds', 'name' => 'Sogne bed 180×200', 'slug' => 'sogne-bed-180', 'sku' => 'NH-KRO-003', 'desc' => 'Large king-size bed with a wooden slat headboard.', 'qty' => 3, 'res' => 1],
            ['cat' => 'tables', 'name' => 'Fjord dining table', 'slug' => 'fjord-dining-table', 'sku' => 'NH-STO-001', 'desc' => 'Extendable dining table for 6 people, oak veneer.', 'qty' => 12, 'res' => 2],
            ['cat' => 'tables', 'name' => 'Loften coffee table', 'slug' => 'loften-coffee-table', 'sku' => 'NH-STO-002', 'desc' => 'Coffee table in light ash wood with a lower shelf.', 'qty' => 15, 'res' => 3],
            ['cat' => 'tables', 'name' => 'Narvik side table', 'slug' => 'narvik-side-table', 'sku' => 'NH-STO-003', 'desc' => 'Round side table, beech legs, white matte top.', 'qty' => 0, 'res' => 0],
            ['cat' => 'chairs', 'name' => 'Scandi chair', 'slug' => 'scandi-chair', 'sku' => 'NH-STU-001', 'desc' => 'Dining chair in solid birch with a padded seat.', 'qty' => 30, 'res' => 4],
            ['cat' => 'chairs', 'name' => 'Tromso bar stool', 'slug' => 'tromso-bar-stool', 'sku' => 'NH-STU-002', 'desc' => 'Bar stool, 65 cm seat height, faux leather seat.', 'qty' => 18, 'res' => 2],
        ];

        $this->products($store, $products);
    }

    private function seedWarmLight(): void
    {
        $store = Store::query()->create([
            'name' => 'Warm Light',
            'slug' => 'warm-light',
        ]);

        $chandeliers = $this->category($store, 'Chandeliers', 'chandeliers');
        $floorLamps = $this->category($store, 'Floor lamps', 'floor-lamps');
        $tableLamps = $this->category($store, 'Table lamps', 'table-lamps');
        $wallLights = $this->category($store, 'Wall lights', 'wall-lights');

        $products = [
            ['cat' => 'chandeliers', 'name' => 'Aurora chandelier', 'slug' => 'aurora-chandelier', 'sku' => 'SV-LYU-001', 'desc' => 'Ceiling chandelier with 5 frosted glass shades and brass accents.', 'qty' => 10, 'res' => 1],
            ['cat' => 'chandeliers', 'name' => 'Minimal chandelier', 'slug' => 'minimal-chandelier', 'sku' => 'SV-LYU-002', 'desc' => 'Sleek 3-light chandelier in black metal, for the living room.', 'qty' => 14, 'res' => 2],
            ['cat' => 'chandeliers', 'name' => 'Daisy chandelier', 'slug' => 'daisy-chandelier', 'sku' => 'SV-LYU-003', 'desc' => 'Pendant chandelier with white opal glass shades.', 'qty' => 6, 'res' => 0],
            ['cat' => 'floor-lamps', 'name' => 'Twilight floor lamp', 'slug' => 'twilight-floor-lamp', 'sku' => 'SV-TOR-001', 'desc' => 'Tall floor lamp with a fabric shade, dimmable.', 'qty' => 9, 'res' => 1],
            ['cat' => 'floor-lamps', 'name' => 'Arc floor lamp', 'slug' => 'arc-floor-lamp', 'sku' => 'SV-TOR-002', 'desc' => 'Arc floor lamp over an armchair, metal frame, adjustable height.', 'qty' => 5, 'res' => 1],
            ['cat' => 'floor-lamps', 'name' => 'Hygge floor lamp', 'slug' => 'hygge-floor-lamp', 'sku' => 'SV-TOR-003', 'desc' => 'Floor lamp with warm diffused light, wooden tripod base.', 'qty' => 0, 'res' => 0],
            ['cat' => 'table-lamps', 'name' => 'Reader table lamp', 'slug' => 'reader-table-lamp', 'sku' => 'SV-NAS-001', 'desc' => 'Desk lamp with a swivel shade, E27 bulb.', 'qty' => 22, 'res' => 3],
            ['cat' => 'table-lamps', 'name' => 'Night glow table lamp', 'slug' => 'night-glow-table-lamp', 'sku' => 'SV-NAS-002', 'desc' => 'Compact lamp with warm glow and a touch switch.', 'qty' => 28, 'res' => 4],
            ['cat' => 'wall-lights', 'name' => 'Wave wall light', 'slug' => 'wave-wall-light', 'sku' => 'SV-BRA-001', 'desc' => 'Wall sconce with a curved shade, for bedroom and hallway.', 'qty' => 16, 'res' => 2],
            ['cat' => 'wall-lights', 'name' => 'Glow mirror light', 'slug' => 'glow-mirror-light', 'sku' => 'SV-BRA-002', 'desc' => 'LED bathroom mirror light, neutral white.', 'qty' => 12, 'res' => 1],
        ];

        $this->products($store, $products);
    }

    private function seedSoftLinen(): void
    {
        $store = Store::query()->create([
            'name' => 'Soft Linen',
            'slug' => 'soft-linen',
        ]);

        $bedding = $this->category($store, 'Bedding', 'bedding');
        $blankets = $this->category($store, 'Blankets & throws', 'blankets-throws');
        $curtains = $this->category($store, 'Curtains', 'curtains');
        $towels = $this->category($store, 'Towels', 'towels');

        $products = [
            ['cat' => 'bedding', 'name' => 'Cloud duvet set, king', 'slug' => 'cloud-duvet-set-king', 'sku' => 'TX-BEL-001', 'desc' => 'Sateen king-size bedding set, 100% cotton.', 'qty' => 25, 'res' => 3],
            ['cat' => 'bedding', 'name' => 'Linen duvet set, single', 'slug' => 'linen-duvet-set-single', 'sku' => 'TX-BEL-002', 'desc' => 'Linen bedding set, breathable fabric, natural colour.', 'qty' => 18, 'res' => 2],
            ['cat' => 'bedding', 'name' => 'North duvet set, double', 'slug' => 'north-duvet-set-double', 'sku' => 'TX-BEL-003', 'desc' => 'Double percale bedding set with a geometric print.', 'qty' => 0, 'res' => 0],
            ['cat' => 'blankets-throws', 'name' => 'Hygge throw 150×200', 'slug' => 'hygge-throw-150', 'sku' => 'TX-PLE-001', 'desc' => 'Knitted merino wool throw, cream colour.', 'qty' => 14, 'res' => 1],
            ['cat' => 'blankets-throws', 'name' => 'Granite bedspread 220×240', 'slug' => 'granite-bedspread', 'sku' => 'TX-PLE-002', 'desc' => 'Quilted double-sided bedspread for a king-size bed.', 'qty' => 9, 'res' => 2],
            ['cat' => 'blankets-throws', 'name' => 'Plaid throw 130×170', 'slug' => 'plaid-throw', 'sku' => 'TX-PLE-003', 'desc' => 'Soft fleece throw in a tartan check, for sofa and cottage.', 'qty' => 30, 'res' => 4],
            ['cat' => 'curtains', 'name' => 'Mist curtains 250 cm', 'slug' => 'mist-curtains', 'sku' => 'TX-SHT-001', 'desc' => 'Linen curtains on tape, semi-sheer, light grey.', 'qty' => 20, 'res' => 2],
            ['cat' => 'curtains', 'name' => 'Blackout curtains 300 cm', 'slug' => 'blackout-curtains', 'sku' => 'TX-SHT-002', 'desc' => 'Heavy blackout drapes, full light blocking.', 'qty' => 11, 'res' => 1],
            ['cat' => 'towels', 'name' => 'Spa towel 70×140', 'slug' => 'spa-towel', 'sku' => 'TX-POL-001', 'desc' => 'Terry towel, 500 gsm, 100% cotton.', 'qty' => 40, 'res' => 5],
            ['cat' => 'towels', 'name' => 'Linen towel set 50×90, 3 pcs', 'slug' => 'linen-towel-set', 'sku' => 'TX-POL-002', 'desc' => 'Set of linen kitchen towels, neutral shades.', 'qty' => 16, 'res' => 0],
        ];

        $this->products($store, $products);
    }

    private function seedCozyStorage(): void
    {
        $store = Store::query()->create([
            'name' => 'Cozy Storage',
            'slug' => 'cozy-storage',
        ]);

        $wardrobes = $this->category($store, 'Wardrobes', 'wardrobes');
        $shelving = $this->category($store, 'Shelving', 'shelving');
        $decor = $this->category($store, 'Decor', 'decor');

        $products = [
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
        ];

        $this->products($store, $products);
    }

    /**
     * @param  array<int, array{cat: string, name: string, slug: string, sku: string, desc: string, qty: int, res: int}>  $items
     */
    private function products(Store $store, array $items): void
    {
        $categories = $store->categories()->get()->keyBy('slug');

        foreach ($items as $item) {
            /** @var Category $category */
            $category = $categories->get($item['cat']);

            $product = Product::query()->create([
                'store_id' => $store->id,
                'category_id' => $category->id,
                'name' => $item['name'],
                'slug' => $item['slug'],
                'description' => $item['desc'],
                'sku' => $item['sku'],
            ]);

            Stock::query()->create([
                'product_id' => $product->id,
                'store_id' => $store->id,
                'quantity' => $item['qty'],
                'reserved' => $item['res'],
            ]);
        }
    }

    private function category(Store $store, string $name, string $slug, ?Category $parent = null): Category
    {
        return Category::query()->create([
            'store_id' => $store->id,
            'parent_id' => $parent?->id,
            'name' => $name,
            'slug' => $slug,
        ]);
    }
}
