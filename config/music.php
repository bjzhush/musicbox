<?php
return [
    'qiniu_accesskey' => env('QINIU_AK',''),
    'qiniu_secretkey' => env('QINIU_SK',''),
    'qiniu_bucket' => env('QINIU_BUCKET',''),
    'qiniu_upload_api' => env('QINIU_UPLOAD_API',''),
    'qiniu_preview_domain' => env('QINIU_PREVIEW_DOMAIN', ''),
];