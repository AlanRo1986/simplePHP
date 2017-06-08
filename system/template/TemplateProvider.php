<?php
/**
 *
 * 模版引擎是基于Smarty的，它太强大了，而我们只需要少部分的功能，所以就把它精简下来，主要用于后台，
 * 如果前台不使用angularJS进行开发，那么它会起到很大作用的。
 *
 * 程序初始化的时候调用register()方法，会把App & Admin两端的模板配置文件生成，开发时，只需要调用Helper.php:display & assign 方法输出模板，App控制器父类，我已经把方法移植进去了。
 *
 * 标记 {} 大括号作为简单的标记
 *
 * output a variable,the $val type of string|int|object|array....
 * assign($key,$val) 模版引擎这个函数就是全局输出变量、对象
 *
 * output a html file.
 * display($html_path)
 *
 * use function：
 * {$variable} this is a variable output.
 *
 * The logic:
 * {if} 逻辑判断,You can use:{if $site_name eq ''} or {if $site_name neq ''} or {if $site_name == ''} or {if $site_name != ''}
 *      do something...
 * {elseif condition....} // if you need more else.
 *
 * {/if}
 *
 * The array each:
 * from:array
 * item:value
 * key:key
 * {foreach from="$list" item="$data" key="key"}
 *      do something....
 *      {$data.id}
 *      {$data.name}
 *      {if $data.id > 0}
 *          other logic....
 *      {/if}
 * {/foreach}
 *
 * Quote a function,The name is function name,And the a is param name,you can user-defined it.
 * {function name="count" a="$list"}
 * return the $list object numbers.
 *
 * get a Locale by the key.
 * {lang key="INDEX"}
 *
 * {app_conf a="app"} return array
 * {app_conf a="app" p2="appName"} return string
 *
 * more:/api/system/template/template.php:select line 398 Of course you can also increase the instructions you need.
 *
 * example:
 * include file: {include file="header.html"}
 * output variable: {$__APP__}
 * output function: {function name="getTime"}
 * locale configuration: {lang value="getTime"}
 * config configuration: {conf p1="app" p2="appName"}
 * Array foreach : {foreach from="$data" item="item" key="key"}{$key} - {$item}{/foreach}
 * logic : {if $key eq '...'}something code...{else}something code...{/if}
 *
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/25 0016
 * Time: 13:24
 */

namespace App\System\Template;

use App\System\Basic\Provider;
use App\System\Http\RouteProvider;
use App\System\Utils\TextUtils;

class TemplateProvider extends Provider {

	protected $template_dir = '';
    protected $cache_dir = '';
    protected $compile_dir = '';
    protected $cache_lifetime = 3600; // 缓存更新时间, 默认 3600 秒
    protected $direct_output = false;
    protected $cache_on = true;
    protected $caching = false;
    protected $template = array ();
    protected $force_compile = false;
    protected $_var = array ();
    protected $_hash = '554fcae493e564ee0dc75bdf2ebf94ca';
    protected $_foreach = array ();
    protected $_current_file = '';
    protected $_expires = 0;
    protected $_errorlevel = 0;
    protected $_nowtime = null;
    protected $_checkfile = true;
    protected $_foreachmark = '';
    protected $_seterror = 0;

    protected $_temp_key = array (); // 临时存放 foreach 里 key 的数组
    protected $_temp_val = array (); // 临时存放 foreach 里 item 的数组
    protected $_tmp_ext = ".php";

    public function __construct(){
        parent::__construct();
    }

    /**
     * The first run the middleware.
     */
    public function middleware()
    {
        // TODO: Implement middleware() method.
        $this->_errorlevel = error_reporting ();
        $this->_nowtime = time ();

    }

