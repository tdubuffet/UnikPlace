<?php

namespace OrderBundle\Service;

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


    public function __construct()
    {
    }

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

        foreach ($this->deliveryModes[$deliveryCode]['prices'] as $rangeWeight => $price) {
            if ($productInfos['weight'] <= $rangeWeight) {
                return $price;
            }
        }

        throw new \Exception('Out price range for '.$deliveryCode.' delivery mode.');
    }

}