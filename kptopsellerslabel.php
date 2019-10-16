<?php
class KpTopSellersLabel extends Module
{
    public static $topSellersIds = [];

    public function __construct()
    {
        $this->name = 'kptopsellerslabel';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = array(
            'min' => '1.7.6.0',
            'max' => _PS_VERSION_
        );
        $this->author = 'Krystian Podemski';
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Top seller label', array(), 'Modules.TopSellersLabel.Admin');
        $this->description = $this->trans('Module will show "top seller" label on product list.', array(), 'Modules.TopSellersLabel.Admin');
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    public function install()
    {
        return parent::install() && $this->registerHook('actionProductFlagsModifier');
    }

    public function getTopSellers()
    {
        $topsellers = Db::getInstance()->ExecuteS('
            SELECT * FROM `'._DB_PREFIX_.'product_sale`
            ORDER BY quantity DESC, date_upd DESC
            LIMIT 3
        ');

        $topSellersIds = array_map(function($product) {
            return $product['id_product'];
        }, $topsellers);

        return $topSellersIds;
    }

    public function hookActionProductFlagsModifier($params)
    {
        $flags = $params['flags'];
        $product = $params['product'];

        if (!count(self::$topSellersIds)) {
            self::$topSellersIds = $this->getTopSellers();
        }

        if (in_array($product['id_product'], self::$topSellersIds)) {
            $flags['topseller'] = [
                'type' => 'topseller',
                'label' => $this->trans('Top seller', array(), 'Modules.TopSellersLabel.Admin')
            ];
        }

        $params['flags'] = $flags;
    }
}
