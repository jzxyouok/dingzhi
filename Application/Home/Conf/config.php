<?php
return array(
    'URL_MODEL'=>'0', //0是?m=Home&c=User&a=login，1是/Home/Order/notify（因此1的时候尽量用post，用户能在前端修改参数的情况。但服务器是nginx的时候要用0）
    'TMPL_FILE_DEPR'=>'_', //模板文件地址规则
    'URL_HTML_SUFFIX'=>'', //伪静态后缀
    'LAYOUT_ON'=>true, //是否启用布局
);