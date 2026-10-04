# Resultado da auditoria pós-build — 2026-10-03

## Verificações executadas

- `php -l` em todos os PHPs do pacote: **0 erros de sintaxe**.
- `bash -n` em todos os scripts shell: **0 erros de sintaxe**.
- Arquivos PHP após limpeza de exemplos duplicados: **591**.
- Arquivos totais no pacote: **2964**.

## Correções P0/P1 aplicadas

- Credenciais DB removidas do código principal.
- Autenticação `admin`/`usuario` migrada para `password_hash`/`password_verify`.
- Migração de senha legada no primeiro login.
- Sessão regenerada no login e endurecida.
- CSRF nos fluxos principais migrados.
- Rate limit de login.
- Recuperação de senha sem envio da senha armazenada.
- Token de recuperação aleatório, hash SHA-256, expiração e uso único.
- Segredos SSH de `servidor` e `usuario_ssh` com envelope libsodium.
- Uploads administrativos endurecidos.
- `update.php` deixou de exportar credenciais dos servidores.
- Path traversal no downloader OVPN corrigido.
- Comandos SSH passaram por allowlist e bloqueio de shell injection/remote download.
- Instalação remota legada desabilitada.
- Scripts de licença/instalação histórica de alto risco foram preservados somente em `quarantine/`.

## Débitos técnicos explícitos

Ainda existem **75 chamadas `PDO::query()` com interpolação detectável por busca estática** nas rotas legadas. Além disso, há muitas chamadas `prepare()` construídas a partir de strings antigas que precisam ser convertidas para parâmetros de forma individual.

Isso não significa que todas as 75 sejam exploráveis, mas significa que o sistema **não deve ser declarado livre de SQL Injection** até a revisão dessas rotas.

## Dependências

O `composer.json` foi preparado para PHP 8.5, phpseclib 2.0.55+ compatível com o namespace legado e PHPMailer 7.1.1. O `composer.lock` precisa ser gerado no ambiente de build e os pacotes devem ser instalados com Composer antes do deploy final.

## Regra de produção

Não executar conteúdo de `quarantine/`.
Não executar o instalador remoto legado.
Não colocar `.env` no webroot.
Não usar usuário DB `root`.
Não usar `chmod 777`.
Não expor backups ou dumps SQL.
