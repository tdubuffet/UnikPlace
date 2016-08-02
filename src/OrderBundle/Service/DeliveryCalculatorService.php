<?php

namespace OrderBundle\Service;

use Symfony\Component\Config\Definition\Exception\Exception;

class DeliveryCalculatorService
{

    private $deliveryModes = array(
        'by_hand' => array(
            'large' => true, // Available for large products
        ),
        'colissimo_tracked_letter' => array(
            'prices' => array(
                20     => 1.80,
                100     => 1.80,
                250     => 3.20,
                500     => 4.60,
                3000    => 6.00
            )
        ),
        'colissimo_parcel' => array(
            'prices' => array(
                250     => 4.10,
                500     => 4.80,
                750     => 5.20,
                1000     => 5.60,
                2000    => 7.00,
                5000    => 8.00,
                10000    => 9.00,
                30000    => 12.50
            )
        ),
    );

    public function getFeeFromProductAndDeliveryModeCode($deliveryCode, $productInfos)
    {
        if ($deliveryCode == 'by_hand') {
            return 0;
        }

        if (!isset($this->deliveryModes[$deliveryCode])) {
            throw new \Exception('Cannot find calculation fee information about '.$deliveryCode.' delivery mode.');
        }
        if (!isset($productInfos['weight'])) {
            throw new \Exception('A weight in grams is required to calculate delivery fee.');
        }

        if (!isset($productInfos['length'])) {
            throw new \Exception('A length in m is required to calculate delivery fee.');
        }

        if (!isset($productInfos['height'])) {
            throw new \Exception('A height in m is required to calculate delivery fee.');
        }

        if (!isset($productInfos['width'])) {
            throw new \Exception('A width in m is required to calculate delivery fee.');
        }


        $dim = ($productInfos['length'] + $productInfos['height'] + $productInfos['width']) * 100;

        if ($dim > 150 || ($productInfos['length']*100) > 100 ) {
            throw new \Exception('The dimensions are too large');
        }

        foreach ($this->deliveryModes[$deliveryCode]['prices'] as $rangeWeight => $price) {
            if ($productInfos['weight'] <= $rangeWeight) {
                return $price;
            }
        }

        throw new \Exception('Out price range for '.$deliveryCode.' delivery mode.');
    }

}