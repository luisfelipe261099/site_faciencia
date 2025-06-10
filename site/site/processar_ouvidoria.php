<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ajuste o caminho se necessário

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturando os dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $assunto = $_POST['assunto'];
    $mensagem = $_POST['mensagem'];

    // Configuração do PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuração do servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';  // Servidor SMTP da Hostinger
        $mail->SMTPAuth = true;
        $mail->Username = 'desenvolvimento@lfmtecnologia.com';  // E-mail do remetente
        $mail->Password = 'T3cn0l0g1a@';  // Senha do e-mail
        $mail->SMTPSecure = 'tls';  // ou 'ssl' para porta 465
        $mail->Port = 587;  // Porta TLS (ou 465 para SSL)

        // Configuração do e-mail
        $mail->setFrom('desenvolvimento@lfmtecnologia.com', 'Site Faciencia');
        $mail->addAddress('ouvidoria@faciencia.edu.br');  // Destinatário
        $mail->addAddress('secretaria@faciencia.edu.br');  // Destinatário
        $mail->addAddress('apoio@faciencia.edu.br');  // Destinatário
        $mail->addAddress('apoio2@faciencia.edu.br');  // Destinatário
        $mail->addReplyTo($email, $nome);  // Para resposta direta ao usuário

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
                <p><strong>Nome:</strong> $nome</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Assunto:</strong> $assunto</p>
                <p><strong>Mensagem:</strong><br>$mensagem</p>
            </body>
            </html>
        ";

        $mail->send();
        echo "<script>alert('Mensagem enviada com sucesso!'); window.location.href = 'ouvidoria';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Erro ao enviar a mensagem: {$mail->ErrorInfo}'); window.location.href = 'ouvidoria';</script>";
    }
}
?>
