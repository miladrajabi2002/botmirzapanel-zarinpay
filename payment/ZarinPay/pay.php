
<?php
$rootPath = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING);
$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_STRING);
ini_set('error_log', 'error_log');
$Pathfile = dirname(dirname($PHP_SELF, 2));
$Pathfiles = $rootPath.$Pathfile;
require_once $Pathfiles.'/config.php';
require_once $Pathfiles.'/functions.php';
require_once $Pathfiles.'/text.php';
$user_id =    htmlspecialchars($_GET['user_id'], ENT_QUOTES, 'UTF-8');
$amount =     htmlspecialchars($_GET['price'], ENT_QUOTES, 'UTF-8');
$invoice_id = htmlspecialchars($_GET['order_id'], ENT_QUOTES, 'UTF-8');
$checkprice = select("Payment_report", "price", "id_order", $invoice_id,"select")['price'];
// Send Parameter
if($checkprice !=$amount){
    echo $textbotlang['users']['moeny']['invalidprice'];
    return;
}
$data = [
     "amount" => $amount,
   "order_id" => $invoice_id,
   "customer_user_id" => $user_id, 
   "description" => "خرید افزونه وردپرس"
];

$jsonData = json_encode($data);

$ch = curl_init('https://zarinpay.me/api/create-payment');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
   'Content-Type: application/json',
   'Authorization: Bearer ' . $accessToken
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
   echo "خطا در اتصال: " . curl_error($ch);
   curl_close($ch);
   exit;
}

curl_close($ch);

$result = json_decode($response, true);
if (isset($result['success']) && $result['success'] === true) {
   session_start();
   $_SESSION['authority'] = $result['authority'];
   $_SESSION['order_id'] = $order_id;
   header('Location: ' . 'https://www.zarinpal.com/pg/StartPay/' . $result['authority']);
   exit;
} else {
   echo "خطا در ایجاد درگاه پرداخت:\n";
   print_r($result);
}
