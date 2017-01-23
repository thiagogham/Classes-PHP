<?php
/*
*   Descrição: Classe para operações em servidor e base de dados RADIUS
*   Autor: Thiago R. Gham
*   Data: 02/12/2016
*   Versão 1.0
    Exemplo de Uso:
        $FreeRadius = new FreeRadius();
        
        $AdicionaRadCheck = $FreeRadius->AdicionaRadCheck($username, sha1($senha));
        if($FreeRadius->RetornouErro()){
            $AdicionaRadCheck = $FreeRadius->RetornaMensagem();
        }

        $AdicionaFramedProtocol  = $FreeRadius->AdicionaFramedProtocol($username, 'PPP');
        if($FreeRadius->RetornouErro()){
            $AdicionaFramedProtocol = $FreeRadius->RetornaMensagem();
        }   

        $FreeRadius->Fechar();
*/

class FreeRadius {
    /**
     * [$query_atual description]
     * @var string
     */
    private $query_atual    = '';
    /**
     * [$CONEXAO description]
     * @var null
     */
    private $CONEXAO        = null;
    /**
     * [$config description]
     * @var array
     */
    private $config         = array();
    /**
     * [$debug description]
     * @var boolean
     */
    public  $debug          = false;
    /**
     * [$erro description]
     * @var null
     */
    public  $erro           = null;
    /**
     * [$mensagem description]
     * @var string
     */
    public  $mensagem       = '';

