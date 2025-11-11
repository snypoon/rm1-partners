<?php
// PHP скрипт для отправки email через PHPMailer

// Проверка наличия vendor/autoload.php (основной способ через Composer)
$vendor_autoload = __DIR__ . '/vendor/autoload.php';
$phpmailer_path = __DIR__ . '/PHPMailer-6.8.1/src/';

if (file_exists($vendor_autoload)) {
    // Используем Composer autoload (как на сервере)
    require_once $vendor_autoload;
} elseif (file_exists($phpmailer_path . 'Exception.php') && 
          file_exists($phpmailer_path . 'PHPMailer.php') && 
          file_exists($phpmailer_path . 'SMTP.php')) {
    // Резервный вариант: прямое подключение файлов
    require_once $phpmailer_path . 'Exception.php';
    require_once $phpmailer_path . 'PHPMailer.php';
    require_once $phpmailer_path . 'SMTP.php';
} else {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false, 
        'message' => 'Ошибка: PHPMailer не найден (проверьте vendor/autoload.php или PHPMailer-6.8.1/src/)',
        'error' => 'PHPMailer files missing'
    ]);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Включаем отображение ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 0); // Отключаем вывод в HTML, но оставляем логирование
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');

// Проверка метода запроса
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен']);
    exit;
}

// Получение данных формы
$fio = isset($_POST['fio']) ? trim($_POST['fio']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$experience = isset($_POST['experience']) ? trim($_POST['experience']) : '';
$industry = isset($_POST['industry']) ? trim($_POST['industry']) : '';
$city = isset($_POST['city']) ? trim($_POST['city']) : '';

// Валидация
if (empty($fio) || empty($phone) || empty($email) || empty($experience) || empty($industry) || empty($city)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Все поля обязательны для заполнения']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Некорректный email адрес']);
    exit;
}

// Отправка через PHPMailer
try {
    // Создаем экземпляр PHPMailer
    $mail = new PHPMailer(true);
    
    // Включаем режим отладки (для разработки)
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Показывать детальные сообщения SMTP
    $mail->Debugoutput = function($str, $level) {
        error_log("PHPMailer SMTP Debug ($level): $str");
    };
    
    // Настройки SMTP для Yandex
    $mail->isSMTP();
    $mail->Host = 'smtp.yandex.ru';
    $mail->SMTPAuth = true;
    $mail->Username = 'FasrWebPro@yandex.ru';
    $mail->Password = 'aatbfyjfwsmnvsjk';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Отправитель
    $mail->setFrom('FasrWebPro@yandex.ru', 'Рыбная мануфактура');
    $mail->addReplyTo($email, $fio);
    
    // Получатель
    $mail->addAddress('g.skrebtsov@rm-1.ru');

    // Содержимое письма
    $mail->isHTML(true);
    $mail->Subject = "Новая заявка с сайта Рыбная мануфактура";
    
    $mail->Body = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Новая заявка</title>
    </head>
    <body>
        <h2>Новая заявка с сайта</h2>
        <table border='1' cellpadding='10' cellspacing='0'>
            <tr>
                <td><strong>ФИО:</strong></td>
                <td>" . htmlspecialchars($fio) . "</td>
            </tr>
            <tr>
                <td><strong>Телефон:</strong></td>
                <td>" . htmlspecialchars($phone) . "</td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td>" . htmlspecialchars($email) . "</td>
            </tr>
            <tr>
                <td><strong>Предпринимательский опыт:</strong></td>
                <td>" . nl2br(htmlspecialchars($experience)) . "</td>
            </tr>
            <tr>
                <td><strong>Опыт в индустрии:</strong></td>
                <td>" . nl2br(htmlspecialchars($industry)) . "</td>
            </tr>
            <tr>
                <td><strong>Город для открытия:</strong></td>
                <td>" . htmlspecialchars($city) . "</td>
            </tr>
        </table>
    </body>
    </html>";
    
    // Отправка
    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Заявка успешно отправлена!']);
    
} catch (Exception $e) {
    // Детальное логирование ошибки
    $errorDetails = [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ];
    
    error_log("PHPMailer ошибка: " . print_r($errorDetails, true));
    
    // Проверяем, есть ли информация об ошибке SMTP
    $smtpError = '';
    if (isset($mail) && !empty($mail->ErrorInfo)) {
        $smtpError = $mail->ErrorInfo;
        error_log("PHPMailer SMTP ErrorInfo: " . $smtpError);
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Ошибка при отправке письма',
        'error' => $e->getMessage(),
        'smtp_error' => $smtpError,
        'details' => [
            'code' => $e->getCode(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ]);
}
?>

