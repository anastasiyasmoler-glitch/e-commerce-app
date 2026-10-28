<?php

namespace Database\Seeders\Stores;

use App\Models\Store;
use Database\Seeders\Concerns\SeedsCatalogDemo;
use Illuminate\Database\Seeder;

class WarmLightStoreSeeder extends Seeder
{
    use SeedsCatalogDemo;

    public function run(): void
    {
        $store = Store::query()->create([
            'name' => 'Warm Light',
            'slug' => 'warm-light',
        ]);

        $this->category($store, 'Chandeliers', 'chandeliers');
        $this->category($store, 'Floor lamps', 'floor-lamps');
        $this->category($store, 'Table lamps', 'table-lamps');
        $this->category($store, 'Wall lights', 'wall-lights');

        $this->seedStoreProducts($store, [
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
        ]);
    }
}
