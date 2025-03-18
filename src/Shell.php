<?php

namespace Zhubaiming\Cornerstone;

final class Shell
{
    private static $instance = null;

    private static array $config = [];

    // 存储容器中的对象实例或闭包
    protected static $entries = [];

    // 存储单例对象实例
    protected $instances = [];

    // 解析规则
    protected static $rules = [];

    // 防止外部实例化
    private function __construct()
    {
        $this->initialize();
    }

    // 防止克隆
    public function __clone(): void
    {
        throw new \Exception("Singleton can not clone");
    }

    // 防止反序列化
    public function __wakeup(): void
    {
        throw new \Exception("Singleton can not unserialize");
    }

    /**
     * 获取单例实例
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 注册服务
     *
     * @param string $service
     * @return void
     */
    public static function registerService(string $service): void
    {
        self::getInstance();
        if (!isset(self::$instance->instances[$service])) {
            self::$instance->instances[$service] = self::$instance->resolve($service);
        }

        /*
        $instances = [
            'Yansongda\Pay\Provider\Alipay::class' => new Yansongda\Pay\Provider\Alipay(),
            'Yansongda\Pay\Provider\Wechat::class' => new Yansongda\Pay\Provider\Wechat(),
            'Yansongda\Pay\Provider\Unipay::class' => new Yansongda\Pay\Provider\Unipay(),
            'Yansongda\Pay\Provider\Jsb::class' => new Yansongda\Pay\Provider\Jsb(),
            'Yansongda\Pay\Provider\Douyin::class' => new Yansongda\Pay\Provider\Douyin(),
        ];
        */
    }

    // 解析出真正的对象
    protected function resolve($alias)
    {
        // 获取反射
        try {
            $reflector = new \ReflectionClass($alias);
        } catch (\ReflectionException $e) {
        }

        // 是否是接口
        if ($reflector->isInterface()) {
            // 根据类型提示接口解析具体到类
            return $this->resolveInterface($reflector);
        }

        // 无法实例化的类 -- 直接异常
        if (!$reflector->isInstantiable()) {
            throw new ContainerException("{$reflector->getName()} cannot be instantiated， id:{$alias}");
        }

        // 获取构造函数
        $constructor = $reflector->getConstructor();

        // 没有构造函数，可以直接实例化
        if (null === $constructor) {
            return $reflector->newInstance();
        }

        // 有构造函数，就获取全部参数再实例化
        $args = $this->getArguments($alias, $constructor);

        return $reflector->newInstanceArgs($args);
    }


    // 解析接口类型
    protected function resolveInterface(\ReflectionClass $reflector)
    {
        if (isset($this->rules['substitute'][$reflector->getName()])) {
            return $this->get($this->rules['substitute'][$reflector->getName()]);
        }

        // 返回全部的类
        $classes = get_declared_classes();

        // 循环判断是否有符合的接口实现类
        foreach ($classes as $class) {
            $rf = new \ReflectionClass($class);
            if ($rf->implementsInterface($reflector->getName())) {
                return $this->get($rf->getName());
            }
        }

        throw new NotFoundException(
            "Class {$reflector->getName()} not found",
            404
        );
    }

    protected function getArguments($alias, \ReflectionMethod $constructor)
    {
        $args = [];
        $parameters = $constructor->getParameters();

        foreach ($parameters as $param) {
            // 获取参数类型
            $parameterType = $param->getType();
            assert($parameterType instanceof \ReflectionNamedType);
            // 参数类型名
            $parameterTypeName = $parameterType->getName();
            // 形参名称
            $parameterName = $param->getName();

            if (class_exists($parameterTypeName)) {
                // 如果类存在
                $args[] = new $parameterTypeName();
            } elseif (isset($this->rules[$alias][$parameterName])) {
                // 查看解析规则中是否有 -- 变量名映射
                $args[] = $this->rules[$alias][$parameterName];
            } elseif (isset($this->rules[$alias][$parameterTypeName])) {
                // 查看解析规则中是否有 -- 类名映射
                $args[] = $this->rules[$alias][$parameterTypeName];
            } elseif ($param->isDefaultValueAvailable()) {
                // 如果有默认值
                $args[] = $param->getDefaultValue();
            } elseif (interface_exists($parameterTypeName)) {
                // 如果接口存在
                $args[] = $this->resolveInterface(new \ReflectionClass($parameterTypeName));
            }
        }

        return $args;
    }

    private function initialize()
    {
        self::$config = config('pay');
    }
}