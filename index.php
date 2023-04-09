    <?php
    include('function.php');
    include('config.php');
    include('jdf.php');
    echo "<body style=background-color:#000;color:#fff>hello</body>";
    #-----------------------------#
    define('API_KEY', $token);
    #-----------------------------#
    $update = json_decode(file_get_contents('php://input'), true);
    if (isset($update['message'])) {
        $from_id = $update['message']['from']['id'];
        $chat_id = $update['message']['chat']['id'];
        $text = $update['message']['text'];
        $first_name = $update['message']['from']['first_name'];
        $message_id = $update['message']['message_id'];
        $usernameaccont = $update['message']['chat']['username'];
    }
    #-----------------------------#
    if (!is_dir("data")) {
        mkdir("data");
    }
    if (!is_dir("data/user")) {
        mkdir("data/user");
    }
    if (!is_dir("data/user/$chat_id")) {
        mkdir("data/user/$chat_id");
    }
    if (!file_exists("data/user/$chat_id/step")) {
        file_put_contents("data/user/$chat_id/step", null);
    }
    if (!file_exists("data/user/$from_id/Account_status")) {
        file_put_contents("data/user/$from_id/Account_status", "false");
    }
    if (!file_exists("data/channel")) {
        file_put_contents("data/channel", "vpniran");
    }
    if (!file_exists("data/channelstatus")) {
        file_put_contents("data/channelstatus", "false");
    }
    #-----------------------------#
    $step = file_get_contents("data/user/$from_id/step");
    $Account_status = file_get_contents("data/user/$from_id/Account_status");
    $channel = file_get_contents('data/channel');
    $channelstatus = file_get_contents('data/channelstatus');
    $forchaneel = json_decode(file_get_contents("https://api.telegram.org/bot$token/getChatMember?chat_id=@$channel&user_id=" . $from_id));
    $tch = $forchaneel->result->status;
    #-----------------------------#
    $o = "🏠 بازگشت به منوی اصلی";
    $back = json_encode([
        'keyboard' => [
            [['text' => "$o"]],
        ],
        'resize_keyboard' => true
    ]);
    $backadmin = json_encode([
        'keyboard' => [
            [['text' => "🏠 بازگشت به منوی ادمین"]],
        ],
        'resize_keyboard' => true
    ]);
    $key1 = json_encode([
        'keyboard' => [
            [['text' => "📊 اطلاعات سرویس"], ['text' => "🔑 اکانت تست"]],
        ],
        'resize_keyboard' => true
    ]);
    $admin = json_encode([
        'keyboard' => [
            [['text' => "📣 تنظیم کانال جوین اجباری"]],
            [['text' => "🔑 روشن / خاموش کردن قفل کانال"]],
        ],
        'resize_keyboard' => true
    ]);
    #-------------channel----------------#
    if ($tch != 'member' && $tch != 'creator' && $tch != 'administrator' && $channelstatus == "true" && $chat_id != $adminidnumbeer) {
        bot('sendmessage', [
            'chat_id' => $chat_id,
            'text' => "⚠️کاربر گرامی ؛ شما عضو چنل ما نیستید
    ❗️@$channel
    عضو کانال بالا شوید و مجدد 
    /start
    کنید❤️",
        ]);
    } else {
        #-------------start----------------#
        if ($text == "/start") {
            bot('sendmessage', [
                'chat_id' => $chat_id,
                'text' => "
    سلام $first_name, عزیز👋
     به ربات  ما خوش آمدی.😊",
                'reply_markup' => $key1,
                'parse_mode' => "Markdown",
                'reply_to_message_id' => $message_id,
            ]);
            file_put_contents("data/user/$from_id/step", "home");
        }
        #-------------back----------------#
        else if ($text == $o) {
            bot('sendmessage', [
                'chat_id' => $chat_id,
                'text' => "به صفحه اصلی بازگشتید!",
                'reply_markup' => $key1,
                'parse_mode' => "Markdown",
                'reply_to_message_id' => $message_id,
            ]);
            file_put_contents("data/user/$from_id/step", "home");
        }
        #-----------------------------------#
        if ($text == "📊 اطلاعات سرویس") {
            bot('sendmessage', [
                'chat_id' => $chat_id,
                'text' => "نام کاربری خود را وارد نمایید
            
            
    ⚠️ نام کاربری باید بدون کاراکترهای اضافه مانند @ ، فاصله ، خط تیره باشد. 
    ⚠️ نام کاربری باید انگلیسی باشد
            ",
                'reply_markup' => $back,
                'parse_mode' => "Markdown",
                'reply_to_message_id' => $message_id,
            ]);
            file_put_contents("data/user/$from_id/step", "Service Inquiry");
        } elseif ($step == "Service Inquiry" && $text != $o && $text != "/start") {
            $username = $text;
            if (preg_match('/^[A-Za-z0-9_]+$/', $username)) {
    
                $data_useer = getuser($text);
                if (isset($data_useer['username'])) {
                    #-------------status----------------#
                    $status = $data_useer['status'];
                    switch ($status) {
                        case 'active':
                            $status_var = "✅فعال";
                            break;
                        case 'limited':
                            $status_var = "🔚پایان حجم";
                            break;
                        case 'disabled':
                            $status_var = "❌غیرفعال";
                            break;
    
                        default:
                            $status_var = "🤷‍♂️نامشخص";
                            break;
                    }
    
    
                    #-----------------------------#
                    $timestamp = $data_useer['expire'];
                    $expirationDate = jdate('Y/m/d', $timestamp);
                    $current_date = jdate('Y/m/d');
                    if (date('Y/m/d', $timestamp) == "1970/01/01") {
                        $expirationDate = "نامحدود";
                    }
                    #-----------------------------#
                    $LastTraffic = round($data_useer['data_limit'] / 1073741824, 2) . "GB";
                    if (round($data_useer['data_limit'] / 1073741824, 2) < 1) {
                        $LastTraffic = round($data_useer['data_limit'] / 1073741824, 2) * 1000 . "MB";
                    }
                    if (round($data_useer['data_limit'] / 1073741824, 2) == 0) {
                        $LastTraffic = "نامحدود";
                        $RemainingVolume = "نامحدود";
                    }
                    #-----------------------------#
                    $usedTrafficGb = round($data_useer['used_traffic'] / 1073741824, 2) . "GB";
                    if (round($data_useer['used_traffic'] / 1073741824, 2) < 1) {
                        $usedTrafficGb = round($data_useer['used_traffic'] / 1073741824, 2) * 1000 . "MB";
                    }
                    if (round($data_useer['used_traffic'] / 1073741824, 2) == 0) {
                        $usedTrafficGb = "مصرف نشده";
                    }
                    #-----------------------------#
                    if (round($data_useer['data_limit'] / 1073741824, 2) != 0) {
                        $min = round($data_useer['data_limit'] / 1073741824, 2) - round($data_useer['used_traffic'] / 1073741824, 2);
                        $RemainingVolume  = $min . "GB";
                        if ($min < 1) {
                            $RemainingVolume = $min * 1000 . "MB";
                        }
                    }
                    #-----------------------------#
    
                    $currentTime = time();
                    $timeDiff = $data_useer['expire'] - $currentTime;
    
                    if ($timeDiff > 0) {
                        $day = floor($timeDiff / 86400) . " روز";
                    } else {
                        $day = "نامحدود";
                    }
                    #-----------------------------#
    
    
                    $keyboardinfo = [
                        'inline_keyboard' => [
                            [
                                ['text' => $data_useer['username'], 'callback_data' => 'username'],
                                ['text' => 'نام کاربری :', 'callback_data' => 'username'],
                            ], [
                                ['text' => $status_var, 'callback_data' => 'status_var'],
                                ['text' => 'وضعیت:', 'callback_data' => 'status_var'],
                            ], [
                                ['text' =>  $expirationDate, 'callback_data' => 'expirationDate'],
                                ['text' => 'زمان پایان:', 'callback_data' => 'expirationDate'],
                            ], [
                                ['text' =>  $day, 'callback_data' => 'روز'],
                                ['text' => 'زمان باقی مانده تا پایان سرویس:', 'callback_data' => 'day'],
                            ], [
                                ['text' =>  $LastTraffic, 'callback_data' => 'LastTraffic'],
                                ['text' => 'حجم کل سرویس :', 'callback_data' => 'LastTraffic'],
                            ], [
                                ['text' =>  $usedTrafficGb, 'callback_data' => 'expirationDate'],
                                ['text' => 'حجم مصرف شده سرویس :', 'callback_data' => 'expirationDate'],
                            ], [
                                ['text' =>  $RemainingVolume, 'callback_data' => 'RemainingVolume'],
                                ['text' => 'حجم باقی مانده  سرویس :', 'callback_data' => 'RemainingVolume'],
                            ]
                        ]
                    ];
                    $parameters = bot('sendmessage', [
                        'chat_id' => $chat_id,
                        'text' => "📊 اطلاعات سرویس شما :",
                        'parse_mode' => 'Markdown',
                        'reply_markup' => json_encode($keyboardinfo),
                        'reply_to_message_id' => $message_id
                    ]);
                    bot('sendmessage', [
                        'chat_id' => $chat_id,
                        'text' => "لطفا یک گزینه را انتخاب کنید :",
                        'parse_mode' => 'Markdown',
                        'reply_markup' => $key1
                    ]);
                } else {
                    bot('sendmessage', [
                        'chat_id' => $chat_id,
                        'text' => "نام کاربری وجود ندارد",
                        'parse_mode' => 'Markdown',
                        'reply_markup' => $key1
                    ]);
                }
                file_put_contents("data/user/$from_id/step", "home");
            } else {
                bot('sendmessage', [
                    'chat_id' => $chat_id,
                    'text' => "❌نام کاربری نامعتبر است
            
            🔄 مجددا نام کاربری خود  را ارسال کنید",
                    'reply_markup' => $back,
                    'parse_mode' => "Markdown",
                    'reply_to_message_id' => $message_id,
                ]);
            }
        }
        #---------------------------------#
        if ($text == "🔑 اکانت تست") {
            if ($Account_status == "false") {
                bot('sendmessage', [
                    'chat_id' => $chat_id,
                    'text' => "
            👤برای ساخت اشتراک تست یک نام کاربری انگلیسی ارسال نمایید.
    
    ⚠️ نام کاربری باید دارای شرایط زیر باشد
    
    1- فقط انگلیسی باشد و حروف فارسی نباشد
    2- کاراکترهای اضافی مانند @،#،% و... را نداشته باشد.
    3 - نام کاربری باید بدون فاصله باشد.
    
    🛑 در صورت رعایت نکردن موارد بالا با خطا مواجه خواهید شد
            ",
                    'reply_markup' => $back,
                    'parse_mode' => "Markdown",
                    'reply_to_message_id' => $message_id,
                ]);
                file_put_contents("data/user/$chat_id/step", "crateusertest");
            } else {
                bot('sendmessage', [
                    'chat_id' => $chat_id,
                    'text' => '🔰 شما فقط یکبار می توانید ازسرویس تست استفاده نمایید.',
                    'reply_markup' => $key1,
                ]);
            }
        }
        #-----------------------------------#
        elseif ($step == "crateusertest") {
            if (preg_match('/^[a-zA-Z0-9_]{3,32}$/', $text)) {
                $Allowedusername = getuser($text);
                if (empty($Allowedusername['username'])) {
                    $date = strtotime("+" . $time . "hours");
                    $timestamp = strtotime(date("Y-m-d H:i:s", $date));
                    $username = $text;
                    $expire = $timestamp;
                    $data_limit = $val * 1000000;
                    $config_test  = adduser($username, $expire, $data_limit);
                    $data_test = json_decode($config_test, true);
                    $output_config_link = $data_test['subscription_url'];
                    bot('sendmessage', [
                        'chat_id' => $chat_id,
                        'text' => "
    🔑 اشتراک شما با موفقیت ساخته شد.
    ⏳ زمان اشتراک تست $time ساعت
    🌐 حجم سرویس تست $val مگابایت
    
    لینک اشتراک شما   :
    ```
    $output_config_link
    ```
            ",
                        'reply_markup' => $key1,
                        'parse_mode' => "Markdown",
                        'reply_to_message_id' => $message_id,
                    ]);
                    file_put_contents("data/user/$from_id/Account_status", "true");
                    file_put_contents("data/user/$chat_id/step", "home");
                } else {
                    if ($text != "/start") {
                        bot('sendmessage', [
                            'chat_id' => $chat_id,
                            'text' => "نام کاربری در سیستم وجود دارد لطفا نام کاربری دیگری را انتخاب نمایید",
                            'reply_markup' => $back,
                            'parse_mode' => "Markdown",
                            'reply_to_message_id' => $message_id,
                        ]);
    
                        file_put_contents("data/user/$chat_id/step", "crateusertest");
                    } else {
                        file_put_contents("data/user/$chat_id/step", "home");
                    }
                }
            } else {
                if ($text != "🏠 بازگشت به منوی اصلی") {
                    bot('sendmessage', [
                        'chat_id' => $chat_id,
                        'text' => "
            ⛔️ نام کاربری معتبر نیست. ",
                        'reply_markup' => $back,
                        'parse_mode' => "Markdown",
                        'reply_to_message_id' => $message_id,
                    ]);
                }
                file_put_contents("data/user/$chat_id/step", "home");
            }
        }
    }
    #-------------admin----------------#
    if ($text == "panel" && $chat_id == $adminidnumbeer) {
                bot('sendmessage', [
                'chat_id' => $chat_id,
                'text' => "به پنل مدیریت خوش آمدید😊",
                'reply_markup' => $admin,
            ]); 
    }
            if ($text == "📣 تنظیم کانال جوین اجباری") {
                bot('sendmessage', [
                    'chat_id' => $chat_id,
                    'text' => "
            آیدی کانال خود را بدون @ ارسال کنید
            کانال فعلی : $channel
            ",
                    'reply_markup' => $backadmin,
                ]);
                file_put_contents("data/user/$chat_id/step", "setchannel");
            }
            if ($step == "setchannel" && $text != "🏠 بازگشت به منوی ادمین") {
                bot('sendmessage', [
                    'chat_id' => $chat_id,
                    'text' => "
           کانال با موفقیت ذخیره شد
            ",
                    'reply_markup' => $admin,
                ]);
                file_put_contents("data/channel", $text);
                file_put_contents("data/user/$chat_id/step", "home");
            }
            if ($text == "🏠 بازگشت به منوی ادمین") {
                bot('sendmessage', [
                    'chat_id' => $chat_id,
                    'text' => "به پنل ادمین خوش آمدید",
                    'reply_markup' => $admin,
                ]);
                file_put_contents("data/user/$chat_id/step", "home");
            }
            if ($text == "🔑 روشن / خاموش کردن قفل کانال") {
    
                if ($channelstatus == "false") {
                    bot('sendmessage', [
                        'chat_id' => $chat_id,
                        'text' => "قفل کانال روشن گردید",
                        'reply_markup' => $admin,
                    ]);
                    file_put_contents("data/user/$chat_id/step", "home");
                    file_put_contents("data/channelstatus", "true");
                } else {
                    bot('sendmessage', [
                        'chat_id' => $chat_id,
                        'text' => "قفل کانال خاموش گردید",
                        'reply_markup' => $admin,
                    ]);
                    file_put_contents("data/channelstatus", "false");
                }
            }
