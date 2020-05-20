<?php


namespace App\System;


use EasySwoole\Template\RenderInterface;
//use think\facade\Template;

class ThinkTemplate implements RenderInterface
{
    // tp模板类对象
    private $_topThinkTemplate;

    public function __construct()
    {
        $temp_dir = sys_get_temp_dir();
        $config = [
            'view_path' => EASYSWOOLE_ROOT . '/App/HttpTemplate/', // 模板存放文件夹根目录
            'cache_path' => $temp_dir, // 模板文件缓存目录
            'view_suffix' => 'html' // 模板文件后缀
        ];
        $this->_topThinkTemplate = new \think\Template($config);
    }

    public function afterRender(?string $result, string $template, array $data = [], array $options = [])
    {
    }
    // 当模板解析出现异常时调用
    public function onException(\Throwable $throwable): string
    {
        $msg = $throwable->getMessage() . " is file " . $throwable->getFile() . ' of line' . $throwable->getLine();
        trigger_error($msg);
        return $msg;
    }
    // 渲染逻辑实现
    public function render(string $template, array $data = [], array $options = []): ?string
    {
        foreach ($data as $k => $v) {
            $this->_topThinkTemplate->assign([$k => $v]);
        }
        // Tp 模板渲染函数都是直接输出 需要打开缓冲区将输出写入变量中 然后渲染的结果
        ob_start();
        $this->_topThinkTemplate->fetch($template);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}