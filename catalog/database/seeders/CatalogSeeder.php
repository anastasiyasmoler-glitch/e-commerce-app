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

        $this->seedFurnitureStore();
        $this->seedElectronicsStore();
        $this->seedClothingStore();
    }

    private function seedFurnitureStore(): void
    {
        $store = Store::query()->create([
            'name' => 'Мебельный магазин',
            'slug' => 'mebel',
        ]);

        $divany = $this->category($store, 'Диваны', 'divany');
        $this->category($store, 'Прямые диваны', 'pryamye-divany', $divany);
        $this->category($store, 'Угловые диваны', 'uglovye-divany', $divany);
        $kresla = $this->category($store, 'Кресла', 'kresla');
        $stoly = $this->category($store, 'Столы и стулья', 'stoly-i-stulya');
        $shkafy = $this->category($store, 'Шкафы', 'shkafy');

        $products = [
            ['cat' => 'divany', 'name' => 'Диван «Лондон» прямой', 'slug' => 'divan-london-pryamoy', 'sku' => 'MEB-DIV-001', 'desc' => 'Прямой диван с раскладным механизмом, обивка велюр.', 'qty' => 12, 'res' => 2],
            ['cat' => 'divany', 'name' => 'Диван «Токио» прямой', 'slug' => 'divan-tokyo-pryamoy', 'sku' => 'MEB-DIV-002', 'desc' => 'Компактный прямой диван для гостиной, рогожка.', 'qty' => 8, 'res' => 1],
            ['cat' => 'divany', 'name' => 'Диван «Прага» угловой', 'slug' => 'divan-praga-uglovoy', 'sku' => 'MEB-DIV-003', 'desc' => 'Угловой диван с оттоманкой и нишей для белья.', 'qty' => 5, 'res' => 0],
            ['cat' => 'divany', 'name' => 'Диван «Осло» угловой', 'slug' => 'divan-oslo-uglovoy', 'sku' => 'MEB-DIV-004', 'desc' => 'Угловой диван, экокожа, панорамная раскладка.', 'qty' => 3, 'res' => 1],
            ['cat' => 'kresla', 'name' => 'Кресло «Барон»', 'slug' => 'kreslo-baron', 'sku' => 'MEB-KRE-001', 'desc' => 'Мягкое кресло с реклайнером, ткань шенилл.', 'qty' => 15, 'res' => 3],
            ['cat' => 'kresla', 'name' => 'Кресло-качалка «Норд»', 'slug' => 'kreslo-kachalka-nord', 'sku' => 'MEB-KRE-002', 'desc' => 'Кресло-качалка из массива берёзы.', 'qty' => 7, 'res' => 0],
            ['cat' => 'stoly-i-stulya', 'name' => 'Обеденный стол «Верона»', 'slug' => 'stol-verona', 'sku' => 'MEB-STO-001', 'desc' => 'Раздвижной обеденный стол на 6 персон, дуб.', 'qty' => 10, 'res' => 2],
            ['cat' => 'stoly-i-stulya', 'name' => 'Стул «Бистро»', 'slug' => 'stul-bistro', 'sku' => 'MEB-STO-002', 'desc' => 'Стул обеденный, металлический каркас, сиденье из дуба.', 'qty' => 40, 'res' => 5],
            ['cat' => 'shkafy', 'name' => 'Шкаф-купе «Атлант»', 'slug' => 'shkaf-kupe-atlant', 'sku' => 'MEB-SHK-001', 'desc' => 'Шкаф-купе трёхдверный 2200 мм, зеркальный фасад.', 'qty' => 6, 'res' => 1],
            ['cat' => 'shkafy', 'name' => 'Шкаф платяной «Сканди»', 'slug' => 'shkaf-skandi', 'sku' => 'MEB-SHK-002', 'desc' => 'Платяной шкаф двухдверный, стиль скандинавский минимализм.', 'qty' => 0, 'res' => 0],
        ];

        $this->products($store, $products);
    }

    private function seedElectronicsStore(): void
    {
        $store = Store::query()->create([
            'name' => 'Магазин электроники',
            'slug' => 'elektronika',
        ]);

        $smartfony = $this->category($store, 'Смартфоны', 'smartfony');
        $noutbuki = $this->category($store, 'Ноутбуки', 'noutbuki');
        $aksessuary = $this->category($store, 'Аксессуары', 'aksessuary');

        $products = [
            ['cat' => 'smartfony', 'name' => 'Смартфон Galaxy A55', 'slug' => 'smartfon-galaxy-a55', 'sku' => 'EL-SMA-001', 'desc' => 'Смартфон 6.6", 128 ГБ, 8 ГБ ОЗУ, две SIM-карты.', 'qty' => 25, 'res' => 4],
            ['cat' => 'smartfony', 'name' => 'Смартфон iPhone 15', 'slug' => 'smartfon-iphone-15', 'sku' => 'EL-SMA-002', 'desc' => 'Смартфон 6.1", 256 ГБ, камера 48 Мп.', 'qty' => 14, 'res' => 2],
            ['cat' => 'smartfony', 'name' => 'Смартфон Redmi Note 13', 'slug' => 'smartfon-redmi-note-13', 'sku' => 'EL-SMA-003', 'desc' => 'Смартфон 6.67", AMOLED, 120 Гц, 256 ГБ.', 'qty' => 30, 'res' => 5],
            ['cat' => 'noutbuki', 'name' => 'Ноутбук ThinkPad E16', 'slug' => 'noutbuk-thinkpad-e16', 'sku' => 'EL-NOU-001', 'desc' => 'Ноутбук 16", Core i5, 16 ГБ ОЗУ, SSD 512 ГБ.', 'qty' => 9, 'res' => 1],
            ['cat' => 'noutbuki', 'name' => 'Ноутбук MacBook Air 13', 'slug' => 'noutbuk-macbook-air-13', 'sku' => 'EL-NOU-002', 'desc' => 'Ноутбук 13.6", чип M3, 8 ГБ ОЗУ, SSD 256 ГБ.', 'qty' => 7, 'res' => 2],
            ['cat' => 'noutbuki', 'name' => 'Ноутбук ASUS Vivobook 15', 'slug' => 'noutbuk-asus-vivobook-15', 'sku' => 'EL-NOU-003', 'desc' => 'Ноутбук 15.6", Ryzen 5, 16 ГБ ОЗУ, SSD 512 ГБ.', 'qty' => 0, 'res' => 0],
            ['cat' => 'aksessuary', 'name' => 'Наушники беспроводные AirDots', 'slug' => 'naushniki-airdots', 'sku' => 'EL-AKS-001', 'desc' => 'TWS-наушники с шумоподавлением, до 30 часов работы.', 'qty' => 50, 'res' => 6],
            ['cat' => 'aksessuary', 'name' => 'Зарядное устройство USB-C 65 Вт', 'slug' => 'zaryadnoe-ustroystvo-65w', 'sku' => 'EL-AKS-002', 'desc' => 'Сетевое зарядное устройство GaN, 65 Вт, USB-C.', 'qty' => 35, 'res' => 3],
        ];

        $this->products($store, $products);
    }

    private function seedClothingStore(): void
    {
        $store = Store::query()->create([
            'name' => 'Магазин одежды',
            'slug' => 'odezhda',
        ]);

        $muzhskaya = $this->category($store, 'Мужская одежда', 'muzhskaya-odezhda');
        $zhenskaya = $this->category($store, 'Женская одежда', 'zhenskaya-odezhda');
        $platya = $this->category($store, 'Платья', 'platya', $zhenskaya);
        $verkhnyaya = $this->category($store, 'Верхняя одежда', 'verkhnyaya-odezhda', $zhenskaya);

        $products = [
            ['cat' => 'muzhskaya-odezhda', 'name' => 'Футболка мужская базовая', 'slug' => 'futbolka-muzhskaya', 'sku' => 'OD-MUZ-001', 'desc' => 'Хлопковая футболка, плотность 180 г/м².', 'qty' => 45, 'res' => 5],
            ['cat' => 'muzhskaya-odezhda', 'name' => 'Джинсы мужские «Слим»', 'slug' => 'dzhinsy-muzhskie-slim', 'sku' => 'OD-MUZ-002', 'desc' => 'Джинсы слим-фит, стрейч-деним.', 'qty' => 20, 'res' => 2],
            ['cat' => 'muzhskaya-odezhda', 'name' => 'Куртка мужская демисезонная', 'slug' => 'kurtka-muzhskaya', 'sku' => 'OD-MUZ-003', 'desc' => 'Демисезонная куртка с капюшоном, водоотталкивающая пропитка.', 'qty' => 8, 'res' => 1],
            ['cat' => 'platya', 'name' => 'Платье «Милана»', 'slug' => 'plate-milana', 'sku' => 'OD-ZHE-001', 'desc' => 'Платье миди, вискоза, цветочный принт.', 'qty' => 12, 'res' => 2],
            ['cat' => 'platya', 'name' => 'Платье вечернее «Аврора»', 'slug' => 'plate-avrora', 'sku' => 'OD-ZHE-002', 'desc' => 'Вечернее платье макси, атлас, разрез.', 'qty' => 5, 'res' => 0],
            ['cat' => 'verkhnyaya-odezhda', 'name' => 'Пальто женское «Эльза»', 'slug' => 'palto-zhenskoe-elza', 'sku' => 'OD-ZHE-003', 'desc' => 'Пальто прямого кроя, шерстяная ткань, пояс в комплекте.', 'qty' => 0, 'res' => 0],
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
