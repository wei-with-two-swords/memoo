<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

function themeConfig($form)
{
    $logoUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'logoUrl',
        null,
        null,
        _t('站点 LOGO 地址'),
        _t('在这里填入一个图片 URL 地址, 以在网站标题前加上一个 LOGO')
    );
    $icp = new \Typecho\Widget\Helper\Form\Element\Text(
        'ICP',
        null,
        null,
        _t('站点ICP备案号'),
        _t('请输入站点的ICP备案编号，该编号将显示在您站点的底部，示例：京ICP备20210282xxx号')
    );
    $sitemapUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'sitemapUrl',
        null,
        null,
        _t('站点sitemap.xml地址'),
        _t('请输入站点的sitemap.xml地址，该地址将显示在页面超链接中，示例：https://www.xx.com/index.php/sitemap.xml')
    );

    $form->addInput($logoUrl);
    $form->addInput($icp);
    $form->addInput($sitemapUrl);

    $sidebarBlock = new \Typecho\Widget\Helper\Form\Element\Checkbox(
        'sidebarBlock',
        [
            'ShowRecentPosts'    => _t('显示最新文章'),
            'ShowRecentComments' => _t('显示最近回复'),
            'ShowCategory'       => _t('显示分类'),
            'ShowArchive'        => _t('显示归档'),
            'ShowOther'          => _t('显示其它杂项')
        ],
        ['ShowRecentPosts', 'ShowRecentComments', 'ShowCategory', 'ShowArchive', 'ShowOther'],
        _t('侧边栏显示')
    );

    $form->addInput($sidebarBlock->multiMode());
}

/*
function themeFields($layout)
{
    $logoUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'logoUrl',
        null,
        null,
        _t('站点LOGO地址'),
        _t('在这里填入一个图片URL地址, 以在网站标题前加上一个LOGO')
    );
    $layout->addItem($logoUrl);
}
*/
