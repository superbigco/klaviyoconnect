<?php
namespace fostercommerce\klaviyoconnect\fields;

use Craft;
use craft\base\Field;
use craft\base\ElementInterface;
use fostercommerce\klaviyoconnect\Plugin;
use GuzzleHttp\Exception\ClientException;

class ListField extends Field
{

    public static function displayName(): string
    {
        return Craft::t('klaviyoconnect', 'Klaviyo List');
    }

    public function getInputHtml($value, ElementInterface $element = NULL): string
    {
        try {
            $lists = Plugin::getInstance()->api->getLists();
        } catch (ClientException $e) {
            $lists = [];
        }

        $allLists = Plugin::getInstance()->settings->klaviyoListsAll;
        $availableLists = Plugin::getInstance()->settings->klaviyoAvailableLists;

        $listOptions = [];
        foreach ($lists as $list) {
            if ($allLists || in_array($list->id, $availableLists)) {
                $listOptions[$list->id] = $list->name;
            }
        }

        return Craft::$app->getView()->renderTemplate('klaviyoconnect/fieldtypes/select', array(
            'name' => $this->handle,
            'options' => $listOptions,
            'value' => $value->id,
        ));
    }

    public function normalizeValue($value, ElementInterface $element = NULL)
    {
        if ($value) {
            $o = json_decode($value);
            if ($o) {
                $value = $o->id;
            }
        }
        $modified = array();

        try {
            $lists = Plugin::getInstance()->api->getLists();
        } catch (ClientException $e) {
            $lists = [];
        }

        if ($value) {
            foreach ($lists as $list) {
                if ($list->id === $value) {
                    return $list;
                }
            }
        }

        return null;
    }
}