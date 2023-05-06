<?php
/*
    pv  => @gholipour3
    channel => @mirzapanel
    */
global $connect, $keyboard, $backuser, $list_marzban_panel_user, $keyboardadmin, $channelkeyboard, $backadmin, $keyboardmarzban, $json_list_marzban_panel, $sendmessageuser, $textbot, $json_list_help, $rollkey, $confrimrolls, $keyboardhelpadmin, $request_contact, $User_Services, $shopkeyboard,$json_list_product_list, $payment, $admin_section_panel, $setting_panel, $valid_Number, $reports, $step_payment, $Confirm_pay;
require_once 'config.php';
require_once 'botapi.php';
require_once 'apipanel.php';
require_once 'jdf.php';
#-----------------------------#
$update = json_decode(file_get_contents("php://input"), true);
$from_id = $update['message']['from']['id'] ?? $update['callback_query']['from']['id'] ?? 0;
$Chat_type = $update["message"]["chat"]["type"] ?? '';
$text = $update["message"]["text"] ?? $update["callback_query"]["message"]["text"] ?? '';
$message_id = $update["message"]["message_id"] ?? $update["callback_query"]["message"]["message_id"] ?? 0;
$message_id = $update["message"]["message_id"] ?? $update["callback_query"]["message"]["message_id"] ?? 0;
$photo = $update["message"]["photo"] ?? 0;
$photoid = $photo ? $photo[count($photo) - 1]["file_id"] : 0;
$caption = $update["message"]["caption"] ?? '';
$video = $update["message"]["video"] ?? 0;
$videoid = $video ? $video["file_id"] : 0;
$forward_from_id = $update["message"]["reply_to_message"]["forward_from"]["id"] ?? 0;
$datain = $update["callback_query"]["data"] ?? '';
$username = $update["message"]["from"]["username"] ?? '';
$user_phone = isset($update["message"]["contact"]["phone_number"]) ? $update["message"]["contact"]["phone_number"] : 0;
$contact_id = isset($update["message"]["contact"]["user_id"]) ? $update["message"]["contact"]["user_id"] : 0;
$first_name = $update['message']['from']['first_name']  ?? '';
$callback_query_id = $update["callback_query"]["id"] ?? 0;
//#-----------------------#
$telegram_ip_ranges = [
    ['lower' => '149.154.160.0', 'upper' => '149.154.175.255'],
    ['lower' => '91.108.4.0',    'upper' => '91.108.7.255']
];
$ip_dec = (float) sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
$ok = false;
foreach ($telegram_ip_ranges as $telegram_ip_range) if (!$ok) {
    $lower_dec = (float) sprintf("%u", ip2long($telegram_ip_range['lower']));
    $upper_dec = (float) sprintf("%u", ip2long($telegram_ip_range['upper']));
    if ($ip_dec >= $lower_dec and $ip_dec <= $upper_dec) $ok = true;
}
if (!$ok) die("false");
#-----------------------#

$user = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '$from_id' LIMIT 1"));
$Processing_value =  $user['Processing_value'];
$Processing_value_one =  $user['Processing_value_one'];
$setting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM setting"));
$helpdata = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM help"));
$datatextbotget = mysqli_query($connect, "SELECT * FROM textbot");
$channels = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM channels  LIMIT 1"));
$id_admin = mysqli_query($connect, "SELECT * FROM admin");
$admin_ids = [];
while ($row = mysqli_fetch_assoc($id_admin)) {
    $admin_ids[] = $row['id_admin'];
}
$id_user = mysqli_query($connect, "SELECT * FROM user");
$users_ids = [];
while ($row = mysqli_fetch_assoc($id_user)) {
    $users_ids[] = $row['id'];
}
$datatxtbot = array();
foreach ($datatextbotget as $row) {
    $datatxtbot[] = array(
        'id_text' => $row['id_text'],
        'text' => $row['text']
    );
    }
