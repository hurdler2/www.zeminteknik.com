<?php
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to = "bilgi@zeminteknik.com"; // Buraya kendi e-posta adresinizi yazın
    $subject = "=?UTF-8?B?" . base64_encode("İletişim Formu Mesajı") . "?=";
    $name = isset($_POST["name"]) ? strip_tags(trim($_POST["name"])) : '';
    $company = isset($_POST["company"]) ? strip_tags(trim($_POST["company"])) : '';
    $email = isset($_POST["email"]) ? filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL) : '';
    $phone = isset($_POST["phone"]) ? strip_tags(trim($_POST["phone"])) : '';
    $subjectForm = isset($_POST["subject"]) ? strip_tags(trim($_POST["subject"])) : '';
    $message = isset($_POST["message"]) ? strip_tags(trim($_POST["message"])) : '';

    if (!$name || !$email || !$phone || !$subjectForm || !$message) {
        echo json_encode(["success" => false, "message" => "Lütfen tüm zorunlu alanları doldurun."]);
        exit;
    }

    // Paylaşımlı sunucular alan adı dışından From başlığını reddedebilir: gönderici alan adından, yanıt kullanıcıya.
    $headers = "From: Zemin Teknik Web <bilgi@zeminteknik.com>\r\nReply-To: $email\r\nContent-Type: text/plain; charset=utf-8\r\n";
    $body = "Ad Soyad: $name\nFirma: $company\nE-posta: $email\nTelefon: $phone\nKonu: $subjectForm\nMesaj:\n$message";

    if (mail($to, $subject, $body, $headers)) {
        echo json_encode(["success" => true, "message" => "Mesajınız başarıyla gönderildi! En kısa sürede size dönüş yapacağız."]);
    } else {
        echo json_encode(["success" => false, "message" => "Bir hata oluştu, lütfen tekrar deneyin."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Geçersiz istek."]);
} 