<?php
/**
 * Descrição: Classe para operações em servidor e base de dados Prosody
 * Autor: Thiago R. Gham
 * Data: 05-07-2016
 * Versão: 1.0
 */
class Prosody {
    /**
     * [$query_atual description]
     * @var string
     */
    private $query_atual  = '';
    /**
     * [$CONEXAO description]
     * @var null
     */
    private $CONEXAO      = null;
    /**
     * [$config description]
     * @var array
     */
    private $config       = array();
    /**
     * [$debug description]
     * @var boolean
     */
    public  $debug        = false;
    /**
     * [$erro description]
     * @var null
     */
    public  $erro         = null;
    /**
     * [$mensagem description]
     * @var string
     */
    public  $mensagem     = '';
    /**
     * [__construct description]
     * @param string $config [description]
     */
    function __construct($config = '') {
        if (empty($config)) {
            $this->config['server']     = 'localhost';
            $this->config['database']   = 'prosody';
            $this->config['username']   = 'prosody';
            $this->config['password']   = 'prosody';
            $this->config['driver']     = 'pgsql';
        }else{
            $this->config['server']     = $config['server'];
            $this->config['database']   = $config['database'];
            $this->config['username']   = $config['username'];
            $this->config['password']   = $config['password'];
            $this->config['driver']     = $config['driver'];
        }
        return $this->Conectar();
    }
    /**
     * [Conectar description]
     */
    private function Conectar(){    
        
        try{
            switch ($this->config['driver']) {
                case 'mysql':
                    $this->CONEXAO = new PDO( "mysql:host={$this->config['server']};dbname={$this->config['database']}", $this->config['username'], $this->config['password']);
                    break;
                case 'pgsql':
                    $this->CONEXAO = new PDO( "pgsql:host={$this->config['server']} dbname={$this->config['database']} user={$this->config['username']} password={$this->config['password']}" );
                    $this->CONEXAO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    break;
                default:
                    $connString = sprintf('%s:host=%s dbname=%s user=%s password=%s',
                                    $this->config['driver'],
                                    $this->config['server'],
                                    $this->config['database'],
                                    $this->config['username'],
                                    $this->config['password']);
                    $this->CONEXAO = new PDO( $connString );
                    break;
            }
            $this->config = array();
            return TRUE;

        }catch (PDOexception $e) {
            die("PDO Exception: " . $e->getMessage());
        }
    }
    /**
     * [Fechar description]
     */
    public function Fechar() {
        $this->CONEXAO = null;
        return true;
    }
    /**
     * [RetornouErro description]
     */
    public function RetornouErro() {
        return $this->erro;
    }
    /**
     * [RetornaMensagem description]
     */
    public function RetornaMensagem() {
        return $this->mensagem;
    }    
    /**
     * [RetornaQuery description]
     */
    public function RetornaQuery() {
        return $this->query_atual;
    }    
    /**
     * [EscapeString description]
     * @param [type] $string [description]
     */
    public function EscapeString($string) {
        return str_replace('\'\'', "'", pg_escape_string($string));
    }
    /**
     * [NovoUsuario description]
     * @param [type] $user  [description]
     * @param [type] $senha [description]
     * @param string $host  [description]
     */
    public function NovoUsuario($user, $senha, $host = 'localhost.com.br') {

        $result = $this->SelectDados('prosody', '*', "\"user\" = '$user' and store = 'accounts'"); 
        if($result['nr_registros'] > 0){
            $this->erro = true;
            $this->mensagem = 'Usuário já existe!';
            return false;
        }

        $campos = '"host", "user", "store", "key", "type", "value"';
        $value  = "'$host', '$user', 'accounts', 'password', 'string', '$senha'";
        $retorno = $this->InsertDados('prosody', $campos, $value);
        if($this->erro){
            return $retorno;
        }
    } 
    /**
     * [UltimasMensagens description]
     * @param [type] $user [description]
     * @param string $dias [description]
     */
    public function UltimasMensagens($user, $dias = '30') {

        $sql = "SELECT \"user\", to_timestamp(MAX(\"when\")) as data
                FROM prosodyarchive
                WHERE \"with\" LIKE '$user%' 
                AND \"user\" <> '$user' 
                AND to_timestamp(\"when\") >= (now() - interval '$dias days')
                GROUP BY \"user\"; ";

        $result     = $this->ExecutaQuery($sql);
        $registros  = $result['nr_registros'];
        $result     = $result['registros'];
        return array('nr_registros' => $registros, 'registros' => $result);
    }
    /**
     * [HistoricoChat description]
     * @param [type] $user [description]
     * @param [type] $with [description]
     */
    public function HistoricoChat($user, $with) {

        $sql = "SELECT \"user\", \"when\", stanza
                FROM prosodyarchive
                WHERE store = 'message_log'
                AND \"user\" = '$user' 
                AND \"with\" LIKE '$with%'
                ORDER BY \"when\"";

        $result     = $this->ExecutaQuery($sql);
        $registros  = $result['nr_registros'];
        $result     = $result['registros'];
        return array('nr_registros' => $registros, 'registros' => $result);
    }  
    /**
     * [ExecutaQuery description]
     * @param string $query [description]
     */
    private function ExecutaQuery($query = ''){
        try{
            $this->query_atual = $this->EscapeString($query);
            if($this->debug){
                echo $this->query_atual."\n<br>";          
            }
            $this->erro     = false;
            $this->mensagem = "SELECT Executado com Sucesso!";

            $result       = $this->CONEXAO->query($this->query_atual);
            $registros    = $result->fetchAll( PDO::FETCH_ASSOC );
            $nr_registros = count($registros);
            return array('nr_registros' => $nr_registros, 'registros' => $registros);

        }catch (PDOexception $e) { 
            $this->erro     = true;
            $this->mensagem = $e->getMessage();
            if($this->debug){
                echo $this->mensagem."\n<br>";          
            }
            return $this->mensagem;
        }
    }
    /**
     * [SelectDados description]
     * @param [type] $table  [description]
     * @param string $campos [description]
     * @param string $where  [description]
     */
    public function SelectDados($table, $campos = '*', $where = 'true') {
        $result     = $this->ExecutaQuery(sprintf('SELECT %s FROM %s WHERE %s', $campos, $table, $where));
        $registros  = $result['nr_registros'];
        $result     = $result['registros'];
        return array('nr_registros' => $registros, 'registros' => $result);
    }
    /**
     * [InsertDados description]
     * @param [type] $table  [description]
     * @param [type] $campos [description]
     * @param [type] $value  [description]
     */
    public function InsertDados($table, $campos, $value) {

        try{
            $this->query_atual = $this->EscapeString(sprintf("INSERT INTO %s ( %s ) VALUES ( %s )", $table, $campos, $value));
            if($this->debug){
                echo $this->query_atual."\n<br>";          
            }
            $this->erro     = false;
            $this->mensagem = "INSERT Executado com Sucesso!";
            $result         = $this->CONEXAO->query($this->query_atual);
            $nr_registros   = $result->rowCount();

            return $nr_registros;

        }catch (PDOexception $e) { 
            $this->erro     = true;
            $this->mensagem = $e->getMessage();
            if($this->debug){
                echo $this->mensagem."\n<br>";          
            }
            return $this->mensagem;
        }
    }
    /**
     * [UpdateDados description]
     * @param [type] $table  [description]
     * @param [type] $values [description]
     * @param string $where  [description]
     */
    public function UpdateDados($table, $values, $where = 'TRUE') {

        try{
            $this->query_atual = $this->EscapeString(sprintf('UPDATE %s SET %s WHERE %s', $table, $values, $where));
            if($this->debug){
                echo $this->query_atual."\n<br>";          
            }
            $this->erro     = false;
            $this->mensagem = "UPDATE Executado com Sucesso!";
            $result         = $this->CONEXAO->query($this->query_atual);
            $nr_registros   = $result->rowCount();

            return $nr_registros;

        }catch (PDOexception $e) { 
            $this->erro     = true;
            $this->mensagem = $e->getMessage();
            if($this->debug){
                echo $this->mensagem."\n<br>";          
            }
            return $this->mensagem;
        }

    }
    /**
     * [DeleteDados description]
     * @param [type] $table [description]
     * @param string $where [description]
     */
    public function DeleteDados($table, $where = 'TRUE') {
 
        try{
            $this->query_atual = $this->EscapeString(sprintf('DELETE FROM %s WHERE %s', $table, $where));
            if($this->debug){
                echo $this->query_atual."\n<br>";          
            }
            $this->erro     = false;
            $this->mensagem = "DELETE Executado com Sucesso!";
            $result         = $this->CONEXAO->query($this->query_atual);
            $nr_registros   = $result->rowCount();

            return $nr_registros;

        }catch (PDOexception $e) { 
            $this->erro     = true;
            $this->mensagem = $e->getMessage();
            if($this->debug){
                echo $this->mensagem."\n<br>";          
            }
            return $this->mensagem;
        }
    }    
}