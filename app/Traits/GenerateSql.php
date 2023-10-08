<?php

namespace App\Traits;

use App\Consts;

Trait GenerateSql
{
    public function toSqlInsertion()
    {
        $attributes = $this->getAttributesNotContainsIgnored();

        $columns = array_keys($attributes);
        $fields = $this->joinString($columns, Consts::CHAR_BACKTICKS);

        $arrValues = array_values($attributes);
        $value = $this->joinString(self::escapeDoubleQuote($arrValues), Consts::CHAR_DOUBLE_QUOTE);

        return "INSERT INTO {$this->getTable()} ({$fields}) VALUES ({$value})";
    }

    public function toSqlUpdate()
    {
        $attributes = $this->getAttributesNotContainsIgnored();

        $value = collect($attributes)->map(function ($value, $key) {
            if (is_null($value)) {
                return "`$key` = null";
            }
            $value = self::escapeDoubleQuote($value);
            return "`$key` = \"$value\"";
        })->join(Consts::CHAR_COMMA);
        return "UPDATE {$this->getTable()} SET {$value} WHERE id = {$this->id}";
    }

    public function generateSql($forceInsertion = false)
    {
        if ($forceInsertion) {
            return $this->toSqlInsertion();
        }

        if($this->updated_at->lt($this->created_at)) {
            $msgError = "======Table `{$this->getTable()}` with id = '{$this->id}':: The updated_at can not less than created_at";
            logger()->error($msgError);
            return null;
        }
        if($this->updated_at->gt($this->created_at)) {
            return $this->toSqlUpdate();
        }
        return $this->toSqlInsertion();
    }

    private function getAttributesNotContainsIgnored()
    {
        $data = $this->getAttributes();
        // foreach ($this->ignoredFields as $key => $field) {
        //     unset($data[$field]);
        // }
        return $data;
    }

    private function joinString(array $data, $charBoundary)
    {
        // $delimiter = $charBoundary . '' . Consts::CHAR_COMMA . '' . $charBoundary;
        $result = '';
        foreach ($data as $index => $value) {
            if ($index > 0 ) {
                $result .= Consts::CHAR_COMMA;
            }

            // Value is null.
            if ($value == null) {
                $result .= 'null';
                continue;
            }
            $result .= "{$charBoundary}{$value}{$charBoundary}";
        }
        return $result;
        // return $charBoundary . join($delimiter, $data) . $charBoundary;
    }

    private function escapeDoubleQuote($value)
    {
        if (is_array($value)) {
            return array_map(function ($item) {
                return str_replace(Consts::CHAR_DOUBLE_QUOTE, Consts::CHAR_SINGLE_QUOTE, $item);
            }, $value);
        }
        return str_replace(Consts::CHAR_DOUBLE_QUOTE, Consts::CHAR_SINGLE_QUOTE, $value);
    }

}

