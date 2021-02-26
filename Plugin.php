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

		Typecho_Plugin::factory('admin/menu.php')->navBar = array('OSSManger_Plugin', 'render');
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
		$F = DIRECTORY_SEPARATOR;
		$G = realpath(__DIR__ . "${F}..${F}..${F}..${F}") . "${F}";
		$R = __DIR__ . "${F}static${F}";
		$success = true;
		if (isset($_GET['action'])) {
			switch ($_GET['action']) {
				case 'replace':
					$success = $success && rename("${G}admin${F}file-upload.php", "${G}admin${F}file-upload.php.old");
					$success = $success && copy("${R}static${F}file-upload.php", "${G}admin${F}file-upload.php");
					break;
				case 'restore':
					$success = $success && unlink("${G}admin${F}file-upload.php");
					$success = $success && rename("${G}static${F}file-upload.php.old", "${G}admin${F}file-upload.php");
					break;
			}
			if ($success) {
				Typecho_Widget::widget('Widget_Notice')->set(_t("操作失败，admin/file-upload.php没有写入权限"), 'error');
			} else {
				Typecho_Widget::widget('Widget_Notice')->set(_t("操作成功！"), 'success');
			}
		}
		//https://oss.console.aliyun.com/bucket/oss-cn-beijing/lsky-sp/object
		$region = new Typecho_Widget_Helper_Form_Element_Text('region', null, null, _t('region:'));
		$form->addInput($region->addRule('required', _t('不能为空！')));

		$accessKeyId = new Typecho_Widget_Helper_Form_Element_Text('accessKeyId', null, null, _t('accessKeyId:'));
		$form->addInput($accessKeyId->addRule('required', _t('不能为空！')));

		$accessKeySecret = new Typecho_Widget_Helper_Form_Element_Text('accessKeySecret', null, null, _t('accessKeySecret:'));
		$form->addInput($accessKeySecret->addRule('required', _t('不能为空！')));

		$bucket = new Typecho_Widget_Helper_Form_Element_Text('bucket', null, null, _t('bucket:'));
		$form->addInput($bucket->addRule('required', _t('不能为空！')));

		$selfDomain = new Typecho_Widget_Helper_Form_Element_Text('selfDomain', null, null, _t('selfDomain:'));
		$form->addInput($selfDomain);

		$replaceBtn = new Typecho_Widget_Helper_Form_Element_Submit();
		$replaceBtn->value(_t('替换file-upload.php'));
		$replaceBtn->description(_t('通常只需要在第一次启用插件的时候，手动点击该按钮。'));
		$replaceBtn->input->setAttribute('class', 'btn btn-s btn-warn btn-operate');
		$replaceBtn->input->setAttribute('formaction', Typecho_Common::url('/options-plugin.php?config=OSSManger&action=replace', Helper::options()->adminUrl));
		$form->addItem($replaceBtn);

		$restoreBtn = new Typecho_Widget_Helper_Form_Element_Submit();
		$restoreBtn->value(_t('还原file-upload.php'));
		$restoreBtn->description(_t('禁用插件之前需手动点击此按钮。'));
		$restoreBtn->input->setAttribute('class', 'btn btn-s btn-warn btn-operate');
		$restoreBtn->input->setAttribute('formaction', Typecho_Common::url('/options-plugin.php?config=OSSManger&action=restore', Helper::options()->adminUrl));
		$form->addItem($restoreBtn);
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
		echo
		<<<eof
        <script type="text/javascript">
            const OSSConfig={
                region: '$opt->region',
                accessKeyId: '$opt->accessKeyId',
                accessKeySecret: '$opt->accessKeySecret',
                bucket: '$opt->bucket',
                selfDomain: '$opt->selfDomain'
            }
        </script>
eof;
	}
}
