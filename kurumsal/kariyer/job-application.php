<?php
header('Content-Type: application/json; charset=utf-8');

$to = "bilgi@zeminteknik.com"; // Başvuru e-postası
$subject = "=?UTF-8?B?" . base64_encode("Yeni İş Başvurusu") . "?=";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST["name"]) ? strip_tags(trim($_POST["name"])) : '';
    $email = isset($_POST["email"]) ? filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL) : '';
    $phone = isset($_POST["phone"]) ? strip_tags(trim($_POST["phone"])) : '';
    $position = isset($_POST["position"]) ? strip_tags(trim($_POST["position"])) : '';
    $message = isset($_POST["message"]) ? strip_tags(trim($_POST["message"])) : '';

    if (!$name || !$email || !$position || !$message) {
        echo json_encode(["success" => false, "message" => "Lütfen zorunlu alanları doldurun."]);
        exit;
    }

    $body = "Ad Soyad: $name\nE-posta: $email\nTelefon: $phone\nPozisyon: $position\n\nKendini Tanıtım:\n$message";

    // Dosya ekleme işlemi
    if (isset($_FILES["cv"]) && $_FILES["cv"]["error"] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES["cv"]["tmp_name"];
        $file_name = $_FILES["cv"]["name"];
        $file_type = $_FILES["cv"]["type"];
        $file_size = $_FILES["cv"]["size"];
        // Güvenlik: Sadece PDF/DOC/DOCX ve 5MB altı dosya kabul et
        $allowed_types = ["application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"];
        if (!in_array($file_type, $allowed_types) || $file_size > 5*1024*1024) {
            echo json_encode(["success" => false, "message" => "Sadece PDF/DOC/DOCX ve 5MB altı dosya yükleyebilirsiniz."]);
            exit;
        }
        $handle = fopen($file_tmp, "rb");
        $content = fread($handle, $file_size);
        fclose($handle);
        $encoded_content = chunk_split(base64_encode($content));

        $boundary = md5("random"); // boundary tanımı
        $headers = "From: Zemin Teknik Web <bilgi@zeminteknik.com>\r\nReply-To: $email\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary = $boundary\r\n\r\n";

        $body_message = "--$boundary\r\n";
        $body_message .= "Content-Type: text/plain; charset=utf-8\r\n\r\n";
        $body_message .= $body . "\r\n";
        $body_message .= "--$boundary\r\n";
        $body_message .= "Content-Type: $file_type; name=\"$file_name\"\r\n";
        $body_message .= "Content-Disposition: attachment; filename=\"$file_name\"\r\n";
        $body_message .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body_message .= $encoded_content . "\r\n";
        $body_message .= "--$boundary--";

        $mail_sent = mail($to, $subject, $body_message, $headers);
    } else {
        // Dosya yoksa normal gönder
        $headers = "From: Zemin Teknik Web <bilgi@zeminteknik.com>\r\nReply-To: $email\r\nContent-Type: text/plain; charset=utf-8\r\n";
        $mail_sent = mail($to, $subject, $body, $headers);
    }

    if ($mail_sent) {
        echo json_encode(["success" => true, "message" => "Başvurunuz başarıyla gönderildi!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Bir hata oluştu, lütfen tekrar deneyin."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Geçersiz istek."]);
} 