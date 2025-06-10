<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Use o caminho correto para o autoload do PHPMailer

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Definindo as variáveis do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $assunto = $_POST['assunto'];
    $mensagem = $_POST['mensagem'];

    // Configuração do PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuração do servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';  // Servidor SMTP da Hostinger
        $mail->SMTPAuth = true;
        $mail->Username = 'desenvolvimento@lfmtecnologia.com';  // E-mail para autenticação
        $mail->Password = 'T3cn0l0g1a@';  // Senha do e-mail
        $mail->SMTPSecure = 'tls';  // ou 'ssl' dependendo do provedor
        $mail->Port = 587;  // Porta para TLS (ou 465 para SSL, dependendo da configuração)

        // Configuração do e-mail
        $mail->setFrom('desenvolvimento@lfmtecnologia.com', 'Site Faciencia');
        $mail->addAddress('secretaria@faciencia.edu.br');  // Destinatário
        $mail->addAddress('apoio@faciencia.edu.br');  // Destinatário
        $mail->addAddress('apoio2@faciencia.edu.br');  // Destinatário
      
        $mail->addReplyTo($email, $nome);  // Para respostas

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = "Nova mensagem de contato: $assunto";
        $mail->Body = "
            <html>
            <head>
                <title>Mensagem de Contato</title>
            </head>
            <body>
                <h2>Nova mensagem de contato</h2>
                <p><strong>Nome:</strong> $nome</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Telefone:</strong> $telefone</p>
                <p><strong>Assunto:</strong> $assunto</p>
                <p><strong>Mensagem:</strong><br>$mensagem</p>
            </body>
            </html>
        ";

        $mail->send();
        echo "<script>alert('Mensagem enviada com sucesso!'); window.location.href = 'contato';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Erro ao enviar a mensagem: {$mail->ErrorInfo}'); window.location.href = 'contato';</script>";
    }
}
?>
