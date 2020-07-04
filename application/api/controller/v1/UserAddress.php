<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/10
 * Time: 15:54
 */

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\user\model\Address as AddressModel;
use app\common\model\Area;
use service\ApiReturn;
use think\Db;

/**
 * 用户收货地址
 * Class UserAddress
 * @package app\api\controller\v1
 */
class UserAddress extends Base
{

    /**
     * 地址列表
     * @param array $data
     * @param array $user
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 2019/4/11 13:40
     * @return \think\response\Json
     */
    public function address_list($data = [], $user = [])
    {
        $where[] = ['user_id', 'eq', $user['id'] ? $user['id'] : 2];
        $addressList = AddressModel::where($where)->field("address_id,name,is_default,mobile,address,province,city,district,postal_code")->order("address_id desc")->select();
        foreach($addressList as &$val){
            $val = $this->filter($val,$this->fname);
        }
        if ($addressList) {
            return ApiReturn::r(1, $addressList, '请求成功');
        }
        return ApiReturn::r(0, [], '暂无收货地址！');
    }

    /**
     * 添加收货地址
     * @param array $data
     * @param array $user
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/23 17:40
     */
    public function add_address($data = [], $user = [])
    {
        if ($data['is_default'] == 1) {
            //查看用户是否存在默认收货地址
            $where[] = ['user_id', 'eq', $user['id'] ? $user['id'] : 2];
            $where[] = ['is_default', 'eq', 1];
            $userAddress = AddressModel::get_one_address($where);
            if ($userAddress) {
                //修改用户默认地址
                @AddressModel::where($where)->update(['is_default' => 0]);
            }
        }
        //进行添加
        $data['user_id'] = $user['id'];
        $data['status'] = 1;
        $data['province_id'] = Area::getIdByName($data['province'], 1);
        $data['city_id'] = Area::getIdByName($data['city'], 2, $data['province_id']);
        $data['district_id'] = Area::getIdByName($data['district'], 3, $data['city_id']);
        $result = AddressModel::create($data);
        if ($result) {
            return ApiReturn::r(1, [], '添加成功');
        }
        return ApiReturn::r(0, [], '添加失败');
    }

    /**
     * 获得单条收货地址
     * @param array $data
     * @param array $user
     * @author  风轻云淡
     * @return \think\response\Json
     */
    public function get_one_address($data = [], $user = [])
    {
        if($data['address_id']){
            $addressId = $data['address_id'];
            $where[] = ['address_id', 'eq', $addressId];
        }else{
            $where[] = ['is_default', 'eq', 1];
        }
        $where[] = ['user_id', 'eq', $user['id']];
        $getAddress = AddressModel::get_one_address($where);
        if ($getAddress) {
            return ApiReturn::r(1, $this->filter($getAddress), '请求成功');
        } else {
            return ApiReturn::r(1, [], '暂无数据');
        }

    }

    /**
     * 修改收货地址
     * @param array $data
     * @param array $user
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/23 17:39
     */
    public function edit_address($data = [], $user = [])
    {
        Db::startTrans();
        try{
            if ($data['is_default'] == 1) {
                //取消所有的默认地址
                $userAddress = AddressModel::where(['user_id'=>$user['id'],'is_default'=>1])->update(['is_default' => 0]);
                if(!$userAddress){
                    exception('操作无效');
                }
            }
            $data['province_id'] = Area::getIdByName($data['province'], 1);
            $data['city_id'] = Area::getIdByName($data['city'], 2, $data['province_id']);
            $data['district_id'] = Area::getIdByName($data['district'], 3, $data['city_id']);
            $result = AddressModel::where(['address_id' => $data['address_id'],'user_id'=>$user['id']])->update($data);
            if(!$result){
                exception('修改失败');
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ApiReturn::r(0, [], $e->getMessage());
        }
        return ApiReturn::r(1, [], '修改成功');
    }

    /**
     * 删除收货地址
     * @param array $data
     * @param array $user
     * @author  风轻云淡
     * @return \think\response\Json
     */
    public function del_address($data = [], $user = [])
    {
        $addressIds = $data['address_ids'];
        $where[] = ['address_id', 'in', explode(",", rtrim($addressIds, ","))];
        $where[] = ['user_id', 'eq', $user['id']];
        $result = AddressModel::where($where)->delete();
        if ($result) {
            return ApiReturn::r(1, [], '删除成功');
        }
        return ApiReturn::r(0, [], '删除失败！');
    }

    /**
     * 修改为默认收货地址
     * @param array $data
     * @param array $user
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/23 17:39
     */
    public function change_default_address($data = [], $user = [])
    {
        Db::startTrans();
        try{
            //取消所有的默认地址
            $userAddress = AddressModel::where(['user_id'=>$user['id'],'is_default'=>1])->update(['is_default' => 0]);
            if(!$userAddress){
                exception('操作无效');
            }
            //修改默认收货地址
            $result = AddressModel::where(['user_id' => $user['id'], 'address_id' => $data['address_id']])->update(['is_default' => 1]);
            if(!$result){
                exception('设置默认地址失败');
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ApiReturn::r(0, [], '操作失败!');
        }
        return ApiReturn::r(1, [], '操作成功');
    }
}