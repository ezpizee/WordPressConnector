<?php

namespace Ezpizee\ConnectorUtils;

class Endpoints
{
    const INSTALL = '/api/install';
    const LOGIN = '/api/user/login';
    const GET_TOKEN = '/api/user/token';
    const LOGOUT = "/api/user/logout";
    const REGISTER = "/api/user/add";
    const ACTIVATE = "/api/user/activate/{id}";
    const PRODUCT_ITEM = "/api/pim/product/item/{id}";
    const PRODUCT_PRICE = "/api/pim/product/price/{id}";

    public static function endpoint(string $str, array $params=[]): string {
        if (!empty($params)) {
            foreach ($params as $k=>$v) {
                $str = str_replace('{'.$k.'}', $v, $str);
            }
        }
        return $str;
    }
}
