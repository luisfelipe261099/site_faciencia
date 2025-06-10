<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ajuste o caminho caso não esteja usando Composer

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Definindo as variáveis do formulário
    $nome_empresa = $_POST['nome'];
    $email_empresa = $_POST['email'];
    $telefone = $_POST['telefone'];
    $solicitante = $_POST['solicitante'];
    $observacao = $_POST['observacao'];
    $link = $_POST['link'];
    
    // Configuração do PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuração do servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';  // Servidor SMTP da Hostinger
        $mail->SMTPAuth = true;
        $mail->Username = 'desenvolvimento@lfmtecnologia.com';  // E-mail do remetente
        $mail->Password = 'T3cn0l0g1a@';  // Senha do e-mail
        $mail->SMTPSecure = 'tls';  // Segurança (ou 'ssl' para porta 465)
        $mail->Port = 587;  // Porta TLS (ou 465 para SSL)

        // Configuração do e-mail
        $mail->setFrom('desenvolvimento@lfmtecnologia.com', 'Site Faciencia');
        $mail->addAddress('secretaria@faciencia.edu.br');
        $mail->addAddress('apoio2@faciencia.edu.br');  
        $mail->addAddress('apoio@faciencia.edu.br');  // Destinatário     apoio@faciencia.edu.br   apoio2@faciencia.edu.br
        $mail->addReplyTo($email_empresa, $nome_empresa);  // Permitir resposta para o solicitante

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = "Solicitacao de Polo: $nome_empresa";
        $mail->Body = "
            <html>
            <head>
                <title>Solicitacao de Polo</title>
            </head>
            <body>
                <h2>Solicitação de Polo</h2>
                <p><strong>Nome da Empresa:</strong> $nome_empresa</p>
                <p><strong>Email:</strong> $email_empresa</p>
                <p><strong>Telefone:</strong> $telefone</p>
                <p><strong>Nome do Solicitante:</strong> $solicitante</p>
                 <p><strong>Link:</strong> $link</p>
                <p><strong>Observação:</strong><br>$observacao</p>
            </body>
            </html>
        ";

        $mail->send();
        echo "<script>alert('Solicitação enviada com sucesso!'); window.location.href = 'solicitacao.html';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Erro ao enviar a solicitação: {$mail->ErrorInfo}'); window.location.href = 'solicitacao.html';</script>";
    }
}
?>