    /**
     * instance register.
     */
    public function register()
    {
        // TODO: Implement register() method.
        header ( 'Content-type: text/html; charset=utf-8' );


        /*
           configurable in ConstantConfig.php

           'cacheAdminTpl' => 'public/cache/admin/tpl',
           'cacheAdminTplCache' => 'public/cache/admin/tpl/cache',
           'cacheAdminTplCompiled' => 'public/cache/admin/tpl/compiled',
           'cacheAppTpl' => 'public/cache/app/tpl',
           'cacheAppTplCache' => 'public/cache/app/tpl/cache',
           'cacheAppTplCompiled' => 'public/cache/app/tpl/compiled',
       */
        $route = app(RouteProvider::class);
        $this->template_dir = ROOT_PATH."views/".TextUtils::lower($route->getApp())."/default/web";

        if (TextUtils::lower($route->getApp()) == "admin"){
            $this->cache_dir =  ROOT_PATH . conf('storage','cacheAdminTplCache');
            $this->compile_dir =  ROOT_PATH . conf('storage','cacheAdminTplCompiled');
        }else{
            $this->cache_dir =  ROOT_PATH . conf('storage','cacheAppTplCache');
            $this->compile_dir =  ROOT_PATH . conf('storage','cacheAppTplCompiled');
        }


        $this->assign('Controller',$route->getController());
        $this->assign('Action',$route->getAction());
        $this->assign('TMPL',SITE_DOMAIN."/views/".TextUtils::lower($route->getApp())."/default/web");
        $this->assign('__APP__',_PHP_FILE_);


    }

    /**
     * assign the vars to the template.
     * @param string $var
     * @param $value
     */
    public function assign(string $var,$value){
        if (is_array ($var)) {
            foreach ( $var as $key => $val ) {
                if (!empty($key)) {
                    $this->_var [$key] = $val;
                }
            }
        } elseif (!empty($var)) {
            $this->_var [$var] = $value;
        }
    }

    /**
     * @param string $filename
     * @param string $cache_id
     * @return mixed|string 返回html数据
     */
    public function fetch(string $filename,string $cache_id = ""){
        if (!$this->_seterror) {
            error_reporting ( E_ALL ^ E_NOTICE );
        }
        $this->_seterror++;

        if (strncmp ($filename, 'str:', 4 ) == 0) {
            $out = $this->_eval ( $this->fetch_str ( substr ( $filename, 4 ) ) );
            $out = $this->es_tmpl ( $out );
        } else {
            if ($this->_checkfile) {
                if (! file_exists ( $filename )) {
                    $filename = $this->template_dir . '/' . $filename;
                }
            } else {
                $filename = $this->template_dir . '/' . $filename;
            }

            if ($this->direct_output) {
                $this->_current_file = $filename;
                $out = $this->_eval ( $this->fetch_str ( file_get_contents ( $filename ) ) );
                $out = $this->es_tmpl ($out);
            } else {
                if ($this->cache_on && $cache_id && $this->caching) {
                    $out = $this->template_out;
                } else {
                    if (! in_array ( $filename, $this->template )) {
                        $this->template [] = $filename;
                    }

                    $out = $this->make_compiled ( $filename ,$cache_id);
                    $out = $this->es_tmpl ( $out );
                    if ($this->cache_on && $cache_id) {
                        $cachename = basename ( $filename, strrchr ( $filename, '.' ) ) . '_' . $cache_id;

                        $data = serialize ( array (
                            'template' => $this->template,
                            'expires' => $this->_nowtime + $this->cache_lifetime,
                            'maketime' => $this->_nowtime
                        ) );
                        $out = str_replace ( "\r", '', $out );

                        while ( strpos ( $out, "\n\n" ) !== false ) {
                            $out = str_replace ( "\n\n", "\n", $out );
                        }

                        $hash_dir = $this->cache_dir . '/c' . substr ( md5 ( $cachename ), 0, 1 );

                        if (! is_dir ( $hash_dir )) {
                            mkdir ( $hash_dir );
                            @chmod ( $hash_dir, 0777 );
                        }
                        if (file_put_contents ( $hash_dir . '/' . $cachename . md5($cache_id) . $this->_tmp_ext, '<?php exit;?>' . $data . $out, LOCK_EX ) === false) {
                            trigger_error ( 'can\'t write:' . $hash_dir . '/' . $cachename . md5($cache_id) . $this->_tmp_ext );
                        }
                        $this->template = array ();
                    }
                }
            }
        }

        $this->_seterror --;
        if (! $this->_seterror) {
            error_reporting ( $this->_errorlevel );
        }


        return $out;
    }

