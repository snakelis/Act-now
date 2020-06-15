<?php

namespace app\controller;

use think\facade\Db;
use think\facade\View;

class Index extends Base
{

    CONST STATUS = [
        0 => '未打卡',
        1 => '打卡完成',
    ];

    /**
     * @description
     * @param string $name
     * @return string
     * @throws
     * @author  李顺
     * @create : 2020/6/15 14:23
     */
    public function index()
    {
        $total_money = 0;

        $today = date('Y-m-d');

        $field = [
            'target.id',
            'target.name',
            'target.money',
            'target_record.clock_in_data',
            'target_record.status'
        ];
        $target_list = Db::table('act_target')->alias('target')->field($field)->leftJoin('act_target_record target_record', 'target_record.target_id = target.id and target_record.clock_in_data = "' . $today . '"')->select();

        $target_total = Db::table('act_target_record')->alias('target_record')->field('count(id) as total_num,target_id')->where('status = 1')->group('target_id')->select();
        $target_total = $target_total->toArray();
        $target_total = array_column($target_total, null, 'target_id');

        $target_list = $target_list->toArray();
        foreach ($target_list as &$item) {
            if (empty($item['clock_in_data'])) {
                $item['status'] = 0;
            }
            $item['total_num'] = !empty($target_total[$item['id']]['total_num']) ? $target_total[$item['id']]['total_num'] : 0;
            $item['total_money'] = $item['total_num'] * $item['money'];
            $item['status_desc'] = self::STATUS[$item['status']];

            $total_money += $item['total_money'];
        }


        View::assign('target_list', $target_list);
        View::assign('total_money', $total_money);

        return View::fetch();
    }


    public function clock_in()
    {
        $target_id = $this->request->param('target_id');
        if (empty($target_id)) {
            return $this->error_json('please choose target!');
        }
        $today = date('Y-m-d');
        $target_detail = Db::table('act_target_record')->alias('target_record')->where('clock_in_data = "' . $today . '"')->group('target_id')->find();
        if (!empty($target_detail)) {
            if ($target_detail['status'] != 1) {
                $update_data = [
                    'status' => 1
                ];
            } else {
                $update_data = [
                    'status' => 0
                ];
            }
            Db::table('act_target_record')->where('id', $target_detail['id'])->update($update_data);
        } else {
            $save_data = [
                'target_id' => $target_id,
                'clock_in_data' => $today,
                'status' => 1,
            ];
            Db::table('act_target_record')->insert($save_data);
        }

        return $this->success_json();
    }
}
