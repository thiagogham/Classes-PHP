<?php
/**
*   Descrição:  Classe que busca desligamentos Programados por cidade
*   Author:     Thiago R. Gham 
*   Data:       08-03-2015
*   Versão:     0.1
*/
class DB {

    private $query_atual    = '';
    private $CONEXAO        = null;
    private $config         = array();
    public  $debug          = false;
    public  $erro           = null;
    public  $mensagem       = '';
    /**
    *   Descrição: Armazena linhas do registro.
    *   @param array Dados para acesso ao servidor Base de Dados
    *   @return 
    */
    function __construct($config = '') {

        if (empty($config)) {
            $this->config['server']     = 'localhost';
            $this->config['database']   = 'mysql';
            $this->config['username']   = 'root';
            $this->config['password']   = '';
            $this->config['driver']     = 'mysql';    
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
    *   Descrição: Conecta na Base de Dados.
    *   @param 
    *   @return Status Conexão
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
            
            return FALSE;
        }
    }

    /**
    *   Descrição: Fecha Conexão Base de Dados.
    *   @param 
    *   @return Status
    */
    public function Fechar() {
        $this->CONEXAO = null;
        return true;
    }

    /**
     * Descrição: Validação de ERRO
     * @param 
     * @return bool
     */
    public function RetornouErro() {
        return $this->erro;
    }
    
    /**
     * Descrição: Mesagem do ERRO
     * @param 
     * @return string
     */
    public function RetornaMensagem() {
        return $this->mensagem;
    }
    
    /**
     * Descrição: Mesagem do ERRO
     * @param 
     * @return string
     */
    public function RetornaQuery() {
        return $this->query_atual;
    }
    
    /**
     * Escape a string to be part of the database query.
     * @param str string The string to escape
     * @return str The escaped string
     */
    public function EscapeString($string) {
        //return $string;
        return str_replace('\'\'', "'", pg_escape_string($string));
    }
    
    /**
     * Descrição: Executa Query verifica erro
     * @param string query
     * @return array Resultados
     */
     public function ExecutaQuery($query = ''){
        try{
            $this->query_atual = $this->EscapeString($query);
            if($this->debug){
                echo "\n<br>".$this->query_atual."\n<br>";          
            }
            $this->erro     = false;
            $this->mensagem = "SELECT Executado com Sucesso!";

            $result       = $this->CONEXAO->query($this->query_atual);
            if($this->debug){
               print_r($result)."\n<br>";
            }
            $registros    = $result->fetchAll( PDO::FETCH_ASSOC );
            $nr_registros = count($registros);
            return array('nr_registros' => $nr_registros, 'registros' => $registros);

        }catch (PDOexception $e) { 
            $this->erro     = true;
            $this->mensagem = $e->getMessage();
            if($this->debug){
                echo "\n<br>".$this->mensagem."\n<br>";          
            }
            return $this->mensagem;
        }
    }

    /**
    * Descrição: Seleciona LInha Tabela
    * @param string tabela, string where
    * @return array Resultados
    */
    public function SelectDados($table, $campos = '*', $where = 'true') {
        $result     = $this->ExecutaQuery(sprintf('SELECT %s FROM %s WHERE %s', $campos, $table, $where));
        $registros  = $result['nr_registros'];
        $result     = $result['registros'];
        return array('nr_registros' => $registros, 'registros' => $result);
    }

    /**
     * Update a row.
     * @param str table
     * @param str values
     * @param str where
     * @return bool
     */
    public function InsertDados($table, $campos, $value) {

        try{
            $this->query_atual = $this->EscapeString(sprintf("INSERT INTO %s ( %s ) VALUES ( %s )", $table, $campos, $value));
            if($this->debug){
                echo "\n<br>".$this->query_atual."\n<br>";          
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
                echo "\n<br>".$this->mensagem."\n<br>";          
            }
            return $this->mensagem;
        }
    }
    /**
     * Update a row.
     * @param str table
     * @param str values
     * @param str where
     * @return bool
     */
    public function UpdateDados($table, $values, $where = 'TRUE') {

        try{
            $this->query_atual = $this->EscapeString(sprintf('UPDATE %s SET %s WHERE %s', $table, $values, $where));
            if($this->debug){
                echo "\n<br>".$this->query_atual."\n<br>";          
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
                echo "\n<br>".$this->mensagem."\n<br>";          
            }
            return $this->mensagem;
        }
    }

    /**
    * Get the columns in a table.
    * @param str table
    * @return resource A resultset resource
    */
    public function DeleteDados($table, $where = 'TRUE') {
 
        try{
            $this->query_atual = $this->EscapeString(sprintf('DELETE FROM %s WHERE %s', $table, $where));
            if($this->debug){
                echo "\n<br>".$this->query_atual."\n<br>";          
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
                echo "\n<br>".$this->mensagem."\n<br>";          
            }
            return $this->mensagem;
        }
    }    
}