    /**
     * 显示页面函数
     *
     * @access public
     * @param string $filename
     * @param string $cache_id
     *
     */
    public function display(string $filename, string $cache_id = '') {
        $this->_seterror ++;
        error_reporting ( E_ALL ^ E_NOTICE );
        $this->_checkfile = false;
        $out = $this->fetch ( $filename, $cache_id );

        error_reporting ( $this->_errorlevel );
        $this->_seterror --;
        echo $out;
    }

    /**
     * 修复url的路径
     * @param string $out
     * @return mixed|string
     */
	protected function es_tmpl(string $out):string {
		$out = str_replace ( "./", SITE_DOMAIN . "/", $out );
		return $out;
	}


    /**
     * 编译模板函数
     * @param string $filename
     * @param string $cache_id
     * @return string 返回编译后文件地址
     */
	protected function make_compiled(string $filename,string $cache_id):string {

		$name = $this->compile_dir . '/' . basename ( $filename ) . md5($cache_id) . $this->_tmp_ext;

		if ($this->_expires) {
			$expires = $this->_expires - $this->cache_lifetime;
		} else {
			$fileStat = @stat ( $name );
			$expires = $fileStat ['mtime'];
		}

        $fileStat = @stat ( $filename );

        if ($fileStat === false){
            throw new \Exception(sprintf("The file '%s'is invalid.",$filename));
        }

        $source = "";
		if ($fileStat ['mtime'] <= $expires && ! $this->force_compile) {
			if (file_exists ( $name )) {
				$source = $this->_require ( $name );
				if ($source == '') {
					$expires = 0;
				}
			} else {
				$source = '';
				$expires = 0;
			}
		}

		if ($this->force_compile || $fileStat ['mtime'] > $expires) {
			$this->_current_file = $filename;
			$source = $this->fetch_str ( @file_get_contents ( $filename ) );
			@file_put_contents ( $name, $source, LOCK_EX );
			$source = $this->_eval ( $source );
		}

		return $source;
	}

    /**
     * 处理字符串函数
     *
     * @access public
     * @param string $source
     * @return string
     */
	protected function fetch_str(string $source):string {
        return preg_replace_callback("/{([^\}\{\n]*)}/",function ($matches){
            return $this->select($matches[1]);
        },$source);
    }

    protected function getFileName(string $filename, string $cached_id):string {
        return basename ( $filename, strrchr ( $filename, '.' ) ) . '_' . $cached_id;
    }

    protected function getHashDirectory(string $filename, string $cached_id):string {
        return $this->cache_dir . '/c' . substr ( md5 ( $this->getFileName($filename,$cached_id) ), 0, 1 );
    }

    /**
     * @param $filename
     * @param $cached_id
     */
	public function clear_cache(string $filename, string $cached_id) {

		$cacheName = $this->getFileName($filename,$cached_id);
		$hash_dir = $this->getHashDirectory($filename,$cached_id);

		@unlink ( $hash_dir . '/' . $cacheName . md5($cached_id) . $this->_tmp_ext );
	}

    /**
     * 判断是否缓存
     * @param $filename
     * @param string $cache_id
     * @return bool
     */
	protected function is_cached($filename, $cache_id = ''):bool {
        $cacheName = $this->getFileName($filename,$cache_id);

		if ($this->caching == true && $this->direct_output == false) {
			$hash_dir = $this->getHashDirectory($filename,$cache_id);

			if ($data = @file_get_contents ( $hash_dir . '/' . $cacheName . md5($cache_id) . $this->_tmp_ext )) {
				$data = substr ( $data, 13 );
				$pos = strpos ( $data, '<' );
				$paradata = substr ( $data, 0, $pos );
				$para = @unserialize ( $paradata );
				if ($para === false || $this->_nowtime > $para ['expires']) {
					$this->caching = false;
					return false;
				}

				$this->_expires = $para ['expires'];

				$this->template_out = substr ( $data, $pos );
				foreach ( $para ['template'] as $val ) {
					$stat = @stat ( $val );
					if ($para ['maketime'] < $stat ['mtime']) {
						$this->caching = false;

						return false;
					}
				}
			} else {
				$this->caching = false;

				return false;
			}

			return true;
		} else {
			return false;
		}
	}

