<?php

use Illuminate\Support\Collection;
use Carbon\Carbon;

require_once 'vendor/autoload.php';
require_once 'storage/array.txt';

//method controller
$collection = collect($array);

$first = $collection->filter(function ($value, $key) {  return $value['fix'] == 1; })           //Filtrar eventos validos
->sortBy('fecha')                                                                               //Ordenar por fecha
->groupBy(['placa', function ($item) { return date('Y-m-d', strtotime($item['fecha'])); }]);    //Agrupar por vehiculo y fecha

//view 1
$kmCounter = 0;
$first->each(function ($vehicle, $key) {
    $vehicle->each(function ($records, $date) use ($key, &$kmCounter) {
        $kmCounter += (($records->last()['distancia'] - $records->first()['distancia']) / 1000);
        echo $key . ' | ' . $date . ' | ' . (($records->last()['distancia'] - $records->first()['distancia']) / 1000) . ' | ' . $kmCounter . '<br>';
    });
});

echo '<br><br>';

//view 2
$kmCounter2 = 0;
$first->each(function ($vehicle, $key) {
    $vehicle->each(function ($records, $date) use ($key) {
        $initialValue = $records->first()['distancia'];
        $records->each(function ($item, $value) use ($key, $date, &$initialValue, &$kmCounter2) {
            $down = false;
            $estado = substr($item['estado_io'], 0, 1);
            $kmCounter2 += ($item['distancia'] - $initialValue);
            if ($estado == '1') {
                $down = true;
            }
            if ($down || $item['modo'] == '34') {
                echo $key . ' | ' . $date . ' | ' . $item['velocidad'] . ' | ' . $item['tipo'] . ' | ' . $item['modo'] . ' | ' . (!!$estado ? 'encendido' : 'apagado') . ' | ' . (($item['distancia'] - $initialValue) / 1000) . 'Km | ' . (($kmCounter2 ?? 0) / 1000) . 'Km | ' . ($item['latitud'] . ', ' . $item['longitud']) . '<br>';
                $initialValue = $item['distancia'];
            }
        });
    });
});

echo '<br><br>';

//view 3
$dateCounter = 0;
$counter = 0;
$initialDate = null;
$first->each(function ($vehicle, $key) {
    $vehicle->each(function ($records, $date) use ($key, &$counter) {
        $initialValue = $records->first()['fecha'];
        $records->each(function ($item, $value) use (&$initialValue, &$dateCounter, &$initialDate) {
            $estado = substr($item['estado_io'], 0, 1);
            if ($estado) {
                $initialDate = new Carbon($item['fecha']);
                $initialValue = new Carbon($initialValue);
                $dateCounter += $initialDate->diffInMinutes($initialValue);
                $initialValue = new Carbon($item['fecha']);
            }
        });
        $counter += round((($dateCounter ?? 0) / 60), 2);
        echo $key . ' | ' . $date . ' | ' . round((($dateCounter ?? 0) / 60), 2) . 'Hrs | ' . $counter . 'Hrs<br>';
    });
});

echo '<br><br>';

//view 4
$dateCounter = 0;
$counter = 0;
$initialDate = null;
$first->each(function ($vehicle, $key) {
    $vehicle->each(function ($records, $date) use ($key) {
        $initialValue = $records->first()['fecha'];
        $records->each(function ($item, $value) use (&$initialValue, &$dateCounter, &$initialDate, &$counter, $key, $date) {
            $estado = substr($item['estado_io'], 0, 1);
            if ($estado) {
                $initialDate = new Carbon($item['fecha']);
                $initialValue = new Carbon($initialValue);
                $dateCounter += $initialDate->diffInMinutes($initialValue);
                echo $key . ' | ' . $date . ' | ' . round(($initialDate->diffInMinutes($initialValue) / 60), 2) . 'Hrs | ' . round((($dateCounter ?? 0) / 60), 2) . 'Hrs' . ' | ' . ($item['latitud'] . ', ' . $item['longitud']) . '<br>';
                $initialValue = new Carbon($item['fecha']);
            }
        });
    });
});
