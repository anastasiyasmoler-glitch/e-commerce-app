<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Stock;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CatalogSeeder extends Seeder
{
    /** @var list<int> */
    private const PRICE_TIERS_CENTS = [
        1999, 3499, 4999, 7900, 12900, 18900, 24900, 39900, 54900, 89900,
        129900, 179900, 249900, 399900, 599900, 999900, 1499900,
    ];

    /**
     * Seed demo catalog data (stores, categories, products, stocks).
     *
     * The seeder is idempotent: all existing catalog rows are removed
     * in FK-safe order before the demo data is created.
     */
    public function run(): void
    {
        $this->wipeSeedProductImages();

        ProductAttributeValue::query()->delete();
        Stock::query()->delete();
        Product::query()->delete();
        Attribute::query()->delete();
        Category::query()->delete();
        Store::query()->delete();

        $this->seedNordicHome();
        $this->seedWarmLight();
        $this->seedSoftLinen();
        $this->seedCozyStorage();

        Cache::forget('catalog:stores');
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
            ['cat' => 'chairs', 'name' => 'Pine dining stool', 'slug' => 'pine-dining-stool', 'sku' => 'NH-STU-003', 'desc' => 'Simple pine stool for kitchen islands.', 'qty' => 24, 'res' => 1, 'price' => 1999],
            ['cat' => 'sofas', 'name' => 'Aurora leather sofa', 'slug' => 'aurora-leather-sofa', 'sku' => 'NH-DIV-006', 'desc' => 'Full-grain leather three-seat sofa, walnut legs.', 'qty' => 2, 'res' => 0, 'price' => 2499900],
            ['cat' => 'tables', 'name' => 'Birch sideboard', 'slug' => 'birch-sideboard', 'sku' => 'NH-STO-004', 'desc' => 'Low sideboard with two doors, birch veneer.', 'qty' => 6, 'res' => 1, 'price' => 49900],
            ['cat' => 'sofas', 'name' => 'Tromso fabric sofa', 'slug' => 'tromso-fabric-sofa', 'sku' => 'NH-DIV-007', 'desc' => 'Deep-seat fabric sofa, washable covers, oak legs.', 'qty' => 5, 'res' => 0],
            ['cat' => 'straight-sofas', 'name' => 'Aalto two-seat sofa', 'slug' => 'aalto-two-seat-sofa', 'sku' => 'NH-DIV-008', 'desc' => 'Compact two-seat sofa for apartments, boucle fabric.', 'qty' => 7, 'res' => 2],
            ['cat' => 'corner-sofas', 'name' => 'Nordkapp corner sofa', 'slug' => 'nordkapp-corner-sofa', 'sku' => 'NH-DIV-009', 'desc' => 'Large corner sofa with lounging module, grey weave.', 'qty' => 3, 'res' => 1],
            ['cat' => 'beds', 'name' => 'Lillehammer bed 160×200', 'slug' => 'lillehammer-bed-160', 'sku' => 'NH-KRO-004', 'desc' => 'Upholstered bed with storage drawers, oatmeal fabric.', 'qty' => 6, 'res' => 0],
            ['cat' => 'tables', 'name' => 'Hamar dining table 180 cm', 'slug' => 'hamar-dining-table', 'sku' => 'NH-STO-005', 'desc' => 'Solid oak dining table, seats up to 8 people.', 'qty' => 8, 'res' => 1],
            ['cat' => 'chairs', 'name' => 'Bodo lounge chair', 'slug' => 'bodo-lounge-chair', 'sku' => 'NH-STU-004', 'desc' => 'Lounge chair with footrest, wool blend upholstery.', 'qty' => 11, 'res' => 0],
            ['cat' => 'chairs', 'name' => 'Molde dining chair', 'slug' => 'molde-dining-chair', 'sku' => 'NH-STU-005', 'desc' => 'Stackable dining chair in molded oak veneer.', 'qty' => 26, 'res' => 3],
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
            ['cat' => 'table-lamps', 'name' => 'Clip-on reading light', 'slug' => 'clip-on-reading-light', 'sku' => 'SV-NAS-003', 'desc' => 'USB clip lamp for desks and headboards.', 'qty' => 35, 'res' => 2, 'price' => 2499],
            ['cat' => 'chandeliers', 'name' => 'Grand ballroom chandelier', 'slug' => 'grand-ballroom-chandelier', 'sku' => 'SV-LYU-004', 'desc' => 'Twelve-light brass chandelier with crystal drops.', 'qty' => 1, 'res' => 0, 'price' => 1899900],
            ['cat' => 'floor-lamps', 'name' => 'Studio floor lamp', 'slug' => 'studio-floor-lamp', 'sku' => 'SV-TOR-004', 'desc' => 'Adjustable task floor lamp, matte black.', 'qty' => 11, 'res' => 0, 'price' => 12900],
            ['cat' => 'chandeliers', 'name' => 'Halo ring chandelier', 'slug' => 'halo-ring-chandelier', 'sku' => 'SV-LYU-005', 'desc' => 'LED ring chandelier, dimmable 3000K warm white.', 'qty' => 8, 'res' => 1],
            ['cat' => 'chandeliers', 'name' => 'Nord pendant cluster', 'slug' => 'nord-pendant-cluster', 'sku' => 'SV-LYU-006', 'desc' => 'Cluster of three opal glass pendants on one canopy.', 'qty' => 13, 'res' => 2],
            ['cat' => 'floor-lamps', 'name' => 'Polar arc floor lamp', 'slug' => 'polar-arc-floor-lamp', 'sku' => 'SV-TOR-005', 'desc' => 'Arc floor lamp in brushed steel, marble base.', 'qty' => 6, 'res' => 0],
            ['cat' => 'floor-lamps', 'name' => 'Breeze floor lamp', 'slug' => 'breeze-floor-lamp', 'sku' => 'SV-TOR-006', 'desc' => 'Lightweight floor lamp, paper shade, beech legs.', 'qty' => 14, 'res' => 1],
            ['cat' => 'table-lamps', 'name' => 'Luna bedside lamp', 'slug' => 'luna-bedside-lamp', 'sku' => 'SV-NAS-004', 'desc' => 'Moon-shaped bedside lamp, touch dimmer, USB-C.', 'qty' => 19, 'res' => 2],
            ['cat' => 'table-lamps', 'name' => 'Moss ceramic table lamp', 'slug' => 'moss-ceramic-table-lamp', 'sku' => 'SV-NAS-005', 'desc' => 'Ceramic base table lamp in sage green glaze.', 'qty' => 16, 'res' => 0],
            ['cat' => 'wall-lights', 'name' => 'Step wall light', 'slug' => 'step-wall-light', 'sku' => 'SV-BRA-003', 'desc' => 'Minimal LED wall sconce, up-down light, aluminium.', 'qty' => 21, 'res' => 1],
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
            ['cat' => 'bedding', 'name' => 'Basic cotton pillowcase pair', 'slug' => 'basic-cotton-pillowcase-pair', 'sku' => 'TX-BEL-004', 'desc' => 'Two white cotton pillowcases, envelope closure.', 'qty' => 50, 'res' => 5, 'price' => 1499],
            ['cat' => 'blankets-throws', 'name' => 'Cashmere throw limited', 'slug' => 'cashmere-throw-limited', 'sku' => 'TX-PLE-004', 'desc' => 'Lightweight cashmere throw, charcoal grey.', 'qty' => 3, 'res' => 0, 'price' => 899900],
            ['cat' => 'curtains', 'name' => 'Sheer voile panels', 'slug' => 'sheer-voile-panels', 'sku' => 'TX-SHT-003', 'desc' => 'Pair of sheer voile curtain panels, 140×250 cm.', 'qty' => 22, 'res' => 1, 'price' => 7900],
            ['cat' => 'bedding', 'name' => 'Arctic duvet set, double', 'slug' => 'arctic-duvet-set-double', 'sku' => 'TX-BEL-005', 'desc' => 'Brushed cotton flannel bedding set for cold nights.', 'qty' => 17, 'res' => 2],
            ['cat' => 'bedding', 'name' => 'Sand percale duvet set, king', 'slug' => 'sand-percale-duvet-set', 'sku' => 'TX-BEL-006', 'desc' => 'Crisp percale king set, 300 thread count, sand tone.', 'qty' => 12, 'res' => 0],
            ['cat' => 'blankets-throws', 'name' => 'Moss wool throw 150×200', 'slug' => 'moss-wool-throw', 'sku' => 'TX-PLE-005', 'desc' => 'Lambswool throw in moss green with fringe.', 'qty' => 21, 'res' => 3],
            ['cat' => 'blankets-throws', 'name' => 'Nordic bedspread 200×220', 'slug' => 'nordic-bedspread', 'sku' => 'TX-PLE-006', 'desc' => 'Jacquard bedspread, reversible, stone and white.', 'qty' => 10, 'res' => 1],
            ['cat' => 'curtains', 'name' => 'Dawn linen curtains 280 cm', 'slug' => 'dawn-linen-curtains', 'sku' => 'TX-SHT-004', 'desc' => 'Heavy linen curtains, eyelet header, natural flax.', 'qty' => 13, 'res' => 0],
            ['cat' => 'towels', 'name' => 'Bath towel duo 70×140', 'slug' => 'bath-towel-duo', 'sku' => 'TX-POL-003', 'desc' => 'Two plush bath towels, 600 gsm, eucalyptus dye.', 'qty' => 28, 'res' => 2],
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
            ['cat' => 'decor', 'name' => 'Ceramic trinket tray', 'slug' => 'ceramic-trinket-tray', 'sku' => 'HR-DEK-005', 'desc' => 'Small glazed tray for keys and jewellery.', 'qty' => 40, 'res' => 2, 'price' => 1999],
            ['cat' => 'wardrobes', 'name' => 'Walk-in wardrobe system', 'slug' => 'walk-in-wardrobe-system', 'sku' => 'HR-SHK-004', 'desc' => 'Modular walk-in wardrobe kit, white and oak.', 'qty' => 1, 'res' => 0, 'price' => 3299900],
            ['cat' => 'shelving', 'name' => 'Corner shelf unit', 'slug' => 'corner-shelf-unit', 'sku' => 'HR-STE-004', 'desc' => 'Five-tier corner shelving, 200 cm height.', 'qty' => 8, 'res' => 0, 'price' => 34900],
            ['cat' => 'wardrobes', 'name' => 'Berg wardrobe 160 cm', 'slug' => 'berg-wardrobe-160', 'sku' => 'HR-SHK-005', 'desc' => 'Two-door wardrobe with soft-close doors, oak foil.', 'qty' => 6, 'res' => 1],
            ['cat' => 'wardrobes', 'name' => 'Norr chest of drawers 120 cm', 'slug' => 'norr-chest-of-drawers', 'sku' => 'HR-SHK-006', 'desc' => 'Wide six-drawer chest, walnut veneer, brass handles.', 'qty' => 9, 'res' => 0],
            ['cat' => 'shelving', 'name' => 'Arctic ladder shelf', 'slug' => 'arctic-ladder-shelf', 'sku' => 'HR-STE-005', 'desc' => 'Leaning ladder shelf in white lacquered MDF, 5 tiers.', 'qty' => 17, 'res' => 2],
            ['cat' => 'shelving', 'name' => 'Hanging shelf oak 90 cm', 'slug' => 'hanging-shelf-oak', 'sku' => 'HR-STE-006', 'desc' => 'Wall-mounted oak shelf with leather straps.', 'qty' => 24, 'res' => 3],
            ['cat' => 'decor', 'name' => 'Nordic candle trio', 'slug' => 'nordic-candle-trio', 'sku' => 'HR-DEK-006', 'desc' => 'Three scented candles: sea salt, birch, amber.', 'qty' => 31, 'res' => 2],
            ['cat' => 'decor', 'name' => 'Stone table clock', 'slug' => 'stone-table-clock', 'sku' => 'HR-DEK-007', 'desc' => 'Silent sweep clock in a travertine-look case.', 'qty' => 15, 'res' => 0],
        ];

        $this->products($store, $products);
    }

    /**
     * @param  array<int, array{cat: string, name: string, slug: string, sku: string, desc: string, qty: int, res: int, price?: int, attributes?: array<string, string>}>  $items
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
                'price' => $this->priceCentsForSeedItem($item),
                'is_published' => true,
            ]);

            $this->seedProductImage($store, $product, $item);

            $attributeMap = $item['attributes'] ?? $this->defaultAttributesForCategory($item['cat']);
            $this->syncProductAttributes($store, $product, $attributeMap);

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

    /**
     * @param  array<string, string>  $attributeMap
     */
    private function syncProductAttributes(Store $store, Product $product, array $attributeMap): void
    {
        foreach ($attributeMap as $code => $value) {
            $attribute = Attribute::query()->firstOrCreate(
                [
                    'store_id' => $store->id,
                    'code' => $code,
                ],
                [
                    'name' => ucfirst(str_replace('_', ' ', $code)),
                ],
            );

            ProductAttributeValue::query()->create([
                'product_id' => $product->id,
                'attribute_id' => $attribute->id,
                'value' => $value,
            ]);
        }
    }

    private function wipeSeedProductImages(): void
    {
        if (config('filesystems.default') !== 's3') {
            return;
        }

        Storage::disk('s3')->deleteDirectory('products');
    }

    /**
     * @param  array{sku: string, price?: int}  $item
     */
    private function priceCentsForSeedItem(array $item): int
    {
        if (isset($item['price'])) {
            return (int) $item['price'];
        }

        $index = abs(crc32((string) $item['sku'])) % count(self::PRICE_TIERS_CENTS);

        return self::PRICE_TIERS_CENTS[$index];
    }

    /**
     * @return array<string, string>
     */
    private function defaultAttributesForCategory(string $categorySlug): array
    {
        $palette = ['natural', 'beige', 'grey', 'black', 'white', 'oak', 'walnut', 'sage', 'terracotta'];

        return match (true) {
            str_contains($categorySlug, 'sofa'), str_contains($categorySlug, 'bed') => [
                'color' => $palette[abs(crc32($categorySlug)) % count($palette)],
                'material' => 'fabric',
                'width_cm' => '200',
            ],
            str_contains($categorySlug, 'lamp'), str_contains($categorySlug, 'chandelier'), str_contains($categorySlug, 'light') => [
                'color' => 'brass',
                'bulb_type' => 'E27',
                'wattage' => '40',
            ],
            str_contains($categorySlug, 'bedding'), str_contains($categorySlug, 'blanket'), str_contains($categorySlug, 'curtain'), str_contains($categorySlug, 'towel') => [
                'color' => 'cream',
                'material' => 'cotton',
                'size' => 'king',
            ],
            default => [
                'color' => 'natural',
                'material' => 'wood',
                'width_cm' => '120',
            ],
        };
    }

    /**
     * Hand-picked image per product, matched to the product name.
     * All files live at the root of the `product-images` bucket.
     *
     * @var array<string, string>
     */
    private const PRODUCT_IMAGES = [
        // Nordic Home
        'oslo-straight-sofa' => 'sofa-grey.jpg',
        'copenhagen-sofa' => 'sofa-modern.jpg',
        'malmo-sofa' => 'sofa-2.jpg',
        'helsinki-corner-sofa' => 'sofa-green.jpg',
        'stockholm-straight-sofa' => 'sofa-beige.jpg',
        'aurora-leather-sofa' => 'armchair-leather.jpg',
        'tromso-fabric-sofa' => 'throw-sofa.jpg',
        'aalto-two-seat-sofa' => 'sofa-velvet.jpg',
        'nordkapp-corner-sofa' => 'armchair-pink.jpg',
        'bergen-bed-160' => 'bed-minimal.jpg',
        'arvika-bed-140' => 'bedroom-interior.jpg',
        'sogne-bed-180' => 'bed-linen.jpg',
        'lillehammer-bed-160' => 'bedroom-2.jpg',
        'fjord-dining-table' => 'dining-table.jpg',
        'loften-coffee-table' => 'coffee-table.jpg',
        'narvik-side-table' => 'table-console.jpg',
        'birch-sideboard' => 'shelf-wall.jpg',
        'hamar-dining-table' => 'table-wood.jpg',
        'scandi-chair' => 'chair-eames.jpg',
        'tromso-bar-stool' => 'stools-kitchen.jpg',
        'pine-dining-stool' => 'stool.jpg',
        'bodo-lounge-chair' => 'chair-lounge.jpg',
        'molde-dining-chair' => 'chair-wood.jpg',
        // Warm Light
        'aurora-chandelier' => 'lamps-bedroom.jpg',
        'minimal-chandelier' => 'pendant-kitchen.jpg',
        'daisy-chandelier' => 'cabinet.jpg',
        'grand-ballroom-chandelier' => 'kitchen-interior.jpg',
        'halo-ring-chandelier' => 'light-bulb.jpg',
        'nord-pendant-cluster' => 'chair-dining.jpg',
        'twilight-floor-lamp' => 'lamp-floor.jpg',
        'arc-floor-lamp' => 'lamp-arched.jpg',
        'hygge-floor-lamp' => 'lamp-arc-room.jpg',
        'studio-floor-lamp' => 'lamp-desk.jpg',
        'polar-arc-floor-lamp' => 'curtains.jpg',
        'breeze-floor-lamp' => 'living-2.jpg',
        'reader-table-lamp' => 'desk-window.jpg',
        'night-glow-table-lamp' => 'bedroom-4.jpg',
        'luna-bedside-lamp' => 'bedroom-3.jpg',
        'moss-ceramic-table-lamp' => 'bedding-stripes.jpg',
        'clip-on-reading-light' => 'desk-minimal.jpg',
        'wave-wall-light' => 'living-3.jpg',
        'step-wall-light' => 'desk-office.jpg',
        // Soft Linen
        'cloud-duvet-set-king' => 'bed-modern.jpg',
        'linen-duvet-set-single' => 'bedroom-5.jpg',
        'north-duvet-set-double' => 'bed-hotel.jpg',
        'arctic-duvet-set-double' => 'throw-knit.jpg',
        'sand-percale-duvet-set' => 'bedroom-3.jpg',
        'basic-cotton-pillowcase-pair' => 'tv-stand.jpg',
        'hygge-throw-150' => 'throw-2.jpg',
        'granite-bedspread' => 'bedroom-2.jpg',
        'plaid-throw' => 'loft-living.jpg',
        'cashmere-throw-limited' => 'throw-knit.jpg',
        'moss-wool-throw' => 'bedroom-interior.jpg',
        'nordic-bedspread' => 'bed-linen.jpg',
        'mist-curtains' => 'curtains.jpg',
        'blackout-curtains' => 'bedroom-4.jpg',
        'dawn-linen-curtains' => 'living-2.jpg',
        'sheer-voile-panels' => 'desk-window.jpg',
        'spa-towel' => 'bathroom.jpg',
        'bath-towel-duo' => 'bathroom.jpg',
        'linen-towel-set' => 'bathroom.jpg',
        // Cozy Storage
        'alba-sliding-wardrobe' => 'wardrobe.jpg',
        'berg-wardrobe-160' => 'tv-stand.jpg',
        'scandi-wardrobe' => 'cabinet.jpg',
        'walk-in-wardrobe-system' => 'kitchen-interior.jpg',
        'nord-chest-of-drawers' => 'shelf-wall.jpg',
        'norr-chest-of-drawers' => 'dining-table.jpg',
        'modul-shelving-unit' => 'bookshelf.jpg',
        'loft-metal-shelving' => 'desk-office.jpg',
        'corner-shelf-unit' => 'shelf-wood.jpg',
        'arctic-ladder-shelf' => 'bookshelf.jpg',
        'veranda-etagere' => 'plant-stand.jpg',
        'hanging-shelf-oak' => 'shelf-wall.jpg',
        'oval-mirror' => 'bed-linen.jpg',
        'flora-vase-set' => 'vases.jpg',
        'forest-scented-candles' => 'candles.jpg',
        'nordic-candle-trio' => 'candles.jpg',
        'ceramic-trinket-tray' => 'table-console.jpg',
        'stone-table-clock' => 'clock.jpg',
        'woven-storage-basket' => 'loft-living.jpg',
    ];

    /**
     * @param  array{cat: string, name: string, slug: string, sku: string, desc: string, qty: int, res: int, price?: int, attributes?: array<string, string>}  $item
     */
    private function seedProductImage(Store $store, Product $product, array $item): void
    {
        if (config('filesystems.default') !== 's3') {
            return;
        }

        $image = self::PRODUCT_IMAGES[$product->slug] ?? 'sofa-modern.jpg';

        $product->forceFill(['image' => $image])->save();
    }
}