    /**
     * 处理{}标签
     * @param $tag
     * @return mixed|string
     */
	protected function select($tag) {
		$tag = stripslashes (trim($tag));

		if (empty ( $tag )) {
			return '{}';

		} elseif ($tag {0} == '*' && substr ( $tag, - 1 ) == '*') {
		    // 注释部分
			return '';

		} elseif ($tag {0} == '$') {
            // 变量
			return '<?php echo ' . $this->get_val ( substr ( $tag, 1 ) ) . '; ?>';

		} elseif ($tag {0} == '/'){
		    //结束 tag
			switch (substr ( $tag, 1 )) {
				case 'if' :
					return '<?php endif; ?>';
					break;

				case 'foreach' :
					if ($this->_foreachmark == 'foreachelse') {
						$output = '<?php endif; unset($_from); ?>';
					} else {
						array_pop ( $this->_patchstack );
						$output = '<?php endforeach; endif; unset($_from); ?>';
					}
					$output .= "<?php \$this->pop_vars();; ?>";

					return $output;
					break;
				default :
					return '{' . $tag . '}';
					break;
			}
		} else {

		    $_arr = explode ( ' ', $tag );
			$tag_sel = array_shift ($_arr);
			switch ($tag_sel) {
				case 'if' :
					return $this->_compile_if_tag ( substr ( $tag, 3 ) );
					break;

				case 'else' :
					return '<?php else: ?>';
					break;

				case 'elseif' :
					return $this->_compile_if_tag ( substr ( $tag, 7 ), true );
					break;

				case 'foreachelse' :
					$this->_foreachmark = 'foreachelse';
					return '<?php endforeach; else: ?>';
					break;

				case 'foreach' :
					$this->_foreachmark = 'foreach';
					if (! isset ( $this->_patchstack )) {
						$this->_patchstack = array ();
					}
					return $this->_compile_foreach_start ( substr ( $tag, 8 ) );
					break;

				case 'assign' :
					$t = $this->get_para ( substr ( $tag, 7 ), 0 );

					if ($t ['value'] {0} == '$') {
						/* 如果传进来的值是变量，就不用用引号 */
						$tmp = '$this->assign(\'' . $t ['var'] . '\',' . $t ['value'] . ');';
					} else {
						$tmp = '$this->assign(\'' . $t ['var'] . '\',\'' . addcslashes ( $t ['value'], "'" ) . '\');';
					}
					// $tmp = $this->assign($t['var'], $t['value']);

					return '<?php ' . $tmp . ' ?>';
					break;

				case 'include' :
					$t = $this->get_para ( substr ( $tag, 8 ), 0 );

					if (substr ( $t ['file'], - 4, 4 ) != 'html') {
						$code = var_export ( $this->_var [$t ['inc_var']], 1 );
						if ($t ['inc_var']) {
							return '<?php $this->assign(\'inc_var\',' . $code . ');echo $this->fetch(' . "$t[file]" . '); ?>';
						} else {
							return '<?php echo $this->fetch(' . "$t[file]" . '); ?>';
						}
					} else {
						$code = var_export ( $this->_var [$t ['inc_var']], 1 );
						if ($t ['inc_var'])
							return '<?php $this->assign(\'inc_var\',' . $code . ');echo $this->fetch(' . "'$t[file]'" . '); ?>';
						else
							return '<?php echo $this->fetch(' . "'$t[file]'" . '); ?>';
					}

					break;

				case 'insert' :
					$t = $this->get_para ( substr ( $tag, 7 ), false );
					$out = "<?php \n" . '$k = ' . preg_replace_callback("/(\'\\$[^,]+)/",function ($matches){
                            return stripslashes(trim($matches[1],'\''));
                        },var_export ( $t, true )) . ";\n";
					$out .= 'echo $this->_hash . $k[\'name\'] . \'|\' . base64_encode(serialize($k)) . $this->_hash;' . "\n?>";

					return $out;
					break;

				case 'function' :
					$t = $this->get_para ( substr ( $tag, 8 ), false );
					$out = "<?php \n" . '$k = ' . preg_replace_callback("/(\'\\$[^,]+)/",function ($matches){
                            return stripslashes(trim($matches[1],'\''));
                        },var_export ( $t, true )) . ";\n";
					$out .= 'echo $k[\'name\'](';
					$first = true;
					foreach ( $t as $n => $v ) {
						if ($n != "name") {
							if ($first) {
								$out .= '$k[\'' . $n . '\']';
								$first = false;
							} else {
								$out .= ',$k[\'' . $n . '\']';
							}
						}
					}
					$out .= ');' . "\n?>";

					return $out;
					break;
                case 'lang' :
                    $reg_text = "/\"([^\"]+)\"/";
                    preg_match_all($reg_text,$tag,$matches);

                    if(count($matches[0])>0)
                    {
                        $param = "";
                        if(isset($matches[0][1])&&$matches[0][1]!='')
                        {
                            //有额外参数
                            for($kk=1;$kk<count($matches[0]);$kk++)
                            {
                                preg_match_all("/[$]([^\"]+)/",$matches[0][$kk],$param_matches);
                                if(count($param_matches[0])>0)
                                {
                                    //有参数
                                    $p_item_arr = explode(".",$param_matches[1][0]);
                                    $var_str = ',$this->_var';
                                    foreach($p_item_arr as $var_item)
                                    {
                                                    $var_str = $var_str."['".$var_item."']";
                                    }
                                    $param.=$var_str;
                                }
                                else
                                    $param.= ",".$matches[0][$kk];
                            }
                        }

                        //{lang v="$module"}{lang v="module"}
                        $lang_key = $matches[1][0];


                        $code =  "<?php\r\n";
                        if (substr($lang_key, 0,1) == '$'){
                            $lang_key = '$this->_var['.substr($lang_key, 1).']';
                            $code.= "echo lang(".$lang_key.$param."); \r\n";

                        }else {
                            $code.= "echo lang(\"";
                            $code.= $lang_key;
                            $code.="\"".$param."); \r\n";
                        }
                        $code.="?>";

                        return $code;
                    }
                    break;

                case 'conf' :
                    $reg_text = "/\"([^\"]+)\"/";
                    preg_match_all($reg_text,$tag,$matches);

                    if(count($matches[0])>0)
                    {
                        $param = "";
                        if(isset($matches[0][1])&&$matches[0][1]!='')
                        {
                            //有额外参数
                            for($kk=1;$kk<count($matches[0]);$kk++)
                            {
                                preg_match_all("/[$]([^\"]+)/",$matches[0][$kk],$param_matches);
                                if(count($param_matches[0])>0)
                                {
                                    //有参数
                                    $p_item_arr = explode(".",$param_matches[1][0]);
                                    $var_str = ',$this->_var';
                                    foreach($p_item_arr as $var_item)
                                    {
                                        $var_str = $var_str."['".$var_item."']";
                                    }
                                    $param.=$var_str;
                                }
                                else
                                    $param.= ",".$matches[0][$kk];
                            }
                        }

                        //{config v="$module"}{config v="module"}
                        $lang_key = $matches[1][0];

                        $code =  "<?php\r\n";
                        if (substr($lang_key, 0,1) == '$'){
                            $lang_key = '$this->_var['.substr($lang_key, 1).']';
                            $code.= "echo conf(".$lang_key.$param."); \r\n";
                        }else {
                            $code.= "echo conf(\"";
                            $code.= $lang_key;
                            $code.="\"".$param."); \r\n";
                        }
                        $code.="?>";

                        return $code;
                    }
                    break;
				default :
					return '{' . $tag . '}';
					break;
			}
		}
        return '';
	}

