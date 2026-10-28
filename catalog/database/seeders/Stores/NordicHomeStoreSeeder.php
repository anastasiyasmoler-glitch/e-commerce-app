<?php

namespace Database\Seeders\Stores;

use App\Models\Store;
use Database\Seeders\Concerns\SeedsCatalogDemo;
use Illuminate\Database\Seeder;

class NordicHomeStoreSeeder extends Seeder
{
    use SeedsCatalogDemo;

    public function run(): void
    {
        $store = Store::query()->create([
            'name' => 'Nordic Home',
            'slug' => 'nordic-home',
        ]);

        $sofas = $this->category($store, 'Sofas', 'sofas');
        $this->category($store, 'Straight sofas', 'straight-sofas', $sofas);
        $this->category($store, 'Corner sofas', 'corner-sofas', $sofas);
        $this->category($store, 'Beds', 'beds');
        $this->category($store, 'Tables', 'tables');
        $this->category($store, 'Chairs', 'chairs');

        $this->seedStoreProducts($store, [
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
        ]);
    }
}
