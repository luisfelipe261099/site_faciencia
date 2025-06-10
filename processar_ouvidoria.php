<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ajuste o caminho se necessário

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1) Verifica se é SPAM via "honeypot"
    // Se o campo "telefone" (honeypot) vier preenchido, é quase certeza de SPAM.
    if (!empty($_POST['telefone'])) {
        die("<script>alert('SPAM detectado. Operação interrompida.'); window.history.back();</script>");
    }

    // 2) Validar reCAPTCHA no servidor
    $recaptchaSecret  = '6LfUo7IqAAAAAFUIUhcAbSWPVTUbGxxx_9mLeAO3';
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    $remoteIP         = $_SERVER['REMOTE_ADDR'];

    if (empty($recaptchaResponse)) {
        die("<script>alert('Validação de reCAPTCHA ausente. Tente novamente.'); window.history.back();</script>");
    }

    $recaptchaURL = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptchaData = file_get_contents("$recaptchaURL?secret=$recaptchaSecret&response=$recaptchaResponse&remoteip=$remoteIP");
    $recaptchaResult = json_decode($recaptchaData, true);

    // Se a verificação falhar, interrompe
    if (!$recaptchaResult['success']) {
        die("<script>alert('Falha na validação do reCAPTCHA. Tente novamente.'); window.history.back();</script>");
    }

    // 3) Capturando os dados do formulário
    $nome     = $_POST['nome']     ?? '';
    $email    = $_POST['email']    ?? '';
    $assunto  = $_POST['assunto']  ?? '';
    $mensagem = $_POST['mensagem'] ?? '';

    // 4) Configuração do PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuração do servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';  // Servidor SMTP da Hostinger (ajuste conforme seu provedor)
        $mail->SMTPAuth   = true;
        $mail->Username   = 'desenvolvimento@lfmtecnologia.com';  // E-mail do remetente
        $mail->Password   = 'T3cn0l0g1a@';  // Senha do e-mail
        $mail->SMTPSecure = 'tls';  // ou 'ssl' para porta 465
        $mail->Port       = 587;    // Porta TLS (ou 465 para SSL)

        // Configuração do e-mail de saída
        $mail->setFrom('desenvolvimento@lfmtecnologia.com', 'Site FaCiencia');
        // Destinatários — adicione quantos forem necessários
        $mail->addAddress('ouvidoria@faciencia.edu.br');
        $mail->addAddress('secretaria@faciencia.edu.br');
        $mail->addAddress('apoio@faciencia.edu.br');
        $mail->addAddress('apoio2@faciencia.edu.br');
        $mail->addAddress('barbara.batista@faciencia.edu.br');
        // Habilita resposta direta ao usuário
        $mail->addReplyTo($email, $nome);

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = "Mensagem de Ouvidoria: $assunto";
        $mail->Body = "
            <html>
            <head>
                <title>Mensagem de Ouvidoria</title>
            </head>
            <body>
                <h2>Mensagem da Ouvidoria</h2>
                <p><strong>Nome:</strong> {$nome}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Assunto:</strong> {$assunto}</p>
                <p><strong>Mensagem:</strong><br>{$mensagem}</p>
            </body>
            </html>
        ";

        // 5) Envio do e-mail
        $mail->send();

        // 6) Retorno de sucesso ao usuário
        echo "<script>alert('Mensagem enviada com sucesso!'); window.location.href = 'ouvidoria';</script>";

    } catch (Exception $e) {
        // Em caso de erro no envio do e-mail
        echo "<script>alert('Erro ao enviar a mensagem: {$mail->ErrorInfo}'); window.location.href = 'ouvidoria';</script>";
    }
}
?>
