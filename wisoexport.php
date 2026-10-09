<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Wisoexport extends Module
{
    public function __construct()
    {
        $this->name = 'wisoexport';
        $this->version = '1.1.0';
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
        $this->initializeConfiguration();

        $output = '';

        if (Tools::isSubmit('submitWisoExportConfig')) {
            if (!in_array(Tools::getValue('WISOEXPORT_TAX_TYPE'), ['NULL', 'Brutto', 'Netto'], true)) {
                return $this->displayError($this->l('Ungültige Umsatzsteuerart.')) . $this->renderConfigurationForm();
            }
            Configuration::updateValue('WISOEXPORT_EXCLUDE_ZERO', (int) (bool) Tools::getValue('WISOEXPORT_EXCLUDE_ZERO'));

            Configuration::updateValue(
                'WISOEXPORT_DESCRIPTION',
                Tools::getValue('WISOEXPORT_DESCRIPTION')
            );

            Configuration::updateValue(
                'WISOEXPORT_ACCOUNT',
                Tools::getValue('WISOEXPORT_ACCOUNT')
            );

            Configuration::updateValue(
                'WISOEXPORT_BANK',
                Tools::getValue('WISOEXPORT_BANK')
            );

            Configuration::updateValue(
                'WISOEXPORT_ORDER_STATE',
                (int)Tools::getValue(
                    'WISOEXPORT_ORDER_STATE'
                )
            );

            Configuration::updateValue(
                'WISOEXPORT_TAX_TYPE',
                Tools::getValue(
                    'WISOEXPORT_TAX_TYPE'
                )
            );

            Configuration::updateValue(
                'WISOEXPORT_USE_STATUS_FILTER',
                (int)Tools::getValue(
                    'WISOEXPORT_USE_STATUS_FILTER'
                )
            );

            $output .= $this->displayConfirmation(
                $this->l('Einstellungen gespeichert.')
            );

        }

        return $output . $this->renderConfigurationForm();

    }

    private function initializeConfiguration()
    {
        if (Configuration::get('WISOEXPORT_EXCLUDE_ZERO') === false) {
            Configuration::updateValue('WISOEXPORT_EXCLUDE_ZERO', 0);
        }

        if (Configuration::get('WISOEXPORT_DESCRIPTION') === false) {

            Configuration::updateValue(
                'WISOEXPORT_DESCRIPTION',
                ''
            );

        }

        if (Configuration::get('WISOEXPORT_ACCOUNT') === false) {

            Configuration::updateValue(
                'WISOEXPORT_ACCOUNT',
                ''
            );

        }

        if (Configuration::get('WISOEXPORT_BANK') === false) {

            Configuration::updateValue(
                'WISOEXPORT_BANK',
                ''
            );

        }

        if (Configuration::get('WISOEXPORT_TAX_TYPE') === false) {

            Configuration::updateValue(
                'WISOEXPORT_TAX_TYPE',
                'NULL'
            );

        }

        if (Configuration::get('WISOEXPORT_USE_STATUS_FILTER') === false) {

            Configuration::updateValue(
                'WISOEXPORT_USE_STATUS_FILTER',
                1
            );

        }

        if (Configuration::get('WISOEXPORT_ORDER_STATE') === false) {

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

        }

    }

    private function renderConfigurationForm()
    {

        $fieldsForm = [

            'form' => [

                'legend' => [

                    'title' => $this->l(
                        'WISO EÜR Export Einstellungen'
                    ),

                    'icon' => 'icon-cogs',

                ],

                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Rechnungen mit Betrag 0 ausschließen'),
                        'name' => 'WISOEXPORT_EXCLUDE_ZERO',
                        'desc' => $this->l('Negative Rechnungsbeträge bleiben enthalten. Der Filter prüft den Bruttorechnungsbetrag.'),
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'exclude_zero_on', 'value' => 1, 'label' => $this->l('Ja')],
                            ['id' => 'exclude_zero_off', 'value' => 0, 'label' => $this->l('Nein')],
                        ],
                    ],

                    [
                        'type' => 'text',
                        'label' => $this->l(
                            'Bezeichnung'
                        ),
                        'name' => 'WISOEXPORT_DESCRIPTION',
                    ],

                    [
                        'type' => 'text',
                        'label' => $this->l(
                            'Sachkonto'
                        ),
                        'name' => 'WISOEXPORT_ACCOUNT',
                    ],

                    [
                        'type' => 'text',
                        'label' => $this->l(
                            'Geldkonto'
                        ),
                        'name' => 'WISOEXPORT_BANK',
                    ],

                    [
                        'type' => 'select',
                        'label' => $this->l(
                            'Bestellstatus für Export'
                        ),
                        'name' => 'WISOEXPORT_ORDER_STATE',

                        'options' => [

                            'query' => OrderState::getOrderStates(
                                (int)$this->context->language->id
                            ),

                            'id' => 'id_order_state',

                            'name' => 'name',

                        ],
                    ],

                    [
                        'type' => 'select',
                        'label' => $this->l(
                            'Umsatzsteuerart'
                        ),
                        'name' => 'WISOEXPORT_TAX_TYPE',

                        'options' => [

                            'query' => [

                                [
                                    'id' => 'NULL',
                                    'name' => 'Keine Umsatzsteuer (Kleinunternehmer)',
                                ],

                                [
                                    'id' => 'Brutto',
                                    'name' => 'Brutto',
                                ],

                                [
                                    'id' => 'Netto',
                                    'name' => 'Netto',
                                ],

                            ],

                            'id' => 'id',

                            'name' => 'name',

                        ],
                    ],

                    [
                        'type' => 'switch',
                        'label' => $this->l(
                            'Bestellstatus verwenden'
                        ),
                        'name' => 'WISOEXPORT_USE_STATUS_FILTER',

                        'is_bool' => true,

                        'values' => [

                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Ja'),
                            ],

                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Nein'),
                            ],

                        ],
                    ],

                ],

                'submit' => [

                    'title' => $this->l(
                        'Speichern'
                    ),

                ],

            ],

        ];

        $helper = new HelperForm();

        $helper->module = $this;

        $helper->name_controller = $this->name;

        $helper->token =
            Tools::getAdminTokenLite(
                'AdminModules'
            );

        $helper->currentIndex =
            AdminController::$currentIndex
            . '&configure='
            . $this->name;

        $helper->submit_action =
            'submitWisoExportConfig';

        $helper->fields_value = [
            'WISOEXPORT_EXCLUDE_ZERO' => (int) Configuration::get('WISOEXPORT_EXCLUDE_ZERO'),

            'WISOEXPORT_DESCRIPTION' =>
                Configuration::get(
                    'WISOEXPORT_DESCRIPTION'
                ),

            'WISOEXPORT_ACCOUNT' =>
                Configuration::get(
                    'WISOEXPORT_ACCOUNT'
                ),

            'WISOEXPORT_BANK' =>
                Configuration::get(
                    'WISOEXPORT_BANK'
                ),

            'WISOEXPORT_ORDER_STATE' =>
                Configuration::get(
                    'WISOEXPORT_ORDER_STATE'
                ),

            'WISOEXPORT_TAX_TYPE' =>
                Configuration::get(
                    'WISOEXPORT_TAX_TYPE'
                ),

            'WISOEXPORT_USE_STATUS_FILTER' =>
                Configuration::get(
                    'WISOEXPORT_USE_STATUS_FILTER'
                ),

        ];

        return $helper->generateForm(
            [$fieldsForm]
        );

    }

}