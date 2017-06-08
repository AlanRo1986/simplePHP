<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/5/29 0029
 * Time: 21:30
 */

namespace App\System\Data;


use App\System\Utils\TextUtils;

class ValidateData
{
    /**
     * 必须
     * 如果validator传入的是custom,那么必须设置regexp,regexp是一个有效的正则表达式
     */
    const VALIDATOR_CUSTOM = "custom";

    /**
     * 比较
     */
    const VALIDATOR_COMPARE = "compare";

    /**
     * 长度
     * 需要传入min&max进行长度匹配,允许不传入max进行最大长度匹配
     */
    const VALIDATOR_LENGTH = "length";

    /**
     * 范围
     * 如果validator传入range那么,必须传入min&max进行范围判断
     */
    const VALIDATOR_RANGE = "range";

    /**
     * operator主要的参数,如果设置了operator那么需要传入to这个值,一般是double
     */
    const OPERATOR_EQUIVALENT = "==";
    const OPERATOR_GREATER = ">";
    const OPERATOR_EQUIVALENT_GREATER = ">=";
    const OPERATOR_LESS = "<";
    const OPERATOR_EQUIVALENT_LESS = "<=";
    const OPERATOR_NOT_EQUIVALENT = "!=";

    /**
     * 自定义传入的正则表达式
     */
    protected $regexp = null;

    /**
     * 输入的内容,通常为字符串型
     */
    protected $input = "";
    protected $require = true;
    protected $result = true;
    protected $validator = "";

    /**
     * 运算
     */
    protected $operator = ValidateData::OPERATOR_EQUIVALENT;
    protected $to = null;
    protected $min = 0;
    protected $max = 0;
    protected $message = "验证失败";

    /**
     * ValidateData constructor.
     * @param string $input 输入的内容
     * @param bool $require 是否必须输入
     * @param string $validator 验证器
     * @param string $message 反馈的提示信息
     */
    public function __construct(string $input,bool $require,string $validator,string $message)
    {
        $this->setInput($input)->setRequire($require)->setValidator($validator)->setMessage($message);
    }

    /**
     * @param string $input
     * @param bool $require
     * @param string $validator
     * @param string $message
     * @return ValidateData
     */
    public static function newInstance(string $input,bool $require,string $validator,string $message){
        return new ValidateData($input,$require,$validator,$message);
    }

    /**
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return int
     */
    public function getMax(): int
    {
        return $this->max;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getMin(): int
    {
        return $this->min;
    }

    /**
     * @return mixed
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return mixed
     */
    public function getRegexp()
    {
        return $this->regexp;
    }

    /**
     * @return null
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * @return bool
     */
    public function getRequire(){
        return $this->require;
    }

    /**
     * @return bool
     */
    public function getResult(){
        return $this->result;
    }

    /**
     * @param mixed $input
     * @return $this
     */
    public function setInput($input)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * @param boolean $require
     * @return $this
     */
    public function setRequire(bool $require)
    {
        $this->require = $require;
        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param int $max
     * @return $this
     */
    public function setMax(int $max)
    {
        $this->max = $max;
        return $this;
    }

    /**
     * @param int $min
     * @return $this
     */
    public function setMin(int $min)
    {
        $this->min = $min;
        return $this;
    }

    /**
     * @param mixed $operator
     * @return $this
     */
    public function setOperator(string $operator)
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * 正则表达式,当$validator == VALIDATOR_CUSTOM 时,需要设置此参数
     * @param mixed $regexp
     * @return $this
     */
    public function setRegexp(string $regexp)
    {
        $this->regexp = $regexp;
        return $this;
    }

    /**
     * @param boolean $result
     * @return $this
     */
    public function setResult(bool $result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @param null $to
     * @return $this
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @param string $validator
     * @return $this
     */
    public function setValidator(string $validator)
    {
        $this->validator = TextUtils::lower($validator);
        return $this;
    }


}