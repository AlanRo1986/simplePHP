<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/23 0023
 * Time: 16:52
 *
 * example:
 * $validate = app(ValidateUtils::class);
 * $validate
 *      ->setValidate(new ValidateData("341455770@qq.com",true,"email","请输入正确的邮箱地址"))
 *      ->setValidate(ValidateData::newInstance("password",true,ValidateData::VALIDATOR_LENGTH,"请输入密码,密码长度必须大于5位")->setMin(5))
 *      ->setValidate(ValidateData::newInstance("password",true,ValidateData::VALIDATOR_COMPARE,"两次输入的密码不一致")->setOperator(ValidateData::OPERATOR_EQUIVALENT)->setTo("password"))
 *      ->setValidate(ValidateData::newInstance("23",true,ValidateData::VALIDATOR_RANGE,"年龄范围应该在18~65岁之间")->setMin(18)->setMax(65))
 *      ->setValidate(ValidateData::newInstance("23",true,ValidateData::VALIDATOR_CUSTOM,"输入的值不是整数型")->setRegexp("/(\\d)$/"));
 * $validate->doValidate();//if valid return null.
 *
 *
 */

namespace App\System\Utils;


use App\System\Basic\CompactUtils;
use App\System\Data\ValidateData;

class ValidateUtils extends CompactUtils
{
    /**
     * 存放验证信息
     * @var array
     */
    protected $validates = [];

    /**
     * 验证规则
     * @var array
     */
    protected $validator = [
        "email"=>'/^([.a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\\.[a-zA-Z0-9_-])+/',
        "phone"=>'/^(([0-9]{2,3})|([0-9]{3}-))?((0[0-9]{2,3})|0[0-9]{2,3}-)?[1-9][0-9]{6,7}(-[0-9]{1,4})?$/',
        "mobile"=>'/^1[0-9]{10}$/',
        "url"=>'/^http:(\\/){2}[A-Za-z0-9]+.[A-Za-z0-9]+[\\/=?%-&_~`@\\[\\]\':+!]*([^<>\"\"])*$/',
        "currency"=>'/^[0-9]+(\\.[0-9]+)?$/',
        "number"=>'/^[0-9]+$/',
        "zip"=>'/^[0-9][0-9]{5}$/',
        "qq"=>'/^[1-9][0-9]{4,8}$/',
        "integer"=>'/^[-+]?[0-9]+$/',
        "integerpositive"=>'/^[+]?[0-9]+$/',
        "double"=>'/^[-+]?[0-9]+(\\.[0-9]+)?$/',
        "doublepositive"=>'/^[+]?[0-9]+(\\.[0-9]+)?$/',
        "english"=>'/^[A-Za-z]+$/',
        "chinese"=>'/^[\x80-\xff]+$/',
        "username"=>'/^[\\w]{3,}$/',
        "nochinese"=>'/^[A-Za-z0-9_-]+$/',
    ];

    public function __construct()
    {

    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->setValidates([]);
    }