$datatextbot = array(
    'text_usertest' => '',
    'text_info' => '',
    'text_support' => '',
    'text_help' => '',
    'text_start' => '',
    'text_bot_off' => '',
    'text_dec_info' => '',
    'text_dec_usertest' => '',
    'text_roll' => '',
    'text_dec_support' => '',
    'text_fq' => '',
    'text_dec_fq' => '',
    'text_account'  => '',
    'text_sell' => '',
    'text_Add_Balance' => ''
);
foreach ($datatxtbot as $item) {
    if (isset($datatextbot[$item['id_text']])) {
        $datatextbot[$item['id_text']] = $item['text'];
    }
}
$connect->query("INSERT IGNORE INTO user (id , step,limit_usertest,User_Status,number,Balance) VALUES ('$from_id', 'none','{$setting['limit_usertest_all']}','Active','none','0')");
#---------channel--------------#
if (isset($channels['link'])) {
$tch = '';
    $response = json_decode(file_get_contents('https://api.telegram.org/bot' . API_KEY . "/getChatMember?chat_id=@{$channels['link']}&user_id=$from_id"));
    $tch = $response->result->status;
}
#-----------------------#
if ($user['User_Status'] == "block") {
    $textblock = "
       🚫 شما از طرف مدیریت بلاک شده اید.
        
    ✍️ دلیل مسدودی : {$user['description_blocking']}
        ";
    sendmessage($from_id, $textblock, null);
    return;
}
if (!in_array($tch, ['member', 'creator', 'administrator']) && $channels['Channel_lock'] == "on" && !in_array($from_id, $admin_ids)) {
    $text_channel = "   
        ⚠️ کاربر گرامی ؛ شما عضو چنل ما نیستید
        ❗️@" . $channels['link'] . "
        عضو کانال بالا شوید و مجدد 
        /start
        کنید ❤️
        ";
    sendmessage($from_id, $text_channel, null);
    return;
}
#-----------------------#
if ($setting['get_number'] == "✅ تایید شماره موبایل روشن است" && $user['step'] != "get_number" && $user['number'] == "none"){
    sendmessage($from_id, "📞 لطفا شماره موبایل خود را  برای احراز هویت ارسال نمایید", $request_contact);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_number';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == 'get_number'){
    if (empty($user_phone)){
        sendmessage($from_id, "❌ شماره تلفن صحبح نیست شماره تلفن صحبح را ارسال نمایید.", $request_contact);
        return;
    }
    if ($contact_id != $from_id){
        sendmessage($from_id, "⚠️ خطا در ذخیره سازی شماره تلفن  . شماره باید حتما  برای همین اکانت باشد.",$request_contact );
        return;
    }
    sendmessage($from_id, "✅ شماره موبایل شما با موفقیت تایید شد.", $keyboard);
    $stmt = $connect->prepare("UPDATE user SET number = ? WHERE id = ?");
    $stmt->bind_param("ss", $user_phone, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = "home";
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($user['number'] == "none" && $setting['get_number'] == "✅ تایید شماره موبایل روشن است" ) return;
#-----------------------#
if ($setting['roll_Status'] == "✅  تایید قانون  روشن است" && $user['roll_Status'] == 0 && $text != "✅ قوانین را می پذیرم" && !in_array($from_id, $admin_ids)) {
            sendmessage($from_id, $datatextbot['text_roll'], $confrimrolls);
            return;
}
if ($text == "✅ قوانین را می پذیرم"){
    sendmessage($from_id, "✅ قوانین تایید شد از الان می توانید از خدمات ربات استفاده نمایید.", $keyboard);
    $stmt = $connect->prepare("UPDATE user SET roll_Status = ? WHERE id = ?");
    $confrim = true;
    $stmt->bind_param("ss", $confrim, $from_id);
    $stmt->execute();
}

#-----------------------#
if ($setting['Bot_Status'] == "❌  ربات خاموش است" && !in_array($from_id, $admin_ids)) {
    sendmessage($from_id, $datatextbot['text_bot_off'], null);
    return;
}
#-----------------------#
if ($text == "/start") {
    sendmessage($from_id, $datatextbot['text_start'], $keyboard);
    return;
}
if ($text == "🏠 بازگشت به منوی اصلی") {
    $textback = "به صفحه اصلی بازگشتید!";
    sendmessage($from_id, $textback, $keyboard);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    return;
}
//________________________________________________________
if ($text == $datatextbot['text_info']) {
    sendmessage($from_id, $datatextbot['text_dec_info'], $backuser);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'getusernameinfo';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($user['step'] == "getusernameinfo") {
    if (!preg_match('~^[a-z][a-z\d_]{2,32}$~i', $text)) {
        $textusernameinva = " 
                    ❌نام کاربری نامعتبر است
                
                🔄 مجددا نام کاربری خود  را ارسال کنید
                    ";
        sendmessage($from_id, $textusernameinva, $backuser);
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'getusernameinfo';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
        return;
    }

    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    sendmessage($from_id, "🌏 موقعیت سرویس خود را انتخاب نمایید.", $list_marzban_panel_user);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'getdata';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}

elseif ($user['step'] == "getdata") {
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$text'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $data_useer = getuser($Processing_value, $Check_token['access_token'], $marzban_list_get['url_panel']);
    if ($data_useer['detail'] == "User not found") {
        sendmessage($from_id, "نام کاربری وجود ندارد", $keyboard);
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'home';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
        return;
    }
    #-------------status----------------#
    $status = $data_useer['status'];
    $status_var = [
        'active' => '✅فعال',
        'limited' => '🚫پایان حجم',
        'disabled' => '❌غیرفعال',
        'expired' => '🔚 پایان زمان سرویس'
    ][$status];
    #--------------expire---------------#
    $expirationDate = $data_useer['expire'] ? jdate('Y/m/d', $data_useer['expire']) : "نامحدود";
    #-------------data_limit----------------#
    $LastTraffic = $data_useer['data_limit'] ? formatBytes($data_useer['data_limit']) : "نامحدود";
    #---------------RemainingVolume--------------#
    $output =  $data_useer['data_limit'] - $data_useer['used_traffic'];
    $RemainingVolume = $data_useer['data_limit'] ? formatBytes($output) : "نامحدود";
    #---------------used_traffic--------------#
    $usedTrafficGb = $data_useer['used_traffic'] ? formatBytes($data_useer['used_traffic']) : "مصرف نشده";
    #--------------day---------------#
    $timeDiff = $data_useer['expire'] - time();
    $day = $data_useer['expire'] ? floor($timeDiff / 86400) . " روز" : "نامحدود";
    #-----------------------------#


    $keyboardinfo = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $data_useer['username'], 'callback_data' => "username"],
                ['text' => 'نام کاربری :', 'callback_data' => 'username'],
            ], [
                ['text' => $status_var, 'callback_data' => 'status_var'],
                ['text' => 'وضعیت :', 'callback_data' => 'status_var'],
            ], [
                ['text' => $expirationDate, 'callback_data' => 'expirationDate'],
                ['text' => 'زمان پایان :', 'callback_data' => 'expirationDate'],
            ], [], [
                ['text' => $day, 'callback_data' => 'روز'],
                ['text' => 'زمان باقی مانده سرویس :', 'callback_data' => 'day'],
            ], [
                ['text' => $LastTraffic, 'callback_data' => 'LastTraffic'],
                ['text' => 'حجم کل سرویس :', 'callback_data' => 'LastTraffic'],
            ], [
                ['text' => $usedTrafficGb, 'callback_data' => 'expirationDate'],
                ['text' => 'حجم مصرف شده سرویس :', 'callback_data' => 'expirationDate'],
            ], [
                ['text' => $RemainingVolume, 'callback_data' => 'RemainingVolume'],
                ['text' => 'حجم باقی مانده  سرویس :', 'callback_data' => 'RemainingVolume'],
            ]
        ]
    ]);
    sendmessage($from_id, "📊  اطلاعات سرویس :", $keyboardinfo);
    sendmessage($from_id, " یک گزینه را انتخاب کنید", $keyboard);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($text == $datatextbot['text_usertest']) {
    if ($user['limit_usertest'] == 0) {
        sendmessage($from_id, "⚠️ محدودیت ساخت اشتراک تست شما به پایان رسید.", $keyboard);
        return;
    }
    sendmessage($from_id, $datatextbot['text_dec_usertest'], $backuser);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'selectloc';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "selectloc") {
    if (!preg_match('~^[a-z\d_]{3,32}$~i', $text)) {
        sendmessage($from_id, "⛔️ نام کاربری معتبر نیست", $backuser);
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'selectloc';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    sendmessage($from_id, "🌏 موقعیت سرویس تست را انتخاب نمایید.", $list_marzban_panel_user);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'crateusertest';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-----------------------------------#
elseif ($user['step'] == "crateusertest") {
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$text'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $Allowedusername = getuser($Processing_value, $Check_token['access_token'], $marzban_list_get['url_panel']);
    if(isset($Allowedusername['username'])){
        $random_number = rand(1000000, 9999999);
        $Processing_value = $Processing_value . $random_number;
    }
        $date = strtotime("+" . time . "hours");
        $timestamp = strtotime(date("Y-m-d H:i:s", $date));
        $expire = $timestamp;
        $data_limit = val * 1000000;
        $config_test = adduser($Processing_value, $expire, $data_limit, $Check_token['access_token'], $marzban_list_get['url_panel']);
        $data_test = json_decode($config_test, true);
        $output_config_link = isset($data_test['subscription_url']) ? $data_test['subscription_url'] : 'خطا';
        $textcreatuser = "
                        
        🔑 اشتراک شما با موفقیت ساخته شد.
        
        ⏳ زمان اشتراک تست %d ساعت
        🌐 حجم سرویس تست %d مگابایت
        
        لینک اشتراک شما:
        
        ```%s```
                        ";
        $textcreatuser = sprintf($textcreatuser, time, val, $output_config_link);
        sendmessage($from_id, $textcreatuser, $keyboard);
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'home';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
        $limit_usertest = $user['limit_usertest'] - 1;
        $stmt = $connect->prepare("UPDATE user SET limit_usertest = ? WHERE id = ?");
        $stmt->bind_param("ss", $limit_usertest, $from_id);
        $stmt->execute();
        $count_usertest = $setting['count_usertest'] + 1;
        $stmt = $connect->prepare("UPDATE setting SET count_usertest = ?");
        $stmt->bind_param("s", $count_usertest);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'home';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
        $text_report = " 
  📣 پیام جدید 

⚙️ یک کاربر اکانت تست با نام کانفیگ ```$Processing_value``` دریافت کرد

اطلاعات کامل 👇👇

👤 آیدی عددی کاربر :  $from_id
 ☎️  شماره تلفن کاربر : {$user['number']}
🖥  نام پنل : $text
⚜️ نام کاربری کاربر : @$username";
    if (strlen($setting['Channel_Report'] )> 0){
    sendmessage($setting['Channel_Report'], $text_report, null);
}
}
//_________________________________________________
if ($text == "📚  آموزش") {
    sendmessage($from_id, "یکی از گزینه ها را انتخاب نمایید", $json_list_help);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'sendhelp';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "sendhelp") {
    $helpdata = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM help WHERE name_os = '$text'"));
    if (strlen($helpdata['Media_os']) != 0) {
        if ($helpdata['type_Media_os'] == "video") {
            sendvideo($from_id, $helpdata['Media_os'], $helpdata['Description_os']);
        } elseif ($helpdata['type_Media_os'] == "photo")
            sendphoto($from_id, $helpdata['Media_os'], $helpdata['Description_os']);
    } else {
        sendmessage($from_id, $helpdata['Description_os'], $json_list_help);
    }
}

//________________________________________________________
if ($text == $datatextbot['text_support']) {
    sendmessage($from_id, "☎️", $backuser);
    sendmessage($from_id, $datatextbot['text_dec_support'], $backuser);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'gettextpm';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == 'gettextpm') {
    sendmessage($from_id, "🚀 پیام شما ارسال شد منتظر پاسخ مدیریت باشید.", $keyboard);
    foreach ($admin_ids as $id_admin) {
        sendmessage($id_admin, "📥  یک پیام از کاربر با شناسه ```$from_id``` دریافت شد برای پاسخ ریپلای بزنید  و پیام خود را ارسال کنید.", null);
        forwardMessage($from_id, $message_id, $id_admin);
    }
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if($text == $datatextbot['text_fq']){
    sendmessage($from_id,$datatextbot['text_dec_fq'] , null);
}
$dateacc = jdate('Y/m/d');
$timeacc = jdate('h:i:s');
if($text == $datatextbot['text_account']){
    $text_account = "
    👨🏻‍💻 وضعیت حساب کاربری شما :

👤 نام شما :  $first_name
🕴🏻 شناسه شما : $from_id
💰 موجودی شما : {$user['Balance']} تومان

📆 $dateacc → ⏰ $timeacc
    ";
    sendmessage($from_id,$text_account , null);
}
$info_product = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM product WHERE name_product = '$Processing_value_one' LIMIT 1"));
if ($text == "🔐 خرید VPN") {
    sendmessage($from_id, "🌏 موقعیت سرویس  را انتخاب نمایید.", $list_marzban_panel_user);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_product';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "get_product"){
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    sendmessage($from_id, "🛒 لوکیشن دریافت شد سرویسی که میخواهید خریداری کنید را انتخاب نمایید.", $json_list_product_list);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_username';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "get_username"){
    $stmt = $connect->prepare("UPDATE user SET Processing_value_one = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    $text_create = "
👤برای ساخت اشتراک  یک نام کاربری انگلیسی ارسال نمایید.
    
    ⚠️ نام کاربری باید دارای شرایط زیر باشد
    
    1- فقط انگلیسی باشد و حروف فارسی نباشد
    2- کاراکترهای اضافی مانند @،#،% و... را نداشته باشد.
    3 - نام کاربری باید بدون فاصله باشد.
    
    🛑 در صورت رعایت نکردن موارد بالا با خطا مواجه خواهید شد
    ";
    sendmessage($from_id, "🛍 محصول انتخاب شد", $backuser);
    sendmessage($from_id, $text_create, $backuser);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'invoice';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "invoice"){
    if (!preg_match('~^[a-z\d_]{3,32}$~i', $text)) {
        sendmessage($from_id, "⛔️ نام کاربری معتبر نیست", $backuser);
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET Processing_value_tow = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    $randomString = bin2hex(random_bytes(5));
    $values = array($from_id, $randomString , $text, $Processing_value,$info_product['name_product'],$info_product['price_product'],$info_product['name_product'],$info_product['price_product'],$info_product['Volume_constraint'],$info_product['Service_time']);
    $stmt = $connect->prepare("INSERT IGNORE INTO invoice (id_user, id_invoice, username, Service_location, name_product, price_product, Volume, Service_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $from_id, $randomString, $text, $Processing_value, $info_product['name_product'], $info_product['price_product'], $info_product['Volume_constraint'], $info_product['Service_time']);
    $stmt->execute();
    $stmt->close();
$textin = "
📇  فاکتور

 👤 نام  کاربری  :   $text
 🔐 نام  سرویس  :   {$info_product['name_product']}
  📆 مدت اعتبار  :  {$info_product['Service_time']} روز
 💶قیمت   :   {$info_product['price_product']} هزار تومان
  👥 حجم اکانت  :  {$info_product['Volume_constraint']} گیگ
  
  💰 سفارش شما آماده پرداخت است.  ";
    sendmessage($from_id, $textin, $payment);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'payment';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "payment" && $text == "💰 پرداخت و دریافت سرویس"){
        if ($info_product['price_product'] >= $user['Balance']){
            sendmessage($from_id, "🚨 خطایی در هنگام پرداخت رخ داده است.
            
📝 دلیل خطا : عدم موجودی کافی ابتدا موجودی خود را افزایش دهید سپس مجددا سرویس را خریداری کنید", $keyboard);
            $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
            $step = 'home';
            $stmt->bind_param("ss", $step, $from_id);
            $stmt->execute();
            return;
    }
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$Processing_value'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $get_username_Check = getuser($user['Processing_value_tow'], $Check_token['access_token'], $marzban_list_get['url_panel']);
    $random_number = rand(1000000, 9999999);
    $username_redon = isset($get_username_Check['username']) ? $user['Processing_value_tow'] . $random_number : $user['Processing_value_tow'] ;
    $date = strtotime("+" . $info_product['Service_time'] . "days");
    $timestamp = strtotime(date("Y-m-d H:i:s", $date));
    $data_limit = $info_product['Volume_constraint'] * 1000000;
    $config = adduser($username_redon, $timestamp, $data_limit, $Check_token['access_token'],$marzban_list_get['url_panel']);
    $data = json_decode($config, true);
    $output_config_link = isset($data['subscription_url']) ? $data['subscription_url'] : 'خطا';
    $textcreatuser = "
                        
        🔑 اشتراک شما با موفقیت ساخته شد.
        
        ⏳ زمان اشتراک تست %d ساعت
        🌐 حجم سرویس تست %d گیگابایت
        
        لینک اشتراک شما   :
        
        ```%s```
                        ";
    $textcreatuser = sprintf($textcreatuser, $info_product['Service_time'], $info_product['Volume_constraint'] , $output_config_link);
    sendmessage($from_id, $textcreatuser, $keyboard);
    $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
    $Balance_prim= $user['Balance']- $info_product['price_product'];
    $stmt->bind_param("ss", $Balance_prim, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();

}
if($text == $datatextbot['text_Add_Balance']){
    sendmessage($from_id, "💸 مبلغ را  به تومان وارد کنید:
    
✅ حداکثر مبلغ 10.000.000میلیون تومان می باشد", $backuser);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'getprice';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "getprice"){
    if (!ctype_digit($text)){
        sendmessage($from_id, "❌ خطا 
💬 مبلغ باید بدون کاراکتر اضافی و فقط عدد باشد ", null);
        return;
    }
    if(intval($text)>10000000){
        sendmessage($from_id, "❌ خطا 
💬 مبلغ  باید کمتر 10 میلیون تومان باشد",  null);
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    sendmessage($from_id, "💵 روش پرداخت خود را انتخاب نمایید ", $step_payment);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_step_payment';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "get_step_payment"){
if ($text == "💳 کارت به کارت"){
    $textcarttocart = "
برای افزایش موجودی به صورت دستی، مبلغ دلخواه را به شماره‌ی حساب زیر واریز کنید 👇🏻

==================== 
6037000000000000 - bank 
====================

  عکس رسید خود را در این مرحله ارسال نمایید. 🔮

    
⚠️ حداکثر واریز مبلغ 10 میلیون تومان می باشد
 ⚠️امکان برداشت وجه از کیف پول ربات نیست
⚠️ مسئولیت واریز اشتباهی با شماست
⚠️ بعد پرداخت فقط تصویر رسید را ارسال نمایید
";
    sendmessage($from_id, $textcarttocart, $backuser);
}
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'forward_admin';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] =="forward_admin"){
    if (!$photo){
        sendmessage($from_id, "رسید نامعتبر است رسید باید فقط عکس باشد", null);
        return;
    }
    $dateacc = date('Y/m/d h:i:s');
    $randomString = bin2hex(random_bytes(5));
    $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status) VALUES (?,?,?,?,?)");
    $payment_Status = "Unpaid";
    $stmt->bind_param("sssss", $from_id ,$randomString, $dateacc,$Processing_value,$payment_Status);
    $stmt->execute();
    sendmessage($from_id, "🚀 رسید پرداخت  شما ارسال شد پس از تایید توسط مدیریت مبلغ به کیف پول شما واریز خواهد شد.", $keyboard);
    $Confirm_pay = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "✅ تایید پرداخت", 'callback_data' => "Confirm_pay_{$randomString}"],
                ['text' => '❌ رد پرداخت', 'callback_data' => "reject_pay_{$randomString}"],
            ]
        ]
    ]);
    $Payment_report = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Payment_report WHERE id_user = '$from_id' LIMIT 1"));
    $textsendrasid = "
    ⭕️ یک پرداخت جدید انجام شده است .

👤شناسه کاربر : ```$from_id```
🛒 کد پیگیری پرداخت : $randomString
⚜️  نام کاربری : $username
💸 مبلغ پرداختی : $Processing_value تومان

توضیحات : $caption
✍️ در صورت درست بودن رسید پرداخت را تایید نمایید.
";
    foreach ($admin_ids as $id_admin) {
        telegram('sendphoto',[
            'chat_id' => $id_admin,
            'photo'=> $photoid,
            'reply_markup' => $Confirm_pay,
            'caption'=> $textsendrasid,
            'parse_mode' => "Markdown",
        ]);
    }
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();

}
#----------------admin------------------#
if (!in_array($from_id, $admin_ids)) return;
if ($text == "panel" || $text == "/panel" || $text == "پنل مدیریت" || $text == "ادمین" ) {
    sendmessage($from_id, "به پنل ادمین خوش آمدید", $keyboardadmin);
}
if ($text == "🏠 بازگشت به منوی مدیریت") {
    sendmessage($from_id, "به پنل ادمین بازگشتید! ", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    return;
}
if ($text == "🔑 روشن / خاموش کردن قفل کانال") {
    if ($channels['Channel_lock'] == "off") {
        sendmessage($from_id, "عضویت اجباری روشن گردید", $channelkeyboard);
        $stmt = $connect->prepare("UPDATE channels SET Channel_lock = ?");
        $Channel_lock = 'on';
        $stmt->bind_param("s", $Channel_lock);
    } else {
        sendmessage($from_id, "عضویت اجباری خاموش گردید", $channelkeyboard);
        $stmt = $connect->prepare("UPDATE channels SET Channel_lock = ?");
        $Channel_lock = 'off';
        $stmt->bind_param("s", $Channel_lock);
        $stmt->execute();
    }
}
if ($text == "📣 تنظیم کانال جوین اجباری") {
    $text_channel = "
        برای تنظیم کانال عضویت اجباری لطفا آیدی کانال خود را بدون @ وارد نمایید.
        
        کانال فعلی شما: @" . $channels['link'];
    sendmessage($from_id, $text_channel, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'addchannel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "addchannel") {
    $text_set_channel = "
        🔰 کانال با موفقیت تنظیم گردید.
         برای  روشن کردن عضویت اجباری از منوی ادمین دکمه 📣 تنظیم کانال جوین اجباری  را بزنید
        ";
    sendmessage($from_id, $text_set_channel, $keyboardadmin);
    if (isset($channels['link'])) {
        $stmt = $connect->prepare("UPDATE channels SET link = ?");
        $stmt->bind_param("s", $text);
    } else {
        $stmt = $connect->prepare("INSERT INTO channels (link,Channel_lock) VALUES (?)");
        $Channel_lock = 'off';
        $stmt->bind_param("ss", $text, $Channel_lock);
    }
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($text == "👨‍💻 اضافه کردن ادمین") {
    sendmessage($from_id, "🌟آیدی عددی ادمین جدید را ارسال نمایید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'addadmin';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($user['step'] == "addadmin") {
    sendmessage($from_id, "🥳ادمین با موفقیت اضافه گردید", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("INSERT INTO admin (id_admin) VALUES (?)");
    $stmt->bind_param("s", $text);
    $stmt->execute();
}
if ($text == "❌ حذف ادمین") {
    sendmessage($from_id, "🛑 آیدی عددی ادمین را ارسال کنید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'deleteadmin';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($user['step'] == "deleteadmin") {
    if (!is_numeric($text) || !in_array($text, $admin_ids)) return;
    sendmessage($from_id, "✅ ادمین با موفقیت حذف گردید.", $keyboardadmin);
    $stmt = $connect->prepare("DELETE FROM admin WHERE id_admin = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($text == "➕ محدودیت ساخت اکانت تست برای کاربر") {
    $text_add_user_admin = "
        ⚜️ آیدی عددی کاربر را ارسال کنید 
    توضیحات : در این بخش میتوانید محدودیت ساخت اکانت تست را برای کاربر تغییر دهید. بطور پیشفرض محدودیت ساخت عدد 1 است
        ";
    sendmessage($from_id, $text_add_user_admin, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_limit_usertest_foruser';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($user['step'] == "add_limit_usertest_foruser") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "کاربری با این شناسه یافت نشد", $backadmin);
        return;
    }
    sendmessage($from_id, "آیدی عددی دریافت شد لطفا تعداد ساخت اکانت تست را ارسال کنید", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_number_limit';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($user['step'] == "get_number_limit") {
    sendmessage($from_id, "محدودیت برای کاربر تنظیم گردید.", $keyboardmarzban);
    $id_user_set = $text;
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET limit_usertest = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
}
if ($text == "➕ محدودیت ساخت اکانت تست برای همه") {
    sendmessage($from_id, "تعداد ساخت اکانت تست را  وارد نمایید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'limit_usertest_allusers';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "limit_usertest_allusers") {
    sendmessage($from_id, "محدودیت ساخت اکانت برای تمام کاربران تنظیم شد", $keyboardmarzban);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET limit_usertest = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
}
if ($text == "📯 تنظیمات کانال") {
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب کنید", $channelkeyboard);
}
if ($text == "📊 آمار ربات") {
    $stmt = $connect->prepare("SELECT COUNT(id) FROM user");
    $stmt->execute();
    $result = $stmt->get_result();
    $statistics = $result->fetch_array(MYSQLI_NUM);
    #-------------------------#
    $keyboardstatistics = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $statistics[0], 'callback_data' => 'countusers'],
                ['text' => '👤 تعداد کاربران', 'callback_data' => 'countusers'],
            ],
            [
                ['text' => $setting['count_usertest'], 'callback_data' => 'count_usertest_var'],
                ['text' => '🖥 مجموع اکانت تست', 'callback_data' => 'count_usertest_var'],
            ],
            [
                ['text' => phpversion(), 'callback_data' => 'phpversion'],
                ['text' => ' 👨‍💻 نسخه php  هاست', 'callback_data' => 'phpversion'],
            ],
        ]
    ]);
    sendmessage($from_id, "📈 آمار ربات شما", $keyboardstatistics);
}
if ($text == "🖥 پنل مرزبان") {
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب کنید", $keyboardmarzban);
}
if ($text == "🔌 وضعیت پنل") {
    sendmessage($from_id, "پنل خود را انتخاب کنید", $json_list_marzban_panel);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_panel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($user['step'] == "get_panel") {
    $stmt = $connect->prepare("SELECT * FROM marzban_panel WHERE name_panel = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $marzban_list_get = $stmt->get_result()->fetch_assoc();
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    if (isset($Check_token['access_token'])) {
        $Condition_marzban = "✅ پنل متصل است";
    } elseif ($Check_token['detail'] == "Incorrect username or password") {
        $Condition_marzban = "❌ نام کاربری یا رمز عبور پنل اشتباه است";
    } else {
        $Condition_marzban = "امکان اتصال به پنل مرزبان وجود ندارد😔";
    }
    $System_Stats = Get_System_Stats($marzban_list_get['url_panel'], $Check_token['access_token']);
    $active_users = $System_Stats['users_active'];
    $text_marzban = "
        اطلاعات پنل شما👇:
             
    🖥 وضعیت اتصال پنل مرزبان  :$Condition_marzban
    👤 تعداد کاربران فعال  :$active_users
        ";
    sendmessage($from_id, $text_marzban, $keyboardmarzban);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($text == "📜 مشاهده لیست  ادمین ها") {
    $List_admin = null;
    $admin_ids = array_filter($admin_ids);
    foreach ($admin_ids as $admin) {
        $List_admin .= "$admin\n";
    }
    $list_admin_text = "👨‍🔧 آیدی عددی ادمین ها: 
    
$List_admin";
    sendmessage($from_id, $list_admin_text, $admin_section_panel);
}

if ($text == "🖥 اضافه کردن پنل  مرزبان") {
    $text_add_panel = "
        برای اضافه کردن پنل مرزبان به ربات ابتدا یک نام برای پنل خود ارسال کنید
        
     ⚠️ توجه : نام پنل نامی است که  در هنگام انجام عملیات جستجو  پنل از طریق نام است.
        ";
    sendmessage($from_id, $text_add_panel, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_name_panel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "add_name_panel") {
    $stmt = $connect->prepare("INSERT INTO marzban_panel (name_panel) VALUES (?)");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $text_add_url_panel = "
            🔗نام پنل ذخیره شد حالا  آدرس  پنل خود ارسال کنید
        
     ⚠️ توجه :
    🔸 آدرس پنل باید  بدون dashboard ارسال شود.
    🔹 در صورتی که  پورت پنل 443 است پورت را نباید وارد کنید.    
            ";
    sendmessage($from_id, $text_add_url_panel, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_link_panel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET  Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "add_link_panel") {
    if (filter_var($text, FILTER_VALIDATE_URL)) {
        sendmessage($from_id, "👤 آدرس پنل ذخیره شد حالا نام کاربری  را ارسال کنید.", $backadmin);
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'add_username_panel';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE marzban_panel SET  url_panel = ? WHERE name_panel = ?");
        $stmt->bind_param("ss", $text, $Processing_value);
        $stmt->execute();
    } else {
        sendmessage($from_id, "🔗 آدرس دامنه نامعتبر است", $backadmin);
    }
} elseif ($user['step'] == "add_username_panel") {
    sendmessage($from_id, "🔑 نام کاربری ذخیره شد  در پایان رمز عبور پنل مرزبان خود را وارد نمایید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_password_panel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE marzban_panel SET  username_panel = ? WHERE name_panel = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
} elseif ($user['step'] == "add_password_panel") {
    sendmessage($from_id, "تبریک پنل شما با موفقیت اضافه گردید.", $backadmin);
    sendmessage($from_id, "🥳", $keyboardmarzban);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE marzban_panel SET  password_panel = ? WHERE name_panel = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
}
if ($text == "❌ حذف پنل") {
    sendmessage($from_id, "پنلی که میخواهید حذف کنید را انتخاب کنید.", $json_list_marzban_panel);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'removepanel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "removepanel") {
    sendmessage($from_id, "پنل با موفقیت حذف گردید.", $keyboardmarzban);
    $stmt = $connect->prepare("DELETE FROM marzban_panel WHERE name_panel = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($text == "📨 ارسال پیام به کاربر") {
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب کنید", $sendmessageuser);
} elseif ($text == "✉️ ارسال همگانی") {
    sendmessage($from_id, "متن خود را ارسال کنید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'gettextforsendall';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "gettextforsendall") {
    foreach ($users_ids as $id) {
        sendmessage($id, $text, null);
    }
    sendmessage($from_id, "✅ پیام برای تمامی کاربرن ارسال شد.", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "📤 فوروارد همگانی") {
    sendmessage($from_id, "متن فورواردی خود را ارسال کنید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'gettextforwardMessage';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "gettextforwardMessage") {
    foreach ($users_ids as $id) {
        forwardMessage($from_id, $message_id, $id);
    }
    sendmessage($from_id, "✅ پیام برای تمامی کاربرن فوروارد شد.", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
//_________________________________________________
if ($text  == "📝 تنظیم متن ربات") {
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب کنید.", $textbot);
} elseif ($text == "تنظیم متن شروع") {
    $textstart = "
            متن جدید خود را ارسال کنید.
        متن فعلی :
         " . $datatextbot['text_start'];
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'changetextstart';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "changetextstart") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_start'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "دکمه اطلاعات سرویس") {
    $textstart = "
        متن جدید خود را ارسال کنید.
        متن فعلی :
         " . $datatextbot['text_info'];
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'changetextinfo';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "changetextinfo") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_info'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "دکمه اکانت تست") {
    $textstart = "
        متن جدید خود را ارسال کنید.
        متن فعلی :
         " . $datatextbot['text_usertest'];
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'changetextusertest';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "changetextusertest") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_usertest'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "📝 تنظیم متن توضیحات اطلاعات سرویس") {
    $textstart = "
        متن جدید خود را ارسال کنید.
        متن فعلی :
        ``` " . $datatextbot['text_dec_info'] . "```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'changetextinfodec';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "changetextinfodec") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_dec_info'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "📝 تنظیم متن توضیحات  اکانت تست") {
    $textstart = "
        متن جدید خود راارسال کنید.
        متن فعلی :
        ``` " . $datatextbot['text_dec_usertest'] . "```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_dec_usertest';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_dec_usertest") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_dec_usertest'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "متن دکمه 📚  آموزش") {
    $textstart = "
        متن جدید خود را ارسال کنید.
        متن فعلی :
        " . $datatextbot['text_help'];
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_help';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_help") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_help'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "متن دکمه ☎️ پشتیبانی") {
    $textstart = "
        متن جدید خود راارسال کنید.
        متن فعلی :
        " . $datatextbot['text_support'];
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_support';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_support") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_support'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "📝 تنظیم متن توضیحات پشتیبانی") {
    $textstart = "
        متن جدید خود راارسال کنید.
        متن فعلی :
        ```". $datatextbot['text_dec_support']."```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_dec_support';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_dec_support") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_dec_support'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "دکمه سوالات متداول") {
    $textstart = "
        متن جدید خود راارسال کنید.
        متن فعلی :
        ```". $datatextbot['text_fq']."```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_fq';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_fq") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_fq'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "📝 تنظیم متن توضیحات  سوالات متداول") {
    $textstart = "
        متن جدید خود راارسال کنید.
        متن فعلی :
        ```". $datatextbot['text_dec_fq']."```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_dec_fq';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_dec_fq") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_dec_fq'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "متن دکمه حساب کاربری") {
    $textstart = "
        متن جدید خود راارسال کنید.
        متن فعلی :
        ```". $datatextbot['text_account']."```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_account';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_account") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_account'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "دکمه افزایش موجودی") {
    $textstart = "
        متن جدید خود راارسال کنید.
        متن فعلی :
        ```". $datatextbot['text_Add_Balance']."```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_Add_Balance';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_Add_Balance") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_Add_Balance'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
//_________________________________________________
if ($text == "✍️ ارسال پیام برای یک کاربر") {
    sendmessage($from_id, "متن خود را ارسال کنید", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'sendmessagetext';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "sendmessagetext") {
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    sendmessage($from_id, "✅ متن دریافت شد  حالا آیدی عددی کاربر را ارسال کنید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'sendmessagetid';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "sendmessagetid") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "کاربری با این شناسه یافت نشد", $backadmin);
        return;
    }
    $textsendadmin = "
        👤 یک پیام از طرف ادمین ارسال شده است  
    
    متن پیام :
    $Processing_value
        ";
    sendmessage($text,  $textsendadmin, null);
    sendmessage($from_id, "✅ پیام ارسال شد", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}

//_________________________________________________
if ($text == "📚 بخش آموزش"){
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب کنید😊", $keyboardhelpadmin);
}
elseif ($text == "📚 اضافه کردن آموزش") {
    $text_add_help_name = "
        برای اضافه کردن   آموزش  یک نام  ارسال کنید  
     ⚠️ توجه : نام آموزش نامی است که کاربر در لیست مشاهده می کند.
        ";
    sendmessage($from_id, $text_add_help_name, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_name_help';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "add_name_help") {
    $stmt = $connect->prepare("INSERT IGNORE INTO help (name_os) VALUES (?)");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $text_add_dec = "
            🔗نام آموزش ذخیره شد حالا  توضیحات خود را ارسال کنید 
        
     ⚠️ توجه :
    🔸 توضیحات میتوانید همراه با عکس یا فیلم ارسال کنید
            ";
    sendmessage($from_id, $text_add_dec, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_dec';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET  Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "add_dec") {
    if ($photo) {
        $stmt = $connect->prepare("UPDATE help SET  Media_os	 = ? WHERE name_os = ?");
        $stmt->bind_param("ss", $photoid, $Processing_value);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE help SET  Description_os	 = ? WHERE name_os = ?");
        $stmt->bind_param("ss", $caption, $Processing_value);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE help SET  type_Media_os	 = ? WHERE name_os = ?");
        $type = "photo";
        $stmt->bind_param("ss", $type, $Processing_value);
        $stmt->execute();
    } elseif ($text) {
        $stmt = $connect->prepare("UPDATE help SET  Description_os	 = ? WHERE name_os = ?");
        $stmt->bind_param("ss", $text, $Processing_value);
        $stmt->execute();
    } elseif ($video) {
        $stmt = $connect->prepare("UPDATE help SET  Media_os	 = ? WHERE name_os = ?");
        $stmt->bind_param("ss", $videoid, $Processing_value);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE help SET  Description_os	 = ? WHERE name_os = ?");
        $stmt->bind_param("ss", $caption, $Processing_value);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE help SET  type_Media_os	 = ? WHERE name_os = ?");
        $type = "video";
        $stmt->bind_param("ss", $type, $Processing_value);
        $stmt->execute();
    }
    sendmessage($from_id, "✅ آموزش با موفقیت ذخیره شد", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "❌ حذف آموزش") {
    sendmessage($from_id, "نام آموزش را انتخاب کنید", $json_list_help);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'remove_help';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "remove_help") {
    $stmt = $connect->prepare("DELETE FROM help WHERE name_os = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    sendmessage($from_id, "✅ آموزش حذف گردید.", $keyboardhelpadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
//_________________________________________________
if ($forward_from_id != 0) {
    $textSendAdminToUser = "
        📩 یک پیام از سمت مدیریت برای شما ارسال گردید.
    
    متن پیام : 
    $text";
    sendmessage($forward_from_id, $textSendAdminToUser, null);
    sendmessage($from_id, "✅ پیام با موفقیت برای کاربر ارسال گردید.", null);
}
//_________________________________________________
$Bot_Status = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['Bot_Status'], 'callback_data' => $setting['Bot_Status']],
        ],
    ]
]);
if ($text == "📡 وضعیت  ربات") {
    sendmessage($from_id, "وضعیت ربات ", $Bot_Status);
}
if ($datain == "✅  ربات روشن است") {
    $stmt = $connect->prepare("UPDATE setting SET Bot_Status = ?");
    $Status = '❌  ربات خاموش است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "ربات خاموش گردید❌", null);
} elseif ($datain == "❌  ربات خاموش است") {
    $stmt = $connect->prepare("UPDATE setting SET Bot_Status = ?");
    $Status = '✅  ربات روشن است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "🤖 ربات روشن گردید.", null);
}
//_________________________________________________
if ($text == "🔒 مسدود کردن کاربر") {
    sendmessage($from_id, "👤 آیدی عددی کاربر را ارسال کنید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'getidblock';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "getidblock") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "کاربری با این شناسه یافت نشد", $backadmin);
        return;
    }
    $query = sprintf("SELECT * FROM user WHERE id = '%d' LIMIT 1", $text);
    $result = mysqli_query($connect, $query);
    $userblock = mysqli_fetch_assoc($result);
    if ($userblock['User_Status'] == "block") {
        sendmessage($from_id, "کاربر از قبل بلاک بوده است❗️ ", $backadmin);
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET User_Status = ? WHERE id = ?");
    $User_Status = "block";
    $stmt->bind_param("ss", $User_Status, $text);
    $stmt->execute();
    sendmessage($from_id, "🚫 کاربر مسدود شد حالا دلیل مسدودی هم ارسال کنید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'adddecriptionblock';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "adddecriptionblock") {
    $stmt = $connect->prepare("UPDATE user SET description_blocking = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id, "✍️ دلیل مسدودی کاربر ذخیره شد", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "🔓 رفع  مسدودی کاربر ") {
    sendmessage($from_id, "👤 آیدی عددی کاربر را ارسال کنید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'getidunblock';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "getidunblock") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "کاربری با این شناسه یافت نشد", $backadmin);
        return;
    }
    $query = sprintf("SELECT * FROM user WHERE id = '%d' LIMIT 1", $text);
    $result = mysqli_query($connect, $query);
    $userunblock = mysqli_fetch_assoc($result);
    if ($userunblock['User_Status'] == "Active") {
        sendmessage($from_id, "کاربر بلاک نیست😐", $backadmin);
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET User_Status = ? WHERE id = ?");
    $User_Status = "Active";
    $stmt->bind_param("ss", $User_Status, $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET description_blocking = ? WHERE id = ?");
    $spcae = "";
    $stmt->bind_param("ss", $spcae, $Processing_value);
    $stmt->execute();
    sendmessage($from_id, "کاربر از حالت مسدودی خارج گردید.🤩", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
//_________________________________________________
if($text == "♨️بخش قوانین"){
    sendmessage($from_id, "یکی از گزینه های ز یر را انتخاب کنید", $rollkey);
}
elseif ($text == "⚖️ متن قانون") {
    $textstart = "
        متن جدید خود راارسال کنید.
        متن فعلی :
        ```". $datatextbot['text_roll']."```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_roll';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_roll") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_roll'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
    $roll_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $setting['roll_Status'], 'callback_data' => $setting['roll_Status']],
            ],
        ]
    ]);
if($text == "💡 روشن / خاموش کردن تایید قوانین"){
        sendmessage($from_id, "وضعیت قانون ", $roll_Status);
    }
    if ($datain == "✅  تایید قانون  روشن است") {
        $stmt = $connect->prepare("UPDATE setting SET roll_Status = ?");
        $Status = '❌ تایید قوانین خاموش است';
        $stmt->bind_param("s", $Status);
        $stmt->execute();
        Editmessagetext($from_id, $message_id, "قانون غیرفعال گردید❌", null);
    } elseif ($datain == "❌ تایید قوانین خاموش است") {
        $stmt = $connect->prepare("UPDATE setting SET roll_Status = ?");
        $Status = '✅  تایید قانون  روشن است';
        $stmt->bind_param("s", $Status);
        $stmt->execute();
        Editmessagetext($from_id, $message_id, "♨️ قانون فعال  گردید. از این پس اگر کاربری قوانین را تایید نکرده باشد نمی تواند از امکانات ربات استفاده نماید", null);
    }
//_________________________________________________
if($text == "👤 خدمات کاربر"){
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب کنید 👇😊", $User_Services);
}
#-------------------------#

elseif($text == "📊 وضعیت تایید شماره کاربر"){
    sendmessage($from_id, "آیدی عددی کاربر را ارسال نمایید", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_status';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "get_status"){
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "کاربری با این شناسه یافت نشد", $backadmin);
        return;
    }
    $user_phone_status = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '$text' LIMIT 1"));
    if ($user_phone_status['number'] == "none"){
        sendmessage($from_id, "🛑شماره موبایل تایید نشده است🛑", $User_Services);
    }
    else{
        sendmessage($from_id,"شماره موبایل کاربر تایید شده است ✅🎉" , $User_Services);
    }
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#

$get_number = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['get_number'], 'callback_data' => $setting['get_number']],
        ],
    ]
]);
if($text == "☎️ وضعیت احراز هویت شماره تماس"){
    sendmessage($from_id, "📞  وضعیت احراز هویت شماره تماس", $get_number);
}
if ($datain == "✅ تایید شماره موبایل روشن است") {
    $stmt = $connect->prepare("UPDATE setting SET get_number = ?");
    $Status = '❌ احرازهویت شماره تماس غیرفعال است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "احرازهویت شماره تماس غیرفعال گردید❌", null);
} elseif ($datain == "❌ احرازهویت شماره تماس غیرفعال است") {
    $stmt = $connect->prepare("UPDATE setting SET get_number = ?");
    $Status = '✅ تایید شماره موبایل روشن است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "🥳 احرازهویت شماره تماس فعال گردید. از این پس هرکاربری بخواهد از خدمات ربات استفاده کند باید شماره تماس خود را ارسال کند.", null);
}
#-------------------------#
if ($text == "👀 مشاهده شماره تلفن کاربر"){
    sendmessage($from_id, "آیدی عددی کاربر را ارسال نمایید", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_number_admin';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "get_number_admin"){
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "کاربری با این شناسه یافت نشد", $backadmin);
        return;
    }
    $user_phone_number = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '$text' LIMIT 1"));
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    if ($user_phone_number['number'] == "none"){
        sendmessage($from_id, "کاربر شماره موبایل خود را ارسال نکرده است", $User_Services);
        return;
    }
    $text_number = "
    ☎️ شماره تلفن کاربر :{$user_phone_number['number']}
     ";
    sendmessage($from_id, $text_number, $User_Services);
}
#-------------------------#
if ($text == "👈 تایید دستی شماره"){
    sendmessage($from_id, "آیدی عددی کاربر را ارسال نمایید", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'confrim_number';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "confrim_number"){
    $stmt = $connect->prepare("UPDATE user SET number  = ? WHERE id = ?");
    $confrimnum = 'confrim number by admin';
    $stmt->bind_param("ss", $confrimnum, $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step  = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $text);
    $stmt->execute();
    sendmessage($from_id, "✅ شماره موبایل کاربر  تایید شد", $User_Services);
}
if($text == "📣 تنظیم کانال گزارش"){
        $text_channel = "
        📣 آیدی عددی کانال گزارش خود را ارسال نمایید.

برای دریافت آیدی عددی کانال می توانید پیامی داخل کانال ارسال کرده و پیام ارسال شده را برای آیدی زیر فوروارد کنید تا ایدی عددی  دریافت کنید.
@myidbot
        
     آیدی عددی کانال فعلی شما:" . $setting['Channel_Report'];
        sendmessage($from_id, $text_channel, $backadmin);
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'addchannelid';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
    }
    elseif ($user['step'] == "addchannelid") {
        sendmessage($from_id, "🔰 کانال با موفقیت تنظیم گردید.", $keyboardadmin);
            $stmt = $connect->prepare("UPDATE setting SET Channel_Report = ?");
            $stmt->bind_param("s", $text);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'home';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
        sendmessage($setting['Channel_Report'], "تست ارسال کانال گزارش", null);
    }
#-------------------------#
if($text == "🏬  بخش فروشگاه"){
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب کنید", $shopkeyboard);
}
elseif ($text == "🛍 اضافه کردن محصول"){
    $text_add_name_product = "
    ابتدا نام اشتراک خود را ارسال نمایید
⚠️ نکات هنگام وارد کردن ام محصول :
• در کنار نام اشتراک حتما قیمت اشتراک را هم وارد کنید .
•  در کنار نام اشتراک حتما زمان اشتراک را هم وارد کنید.
    ";
    sendmessage($from_id, $text_add_name_product, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_limit';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "get_limit"){
    $stmt = $connect->prepare("INSERT IGNORE INTO product (name_product) VALUES (?)");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    sendmessage($from_id,"نام محصول ذخیره شد ✅
حجم اشتراک را ارسال کنید توجه واجد حجم گیگابایت است", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_time';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "get_time"){
    if (!ctype_digit($text))
    {
        sendmessage($from_id,"حجم نامعتبر است", $backadmin);
        return;
    }
    $stmt = $connect->prepare("UPDATE product SET Volume_constraint = ? WHERE name_product = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id,"حجم اشتراک ذخیره شد✅
زمان اشتراک را وارد نمایید توجه زمان واحد زمان اشتراک روز است", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_price';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "get_price"){
    if (!ctype_digit($text))
    {
        sendmessage($from_id,"زمان نامعتبر است", $backadmin);
        return;
    }
    $stmt = $connect->prepare("UPDATE product SET Service_time = ? WHERE name_product = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id,"زمان اشتراک ذخیره شد✅
قمیت اشتراک  را ارسال کنید.
توجه : 
قیمت محصول براساس تومان است و  قیمت را بدون هیچ کاراکتر اضافی ارسال نمایید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'endstep';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "endstep"){
    if (!ctype_digit($text))
    {
        sendmessage($from_id,"قیمت نامعتبر است", $backadmin);
        return;
    }
    $stmt = $connect->prepare("UPDATE product SET price_product = ? WHERE name_product = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id,"محصول با موفقیت ذخیره شد🥳🎉", $shopkeyboard);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if($text == "👨‍🔧 بخش ادمین"){
    sendmessage($from_id,"یکی از گزینه های زیر را انتخاب کنید", $admin_section_panel);
}
#-------------------------#
if($text == "⚙️ تنظیمات"){
    sendmessage($from_id,"یکی از گزینه های زیر را انتخاب کنید",$setting_panel);
}
#-------------------------#
if($text == "📱 احراز هویت شماره"){
    sendmessage($from_id,"یکی از گزینه های زیر را انتخاب کنید",$valid_Number);
}
#-------------------------#
if($text == "📊 بخش گزارشات"){
    sendmessage($from_id,"یکی از گزینه های زیر را انتخاب کنید",$reports);
}
#-------------------------#
if(preg_match('/Confirm_pay_(\w+)/',$datain, $dataget)) {
    $order_id= $dataget[1];
    $Payment_report = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Payment_report WHERE id_order = '$order_id' LIMIT 1"));
    $Balance_id = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '{$Payment_report['id_user']}' LIMIT 1"));
    if ($Payment_report['payment_Status'] == "paid" || $Payment_report['payment_Status'] == "reject"){
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "❌ این پرداخت قبلا توسط ادمین دیگری بررسی  شده است",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
    $Balance_confrim = intval($Balance_id['Balance']) + intval($Payment_report['price']);
    $stmt->bind_param("ss", $Balance_confrim, $Payment_report['id_user']);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE Payment_report SET payment_Status = ? WHERE id_order = ?");
    $Status_change = "paid";
    $stmt->bind_param("ss", $Status_change, $Payment_report['id_order']);
    $stmt->execute();

    $textconfrom = "
    💵 پرداخت با موفقیت تایید گردید.
      به موجودی کاربر مبلغ {$Payment_report['price']} اضافه گردید.
    ";
    sendmessage($from_id,$textconfrom,null);
    sendmessage($Payment_report['id_user'],"💎 کاربر گرامی مبلغ{$Payment_report['price']} تومان به کیف پول شما واریز گردید با تشکر از پرداخت شما.

🛒 کد  پیگیری شما : {$Payment_report['id_order']}",null);
}
if(preg_match('/reject_pay_(\w+)/',$datain, $datagetr)) {
    $id_order= $datagetr[1];
    $Payment_report = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Payment_report WHERE id_order = '$id_order' LIMIT 1"));
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $Payment_report['id_user'], $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET Processing_value_one = ? WHERE id = ?");
    $stmt->bind_param("ss", $id_order, $from_id);
    $stmt->execute();
    if ($Payment_report['payment_Status'] == "reject" || $Payment_report['payment_Status']  == "paid"){
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "❌ این پرداخت قبلا توسط ادمین دیگری بررسی  شده است",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    $stmt = $connect->prepare("UPDATE Payment_report SET payment_Status = ? WHERE id_order = ?");
    $Status_change = "reject";
    $stmt->bind_param("ss", $Status_change, $id_order);
    $stmt->execute();

    sendmessage($from_id,"دلیل رد کردن پرداخت را ارساال نمایید",$backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = "reject-dec";
    $stmt->bind_param("ss", $step, $Payment_report['id_user']);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $text, null);
}
elseif($user['step'] == "reject-dec"){
    $stmt = $connect->prepare("UPDATE Payment_report SET dec_not_confirmed = ? WHERE id_order = ?");
    $stmt->bind_param("ss", $text, $user['Processing_value_one']);
    $stmt->execute();
    $text_reject = "❌کاربر گرامی پرداخت شما به دلیل زیر رد گردید.
✍️ $text
🛒 کد  پیگیری پرداخت : {$user['Processing_value_one']}
";
    sendmessage($Processing_value,$text_reject,null);
    sendmessage($from_id,"⭕️ پرداخت با موفقیت رد گردید و به کاربر پیام ارسال شد.",$keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = "home";
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
