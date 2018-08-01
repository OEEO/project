<?php
namespace Daren\Controller;
use Daren\Common\MainController;

// @className 用户答疑
class AskController extends MainController {
	
	/**
	 * @apiName 获取新的提问列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * [
	 *     {
	 *         "content": "测试问答是否正常？测试问答是否正常？测试问答是否正常？测试问答是否正常？",
	 *         "answer": null,
	 *         "datetime": "2016-01-11 17:42:17",
	 *         "nickname": "w!nG",
	 *         "path": "http://wx.qlogo.cn/mmopen/hRkOoB5ZTmItKSDhKPNvian0YmiaFzYaLw9tx1RaNZGWGxVicOH8rpqU2kA4XzVPaTsP3xEQNIpGW8hhsGfHvIT5w/0",
	 *         "title": "吖咪烘焙课|小花的星空糖教学",
	 *         "tips_member_id": "9982",
	 *         "catname": "已售罄"
	 *     },
	 *     {
	 *         "content": "再次测试问答是否合理！能否回答！",
	 *         "answer": null,
	 *         "datetime": "2016-01-11 17:42:45",
	 *         "nickname": "石頭貓",
	 *         "path": "http://wx.qlogo.cn/mmopen/Oe67HqhICYsteVviadFCsCTcAf11AFOCgibCRZeKPEJicnHGic3ZajNeOEDTbOaiaH66kRoGc04Bibm30n2ejzKqE8wfX1kwKnO6up/0",
	 *         "title": "吖咪烘焙课|小花的星空糖教学",
	 *         "tips_member_id": "9982",
	 *         "catname": "已售罄"
	 *     }
	 * ]
	 */
	public function getList(){
		$rs = D('AskView')->where(array('tips_member_id' => session('member.id'), 'answer is null'))->select();
		if(empty($rs))$this->ajaxReturn(array());
		$this->ajaxReturn($rs);
	}
	
	/**
	 * @apiName 提交回答内容
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {string} ask_id: 问题ID
	 * @apiPostParam {string} answer: 回答内容（不超过250个字节）
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "回答成功！",
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
	Public function reply(){
		$ask_id = I('post.ask_id');
		$answer = I('post.answer');
		
		if(empty($ask_id) || empty($answer)){
			$this->error('问题ID和回答内容不能为空！');
		}
		if(strlen($answer) > 250){
			$this->error('回答内容太长了，不能超过250字节！');
		}
		
		$rs = M('feedback')->where(array('id' => $ask_id))->save(array('answer' => $answer));
		if($rs && $rs > 0){
			$this->success('回答成功！');
		}else{
			$this->error('回答失败！');
		}
	}
	
}