    /**
     * if valid return null.else return error message.
     * @return null|string
     * @throws \Exception
     */
    public function doValidate(){
        if (!is_array($this->getValidates())){
            throw new \Exception("dose this function must call setValidate() function first.");
        }

        foreach($this->getValidates() as $k => $v){
            //$v = (ValidateData)$v;
            if (TextUtils::isEmpty($v->getInput()) && $v->getRequire() == true){
                $v->setResult(false);
            }else{
                $v->setResult(true);
            }

            if ($v->getResult() && !TextUtils::isEmpty($v->getInput())){
                switch($v->getValidator()){
                    case ValidateData::VALIDATOR_CUSTOM:
                        $v->setResult($this->check($v->getInput(),$v->getRegexp()));
                        break;
                    case ValidateData::VALIDATOR_COMPARE:
                        $result = false;
                        if (!TextUtils::isEmpty($v->getOperator())){
                            switch ($v->getOperator()){
                                case ValidateData::OPERATOR_EQUIVALENT:
                                    $result = $v->getInput() == $v->getTo();
                                    break;
                                case ValidateData::OPERATOR_GREATER:
                                    $result = $v->getInput() > $v->getTo();
                                    break;
                                case ValidateData::OPERATOR_LESS:
                                    $result = $v->getInput() < $v->getTo();
                                    break;
                                case ValidateData::OPERATOR_EQUIVALENT_GREATER:
                                    $result = $v->getInput() >= $v->getTo();
                                    break;
                                case ValidateData::OPERATOR_EQUIVALENT_LESS:
                                    $result = $v->getInput() <= $v->getTo();
                                    break;
                                case ValidateData::OPERATOR_NOT_EQUIVALENT:
                                    $result = $v->getInput() != $v->getTo();
                                    break;
                            }

                        }
                        $v->setResult($result);
                        break;
                    case ValidateData::VALIDATOR_LENGTH:
                        $len = mb_strlen($v->getInput(),'UTF-8');
                        $v->setResult($len >= $v->getMin());
                        if ($v->getMax() > $v->getMin()){
                            $v->setResult($len <= $v->getMax() && $len >= $v->getMin());
                        }

                        break;

                    case ValidateData::VALIDATOR_RANGE:
                        $v->setResult((int)$v->getInput() >= $v->getMin());
                        if ($v->getMax() > $v->getMin()){
                            $v->setResult((int)$v->getInput() <= $v->getMax() && (int)$v->getInput() >= $v->getMin());
                        }

                        break;
                    default:
                        $v->setResult($this->check($v->getInput(),$this->getValidator($v->getValidator())));
                        break;
                }
            }
        }
        return $this->getError();
    }

    /**
     * 需要验证的内容
     *
     *
     * @param ValidateData $validate
     * @return $this
     * $validate input 要验证的值* input 要验证的值
     * require是否必填，true是必填false是可选
     * validator验证的类型:email|phone|mobile
     *      其中Compare，Custom，Length,Range比较特殊。
     *      Compare是用来比较2个字符串或数字，operator和to用来配合使用，operator是比较的操作符(==,>,<,>=,<=,!=)，to是用来比较的字符串；
     *      Custom是定制验证的规则，regexp用来配合使用，regexp是正则表达试；
     *      Length是验证字符串或数字的长度是否在一顶的范围内，min和max用来配合使用，min是最小的长度，max是最大的长度，如果不写max则被认为是长度必须等于min;
     *      Range是数字是否在某个范围内，min和max用来配合使用。
     *
     * 值得注意的是，如果需要判断的规则比较复杂，建议直接写正则表达式。
     *
     * $validate = app(ValidateUtils::class);
     * $validate->setValidate(new ValidateData("asdf",true,"UserName","请输入正确的用户名"));
     * $validate->doValidate();
     */
    public function setValidate(ValidateData $validate)
    {
        $this->validates[] = $validate;
        return $this;
    }

    /**
     * @param array $validates
     */
    protected function setValidates(array $validates)
    {
        $this->validates = $validates;
    }

    /**
     * 正则表达式运算
     * @param string $str
     * @param string $validator
     * @return bool
     */
    protected function check(string $str = '',string $validator = ''):bool {
        if (!TextUtils::isEmpty($str) && !TextUtils::isEmpty($validator)){
            if (preg_match($validator,$str)){
                return true;
            }
            else{
                return false;
            }
        }
        return true;
    }

    /**
     * 错误信息
     * @return null
     */
    protected function getError(){
        foreach($this->getValidates() as $k => $v){
            if ($v->getResult() == false){
                return $v->getMessage();
            }
        }
        return null;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getValidator(string $key): string
    {
        return $this->validator[$key];
    }

    /**
     * @return array
     */
    protected function getValidates(): array
    {
        return $this->validates;
    }


}