    /**
     * [__construct description]
     * @param string $config [description]
     */
    function __construct($config = '') {

        if (empty($config)) {
            $this->config['server']     = 'localhost';
            $this->config['database']   = 'radius';
            $this->config['username']   = 'radius';
            $this->config['password']   = 'radius';
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
     * [ExecutaQuery description]
     * @param string $query [description]
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
     * [UpdateDados description]
     * @param [type] $table  [description]
     * @param [type] $values [description]
     * @param string $where  [description]
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
     * [DeleteDados description]
     * @param [type] $table [description]
     * @param string $where [description]
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
    /**
     * [RemoveSessao description]
     * @param string $username [description]
     */
    public function RemoveSessao($username = ''){

        if(empty($username)){
            $this->erro = true;
            $this->mensagem = 'Usuário Inválido!';
            return 0;
        }

        $where  = "(acctstoptime IS NULL 
                    OR acctsessiontime IS NULL
                    OR acctterminatecause = '' 
                    OR acctterminatecause = 'Stale-Session'
                    OR (acctinputoctets < 3000 AND acctoutputoctets < 3000)) 
                    AND username = '$username' ";

        $result = $this->DeleteDados('radacct', $where);
        return $result;
    } 
    /**
     * [RemoveUsuario description]
     * @param string $username [description]
     */
    public function RemoveUsuario($username = ''){

        if(empty($username)){
            $this->erro = true;
            $this->mensagem = 'Usuário Inválido!';
            return 0;
        }
        
        $result = $this->DeleteDados('radreply', " username = '$username' ");
        $result = $this->DeleteDados('radusergroup', " username = '$username' ");
        $result = $this->DeleteDados('radcheck', " username = '$username' ");
        
        return $result;
    } 
    /**
     * [AdicionaCallingStationID Adiciona regra do MAC com Usuário]
     * @param string $username [description]
     * @param string $value    [description]
     */
    public function AdicionaCallingStationID($username = '', $value = '0'){

        if(empty($username)) {
            return 0;
        }
        $result = $this->ExecutaQuery(sprintf("SELECT username FROM radcheck WHERE username = '%s' AND attribute = 'Calling-Station-ID' ", $username));
        if($result['nr_registros'] == 0){
            $result = $this->InsertDados('radcheck', 'username, attribute, op, value', "'$username', 'Calling-Station-ID', '==', '$value'");
        }else{
            $result = $this->UpdateDados('radcheck', "value = '$value'", "username = '$username' AND attribute = 'Calling-Station-ID'");
        }       
        return $result; 
    }
    /**
     * [RemoveCallingStationID description]
     * @param string $username [description]
     */
    public function RemoveCallingStationID($username = ''){

        if(empty($username)) {
            return 0;
        }

        $result = $this->DeleteDados('radcheck', "username = '$username' AND attribute = 'Calling-Station-ID' ");

        return $result;
    }
    /**
     * [AdicionaMaxDailySession description]
     * @param string $username [description]
     * @param string $value    [description]
     */
    public function AdicionaMaxDailySession($username = '', $value = '0'){

        if(empty($username)) {
            return 0;
        }
        $result = $this->ExecutaQuery(sprintf("SELECT username FROM radcheck WHERE username = '%s' AND attribute = 'Max-Daily-Session' ", $username));
        if($result['nr_registros'] == 0){
            $result = $this->InsertDados('radcheck', 'username, attribute, op, value', "'$username', 'Max-Daily-Session', ':=', '$value'");
        }else{
            $result = $this->UpdateDados('radcheck', "value = '$value'", "username = '$username' AND attribute = 'Max-Daily-Session'");
        }       
        return $result;
    }
    /**
     * [AdicionaSessionTerminate description]
     * @param string $username [description]
     * @param string $value    [description]
     */
    public function AdicionaSessionTerminate($username = '', $value = ''){

        if(empty($username)) {
            return 0;
        }
        $result = $this->ExecutaQuery(sprintf("SELECT username FROM radreply WHERE username = '%s' AND attribute = 'WISPr-Session-Terminate-Time' ", $username));
        if($result['nr_registros'] == 0){
            $result = $this->InsertDados('radreply', 'username, attribute, op, value', "'$username', 'WISPr-Session-Terminate-Time', ':=', '$value'");
        }else{
            $result = $this->UpdateDados('radreply', "value = '$value'", "username = '$username' AND attribute = 'WISPr-Session-Terminate-Time'");
        }       
        return $result;
    }
    /**
     * [AlteraSenha description]
     * @param string $username [description]
     * @param string $senha    [description]
     */
    public function AlteraSenha($username = '', $senha = ''){
        
        if(empty($username)){
            return 0;
        }

        $result = $this->ExecutaQuery(sprintf("SELECT username FROM radcheck WHERE username = '%s' AND attribute = 'SHA-Password'", $username));
        if($result['nr_registros'] > 0){
            $result = $this->UpdateDados('radcheck', "value = '$senha'", "username = '$username' AND attribute = 'SHA-Password'");
        }
        return $result;
    }
    /**
     * [AdicionaRadgroupReply description]
     * @param string $groupname     [description]
     * @param string $groupname_old [description]
     * @param string $value         [description]
     */
    public function AdicionaRadgroupReply($groupname = '', $groupname_old = '', $value = ''){
        
        if(empty($groupname)){
            return 0;
        }

        $result = $this->ExecutaQuery(sprintf("SELECT groupname FROM radgroupreply WHERE groupname = '%s' AND attribute = 'Mikrotik-Rate-Limit'", $groupname));
        if($result['nr_registros'] == 0 and $groupname == $groupname_old){
            $result = $this->InsertDados('radgroupreply', 'groupname, attribute, op, value', "'$groupname', 'Mikrotik-Rate-Limit', ':=', '$value'");
        }else{
            $result = $this->UpdateDados('radgroupreply', "value = '$value', groupname = '$groupname'", "groupname = '$groupname_old'");
            $result = $this->UpdateDados('radusergroup', "groupname = '$groupname'", "groupname = '$groupname_old'");
        }

        return $result;
    }
    /**
     * [AdicionaRadroupCheck description]
     * @param string $groupname     [description]
     * @param string $groupname_old [description]
     * @param string $value         [description]
     */
    public function AdicionaRadroupCheck($groupname = '', $groupname_old = '', $value = ''){
        
        if(empty($groupname)){
            return 0;
        }
        
        $result = $this->ExecutaQuery(sprintf("SELECT groupname FROM radgroupcheck WHERE groupname = '%s' AND attribute = 'Simultaneous-Use'", $groupname));
        if($result['nr_registros'] == 0 and $groupname == $groupname_old){
            $result = $this->InsertDados('radgroupcheck', 'groupname, attribute, op, value', "'$groupname', 'Simultaneous-Use', ':=', '$value'");
        }else{
            $result = $this->UpdateDados('radgroupcheck', "value = '$value', groupname = '$groupname'", "groupname = '$groupname_old'");
            $result = $this->UpdateDados('radusergroup', "groupname = '$groupname'", "groupname = '$groupname_old'");
        }

        return $result;
    }
    /**
     * [AdicionaCleartextPassword description]
     * @param string $username [description]
     * @param string $senha    [description]
     */
    public function AdicionaCleartextPassword($username = '', $senha = ''){
        
        if(empty($username)){
            return 0;
        }
        $result = $this->ExecutaQuery(sprintf("SELECT username FROM radcheck WHERE username = '%s' AND attribute = 'Cleartext-Password'", $username));
        if($result['nr_registros'] == 0){
            $result = $this->InsertDados('radcheck', 'username, attribute, op, value', "'$username', 'Cleartext-Password', ':=', '$senha'");
        }else{
            $result = $this->UpdateDados('radcheck', "value = '$senha'", "username = '$username' AND attribute = 'Cleartext-Password'");
        }

        $this->DeleteDados('radcheck', "username = '$username' AND (attribute = 'SHA-Password' OR attribute = 'Password') ");

        return $result;
    }
    /**
     * [AdicionaSHAPassword description]
     * @param string $username [description]
     * @param string $senha    [description]
     */
    public function AdicionaSHAPassword($username = '', $senha = ''){
        
        if(empty($username)){
            return 0;
        }
        $result = $this->ExecutaQuery(sprintf("SELECT username FROM radcheck WHERE username = '%s' AND attribute = 'SHA-Password'", $username));
        if($result['nr_registros'] == 0){
            $result = $this->InsertDados('radcheck', 'username, attribute, op, value', "'$username', 'SHA-Password', ':=', '$senha'");
        }else{
            $result = $this->UpdateDados('radcheck', "value = '$senha'", "username = '$username' AND attribute = 'SHA-Password'");
        }

        $this->DeleteDados('radcheck', "username = '$username' AND (attribute = 'Cleartext-Password' OR attribute = 'Password') ");

        return $result;
    }
    /**
     * [AdicionaRadCheck description]
     * @param string $username [description]
     * @param string $senha    [description]
     */
    public function AdicionaRadCheck($username = '', $senha = ''){
        
        if(empty($username)){
            return 0;
        }
        $result = $this->ExecutaQuery(sprintf("SELECT username FROM radcheck WHERE username = '%s' AND attribute = 'SHA-Password'", $username));
        if($result['nr_registros'] == 0){
            $result = $this->InsertDados('radcheck', 'username, attribute, op, value', "'$username', 'SHA-Password', ':=', '$senha'");
        }else{
            $result = $this->UpdateDados('radcheck', "value = '$senha'", "username = '$username' AND attribute = 'SHA-Password'");
        }

        return $result;
    }
    /**
     * [AdicionaRadUserGroup description]
     * @param string $username  [description]
     * @param string $groupname [description]
     * @param string $priority  [description]
     */
    public function AdicionaRadUserGroup($username = '', $groupname = '', $priority = '1'){
        
        if(empty($username) or empty($groupname)) {
            return 0;
        }
        $result = $this->ExecutaQuery(sprintf("SELECT username FROM radusergroup WHERE username = '%s'", $username));
        if($result['nr_registros'] == 0){
            $result = $this->InsertDados('radusergroup', 'username, groupname, priority', "'$username', '$groupname', '$priority'");
        }else{
            $result = $this->UpdateDados('radusergroup', "groupname = '$groupname', priority = '$priority'", "username = '$username'");
        }
        return $result;
    }
    /**
     * [AdicionaSimultaneousUse description]
     * @param string $username [description]
     * @param string $value    [description]
     */
    public function AdicionaSimultaneousUse($username = '', $value = '1'){
        
        if(empty($username)){
            return 0;
        }
        $result = $this->ExecutaQuery(sprintf("SELECT username FROM radcheck WHERE username = '%s' AND attribute = 'Simultaneous-Use'", $username));
        if($result['nr_registros'] == 0){
            $result = $this->InsertDados('radcheck', 'username, attribute, op, value', "'$username', 'Simultaneous-Use', ':=', '$value'");
        }else{
            $result = $this->UpdateDados('radcheck', "value = '$value'", "username = '$username' AND attribute = 'Simultaneous-Use'");
        }
        return $result;
    }
    /**
     * [AdicionaFramedProtocol description]
     * @param string $username [description]
     * @param string $value    [description]
     */
    public function AdicionaFramedProtocol($username = '', $value = 'PPP'){
        
        if(empty($username)){
            return 0;
        }
        $result = $this->ExecutaQuery(sprintf("SELECT username FROM radcheck WHERE username = '%s' AND attribute = 'Framed-Protocol'", $username));
        if($result['nr_registros'] == 0){
            $result = $this->InsertDados('radcheck', 'username, attribute, op, value', "'$username', 'Framed-Protocol', '==', '$value'");
        }else{
            $result = $this->UpdateDados('radcheck', "value = '$value'", "username = '$username' AND attribute = 'Framed-Protocol'");
        }
        return $result;
    }
    /**
     * [RemoveFramedProtocol description]
     * @param string $username [description]
     */
    public function RemoveFramedProtocol($username = ''){
        
        if(empty($username)){
            return 0;
        }
        
        $result = $this->DeleteDados('radcheck', "username = '$username' AND attribute = 'Framed-Protocol'");
        
        return $result;
    }
    /**
     * [BloquearUsuario description]
     * @param string $username [description]
     * @param string $value    [description]
     */
    public function BloquearUsuario($username = '', $value = 'bloqueio'){ 
        
        if(empty($username)) {
            return 0;
        }
        $result = $this->ExecutaQuery(sprintf("SELECT attribute, value FROM radreply WHERE username = '%s' AND attribute = 'Framed-Pool'", $username));
        if($result['nr_registros'] == 0){
            $result = $this->InsertDados('radreply', 'username, attribute, op, value', "'$username', 'Framed-Pool', '=', '$value'");
        }else{
            $result = $this->UpdateDados('radreply', "value = '$value'", "username = '$username' AND attribute = 'Framed-Pool'");
        }

        $this->AdicionaRadUserGroup($username, $value);

        return $result;
    }
    /**
     * [LiberarUsuario description]
     * @param string $username  [description]
     * @param string $groupname [description]
     */
    public function LiberarUsuario($username = '', $groupname = ''){

        if(empty($username)){
            return 0;
        }
        $result = $this->DeleteDados('radcheck', "username = '$username' AND attribute = 'Auth-type'");
        $result = $this->DeleteDados('radreply', "username = '$username' AND attribute = 'Framed-Pool'");

        if(!empty($groupname)){
            $this->AdicionaRadUserGroup($username, $groupname);
        }

        return $result;
    }
    /**
     * [BloquearUsuarioHotspot description]
     * @param string $username [description]
     */
    public function BloquearUsuarioHotspot($username = ''){ 
        
        if(empty($username)) {
            return 0;
        }
        $result = $this->ExecutaQuery(sprintf("SELECT attribute, value FROM radcheck WHERE username = '%s' AND attribute = 'Auth-type'", $username));
        if($result['nr_registros'] == 0){
            $result = $this->InsertDados('radcheck', 'username, attribute, op, value', "'$username', 'Auth-type', '=', 'Reject'");
        }else{
            $result = $this->UpdateDados('radcheck', "value = 'Reject'", "username = '$username' AND attribute = 'Auth-type'");
        }
        return $result;
    }
    /**
     * [LiberarUsuarioHotspot description]
     * @param string $username [description]
     */
    public function LiberarUsuarioHotspot($username = ''){ 
        
        if(empty($username)) {
            return 0;
        }

        $result = $this->DeleteDados('radcheck', "username = '$username' AND attribute = 'Auth-type'");

        return $result;
    }
    /**
     * [AtualizaIPFixo description]
     * @param string $username [description]
     * @param string $ip       [description]
     */
    public function AtualizaIPFixo($username = '', $ip = ''){ 

        if(empty($username) or empty($ip)) {
            $this->erro     = true;
            $this->mensagem = utf8_encode("O Usuário e IP são obrigatórios.");
            return 0;
        }

        $result    = $this->ExecutaQuery(sprintf("SELECT username FROM radreply WHERE  username <> '%s' AND attribute = 'Framed-IP-Address' AND value = '%s'", $username, $ip));
        if($result['nr_registros'] == 0){
            $result = $this->ExecutaQuery(sprintf("SELECT username FROM radreply WHERE username = '%s' AND attribute = 'Framed-IP-Address'", $username));
            if($result['nr_registros'] == 0){
                $result = $this->InsertDados('radreply', 'username, attribute, op, value', "'$username', 'Framed-IP-Address', ':=', '$ip'");
            }else{
                $result = $this->UpdateDados('radreply', "value = '$ip'", "username = '$username' AND attribute = 'Framed-IP-Address'");
            }      
        }else{
            $this->erro     = true;
            $this->mensagem = utf8_encode("O IP $ip já esta em uso.");
            return 0;
        }

        return $result;
    }
    /**
     * [RemoveIPFixo description]
     * @param string $username [description]
     */
    public function RemoveIPFixo($username = ''){
        if(empty($username)) {
            $this->erro = true;
            $this->mensagem = "O Usuario não informado.";
            return 0;
        }
        $result = $this->DeleteDados('radreply', "username = '$username' AND attribute = 'Framed-IP-Address'");
        return $result;
    }
}