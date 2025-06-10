<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ajuste o caminho conforme a localização do seu autoload

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1) HONEYPOT: se este campo oculto vier preenchido, é SPAM
    if (!empty($_POST['campo_oculto'])) {
        die("<script>alert('SPAM detectado. Operação interrompida.'); window.history.back();</script>");
    }

    // 2) Validar reCAPTCHA
    $recaptchaSecret  = '6Lfmo7IqAAAAAC6RDoDAJKSiOO9-U_ZWUaVL6YIW'; // Chave secreta
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    $remoteIP         = $_SERVER['REMOTE_ADDR'];

    // Verifica se o token foi recebido
    if (empty($recaptchaResponse)) {
        die("<script>alert('Validação reCAPTCHA ausente. Tente novamente.'); window.history.back();</script>");
    }

    $recaptchaURL   = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptchaCheck = file_get_contents("{$recaptchaURL}?secret={$recaptchaSecret}&response={$recaptchaResponse}&remoteip={$remoteIP}");
    $recaptchaResult = json_decode($recaptchaCheck, true);

    if (empty($recaptchaResult['success']) || !$recaptchaResult['success']) {
        die("<script>alert('Falha na validação do reCAPTCHA. Tente novamente.'); window.history.back();</script>");
    }

    // 3) Capturando dados do formulário (com sanitização básica)
    $nome     = htmlspecialchars($_POST['nome'] ?? '');
    $email    = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $telefone = htmlspecialchars($_POST['telefone'] ?? '');
    $assunto  = htmlspecialchars($_POST['assunto'] ?? '');
    $mensagem = htmlspecialchars($_POST['mensagem'] ?? '');

    // 4) Envio de e-mail via PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Configuração do servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com'; // Ajuste conforme seu provedor
        $mail->SMTPAuth   = true;
        $mail->Username   = 'desenvolvimento@lfmtecnologia.com'; // Seu usuário SMTP
        $mail->Password   = 'T3cn0l0g1a@';                       // Sua senha
        $mail->SMTPSecure = 'tls';                               // ou 'ssl' (verifique porta)
        $mail->Port       = 587;                                 // Porta TLS (ou 465 para SSL)

        // Endereços de envio
        $mail->setFrom('desenvolvimento@lfmtecnologia.com', 'Site Faciencia');
        $mail->addAddress('secretaria@faciencia.edu.br');
        $mail->addAddress('apoio@faciencia.edu.br');
        $mail->addAddress('apoio2@faciencia.edu.br');
        $mail->addAddress('barbara.batista@faciencia.edu.br');

        // Resposta vai direto para quem enviou
        $mail->addReplyTo($email, $nome);

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = "Nova mensagem de contato: {$assunto}";
        $mail->Body = "
            <html>
            <head>
                <title>Mensagem de Contato</title>
            </head>
            <body>
                <h2>Nova mensagem de contato</h2>
                <p><strong>Nome:</strong> {$nome}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Telefone:</strong> {$telefone}</p>
                <p><strong>Assunto:</strong> {$assunto}</p>
                <p><strong>Mensagem:</strong><br>{$mensagem}</p>
            </body>
            </html>
        ";

        $mail->send();
        echo "<script>alert('Mensagem enviada com sucesso!'); window.location.href = 'contato';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Erro ao enviar a mensagem: {$mail->ErrorInfo}'); window.location.href = 'contato';</script>";
    }
}
