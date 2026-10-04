# SEG-04 — SQL dinâmico legado permanece no módulo Conecta4G

**Severidade:** alta  
**Status:** provavel

## Evidência
`gestorssh/conecta4g/editar.php:110-112`

```text
        $busca = $conn->query("SELECT * FROM mensagens WHERE id_owner='$id_owner'");

        if ($busca->rowCount() > 0) :
```

SHA-256: `917a970f65f902199272951c6ca8b05554a53d12efa65d5b9ba167dc82b7811b`

## Descrição
A varredura encontrou 53 chamadas PDO::query interpoladas e 0 chamadas mysqli::query interpoladas fora de vendor. O módulo Conecta4G ainda contém entradas vindas de variáveis de requisição em SQL dinâmico.

## Correção
Converter cada consulta dinâmica para prepared statement com parâmetros e testar autorização do recurso.
