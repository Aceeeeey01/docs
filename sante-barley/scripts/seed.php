<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../config/database.php';
$db->exec(file_get_contents(__DIR__.'/../database/sante_barley_db.sql'));
foreach (json_decode(file_get_contents(__DIR__.'/../database/catalog.json'),true) as $i=>$p) {
 $q=$db->prepare('INSERT INTO products(category_id, product_name, slug, description, price, stock, image, size, sku) VALUES ((SELECT id FROM categories WHERE name=?),?,?,?,?,?,?,?,?) ON CONFLICT(slug) DO NOTHING');
 $q->execute([$p[1],$p[0],$p[6],$p[4],$p[2],$p[3],'/assets/images/products/'.$p[6].'.svg',$p[5],'SB-'.str_pad((string)($i+1),4,'0',STR_PAD_LEFT)]);
}
echo "Schema and sample catalog ready. Prices and pack sizes are illustrative; verify before launch.\n";
