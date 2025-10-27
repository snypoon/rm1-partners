<?php
// PHP скрипт для отправки email
// Для работы нужен сервер с поддержкой PHP и настроенной функцией mail()

header('Content-Type: application/json; charset=utf-8');

// Проверка метода запроса
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен']);
    exit;
}

// Получение данных формы
$fio = isset($_POST['fio']) ? trim($_POST['fio']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$experience = isset($_POST['experience']) ? trim($_POST['experience']) : '';
$industry = isset($_POST['industry']) ? trim($_POST['industry']) : '';
$city = isset($_POST['city']) ? trim($_POST['city']) : '';

// Валидация
if (empty($fio) || empty($email) || empty($experience) || empty($industry) || empty($city)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Все поля обязательны для заполнения']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Некорректный email адрес']);
    exit;
}

// Email получателя (замените на ваш email)
$to = "g.skrebtsov@rm-1.ru";

// Тема письма
$subject = "Новая заявка с сайта Рыбная мануфактура";

// Формирование тела письма
$message = "
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
</html>
";

// Заголовки для HTML письма
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: noreply@rybnaya-manufactura.ru" . "\r\n";
$headers .= "From: " . $email . "\r\n";
$headers .= "Reply-To: " . $email . "\r\n";

// Отправка письма
if (mail($to, $subject, $message, $headers)) {
    echo json_encode(['success' => true, 'message' => 'Заявка успешно отправлена!']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка при отправке письма']);
}
?>

