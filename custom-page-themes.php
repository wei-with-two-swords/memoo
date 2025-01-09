<?php
/**
 * 主题推荐模板
 * 
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<div class="container-fluid"role="main">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 " >
        <?php $this->content(); ?>
    </div>
    <style>
        .theme-cover-img {
            width: 100%; /* 图片宽度为容器的宽度 */
            height: 100%; /* 图片高度为容器的高度 */
            object-fit: cover; /* 图片保持其宽高比，同时填充整个容器，可能会裁剪部分图片 */
            transition: transform 0.5s ease; 
        }
        .theme-cover-img:hover{
            transform: scale(1.05);
        }
        .theme-desc{
            line-height: 1.8;
        }
    </style>
    <script>
        (function(){
            const themes_list =document.getElementById('themes-list');
            if(themes_list){
                const list_items =themes_list.querySelectorAll('li');
                let html_str = ""
                for(let i=0;i<list_items.length-3;i+=4){
                    html_str += `<div class="col py-3 pe-3 ">
                                    <div>
                                        <a href="${list_items[i+2].innerText}" title="点击预览">
                                            <img class="theme-cover-img" loading="lazy" src="${list_items[i].innerText}" alt="${list_items[i+1].innerText}"/>
                                        </a> 
                                        <div class="pt-1 theme-desc"> 
                                            <a href="${list_items[i+3].innerText}">下载地址</a> &nbsp;
                                            ${list_items[i+1].innerText}
                                        </div>
                                    </div> 
                                </div>`;
                }
                
                themes_list.parentNode.innerHTML = html_str;
                themes_list.style="display:none;";
            }
        }())
    </script>
    <?php $this->need('comments.php'); ?>
</div><!-- end #main-->

<?php $this->need('footer.php'); ?>
