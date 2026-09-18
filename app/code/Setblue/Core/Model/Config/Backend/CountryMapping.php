<?php
namespace Setblue\Core\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

class CountryMapping extends Value
{
    /**
     * Before saving to DB — clean & JSON encode
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        if (is_array($value)) {
            // Remove Magento's "__empty" and empty rows
            $cleaned = [];
            foreach ($value as $row) {
                if (!empty($row['field_one']) && !empty($row['field_two'])) {
                    $cleaned[$row['field_one']] = $row['field_two'];
                }
            }
            $this->setValue(json_encode($cleaned, JSON_UNESCAPED_UNICODE));
        } elseif (!is_string($value)) {
            $this->setValue(json_encode([], JSON_UNESCAPED_UNICODE));
        }

        return parent::beforeSave();
    }

    /**
     * After loading from DB — decode JSON for UI
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();

        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (is_array($value)) {
            $uiValue = [];
            foreach ($value as $code => $text) {
                $uiValue[] = [
                    'field_one' => $code,
                    'field_two' => $text
                ];
            }
            $this->setValue($uiValue);
        }

        return parent::_afterLoad();
    }

    /**
     * Get Country → SEO text mapping as array
     */
    public function getCountryMap()
    {
        $value = $this->getValue();

        // If still a JSON string, decode it
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        // UI format → convert to associative
        if (is_array($value) && isset($value[0]['field_one'])) {
            $map = [];
            foreach ($value as $row) {
                $map[$row['field_one']] = $row['field_two'];
            }
            return $map;
        }

        return is_array($value) ? $value : [];
    }
}
