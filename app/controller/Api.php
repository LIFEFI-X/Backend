<?php

namespace app\controller;

use app\BaseController;
use think\facade\Log;
use xy_jx\Utils;

class Api extends BaseController
{

    public function index()
    {
        \Api::success(\xy_jx\Utils\Encryption::resetKey());
    }

    /**
     * Modify specific content within the file
     * @return void
     */
    public function put_file()
    {
        $f = '../vendor/xy_jx/utils/src/Encryption.php';
        $s = uniqid(mt_rand(100, 999));
        $fileGet = file_get_contents($f);
        $file = str_replace('6f1b1d693ec48c9fdda723018eeb73fa', md5($s), $fileGet);
        $file = str_replace('encrypt@decrypt@', $s, $file);
        \Api::success(file_put_contents($f, $file));
    }

    /**
     * Retrieve request data
     * @return void
     */
    public function getData()
    {
        $cs = $this->getParam([
            ['name', '999.9',''],
            ['age', 10],
            ['email', 'qq@qq.com'],
            ['data', 'aaaaaaaaaaaaa'],
            'sex/d'=>'1'
        ], [
            'name' => 'require|max:25',
            'age' => 'number|between:1,120',
            'email' => 'email',
        ], [
            'name.require' => 'Name is required',
            'name.max' => 'Name cannot exceed 25 characters',
            'age.number' => 'Age must be a number',
            'age.between' => 'Age must be between 1 and 120',
            'email' => 'Invalid email format',
        ]);
        \Api::success($cs);
    }

    /**
     * Write log entries in real time
     * @return void
     */
    public function log()
    {
        Log::write('aaaaaaaa', 'loh');
        \Api::success('log');
    }

    public function cs()
    {
        \Api::success(Utils\Rmb::rmbCapital(15554.4));

    }

    // Export requires installing the extra package: composer require phpoffice/phpspreadsheet
    public function export()
    {
        // Exported data as a two-dimensional array.
        $list = [
            ['name' => 'name_A', 'area_name' => 'Beijing', 'account' => 'beijing', 'date' => '2020-01-01'],
            ['name' => 'name_B', 'area_name' => 'Shenzhen', 'account' => 'shenzhen', 'date' => '2020-01-02'],
            ['name' => 'name_C', 'area_name' => 'Shanghai', 'account' => 'shanghai', 'date' => '2020-01-03']
        ];

        Utils\\Excel::header('Account Export', ['name' => 'Name', 'account' => 'Account', 'area_name' => 'City', 'date' => 'Date'], 'Total exported: ' . count() . ' rows')
            ->content($list)
            ->save();
    }

    public function cs1()
    {
        $cache = \Utils::redis();
        for ($i = 1; $i < 10; $i++) {
            $orderInfo = ['goods' => mt_rand(1, 10000), 'uid' => mt_rand(1, 20), 'num' => mt_rand(1, 5)];
            $cache->lPush('order:create:list', json_encode([
                'goods' => $orderInfo['goods'],
                'uid' => $orderInfo['uid'],
                'num' => $orderInfo['num']
            ]));
        }
    }

    public function getData123()
    {
        $orderInfo = $this::getParam([
            ['goods', mt_rand(1, 3)],
            ['uid', mt_rand(1, 100)],
            ['num', mt_rand(1, 5)]
        ]);
        $redis = \Utils::redis();
        $redis->lPush('order:createList', json_encode([
            'goods' => $orderInfo['goods'],
            'uid' => $orderInfo['uid'],
            'num' => $orderInfo['num']
        ]));
        for ($i = 0; $i < 500; $i++) {
            if ($result = $redis->get('order_list_' . $orderInfo['uid'] . '_' . $orderInfo['goods'])) {
                $data = json_decode($result, true);
                \Api::success($data['data'], $data['code'], $data['msg']);
            }
            //  usleep(10000);
            usleep(7000);
        }
        \Api::success(['goods' => $orderInfo['goods']], 201, 'In queue...');
    }
}


