<?php
$content = file_get_contents('c:\Users\aguil\OneDrive\Documentos\firefighters_app\bomberos_3.sql', false, null, 0, 5000);
file_put_contents('c:\Users\aguil\OneDrive\Documentos\CAPAAdmin\sql_head.txt', $content);
echo "Leído head del SQL";
