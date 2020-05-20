<?php


namespace App\HttpController;

use App\Lib\Redis\RedisSession;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\FastCache\Cache;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Template\Render;
use EasySwoole\Validate\Validate;
use App\Lib\StatusCode;

/* App下所有控制器基类*/
class Base extends Controller
{
    public $template_data = [];
    public $params;

    function index(){
        //return $this->response()->write(1);
    }

    /**
     * 重写的拦截器，目前用来做请求校验，子类可根据需求重写
     * @param string|null $action
     * @return bool|null
     */
    public function onRequest(?string $action): ?bool
    {
        /* 加载请求参数自动验证器 */
        if(!$this->autoValidate($action)) return false;

        /* 请求参数处理 */
        $this->getParams();

        return true;

    }

    /**
     * 异常拦截
     * @param \Throwable $throwable
     * @throws \Throwable
     */
    protected function onException(\Throwable $throwable): void
    {
        $msg = $throwable->getMessage();
        // 写日志 后续可加入邮件、短信...
        Logger::getInstance()->console($msg,Logger::LOG_LEVEL_INFO,'debug');

        $msgArr = explode('--', $msg);
        if (!empty(count($msgArr))){
            $msg = $msgArr[count($msgArr) - 1];
        }
        //var_dump($msg);
        if( !empty(\Yaconf::get('es_conf.app.app_debug')) )
            // 调试阶段
            $this->writeJson($throwable->getCode(), $msg);
         else
            // 生产阶段
            $this->writeJson($throwable->getCode(), '请稍后再试');

        $this->response()->end();
    }

    /**
     * 重写validate，支持解密后的参数
     * @param Validate $validate
     * @return bool
     */
    protected function validate(Validate $validate)
    {
        //var_dump($this->request()->decryptParams);
        return $validate->validate($this->request()->decryptParams ?: []);
    }

    /** 自动校验
     * @param $action
     * @return bool
     */
    protected function autoValidate($action) {
        $ret =  parent::onRequest($action);
        if($ret === false){
            return false;
        }
        $v = $this->validateRule($action);
        if($v){
            $ret = $this->validate($v);
            if($ret == false){
                $this->writeJson(StatusCode::WARNING,
                    "{$v->getError()->getField()}@{$v->getError()->getFieldAlias()}:{$v->getError()->getErrorRuleMsg()}");
                return false;
            }
        }
        return true;
    }

    /**
     * 提前定义，防止子类继承后不定义这个方法，会导致拦截器里，调用不到报错
     * 子类控制器需要验证器时，重写这个方法，不需要时可以忽略
     * @param string|null $action
     * @return Validate|null
     */
    protected function validateRule(?string $action):?Validate{
        return null;
    }

    /**
     * 获取请求参数值基础封装，子类可根据需求重写
     */
    protected function getParams()
    {
        $this->params = $this->request()->decryptParams;

        // 在swoole中PHP自带全局变量全是空值，但又不能自己定义全局变量（指的是会话期全局变量）
        // 可以把自带的 当做全局作用域的变量容器来使用，在model和server层都可以使用
        // 把ip放全局变量里面
        $_POST['ip'] = $this->clientRealIP();
    }

    /**
     * 获取用户的真实IP
     * @param string $headerName 代理服务器传递的标头名称
     * @return string
     */
    protected function clientRealIP($headerName = 'x-real-ip')
    {
        $server = ServerManager::getInstance()->getSwooleServer();
        $client = $server->getClientInfo($this->request()->getSwooleRequest()->fd);
        $clientAddress = $client['remote_ip'];
        $xri = $this->request()->getHeader($headerName);
        $xff = $this->request()->getHeader('x-forwarded-for');
        if ($clientAddress === '127.0.0.1')
        {
            if (!empty($xri))
            {  // 如果有xri 则判定为前端有NGINX等代理
                $clientAddress = $xri[0];
            }
            elseif (!empty($xff))
            {  // 如果不存在xri 则继续判断xff
                $list = explode(',', $xff[0]);
                if (isset($list[0])) $clientAddress = $list[0];
            }
        }
        return $clientAddress;
    }

    /**
     * 实现TP模板方法
     * @param $name
     * @param $value
     */
    public function assign($name, $value = []) {

        if (is_array($name)) {
            $this->template_data = array_merge($this->template_data, $name);
        } else {
            $this->template_data[$name] = $value;
        }
    }

    /**
     * 实现TP模板方法
     * @param $template_name
     * @return string|null
     */
    public function fetch($template_name, $value = []) {

        $this->template_data = array_merge($this->template_data, $value);
        $this->response()->write(Render::getInstance()->render($template_name, $this->template_data));
        $this->response()->end();

    }


    /**
     * 重写writeJson方法
     * @param int $statusCode
     * @param null $message
     * @param null $result
     * @return bool
     */
    public function writeJson($statusCode = 200, $message = null, $result = null)
    {
        if (!$this->response()->isEndResponse()) {
            $data = Array(
                "code" => $statusCode,
                "msg" => $message,
                "result" => $result
            );
            $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response()->withStatus($statusCode);
            return true;
        } else {
            return false;
        }
    }


}