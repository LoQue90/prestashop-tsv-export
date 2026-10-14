<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Wisoexport extends Module
{
    public function __construct()
    {
        $this->name = 'wisoexport';
        $this->version = '1.2.0';
        $this->ps_versions_compliancy = ['min' => '9.1.0', 'max' => '9.1.99'];
        $this->author = 'Community';
        $this->tab = 'administration';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('WISO EÜR Export');

        $this->description = $this->l(
            'Exportiert Rechnungen als TSV-Datei für WISO EÜR & Kasse.'
        );
    }

    public function install()
    {
        return parent::install()
            && $this->installConfiguration()
            && $this->installTab();
    }

    public function uninstall()
    {
        return $this->uninstallConfiguration()
            && $this->uninstallTab()
            && parent::uninstall();
    }

    private function installConfiguration()
    {

        Configuration::updateValue(
            'WISOEXPORT_DESCRIPTION',
            ''
        );

        Configuration::updateValue(
            'WISOEXPORT_ACCOUNT',
            ''
        );

        Configuration::updateValue(
            'WISOEXPORT_BANK',
            ''
        );

        Configuration::updateValue(
            'WISOEXPORT_TAX_TYPE',
            'NULL'
        );

        Configuration::updateValue(
            'WISOEXPORT_USE_STATUS_FILTER',
            1
        );

        $shippingState = (int)Db::getInstance()->getValue(
            '
            SELECT id_order_state
            FROM ' . _DB_PREFIX_ . 'order_state_lang
            WHERE name = "Versand"
            AND id_lang = ' . (int)$this->context->language->id
        );

        Configuration::updateValue(
            'WISOEXPORT_ORDER_STATE',
            $shippingState
        );

        return Configuration::updateValue('WISOEXPORT_EXCLUDE_ZERO', 0);
    }

    private function uninstallConfiguration()
    {
        Configuration::deleteByName('WISOEXPORT_PREFIX');
        Configuration::deleteByName('WISOEXPORT_NUMBER_LENGTH');
        Configuration::deleteByName('WISOEXPORT_DESCRIPTION');
        Configuration::deleteByName('WISOEXPORT_ACCOUNT');
        Configuration::deleteByName('WISOEXPORT_BANK');
        Configuration::deleteByName('WISOEXPORT_ORDER_STATE');
        Configuration::deleteByName('WISOEXPORT_TAX_TYPE');
        Configuration::deleteByName('WISOEXPORT_USE_STATUS_FILTER');
        Configuration::deleteByName('WISOEXPORT_EXCLUDE_ZERO');
        Configuration::deleteByName('WISOEXPORT_LAST_EXPORT');

        return true;
    }

    private function installTab()
    {
        $tab = new Tab();

        $tab->class_name = 'AdminWisoExport';

        $tab->module = $this->name;

        $tab->id_parent =
            (int)Tab::getIdFromClassName(
                'AdminParentOrders'
            );

        foreach (Language::getLanguages(true) as $lang) {

            $tab->name[$lang['id_lang']] =
                'WISO EÜR Export';

        }

        return $tab->add();
    }

    private function uninstallTab()
    {
        $id = (int)Tab::getIdFromClassName(
            'AdminWisoExport'
        );

        if ($id) {

            $tab = new Tab($id);

            return $tab->delete();

        }

        return true;
    }
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminWisoExport'));
        return '';
    }
}
