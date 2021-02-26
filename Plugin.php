<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * OSSManger
 *
 * @package OSSManger
 * @author 鸿
 * @version 1.0.0
 * @link https://b.saytf.cn/cross.html
 */
class OSSManger_Plugin implements Typecho_Plugin_Interface
{
	/**
	 * 激活插件方法,如果激活失败,直接抛出异常
	 *
	 * @access public
	 * @return void
	 * @throws Typecho_Plugin_Exception
	 */
	public static function activate()
	{
		$F = DIRECTORY_SEPARATOR;
		$G = realpath(__DIR__ . "${F}..${F}..${F}..${F}") . "${F}";
		$R = __DIR__ . "${F}static${F}";
		//替换file-upload.php
		if (!file_exists("${G}admin${F}file-upload.php.old.OSSManger")) {
			rename("${G}admin${F}file-upload.php", "${G}admin${F}file-upload.php.old.OSSManger");
			copy("${R}file-upload.php", "${G}admin${F}file-upload.php");
		}
		Typecho_Plugin::factory('admin/menu.php')->navBar = array('OSSManger_Plugin', 'render');
		return _t("插件启用成功，请前往设置填写OSS信息");
	}

	/**
	 * 禁用插件方法,如果禁用失败,直接抛出异常
	 *
	 * @static
	 * @access public
	 * @return void
	 * @throws Typecho_Plugin_Exception
	 */
	public static function deactivate()
	{
		$F = DIRECTORY_SEPARATOR;
		$G = realpath(__DIR__ . "${F}..${F}..${F}..${F}") . "${F}";
		//还原file-upload.php
		unlink("${G}admin${F}file-upload.php");
		rename("${G}admin${F}file-upload.php.old.OSSManger", "${G}admin${F}file-upload.php");
	}
	/**
	 * 获取插件配置面板
	 *
	 * @access public
	 * @param Typecho_Widget_Helper_Form $form 配置面板
	 * @return void
	 */
	public static function config(Typecho_Widget_Helper_Form $form)
	{
		$adress = new Typecho_Widget_Helper_Form_Element_Text('adress', null, null, _t('adress:'), _t("该OSS在线文件管理的url，形如https://oss.console.aliyun.com/bucket/oss-cn-beijing/lsky-sp/object"));
		$form->addInput($adress->addRule('required', _t('不能为空！')));

		$accessKey = new Typecho_Widget_Helper_Form_Element_Text('accessKey', null, null, _t('accessKey:'), _t("IDd+空格+Secret"));
		$form->addInput($accessKey->addRule('required', _t('不能为空！')));

		$selfDomain = new Typecho_Widget_Helper_Form_Element_Text('selfDomain', null, null, _t('selfDomain:'));
		$form->addInput($selfDomain);
	}

	/**
	 * 个人用户的配置面板
	 *
	 * @access public
	 * @param Typecho_Widget_Helper_Form $form
	 * @return void
	 */
	public static function personalConfig(Typecho_Widget_Helper_Form $form)
	{
	}

	/**
	 * 插件实现方法
	 *
	 * @access public
	 * @return void
	 */
	public static function render()
	{
		$opt = Typecho_Widget::widget('Widget_Options')->plugin('OSSManger');
		@preg_match_all('#https://oss.console.aliyun.com/bucket/(.+?)/(.+?)/object#', $opt->adress, $matches);
		@$region = $matches[1][0];
		@$bucket = $matches[2][0];
		@preg_match_all('#(.+?) (.+)#', $opt->accessKey, $matches);
		@$accessKeyId = $matches[1][0];
		@$accessKeySecret = $matches[2][0];
		echo
		<<<eof
      <script type="text/javascript">
        const OSSConfig={
          region: '$region',
          accessKeyId: '$accessKeyId',
          accessKeySecret: '$accessKeySecret',
          bucket: '$bucket',
          selfDomain: '$opt->selfDomain'
        }
      </script>
eof;
	}
}