	/**
	 * 处理smarty标签中的变量标签
	 * @access public
	 * @param string $val
	 * @return bool
	 */
	protected function get_val($val) {
		if (strrpos ( $val, '[' ) !== false) {
			$val = preg_replace_callback("/\[([^\[\]]*)\]/",function ($matters){
                return str_replace('$','\$',$matters[1]);
            },$val);
		}

		if (strrpos ( $val, '|' ) !== false) {
			$moddb = explode ( '|', $val );
			$val = array_shift ( $moddb );
		}

		if (empty ( $val )) {
			return '';
		}

		if (strpos ( $val, '.$' ) !== false) {
			$all = explode ( '.$', $val );

			foreach ( $all as $key => $val ) {
				$all [$key] = $key == 0 ? $this->make_var ( $val ) : '[' . $this->make_var ( $val ) . ']';
			}
			$p = implode ( '', $all );
		} else {
			$p = $this->make_var ( $val );
		}

		if (! empty ( $moddb )) {
			foreach ( $moddb as $key => $mod ) {
				$s = explode ( ':', $mod );
				switch ($s [0]) {
					case 'escape' :
						$s [1] = trim ( $s [1], '"' );
						if ($s [1] == 'html') {
							$p = 'htmlspecialchars(' . $p . ')';
						} elseif ($s [1] == 'url') {
							$p = 'urlencode(' . $p . ')';
						} elseif ($s [1] == 'decode_url') {
							$p = 'urldecode(' . $p . ')';
						} elseif ($s [1] == 'quotes') {
							$p = 'addslashes(' . $p . ')';
						} elseif ($s [1] == 'u8_url') {
                            $p = 'urlencode(' . $p . ')';
						} else {
							$p = 'htmlspecialchars(' . $p . ')';
						}
						break;

					case 'default' :
						$s [1] = $s [1] {0} == '$' ? $this->get_val ( substr ( $s [1], 1 ) ) : "'$s[1]'";
						$p = 'empty(' . $p . ') ? ' . $s [1] . ' : ' . $p;
						break;

					default :
						break;
				}
			}
		}

		return $p;
	}

