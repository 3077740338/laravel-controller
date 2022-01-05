<?php
/*
|----------------------------------------------------------------------------
| TopWindow [ Internet Ecological traffic aggregation and sharing platform ]
|----------------------------------------------------------------------------
| Copyright (c) 2006-2019 http://yangrong1.cn All rights reserved.
|----------------------------------------------------------------------------
| Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
|----------------------------------------------------------------------------
| Author: yangrong <yangrong2@gmail.com>
|----------------------------------------------------------------------------
| 控制器基类
|----------------------------------------------------------------------------
*/
declare (strict_types=1);
namespace Learn;

use Learn\Jump\JumpTrait;
use Learn\View\ViewTrait;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\ControllerMiddlewareOptions;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Symfony\Component\HttpKernel\Exception\HttpException;
abstract class Controller
{
    use JumpTrait, ViewTrait, AuthorizesRequests, DispatchesJobs, ValidatesRequests {
        JumpTrait::__construct as JumpTrait__construct;
        ViewTrait::__construct as ViewTrait__construct;
    }
    /**
     * Request实例
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;
    /**
     * Session实例
     *
     * @var \Illuminate\Session\Store|null
     */
    protected $session;
    /**
     * Route实例
     *
     * @var \Illuminate\Routing\Route|null
     */
    protected $route;
    /**
     * 控制器中间件
     *
     * @var array
     */
    protected $middleware = [];
    /**
     * Object Oriented
     * 
     * @param  \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->request = $app['request'];
        $this->view = $app['view'];
        if (!($this->session = $app['request']->getSession())) {
            throw new \RuntimeException('Please open session');
        }
        if (!($this->route = $app['router']->current())) {
            throw new HttpException(404, 'Not Found');
        }
        $this->registerJumpViewPaths();
        // 初始化操作
        $this->initialize();
    }
    /**
     * 初始化操作
     *
     * @return viod
     */
    protected function initialize()
    {
    }
    /**
     * 注册控制器中间件
     *
     * @param  \Closure|array|string  $middleware
     * @param  array  $options
     * @return \Illuminate\Routing\ControllerMiddlewareOptions
     */
    public function middleware($middleware, array $options = [])
    {
        foreach ((array) $middleware as $m) {
            $this->middleware[] = ['middleware' => $m, 'options' => &$options];
        }
        return new ControllerMiddlewareOptions($options);
    }
    /**
     * 获取控制器中间件
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }
    /**
     * 方法调用
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function callAction($method, $parameters)
    {
        return call_user_func_array([$this, $method], array_values($parameters));
    }
    /**
     * 空操作
     * 
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ($this->app['config']->get('app.debug')) {
            throw new \BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $method));
        }
        throw new HttpException(404, 'Not Found');
    }
}