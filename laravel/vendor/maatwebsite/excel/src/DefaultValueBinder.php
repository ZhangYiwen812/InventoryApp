<?php

namespace Maatwebsite\Excel;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder as PhpSpreadsheetDefaultValueBinder;

class DefaultValueBinder extends PhpSpreadsheetDefaultValueBinder
{
    /**
     * @param  Cell  $cell  Cell to bind value to
     * @param  mixed  $value  Value to bind in cell
     * @return bool
     */
    public function bindValue(Cell $cell, $value)
    {
        if (is_array($value)) {
            $value = \json_encode($value);
        }
        //超过10位的数字转文本格式，防止科学计数法
        if (strlen($value) > 10) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