	/**
	 * 处理去掉$的字符串
	 *
	 * @access public
	 * @param string $val
	 *
	 * @return bool
	 */
	protected function make_var($val) {
		if (strrpos ( $val, '.' ) === false) {
			if (isset ( $this->_var [$val] ) && isset ( $this->_patchstack [$val] )) {
				$val = $this->_patchstack [$val];
			}
			$p = '$this->_var[\'' . $val . '\']';
		} else {
			$t = explode ( '.', $val );
			$_var_name = array_shift ( $t );
			if (isset ( $this->_var [$_var_name] ) && isset ( $this->_patchstack [$_var_name] )) {
				$_var_name = $this->_patchstack [$_var_name];
			}
			if ($_var_name == 'smarty') {
				$p = $this->_compile_smarty_ref ( $t );
			} else {
				$p = '$this->_var[\'' . $_var_name . '\']';
			}
			foreach ( $t as $val ) {
				$p .= '[\'' . $val . '\']';
			}
		}

		return $p;
	}

	/**
	 * 处理insert外部函数/需要include运行的函数的调用数据
	 *
	 * @access public
	 * @param string $val
	 * @param int $type
	 *
	 * @return array
	 */
	protected function get_para($val, $type = 1)
    {
		$pa = $this->str_trim ( $val );
        $para = [];
		foreach ( $pa as $value ) {
			if (strrpos ( $value, '=' )) {
				list ( $a, $b ) = explode ( '=', str_replace ( array (
						' ',
						'"',
						"'",
						'&quot;'
				), '', $value ) );

				if ($b {0} == '$') {
					if ($type) {
						eval ( '$para[\'' . $a . '\']=' . $this->get_val ( substr ( $b, 1 ) ) . ';' );
					} else {
						$para [$a] = $this->get_val ( substr ( $b, 1 ) );
					}
				} else {
					$para [$a] = $b;
				}
			}
		}
		return $para;
	}

