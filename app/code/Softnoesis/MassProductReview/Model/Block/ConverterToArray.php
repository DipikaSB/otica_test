<?php
/**
 * Softnoesis
 * Copyright(C) 05/2023 Softnoesis <ideveloper1990@gmail.com>
 * @package Softnoesis_MassProductReview
 * @copyright Copyright(C) 2015 Softnoesis (ideveloper1990@gmail.com)
 * @author Softnoesis <ideveloper1990@gmail.com>
 */
namespace Softnoesis\MassProductReview\Model\Block;

/**
 * Class Converter
 */
class ConverterToArray
{
    /**
     * Convert CSV format row to array
     *
     * @param array $row
     * @return array
     */
    public function convertRow($row)
    {
        $data = [];
        foreach ($row as $field => $value) {
            $data[0][$field] = $value;
        }
        return $data;
    }
}
