<?php

namespace Database\Seeders\Data;

/**
 * Hand-picked demo image filename per product slug (root of the product-images bucket).
 */
final class ProductImageMap
{
    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
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
    }
}
