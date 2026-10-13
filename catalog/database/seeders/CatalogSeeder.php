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
        $this->seedTeplyySvet();
        $this->seedDomashniyTekstil();
        $this->seedUyutIKhranenie();
    }

    private function seedNordicHome(): void
    {
        $store = Store::query()->create([
            'name' => 'Nordic Home',
            'slug' => 'nordic-home',
        ]);

        $divany = $this->category($store, 'Диваны', 'divany');
        $this->category($store, 'Прямые диваны', 'pryamye-divany', $divany);
        $this->category($store, 'Угловые диваны', 'uglovye-divany', $divany);
        $krovati = $this->category($store, 'Кровати', 'krovati');
        $stoly = $this->category($store, 'Столы', 'stoly');
        $stulya = $this->category($store, 'Стулья', 'stulya');

        $products = [
            ['cat' => 'divany', 'name' => 'Диван «Осло» прямой', 'slug' => 'divan-oslo-pryamoy', 'sku' => 'NH-DIV-001', 'desc' => 'Прямой диван в скандинавском стиле, светлый дуб, обивка из рогожки.', 'qty' => 8, 'res' => 1],
            ['cat' => 'pryamye-divany', 'name' => 'Диван «Копенгаген»', 'slug' => 'divan-kopengagen', 'sku' => 'NH-DIV-002', 'desc' => 'Прямой диван-трёхместка, ножки из бука, съёмные чехлы серого цвета.', 'qty' => 6, 'res' => 2],
            ['cat' => 'pryamye-divany', 'name' => 'Диван «Мальмё»', 'slug' => 'divan-malmo', 'sku' => 'NH-DIV-003', 'desc' => 'Компактный прямой диван для небольшой гостиной, светлая ткань.', 'qty' => 10, 'res' => 0],
            ['cat' => 'uglovye-divany', 'name' => 'Диван «Хельсинки» угловой', 'slug' => 'divan-helsinki-uglovoy', 'sku' => 'NH-DIV-004', 'desc' => 'Угловой диван с оттоманкой, каркас из массива сосны, минималистичный дизайн.', 'qty' => 4, 'res' => 1],
            ['cat' => 'divany', 'name' => 'Диван «Стокгольм» прямой', 'slug' => 'divan-stokgolm-pryamoy', 'sku' => 'NH-DIV-005', 'desc' => 'Прямой диван с высокими ножками, велюр песочного оттенка.', 'qty' => 0, 'res' => 0],
            ['cat' => 'krovati', 'name' => 'Кровать «Берген» 160×200', 'slug' => 'krovat-bergen-160', 'sku' => 'NH-KRO-001', 'desc' => 'Двуспальная кровать из светлого дерева с мягким изголовьем.', 'qty' => 7, 'res' => 1],
            ['cat' => 'krovati', 'name' => 'Кровать «Арвика» 140×200', 'slug' => 'krovat-arvika-140', 'sku' => 'NH-KRO-002', 'desc' => 'Кровать односпальная/полуторная, основание с ламелями, сканди-стиль.', 'qty' => 9, 'res' => 0],
            ['cat' => 'krovati', 'name' => 'Кровать «Согне» 180×200', 'slug' => 'krovat-sogne-180', 'sku' => 'NH-KRO-003', 'desc' => 'Большая двуспальная кровать, изголовье с деревянными рейками.', 'qty' => 3, 'res' => 1],
            ['cat' => 'stoly', 'name' => 'Обеденный стол «Фьорд»', 'slug' => 'stol-ford', 'sku' => 'NH-STO-001', 'desc' => 'Раздвижной обеденный стол на 6 персон, дубовый шпон.', 'qty' => 12, 'res' => 2],
            ['cat' => 'stoly', 'name' => 'Журнальный столик «Лофтен»', 'slug' => 'stolik-loften', 'sku' => 'NH-STO-002', 'desc' => 'Журнальный столик из светлого ясеня с полкой.', 'qty' => 15, 'res' => 3],
            ['cat' => 'stoly', 'name' => 'Кофейный стол «Нарвик»', 'slug' => 'stol-narvik', 'sku' => 'NH-STO-003', 'desc' => 'Круглый кофейный стол, ножки из бука, столешница белая матовая.', 'qty' => 0, 'res' => 0],
            ['cat' => 'stulya', 'name' => 'Стул «Сканди»', 'slug' => 'stul-skandi', 'sku' => 'NH-STU-001', 'desc' => 'Обеденный стул из массива берёзы с мягким сиденьем.', 'qty' => 30, 'res' => 4],
            ['cat' => 'stulya', 'name' => 'Барный стул «Тромсё»', 'slug' => 'stul-tromso', 'sku' => 'NH-STU-002', 'desc' => 'Барный стул высотой 65 см, сиденье из искусственной кожи.', 'qty' => 18, 'res' => 2],
        ];

        $this->products($store, $products);
    }

    private function seedTeplyySvet(): void
    {
        $store = Store::query()->create([
            'name' => 'Тёплый свет',
            'slug' => 'teplyy-svet',
        ]);

        $lyustry = $this->category($store, 'Люстры', 'lyustry');
        $torshery = $this->category($store, 'Торшеры', 'torshery');
        $lampy = $this->category($store, 'Настольные лампы', 'nastolnye-lampy');
        $bra = $this->category($store, 'Бра и подсветка', 'bra-i-podsvetka');

        $products = [
            ['cat' => 'lyustry', 'name' => 'Люстра «Северное сияние»', 'slug' => 'lyustra-severnoe-siyanie', 'sku' => 'SV-LYU-001', 'desc' => 'Потолочная люстра на 5 плафонов, матовое стекло и латунь.', 'qty' => 10, 'res' => 1],
            ['cat' => 'lyustry', 'name' => 'Люстра «Минимал»', 'slug' => 'lyustra-minimal', 'sku' => 'SV-LYU-002', 'desc' => 'Лаконичная люстра на 3 лампы, чёрный металл, для гостиной.', 'qty' => 14, 'res' => 2],
            ['cat' => 'lyustry', 'name' => 'Люстра «Ромашка»', 'slug' => 'lyustra-romashka', 'sku' => 'SV-LYU-003', 'desc' => 'Подвесная люстра с плафонами из белого опалового стекла.', 'qty' => 6, 'res' => 0],
            ['cat' => 'torshery', 'name' => 'Торшер «Сумерки»', 'slug' => 'torsher-sumerki', 'sku' => 'SV-TOR-001', 'desc' => 'Высокий торшер с тканевым абажуром, диммируемый.', 'qty' => 9, 'res' => 1],
            ['cat' => 'torshery', 'name' => 'Торшер «Арка»', 'slug' => 'torsher-arka', 'sku' => 'SV-TOR-002', 'desc' => 'Торшер-дуга над креслом, металлический каркас, регулировка высоты.', 'qty' => 5, 'res' => 1],
            ['cat' => 'torshery', 'name' => 'Торшер «Хюгге»', 'slug' => 'torsher-hygge', 'sku' => 'SV-TOR-003', 'desc' => 'Торшер с тёплым рассеянным светом, деревянная тренога.', 'qty' => 0, 'res' => 0],
            ['cat' => 'nastolnye-lampy', 'name' => 'Настольная лампа «Читатель»', 'slug' => 'lampa-chitatel', 'sku' => 'SV-NAS-001', 'desc' => 'Лампа для рабочего стола с поворотным плафоном, E27.', 'qty' => 22, 'res' => 3],
            ['cat' => 'nastolnye-lampy', 'name' => 'Настольная лампа «Ночник»', 'slug' => 'lampa-nochnik', 'sku' => 'SV-NAS-002', 'desc' => 'Компактная лампа с тёплой подсветкой и сенсорным выключателем.', 'qty' => 28, 'res' => 4],
            ['cat' => 'bra-i-podsvetka', 'name' => 'Бра «Волна»', 'slug' => 'bra-volna', 'sku' => 'SV-BRA-001', 'desc' => 'Настенное бра с изогнутым плафоном, для спальни и прихожей.', 'qty' => 16, 'res' => 2],
            ['cat' => 'bra-i-podsvetka', 'name' => 'Подсветка зеркала «Глоу»', 'slug' => 'podsvetka-glow', 'sku' => 'SV-BRA-002', 'desc' => 'LED-подсветка для зеркала в ванную, нейтральный белый свет.', 'qty' => 12, 'res' => 1],
        ];

        $this->products($store, $products);
    }

    private function seedDomashniyTekstil(): void
    {
        $store = Store::query()->create([
            'name' => 'Домашний текстиль',
            'slug' => 'domashniy-tekstil',
        ]);

        $bele = $this->category($store, 'Постельное бельё', 'postelnoe-bele');
        $pled = $this->category($store, 'Пледы и покрывала', 'pledyy-i-pokryvala');
        $shtory = $this->category($store, 'Шторы', 'shtory');
        $polotentsa = $this->category($store, 'Полотенца', 'polotentsa');

        $products = [
            ['cat' => 'postelnoe-bele', 'name' => 'Комплект белья «Облако» евро', 'slug' => 'bele-oblako-evro', 'sku' => 'TX-BEL-001', 'desc' => 'Сатиновое постельное бельё евро-размера, 100% хлопок.', 'qty' => 25, 'res' => 3],
            ['cat' => 'postelnoe-bele', 'name' => 'Комплект белья «Лён» 1.5-спальный', 'slug' => 'bele-len-15', 'sku' => 'TX-BEL-002', 'desc' => 'Постельное бельё из льна, дышащая ткань, натуральный цвет.', 'qty' => 18, 'res' => 2],
            ['cat' => 'postelnoe-bele', 'name' => 'Комплект белья «Север» семейный', 'slug' => 'bele-sever-semeynyy', 'sku' => 'TX-BEL-003', 'desc' => 'Семейный комплект поплин, геометрический принт.', 'qty' => 0, 'res' => 0],
            ['cat' => 'pledyy-i-pokryvala', 'name' => 'Плед «Хюгге» 150×200', 'slug' => 'pled-hygge-150', 'sku' => 'TX-PLE-001', 'desc' => 'Вязаный плед из шерсти мериноса, кремовый.', 'qty' => 14, 'res' => 1],
            ['cat' => 'pledyy-i-pokryvala', 'name' => 'Покрывало «Гранит» 220×240', 'slug' => 'pokryvalo-granit', 'sku' => 'TX-PLE-002', 'desc' => 'Стёганое покрывало на кровать, двустороннее.', 'qty' => 9, 'res' => 2],
            ['cat' => 'pledyy-i-pokryvala', 'name' => 'Плед «Клетка» 130×170', 'slug' => 'pled-kletka', 'sku' => 'TX-PLE-003', 'desc' => 'Мягкий флисовый плед в клетку, для дачи и гостиной.', 'qty' => 30, 'res' => 4],
            ['cat' => 'shtory', 'name' => 'Шторы «Туман» 250 см', 'slug' => 'shtory-tuman', 'sku' => 'TX-SHT-001', 'desc' => 'Льняные шторы на ленте, полупрозрачные, светло-серые.', 'qty' => 20, 'res' => 2],
            ['cat' => 'shtory', 'name' => 'Шторы «Блэкаут» 300 см', 'slug' => 'shtory-blekaut', 'sku' => 'TX-SHT-002', 'desc' => 'Плотные портьеры блэкаут, не пропускают свет.', 'qty' => 11, 'res' => 1],
            ['cat' => 'polotentsa', 'name' => 'Полотенце махровое «Спа» 70×140', 'slug' => 'polotentse-spa', 'sku' => 'TX-POL-001', 'desc' => 'Махровое полотенце плотностью 500 г/м², 100% хлопок.', 'qty' => 40, 'res' => 5],
            ['cat' => 'polotentsa', 'name' => 'Набор полотенец «Лён» 50×90, 3 шт', 'slug' => 'nabor-polotentsev-len', 'sku' => 'TX-POL-002', 'desc' => 'Набор кухонных полотенец из льна, нейтральные оттенки.', 'qty' => 16, 'res' => 0],
        ];

        $this->products($store, $products);
    }

    private function seedUyutIKhranenie(): void
    {
        $store = Store::query()->create([
            'name' => 'Уют и хранение',
            'slug' => 'uyut-i-hranenie',
        ]);

        $shkafy = $this->category($store, 'Шкафы', 'shkafy');
        $stellazhi = $this->category($store, 'Стеллажи', 'stellazhi');
        $dekor = $this->category($store, 'Декор', 'dekor');

        $products = [
            ['cat' => 'shkafy', 'name' => 'Шкаф-купе «Альба» 180 см', 'slug' => 'shkaf-kupe-alba', 'sku' => 'HR-SHK-001', 'desc' => 'Шкаф-купе двухдверный с зеркалом, корпус белый.', 'qty' => 5, 'res' => 1],
            ['cat' => 'shkafy', 'name' => 'Шкаф «Сканди» 120 см', 'slug' => 'shkaf-skandi', 'sku' => 'HR-SHK-002', 'desc' => 'Платяной шкаф из светлого дерева с двумя дверцами.', 'qty' => 7, 'res' => 0],
            ['cat' => 'shkafy', 'name' => 'Комод «Норд» 90 см', 'slug' => 'komod-nord', 'sku' => 'HR-SHK-003', 'desc' => 'Комод на четыре ящика, фасады без ручек, push-to-open.', 'qty' => 12, 'res' => 2],
            ['cat' => 'stellazhi', 'name' => 'Стеллаж «Модуль» 5 полок', 'slug' => 'stellazh-modul', 'sku' => 'HR-STE-001', 'desc' => 'Открытый стеллаж из ДСП дубовый шпон, 180 см высота.', 'qty' => 15, 'res' => 1],
            ['cat' => 'stellazhi', 'name' => 'Стеллаж металлический «Лофт»', 'slug' => 'stellazh-loft', 'sku' => 'HR-STE-002', 'desc' => 'Стеллаж на металлокаркасе, регулируемые полки.', 'qty' => 9, 'res' => 0],
            ['cat' => 'stellazhi', 'name' => 'Этажерка «Веранда»', 'slug' => 'etazherka-veranda', 'sku' => 'HR-STE-003', 'desc' => 'Этажерка для растений и книг, четыре яруса.', 'qty' => 0, 'res' => 0],
            ['cat' => 'dekor', 'name' => 'Зеркало «Овал» 60×90', 'slug' => 'zerkalo-oval', 'sku' => 'HR-DEK-001', 'desc' => 'Настенное зеркало в тонкой раме из дуба.', 'qty' => 20, 'res' => 2],
            ['cat' => 'dekor', 'name' => 'Набор ваз «Флора»', 'slug' => 'nabor-vaz-flora', 'sku' => 'HR-DEK-002', 'desc' => 'Три керамические вазы разной высоты, матовая глазурь.', 'qty' => 17, 'res' => 1],
            ['cat' => 'dekor', 'name' => 'Свечи ароматические «Лес»', 'slug' => 'svechi-les', 'sku' => 'HR-DEK-003', 'desc' => 'Набор из двух свечей из соевого воска, аромат хвои и кедра.', 'qty' => 34, 'res' => 4],
            ['cat' => 'dekor', 'name' => 'Корзина для хранения «Плетёнка»', 'slug' => 'korzina-pletenka', 'sku' => 'HR-DEK-004', 'desc' => 'Плетёная корзина из ротанга, 35 л, с ручками.', 'qty' => 22, 'res' => 3],
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
