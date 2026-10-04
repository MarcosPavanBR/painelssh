# Autorização

- `protegePagina("user")`/`protegePagina("admin")` exigidos nas áreas principais.
- Fluxos de edição de usuário verificam propriedade `id_mestre` quando aplicável.
- O módulo Conecta4G ainda requer revisão endpoint a endpoint para eliminar confiança em `id_owner` enviado pelo cliente.
