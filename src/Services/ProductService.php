<?php

namespace App\Services;

class ProductService
{
    private array $products;

    public function findProduct(string $query): ?array
    {
        $this->products = [
          [
              'name' => 'ЖМС №5 Средство для мытья стекол в ПЭТ - КОНЦЕНТРАТ (5л)',
              'description' => 'Спиртосодержащее концентрированное средство для мытья оконных, витринных и автомобильных стёкол, кафеля, витражей, стеклянных изделий, зеркал, пластика, окрашенного дерева, ламината, линолеума. Предназначено для использования в медицинских, детских учреждениях, на предприятиях пищевой промышленности, общественного питания, в стекольных производствах, автосервисах и в быту. Чистит, обладает антибактериальным эффектом и антистатическими свойствами, придаёт блеск, не оставляет разводов.',
              'article' => '005',
              'price' => '999 руб.',
              'link' => 'https://компаниябогатая.рф/catalog/chistyashchie_sredstva/zhms_5_sredstvo_dlya_mytya_stekol_v_pet_kontsentrat_5l'
          ],
            [
                'name' => 'Ящик для хранения черный 0108',
                'description' => 'Ширина 44 см Длина 66 см Высота 36см',
                'article' => '001',
                'price' => '600 руб.',
                'link' => 'https://компаниябогатая.рф/catalog/chistyashchie_sredstva/dlya_mytya_okon/rezinka_dlya_sgona_105sm'
            ],

        ];


        // Поиск по названию, артикулу, описанию
        foreach ($this->products as $product) {
            if (stripos($product['name'], $query) !== false ||
                stripos($product['description'], $query) !== false ||
                stripos($product['article'], $query) !== false) {
                return $product;
            }
        }
        return null;
    }
}