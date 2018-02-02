<?php
namespace App\Http\Serializers;

use League\Fractal\Serializer\DataArraySerializer as FractalDataArraySerializer;

class DataArraySerializer extends FractalDataArraySerializer
{

    public function collection($resourceKey, array $data)
    {
        if ($resourceKey === false) {
            return $data;
        }
        return array($resourceKey ?: 'data' => $data);
    }

    public function item($resourceKey, array $data)
    {
        if ($resourceKey === false) {
            return $data;
        }
        return array($resourceKey ?: 'data' => $data);
    }

}
