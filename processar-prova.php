<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Iniciar sessão para manter dados do usuário
session_start();

// Verificar se a requisição é POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Receber dados da prova
    $dados = json_decode(file_get_contents('php://input'), true);

    if (isset($dados['respostas']) && isset($dados['questoes']) && isset($dados['candidato'])) {
        // Extrair dados
        $respostas = $dados['respostas'];
        $questoes = $dados['questoes'];
        $candidato = $dados['candidato'];

        // Calcular acertos
        $acertos = 0;
        $detalhes_questoes = [];

        for ($i = 0; $i < count($questoes); $i++) {
            $resposta_correta = isset($respostas[$i]) && $respostas[$i] == $questoes[$i]['resposta'];

            if ($resposta_correta) {
                $acertos++;
            }

            // Guardar detalhes para o e-mail
            $detalhes_questoes[] = [
                'pergunta' => $questoes[$i]['pergunta'],
                'resposta_candidato' => isset($respostas[$i]) ? $questoes[$i]['opcoes'][$respostas[$i]] : 'Não respondida',
                'resposta_correta' => $questoes[$i]['opcoes'][$questoes[$i]['resposta']],
                'acertou' => $resposta_correta
            ];
        }

        // Calcular porcentagem
        $porcentagem = ($acertos / count($questoes)) * 100;

        // Verificar aprovação (60% = aprovado)
        $aprovado = $porcentagem >= 60;

        // Registrar tentativa
        if (!isset($_SESSION['tentativas'])) {
            $_SESSION['tentativas'] = 0;
        }
        $_SESSION['tentativas']++;

        // Preparar resposta
        $resposta = [
            'sucesso' => true,
            'acertos' => $acertos,
            'total' => count($questoes),
            'porcentagem' => $porcentagem,
            'aprovado' => $aprovado,
            'tentativas' => $_SESSION['tentativas'],
            'tentativas_restantes' => max(0, 2 - $_SESSION['tentativas'])
        ];

        // Enviar e-mail com o resultado
        $mail = new PHPMailer(true);

        try {
            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'desenvolvimento@lfmtecnologia.com';
            $mail->Password   = 'T3cn0l0g1a@';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            // Remetente e destinatários
            $mail->setFrom('desenvolvimento@lfmtecnologia.com', 'Vestibular FaCiencia');
            $mail->addAddress('secretaria@faciencia.edu.br');

            // Se o candidato tiver e-mail, adicionar como cópia
            if (isset($candidato['email']) && !empty($candidato['email'])) {
                $mail->addCC($candidato['email']);
            }

            // Conteúdo do e-mail
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            if ($aprovado) {
                $mail->Subject = '🎉 PARABÉNS! Você foi aprovado no Vestibular FaCiencia 2025!';
                // Prioridade alta para e-mails de aprovação
                $mail->Priority = 1;
                $mail->AddCustomHeader("X-MSMail-Priority: High");
                $mail->AddCustomHeader("Importance: High");
            } else {
                $mail->Subject = 'Resultado do Vestibular FaCiencia 2025';
            }

            // Corpo do e-mail com layout bonito
            $mensagem = '
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                    }
                    .container {
                        max-width: 600px;
                        margin: 0 auto;
                        padding: 20px;
                        border: 1px solid #ddd;
                        border-radius: 5px;
                    }
                    .header {
                        background-color: ' . ($aprovado ? '#28a745' : '#9d359c') . ';
                        color: white;
                        padding: 15px;
                        text-align: center;
                        border-radius: 5px 5px 0 0;
                    }
                    .content {
                        padding: 20px;
                    }
                    .footer {
                        background-color: #f5f5f5;
                        padding: 15px;
                        text-align: center;
                        font-size: 12px;
                        border-radius: 0 0 5px 5px;
                    }
                    .result-box {
                        text-align: center;
                        padding: 20px;
                        margin: 20px 0;
                        border-radius: 5px;
                        background-color: ' . ($aprovado ? '#d4edda' : '#f8d7da') . ';
                        color: ' . ($aprovado ? '#155724' : '#721c24') . ';
                    }
                    .result-icon {
                        font-size: 48px;
                        margin-bottom: 10px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 20px 0;
                    }
                    table, th, td {
                        border: 1px solid #ddd;
                    }
                    th, td {
                        padding: 10px;
                        text-align: left;
                    }
                    th {
                        background-color: #f2f2f2;
                    }
                    .correct {
                        color: #28a745;
                        font-weight: bold;
                    }
                    .incorrect {
                        color: #dc3545;
                    }
                    .highlight {
                        background-color: #ffe925;
                        padding: 2px 5px;
                        border-radius: 3px;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header" style="background-color: ' . ($aprovado ? '#28a745' : '#9d359c') . '; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0;">
                        ' . ($aprovado ? '<h2 style="margin: 0;">PARABÉNS! VOCÊ FOI APROVADO!</h2><p style="margin: 10px 0 0 0;">Vestibular FaCiencia 2025</p>' : '<h2 style="margin: 0;">Resultado do Vestibular FaCiencia 2025</h2>') . '
                    </div>
                    <div class="content">
                        <div class="result-box">
                            <div class="result-icon">' . ($aprovado ? '&#128079;' : '&#128533;') . '</div>
                            <h3>' . ($aprovado ? 'Parabéns! Você foi aprovado!' : 'Você não atingiu a pontuação mínima.') . '</h3>
                            <p>Você acertou <strong>' . $acertos . '</strong> de <strong>10</strong> questões.</p>
                            <p>Porcentagem de acertos: <strong>' . number_format($porcentagem, 1) . '%</strong></p>
                        </div>

                        <h3>Dados do Candidato</h3>
                        <table>
                            <tr>
                                <th>Nome</th>
                                <td>' . htmlspecialchars($candidato['nome'] ?? 'Não informado') . '</td>
                            </tr>
                            <tr>
                                <th>CPF</th>
                                <td>' . htmlspecialchars($candidato['cpf'] ?? 'Não informado') . '</td>
                            </tr>
                            <tr>
                                <th>E-mail</th>
                                <td>' . htmlspecialchars($candidato['email'] ?? 'Não informado') . '</td>
                            </tr>
                            <tr>
                                <th>Tentativa</th>
                                <td>' . $_SESSION['tentativas'] . ' de 2</td>
                            </tr>
                        </table>

                        <h3>Detalhes da Prova</h3>
                        <table>
                            <tr>
                                <th>#</th>
                                <th>Questão</th>
                                <th>Sua Resposta</th>
                                <th>Resposta Correta</th>
                                <th>Resultado</th>
                            </tr>';

            // Adicionar detalhes de cada questão
            foreach ($detalhes_questoes as $index => $questao) {
                $mensagem .= '
                            <tr>
                                <td>' . ($index + 1) . '</td>
                                <td>' . htmlspecialchars($questao['pergunta']) . '</td>
                                <td>' . htmlspecialchars($questao['resposta_candidato']) . '</td>
                                <td>' . htmlspecialchars($questao['resposta_correta']) . '</td>
                                <td class="' . ($questao['acertou'] ? 'correct' : 'incorrect') . '">' . ($questao['acertou'] ? 'Correto' : 'Incorreto') . '</td>
                            </tr>';
            }

            $mensagem .= '
                        </table>

                        <p>Data da prova: ' . date('d/m/Y H:i:s') . '</p>';

            if ($aprovado) {
                $mensagem .= '
                        <div style="background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; padding: 20px; margin: 20px 0;">
                            <div style="text-align: center; margin-bottom: 20px;">
                                <img src="https://faciencia.edu.br/logo.png" alt="Logo FaCiencia" style="max-width: 200px; height: auto;">
                            </div>
                            <h3 style="color: #155724; margin-top: 0; text-align: center; font-size: 24px;">Bem-vindo à Família FaCiencia!</h3>
                            <div style="text-align: center; margin: 15px 0;">
                                <span style="font-size: 40px;">🎓 🎊 🎉</span>
                            </div>

                            <p>Estamos muito felizes em recebê-lo como novo aluno da nossa instituição! Sua jornada acadêmica está prestes a começar, e estamos ansiosos para fazer parte do seu crescimento profissional.</p>

                            <h4 style="color: #155724;">Próximos Passos:</h4>
                            <ol>
                                <li><strong>Contato da Secretaria:</strong> Nossa equipe entrará em contato em até 48 horas para orientar sobre os procedimentos de matrícula.</li>
                                <li><strong>Documentos Necessários:</strong> Prepare seus documentos pessoais (RG, CPF, comprovante de residência, histórico escolar e certificado de conclusão do ensino médio).</li>
                                <li><strong>Matrícula:</strong> Após o contato, você poderá realizar sua matrícula online ou presencialmente na nossa sede.</li>
                                <li><strong>Início das Aulas:</strong> As aulas para calouros do primeiro semestre de 2025 têm início no dia 17 de fevereiro de 2025.</li>
                            </ol>

                            <p>Caso tenha qualquer dúvida, não hesite em entrar em contato conosco:</p>
                            <ul>
                                <li>Telefone: (41) 9 9256-2500</li>
                                <li>E-mail: secretaria@faciencia.edu.br</li>
                                <li>WhatsApp: <a href="https://wa.link/vfs21y">Clique aqui</a></li>
                            </ul>

                            <div style="text-align: center; margin-top: 30px;">
                                <a href="https://faciencia.edu.br/matricula" style="background-color: #9d359c; color: white; padding: 12px 25px; text-decoration: none; border-radius: 50px; font-weight: bold; font-size: 16px; display: inline-block;">INICIAR MINHA MATRÍCULA</a>
                            </div>
                        </div>';
            } else if ($_SESSION['tentativas'] < 2) {
                $mensagem .= '
                        <p><strong>Observação:</strong> Você ainda tem mais uma tentativa disponível para realizar a prova.</p>
                        <p>Recomendamos que revise os conteúdos antes de tentar novamente. Você pode acessar materiais de estudo gratuitos em nosso site.</p>';
            } else {
                $mensagem .= '
                        <p><strong>Observação:</strong> Você já utilizou todas as suas tentativas. Entre em contato com a secretaria para mais informações sobre outras formas de ingresso.</p>
                        <p>Telefone: (41) 9 9256-2500 | E-mail: secretaria@faciencia.edu.br</p>';
            }

            $mensagem .= '
                    </div>
                    <div class="footer" style="background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 5px 5px;">
                        <p style="margin-bottom: 10px;">Este é um e-mail automático enviado pelo sistema de vestibular da FaCiencia.</p>
                        <div style="margin-bottom: 10px;">
                            <a href="https://faciencia.edu.br" style="color: #9d359c; text-decoration: none; margin: 0 10px;">Site</a> |
                            <a href="https://wa.link/vfs21y" style="color: #9d359c; text-decoration: none; margin: 0 10px;">WhatsApp</a> |
                            <a href="https://faciencia.edu.br/contato" style="color: #9d359c; text-decoration: none; margin: 0 10px;">Contato</a>
                        </div>
                        <p style="margin-top: 10px;">&copy; 2025 FaCiencia. Todos os direitos reservados.</p>
                        <p style="font-size: 10px; color: #777; margin-top: 10px;">Rua Visconde de Nacar, 1510 – 10° Andar – Conj. 1003 – Centro – Curitiba/PR</p>
                    </div>
                </div>
            </body>
            </html>';

            $mail->Body = $mensagem;
            $mail->AltBody = "Resultado do Vestibular FaCiencia 2025\n\n" .
                            ($aprovado ? "Parabéns! Você foi aprovado!" : "Você não atingiu a pontuação mínima.") . "\n" .
                            "Acertos: $acertos de " . count($questoes) . " questões.\n" .
                            "Porcentagem: " . number_format($porcentagem, 1) . "%";

            $mail->send();

        } catch (Exception $e) {
            // Registrar erro de e-mail, mas continuar com a resposta
            error_log("Erro ao enviar e-mail de resultado: {$mail->ErrorInfo}");
        }

        // Retornar resposta como JSON
        header('Content-Type: application/json');
        echo json_encode($resposta);
        exit;
    }
}

// Se chegou aqui, houve um erro
header('Content-Type: application/json');
echo json_encode(['sucesso' => false, 'mensagem' => 'Dados inválidos ou método incorreto']);
?>
