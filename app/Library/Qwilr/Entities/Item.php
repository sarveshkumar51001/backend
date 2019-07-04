<?php
namespace App\Library\Qwilr\Entities;

class Item
{

    public static function getLineItem($item)
    {
        $line_item = [];
        if (self::isItemOptional($item)) {
            if (self::isItemSelected($item)) {
                $line_item = self::createLineItem($item);
            }
        } else {
            $line_item = self::createLineItem($item);
        }
        return $line_item;
    }

    private static function createLineItem($item)
    {
        return [
            'code' => self::getItemCode($item),
            'desc' => self::getItemDescription($item),
            'qty' => self::getItemQuantity($item),
            'unit_price' => self::getItemRate($item),
            'total_price' => self::getItemTotalPrice($item),
            'discount' => self::getItemDiscount($item),
            'final_price' => self::getItemFinalPrice($item)
        ];
    }

    private static function isItemOptional($item)
    {
        return $item['interactive']['isOptional'];
    }

    private static function isItemSelected($item)
    {
        return $item['interactive']['isOptionalSelected'];
    }

    private static function getItemContent($item)
    {
        return $item['description']['content'];
    }

    private static function getItemDescription($item)
    {
        $item_content = self::getItemContent($item);
        $pattern = '#<p(.*?)>(.*?)</p>#is';
        $replace = '$2<br/>';

        return strip_tags(preg_replace($pattern, $replace, $item_content), "<br>");
    }

    private static function getItemCode($item)
    {
        $item_content = self::getItemContent($item);
        $pattern = '/(\[Item Code:)(.*)(\])/m';

        if (preg_match($pattern, $item_content, $match))
            return trim($match[2]);

        return false;
    }

    private static function getItemQuantity($item)
    {
        return $item['quantity'];
    }

    private static function getItemRate($item)
    {
        return $item['rate']['rate'];
    }

    private static function getItemTotalPrice($item)
    {
        return self::getItemQuantity($item) * self::getItemRate($item);
    }

    private static function getItemDiscount($item)
    {
        $discount_amount = 0;
        $discount = $item['discount'];
        if ($discount['enabled']) {
            if ($discount['type'] = 'percent') {
                $discount_amount = self::getItemTotalPrice($item) * $discount['units'] / 100;
            } else {
                $discount_amount = self::getItemTotalPrice($item) - $discount['units'];
            }
        }
        return $discount_amount ?? 0;
    }

    private static function getItemFinalPrice($item)
    {
        return self::getItemTotalPrice($item) - self::getItemDiscount($item);
    }
}