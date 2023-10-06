<?php

namespace App\Services\Distances;

use InvalidArgumentException;

class CalculateDistanceService{

    public function convertToMark(array $scores): int{
        $currentMin = 0;
        $currentMax = 100;
        $newMax = 5;
        $newMin = 2;
        $average = array_sum($scores)/count($scores);
        return round(($average - $currentMin) * ($newMax - $newMin) / ($currentMax - $currentMin) + $newMin);
    }
     
    private function calculateDistance($position1, $position2): float {
        $xDistance = $position1->x - $position2->x;
        $yDistance = $position1->y - $position2->y;
        return sqrt(pow($xDistance, 2) + pow($yDistance, 2));
    }
     
    private function transformDistanceToScore($distance, $option): float {
        // Ensure the maximum possible distance between two points is taken into account
        // You might adjust the logic to scale the distance into a score between maxScore and minScore based on your requirements.
        if ($distance === null) {
            return $option['minScore'];
        }
     
        $score = $option['maxScore'] - $distance; // Example logic, adapt as per your needs
        return max($option['minScore'], min($option['maxScore'], $score)); // Ensure the score is between maxScore and minScore
    }
     public function calculateSimilarityCoefficient($teacherAnswerArray, $studentAnswerArray, $option = ['maxScore' => 100, 'minScore' => 0]): array {
        $similarityCoefficients = [];
     
        foreach ($studentAnswerArray->pieces as $studentPiece) {
            $minDistance = null;
            foreach ($teacherAnswerArray->pieces as $teacherPiece) {
                if ($studentPiece->type == $teacherPiece->type) {
                    $distance = $this->calculateDistance($studentPiece->position, $teacherPiece->position);
                    if (is_null($minDistance) || $distance < $minDistance) {
                        $minDistance = $distance;
                    }
                }
            }
            $similarityCoefficients[] = $this->transformDistanceToScore($minDistance, $option);
        }
        return $similarityCoefficients;
    }
}