    /**
     * 判断变量是否被注册并返回值
     * @param null $name
     * @return array|mixed|null
     */
	protected function &get_template_vars($name = null) {
		if (empty ( $name )) {
			return $this->_var;
		} elseif (! empty ( $this->_var [$name] )) {
			return $this->_var [$name];
		} else {
			$_tmp = null;
			return $_tmp;
		}
	}

	/**
	 * 处理if标签
	 *
	 * @access public
	 * @param string $tag_args
	 * @param bool $elseif
	 *
	 * @return string
	 */
	protected function _compile_if_tag($tag_args, $elseif = false) {
		preg_match_all ( '/\-?\d+[\.\d]+|\'[^\'|\s]*\'|"[^"|\s]*"|[\$\w\.]+|!==|===|==|!=|<>|<<|>>|<=|>=|&&|\|\||\(|\)|,|\!|\^|=|&|<|>|~|\||\%|\+|\-|\/|\*|\@|\S/', $tag_args, $match );

		$tokens = $match [0];
		// make sure we have balanced parenthesis
		$token_count = array_count_values ( $tokens );
		if (! empty ( $token_count ['('] ) && $token_count ['('] != $token_count [')']) {
			// $this->_syntax_error('unbalanced parenthesis in if statement', E_USER_ERROR, __FILE__, __LINE__);
		}

		for($i = 0, $count = count ( $tokens ); $i < $count; $i ++) {
			$token = &$tokens [$i];
			switch (strtolower ( $token )) {
				case 'eq' :
					$token = '==';
					break;

				case 'ne' :
				case 'neq' :
					$token = '!=';
					break;

				case 'lt' :
					$token = '<';
					break;

				case 'le' :
				case 'lte' :
					$token = '<=';
					break;

				case 'gt' :
					$token = '>';
					break;

				case 'ge' :
				case 'gte' :
					$token = '>=';
					break;

				case 'and' :
					$token = '&&';
					break;

				case 'or' :
					$token = '||';
					break;

				case 'not' :
					$token = '!';
					break;

				case 'mod' :
					$token = '%';
					break;

				default :
					if ($token [0] == '$') {
						$token = $this->get_val ( substr ( $token, 1 ) );
					}
					break;
			}
		}

		if ($elseif) {
			return '<?php elseif (' . implode ( ' ', $tokens ) . '): ?>';
		} else {
			return '<?php if (' . implode ( ' ', $tokens ) . '): ?>';
		}
	}

	/**
	 * 处理foreach标签
	 *
	 * @access public
	 * @param string $tag_args
	 *
	 * @return string
	 */
	protected function _compile_foreach_start($tag_args) {
		$attrs = $this->get_para ( $tag_args, 0 );

		$from = $attrs ['from'];
		if (isset ( $this->_var [$attrs ['item']] ) && ! isset ( $this->_patchstack [$attrs ['item']] )) {
			$this->_patchstack [$attrs ['item']] = $attrs ['item'] . '_' . str_replace ( array (
					' ',
					'.'
			), '_', microtime () );
			$attrs ['item'] = $this->_patchstack [$attrs ['item']];
		} else {
			$this->_patchstack [$attrs ['item']] = $attrs ['item'];
		}
		$item = $this->get_val ( $attrs ['item'] );

		if (! empty ( $attrs ['key'] )) {
			$key = $attrs ['key'];
			$key_part = $this->get_val ( $key ) . ' => ';
		} else {
			$key = null;
			$key_part = '';
		}

		if (! empty ( $attrs ['name'] )) {
			$name = $attrs ['name'];
		} else {
			$name = null;
		}

		$output = '<?php ';
		$output .= "\$_from = $from; if (!is_array(\$_from) && !is_object(\$_from)) { settype(\$_from, 'array'); }; \$this->push_vars('$attrs[key]', '$attrs[item]');";

		if (! empty ( $name )) {
			$foreach_props = "\$this->_foreach['$name']";
			$output .= "{$foreach_props} = array('total' => count(\$_from), 'iteration' => 0);\n";
			$output .= "if ({$foreach_props}['total'] > 0):\n";
			$output .= "    foreach (\$_from AS $key_part$item):\n";
			$output .= "        {$foreach_props}['iteration']++;\n";
		} else {
			$output .= "if (count(\$_from)):\n";
			$output .= "    foreach (\$_from AS $key_part$item):\n";
		}
		return $output . '?>';
	}

