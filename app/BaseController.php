<?php
declare (strict_types=1);

namespace app;

use app\common\enums\ErrorCode;
use think\App;
use think\exception\ValidateException;
use think\Response;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected \app\Request $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected \think\App $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];
    /**
     * @var int 登录用户id
     */
    protected $uid = 0;
    protected $token = "";
    protected \app\common\model\User $user;
    /**
     * @var string[] 访问控制
     */
    protected $access = ['*'];

    /**
     * 构造方法
     * @access public
     * @param App $app 应用对象
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
        $this->checkLogin();
    }

    protected function checkLogin()
    {
        $token = $this->request->header("Authorization");
        $this->token = $token;
        $uid = validate_token($token);
        if ($uid) {
            session("uid", $uid);
            $this->uid = $uid;
//            检查账号状态
            $user = app('app\\common\\model\\User')->where("id", $uid)->find();
            if (empty($user)) {
                $this->error("账号异常，请重新登录", ErrorCode::NOT_LOGIN->value);
            }
            if ($user->status != 0) {
//                账号异常状态
                if ($user->status == 1) {
                    $this->error("账号已被冻结", ErrorCode::ACCOUNT_FREEZE->value);
                }
            }
            $this->user = $user;
            return true;
        }
        if (in_array('*', $this->access)) {
            return true;
        }
        if (in_array($this->request->action(), $this->access)) {
            return true;
        }
        $this->error("当前未登录", ErrorCode::NOT_LOGIN->value);
    }

    protected function resp($code = ErrorCode::SUCCESS->value, $msg = "", $data = []): void
    {
        $r = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ];
        $this->request->setCustomResponse($r);
        abort(json($r));
    }

    /**
     * 返回请求成功的json响应
     * @param $msg
     * @param $data
     * @return void
     */
    protected function success($msg = "请求成功", $data = []): void
    {
        $this->resp(ErrorCode::SUCCESS->value, $msg, $data);
    }

    /**
     * 返回请求失败的json响应
     * @param $msg
     * @param $code
     * @param $data
     * @return void
     */
    protected function error($msg = "请求失败", $code = ErrorCode::ERROR->value, $data = []): void
    {
        $this->resp($code, $msg, $data);
    }

    /**
     * 验证数据
     * @access protected
     * @param array $data 数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

}
