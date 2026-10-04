# Verificação da auditoria — 2026-10-04

## Resultado

A versão publicada em `MarcosPavanBR/painelssh` contém as correções de hardening já descritas no relatório para roteamento, autenticação, sessão e CSRF. Ela **não deve ser declarada pronta para produção** porque os dois bloqueadores abaixo continuam presentes.

## Achados conferidos

| Achado | Situação | Evidência atual |
|---|---|---|
| SEG-01 — allowlist no roteador do usuário | Aplicado | `gestorssh/home.php` usa `$allowedPages` antes do include |
| SEG-01 — allowlist no roteador administrativo | Aplicado | `gestorssh/admin/home.php` usa `$allowedPages` antes do include |
| SEG-02 — edição administrativa com CSRF, prepared statements e hash | Aplicado | `gestorssh/admin/pages/usuario/editar_exe.php` |
| SEG-03 — login Conecta4G com `password_verify`, migração de MD5 legado e regeneração de sessão | Aplicado | `gestorssh/conecta4g/login.php` |
| SEG-04 — SQL dinâmico legado | **Corrigido estaticamente** | Consultas interpoladas dos endpoints Conecta4G foram migradas para prepared statements; falta confirmar em staging com MariaDB/IDOR |
| DOC-01 — ofuscação do módulo `appss` | **Pendente** | `gestorssh/appss/*.php` ainda usa `GLOBALS` e escapes hexadecimais |

As consultas que recebiam valores de entrada nos endpoints Conecta4G foram migradas individualmente para prepared statements. Restam consultas estáticas e identificadores de coluna/tabela controlados por allowlists; a validação end-to-end ainda depende de MariaDB e testes de autorização/IDOR.

## Plataforma

A documentação pública foi atualizada para recomendar Ubuntu 24.04 LTS e PHP 8.3 ou superior, mantendo compatibilidade declarada a partir do PHP 8.1. Ubuntu 18.04/20.04 e instaladores antigos permanecem apenas como material histórico em `quarantine/legacy/` e não são caminhos suportados.

## Validações desta verificação

- Comparação direta dos trechos de código com os achados do PDF;
- busca estática de consultas `->query()` fora de `vendor`;
- inventário de referências de Ubuntu e instaladores;
- revisão do README, requisitos e estado do repositório.

Ainda faltam MariaDB/pdo_mysql, Composer com `composer.lock` e testes end-to-end no ambiente atual; portanto esta verificação não substitui um gate de produção.