	/**
	 * 将 foreach 的 key, item 放入临时数组
	 *
	 * @param mixed $key
	 * @param mixed $val
	 *
	 * @return void
	 */
	protected function push_vars($key, $val) {
		if (! empty ( $key )) {
			array_push ( $this->_temp_key, "\$this->_vars['$key']='" . $this->_vars [$key] . "';" );
		}
		if (! empty ( $val )) {
			array_push ( $this->_temp_val, "\$this->_vars['$val']='" . $this->_vars [$val] . "';" );
		}
	}

	/**
	 * 弹出临时数组的最后一个
	 *
	 * @return void
	 */
	protected function pop_vars() {
		$key = array_pop ( $this->_temp_key );
		//$val = array_pop ( $this->_temp_val );

		if (! empty ( $key )) {
			eval ( $key );
		}
	}

	/**
	 * 处理smarty开头的预定义变量
	 *
	 * @access public
	 * @param array $indexes
	 *
	 * @return string
	 */
	protected function _compile_smarty_ref(&$indexes) {
		/* Extract the reference name. */
		$_ref = $indexes [0];
		switch ($_ref) {
			case 'now' :
				$compiled_ref = 'time()';
				break;

			case 'foreach' :
				array_shift ( $indexes );
				$_var = $indexes [0];
				$_propname = $indexes [1];
				switch ($_propname) {
					case 'index' :
						array_shift ( $indexes );
						$compiled_ref = "(\$this->_foreach['$_var']['iteration'] - 1)";
						break;

					case 'first' :
						array_shift ( $indexes );
						$compiled_ref = "(\$this->_foreach['$_var']['iteration'] <= 1)";
						break;

					case 'last' :
						array_shift ( $indexes );
						$compiled_ref = "(\$this->_foreach['$_var']['iteration'] == \$this->_foreach['$_var']['total'])";
						break;

					case 'show' :
						array_shift ( $indexes );
						$compiled_ref = "(\$this->_foreach['$_var']['total'] > 0)";
						break;

					default :
						$compiled_ref = "\$this->_foreach['$_var']";
						break;
				}
				break;

			case 'get' :
				$compiled_ref = '$_GET';
				break;

			case 'post' :
				$compiled_ref = '$_POST';
				break;

			case 'cookies' :
				$compiled_ref = '$_COOKIE';
				break;

			case 'env' :
				$compiled_ref = '$_ENV';
				break;

			case 'server' :
				$compiled_ref = '$_SERVER';
				break;

			case 'request' :
				$compiled_ref = '$_REQUEST';
				break;

			case 'session' :
				$compiled_ref = '$_SESSION';
				break;

			default :
                $compiled_ref = "";
				// $this->_syntax_error('$smarty.' . $_ref . ' is an unknown reference', E_USER_ERROR, __FILE__, __LINE__);
				break;
		}
		array_shift ( $indexes );

		return $compiled_ref;
	}

    /**
     * 处理a=b or c = d or k = f类字符串
     * @param $str
     * @return array
     */
	protected function str_trim(string $str):array {
		while ( strpos ( $str, '= ' ) != 0 ) {
			$str = str_replace ( '= ', '=', $str );
		}
		while ( strpos ( $str, ' =' ) != 0 ) {
			$str = str_replace ( ' =', '=', $str );
		}
		return explode ( ' ', trim ( $str ) );
	}

    /**
     * @param $content
     * @return string
     */
	protected function _eval(string $content):string {
		ob_start ();
		eval ( '?' . '>' . trim ( $content ) );
		$content = ob_get_contents ();
		ob_end_clean ();
		return $content;
	}

    /**
     * @param $filename
     * @return string
     */
	protected function _require(string $filename):string {
		ob_start ();
		include $filename;
		$content = ob_get_contents ();
		ob_end_clean ();
		return $content;
	}

}

?>