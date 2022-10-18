<?php

namespace Onlyoung4u\AsApi\Kernel;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Validator;

class AsValidator
{
    private static $translators = [];

    /**
     * 获取翻译器
     *
     * @param string $lang
     * @return Translator
     */
    private static function getTranslator(string $lang): Translator
    {
        $path = config('plugin.onlyoung4u.as-api.app.validator.lang_path');

        $filesystem = new Filesystem();
        $loader = new FileLoader($filesystem, $path);
        $loader->addNamespace('lang', $path);
        $loader->load($lang, 'validation', 'lang');

        return new Translator($loader, $lang);
    }

    /**
     * 获取验证器实例
     *
     * @param string|null $lang
     * @return Factory
     */
    public static function getInstance(string|null $lang = null): Factory
    {
        if (empty($lang)) {
            $lang = config('plugin.onlyoung4u.as-api.app.validator.lang', 'zh_CN');
        }

        if (!isset(self::$translators[$lang])) {
            self::$translators[$lang] = self::getTranslator($lang);
        }

        return new Factory(self::$translators[$lang]);
    }

    /**
     * 验证
     *
     * @param array $params
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @param string|null $lang
     * @return Validator
     */
    public static function validate(array $params, array $rules, array $messages = [], array $customAttributes = [], string|null $lang = null): Validator
    {
        $instance = self::getInstance($lang);

        $v = $instance->make($params, $rules, $messages, $customAttributes);

        return $v;
    }

    /**
     * 验证
     *
     * @param array $params
     * @param array $customRules
     * @param string|null $lang
     * @return Validator
     */
    public static function asValidate(array $params, array $customRules, string|null $lang = null)
    {
        $rules = [];
        $messages = [];
        $customAttributes = [];

        foreach ($customRules as $key => $rule) {
            $rules[$key] = $rule[0];
            if (isset($rule[2])) $messages[$key] = $rule[2];
            if (isset($rule[1])) $customAttributes[$key] = $rule[1];
        }

        return self::validate($params, $rules, $messages, $customAttributes, $lang);
    }
}