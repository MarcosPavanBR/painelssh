<?php
declare(strict_types=1);

/**
 * Hardened SSH adapter.
 *
 * Remote commands are an administrative capability, not a general shell.
 * Dangerous network download / shell construction is rejected by default.
 */
class SSH2
{
    private $ssh;
    private $stream;

    public function __construct($host, $port = 22)
    {
        $host = filter_var($host, FILTER_VALIDATE_IP) ?: (preg_match('/^[A-Za-z0-9.-]{1,253}$/', (string)$host) ? $host : null);
        $port = filter_var($port, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>65535]]);
        if (!$host || !$port || !function_exists('ssh2_connect')) throw new RuntimeException('SSH indisponível ou destino inválido.');
        $this->ssh = @ssh2_connect($host, $port);
        if (!$this->ssh) throw new RuntimeException('Não foi possível conectar ao servidor SSH.');
    }

    public function online($host)
    {
        try { new self($host,22); return true; } catch (Throwable $e) { return false; }
    }

    public function auth($username, $auth, $private = null, $secret = null)
    {
        if (!preg_match('/^[A-Za-z0-9._@-]{1,64}$/', (string)$username)) return false;
        if (is_file((string)$auth) && is_readable((string)$auth) && $private !== null) {
            return @ssh2_auth_pubkey_file($this->ssh, $username, $auth, $private, $secret);
        }
        return @ssh2_auth_password($this->ssh, $username, (string)$auth);
    }

    public function send($local, $remote, $perm = 0640)
    {
        $local = realpath((string)$local);
        if (!$local || !is_file($local)) return false;
        if (preg_match('/[\x00\r\n;|&`$<>]/', (string)$remote)) return false;
        return @ssh2_scp_send($this->ssh, $local, (string)$remote, (int)$perm);
    }

    public function get($remote, $local)
    {
        if (preg_match('/[\x00\r\n;|&`$<>]/', (string)$remote)) return false;
        $local = (string)$local;
        $dir = dirname($local);
        if (!is_dir($dir)) return false;
        return @ssh2_scp_recv($this->ssh, $remote, $local);
    }

    private function validateCommand(string $cmd): string
    {
        $cmd = trim($cmd);
        if ($cmd === '' || strlen($cmd) > 4096) throw new InvalidArgumentException('Comando inválido.');
        if (preg_match('/[\x00\r\n;`$<>]/', $cmd)) throw new InvalidArgumentException('Comando rejeitado.');
        if (preg_match('/\b(wget|curl|nc|netcat|bash\s+<|sh\s+<|chmod\s+777|rm\s+-rf|mkfs|dd\s+if=|iptables|useradd|userdel|passwd\s+-)/i', $cmd)) {
            throw new InvalidArgumentException('Comando administrativo perigoso bloqueado.');
        }
        // Legacy conditional used by the panel. Only allow known plugin-sync actions.
        if (preg_match('/^\[\[\s+-f\s+"\/opt\/sshplus\/sshplus"\s+\]\]\s+&&\s+\/opt\/sshplus\/plugin-sync\s+(--[a-z_]+)(?:\s+([^|]+?))?\s+\|\|\s+\.\/(\w+\.sh)(?:\s+(.+))?$/', $cmd, $m)) {
            $allowed = ['--del_user','--pass_user','--date_user','--limit_user','--create_user','--kill_user','--monitor_users'];
            if (!in_array($m[1],$allowed,true)) throw new InvalidArgumentException('Ação SSH não permitida.');
            if (preg_match('/[\x00\r\n;|&`$<>]/', ($m[2] ?? '') . ' ' . ($m[4] ?? ''))) throw new InvalidArgumentException('Argumento SSH rejeitado.');
            $fallbackAllowed=['remover.sh','AlterarSenha.sh','AlterarData.sh','alterarlimite.sh','criarusuario.sh','KillUser.sh','sshmonitor.sh'];
            if (!in_array($m[3],$fallbackAllowed,true)) throw new InvalidArgumentException('Script SSH não permitido.');
            return $cmd;
        }
        if (preg_match('/^(\/opt\/sshplus\/plugin-sync|\.\/(remover|AlterarSenha|AlterarData|alterarlimite|criarusuario|KillUser|sshmonitor)\.sh)(?:\s+.*)?$/', $cmd)) return $cmd;
        if (in_array($cmd,['pwd','ls -la','ping 127.0.0.1','reboot','shutdown','service squid3 restart','service squid restart'],true)) return $cmd;
        throw new InvalidArgumentException('Comando remoto não está na allowlist.');
    }

    public function cmd($cmd, $blocking = true)
    {
        $cmd = $this->validateCommand((string)$cmd);
        $this->stream = @ssh2_exec($this->ssh, $cmd);
        if (!$this->stream) throw new RuntimeException('Falha ao executar comando SSH.');
        stream_set_blocking($this->stream, (bool)$blocking);
        return true;
    }

    public function exec($cmd, $blocking = true) { return $this->cmd($cmd, $blocking); }
    public function output() { return $this->stream ? stream_get_contents($this->stream) : ''; }
}
