<?php
namespace admin;
use Ivy\core\Controller;
use Ivy\core\CException;
class YunController extends Controller
{
	const EXPIRE_TIME = 86400; //token过期时间，默认一天
	
	public function indexAction() {
		$model = \admin\CompanyInfo::model()->findAll("status=1");
		$this->view->assign(array(
			'model' => $model,
		))->display('index');
	}

	public function auditAction() {
		if(!$this->getIsAjax()) {
			throw new CException('非法访问');
		}
		$company_id = isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : 0;
		if(empty($company_id)) {
			throw new CException('无效参数');
		}
		//审核通过
		$model = \admin\CompanyInfo::model()->findByPk($company_id);
		if(empty($model)) {
			throw new CException('无法找到Model');
		}
		$model->database = 'beauty_'.$company_id.'_'.uniqid();
		$token = \Utils::getRandomKeys(32);
		$token_endtime = time() + self::EXPIRE_TIME;
		$model->token = $token;
		$model->token_endtime = $token_endtime;
		$host_info = $this->getHostInfo();
		$active_url = $host_info . $this->url('index/register3', array('id' => $model->id, 'token' => $token));
		$active_url = htmlspecialchars($active_url);
		if($model->save()) {
			//发送邮件
            \Ivy::importExt('phpmailer/SendMail');
			$mailObj = new \SendMail();
			$company_name = $model->company_name;
			$body = "<h3>{$company_name}:</h3>"
					."<p>您好！</p>"
					."<p>您的注册申请已通过，请点击<a href='{$active_url}'>注册激活</a>以进行下一步操作!</p>"
					."<p>如果以上链接无法点击，请复制一下链接至浏览器访问！</p>"
					."<p>{$active_url}</p>";
	
			$mailObj->SmtpSendMail($model->email, '好哇连锁管控平台注册激活', $body);
			$this->ajaxReturn('200', '审核成功');
		}
		else {
			$this->ajaxReturn('500', '审核失败');
		}
	}
}