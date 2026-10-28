<?php

namespace Database\Seeders\Stores;

use App\Models\Store;
use Database\Seeders\Concerns\SeedsCatalogDemo;
use Illuminate\Database\Seeder;

class SoftLinenStoreSeeder extends Seeder
{
    use SeedsCatalogDemo;

    public function run(): void
    {
        $store = Store::query()->create([
            'name' => 'Soft Linen',
            'slug' => 'soft-linen',
        ]);

        $this->category($store, 'Bedding', 'bedding');
        $this->category($store, 'Blankets & throws', 'blankets-throws');
        $this->category($store, 'Curtains', 'curtains');
        $this->category($store, 'Towels', 'towels');

        $this->seedStoreProducts($store, [
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
        ]);
    }
}
