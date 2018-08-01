<?php
namespace Member\Controller;
use Member\Common\MainController;

// @className 我的收货地址
class AddressController extends MainController {
	
	/**
	 * @apiName 获取地址列表
	 * 
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} address_id: 地址id(忽略则获取列表)
	 * 
	 * @apiSuccessResponse
	 * [
	 * 	{
	 * 		"id": "503",
	 * 		"member_id": "9982",
	 * 		"address": "广州市建设六马路47号201",
	 * 		"zipcode": "0",
	 * 		"linkman": "陈小燕",
	 * 		"telephone": "18029266389",
	 * 		"is_default": "1",
	 * 		"datetime": "2016-01-23 15:48:01",
	 * 		"area_id": "2095",
	 * 		"area_name": "天河",
	 * 		"city_id": "224",
	 * 		"city_name": "广州",
	 * 		"province_id": "19",
	 * 		"province_name": "广东"
	 * 	},
	 * 	{
	 * 		"id": "504",
	 * 		"member_id": "9982",
	 * 		"address": "广州市天河区广和路10号富景花园101室",
	 * 		"zipcode": "0",
	 * 		"linkman": "杨智慧",
	 * 		"telephone": "18988833926",
	 * 		"is_default": "1",
	 * 		"datetime": "2016-01-23 15:48:06",
	 * 		"area_id": "2095",
	 * 		"area_name": "天河",
	 * 		"city_id": "224",
	 * 		"city_name": "广州",
	 * 		"province_id": "19",
	 * 		"province_name": "广东"
	 * 	}
	 * ]
	 */
	Public function getList(){
		$member_id = session('member.id');
		$rs = D('MemberAddressView')->where(['member_id' => $member_id, 'status' => 1])->select();
		$this->ajaxReturn($rs);
	}

	/**
	 * @apiName 获取地址详情
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} address_id: 地址id(忽略则获取列表)
	 *
	 * @apiSuccessResponse
	 * {
	 * 		"id": "503",
	 * 		"member_id": "9982",
	 * 		"address": "广州市建设六马路47号201",
	 * 		"zipcode": "0",
	 * 		"linkman": "陈小燕",
	 * 		"telephone": "18029266389",
	 * 		"is_default": "1",
	 * 		"datetime": "2016-01-23 15:48:01",
	 * 		"area_id": "2095",
	 * 		"area_name": "天河",
	 * 		"city_id": "224",
	 * 		"city_name": "广州",
	 * 		"province_id": "19",
	 * 		"province_name": "广东"
	 * }
	 */
	Public function getDetail(){
		$address_id = I('post.address_id');
		$rs = D('MemberAddressView')->where(['id' => $address_id, 'member_id' => session('member.id'), 'status' => 1])->find();
		if(empty($rs))$this->error('地址不存在!!');
		$this->ajaxReturn($rs);
	}

	/**
	 * @apiName 保存收货地址信息
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} address_id: 要修改的地址ID(添加操作忽略该参数)
	 * @apiPostParam {int} citys_id: 城市ID
	 * @apiPostParam {string} address: 详细地址
	 * @apiPostParam {int} zipcode: 邮政编码(默认510000)
	 * @apiPostParam {string} linkman: 联系人(默认会员昵称)
	 * @apiPostParam {int} telephone: 联系人手机号(默认会员手机号)
	 * @apiPostParam {int} is_default: 是否为默认地址(0-否 1-是)
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "保存成功！",
	 *     "status": 1,
	 *     "url": ""
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "失败原因",
	 *     "status": 0,
	 *     "url": ""
	 * }
	 */
	Public function save(){
		$address_id = I('post.address_id');
		$citys_id = I('post.citys_id');
		$address = I('post.address');
		$zipcode = I('post.zipcode', '510000');
		$linkman = I('post.linkman', session('member.nickname'));
		$telephone = I('post.telephone', session('member.telephone'));
		$is_default = I('post.is_default', 0);

		if(empty($citys_id) || empty($address)){
			$this->error('收货地址和城市填写不全!');
		}

		$data = [
			'citys_id' => $citys_id,
			'address' => $address,
			'zipcode' => $zipcode,
			'linkman' => $linkman,
			'telephone' => $telephone,
			'is_default' => $is_default
		];

		if($is_default == 1){
			M('MemberAddress')->where(['member_id' => session('member.id'), 'is_default' => 1])->save(['is_default' => 0]);
		}

		if(empty($address_id)){
			$data['member_id'] = session('member.id');
			M('MemberAddress')->add($data);
		}else{
			M('MemberAddress')->where(['id' => $address_id, 'member_id' => session('member.id')])->save($data);
		}
		$this->success('保存成功!');
	}

	/**
	 * @apiName 删除收货地址信息
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} address_id: 要删除的地址ID
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "删除成功！",
	 *     "status": 1,
	 *     "url": ""
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "失败原因",
	 *     "status": 0,
	 *     "url": ""
	 * }
	 */
	Public function delete(){
		$address_id = I('post.address_id');

		$rs = M('MemberAddress')->where(array('id' => $address_id))->find();
		if($rs['member_id'] != session('member.id')){
			$this->error('该地址不属于你,无法删除!');
		}
		M('MemberAddress')->where(array('id' => $address_id))->save(array('status' => 0));
		$this->success('删除成功!');
	}
	
}


