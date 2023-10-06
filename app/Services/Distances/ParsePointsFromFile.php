<?php

namespace App\Services\Distances;

class ParsePointsFromFile{
    public function parseFromDict($pieces){
        $output = [];
        foreach ($pieces as $piece){
            $temp = [];
            foreach ($piece['position'] as $key => $value)
                array_push($temp, $value);
            array_push($output, $temp);
        }
        return $output;
    }
    public function parseFromObject($pieces){
        $output = [];
        foreach ($pieces as $piece){
            $temp = [];
            foreach ($piece->position as $key => $value)
                array_push($temp, $value);
            array_push($output, $temp);
        }
        return $output;
    }
    public function parseArrayFromDict($pieces){
        $output = [];
        foreach ($pieces as $piece){
            $temp = [];
            foreach ($piece['position'] as $key => $value)
                array_push($temp, $value);
            array_push($output, $temp);
        }
        return $output;
    }
    public function parseArrayFromObject($pieces){
        $output = [];
        foreach ($pieces as $piece){
            $temp = [];
            foreach ($piece->position as $key => $value)
                array_push($temp, $value);
            array_push($output, $temp);
        }
        return $output;
    }
}