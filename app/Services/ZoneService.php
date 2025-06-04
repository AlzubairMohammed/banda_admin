<?php

namespace App\Services;

use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;

class ZoneService
{

    public function getAddData(Object $request, int|string $zoneId): array
    {
        $value = $request['coordinates'];


        foreach(explode('),(',trim($value,'()')) as $index=>$single_array){
            if($index == 0)
            {
                $lastCord = explode(',',$single_array);
            }
            $coords = explode(',',$single_array);

            $polygon[] = new Point($coords[0], $coords[1]);
        }
        $polygon[] = new Point($lastCord[0], $lastCord[1]);
        return [
            'name' => $request->name[array_search('default', $request->lang)],
            'display_name' => $request->display_name[array_search('default', $request->lang)],
            'coordinates' => new Polygon([new LineString($polygon)]),
            'store_wise_topic' => 'zone_'.$zoneId.'_store',
            'customer_wise_topic' => 'zone_'.$zoneId.'_customer',
            'deliveryman_wise_topic' => 'zone_'.$zoneId.'_delivery_man',
            'cash_on_delivery' => $request->cash_on_delivery?1:0,
            'digital_payment' => $request->digital_payment?1:0,
        ];
    }

    public function getUpdateData(Object $request, int|string $zoneId): array
    {
        $value = $request['coordinates'];

        foreach(explode('),(',trim($value,'()')) as $index=>$single_array){
            if($index == 0)
            {
                $lastCord = explode(',',$single_array);
            }
            $coords = explode(',',$single_array);

            $polygon[] = new Point($coords[0], $coords[1]);
        }
        $polygon[] = new Point($lastCord[0], $lastCord[1]);
        return [
            'name' => $request->name[array_search('default', $request->lang)],
            'display_name' => $request->display_name[array_search('default', $request->lang)],
            'store_wise_topic' => 'zone_'.$zoneId.'_store',
            'customer_wise_topic' => 'zone_'.$zoneId.'_customer',
            'deliveryman_wise_topic' => 'zone_'.$zoneId.'_delivery_man',
            'coordinates' => new Polygon([new LineString($polygon)]),
        ];
    }
    public function getZoneModuleSetupData(Object $request): array
    {
        return [
            'cash_on_delivery' => $request->cash_on_delivery?1:0,
            'digital_payment' => $request->digital_payment?1:0,
            'offline_payment' => $request->offline_payment?1:0,
            'increased_delivery_fee' => $request->increased_delivery_fee ?? 0,
            'increased_delivery_fee_status' => $request->increased_delivery_fee_status ?? 0,
            'increase_delivery_charge_message' => $request->increase_delivery_charge_message ?? null,
        ];
    }

    public function formatCoordinates(array $coordinates): array
    {
        $data = [];
        foreach ($coordinates as $coordinate) {
            $data[] = (object)['lat' => $coordinate[1], 'lng' => $coordinate[0]];
        }
        return $data;
    }

    public function formatZoneCoordinates(object $zones): array
    {
        $data = [];
        foreach($zones as $zone)
        {
            $area = json_decode($zone->coordinates[0]->toJson(),true);
            $data[] = self::formatCoordinates(coordinates: $area['coordinates']);
        }
        return $data;
    }

    public function checkModuleDeliveryCharge(array $moduleData): array
    {
        foreach($moduleData as $data){
            if(isset($data['maximum_shipping_charge']) && ((int)$data['maximum_shipping_charge'] < (int)$data['minimum_shipping_charge'])){
                return ['flag' => 'max_delivery_charge'];
            }
        }
        return [];
    }

}
