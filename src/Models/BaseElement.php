<?php

namespace ATW\ElementalBase\Models;

use DNADesign\Elemental\Forms\TextCheckboxGroupField;
use DNADesign\Elemental\Models\BaseElement as ElementalBase;
use SilverStripe\Core\Config\ConfigLoader;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;

class BaseElement extends ElementalBase
{
    private static $description = 'Base element class';

    private static $table_name = 'ElementBase';

    private static $use_submenu = false;

    private static $db = [
        'Variant' => 'Varchar(255)',
        'Options' => 'Varchar(255)',
        'ShowInMenu' => 'Boolean',
        'MenuTitle' => 'Varchar(255)',
    ];

    //For adiwidjaja/silverstripe-headless
    private static $headless_fields = [
        'ShowTitle' => 'showTitle',
        'Variant' => 'variant',
        'Options' => 'options',
        'ShowInMenu' => 'showInMenu',
        'MenuTitle' => 'menuTitle'
    ];

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $page = $this->getPage();
            if(!$page) return;
            $config = $page->config()->get($this->ClassName);
            $variants = $config["variants"] ?? [];
            $variants_name = $config["variants_name"] ?? _t(__CLASS__.'.VARIANT', 'Variants');

            if ($variants && count($variants) > 0) {
                $variantDropdown = DropdownField::create('Variant', $variants_name, $variants);

                $fields->addFieldToTab('Root.Main', $variantDropdown, "Title");

                $variantDropdown->setEmptyString(_t(__CLASS__.'.CHOOSE_VARIANT', 'Choose variant'));
            } else {
                $fields->removeByName('Variant');
            }

            $options = $config["options"] ?? [];
            $options_name = $config["options_name"] ?? _t(__CLASS__.'.VARIANT', 'Variants');

            if ($options && count($options) > 0) {
                $optionsField = CheckboxSetField::create('Options', $options_name, $options);

                $fields->addFieldToTab('Root.Main', $optionsField, "Title");
            } else {
                $fields->removeByName('Options');
            }

            $use_submenu = $this->config()->get('use_submenu');

            if ($use_submenu) {
                $fields->removeByName('ShowInMenu');
                $fields->replaceField(
                    'MenuTitle',
                    TextCheckboxGroupField::create(
                        TextField::create('MenuTitle', _t(__CLASS__ . '.db_MenuTitle', 'MenuTitle')),
                        CheckboxField::create('ShowInMenu', _t(__CLASS__ . '.db_ShowInMenu', 'Show in submenu'))
                    )
                        ->setName('ShowInMenuTitle')
                );
            } else {
                $fields->removeByName('ShowInMenu');
                $fields->removeByName('MenuTitle');
            }
            // Hide the navigation section of the tabs in the React component {@see silverstripe/admin Tabs}
            $rootTabset = $fields->fieldByName('Root');
            $rootTabset->setSchemaState(['hideNav' => true]);

        });

        return parent::getCMSFields();
    }

    public function getVariantClasses() {
        $classes = [];
        if($this->Variant)
            $classes[] = $this->Variant;
        if($options = $this->Options) {
            $options = json_decode(trim($options));
            $classes = array_merge($classes, $options);
        }
        return implode(" ", $classes);
    }

    public function HasOption($option) {
        if($options = $this->Options) {
            return strstr($options, $option) !== false;
        }
    }

    public function getMenuTitle() {
        if ($this->dbObject("MenuTitle")->getValue())
            return $this->dbObject("MenuTitle")->getValue();
        return $this->Title;
    }

    public function getAnchorTitle() {
        return $this->getMenuTitle();
    }

    public function getEditorContent() {
        return _t('ElementSummary.NO_PREVIEW', 'Keine Vorschau');
    }

    protected function provideBlockSchema() {
        $schema = parent::provideBlockSchema();
        $schema["content"] = $this->getEditorContent();
        return $schema;
    }

    public function onBeforeWrite() {
        parent::onBeforeWrite();
    }


}
