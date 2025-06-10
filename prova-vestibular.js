document.addEventListener('DOMContentLoaded', function() {
    // Carregar as questões do vestibular
    let bancoQuestoes = [];

    // Verificar se o banco de questões do vestibular está disponível
    if (typeof bancoQuestoesVestibular !== 'undefined') {
        bancoQuestoes = bancoQuestoesVestibular;
    }

    // Variáveis globais
    let questoes = [];
    let questaoAtual = 0;
    let respostas = Array(10).fill(null);
    let tempoRestante = 3600; // 1 hora em segundos
    let cronometroInterval;
    let tentativas = parseInt(localStorage.getItem('vestibular_tentativas') || '0');
    let bloqueadoAte = localStorage.getItem('vestibular_bloqueado_ate');
    let candidatoEmail = localStorage.getItem('vestibular_email') || '';
    let candidatoCPF = localStorage.getItem('vestibular_cpf') || '';
    let candidatoNome = localStorage.getItem('vestibular_nome') || '';

    // Armazenar IDs de questões já usadas para evitar repetição entre tentativas
    let questoesUsadas = JSON.parse(localStorage.getItem('questoes_usadas') || '[]');

    // Elementos DOM
    const btnIniciar = document.getElementById('iniciar-prova');
    const secaoInstrucoes = document.getElementById('instrucoes');
    const secaoProva = document.getElementById('prova');
    const secaoResultado = document.getElementById('resultado');
    const cronometroEl = document.getElementById('cronometro');
    const tempoEl = document.getElementById('tempo');
    const questoesContainer = document.getElementById('questoes-container');
    const btnAnterior = document.getElementById('questao-anterior');
    const btnProxima = document.getElementById('questao-proxima');
    const btnFinalizar = document.getElementById('finalizar-prova');
    const progressoEl = document.getElementById('progresso');
    const secaoAprovado = document.getElementById('aprovado');
    const secaoReprovado = document.getElementById('reprovado');
    const acertosAprovadoEl = document.getElementById('acertos-aprovado');
    const acertosReprovadoEl = document.getElementById('acertos-reprovado');
    const btnTentarNovamente = document.getElementById('tentar-novamente');

    // Banco de questões já carregado no início do script

    // Verificar se o candidato está bloqueado
    function verificarBloqueio() {
        if (bloqueadoAte) {
            const agora = new Date().getTime();
            const tempoDesbloqueio = parseInt(bloqueadoAte);

            if (agora < tempoDesbloqueio) {
                const horasRestantes = Math.ceil((tempoDesbloqueio - agora) / (1000 * 60 * 60));

                secaoInstrucoes.innerHTML = `
                    <div class="alert alert-danger text-center">
                        <h3><i class="fas fa-lock me-2"></i>Acesso Temporariamente Bloqueado</h3>
                        <p>Você esgotou suas tentativas de prova. Tente novamente em aproximadamente <strong>${horasRestantes} hora(s)</strong>.</p>
                        <p>Se precisar de ajuda, entre em contato com nossa secretaria.</p>
                        <a href="home" class="btn btn-primary mt-3">
                            <i class="fas fa-home me-2"></i>Voltar para Home
                        </a>
                    </div>
                `;
                return true;
            } else {
                // Desbloqueio automático
                localStorage.removeItem('vestibular_bloqueado_ate');
                localStorage.removeItem('vestibular_tentativas');
                tentativas = 0;
                bloqueadoAte = null;
            }
        }
        return false;
    }

    // Verificar bloqueio ao carregar a página
    if (verificarBloqueio()) {
        return; // Para a execução se estiver bloqueado
    }

    // Iniciar a prova
    btnIniciar.addEventListener('click', function() {
        if (!verificarBloqueio()) {
            iniciarProva();
        }
    });

    // Botão anterior
    btnAnterior.addEventListener('click', function() {
        if (questaoAtual > 0) {
            questaoAtual--;
            mostrarQuestao(questaoAtual);
            atualizarBotoes();
        }
    });

    // Botão próxima
    btnProxima.addEventListener('click', function() {
        if (questaoAtual < questoes.length - 1) {
            questaoAtual++;
            mostrarQuestao(questaoAtual);
            atualizarBotoes();
        }
    });

    // Botão finalizar
    btnFinalizar.addEventListener('click', function() {
        if (confirm('Tem certeza que deseja finalizar a prova?')) {
            finalizarProva();
        }
    });

    // Botão tentar novamente
    btnTentarNovamente.addEventListener('click', function() {
        secaoResultado.classList.add('d-none');
        secaoInstrucoes.classList.remove('d-none');
    });

    // Função para iniciar a prova
    function iniciarProva() {
        // Incrementar tentativas
        tentativas++;
        localStorage.setItem('vestibular_tentativas', tentativas.toString());

        // Selecionar 10 questões aleatórias do banco
        questoes = selecionarQuestoesAleatorias(bancoQuestoes, 10);

        // Resetar respostas
        respostas = Array(10).fill(null);

        // Resetar questão atual
        questaoAtual = 0;

        // Esconder instruções e mostrar prova
        secaoInstrucoes.classList.add('d-none');
        secaoProva.classList.remove('d-none');
        cronometroEl.classList.remove('d-none');
        secaoResultado.classList.add('d-none');

        // Gerar HTML das questões
        gerarQuestoes();

        // Mostrar primeira questão
        mostrarQuestao(0);

        // Atualizar botões
        atualizarBotoes();

        // Iniciar cronômetro
        iniciarCronometro();

        // Ativar medidas de segurança
        ativarMedidasSeguranca();
    }

    // Função para selecionar questões aleatórias evitando repetições
    function selecionarQuestoesAleatorias(banco, quantidade) {
        // Adicionar IDs às questões se não tiverem
        const bancoComIds = banco.map((questao, index) => {
            if (!questao.id) {
                questao.id = index;
            }
            return questao;
        });

        // Filtrar questões que não foram usadas recentemente
        let questoesDisponiveis = bancoComIds.filter(questao => !questoesUsadas.includes(questao.id));

        // Se não houver questões suficientes disponíveis, resetar o histórico parcialmente
        if (questoesDisponiveis.length < quantidade) {
            // Manter apenas as últimas questões usadas (metade do histórico)
            const metadeHistorico = Math.floor(questoesUsadas.length / 2);
            questoesUsadas = questoesUsadas.slice(-metadeHistorico);

            // Atualizar questões disponíveis
            questoesDisponiveis = bancoComIds.filter(questao => !questoesUsadas.includes(questao.id));
        }

        // Embaralhar as questões disponíveis
        questoesDisponiveis.sort(() => Math.random() - 0.5);

        // Selecionar a quantidade desejada
        const questoesSelecionadas = questoesDisponiveis.slice(0, quantidade);

        // Registrar as questões usadas
        const novasQuestoesUsadas = questoesSelecionadas.map(q => q.id);
        questoesUsadas = [...questoesUsadas, ...novasQuestoesUsadas];

        // Salvar no localStorage
        localStorage.setItem('questoes_usadas', JSON.stringify(questoesUsadas));

        return questoesSelecionadas;
    }

    // Função para gerar o HTML das questões
    function gerarQuestoes() {
        questoesContainer.innerHTML = '';

        questoes.forEach((questao, index) => {
            const divQuestao = document.createElement('div');
            divQuestao.className = 'questao';
            divQuestao.id = `questao-${index}`;

            const divNumero = document.createElement('div');
            divNumero.className = 'questao-numero';
            divNumero.textContent = `Questão ${index + 1} de ${questoes.length}`;

            const divTexto = document.createElement('div');
            divTexto.className = 'questao-texto';
            divTexto.textContent = questao.pergunta;

            const ulOpcoes = document.createElement('ul');
            ulOpcoes.className = 'opcoes';

            // Criar as opções
            questao.opcoes.forEach((opcao, opcaoIndex) => {
                const liOpcao = document.createElement('li');
                liOpcao.className = 'opcao';
                liOpcao.dataset.index = opcaoIndex;

                const spanLetra = document.createElement('span');
                spanLetra.className = 'opcao-letra';
                spanLetra.textContent = String.fromCharCode(65 + opcaoIndex); // A, B, C, D

                const spanTexto = document.createElement('span');
                spanTexto.className = 'opcao-texto';
                spanTexto.textContent = opcao;

                liOpcao.appendChild(spanLetra);
                liOpcao.appendChild(spanTexto);

                // Adicionar evento de clique
                liOpcao.addEventListener('click', function() {
                    selecionarOpcao(index, opcaoIndex);
                });

                ulOpcoes.appendChild(liOpcao);
            });

            divQuestao.appendChild(divNumero);
            divQuestao.appendChild(divTexto);
            divQuestao.appendChild(ulOpcoes);

            questoesContainer.appendChild(divQuestao);
        });
    }

    // Função para mostrar uma questão específica
    function mostrarQuestao(index) {
        // Esconder todas as questões
        document.querySelectorAll('.questao').forEach(questao => {
            questao.classList.remove('ativa');
        });

        // Mostrar a questão atual
        document.getElementById(`questao-${index}`).classList.add('ativa');

        // Atualizar progresso
        const progresso = Math.round((index + 1) / questoes.length * 100);
        progressoEl.style.width = `${progresso}%`;
        progressoEl.textContent = `${progresso}%`;
        progressoEl.setAttribute('aria-valuenow', progresso);

        // Verificar se todas as questões foram respondidas
        const todasRespondidas = respostas.every(resposta => resposta !== null);
        if (todasRespondidas) {
            btnFinalizar.classList.remove('d-none');
        } else {
            btnFinalizar.classList.add('d-none');
        }
    }

    // Função para selecionar uma opção
    function selecionarOpcao(questaoIndex, opcaoIndex) {
        // Atualizar array de respostas
        respostas[questaoIndex] = opcaoIndex;

        // Atualizar visual
        const questaoEl = document.getElementById(`questao-${questaoIndex}`);
        const opcoes = questaoEl.querySelectorAll('.opcao');

        opcoes.forEach(opcao => {
            opcao.classList.remove('selecionada');
        });

        opcoes[opcaoIndex].classList.add('selecionada');

        // Verificar se todas as questões foram respondidas
        const todasRespondidas = respostas.every(resposta => resposta !== null);
        if (todasRespondidas) {
            btnFinalizar.classList.remove('d-none');
        }
    }

    // Função para atualizar os botões de navegação
    function atualizarBotoes() {
        btnAnterior.disabled = questaoAtual === 0;
        btnProxima.disabled = questaoAtual === questoes.length - 1;
    }

    // Função para iniciar o cronômetro
    function iniciarCronometro() {
        // Resetar tempo
        tempoRestante = 3600; // 1 hora

        // Atualizar display inicial
        atualizarTempo();

        // Iniciar intervalo
        clearInterval(cronometroInterval);
        cronometroInterval = setInterval(function() {
            tempoRestante--;

            if (tempoRestante <= 0) {
                clearInterval(cronometroInterval);
                finalizarProva();
            } else {
                atualizarTempo();
            }
        }, 1000);
    }

    // Função para atualizar o display do tempo
    function atualizarTempo() {
        const horas = Math.floor(tempoRestante / 3600);
        const minutos = Math.floor((tempoRestante % 3600) / 60);
        const segundos = tempoRestante % 60;

        tempoEl.textContent = `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;

        // Mudar cor quando estiver acabando o tempo
        if (tempoRestante < 300) { // Menos de 5 minutos
            tempoEl.classList.add('text-danger');
        } else {
            tempoEl.classList.remove('text-danger');
        }
    }

    // Função para finalizar a prova
    function finalizarProva() {
        // Parar cronômetro
        clearInterval(cronometroInterval);

        // Calcular pontuação
        const acertos = calcularAcertos();

        // Esconder prova e cronômetro
        secaoProva.classList.add('d-none');
        cronometroEl.classList.add('d-none');

        // Mostrar indicador de carregamento
        secaoResultado.innerHTML = `
            <div class="text-center p-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2">Processando seu resultado...</p>
            </div>
        `;
        secaoResultado.classList.remove('d-none');

        // Obter dados do candidato do localStorage (salvos durante a inscrição)
        const candidato = {
            nome: localStorage.getItem('vestibular_nome') || 'Candidato',
            cpf: localStorage.getItem('vestibular_cpf') || '',
            email: localStorage.getItem('vestibular_email') || ''
        };

        // Log para debug
        console.log('Dados do candidato:', candidato);

        // Se não houver e-mail, solicitar ao usuário
        if (!candidato.email) {
            const emailInformado = prompt('Por favor, informe seu e-mail para receber o resultado da prova:');
            if (emailInformado && emailInformado.includes('@')) {
                candidato.email = emailInformado;
                localStorage.setItem('vestibular_email', emailInformado);
            }
        }

        // Preparar dados para enviar ao servidor
        const dadosProva = {
            candidato: candidato,
            respostas: respostas,
            questoes: questoes.map(q => ({
                pergunta: q.pergunta,
                opcoes: q.opcoes,
                resposta: q.resposta
            })),
            tentativas: tentativas,
            acertos: acertos
        };

        // Enviar dados para o servidor
        fetch('processar-prova.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dadosProva)
        })
        .then(response => response.json())
        .then(data => {
            // Restaurar HTML original do resultado
            secaoResultado.innerHTML = `
                <div id="aprovado" class="resultado-container d-none">
                    <div class="animate__animated animate__bounceIn">
                        <i class="fas fa-trophy resultado-icone text-success"></i>
                        <h2>Parabéns! Você foi aprovado!</h2>
                        <p class="resultado-texto">Você acertou <span id="acertos-aprovado">0</span> de 10 questões.</p>
                        <p>Nossa equipe entrará em contato em breve para os próximos passos da matrícula.</p>
                        <a href="home" class="btn btn-primary mt-3">
                            <i class="fas fa-home me-2"></i>Voltar para Home
                        </a>
                    </div>
                </div>

                <div id="reprovado" class="resultado-container d-none">
                    <div class="animate__animated animate__fadeIn">
                        <i class="fas fa-exclamation-circle resultado-icone text-danger"></i>
                        <h2>Você não atingiu a pontuação mínima</h2>
                        <p class="resultado-texto">Você acertou <span id="acertos-reprovado">0</span> de 10 questões.</p>
                        <p>Não desanime! Você ainda tem mais uma tentativa.</p>
                        <button id="tentar-novamente" class="btn btn-primary mt-3">
                            <i class="fas fa-redo me-2"></i>Tentar Novamente
                        </button>
                    </div>
                </div>
            `;

            // Atualizar referências aos elementos
            const secaoAprovado = document.getElementById('aprovado');
            const secaoReprovado = document.getElementById('reprovado');
            const acertosAprovadoEl = document.getElementById('acertos-aprovado');
            const acertosReprovadoEl = document.getElementById('acertos-reprovado');
            const btnTentarNovamente = document.getElementById('tentar-novamente');

            // Verificar se foi aprovado (60% = 6 acertos em 10 questões)
            if (acertos >= 6) {
                secaoAprovado.classList.remove('d-none');
                secaoReprovado.classList.add('d-none');
                acertosAprovadoEl.textContent = acertos;

                // Limpar dados de tentativas após aprovação
                localStorage.removeItem('vestibular_tentativas');
                localStorage.removeItem('vestibular_bloqueado_ate');
            } else {
                secaoAprovado.classList.add('d-none');
                secaoReprovado.classList.remove('d-none');
                acertosReprovadoEl.textContent = acertos;

                // Verificar se já usou as duas tentativas
                if (tentativas >= 2) {
                    // Bloquear por 24 horas
                    const agora = new Date().getTime();
                    const bloqueioAte = agora + (24 * 60 * 60 * 1000); // 24 horas
                    localStorage.setItem('vestibular_bloqueado_ate', bloqueioAte.toString());

                    // Atualizar interface para tentativas esgotadas
                    const mensagemReprovado = secaoReprovado.querySelector('p:nth-of-type(2)');
                    mensagemReprovado.textContent = 'Você esgotou suas tentativas. Tente novamente em 24 horas.';

                    btnTentarNovamente.disabled = true;
                    btnTentarNovamente.textContent = 'Tentativas esgotadas';
                    btnTentarNovamente.classList.add('btn-secondary');
                    btnTentarNovamente.classList.remove('btn-primary');

                    // Enviar email para secretaria sobre tentativas esgotadas
                    enviarEmailSecretaria(candidato, acertos, tentativas);
                } else {
                    // Ainda tem tentativas
                    const mensagemReprovado = secaoReprovado.querySelector('p:nth-of-type(2)');
                    mensagemReprovado.textContent = 'Não desanime! Você ainda tem mais uma tentativa.';

                    // Adicionar evento de clique para tentar novamente
                    btnTentarNovamente.addEventListener('click', function() {
                        secaoResultado.classList.add('d-none');
                        secaoInstrucoes.classList.remove('d-none');
                    });
                }
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            secaoResultado.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">Erro ao processar resultado!</h4>
                    <p>Ocorreu um erro ao processar seu resultado. Por favor, tente novamente ou entre em contato com a secretaria.</p>
                    <hr>
                    <button class="btn btn-primary" onclick="location.reload()">Tentar Novamente</button>
                </div>
            `;
        });
    }

    // Função para calcular acertos
    function calcularAcertos() {
        let acertos = 0;

        for (let i = 0; i < questoes.length; i++) {
            if (respostas[i] === questoes[i].resposta) {
                acertos++;
            }
        }

        return acertos;
    }

    // Função para enviar email para secretaria sobre tentativas esgotadas
    function enviarEmailSecretaria(candidato, acertos, tentativas) {
        const dadosEmail = {
            tipo: 'tentativas_esgotadas',
            candidato: {
                nome: candidato.nome,
                cpf: candidato.cpf,
                email: candidato.email
            },
            detalhes: {
                acertos: acertos,
                tentativas: tentativas,
                data: new Date().toLocaleString('pt-BR'),
                ip: 'N/A' // Será preenchido pelo servidor
            }
        };

        fetch('enviar-email-secretaria.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dadosEmail)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Email enviado para secretaria:', data);
        })
        .catch(error => {
            console.error('Erro ao enviar email para secretaria:', error);
        });
    }

    // Função para ativar medidas de segurança
    function ativarMedidasSeguranca() {
        // Sistema centralizado de alertas para evitar repetições
        const alertasRecentes = {};
        const tempoEntreMensagens = 30000; // 30 segundos entre alertas do mesmo tipo

        function mostrarAlertaControlado(tipo, mensagem) {
            const agora = new Date().getTime();

            // Verificar se já mostrou este alerta recentemente
            if (alertasRecentes[tipo] && (agora - alertasRecentes[tipo] < tempoEntreMensagens)) {
                console.log(`Alerta "${tipo}" suprimido - muito recente`);
                return false;
            }

            // Registrar o momento do alerta
            alertasRecentes[tipo] = agora;

            // Mostrar o alerta
            alert(mensagem);
            return true;
        }

        // 1. Impedir cópia e cola
        document.addEventListener('copy', function(e) {
            e.preventDefault();
            mostrarAlertaControlado('copia', 'Não é permitido copiar o conteúdo da prova!');
        });

        document.addEventListener('paste', function(e) {
            e.preventDefault();
            mostrarAlertaControlado('cola', 'Não é permitido colar conteúdo durante a prova!');
        });

        // 2. Detectar troca de página/aba
        let ultimoEstadoVisibilidade = 'visible';
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden' && ultimoEstadoVisibilidade === 'visible') {
                mostrarAlertaControlado('visibilidade', 'Atenção! Você saiu da página da prova. Isso pode ser considerado uma tentativa de fraude.');
            }
            ultimoEstadoVisibilidade = document.visibilityState;
        });

        // 3. Impedir clique com botão direito
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            mostrarAlertaControlado('contextmenu', 'O menu de contexto está desabilitado durante a prova!');
        });

        // 4. Detectar teclas de atalho comuns
        document.addEventListener('keydown', function(e) {
            // Ctrl+C, Ctrl+V, Ctrl+P, F12, Alt+Tab, etc.
            if ((e.ctrlKey && (e.key === 'c' || e.key === 'v' || e.key === 'p')) ||
                e.key === 'F12' ||
                e.key === 'PrintScreen') {
                e.preventDefault();
                mostrarAlertaControlado('atalho', 'Atalhos de teclado estão desabilitados durante a prova!');
            }
        });

        // 5. Impedir arrastar e soltar
        document.addEventListener('dragstart', function(e) {
            e.preventDefault();
        });

        // 6. Verificar se a janela perdeu o foco (melhorado)
        let focoInicial = true;
        let janelaTeveFoco = false;
        let timeoutFoco;

        // Marcar que a janela teve foco inicial
        window.addEventListener('focus', function() {
            janelaTeveFoco = true;
            focoInicial = false;
            clearTimeout(timeoutFoco);
        });

        window.addEventListener('blur', function() {
            // Só alertar se a janela já teve foco e não é o carregamento inicial
            if (janelaTeveFoco && !focoInicial) {
                // Aguardar um pouco para evitar falsos positivos
                timeoutFoco = setTimeout(function() {
                    mostrarAlertaControlado('foco', 'Atenção! A janela da prova perdeu o foco. Isso pode ser considerado uma tentativa de fraude.');
                }, 1000); // 1 segundo de delay
            }
        });

        // 7. Registrar tentativas de fraude
        let tentativasFraude = 0;
        const maxTentativas = 5;

        // Sobrescrever a função mostrarAlertaControlado para contar tentativas
        const mostrarAlertaOriginal = mostrarAlertaControlado;
        mostrarAlertaControlado = function(tipo, mensagem) {
            if (mostrarAlertaOriginal(tipo, mensagem)) {
                tentativasFraude++;
                console.log(`Tentativa de fraude #${tentativasFraude}: ${tipo}`);

                // Se exceder o limite, avisar
                if (tentativasFraude >= maxTentativas) {
                    alert('AVISO IMPORTANTE: Muitas tentativas suspeitas detectadas. Isso será reportado.');
                    // Aqui poderia enviar um registro para o servidor
                }
            }
        };
    }
});
