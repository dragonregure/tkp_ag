<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'ITM-001', 'name' => 'Kopi Arabica 250g', 'price' => 85000, 'stock' => 250],
            ['code' => 'ITM-002', 'name' => 'Kopi Robusta 250g', 'price' => 65000, 'stock' => 250],
            ['code' => 'ITM-003', 'name' => 'Gula Aren Cair', 'price' => 42000, 'stock' => 220],
            ['code' => 'ITM-004', 'name' => 'Susu Oat 1L', 'price' => 56000, 'stock' => 220],
            ['code' => 'ITM-005', 'name' => 'Cold Brew Bottle', 'price' => 35000, 'stock' => 300],
            ['code' => 'ITM-006', 'name' => 'Espresso Blend 500g', 'price' => 145000, 'stock' => 180],
            ['code' => 'ITM-007', 'name' => 'House Blend 1kg', 'price' => 238000, 'stock' => 160],
            ['code' => 'ITM-008', 'name' => 'Single Origin Flores 200g', 'price' => 94000, 'stock' => 180],
            ['code' => 'ITM-009', 'name' => 'Single Origin Toraja 200g', 'price' => 98000, 'stock' => 180],
            ['code' => 'ITM-010', 'name' => 'Drip Bag Coffee Box', 'price' => 78000, 'stock' => 260],
            ['code' => 'ITM-011', 'name' => 'Chocolate Powder 500g', 'price' => 69000, 'stock' => 210],
            ['code' => 'ITM-012', 'name' => 'Matcha Powder 250g', 'price' => 112000, 'stock' => 170],
            ['code' => 'ITM-013', 'name' => 'Vanilla Syrup 750ml', 'price' => 89000, 'stock' => 190],
            ['code' => 'ITM-014', 'name' => 'Caramel Syrup 750ml', 'price' => 91000, 'stock' => 190],
            ['code' => 'ITM-015', 'name' => 'Hazelnut Syrup 750ml', 'price' => 93000, 'stock' => 190],
            ['code' => 'ITM-016', 'name' => 'Paper Cup 8oz Pack', 'price' => 38000, 'stock' => 350],
            ['code' => 'ITM-017', 'name' => 'Paper Cup 12oz Pack', 'price' => 46000, 'stock' => 350],
            ['code' => 'ITM-018', 'name' => 'Coffee Filter V60', 'price' => 52000, 'stock' => 280],
            ['code' => 'ITM-019', 'name' => 'Manual Brew Starter Kit', 'price' => 315000, 'stock' => 80],
            ['code' => 'ITM-020', 'name' => 'French Press 600ml', 'price' => 185000, 'stock' => 95],
            ['code' => 'ITM-021', 'name' => 'Milk Pitcher 350ml', 'price' => 78000, 'stock' => 140],
            ['code' => 'ITM-022', 'name' => 'Reusable Tumbler 350ml', 'price' => 125000, 'stock' => 150],
            ['code' => 'ITM-023', 'name' => 'Granola Bar Box', 'price' => 54000, 'stock' => 240],
            ['code' => 'ITM-024', 'name' => 'Butter Croissant Frozen', 'price' => 72000, 'stock' => 240],
        ];

        foreach ($items as $item) {
            Item::query()->updateOrCreate(['code' => $item['code']], $item);
        }
    }
}
